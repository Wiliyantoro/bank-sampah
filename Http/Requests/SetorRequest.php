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
 * Validasi transaksi setor (issue #65).
 *
 * Dipanggil manual dari controller (`createFrom()` + `validate()`)
 * karena router hybrid inti (Luthier/CI3) tidak melakukan dependency
 * injection FormRequest ke argumen method controller.
 */
class SetorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id'        => 'required|integer',
            // BS-016: lokasi/TPS wajib milik desa yang sedang login
            'lokasi_id'        => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('bank_sampah_lokasi', 'id')
                ->where('config_id', identitas('id'))],
            'item'             => 'required|array|min:1',
            'item.*.sampah_id' => 'required|integer',
            'item.*.berat'     => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required'        => 'Nasabah wajib dipilih.',
            'item.required'             => 'Minimal satu jenis sampah harus diisi.',
            'item.*.sampah_id.required' => 'Jenis sampah wajib dipilih.',
            'item.*.berat.required'     => 'Berat wajib diisi.',
            'item.*.berat.min'          => 'Berat minimal 0.01 kg.',
        ];
    }

    /**
     * Data item siap proses: hanya kolom yang dipakai Action.
     */
    public function items(): array
    {
        return array_map(static fn (array $item): array => [
            'sampah_id' => (int) $item['sampah_id'],
            'berat'     => (float) $item['berat'],
        ], (array) $this->input('item', []));
    }
}
