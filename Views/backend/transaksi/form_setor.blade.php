@extends('admin::layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-plus-circle text-success"></i> Setor Sampah</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ url('bank-sampah/transaksi/proses-setor') }}" method="POST" id="formSetor">
                @csrf

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Data Nasabah</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pilih Nasabah <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="member_id" name="member_id" required>
                                        <option value="">-- Pilih Nasabah --</option>
                                        @foreach($memberList as $m)
                                            <option value="{{ $m->id }}"
                                                data-saldo="{{ $m->saldo }}"
                                                data-poin="{{ $m->poin }}"
                                                data-no="{{ $m->no_member }}"
                                                {{ ($selectedMember && $selectedMember->id == $m->id) ? 'selected' : '' }}>
                                                {{ $m->no_member }} - {{ $m->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Saldo Saat Ini</label>
                                    <p class="form-control-static text-bold" id="infoSaldo">-</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Poin</label>
                                    <p class="form-control-static text-bold" id="infoPoin">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Lokasi / TPS</label>
                            <select class="form-control" id="lokasi_id" name="lokasi_id">
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach($lokasiList as $l)
                                    <option value="{{ $l->id }}">{{ $l->kode }} - {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Jenis Sampah yang Disetor</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" id="btnTambahItem">
                                <i class="fa fa-plus"></i> Tambah Item
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered" id="tableItem">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Jenis Sampah</th>
                                    <th width="120">Berat (kg)</th>
                                    <th width="150">Harga/kg</th>
                                    <th width="180">Subtotal</th>
                                    <th width="60">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyItem">
                                <tr class="item-row">
                                    <td>1</td>
                                    <td>
                                        <select class="form-control select2 item-sampah" name="item[0][sampah_id]" required>
                                            <option value="">-- Pilih Sampah --</option>
                                            @foreach($sampahList as $s)
                                                <option value="{{ $s->id }}"
                                                    data-harga="{{ $s->harga_beli }}"
                                                    data-satuan="{{ $s->satuan }}">
                                                    {{ $s->kategori->nama }} - {{ $s->nama }} ({{ format_rupiah($s->harga_beli) }}/{{ $s->satuan }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control item-berat" name="item[0][berat]" step="0.01" min="0.01" required placeholder="0.00"></td>
                                    <td class="item-harga">Rp 0</td>
                                    <td class="item-subtotal text-bold">Rp 0</td>
                                    <td><button type="button" class="btn btn-danger btn-xs btn-hapus-item"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>TOTAL</strong></td>
                                    <td class="text-bold" id="grandTotal" style="font-size: 18px;">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="2" placeholder="Catatan transaksi...">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ url('bank-sampah/transaksi') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success btn-lg pull-right" id="btnProses">
                        <i class="fa fa-check"></i> Proses Setor
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Info member
    $('#member_id').on('change', function() {
        var option = $(this).find(':selected');
        if (option.val()) {
            $('#infoSaldo').text('Rp ' + parseInt(option.data('saldo')).toLocaleString('id-ID'));
            $('#infoPoin').text(option.data('poin') + ' poin');
        } else {
            $('#infoSaldo').text('-');
            $('#infoPoin').text('-');
        }
    }).trigger('change');

    // Tambah item
    var itemCount = 1;
    $('#btnTambahItem').on('click', function() {
        var row = `
        <tr class="item-row">
            <td>${itemCount + 1}</td>
            <td>
                <select class="form-control select2 item-sampah" name="item[${itemCount}][sampah_id]" required>
                    <option value="">-- Pilih Sampah --</option>
                    @foreach($sampahList as $s)
                        <option value="{{ $s->id }}"
                            data-harga="{{ $s->harga_beli }}"
                            data-satuan="{{ $s->satuan }}">
                            {{ $s->kategori->nama }} - {{ $s->nama }} ({{ format_rupiah($s->harga_beli) }}/{{ $s->satuan }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" class="form-control item-berat" name="item[${itemCount}][berat]" step="0.01" min="0.01" required placeholder="0.00"></td>
            <td class="item-harga">Rp 0</td>
            <td class="item-subtotal text-bold">Rp 0</td>
            <td><button type="button" class="btn btn-danger btn-xs btn-hapus-item"><i class="fa fa-trash"></i></button></td>
        </tr>`;
        $('#tbodyItem').append(row);
        itemCount++;
        initSelect2();
        hitungTotal();
    });

    // Hapus item
    $(document).on('click', '.btn-hapus-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            hitungTotal();
        }
    });

    // Hitung subtotal
    $(document).on('change', '.item-sampah', function() {
        var harga = $(this).find(':selected').data('harga') || 0;
        $(this).closest('tr').find('.item-harga').text('Rp ' + parseInt(harga).toLocaleString('id-ID'));
        hitungSubtotal($(this).closest('tr'));
        hitungTotal();
    });

    $(document).on('input', '.item-berat', function() {
        hitungSubtotal($(this).closest('tr'));
        hitungTotal();
    });

    function hitungSubtotal(row) {
        var harga = parseInt(row.find('.item-sampah :selected').data('harga')) || 0;
        var berat = parseFloat(row.find('.item-berat').val()) || 0;
        var subtotal = harga * berat;
        row.find('.item-subtotal').text('Rp ' + Math.round(subtotal).toLocaleString('id-ID'));
    }

    function hitungTotal() {
        var total = 0;
        $('.item-subtotal').each(function() {
            var val = parseInt($(this).text().replace(/[^0-9]/g, '')) || 0;
            total += val;
        });
        $('#grandTotal').text('Rp ' + total.toLocaleString('id-ID'));
    }

    function initSelect2() {
        $('.select2').select2({ theme: 'bootstrap' });
    }
    initSelect2();
});
</script>
@endpush
