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
 * Validasi tukar poin (issue #65).
 */
class TukarPoinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'poin'       => 'required|integer|min:1',
            'keterangan' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'poin.required'       => 'Jumlah poin wajib diisi.',
            'keterangan.required' => 'Keterangan wajib diisi.',
        ];
    }
}
