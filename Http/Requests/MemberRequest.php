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
 * Validasi nasabah (D-004 — PRD aturan/optimasi-keamanan-controller.md).
 *
 * Dipanggil manual dari controller (`createFrom()` + `validate()`)
 * karena router hybrid inti (Luthier/CI3) tidak melakukan dependency
 * injection FormRequest ke argumen method controller.
 */
class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_member'   => 'required|string|max:20|unique:bank_sampah_member,no_member,NULL,id,config_id,' . identitas('id'),
            'nama'        => 'required|string|max:150',
            'no_hp'       => 'nullable|string|max:20',
            'alamat'      => 'nullable|string',
            // BS-015: ID eksternal wajib ter-scope desa agar tidak bisa
            // dilink ke data penduduk/lokasi desa lain
            'penduduk_id' => 'nullable|integer|exists:tweb_penduduk,id,config_id,' . identitas('id'),
            'lokasi_id'   => 'nullable|integer|exists:bank_sampah_lokasi,id,config_id,' . identitas('id'),
        ];
    }

    /**
     * Rules untuk update: no_member tidak diubah saat edit.
     */
    public function updateRules(): array
    {
        $rules = $this->rules();
        unset($rules['no_member']);

        return $rules;
    }
}
