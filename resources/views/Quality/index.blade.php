@extends('layout.admin')
@section('content')
<div class="container-fluid py-4">
    <h2 class="font-weight-bold mb-4">QUALITY_GATE CONTROL rill</h2>
    <div class="row">
        {{-- Loop Antrean (Contoh Stamping) --}}
        @foreach($stampingQueue as $s)
        <div class="col-md-4">
            <div class="card shadow-sm mb-4" style="border-radius: 20px; border-left: 5px solid #4361ee;">
                <div class="card-body">
                    <h6 class="text-primary font-weight-bold">{{ $s->no_produksi }}</h6>
                    <h5>{{ $s->material_code }}</h5>
                    <p class="mb-1 text-muted">Laporan Produksi: <b>{{ $s->qty_hasil_ok }} PCS</b></p>
                    <hr>
                    <form action="{{ route('quality.gate.approve', ['type' => 'stamping', 'id' => $s->id]) }}" method="POST">
                        @csrf
                        <label class="small font-weight-bold">QTY OK FINAL rill</label>
                        <input type="number" name="qty_ok_final" class="form-control mb-2" value="{{ $s->qty_hasil_ok }}" required>
                        <label class="small font-weight-bold">QTY NG (TEMUAN QC)</label>
                        <input type="number" name="qty_ng_final" class="form-control mb-2" value="0">
                        <textarea name="ng_reason" class="form-control mb-3" placeholder="Alasan jika ada NG rill..."></textarea>
                        <button class="btn btn-success btn-block font-weight-bold">RELEASE TO FG rill</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection