@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1>
        <i class="fa fa-cubes"></i>
        {{ $sampah ? 'Ubah Jenis Sampah' : 'Tambah Jenis Sampah' }}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form {{ $sampah ? 'Ubah' : 'Tambah' }} Jenis Sampah</h3>
                </div>

                {!! form_open($form_action, '') !!}

                    <div class="box-body">
                        <div class="form-group">
                            <label for="kategori_id">Kategori <span class="text-danger">*</span></label>
                            <select class="form-control" id="kategori_id" name="kategori_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriList as $k)
                                    <option value="{{ $k->id }}" {{ old('kategori_id', $sampah->kategori_id ?? '') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nama">Nama Jenis Sampah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                value="{{ old('nama', $sampah->nama ?? '') }}" required
                                placeholder="Contoh: Kertas HVS, Botol Plastik">
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="satuan">Satuan <span class="text-danger">*</span></label>
                                    <select class="form-control" id="satuan" name="satuan" required>
                                        <option value="kg" {{ old('satuan', $sampah->satuan ?? 'kg') == 'kg' ? 'selected' : '' }}>kg</option>
                                        <option value="pcs" {{ old('satuan', $sampah->satuan ?? '') == 'pcs' ? 'selected' : '' }}>pcs</option>
                                        <option value="liter" {{ old('satuan', $sampah->satuan ?? '') == 'liter' ? 'selected' : '' }}>liter</option>
                                        <option value="unit" {{ old('satuan', $sampah->satuan ?? '') == 'unit' ? 'selected' : '' }}>unit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="harga_beli">Harga Beli (per satuan) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="harga_beli" name="harga_beli"
                                        value="{{ old('harga_beli', $sampah->harga_beli ?? 0) }}" required min="0">
                                    <small class="text-muted">Harga beli dari nasabah</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="harga_jual">Harga Jual (per satuan)</label>
                                    <input type="number" class="form-control" id="harga_jual" name="harga_jual"
                                        value="{{ old('harga_jual', $sampah->harga_jual ?? 0) }}" min="0">
                                    <small class="text-muted">Harga jual ke pengepul</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="aktif">Status</label>
                            <select class="form-control" id="aktif" name="aktif">
                                <option value="1" {{ old('aktif', $sampah->aktif ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('aktif', $sampah->aktif ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ url('bank-sampah/jenis-sampah') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary pull-right">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
