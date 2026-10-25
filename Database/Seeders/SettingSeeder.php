<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * Semua pengaturan modul didaftarkan ke Pengaturan Aplikasi inti
 * (tabel setting_aplikasi, per config_id) via createSettings() dari
 * App\Traits\Migrator — pola modul resmi (bukutamu/ppid/anjungan).
 * Tidak ada lagi .env maupun nilai hardcoded di Config/config.php,
 * dan halaman pengaturan buatan modul dihapus (issue #43 item 5,
 * D-001 untuk pengaturan dashboard warga).
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Database\Seeders;

use App\Traits\Migrator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    use Migrator;

    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        $this->createSettings([
            [
                'judul'      => 'Nama Bank Sampah',
                'key'        => 'bank_sampah_nama',
                'value'      => 'Bank Sampah Desa',
                'keterangan' => 'Nama bank sampah yang tampil di struk, kartu nasabah, dan laporan',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Alamat Bank Sampah',
                'key'        => 'bank_sampah_alamat',
                'value'      => '',
                'keterangan' => 'Alamat yang tampil di kop laporan dan kartu nasabah (kosongkan untuk memakai alamat kantor desa)',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'No. HP / WhatsApp Bank Sampah',
                'key'        => 'bank_sampah_no_hp',
                'value'      => '',
                'keterangan' => 'Nomor kontak yang tampil di kop laporan (kosongkan untuk memakai telepon desa)',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Logo Bank Sampah',
                'key'        => 'bank_sampah_logo',
                'value'      => '',
                'keterangan' => 'Path/logo yang tampil di kartu nasabah dan struk (kosongkan untuk memakai logo desa)',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Prefix Nomor Nasabah',
                'key'        => 'bank_sampah_prefix_no_member',
                'value'      => 'BS',
                'keterangan' => 'Awalan nomor nasabah yang dibuat otomatis',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Auto Generate Nomor Nasabah',
                'key'        => 'bank_sampah_auto_generate_no_member',
                'value'      => 1,
                'keterangan' => '1 = nomor nasabah dibuat otomatis; 0 = diisi manual petugas',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Format Cetak Struk',
                'key'        => 'bank_sampah_format_struk',
                'value'      => 'thermal',
                'keterangan' => 'Isi "thermal" untuk struk kecil (58/80mm) atau "a4" untuk kertas A4',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Multi Lokasi / TPS',
                'key'        => 'bank_sampah_multi_lokasi',
                'value'      => 1,
                'keterangan' => '1 = transaksi boleh memilih lokasi/TPS; 0 = hanya satu lokasi utama',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Reward & Poin Aktif',
                'key'        => 'bank_sampah_reward_aktif',
                'value'      => 1,
                'keterangan' => '1 = poin diberikan otomatis dari setoran dan bisa ditukar',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Poin per Kg Setoran',
                'key'        => 'bank_sampah_poin_per_kg',
                'value'      => 10,
                'keterangan' => 'Jumlah poin yang didapat nasabah untuk setiap 1 kg setoran',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Minimal Poin untuk Tukar',
                'key'        => 'bank_sampah_minimal_poin_tukar',
                'value'      => 100,
                'keterangan' => 'Batas minimal poin sebelum nasabah boleh menukar poin',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Notifikasi WhatsApp Aktif',
                'key'        => 'bank_sampah_whatsapp_aktif',
                'value'      => 0,
                'keterangan' => '1 = kirim notifikasi WhatsApp ke nasabah saat transaksi (butuh API di bawah)',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'WhatsApp API URL',
                'key'        => 'bank_sampah_whatsapp_api_url',
                'value'      => '',
                'keterangan' => 'Endpoint gateway WhatsApp yang dipakai pengiriman notifikasi',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'WhatsApp API Key',
                'key'        => 'bank_sampah_whatsapp_api_key',
                'value'      => '',
                'keterangan' => 'Kredensial gateway WhatsApp — tidak boleh dibagikan',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'WhatsApp Nomor Pengirim',
                'key'        => 'bank_sampah_whatsapp_sender',
                'value'      => '',
                'keterangan' => 'Nomor pengirim yang tercatat pada notifikasi WhatsApp',
                'jenis'      => 'text',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Dashboard Nasabah Aktif',
                'key'        => 'bank_sampah_warga_aktif',
                'value'      => 1,
                'keterangan' => '1 = warga bisa membuka dashboard nasabah lewat Layanan Mandiri',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Dashboard Nasabah: Tampilkan Saldo',
                'key'        => 'bank_sampah_show_saldo',
                'value'      => 1,
                'keterangan' => '1 = saldo nasabah tampil di dashboard warga',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Dashboard Nasabah: Tampilkan Poin',
                'key'        => 'bank_sampah_show_poin',
                'value'      => 1,
                'keterangan' => '1 = poin nasabah tampil di dashboard warga',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Dashboard Nasabah: Tampilkan Riwayat',
                'key'        => 'bank_sampah_show_riwayat',
                'value'      => 1,
                'keterangan' => '1 = riwayat transaksi tampil di dashboard warga',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
            [
                'judul'      => 'Dashboard Nasabah: Jumlah Riwayat',
                'key'        => 'bank_sampah_max_riwayat',
                'value'      => 20,
                'keterangan' => 'Jumlah maksimal riwayat transaksi yang tampil di dashboard warga',
                'jenis'      => 'input-number',
                'kategori'   => 'Bank Sampah',
            ],
        ]);
    }
}
