{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1>Riwayat Poin - {{ $member->nama }}</h1>
@endsection

@section('breadcrumb')
    <li>Reward & Poin</li>
    <li class="active">Riwayat Poin</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Info Nasabah</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="120">No Member</th>
                            <td><span class="label label-info">{{ $member->no_member }}</span></td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td><strong>{{ $member->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th>Poin Saat Ini</th>
                            <td><span class="label label-warning" style="font-size: 16px;">{{ number_format($member->poin) }} poin</span></td>
                        </tr>
                    </table>
                    <a href="{{ url('bank-sampah/reward') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali ke Reward
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Riwayat Transaksi Poin</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tabeldata">
                            <thead>
                                <tr>
                                    <th class="padat">NO</th>
                                    <th class="padat">TIPE</th>
                                    <th class="padat">POIN</th>
                                    <th>KETERANGAN</th>
                                    <th class="padat">TANGGAL</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#tabeldata').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                order: [[4, 'desc']],
                ajax: {
                    url: "{{ url('bank-sampah/reward/riwayat', $member->id) }}",
                    method: 'POST',
                },
                columns: [
                    { data: 'DT_RowIndex', class: 'padat', searchable: false, orderable: false },
                    { data: 'tipe', name: 'tipe', class: 'padat', searchable: false,
                      render: function(data) {
                          return data === 'add'
                              ? '<span class="label label-success">MASUK</span>'
                              : '<span class="label label-danger">TUKAR</span>';
                      } },
                    { data: 'poin', name: 'poin', class: 'padat', searchable: false,
                      render: function(data, type, row) {
                          var tanda = row.tipe === 'add' ? '+' : '-';
                          return '<span class="text-' + (row.tipe === 'add' ? 'success' : 'danger') + ' text-bold">' + tanda + data + '</span>';
                      } },
                    { data: 'keterangan', name: 'keterangan', searchable: false, defaultContent: '-' },
                    { data: 'created_at', name: 'created_at', class: 'padat', searchable: false,
                      render: function(data) { return data ? data.slice(0, 16) : '-'; } },
                ],
            });
        });
    </script>
@endpush
