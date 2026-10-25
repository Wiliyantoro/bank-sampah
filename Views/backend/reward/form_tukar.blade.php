@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-exchange"></i> Tukar Poin</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Data Nasabah</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">No Member</th>
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
                        <tr>
                            <th>Minimal Tukar</th>
                            <td>{{ number_format($config['reward']['minimal_poin_tukar'] ?? 100) }} poin</td>
                        </tr>
                    </table>
                </div>
            </div>

            <form action="{{ url('bank-sampah/reward/proses-tukar', $member->id) }}" method="POST">
                @csrf

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Form Tukar Poin</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>Jumlah Poin yang Ditukar <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="poin"
                                min="{{ $config['reward']['minimal_poin_tukar'] ?? 100 }}"
                                max="{{ $member->poin }}"
                                required placeholder="Masukkan jumlah poin">
                            <small class="text-muted">Maksimal: {{ number_format($member->poin) }} poin</small>
                        </div>

                        <div class="form-group">
                            <label>Keterangan / Hadiah <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="keterangan" rows="3"
                                required placeholder="Contoh: Tukar 100 poin = Rp 10.000 voucher belanja">{{ old('keterangan') }}</textarea>
                        </div>

                        <div class="alert alert-info" style="margin-bottom: 0;">
                            <i class="fa fa-info-circle"></i>
                            Poin yang ditukar akan mengurangi total poin nasabah. Minimal {{ number_format($config['reward']['minimal_poin_tukar'] ?? 100) }} poin untuk menukar.
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ url('bank-sampah/reward') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-warning btn-lg pull-right">
                        <i class="fa fa-check"></i> Tukar Poin
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
