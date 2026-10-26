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
        <i class="fa fa-tags"></i>
        {{ $kategori ? 'Ubah Kategori' : 'Tambah Kategori' }}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form {{ $kategori ? 'Ubah' : 'Tambah' }} Kategori</h3>
                </div>

                {!! form_open($form_action, '') !!}

                    <div class="box-body">
                        <div class="form-group">
                            <label for="nama">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                value="{{ old('nama', $kategori->nama ?? '') }}" required
                                placeholder="Contoh: Organik, Anorganik, B3">
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                placeholder="Deskripsi kategori sampah...">{{ old('keterangan', $kategori->keterangan ?? '') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="aktif">Status</label>
                            <select class="form-control" id="aktif" name="aktif">
                                <option value="1" {{ old('aktif', $kategori->aktif ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('aktif', $kategori->aktif ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ url('bank-sampah/kategori-sampah') }}" class="btn btn-default">
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
