{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1><i class="fa fa-exchange"></i> Setor / Tarik Saldo</h1>
@endsection

@section('breadcrumb')
    <li class="active">Transaksi</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    <div class="box box-info">
        <div class="box-header with-border">
            @if (can('u'))
                <a href="{{ url('bank-sampah/transaksi/setor') }}" class="btn btn-social btn-success btn-sm"><i class="fa fa-plus-circle"></i> Setor Sampah</a>
                <a href="{{ url('bank-sampah/transaksi/tarik') }}" class="btn btn-social btn-danger btn-sm"><i class="fa fa-minus-circle"></i> Tarik Saldo</a>
            @endif
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabeldata">
                    <thead>
                        <tr>
                            <th class="padat">NO</th>
                            <th class="padat">AKSI</th>
                            <th class="padat">NO TRANSAKSI</th>
                            <th>NASABAH</th>
                            <th class="padat">TIPE</th>
                            <th class="padat">TOTAL</th>
                            <th class="padat">POIN</th>
                            <th class="padat">TANGGAL</th>
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
                order: [[7, 'desc']],
                ajax: {
                    url: "{{ url('bank-sampah/transaksi') }}",
                    method: 'POST',
                },
                columns: [
                    { data: 'DT_RowIndex', class: 'padat', searchable: false, orderable: false },
                    { data: 'aksi', class: 'aksi', searchable: false, orderable: false },
                    { data: 'no_transaksi', name: 'no_transaksi', class: 'padat' },
                    { data: 'member.nama', name: 'member.nama', searchable: false, orderable: false, defaultContent: '-' },
                    { data: 'tipe', name: 'tipe', class: 'padat', searchable: false,
                      render: function(data) {
                          return data === 'setor'
                              ? '<span class="label label-success">SETOR</span>'
                              : '<span class="label label-danger">TARIK</span>';
                      } },
                    { data: 'total', name: 'total', class: 'padat', searchable: false, orderable: false,
                      render: function(data, type, row) {
                          var tanda = row.tipe === 'setor' ? '+' : '-';
                          return tanda + 'Rp ' + Number(data).toLocaleString('id-ID');
                      } },
                    { data: 'poin', name: 'poin', class: 'padat', searchable: false, orderable: false,
                      render: function(data) { return data > 0 ? '+' + data : '-'; } },
                    { data: 'created_at', name: 'created_at', class: 'padat', searchable: false,
                      render: function(data) { return data ? data.replace(' ', ' ').slice(0, 16) : '-'; } },
                ],
            });
        });
    </script>
@endpush
