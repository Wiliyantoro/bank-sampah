# 🔍 ANALISA: Seeder vs Data Custom Desa — Kategori & Jenis Sampah

## 📊 Status Data Saat Ini

### Kategori Sampah (6 data dari seeder)

| ID | Nama | Keterangan | Aktif |
|----|------|------------|-------|
| 1 | Organik | Sisa makanan, daun, dll | ✅ |
| 2 | Anorganik | Plastik, botol, kardus, dll | ✅ |
| 3 | B3 | Baterai, lampu, dll | ✅ |
| 4 | Kertas | HVS, koran, majalah, kardus | ✅ |
| 5 | Logam | Kaleng, besi, tembaga, aluminium | ✅ |
| 6 | Kaca | Botol kaca, pecahan kaca | ✅ |

### Jenis Sampah (16 data dari seeder)

| ID | Kategori | Nama | Harga Beli | Harga Jual |
|----|----------|------|-----------|-----------|
| 1 | Organik | Sisa Makanan | Rp200 | Rp500 |
| 2 | Organik | Daun Kering | Rp100 | Rp300 |
| 3 | Anorganik | Botol Plastik (PET) | Rp1.500 | Rp3.500 |
| ... | ... | ... | ... | ... |
| 24 | B3 | Lampu Neon | Rp2.000 | Rp5.000 |

---

## 🔴 MASALAH KRITIS

### 1. Seeder Pakai `count() > 0` — Global Check

```php
// ❌ SEKARANG (SALAH)
public function run(): void
{
    if (DB::table('bank_sampah_kategori')->count() > 0) {
        return; // SKIP jika ADA data (dari desa manapun!)
    }
    // insert data...
}
```

**Masalah:**
```
Database gabungan: Desa A (config_id=17) + Desa B (config_id=25)

1. Desa A install modul → seeder jalan → insert 6 kategori ✅
2. Desa B install modul → seeder jalan → count() > 0 → SKIP ❌
   → Desa B TIDAK punya kategori bawaan!
   → Desa B harus buat manual dari awal
```

### 2. Tidak Ada Flag `is_default`

```
Data Organik di bank_sampah_kategori:
  → Tidak bisa dibedakan mana yang dari seeder (bawaan)
  → Tidak bisa dibedakan mana yang ditambah/diedit oleh desa
  → Jika desa edit "Organik" → mengubah data bawaan
  → Desa lain yang belum edit → masih pakai data lama
  → Hasilnya: inkonsisten antar desa
```

### 3. Tidak Ada `config_id` — Data Campur

```
bank_sampah_kategori:
  id=1, nama="Organik"     ← dari seeder Desa A
  id=7, nama="Organik"     ← dari seeder Desa B (GAGAL: duplikat!)
  
bank_sampah_sampah:
  kategori_id=1 ← milik Desa A atau Desa B? TIDAK JELAS!
```

### 4. Unique Constraint Global

```sql
-- Sekarang
bank_sampah_lokasi.kode UNIQUE → "TPS-001" cuma 1 di SELURUH database
bank_sampah_member.no_member UNIQUE → "M-001" cuma 1

-- Jika 2 desa pakai kode yang sama → COLLISION!
```

---

## 📐 Pola yang Digunakan OpenSID Core

### Pattern 1: `config_id` + `sumber` (suplemen)

```sql
-- Tabel suplemen
config_id  INT     → per desa
sumber     ENUM('OpenSID','OpenKab') → sumber data
```

### Pattern 2: `config_id` + `lock` (analisis_master)

```sql
-- Tabel analisis_master
config_id  INT     → per desa
lock       TINYINT → 1=terkunci (tidak bisa dihapus), 0=bisa dihapus
```

### Pattern 3: `config_id` + `created_by` (ref_jabatan)

```sql
-- Tabel ref_jabatan
config_id  INT     → per desa
created_by INT     → siapa yang buat
```

### Pattern 4: Prodeskel `config_id` (per desa)

```sql
-- Semua data punya config_id
-- Seeder pakai identitas('id') → data bawaan per desa
-- Unique: (config_id, kolom_lain) → per desa
```

---

## ✅ SOLUSI: Tambah `is_default` + `config_id`

### Konsep

