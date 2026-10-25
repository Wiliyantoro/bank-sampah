@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1><i class="fa fa-gift"></i> Reward & Poin</h1>
@endsection

@section('breadcrumb')
    <li class="active">Reward & Poin</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    <div class="row">
        <div class="col-md-4">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TOTAL POIN NASABAH</span>
                    <span class="info-box-number">{{ number_format($totalPoin) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Daftar Nasabah Berdasarkan Poin</h3>
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
                            <th class="padat">SALDO</th>
                            <th class="padat">POIN</th>
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
                order: [[5, 'desc']],
                ajax: {
                    url: "{{ url('bank-sampah/reward') }}",
                    method: 'POST',
                },
                columns: [
                    { data: 'DT_RowIndex', class: 'padat', searchable: false, orderable: false },
                    { data: 'aksi', class: 'aksi', searchable: false, orderable: false },
                    { data: 'no_member', name: 'no_member', class: 'padat' },
                    { data: 'nama', name: 'nama' },
                    { data: 'saldo', name: 'saldo', class: 'padat', searchable: false, orderable: false,
                      render: function(data) { return 'Rp ' + Number(data).toLocaleString('id-ID'); } },
                    { data: 'poin', name: 'poin', class: 'padat' },
                ],
            });
        });
    </script>
@endpush
