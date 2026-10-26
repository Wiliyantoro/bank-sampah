{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1>
        <i class="fa fa-map-marker"></i>
        {{ $lokasi ? 'Ubah Lokasi / TPS' : 'Tambah Lokasi / TPS' }}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form {{ $lokasi ? 'Ubah' : 'Tambah' }} Lokasi</h3>
                </div>

                {!! form_open($form_action, '') !!}

                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kode">Kode Lokasi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kode" name="kode"
                                        value="{{ old('kode', $lokasi->kode ?? '') }}" required
                                        placeholder="Contoh: TPS-01">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nama">Nama Lokasi / TPS <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        value="{{ old('nama', $lokasi->nama ?? '') }}" required
                                        placeholder="Contoh: TPS Dusun Utara">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2"
                                placeholder="Alamat lokasi TPS">{{ old('alamat', $lokasi->alamat ?? '') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="penanggung_jawab">Penanggung Jawab</label>
                                    <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab"
                                        value="{{ old('penanggung_jawab', $lokasi->penanggung_jawab ?? '') }}"
                                        placeholder="Nama PJ">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_hp">No HP PJ</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp"
                                        value="{{ old('no_hp', $lokasi->no_hp ?? '') }}"
                                        placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="aktif">Status</label>
                            <select class="form-control" id="aktif" name="aktif">
                                <option value="1" {{ old('aktif', $lokasi->aktif ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('aktif', $lokasi->aktif ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ url('bank-sampah/lokasi') }}" class="btn btn-default">
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
