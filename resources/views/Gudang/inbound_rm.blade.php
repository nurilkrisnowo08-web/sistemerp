@extends('layout.admin')

@section('content')
<style>
    .inbound-card { border-radius: 20px; border: none; transition: all 0.3s ease; }
    .inbound-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
    .input-industrial { 
        background: #f8f9fc; border: 2px solid #e3e6ec; border-radius: 12px; 
        padding: 12px 15px; font-weight: 700; font-family: 'Roboto Mono', monospace;
        color: var(--ind-dark); transition: all 0.2s;
    }
    .input-industrial:focus { border-color: var(--ind-accent); background: #fff; outline: none; }
    .label-tactical { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
    .btn-execute { border-radius: 15px; font-weight: 800; letter-spacing: 1px; padding: 18px; text-transform: uppercase; }
</style>

<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8">
        <div class="card inbound-card shadow-sm mb-5">
            <div class="card-header bg-white py-4 px-5 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-3 mr-3 shadow-sm">
                        <i class="fas fa-truck-loading fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="m-0 font-weight-extrabold text-dark">Material Inbound</h4>
                        <p class="text-muted small m-0 font-weight-bold italic">Registrasi Coil/Bandel dari Supplier</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('rm.inbound_store') }}" method="POST">
                @csrf
                <div class="card-body px-5 py-2">
                    
                    {{-- 1. SELECT PO --}}
                    <div class="form-group mb-4">
                        <label class="label-tactical"><i class="fas fa-file-invoice mr-2 text-primary"></i> 01. Referensi PO Supplier</label>
                        <select name="no_po" id="sel_po" class="input-industrial w-100" required>
                            <option value="" selected disabled>-- PILIH PO AKTIF --</option>
                            @foreach($activePOs as $po)
                                <option value="{{ $po->no_po }}" data-spec="{{ $po->spec }}">
                                    {{ $po->no_po }} | {{ $po->supplier_name }} ({{ $po->spec }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. IDENTITAS BANDEL --}}
                    <div class="form-group mb-4">
                        <label class="label-tactical text-primary"><i class="fas fa-barcode mr-2"></i> 02. Nomor Bandel / Coil ID (Label Fisik)</label>
                        <input type="text" name="no_bandel" class="input-industrial w-100 border-primary" 
                               placeholder="Contoh: CL-815" required style="font-size: 1.2rem; text-transform: uppercase;">
                        <small class="text-info font-weight-bold"><i class="fas fa-info-circle mt-2 mr-1"></i> WAJIB diinput manual sesuai label dari supplier Ndan!</small>
                    </div>

                    <div class="row">
                        {{-- 3. QUANTITY --}}
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="label-tactical"><i class="fas fa-weight-hanging mr-2"></i> 03. Quantity Datang</label>
                                <div class="input-group">
                                    <input type="number" name="qty_in" class="input-industrial w-100" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                        {{-- 4. SPEC (Auto Fill) --}}
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="label-tactical"><i class="fas fa-microchip mr-2"></i> Material Spec</label>
                                <input type="text" id="display_spec" class="input-industrial w-100 bg-light" value="-- AUTO --" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="alert bg-gray-100 border-0 p-3 mb-4" style="border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt text-success mr-3 fa-lg"></i>
                            <p class="small m-0 text-dark font-weight-bold">
                                Sistem akan otomatis mendistribusikan stok ke semua Part Number dengan Spec yang sama.
                            </p>
                        </div>
                    </div>

                </div>
                <div class="card-footer bg-white border-0 px-5 pb-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block btn-execute shadow-lg">
                        <i class="fas fa-check-double mr-2"></i> Register Bundle & Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-fill Spec biar Admin gak bingung Ndan
        $('#sel_po').on('change', function() {
            var spec = $(this).find(':selected').data('spec');
            $('#display_spec').val(spec);
        });
    });
</script>
@endsection