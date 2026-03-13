@extends('layout')

@section('title','Certificates')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Certificates</h1>
            <p class="text-sm text-slate-500">Manage generated certificates. Use "Generate Certificate" to create a new certificate record.</p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div></div>
        <div>
            <button id="openAssetsBtn" class="bg-indigo-600 text-white px-4 py-2 rounded">Logos & Signatures</button>
        </div>
    </div>

    <!-- Modal (hidden) -->
    <div id="assetsModal" class="fixed inset-0 z-50 hidden items-start justify-center pt-12 pb-12 bg-black bg-opacity-45">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl mx-4 max-h-[90vh] overflow-hidden">
            {{-- Modal header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <div>
                    <h2 class="text-xl font-semibold">Logos & Signatures</h2>
                    <p class="text-sm text-slate-500">Manage company logo and e-signatures. Draw, save, or upload images.</p>
                </div>
                <div>
                    <button id="closeAssetsBtn" class="text-slate-600 hover:text-slate-800">Close</button>
                </div>
            </div>

            {{-- Modal body with form wrapper --}}
            @php
                $keys = ['company_logo','signature_president','signature_instructor','signature_collaborator'];
                $resp = [];
                foreach ($keys as $k) {
                    try {
                        $a = \App\Models\CertificateAsset::where('key', $k)->where('status','active')->latest()->first();
                        $resp[$k] = $a ? \Illuminate\Support\Facades\Storage::disk('public')->url($a->path) : null;
                    } catch (\Throwable $e) { $resp[$k] = null; }
                }
            @endphp

            <form id="assetsForm" class="p-6 overflow-y-auto max-h-[68vh]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- LEFT COLUMN: Company Logo + Other Asset --}}
                    <div class="flex flex-col gap-6">
                        {{-- Company Logo Card --}}
                        <div class="asset-card bg-white border rounded-lg p-4 shadow-sm">
                            <h3 class="text-sm font-medium mb-2">Company Logo</h3>
                            <div class="flex items-center gap-4">
                                <div class="w-40 h-24 bg-gray-50 border rounded flex items-center justify-center overflow-hidden">
                                    <img id="preview-company_logo" src="{{ $resp['company_logo'] ?? url('/assets/Maptech-Official-Logo.png') }}" alt="Logo preview" class="max-h-full max-w-full object-contain" />
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm text-slate-600 mb-2">Current logo preview</div>
                                    <div class="flex items-center gap-3">
                                        <input type="file" id="file-company_logo" name="asset_company_logo" accept="image/*" class="text-sm" />
                                        <button type="button" data-key="company_logo" class="upload-btn bg-blue-600 text-white px-3 py-1 rounded">Replace</button>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-2">This logo will be used when generating certificates.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Other Asset Card --}}
                        <div class="asset-card bg-white border rounded-lg p-4 shadow-sm">
                            <h3 class="text-sm font-medium mb-2">Other Asset</h3>
                            <div class="mb-2 text-sm text-slate-600">Add any additional image asset (e.g., partner logo)</div>
                            <div class="flex items-center gap-3">
                                <input type="text" id="otherAssetName" placeholder="Asset base name (e.g. partner_logo)" class="border rounded px-3 py-2 flex-1" />
                                <input type="file" id="otherAssetFile" accept="image/*" class="text-sm" />
                                <button type="button" id="uploadOtherAsset" class="bg-blue-600 text-white px-3 py-1 rounded">Upload</button>
                            </div>
                            <div class="text-xs text-slate-400 mt-2">Provide a simple base name so the asset is stored with a predictable key.</div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: Signatures stacked --}}
                    <div class="flex flex-col gap-6">
                        @php
                            $signers = [
                                ['key'=>'signature_president','label'=>'President'],
                                ['key'=>'signature_instructor','label'=>'Instructor'],
                                ['key'=>'signature_collaborator','label'=>'Collaborator'],
                            ];
                        @endphp

                        @foreach($signers as $s)
                        <div class="asset-card bg-white border rounded-lg p-4 shadow-sm">
                            <div class="flex items-start justify-between">
                                <h3 class="text-sm font-medium">{{ $s['label'] }} Signature</h3>
                                <div id="source-{{ $s['key'] }}" class="text-xs text-slate-500">{{ $resp[$s['key']] ? 'Saved' : 'None' }}</div>
                            </div>

                            <div class="mt-3 flex items-center gap-4">
                                <div class="w-36 h-16 bg-gray-50 border rounded overflow-hidden flex items-center justify-center">
                                    <img id="preview-{{ $s['key'] }}" src="{{ $resp[$s['key']] ?? '' }}" alt="preview" class="max-h-full max-w-full object-contain" />
                                </div>
                                <div class="flex-1 text-sm text-slate-600">Preview — saved signature (if any)</div>
                            </div>

                            <div class="mt-3">
                                <canvas id="canvas-{{ $s['key'] }}" width="800" height="220" class="w-full h-36 rounded border"></canvas>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button data-key="{{ $s['key'] }}" type="button" class="clear-signature bg-gray-200 text-sm px-3 py-1 rounded">Clear</button>
                                    <button data-key="{{ $s['key'] }}" type="button" class="save-signature bg-green-600 text-white text-sm px-3 py-1 rounded">Save Signature</button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="file" id="file-{{ $s['key'] }}" accept="image/*" class="text-sm" />
                                    <button data-key="{{ $s['key'] }}" type="button" class="upload-btn bg-blue-600 text-white text-sm px-3 py-1 rounded">Upload</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>

                {{-- Footer actions inside form so submit works if desired --}}
                <div class="mt-6 border-t pt-4 flex items-center justify-end gap-3">
                    <button type="button" class="close-modal px-4 py-2 rounded border">Cancel</button>
                    <button type="submit" id="saveAllAssets" class="bg-green-600 text-white px-4 py-2 rounded">Upload / Save</button>
                </div>
            </form>

            <style>
                /* Full replacement modal styles - clean, landscape, professional */
                #assetsModal .asset-card { background: #fff; }
                #assetsModal .asset-card .w-40 { min-width: 160px; }
                #assetsModal .asset-card img { display: block; }
                #assetsModal canvas { background: #fff; border-radius: 6px; border: 1px solid #e6eef6; }
                /* Prevent buttons wrapping inside action rows */
                #assetsModal .asset-card .flex.items-center.justify-between { gap: 12px; }
                #assetsModal .asset-card .mt-3 .flex.items-center { gap: 12px; }
                #assetsModal .controls { white-space: nowrap; }
                @media (max-width: 767px) {
                    #assetsModal .max-w-6xl { padding: 0 12px; }
                    #assetsModal canvas { height: 140px !important; }
                }
            </style>

        </div>
    </div>

    <script>
        (function(){
            const openBtn = document.getElementById('openAssetsBtn');
            const modal = document.getElementById('assetsModal');
            const closeBtn = document.getElementById('closeAssetsBtn');
            const cancelBtn = document.getElementById('assetsCancel');
            const form = document.getElementById('assetsForm');
            const msg = document.getElementById('assetsMessage');

            function adjustCanvases(){
                try{
                    const canvases = modal.querySelectorAll('canvas');
                    canvases.forEach(c => {
                        const rect = c.getBoundingClientRect();
                        const ratio = window.devicePixelRatio || 1;
                        const w = Math.max(300, rect.width);
                        const h = Math.max(120, rect.height);
                        c.width = Math.round(w * ratio);
                        c.height = Math.round(h * ratio);
                        c.style.width = rect.width + 'px';
                        c.style.height = rect.height + 'px';
                        const ctx = c.getContext('2d');
                        if (ctx) { ctx.setTransform(ratio,0,0,ratio,0,0); ctx.lineWidth = 2; ctx.lineCap = 'round'; }
                    });
                }catch(e){console.warn('Adjust canvas failed',e)}
            }

            function showModal(){ modal.classList.remove('hidden'); modal.classList.add('flex'); setTimeout(adjustCanvases,50); }
            function hideModal(){ modal.classList.remove('flex'); modal.classList.add('hidden'); }

            openBtn.addEventListener('click', async function(){
                // refresh previews
                try{
                    const r = await fetch('/admin/certificates/assets', { credentials:'include', headers:{'Accept':'application/json'}});
                    if (r.ok){ const a = await r.json();
                        const pc = document.getElementById('preview_company_logo'); if (pc) pc.src = a.company_logo || '/assets/Maptech-Official-Logo.png';
                        const pp = document.getElementById('preview_signature_president'); if (pp) pp.src = a.signature_president || '/assets/signature_president.png';
                        const pi = document.getElementById('preview_signature_instructor'); if (pi) pi.src = a.signature_instructor || '/assets/signature_instructor.png';
                        const pcoll = document.getElementById('preview_signature_collaborator'); if (pcoll) pcoll.src = a.signature_collaborator || '/assets/signature_collaborator.png';
                    }
                }catch(e){ console.error(e); }
                showModal();
            });
            // wire change logo button to hidden file input and show instant preview (guard elements)
            const changeLogoBtn = document.getElementById('changeLogoBtn');
            const logoInput = document.getElementById('asset_company_logo_input') || document.getElementById('file-company_logo');
            const logoPreview = document.getElementById('preview-company_logo');
            let _logoObjectUrl = null;
            if (changeLogoBtn && logoInput) {
                changeLogoBtn.addEventListener('click', function(){ logoInput.click(); });
            }
            if (logoInput) {
                logoInput.addEventListener('change', function(){
                    const f = (this.files && this.files[0]) ? this.files[0] : null;
                    if (f) {
                        try{ if (_logoObjectUrl) URL.revokeObjectURL(_logoObjectUrl); }catch(e){}
                        _logoObjectUrl = URL.createObjectURL(f);
                        if (logoPreview) { logoPreview.src = _logoObjectUrl; logoPreview.style.display = ''; }
                        setTimeout(adjustCanvases,50);
                    }
                });
            }
            if (closeBtn) closeBtn.addEventListener('click', hideModal);
            document.querySelectorAll('.close-modal').forEach(b => b.addEventListener('click', hideModal));
            if (cancelBtn) cancelBtn.addEventListener('click', hideModal);

            if (form) {
            form.addEventListener('submit', async function(ev){
                ev.preventDefault();
                msg.style.display='none';
                const fd = new FormData();
                // map known inputs
                const logoFile = form.querySelector('input[name="asset_company_logo"]').files[0];
                if (logoFile) { fd.append('asset', logoFile); fd.append('type', 'logo'); await uploadOne(fd); }
                const presFile = form.querySelector('input[name="asset_signature_president"]').files[0];
                if (presFile) { const f = new FormData(); f.append('asset', presFile); f.append('type','signature_president'); await uploadOne(f); }
                const instrFile = form.querySelector('input[name="asset_signature_instructor"]').files[0];
                if (instrFile) { const f = new FormData(); f.append('asset', instrFile); f.append('type','signature_instructor'); await uploadOne(f); }
                // other asset
                const otherName = document.getElementById('otherAssetName').value.trim();
                const otherFile = document.getElementById('otherAssetFile').files[0];
                if (otherName && otherFile){ const f = new FormData(); f.append('asset', otherFile); f.append('type','other'); f.append('other_name', otherName); await uploadOne(f); }
                msg.textContent = 'Uploaded successfully.'; msg.style.display = 'block';
                // refresh previews
                try{ const r = await fetch('/admin/certificates/assets', { credentials:'include', headers:{'Accept':'application/json'}}); if(r.ok){ const a = await r.json(); document.getElementById('preview_company_logo').src = a.company_logo; document.getElementById('preview_signature_president').src = a.signature_president; document.getElementById('preview_signature_instructor').src = a.signature_instructor; }}catch(e){}
            });
            }

            async function uploadOne(fd){
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch('/admin/certificates/upload-asset', { method:'POST', credentials:'include', headers:{'X-CSRF-TOKEN': token}, body: fd });
                if (!res.ok) { console.error('upload failed', res.status); }
            }
            // ensure canvases resize on window resize while modal open
            window.addEventListener('resize', function(){ if (!modal.classList.contains('hidden')) { setTimeout(adjustCanvases,50); } });
        })();
    </script>

        {{-- Inline signature pad script (draw + upload fallback) --}}
        <script>
        // signature-pad.js inlined for admin modal
        document.addEventListener('DOMContentLoaded', function () {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
            const allowedKeys = ['company_logo','signature_president','signature_instructor','signature_collaborator'];

            allowedKeys.forEach(key => {
                const canvas = document.getElementById('canvas-' + key);
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                let drawing = false;
                let last = { x: 0, y: 0 };

                canvas.addEventListener('pointerdown', (e) => {
                    drawing = true;
                    const rect = canvas.getBoundingClientRect();
                    last.x = e.clientX - rect.left;
                    last.y = e.clientY - rect.top;
                });

                canvas.addEventListener('pointermove', (e) => {
                    if (!drawing) return;
                    const rect = canvas.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    ctx.beginPath();
                    ctx.moveTo(last.x, last.y);
                    ctx.lineTo(x, y);
                    ctx.stroke();
                    last.x = x;
                    last.y = y;
                });

                const stop = () => drawing = false;
                canvas.addEventListener('pointerup', stop);
                canvas.addEventListener('pointerleave', stop);

                // clear button
                document.querySelectorAll('.clear-signature').forEach(btn => {
                    if (btn.dataset.key !== key) return;
                    btn.addEventListener('click', () => ctx.clearRect(0, 0, canvas.width, canvas.height));
                });

                // save button
                document.querySelectorAll('.save-signature').forEach(btn => {
                    if (btn.dataset.key !== key) return;
                    btn.addEventListener('click', async () => {
                        const dataUrl = canvas.toDataURL('image/png');
                        try {
                            const res = await fetch('/admin/certificate-assets/store', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ key: key, image: dataUrl })
                            });
                            if (!res.ok) throw new Error('Save failed');
                            const json = await res.json();
                            const img = document.getElementById('preview-' + key);
                            if (img && json.url) img.src = json.url + '?t=' + Date.now();
                            const sourceLabel = document.getElementById('source-' + key);
                            if (sourceLabel) sourceLabel.textContent = 'Saved: drawn';
                        } catch (err) {
                            alert('Error saving signature: ' + err.message);
                        }
                    });
                });

                // upload button
                document.querySelectorAll('.upload-btn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        const key = e.currentTarget.dataset.key;
                        const fileInput = document.getElementById('file-' + key) || document.getElementById('file-company_logo') || document.getElementById('file-' + key.replace('signature_',''));
                        if (!fileInput || fileInput.files.length === 0) { alert('Choose a file first'); return; }
                        const formData = new FormData();
                        formData.append('key', key);
                        formData.append('file', fileInput.files[0]);

                        try {
                            const res = await fetch('/admin/certificate-assets/store', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf },
                                body: formData
                            });
                            if (!res.ok) throw new Error('Upload failed');
                            const json = await res.json();
                            const img = document.getElementById('preview-' + key);
                            if (img && json.url) img.src = json.url + '?t=' + Date.now();
                            const sourceLabel = document.getElementById('source-' + key);
                            if (sourceLabel) sourceLabel.textContent = 'Saved: upload';
                        } catch (err) {
                            alert('Upload failed: ' + err.message);
                        }
                    });
                });

            });
        });
        </script>

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <div class="text-sm text-slate-600">Showing {{ $certificates->count() }} certificates</div>
            <div>
                <input type="search" placeholder="Search by user or course" class="border rounded px-2 py-1 text-sm" />
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Issued</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificate ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($certificates as $c)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($c->user)->fullname }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($c->course)->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ($c->certificate_data['issued_at'] ?? null) ?? $c->created_at->toDateString() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $c->certificate_data['certificate_id'] ?? $c->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="{{ route('certificates.generate') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $c->user_id }}">
                                <input type="hidden" name="course_id" value="{{ $c->course_id }}">
                                <button class="bg-blue-600 text-white px-3 py-1 rounded">Generate Certificate</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-6 text-center text-slate-500">No certificates found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection
