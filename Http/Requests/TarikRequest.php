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
 * Validasi transaksi tarik saldo (issue #65).
 */
class TarikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|integer',
            'nominal'   => 'required|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Nasabah wajib dipilih.',
            'nominal.required'   => 'Nominal wajib diisi.',
            'nominal.min'        => 'Nominal minimal Rp 1.',
        ];
    }
}
