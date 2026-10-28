<?php

/*
 * File ini bagian dari OpenSID - Modul Bank Sampah
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

namespace Modules\BankSampah\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi lokasi/TPS (D-004 — PRD aturan/optimasi-keamanan-controller.md).
 *
 * Dipanggil manual dari controller (`createFrom()` + `validate()`)
 * karena router hybrid inti (Luthier/CI3) tidak melakukan dependency
 * injection FormRequest ke argumen method controller.
 */
class LokasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'   => 'required|string|max:150',
            'kode'   => 'required|string|max:20|unique:bank_sampah_lokasi,kode,NULL,id,config_id,' . identitas('id'),
            'alamat' => 'nullable|string',
            // BS-040: batas panjang mengikuti kolom DB (penanggung_jawab 150,
            // no_hp 20) — tanpa ini MySQL strict melempar error 1406 (500)
            'penanggung_jawab' => 'nullable|string|max:150',
            'no_hp'            => 'nullable|string|max:20',
        ];
    }

    /**
     * Rules untuk update: kode unik diabaikan untuk id yang sedang diedit.
     */
    public function updateRules(int $id): array
    {
        $rules = $this->rules();
        $rules['kode'] = 'required|string|max:20|unique:bank_sampah_lokasi,kode,' . $id . ',id,config_id,' . identitas('id');

        return $rules;
    }
}