```
┌─────────────────────────────────────────────────────────────┐
│                    DATA KATEGORI SAMPAH                     │
├─────┬────────────┬─────────────┬──────────┬────────────────┤
│ ID  │ config_id  │ nama        │ is_default│ sumber        │
├─────┼────────────┼─────────────┼──────────┼────────────────┤
│ 1   │ 17         │ Organik     │ 1        │ system        │ ← Seeder
│ 2   │ 17         │ Anorganik   │ 1        │ system        │ ← Seeder
│ 3   │ 17         │ B3          │ 1        │ system        │ ← Seeder
│ 4   │ 17         │ Kertas      │ 1        │ system        │ ← Seeder
│ 5   │ 17         │ Logam       │ 1        │ system        │ ← Seeder
│ 6   │ 17         │ Kaca        │ 1        │ system        │ ← Seeder
│ 7   │ 17         │ Elektronik  │ 0        │ desa          │ ← Ditambah desa
│ 8   │ 17         │ Organik Lokal│ 0       │ desa          │ ← Ditambah desa
└─────┴────────────┴─────────────┴──────────┴────────────────┘
```

### Kolom Baru

| Kolom | Tipe | Default | Keterangan |
|-------|------|---------|------------|
| `config_id` | INT | NULL | FK ke `config` → isolasi per desa |
| `is_default` | TINYINT | 0 | 1=data bawaan (seeder), 0=data custom desa |
| `sumber` | ENUM | 'system' | 'system'=seeder, 'desa'=tambahan desa |

### Aturan Bisnis

```
1. SEEDER → insert data dengan is_default=1, sumber='system'
2. DESA TAMBAH → insert data dengan is_default=0, sumber='desa', config_id=identitas('id')
3. DESA EDIT data seeder → data tetap is_default=1, tapi ada flag "modified"
4. DESA HAPUS data seeder → TIDAK BOLEH (is_default=1 dilindungi)
5. DESA HAPUS data custom → BOLEH (is_default=0)
6. FILTER → SELECT * WHERE config_id = identitas('id')
```

### Strategi Seeder Per-Desa

```php
// ✅ BARU (BENAR)
public function run(): void
{
    $configId = identitas('id');
    
    // Cek apakah DESA INI sudah punya data
    $exists = DB::table('bank_sampah_kategori')
        ->where('config_id', $configId)
        ->exists();
    
    if ($exists) {
        return; // Skip jika DESA INI sudah punya data
    }
    
    // Insert data bawaan untuk DESA INI
    $kategori = [
        ['config_id' => $configId, 'nama' => 'Organik', 'is_default' => 1, 'sumber' => 'system', ...],
        ['config_id' => $configId, 'nama' => 'Anorganik', 'is_default' => 1, 'sumber' => 'system', ...],
        // ...
    ];
    DB::table('bank_sampah_kategori')->insert($kategori);
}
```

---

## 📋 Migration yang Diperlukan

### 1. Tambah Kolom ke `bank_sampah_kategori`

```php
Schema::table('bank_sampah_kategori', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->tinyInteger('is_default')->default(0)->after('keterangan');
    $table->string('sumber', 20)->default('system')->after('is_default');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
    $table->index(['config_id', 'is_default']);
});
```

### 2. Tambah Kolom ke `bank_sampah_sampah`

```php
Schema::table('bank_sampah_sampah', function (Blueprint $table) {
    $table->integer('config_id')->nullable()->after('id');
    $table->tinyInteger('is_default')->default(0)->after('aktif');
    $table->string('sumber', 20)->default('system')->after('is_default');
    $table->foreign('config_id')->references('id')->on('config')
          ->onUpdate('cascade')->onDelete('cascade');
    $table->index(['config_id', 'is_default']);
});
```

### 3. Update Unique Constraints

```php
// Kategori: unik nama per desa
Schema::table('bank_sampah_kategori', function (Blueprint $table) {
    $table->unique(['config_id', 'nama']);
});

// Sampah: unik nama per kategori per desa
Schema::table('bank_sampah_sampah', function (Blueprint $table) {
    $table->unique(['config_id', 'kategori_id', 'nama']);
});
```

### 4. Update Data Existing

```php
// Set config_id untuk data yang sudah ada
$desaId = identitas('id');
DB::table('bank_sampah_kategori')
    ->whereNull('config_id')
    ->update(['config_id' => $desaId, 'is_default' => 1, 'sumber' => 'system']);

DB::table('bank_sampah_sampah')
    ->whereNull('config_id')
    ->update(['config_id' => $desaId, 'is_default' => 1, 'sumber' => 'system']);
```

---

## 📋 Update Model

### BankSampahKategori

