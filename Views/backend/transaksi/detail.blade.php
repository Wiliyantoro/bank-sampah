{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-file-text-o"></i> Detail Transaksi</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Transaksi #{{ $transaksi->no_transaksi }}
                        {!! bank_sampah_tipe_transaksi_label($transaksi->tipe) !!}
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ url('bank-sampah/transaksi/struk', $transaksi->id) }}" class="btn btn-default btn-sm" target="_blank">
                            <i class="fa fa-print"></i> Cetak Struk
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr><th width="150">No Transaksi</th><td>{{ $transaksi->no_transaksi }}</td></tr>
                                <tr><th>Tanggal</th><td>{{ $transaksi->created_at->format('d/m/Y H:i') }}</td></tr>
                                <tr><th>Tipe</th><td>{!! bank_sampah_tipe_transaksi_label($transaksi->tipe) !!}</td></tr>
                                @if($transaksi->lokasi)
                                <tr><th>Lokasi/TPS</th><td>{{ $transaksi->lokasi->nama }}</td></tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr><th width="150">Nasabah</th><td>{{ $transaksi->member->nama ?? '-' }}</td></tr>
                                <tr><th>No Member</th><td>{{ $transaksi->member->no_member ?? '-' }}</td></tr>
                                <tr><th>Total</th><td class="text-bold text-lg">{{ bank_sampah_format_rupiah($transaksi->total) }}</td></tr>
                                @if($transaksi->tipe === 'setor' && $transaksi->poin > 0)
                                <tr><th>Poin Didapat</th><td class="text-success">+{{ $transaksi->poin }} poin</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($transaksi->tipe === 'setor' && $transaksi->detail->count() > 0)
                    <h4>Detail Setoran:</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis Sampah</th>
                                <th>Kategori</th>
                                <th>Berat</th>
                                <th>Harga/kg</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaksi->detail as $d)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $d->sampah->nama ?? '-' }}</td>
                                <td>{{ $d->sampah->kategori->nama ?? '-' }}</td>
                                <td>{{ bank_sampah_format_berat($d->berat) }}</td>
                                <td>{{ bank_sampah_format_rupiah($d->harga) }}</td>
                                <td class="text-bold">{{ bank_sampah_format_rupiah($d->subtotal) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                    @if($transaksi->keterangan)
                    <p><strong>Keterangan:</strong> {{ $transaksi->keterangan }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
