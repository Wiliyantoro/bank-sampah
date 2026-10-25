@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1>
        <i class="fa fa-users"></i>
        {{ $action === 'tambah' ? 'Tambah Nasabah' : 'Ubah Nasabah' }}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form {{ $action === 'tambah' ? 'Tambah' : 'Ubah' }} Nasabah</h3>
                </div>

                <form action="{{ $action === 'tambah' ? url('bank-sampah/member/simpan') : url('bank-sampah/member/update', $member->id) }}" method="POST" id="formNasabah">
                    @csrf

                    <div class="box-body">

                        {{-- Mode Selection: Pilih Sumber Data --}}
                        @if($action === 'tambah')
                        <div class="form-group">
                            <label class="control-label"><i class="fa fa-database"></i> Sumber Data Nasabah</label>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="sumber_data" id="sumber_penduduk" value="penduduk" checked>
                                    <strong><i class="fa fa-search"></i> Pilih dari Data Penduduk Desa</strong>
                                    <br><small class="text-muted">Cari berdasarkan NIK atau nama dari database kependudukan desa</small>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="sumber_data" id="sumber_manual" value="manual">
                                    <strong><i class="fa fa-keyboard-o"></i> Input Manual</strong>
                                    <br><small class="text-muted">Isi data nasabah secara manual tanpa link ke data penduduk</small>
                                </label>
                            </div>
                        </div>
                        @endif

                        {{-- === MODE: CARI DARI PENDUDUK === --}}
                        <div id="mode-penduduk" class="sumber-mode" @if($action === 'ubah') style="display:none;" @endif>
                            <div class="form-group">
                                <label for="search_penduduk">Cari Data Penduduk <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" id="search_penduduk"
                                        placeholder="Ketik NIK atau nama penduduk..." autocomplete="off">
                                </div>
                                <div id="hasil_penduduk" class="list-group" style="max-height: 250px; overflow-y: auto; display: none;"></div>
                                <small class="text-muted">Ketik minimal 2 karakter untuk mulai mencari</small>
                            </div>

                            {{-- Info Penduduk Terpilih --}}
                            <div id="info_penduduk" class="callout callout-info" style="display: none;">
                                <h4><i class="fa fa-check-circle"></i> Data Penduduk Terpilih</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>NIK:</strong> <span id="info_nik">-</span><br>
                                        <strong>Nama:</strong> <span id="info_nama">-</span><br>
                                        <strong>Jenis Kelamin:</strong> <span id="info_jk">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Alamat:</strong> <span id="info_alamat">-</span><br>
                                        <strong>Telepon:</strong> <span id="info_telepon">-</span>
                                    </div>
                                </div>
                                <input type="hidden" name="penduduk_id" id="penduduk_id" value="{{ old('penduduk_id', $member->penduduk_id ?? '') }}">
                            </div>
                        </div>

                        {{-- === MODE: INPUT MANUAL === --}}
                        <div id="mode-manual" class="sumber-mode" style="display: none;">
                            <div class="callout callout-warning">
                                <p><i class="fa fa-info-circle"></i> Mode input manual — data tidak akan terhubung dengan database kependudukan desa.</p>
                            </div>
                            <input type="hidden" name="penduduk_id" id="penduduk_id_manual" value="">
                        </div>

                        <hr>

                        {{-- No Member --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_member">No Member <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="no_member" name="no_member"
                                        value="{{ old('no_member', $noMember) }}" required
                                        {{ $action === 'ubah' ? 'readonly' : '' }}
                                        placeholder="Auto generate">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lokasi_id">Lokasi / TPS</label>
                                    <select class="form-control select2" id="lokasi_id" name="lokasi_id">
                                        <option value="">-- Pilih Lokasi --</option>
                                        @foreach($lokasiList as $l)
                                            <option value="{{ $l->id }}" {{ old('lokasi_id', $member->lokasi_id ?? '') == $l->id ? 'selected' : '' }}>
                                                {{ $l->kode }} - {{ $l->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Nama --}}
                        <div class="form-group">
                            <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                value="{{ old('nama', $member->nama ?? '') }}" required
                                placeholder="Nama lengkap nasabah">
                        </div>

                        {{-- HP & Alamat --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_hp">No HP / WhatsApp</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp"
                                        value="{{ old('no_hp', $member->no_hp ?? '') }}"
                                        placeholder="08xxxxxxxxxx">
                                    <small class="text-muted">Untuk notifikasi WhatsApp</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="2"
                                        placeholder="Alamat lengkap nasabah">{{ old('alamat', $member->alamat ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        @if($action === 'ubah')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Saldo Saat Ini</label>
                                    <p class="form-control-static text-bold text-success" style="font-size: 18px;">
                                        {{ bank_sampah_format_rupiah($member->saldo) }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Poin Saat Ini</label>
                                    <p class="form-control-static text-bold text-warning" style="font-size: 18px;">
                                        {{ $member->poin }} poin
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="aktif">Status</label>
                            <select class="form-control" id="aktif" name="aktif">
                                <option value="1" {{ old('aktif', $member->aktif ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('aktif', $member->aktif ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ url('bank-sampah/member') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary pull-right" id="btnSimpan">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar Info --}}
        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Panduan</h3>
                </div>
                <div class="box-body">
                    <div id="panduan_penduduk">
                        <h5><i class="fa fa-database text-aqua"></i> Pilih dari Data Penduduk</h5>
                        <ol>
                            <li>Ketik NIK atau nama di kolom pencarian</li>
                            <li>Pilih penduduk dari hasil pencarian</li>
                            <li>Data otomatis terisi (nama, alamat, telepon)</li>
                            <li>Simpan nasabah</li>
                        </ol>
                        <p class="text-muted"><small>Nasabah akan terhubung dengan data kependudukan desa.</small></p>
                    </div>
                    <div id="panduan_manual" style="display: none;">
                        <h5><i class="fa fa-keyboard-o text-yellow"></i> Input Manual</h5>
                        <ol>
                            <li>Isi semua field yang diperlukan</li>
                            <li>No member auto-generate</li>
                            <li>Simpan nasabah</li>
                        </ol>
                        <p class="text-muted"><small>Data tidak terhubung dengan database kependudukan.</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var searchTimer = null;
    var selectedPenduduk = null;

    // === Mode Toggle ===
    $('input[name="sumber_data"]').on('change', function() {
        var mode = $(this).val();
        $('.sumber-mode').hide();

        if (mode === 'penduduk') {
            $('#mode-penduduk').show();
            $('#panduan_penduduk').show();
            $('#panduan_manual').hide();
            $('#penduduk_id_manual').val('');
        } else {
            $('#mode-manual').show();
            $('#panduan_penduduk').hide();
            $('#panduan_manual').show();
            // Clear penduduk selection
            $('#penduduk_id').val('');
            $('#info_penduduk').hide();
            $('#hasil_penduduk').hide();
            selectedPenduduk = null;
        }
    });

    // === Search Penduduk ===
    $('#search_penduduk').on('keyup', function() {
        var q = $(this).val().trim();
        clearTimeout(searchTimer);

        if (q.length < 2) {
            $('#hasil_penduduk').hide();
            return;
        }

        searchTimer = setTimeout(function() {
            $.ajax({
                url: '{{ $ajaxPendudukUrl ?? url("bank-sampah/ajax/penduduk") }}',
                type: 'GET',
                data: { q: q, limit: 15 },
                dataType: 'json',
                beforeSend: function() {
                    $('#hasil_penduduk').html('<a class="list-group-item"><i class="fa fa-spinner fa-spin"></i> Mencari...</a>').show();
                },
                success: function(response) {
                    var html = '';
                    if (response.results && response.results.length > 0) {
                        response.results.forEach(function(item) {
                            html += '<a href="#" class="list-group-item penduduk-item" ' +
                                'data-id="' + item.id + '" ' +
                                'data-nama="' + escapeHtml(item.nama) + '" ' +
                                'data-nik="' + escapeHtml(item.nik) + '" ' +
                                'data-alamat="' + escapeHtml(item.alamat) + '" ' +
                                'data-telepon="' + escapeHtml(item.telepon) + '" ' +
                                'data-jk="' + escapeHtml(item.jk) + '">' +
                                '<strong>' + escapeHtml(item.nik) + '</strong> &mdash; ' +
                                escapeHtml(item.nama) +
                                ' <span class="pull-right badge bg-blue">' + escapeHtml(item.jk) + '</span>' +
                                '<br><small class="text-muted">' + escapeHtml(item.alamat) + '</small>' +
                                '</a>';
                        });
                        html += '<a class="list-group-item text-center text-muted"><small>' + response.count + ' data ditemukan</small></a>';
                    } else {
                        html = '<a class="list-group-item text-center text-muted"><i class="fa fa-search"></i> Tidak ada data ditemukan untuk "' + escapeHtml(q) + '"</a>';
                    }
                    $('#hasil_penduduk').html(html).show();
                },
                error: function() {
                    $('#hasil_penduduk').html('<a class="list-group-item text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Gagal mencari data</a>').show();
                }
            });
        }, 300); // Debounce 300ms
    });

    // === Select Penduduk ===
    $(document).on('click', '.penduduk-item', function(e) {
        e.preventDefault();

        var $item = $(this);
        selectedPenduduk = {
            id: $item.data('id'),
            nama: $item.data('nama'),
            nik: $item.data('nik'),
            alamat: $item.data('alamat'),
            telepon: $item.data('telepon'),
            jk: $item.data('jk')
        };

        // Fill form fields
        $('#penduduk_id').val(selectedPenduduk.id);
        $('#nama').val(selectedPenduduk.nama);
        $('#no_hp').val(selectedPenduduk.telepon);
        $('#alamat').val(selectedPenduduk.alamat);

        // Show info panel
        $('#info_nik').text(selectedPenduduk.nik);
        $('#info_nama').text(selectedPenduduk.nama);
        $('#info_jk').text(selectedPenduduk.jk);
        $('#info_alamat').text(selectedPenduduk.alamat || '-');
        $('#info_telepon').text(selectedPenduduk.telepon || '-');
        $('#info_penduduk').fadeIn();

        // Hide search results
        $('#hasil_penduduk').hide();
        $('#search_penduduk').val('');

        // Highlight selected
        $('.penduduk-item').removeClass('active');
        $item.addClass('active');
    });

    // === Helper ===
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
});
</script>
@endpush