```php
class BankSampahKategori extends BaseModel
{
    protected $fillable = [
        'config_id',
        'nama',
        'keterangan',
        'aktif',
        'is_default',
        'sumber',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Scope: filter per desa
    public function scopeDesa($query)
    {
        return $query->where('config_id', identitas('id'));
    }

    // Scope: hanya data bawaan
    public function scopeDefault($query)
    {
        return $query->where('is_default', 1);
    }

    // Scope: hanya data custom desa
    public function scopeCustom($query)
    {
        return $query->where('is_default', 0);
    }

    // Cek apakah ini data bawaan
    public function isDefault(): bool
    {
        return $this->is_default === 1;
    }

    // Cek apakah ini data custom desa
    public function isCustom(): bool
    {
        return $this->is_default === 0;
    }
}
```

### BankSampahSampah

```php
class BankSampahSampah extends BaseModel
{
    protected $fillable = [
        'config_id',
        'kategori_id',
        'nama',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'aktif',
        'is_default',
        'sumber',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok'       => 'decimal:2',
    ];

    public function scopeDesa($query)
    {
        return $query->where('config_id', identitas('id'));
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', 1);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_default', 0);
    }

    public function isDefault(): bool
    {
        return $this->is_default === 1;
    }
}
```

---

## 📋 Update Controller

### Kategori Controller

```php
public function index(Request $request)
{
    $query = BankSampahKategori::desa(); // ← Filter per desa

    if ($request->has('cari') && $request->cari !== '') {
        $query->where('nama', 'LIKE', '%' . safe_like($request->cari) . '%');
    }

    $kategori = $query->orderBy('nama')->paginate(15);
    return view('banksampah::backend.kategori.index', compact('kategori'));
}

public function store(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:100',
    ]);

    BankSampahKategori::create([
        'config_id'  => identitas('id'),  // ← Tambah config_id
        'nama'       => $request->nama,
        'keterangan' => $request->keterangan,
        'aktif'      => $request->input('aktif', 1),
        'is_default' => 0,                // ← Data custom
        'sumber'     => 'desa',           // ← Dari desa
    ]);

    return redirect()->route('bank-sampah/kategori')
        ->with('success', 'Kategori berhasil ditambahkan');
}

public function destroy($id)
{
    $kategori = BankSampahKategori::desa()->findOrFail($id);

    // Cek apakah data bawaan
    if ($kategori->isDefault()) {
        return redirect()->route('bank-sampah/kategori')
            ->with('error', 'Data bawaan tidak bisa dihapus');
    }

    if ($kategori->sampah()->count() > 0) {
        return redirect()->route('bank-sampah/kategori')
            ->with('error', 'Tidak bisa hapus, masih ada jenis sampah di kategori ini');
    }

    $kategori->delete();
    return redirect()->route('bank-sampah/kategori')
        ->with('success', 'Kategori berhasil dihapus');
}
```

### Sampah Controller

```php
public function index(Request $request)
{
    $query = BankSampahSampah::with('kategori')->desa(); // ← Filter per desa

    if ($request->has('cari') && $request->cari !== '') {
        $query->where('nama', 'LIKE', '%' . safe_like($request->cari) . '%');
    }

    if ($request->has('kategori_id') && $request->kategori_id !== '') {
        $query->where('kategori_id', $request->kategori_id);
    }

    $sampah = $query->orderBy('kategori_id')->orderBy('nama')->paginate(15);
    $kategoriList = BankSampahKategori::desa()->aktif()->orderBy('nama')->get();

    return view('banksampah::backend.sampah.index', compact('sampah', 'kategoriList'));
}

public function store(Request $request)
{
    $request->validate([
        'nama'        => 'required|string|max:150',
        'kategori_id' => 'required|integer',
        'satuan'      => 'required|string|max:20',
        'harga_beli'  => 'required|numeric|min:0',
        'harga_jual'  => 'nullable|numeric|min:0',
    ]);

    BankSampahSampah::create([
        'config_id'  => identitas('id'),  // ← Tambah config_id
        'kategori_id' => $request->kategori_id,
        'nama'        => $request->nama,
        'satuan'      => $request->satuan,
        'harga_beli'  => $request->harga_beli,
        'harga_jual'  => $request->input('harga_jual', 0),
        'aktif'       => $request->input('aktif', 1),
        'is_default'  => 0,               // ← Data custom
        'sumber'      => 'desa',          // ← Dari desa
    ]);

    return redirect()->route('bank-sampah/sampah')
        ->with('success', 'Jenis sampah berhasil ditambahkan');
}
```

---

## 📋 Update Seeder

### KategoriSeeder

