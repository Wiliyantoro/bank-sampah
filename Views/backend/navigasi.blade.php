{{-- File ini bagian dari OpenSID - Modul Bank Sampah
 | @package   OpenSID
 | @author    Tim Pengembang OpenDesa
 | @copyright Hak Cipta 2016 - 2026 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 | @license   http://www.gnu.org/licenses/gpl.html GPL V3
 --}}

{{-- Dashboard Cards --}}
<div class="row">
    <a href="{{ url('bank-sampah/member') }}">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">NASABAH AKTIF</span>
                    <span class="info-box-number">{{ $totalMember }}</span>
                </div>
            </div>
        </div>
    </a>

    <a href="{{ url('bank-sampah/transaksi') }}">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-exchange"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TRANSAKSI HARI INI</span>
                    <span class="info-box-number">{{ $transaksiHariIni }}</span>
                </div>
            </div>
        </div>
    </a>

    <a href="{{ url('bank-sampah/laporan') }}">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">SETORAN BULAN INI</span>
                    <span class="info-box-number">{{ bank_sampah_format_rupiah($setoranBulan) }}</span>
                </div>
            </div>
        </div>
    </a>

    <a href="{{ url('bank-sampah/reward') }}">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-gift"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TOTAL STOK (KG)</span>
                    <span class="info-box-number">{{ number_format($totalStok, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </a>
</div>

{{-- Saldo Kas & Lokasi --}}
<div class="row">
    <div class="col-md-6">
        <div class="box box-widget widget-user-2">
            <div class="widget-user-header bg-navy">
                <h3 class="widget-user-username">SALDO KAS BANK SAMPAH</h3>
            </div>
            <div class="box-footer no-padding">
                <ul class="nav nav-stacked">
                    <li><a href="#">Total Setoran <span class="pull-right badge bg-green">{{ bank_sampah_format_rupiah($setoranBulan) }}</span></a></li>
                    <li><a href="#">Total Tarikan <span class="pull-right badge bg-red">{{ bank_sampah_format_rupiah($tarikanBulan) }}</span></a></li>
                    <li><a href="#">Saldo Kas <span class="pull-right badge bg-aqua"><b>{{ bank_sampah_format_rupiah($saldoKas) }}</b></span></a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-widget widget-user-2">
            <div class="widget-user-header bg-teal">
                <h3 class="widget-user-username">LOKASI / TPS</h3>
            </div>
            <div class="box-footer no-padding">
                <ul class="nav nav-stacked">
                    <li><a href="{{ url('bank-sampah/lokasi') }}">Total Lokasi Aktif <span class="pull-right badge bg-aqua">{{ $totalLokasi }}</span></a></li>
                    <li><a href="{{ url('bank-sampah/transaksi/setor') }}">
                        <button class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> Setor Sampah</button>
                        <b>Transaksi Baru</b>
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Grafik Transaksi Bulanan --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> Grafik Transaksi 12 Bulan Terakhir</h3>
            </div>
            <div class="box-body">
                <canvas id="grafikTransaksi" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Transaksi Terakhir --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Transaksi Terakhir</h3>
                <div class="box-tools pull-right">
                    <a href="{{ url('bank-sampah/transaksi') }}" class="btn btn-box-tool">Lihat Semua <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Nasabah</th>
                            <th>Tipe</th>
                            <th>Total</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksiTerakhir as $trx)
                        <tr>
                            <td>{{ $trx->no_transaksi }}</td>
                            <td>{{ $trx->member->nama ?? '-' }}</td>
                            <td>{!! bank_sampah_tipe_transaksi_label($trx->tipe) !!}</td>
                            <td>{{ bank_sampah_format_rupiah($trx->total) }}</td>
                            <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada transaksi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('grafikTransaksi').getContext('2d');
    const data = @json($grafikData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.bulan),
            datasets: [
                {
                    label: 'Setor',
                    data: data.map(d => d.setor),
                    backgroundColor: 'rgba(60, 179, 113, 0.7)',
                    borderColor: 'rgba(60, 179, 113, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Tarik',
                    data: data.map(d => d.tarik),
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            tooltips: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        }
    });
});
</script>
@endpush
