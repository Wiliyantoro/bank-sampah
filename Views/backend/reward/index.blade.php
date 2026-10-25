@extends('admin::layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-gift"></i> Reward & Poin</h1>
</section>

<section class="content">
    {{-- Ringkasan --}}
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
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Daftar Nasabah Berdasarkan Poin</h3>
                    <div class="box-tools pull-right">
                        <form action="{{ url('bank-sampah/reward') }}" method="GET" class="form-inline">
                            <input type="text" name="cari" class="form-control input-sm" placeholder="Cari nama/no member..." value="{{ request('cari') }}">
                            <label style="margin-left: 10px;">
                                <input type="checkbox" name="ada_poin" value="1" {{ request('ada_poin') == '1' ? 'checked' : '' }}> Yang ada poin saja
                            </label>
                            <button type="submit" class="btn btn-primary btn-sm" style="margin-left: 5px;"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>No Member</th>
                                <th>Nama</th>
                                <th class="text-center">Poin</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($member as $item)
                            <tr>
                                <td>{{ ($member->currentPage() - 1) * $member->perPage() + $loop->iteration }}</td>
                                <td><span class="label label-info">{{ $item->no_member }}</span></td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-center">
                                    <span class="label label-warning" style="font-size: 14px;">⭐ {{ number_format($item->poin) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item->poin >= setting("bank_sampah_minimal_poin_tukar", 100))
                                        <a href="{{ url('bank-sampah/reward/tukar', $item->id) }}" class="btn btn-success btn-xs">
                                            <i class="fa fa-exchange"></i> Tukar Poin
                                        </a>
                                    @endif
                                    <a href="{{ url('bank-sampah/reward/riwayat', $item->id) }}" class="btn btn-info btn-xs">
                                        <i class="fa fa-history"></i> Riwayat
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="box-footer text-center">
                    {{ $member->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Reward Terakhir --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Reward Terakhir</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nasabah</th>
                                <th>Tipe</th>
                                <th class="text-center">Poin</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatReward as $r)
                            <tr>
                                <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $r->member->nama ?? '-' }}</td>
                                <td>
                                    @if($r->tipe === 'add')
                                        <span class="label label-success">MASUK</span>
                                    @else
                                        <span class="label label-danger">TUKAR</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="text-{{ $r->tipe === 'add' ? 'success' : 'danger' }} text-bold">
                                        {{ $r->tipe === 'add' ? '+' : '-' }}{{ $r->poin }}
                                    </span>
                                </td>
                                <td>{{ $r->keterangan ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada riwayat reward</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
