{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1><i class="fa fa-users"></i> Nasabah / Member</h1>
@endsection

@section('breadcrumb')
    <li class="active">Nasabah</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    <div class="box box-info">
        <div class="box-header with-border">
            @if (can('u'))
                <a href="{{ url('bank-sampah/member/form') }}" class="btn btn-social btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Nasabah</a>
            @endif
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabeldata">
                    <thead>
                        <tr>
                            <th class="padat">NO</th>
                            <th class="padat">AKSI</th>
                            <th class="padat">NO MEMBER</th>
                            <th>NAMA</th>
                            <th class="padat">NO HP</th>
                            <th>LOKASI</th>
                            <th class="padat">SALDO</th>
                            <th class="padat">POIN</th>
                            <th class="padat">STATUS</th>
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
                    url: "{{ url('bank-sampah/member') }}",
                    method: 'POST',
                },
                columns: [
                    { data: 'DT_RowIndex', class: 'padat', searchable: false, orderable: false },
                    { data: 'aksi', class: 'aksi', searchable: false, orderable: false },
                    { data: 'no_member', name: 'no_member', class: 'padat' },
                    { data: 'nama', name: 'nama' },
                    { data: 'no_hp', name: 'no_hp', class: 'padat', searchable: false, defaultContent: '-' },
                    { data: 'lokasi.nama', name: 'lokasi.nama', searchable: false, orderable: false, defaultContent: '-' },
                    { data: 'saldo', name: 'saldo', class: 'padat', searchable: false, orderable: false,
                      render: function(data) { return 'Rp ' + Number(data).toLocaleString('id-ID'); } },
                    { data: 'poin', name: 'poin', class: 'padat', searchable: false, orderable: false },
                    { data: 'aktif', name: 'aktif', class: 'padat', searchable: false, orderable: false,
                      render: function(data) {
                          return data == 1
                              ? '<span class="label label-success">Aktif</span>'
                              : '<span class="label label-danger">Nonaktif</span>';
                      } },
                ],
            });

            $(document).on('click', '.btn-hapus', function(e) {
                e.preventDefault();
                var url = $(this).data('href');
                if (! confirm('Yakin hapus nasabah ini?')) return;
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
