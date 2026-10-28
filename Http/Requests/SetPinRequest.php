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
 * Validasi PIN Layanan Mandiri warga (D-001) (D-004 — PRD aturan/optimasi-keamanan-controller.md).
 *
 * Dipanggil manual dari controller (`createFrom()` + `validate()`)
 * karena router hybrid inti (Luthier/CI3) tidak melakukan dependency
 * injection FormRequest ke argumen method controller.
 */
class SetPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string', 'regex:/^[0-9]{4,6}$/'],
        ];
    }
}
