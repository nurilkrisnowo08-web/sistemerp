{{-- 🚀 MODAL: DEPLOYMENT CENTER (WELDING BATCH DEPLOY) --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalDeployWelding" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 40px; overflow: hidden;">
            
            {{-- HEADER: Cyber Dark Style --}}
            <div class="modal-header bg-dark text-white p-4 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="fas fa-bolt text-white"></i>
                    </div>
                    <h5 class="modal-title font-weight-black text-uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">
                        Deployment_Center
                    </h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('welding.deploy') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    
                    {{-- 01. PART SELECTION --}}
                    <div class="form-group mb-4">
                        <label class="small font-weight-black text-muted uppercase mb-2 d-block">
                            <i class="fas fa-tag mr-2 text-primary"></i> 01. Target_Part_Identification
                        </label>
                        <select name="part_no" id="part_select" class="form-control tech-input-lg" style="height: 65px; font-size: 18px;" required>
                            <option value="" disabled selected>-- SELECT PART --</option>
                            @foreach($inventoryWelding as $inv)
                                <option value="{{ $inv->part_no }}">
                                    {{ $inv->part_no }} (Available: {{ number_format($inv->live_stock) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 02. STATION SELECTION --}}
                    <div class="form-group mb-4">
                        <label class="small font-weight-black text-muted uppercase mb-2 d-block">
                            <i class="fas fa-microchip mr-2 text-primary"></i> 02. Authorized_Welding_Station
                        </label>
                        <select name="line_id" class="form-control tech-input-lg" style="height: 65px; font-size: 18px;" required>
                            <option value="" disabled selected>-- SELECT STATION --</option>
                            @foreach($weldingLines as $wl)
                                <option value="{{ $wl->id }}">{{ $wl->kode_line }} - {{ $wl->nama_line }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 03. QUANTITY INPUT --}}
                    <div class="form-group mb-0">
                        <label class="small font-weight-black text-primary uppercase mb-2 d-block">
                            <i class="fas fa-box-open mr-2"></i> 03. Deployment_Quantity
                        </label>
                        <input type="number" name="qty_ambil" class="form-control tech-input-lg font-weight-black text-primary"
                               required style="font-size: 48px; height: 110px;" placeholder="0" min="1">
                        <div class="text-center mt-3">
                            <small class="text-muted font-weight-bold uppercase" style="letter-spacing: 1px;">
                                <i class="fas fa-info-circle mr-1"></i> Data will be registered to active workstream
                            </small>
                        </div>
                    </div>
                </div>

                {{-- FOOTER: Execution Button --}}
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-3xl shadow-xl uppercase" style="font-size: 1.1rem; letter-spacing: 2px;">
                        Authorize & Start Deployment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>