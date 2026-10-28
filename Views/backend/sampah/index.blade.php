{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1>Jenis Sampah</h1>
@endsection

@section('breadcrumb')
    <li class="active">Jenis Sampah</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    <div class="box box-info">
        <div class="box-header with-border">
            @if (can('u'))
                <a href="{{ url('bank-sampah/jenis-sampah/form') }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-plus"></i> Tambah Jenis Sampah</a>
            @endif
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabeldata">
                    <thead>
                        <tr>
                            <th class="padat">NO</th>
                            <th class="padat">AKSI</th>
                            <th>KATEGORI</th>
                            <th>NAMA JENIS</th>
                            <th class="padat">SATUAN</th>
                            <th class="padat">HARGA BELI</th>
                            <th class="padat">HARGA JUAL</th>
                            <th class="padat">STOK</th>
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
                    url: "{{ url('bank-sampah/jenis-sampah') }}",
                    method: 'POST',
                },
                columns: [
                    { data: 'DT_RowIndex', class: 'padat', searchable: false, orderable: false },
                    { data: 'aksi', class: 'aksi', searchable: false, orderable: false },
                    { data: 'kategori.nama', name: 'kategori.nama', searchable: false, orderable: false, defaultContent: '-' },
                    { data: 'nama', name: 'nama' },
                    { data: 'satuan', name: 'satuan', class: 'padat', searchable: false },
                    { data: 'harga_beli', name: 'harga_beli', class: 'padat', searchable: false, orderable: false,
                      render: function(data) { return 'Rp ' + Number(data).toLocaleString('id-ID'); } },
                    { data: 'harga_jual', name: 'harga_jual', class: 'padat', searchable: false, orderable: false,
                      render: function(data) { return 'Rp ' + Number(data).toLocaleString('id-ID'); } },
                    { data: 'stok', name: 'stok', class: 'padat', searchable: false, orderable: false,
                      render: function(data) { return Number(data).toLocaleString('id-ID'); } },
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
