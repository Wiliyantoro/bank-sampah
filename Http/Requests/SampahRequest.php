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
 * Validasi jenis sampah (D-004 — PRD aturan/optimasi-keamanan-controller.md).
 *
 * Dipanggil manual dari controller (`createFrom()` + `validate()`)
 * karena router hybrid inti (Luthier/CI3) tidak melakukan dependency
 * injection FormRequest ke argumen method controller.
 */
class SampahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'        => 'required|string|max:150',
            // BS-034: kategori wajib ada & milik desa aktif — tanpa ini
            // id ngawur melempar QueryException FK (500) dan id milik
            // desa lain bisa lolos (referensi lintas desa)
            'kategori_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('bank_sampah_kategori', 'id')
                ->where('config_id', identitas('id'))],
            'satuan'      => 'required|string|max:20',
            'harga_beli'  => 'required|numeric|min:0',
            'harga_jual'  => 'nullable|numeric|min:0',
        ];
    }
}
