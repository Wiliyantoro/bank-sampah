@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-history"></i> Riwayat Poin - {{ $member->nama }}</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6">
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
                            <td>
                                <span class="label label-warning" style="font-size: 16px;">⭐ {{ number_format($member->poin) }} poin</span>
                            </td>
                        </tr>
                    </table>

                    <a href="{{ url('bank-sampah/reward') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali ke Reward
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Riwayat Transaksi Poin</h3>
                </div>
                <div class="box-body">
                    @forelse($riwayat as $r)
                    <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                @if($r->tipe === 'add')
                                    <span class="label label-success">MASUK</span>
                                @else
                                    <span class="label label-danger">TUKAR</span>
                                @endif
                                <span style="margin-left: 5px; font-size: 12px; color: #999;">
                                    {{ $r->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-{{ $r->tipe === 'add' ? 'success' : 'danger' }} text-bold" style="font-size: 16px;">
                                    {{ $r->tipe === 'add' ? '+' : '-' }}{{ number_format($r->poin) }} poin
                                </span>
                            </div>
                        </div>
                        <div style="margin-top: 5px; font-size: 12px; color: #666;">
                            {{ $r->keterangan ?? '-' }}
                            @if($r->transaksi_id)
                                | Transaksi #{{ $r->transaksi_id }}
                            @endif
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 30px; color: #999;">
                        <i class="fa fa-inbox" style="font-size: 40px;"></i>
                        <p>Belum ada riwayat poin</p>
                    </div>
                    @endforelse

                    <div class="text-center" style="margin-top: 15px;">
                        {{ $riwayat->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
