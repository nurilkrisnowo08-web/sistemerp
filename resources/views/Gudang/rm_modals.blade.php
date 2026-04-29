{{-- 🚀 MODAL: REGISTER NEW COIL --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalTambahRM" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                        {{-- LEFT SIDE --}}
                        <div class="col-md-6 border-right pr-4">
                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-muted uppercase mb-2 d-block">01. Client Entity</label>
                                <select name="customer_code" id="modalFilterCustomer" class="form-control form-control-tech" required>
                                    <option value="" disabled selected>-- CHOOSE CLIENT --</option>
                                    @foreach($availableCustomers as $c) 
                                        <option value="{{ trim($c->code) }}">{{ $c->name }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-muted uppercase mb-2 d-block">02. Coil ID / Number</label>
                                <input type="text" name="coil_id" class="form-control form-control-tech font-weight-black" placeholder="e.g. M29158..." required>
                            </div>

                            <div class="form-group mb-0">
                                <label class="small font-weight-black text-muted uppercase mb-2 d-block">03. Specification (From DB)</label>
                                <select id="selectMasterSpec" class="form-control form-control-tech" required disabled>
                                    <option value="">-- SELECT CLIENT FIRST --</option>
                                </select>
                                {{-- Hidden input untuk kirim data spec & size ke controller --}}
                                <input type="hidden" name="spec" id="autoSpec">
                                <input type="hidden" name="size" id="autoSize">
                            </div>
                        </div>

                        {{-- RIGHT SIDE --}}
                        <div class="col-md-6 pl-4">
                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-primary uppercase mb-2 d-block">04. Initial Quantity (PCS)</label>
                                <input type="number" name="stock_pcs" class="form-control form-control-tech font-weight-black text-primary" style="font-size: 24px;" placeholder="0" required>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="small font-weight-black text-muted uppercase mb-2 d-block">Min_Stock</label>
                                    <input type="number" name="min_stock" class="form-control form-control-tech" value="500">
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-black text-muted uppercase mb-2 d-block">Max_Stock</label>
                                    <input type="number" name="max_stock" class="form-control form-control-tech" value="1000">
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="small font-weight-black text-muted uppercase mb-2 d-block">05. Mapped Parts (Link to Coil)</label>
                                <select name="part_nos[]" id="selectPart" class="form-control form-control-tech" multiple style="height: 120px;" required disabled>
                                    {{-- Diisi otomatis lewat AJAX --}}
                                </select>
                                <small class="text-muted italic">Hold CTRL to select multiple parts.</small>
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

{{-- 🛡️ MODAL: SPEC_DB MASTER (Hanya jika Bapak butuh input spek baru) --}}
<div class="modal fade" id="modalMasterSpec" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 35px;">
            <div class="modal-header bg-dark text-white p-4">
                <h6 class="modal-title font-weight-black uppercase">Material_Spec_Registry</h6>
            </div>
            <form action="{{ route('rm.store_master_spec') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Client</label>
                        <select name="customer_code" class="form-control form-control-tech" required>
                            @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Alias Code</label>
                        <input type="text" name="alias_code" class="form-control form-control-tech" placeholder="e.g. SPC13" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Material Type</label>
                        <input type="text" name="material_type" class="form-control form-control-tech" placeholder="e.g. SPG340X" required>
                    </div>
                    <div class="row">
                        <div class="col-6"><label class="small font-weight-bold">Thickness</label><input type="text" name="thickness" class="form-control form-control-tech" placeholder="0.80" required></div>
                        <div class="col-6"><label class="small font-weight-bold">Size</label><input type="text" name="size" class="form-control form-control-tech" placeholder="69X154" required></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-dark btn-block py-3 font-weight-bold rounded-pill">REGISTER_SPEC</button>
                </div>
            </form>
        </div>
    </div>
</div>