{{-- 🚀 MODAL 01: REGISTER NEW COIL --}}
<div class="modal fade animate__animated animate__fadeIn" id="modalTambahRM" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered"> {{-- Diubah ke XL agar lebih luas --}}
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 40px; overflow: hidden;">
            
            {{-- HEADER --}}
            <div class="modal-header bg-dark text-white p-4 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="fas fa-layer-group text-white"></i>
                    </div>
                    <h5 class="modal-title font-weight-black text-uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">
                        Register_New_Coil
                    </h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <form action="{{ route('rm.store_batch') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row">
                        {{-- SISI KIRI: IDENTIFIKASI --}}
                        <div class="col-md-6 border-right pr-md-5">
                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-muted uppercase mb-2 d-block">01. Client Entity</label>
                                <select name="customer_code" id="modalFilterCustomer" class="form-control form-control-tech" required style="height: 55px; font-weight: 700;">
                                    <option value="" disabled selected>-- CHOOSE CLIENT --</option>
                                    @foreach($availableCustomers as $c) 
                                        <option value="{{ trim($c->code) }}">{{ $c->name }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-muted uppercase mb-2 d-block">02. Coil ID / Number</label>
                                <input type="text" name="coil_id" class="form-control form-control-tech font-weight-black" placeholder="Input Coil ID (e.g. M29158...)" required style="height: 55px;">
                            </div>

                            <div class="form-group mb-0">
                                <label class="small font-weight-black text-muted uppercase mb-2 d-block">
                                    03. Material Specification (From DB)
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#modalMasterSpec" class="float-right text-primary" style="font-size: 10px; letter-spacing: 0;">+ REGISTER NEW SPEC</a>
                                </label>
                                <select id="selectMasterSpec" class="form-control form-control-tech" required disabled style="height: 55px; font-weight: 700;">
                                    <option value="">-- SELECT CLIENT FIRST --</option>
                                </select>
                                <input type="hidden" name="spec" id="autoSpec">
                                <input type="hidden" name="size" id="autoSize">
                            </div>
                        </div>

                        {{-- SISI KANAN: QUANTITY & MAPPING --}}
                        <div class="col-md-6 pl-md-5 mt-4 mt-md-0">
                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-primary uppercase mb-2 d-block">04. Initial Quantity (PCS)</label>
                                <input type="number" name="stock_pcs" class="form-control form-control-tech font-weight-black text-primary" style="font-size: 32px; height: 80px;" placeholder="0" required>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="small font-weight-black text-muted uppercase mb-2 d-block">Min_Stock</label>
                                    <input type="number" name="min_stock" class="form-control form-control-tech" value="500" style="height: 50px; font-weight: 700;">
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-black text-muted uppercase mb-2 d-block">Max_Stock</label>
                                    <input type="number" name="max_stock" class="form-control form-control-tech" value="1000" style="height: 50px; font-weight: 700;">
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="small font-weight-black text-muted uppercase mb-2 d-block">05. Mapped Parts (Link to Coil)</label>
                                <select name="part_nos[]" id="selectPart" class="form-control form-control-tech" multiple style="height: 130px; font-size: 12px; font-weight: 600;" required disabled>
                                    {{-- Diisi otomatis lewat AJAX --}}
                                </select>
                                <small class="text-muted italic mt-2 d-block">Hold <b>CTRL</b> (Windows) or <b>Command</b> (Mac) to select multiple parts.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-3xl shadow-xl uppercase" style="font-size: 1.1rem; letter-spacing: 2px;">
                        Authorize & Commit Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL 02: MASTER SPEC REGISTRY --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalMasterSpec" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 35px; overflow: hidden;">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h6 class="modal-title font-weight-black uppercase"><i class="fas fa-database mr-2 text-warning"></i>Material_Spec_Registry</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('rm.store_master_spec') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Target Client</label>
                        <select name="customer_code" class="form-control form-control-tech" required style="height: 50px; font-weight: 700;">
                            <option value="">-- SELECT CLIENT --</option>
                            @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Alias Code (e.g. SPC13)</label>
                        <input type="text" name="alias_code" class="form-control form-control-tech font-weight-bold" placeholder="Input alias..." required style="height: 50px;">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Material Type (e.g. SPG340X)</label>
                        <input type="text" name="material_type" class="form-control form-control-tech font-weight-bold" placeholder="Input spec type..." required style="height: 50px;">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold">Thickness</label>
                            <input type="text" name="thickness" class="form-control form-control-tech font-weight-bold" placeholder="0.80" required style="height: 50px;">
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold">Size</label>
                            <input type="text" name="size" class="form-control form-control-tech font-weight-bold" placeholder="69X154" required style="height: 50px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-warning btn-block py-3 font-weight-black rounded-pill shadow-lg">REGISTER_MASTER_SPEC</button>
                </div>
            </form>
        </div>
    </div>
</div>