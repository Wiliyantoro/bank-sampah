@extends('admin.layouts.index')

@section('content')
<section class="content-header">
    <h1><i class="fa fa-minus-circle text-danger"></i> Tarik Saldo</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            @include('admin.layouts.components.notifikasi')

            {!! form_open(url('bank-sampah/transaksi/proses-tarik'), '') !!}

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
                                    <p class="form-control-static text-bold text-success" id="infoSaldo" style="font-size: 18px;">-</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Poin</label>
                                    <p class="form-control-static text-bold" id="infoPoin">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Detail Penarikan</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>Nominal Penarikan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="number" class="form-control" id="nominal" name="nominal"
                                    min="1" required placeholder="Masukkan nominal penarikan">
                            </div>
                            <small class="text-muted">Maksimal: <span id="maxTarik">Rp 0</span></small>
                        </div>

                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="2"
                                placeholder="Catatan penarikan...">{{ old('keterangan') }}</textarea>
                        </div>

                        <div class="alert alert-info" style="margin-bottom: 0;">
                            <i class="fa fa-info-circle"></i>
                            Penarikan saldo akan mengurangi saldo nasabah. Pastikan nominal penarikan tidak melebihi saldo yang tersedia.
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ url('bank-sampah/transaksi') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-danger btn-lg pull-right" id="btnProses">
                        <i class="fa fa-check"></i> Proses Penarikan
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
            var saldo = parseInt(option.data('saldo')) || 0;
            $('#infoSaldo').text('Rp ' + saldo.toLocaleString('id-ID'));
            $('#infoPoin').text(option.data('poin') + ' poin');
            $('#maxTarik').text('Rp ' + saldo.toLocaleString('id-ID'));
            $('#nominal').attr('max', saldo);
        } else {
            $('#infoSaldo').text('-');
            $('#infoPoin').text('-');
            $('#maxTarik').text('Rp 0');
            $('#nominal').removeAttr('max');
        }
    }).trigger('change');

    // Validasi nominal
    $('#nominal').on('input', function() {
        var max = parseInt($('#member_id').find(':selected').data('saldo')) || 0;
        var val = parseInt($(this).val()) || 0;
        if (val > max) {
            $(this).val(max);
        }
    });
});
</script>
@endpush
