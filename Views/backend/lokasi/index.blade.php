{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1><i class="fa fa-map-marker"></i> Lokasi / TPS</h1>
@endsection

@section('breadcrumb')
    <li class="active">Lokasi / TPS</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    <div class="box box-info">
        <div class="box-header with-border">
            @if (can('u'))
                <a href="{{ url('bank-sampah/lokasi/form') }}" class="btn btn-social btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Lokasi</a>
            @endif
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabeldata">
                    <thead>
                        <tr>
                            <th class="padat">NO</th>
                            <th class="padat">AKSI</th>
                            <th>NAMA LOKASI</th>
                            <th class="padat">KODE</th>
                            <th>PENANGGUNG JAWAB</th>
                            <th class="padat">NO HP</th>
                            <th class="padat">NASABAH</th>
                            <th class="padat">TRANSAKSI</th>
                            <th class="padat">SUMBER</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var TableData = $('#tabeldata').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('bank-sampah/lokasi') }}",
                    method: 'POST',
                },
                columns: [
                    { data: 'DT_RowIndex', class: 'padat', searchable: false, orderable: false },
                    { data: 'aksi', class: 'aksi', searchable: false, orderable: false },
                    { data: 'nama', name: 'nama' },
                    { data: 'kode', name: 'kode', class: 'padat' },
                    { data: 'penanggung_jawab', name: 'penanggung_jawab', searchable: false, defaultContent: '-' },
                    { data: 'no_hp', name: 'no_hp', class: 'padat', searchable: false, defaultContent: '-' },
                    { data: 'member_count', name: 'member_count', class: 'padat', searchable: false, orderable: false },
                    { data: 'transaksi_count', name: 'transaksi_count', class: 'padat', searchable: false, orderable: false },
                    { data: 'is_default', name: 'is_default', class: 'padat', searchable: false, orderable: false },
                ],
            });

            $(document).on('click', '.btn-hapus', function(e) {
                e.preventDefault();
                var url = $(this).data('href');
                if (! confirm('Yakin hapus data ini?')) return;
                var data = {};
                data[csrfParam] = getCsrfToken();
                $.post(url, data).done(function() {
                    TableData.ajax.reload();
                }).fail(function() {
                    alert('Gagal menghapus data');
                });
            });
        });
    </script>
@endpush