```php
public function run(): void
{
    $configId = identitas('id');

    // Skip jika DESA INI sudah punya data
    $exists = DB::table('bank_sampah_kategori')
        ->where('config_id', $configId)
        ->exists();
    if ($exists) {
        return;
    }

    $kategori = [
        ['config_id' => $configId, 'nama' => 'Organik',   'is_default' => 1, 'sumber' => 'system', ...],
        ['config_id' => $configId, 'nama' => 'Anorganik', 'is_default' => 1, 'sumber' => 'system', ...],
        ['config_id' => $configId, 'nama' => 'B3',        'is_default' => 1, 'sumber' => 'system', ...],
        ['config_id' => $configId, 'nama' => 'Kertas',    'is_default' => 1, 'sumber' => 'system', ...],
        ['config_id' => $configId, 'nama' => 'Logam',     'is_default' => 1, 'sumber' => 'system', ...],
        ['config_id' => $configId, 'nama' => 'Kaca',      'is_default' => 1, 'sumber' => 'system', ...],
    ];
    DB::table('bank_sampah_kategori')->insert($kategori);
}
```

### SampahSeeder

```php
public function run(): void
{
    $configId = identitas('id');

    // Skip jika DESA INI sudah punya data
    $exists = DB::table('bank_sampah_sampah')
        ->where('config_id', $configId)
        ->exists();
    if ($exists) {
        return;
    }

    // Perlu resolve kategori_id berdasarkan config_id desa ini
    $kategoriMap = [];
    foreach (['Organik' => 1, 'Anorganik' => 2, 'B3' => 3, 'Kertas' => 4, 'Logam' => 5, 'Kaca' => 6] as $nama => $legacyId) {
        $row = DB::table('bank_sampah_kategori')
            ->where('config_id', $configId)
            ->where('nama', $nama)
            ->first();
        if ($row) {
            $kategoriMap[$nama] = $row->id;
        }
    }

    $sampah = [
        // Organik
        ['config_id' => $configId, 'kategori_id' => $kategoriMap['Organik'] ?? 1, 'nama' => 'Sisa Makanan', 'is_default' => 1, 'sumber' => 'system', ...],
        // ... semua data lainnya
    ];
    DB::table('bank_sampah_sampah')->insert($sampah);
}
```

---

## 📋 Update View (Badge Indikator)

### Kategori Index

```blade
<td>
    <strong>{{ $item->nama }}</strong>
    @if($item->is_default)
        <span class="label label-default" title="Data bawaan sistem">
            <i class="fa fa-lock"></i> Bawaan
        </span>
    @else
        <span class="label label-success" title="Data custom desa">
            <i class="fa fa-pencil"></i> Desa
        </span>
    @endif
</td>
```

### Aksi Kolom

```blade
<td>
    <a href="{{ ci_route('bank-sampah/kategori-sampah/ubah', $item->id) }}" 
       class="btn btn-warning btn-xs" title="Edit">
        <i class="fa fa-edit"></i>
    </a>
    @if(!$item->is_default)
        {{-- Hanya data custom yang bisa dihapus --}}
        <form action="{{ ci_route('bank-sampah/kategori-sampah/hapus', $item->id) }}" 
              method="POST" style="display:inline" 
              onsubmit="return confirm('Yakin hapus kategori ini?')">
            @csrf
            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                <i class="fa fa-trash"></i>
            </button>
        </form>
    @else
        <span class="btn btn-default btn-xs" title="Data bawaan tidak bisa dihapus" 
              style="opacity: 0.5; cursor: not-allowed;">
            <i class="fa fa-lock"></i>
        </span>
    @endif
</td>
```

---

## 📊 Ringkasan Perubahan

| Komponen | Perubahan | Prioritas |
|----------|-----------|-----------|
| **Migration** | Tambah `config_id`, `is_default`, `sumber` ke kategori & sampah | 🔴 CRITICAL |
| **Unique Constraint** | `UNIQUE(config_id, nama)` untuk kategori | 🔴 CRITICAL |
| **Model** | Tambah scope `desa()`, `is_default()`, `isCustom()` | 🔴 CRITICAL |
| **Controller** | Filter `desa()`, set `config_id` saat create, cek `is_default` saat hapus | 🔴 CRITICAL |
| **Seeder** | Check per-desa (`where config_id =`), set `is_default=1, sumber='system'` | 🔴 CRITICAL |
| **View** | Badge "Bawaan"/"Desa", sembunyi tombol hapus untuk data bawaan | 🟠 HIGH |

---

*Analisa ini memastikan data seeder tidak tercampur dengan data custom desa, dan setiap desa punya data bawaan sendiri.*
