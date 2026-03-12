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
    <div id="assetsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded shadow-lg w-full max-w-2xl p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold">Logos & Signatures</h3>
                    <p class="text-sm text-slate-500">Upload or replace company logo and e-signatures.</p>
                </div>
                <div>
                    <button id="closeAssetsBtn" class="text-slate-600 hover:text-slate-800">Close</button>
                </div>
            </div>

            <div id="assetsMessage" class="text-sm text-green-700 mb-3" style="display:none"></div>

            <form id="assetsForm" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Company Logo (preview)</label>
                        <div class="mb-2 flex items-center gap-3">
                            <img id="preview_company_logo" src="/assets/Maptech-Official-Logo.png" alt="logo" class="h-12" onerror="this.style.display='none'">
                            <button type="button" id="changeLogoBtn" class="px-2 py-1 bg-gray-100 border rounded text-sm">Change Logo</button>
                        </div>
                        <input id="asset_company_logo_input" style="display:none" type="file" name="asset_company_logo" accept="image/*">
                        <input type="hidden" name="type_company" value="logo">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">President Signature (preview)</label>
                        <div class="mb-2"><img id="preview_signature_president" src="/assets/signature_president.png" alt="pres" class="h-12" onerror="this.style.display='none'"></div>
                        <input type="file" name="asset_signature_president" accept="image/*">
                        <input type="hidden" name="type_president" value="signature_president">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Instructor Signature (preview)</label>
                        <div class="mb-2"><img id="preview_signature_instructor" src="/assets/signature_instructor.png" alt="instr" class="h-12" onerror="this.style.display='none'"></div>
                        <input type="file" name="asset_signature_instructor" accept="image/*">
                        <input type="hidden" name="type_instructor" value="signature_instructor">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Other Asset</label>
                        <input type="text" id="otherAssetName" placeholder="Asset base name (e.g. partner_logo)" class="block w-full border rounded px-2 py-1 mb-2" />
                        <input type="file" id="otherAssetFile" accept="image/*">
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end space-x-3">
                    <button type="button" id="assetsCancel" class="px-3 py-2 rounded border">Cancel</button>
                    <button type="submit" id="assetsSave" class="bg-green-600 text-white px-4 py-2 rounded">Upload / Save</button>
                </div>
            </form>
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

            function showModal(){ modal.classList.remove('hidden'); modal.classList.add('flex'); }
            function hideModal(){ modal.classList.remove('flex'); modal.classList.add('hidden'); }

            openBtn.addEventListener('click', async function(){
                // refresh previews
                try{
                    const r = await fetch('/admin/certificates/assets', { credentials:'include', headers:{'Accept':'application/json'}});
                    if (r.ok){ const a = await r.json();
                        document.getElementById('preview_company_logo').src = a.company_logo || '/assets/Maptech-Official-Logo.png';
                        document.getElementById('preview_signature_president').src = a.signature_president || '/assets/signature_president.png';
                        document.getElementById('preview_signature_instructor').src = a.signature_instructor || '/assets/signature_instructor.png';
                    }
                }catch(e){ console.error(e); }
                showModal();
            });
            // wire change logo button to hidden file input and show instant preview
            const changeLogoBtn = document.getElementById('changeLogoBtn');
            const logoInput = document.getElementById('asset_company_logo_input');
            const logoPreview = document.getElementById('preview_company_logo');
            let _logoObjectUrl = null;
            changeLogoBtn.addEventListener('click', function(){ logoInput.click(); });
            logoInput.addEventListener('change', function(){
                const f = (this.files && this.files[0]) ? this.files[0] : null;
                if (f) {
                    try{ if (_logoObjectUrl) URL.revokeObjectURL(_logoObjectUrl); }catch(e){}
                    _logoObjectUrl = URL.createObjectURL(f);
                    logoPreview.src = _logoObjectUrl;
                    logoPreview.style.display = '';
                }
            });
            closeBtn.addEventListener('click', hideModal);
            cancelBtn.addEventListener('click', hideModal);

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

            async function uploadOne(fd){
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch('/admin/certificates/upload-asset', { method:'POST', credentials:'include', headers:{'X-CSRF-TOKEN': token}, body: fd });
                if (!res.ok) { console.error('upload failed', res.status); }
            }
        })();
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
