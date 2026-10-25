<?php

/*
 *
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

namespace Modules\BankSampah\Libraries;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\BankSampah\Models\MemberModel;
use Modules\BankSampah\Models\TransaksiModel;

class WhatsAppNotification
{
    protected array $config;

    public function __construct()
    {
        $this->config = [
            'enabled' => setting('bank_sampah_whatsapp_aktif') == 1,
            'api_url' => (string) setting('bank_sampah_whatsapp_api_url'),
            'api_key' => (string) setting('bank_sampah_whatsapp_api_key'),
            'sender'  => (string) setting('bank_sampah_whatsapp_sender'),
        ];
    }

    /**
     * Kirim notifikasi transaksi setor
     */
    public function kirimNotifikasiSetor(MemberModel $member, TransaksiModel $transaksi): bool
    {
        if (!$this->config['enabled'] || empty($member->no_hp)) {
            return false;
        }

        $pesan = "🏦 *" . setting('bank_sampah_nama', 'Bank Sampah') . "*\n\n"
            . "Halo *{$member->nama}*! 👋\n\n"
            . "Transaksi *SETOR* berhasil:\n"
            . "📋 No: {$transaksi->no_transaksi}\n"
            . "💰 Total: Rp " . number_format($transaksi->total, 0, ',', '.') . "\n"
            . "⭐ Poin: +{$transaksi->poin}\n"
            . "💳 Saldo: Rp " . number_format($member->saldo, 0, ',', '.') . "\n\n"
            . "Terima kasih sudah menjaga lingkungan! 🌱\n"
            . "Salam hijau! 💚";

        return $this->kirim($member->no_hp, $pesan);
    }

    /**
     * Kirim notifikasi transaksi tarik
     */
    public function kirimNotifikasiTarik(MemberModel $member, TransaksiModel $transaksi): bool
    {
        if (!$this->config['enabled'] || empty($member->no_hp)) {
            return false;
        }

        $pesan = "🏦 *" . setting('bank_sampah_nama', 'Bank Sampah') . "*\n\n"
            . "Halo *{$member->nama}*! 👋\n\n"
            . "Transaksi *TARIK* berhasil:\n"
            . "📋 No: {$transaksi->no_transaksi}\n"
            . "💸 Nominal: Rp " . number_format($transaksi->total, 0, ',', '.') . "\n"
            . "💳 Sisa Saldo: Rp " . number_format($member->saldo, 0, ',', '.') . "\n\n"
            . "Semoga bermanfaat! 🙏";

        return $this->kirim($member->no_hp, $pesan);
    }

    /**
     * Kirim notifikasi poin ditukar
     */
    public function kirimNotifikasiTukarPoin(MemberModel $member, int $poin, string $keterangan): bool
    {
        if (!$this->config['enabled'] || empty($member->no_hp)) {
            return false;
        }

        $pesan = "🏦 *" . setting('bank_sampah_nama', 'Bank Sampah') . "*\n\n"
            . "Halo *{$member->nama}*! 👋\n\n"
            . "Poin berhasil ditukar:\n"
            . "⭐ Poin ditukar: {$poin}\n"
            . "📝 Keterangan: {$keterangan}\n"
            . "💳 Sisa Poin: {$member->poin}\n\n"
            . "Terima kasih! 🎁";

        return $this->kirim($member->no_hp, $pesan);
    }

    /**
     * Kirim pesan WhatsApp via API
     */
    protected function kirim(string $nomor, string $pesan): bool
    {
        try {
            // Format nomor HP (hilangkan 0 di awal, tambah 62)
            $nomor = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $nomor));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Content-Type'  => 'application/json',
            ])
            // BS-040: timeout eksplisit — dulu default 30 detik menahan
            // respons proses setor/tarik penuh kalau API WA lambat/mati
            ->timeout(10)
            ->post($this->config['api_url'], [
                'phone'   => $nomor,
                'message' => $pesan,
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp notification sent to {$nomor}");
                return true;
            }

            Log::error("WhatsApp notification failed", [
                'phone' => $nomor,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error("WhatsApp notification error", [
                'phone' => $nomor,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
