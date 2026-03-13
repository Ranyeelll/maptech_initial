@php
    // $assets array optional: keys company_logo, signature_president, signature_instructor, signature_collaborator
    $assets = $assets ?? [];
@endphp

<div id="cert-assets-modal" class="p-4">
    <h3 class="text-lg font-semibold mb-3">Logos & Signatures</h3>

    <div class="grid grid-cols-1 gap-6">
        <div class="asset-block bg-white p-4 rounded shadow-sm">
            <h4 class="font-medium">Company Logo</h4>
            <div class="flex items-center gap-4 mt-2">
                <img id="preview-company_logo" src="{{ $assets['company_logo'] ?? url('/assets/Maptech-Official-Logo.png') }}" alt="logo" style="max-height:110px;" />
                <div>
                    <input type="file" id="file-company_logo" accept="image/*">
                    <button data-key="company_logo" class="upload-btn bg-blue-600 text-white px-3 py-1 rounded mt-2">Replace Logo</button>
                </div>
            </div>
            <div class="text-sm text-slate-500 mt-2">Current logo preview — this will be used for certificate generation.</div>
        </div>
        <div class="asset-block bg-white p-4 rounded shadow-sm">
            <h4 class="font-medium">Partner Logo</h4>
            <div class="flex items-center gap-4 mt-2">
                <img id="preview-partner_logo" src="{{ $assets['partner_logo'] ?? '' }}" alt="partner logo" style="max-height:110px; object-fit:contain; border:1px solid #f0f0f0; padding:6px; background:#fff;" />
                <div>
                    <input type="file" id="file-partner_logo" accept="image/*">
                    <button data-key="partner_logo" class="upload-btn bg-blue-600 text-white px-3 py-1 rounded mt-2">Upload Partner Logo</button>
                </div>
            </div>
            <div id="source-partner_logo" class="text-sm text-slate-500 mt-2">{{ $assets['partner_logo'] ? 'Saved' : 'None' }}</div>
        </div>
        @php
            $signers = [
                ['key'=>'signature_president','label'=>'President Signature'],
                ['key'=>'signature_instructor','label'=>'Instructor Signature'],
                ['key'=>'signature_collaborator','label'=>'Collaborator Signature'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($signers as $s)
            <div class="asset-block bg-white p-4 rounded shadow-sm flex flex-col">
                <h4 class="font-medium">{{ $s['label'] }}</h4>
                <div class="flex items-center gap-4 mb-2 mt-2">
                    <img id="preview-{{ $s['key'] }}" src="{{ $assets[$s['key']] ?? '' }}" alt="signature" style="max-height:80px; border:1px solid #eee; padding:4px; background:#fff; width:140px; height:60px; object-fit:contain" />
                    <div id="source-{{ $s['key'] }}" class="text-sm text-slate-500">{{ $assets[$s['key']] ? 'Saved' : 'None' }}</div>
                </div>

                <div class="mb-2 flex-1">
                    <canvas id="canvas-{{ $s['key'] }}" width="800" height="220" style="border:1px solid #ddd; width:100%; height:140px; display:block;"></canvas>
                </div>

                <div class="controls mt-2 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <button data-key="{{ $s['key'] }}" class="clear-signature bg-gray-200 px-3 py-1 rounded">Clear</button>
                        <button data-key="{{ $s['key'] }}" class="save-signature bg-green-600 text-white px-3 py-1 rounded">Save Signature</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-slate-500">Upload:</div>
                        <input type="file" id="file-{{ $s['key'] }}" accept="image/*">
                        <button data-key="{{ $s['key'] }}" class="upload-btn bg-blue-600 text-white px-3 py-1 rounded">Upload</button>
                    </div>
                </div>
                @if($s['key'] === 'signature_collaborator')
                    <div class="mt-2">
                        <label class="block text-sm font-medium">Collaborator Printed Name</label>
                        <input type="text" id="collaborator_display_name" value="{{ $assets['collaborator_name'] ?? '' }}" class="block w-full border rounded px-2 py-1 mt-1" placeholder="Enter collaborator printed name" />
                    </div>
                @endif
                @if($s['key'] === 'signature_president')
                    <div class="mt-2">
                        <label class="block text-sm font-medium">President Printed Name</label>
                        <input type="text" id="president_printed_name" name="president_printed_name" value="{{ $assets['president_name'] ?? 'Prud De Leon' }}" class="block w-full border rounded px-2 py-1 mt-1" />
                    </div>
                @endif
            </div>
        @endforeach
        </div>

    </div>

    <div class="mt-4 flex justify-end">
        <button class="close-modal bg-gray-700 text-white px-4 py-2 rounded">Close</button>
    </div>
</div>

    <script>
    (() => {
        const getCsrfToken = function(){
            const m = document.querySelector('meta[name="csrf-token"]');
            if (m && m.getAttribute('content')) return m.getAttribute('content');
            if (window.Laravel && window.Laravel.csrfToken) return window.Laravel.csrfToken;
            const c = document.cookie.match('(^|;)\\s*XSRF-TOKEN\\s*=\\s*([^;]+)');
            return c ? decodeURIComponent(c.pop()) : '';
        };
        const keys = ['signature_president','signature_instructor','signature_collaborator'];
        const ratio = window.devicePixelRatio || 1;

        function setupCanvasById(id){
            const c = document.getElementById(id);
            if(!c || !(c instanceof HTMLCanvasElement)) return null;
            const ctx = c.getContext('2d'); if(!ctx) return null;
            const w = c.clientWidth; const h = c.clientHeight;
            c.width = Math.floor(w * ratio); c.height = Math.floor(h * ratio);
            ctx.scale(ratio, ratio);
            ctx.lineJoin = 'round'; ctx.lineCap = 'round'; ctx.strokeStyle = '#111827'; ctx.lineWidth = 2;

            let drawing = false; let lastX = 0; let lastY = 0;
            function getPos(e){ const rect = c.getBoundingClientRect(); return { x: (e.clientX - rect.left), y: (e.clientY - rect.top) }; }
            function onDown(e){ drawing = true; const p = getPos(e); lastX = p.x; lastY = p.y; }
            function onMove(e){ if(!drawing) return; const p = getPos(e); ctx.beginPath(); ctx.moveTo(lastX,lastY); ctx.lineTo(p.x,p.y); ctx.stroke(); lastX = p.x; lastY = p.y; }
            function onUp(){ drawing = false; }

            c.addEventListener('mousedown', onDown);
            c.addEventListener('mousemove', onMove);
            c.addEventListener('mouseleave', onUp);
            window.addEventListener('mouseup', onUp);

            // touch
            c.addEventListener('touchstart', function(ev){ ev.preventDefault(); const t=ev.touches[0]; const rect=c.getBoundingClientRect(); lastX = t.clientX-rect.left; lastY = t.clientY-rect.top; drawing=true; });
            c.addEventListener('touchmove', function(ev){ ev.preventDefault(); if(!drawing) return; const t=ev.touches[0]; const rect=c.getBoundingClientRect(); const x=t.clientX-rect.left; const y=t.clientY-rect.top; ctx.beginPath(); ctx.moveTo(lastX,lastY); ctx.lineTo(x,y); ctx.stroke(); lastX=x; lastY=y; });
            c.addEventListener('touchend', function(ev){ ev.preventDefault(); drawing=false; });

            return c;
        }

        // initialize canvases
        keys.forEach(k => setupCanvasById('canvas-'+k));

        // Clear handlers
        document.querySelectorAll('.clear-signature').forEach(btn => {
            btn.addEventListener('click', function(){ const key = this.getAttribute('data-key'); const c = document.getElementById('canvas-'+key); if(c && c.getContext){ const ctx = c.getContext('2d'); ctx.clearRect(0,0,c.width,c.height); } });
        });

        // Save handlers (drawn)
        document.querySelectorAll('.save-signature').forEach(btn => {
            btn.addEventListener('click', async function(){
                const key = this.getAttribute('data-key'); const c = document.getElementById('canvas-'+key);
                if(!c) return alert('Canvas not found');
                const dataUrl = c.toDataURL('image/png');
                const token = getCsrfToken() || '';
                try{
                    // include collaborator display name when saving collaborator signature
                    const payload = { key: key, image: dataUrl };
                    if (key === 'signature_collaborator' || key === 'signature_president') {
                        const fieldId = key === 'signature_collaborator' ? 'collaborator_display_name' : 'president_printed_name';
                        const dn = (document.getElementById(fieldId) as HTMLInputElement)?.value || '';
                        if (dn) payload.display_name = dn;
                    }
                    const res = await fetch('/admin/certificate-assets/store', { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept':'application/json' }, body: JSON.stringify(payload) });
                    if(!res.ok) throw new Error('Save failed');
                    const j = await res.json();
                    if(j.url){ const img = document.getElementById('preview-'+key); if(img) img.src = j.url; const src = document.getElementById('source-'+key); if(src) src.textContent = 'Saved'; }
                    alert('Signature saved');
                }catch(err){ console.error(err); alert('Failed to save signature'); }
            });
        });

        // Upload handlers
        document.querySelectorAll('.upload-btn').forEach(btn => {
            btn.addEventListener('click', async function(e){
                const key = this.getAttribute('data-key');
                // find corresponding file input
                const fileInput = document.getElementById('file-'+key) || document.getElementById('file-'+key.replace('asset_',''));
                if(!fileInput || !(fileInput instanceof HTMLInputElement)) return alert('File input not found');
                const f = fileInput.files && fileInput.files[0]; if(!f) return alert('Choose a file first');
                const fd = new FormData(); fd.append('file', f); fd.append('key', key);
                // include display name when uploading collaborator or president signature
                if (key === 'signature_collaborator' || key === 'signature_president') {
                    const fieldId = key === 'signature_collaborator' ? 'collaborator_display_name' : 'president_printed_name';
                    const dn = (document.getElementById(fieldId) as HTMLInputElement)?.value || '';
                    if (dn) fd.append('display_name', dn);
                }
                const token = getCsrfToken() || '';
                try{
                    const res = await fetch('/admin/certificate-assets/store', { method: 'POST', credentials: 'include', headers: { 'X-CSRF-TOKEN': token }, body: fd });
                    if(!res.ok) throw new Error('Upload failed');
                    const j = await res.json(); if(j.url){ const img = document.getElementById('preview-'+key); if(img) img.src = j.url; const src = document.getElementById('source-'+key); if(src) src.textContent = 'Saved'; }
                    alert('Uploaded');
                }catch(err){ console.error(err); alert('Upload failed'); }
            });
        });

        // close modal (if present)
        document.querySelectorAll('.close-modal').forEach(btn => btn.addEventListener('click', ()=>{ const wrapper = document.getElementById('cert-assets-modal'); if(wrapper) wrapper.closest('.modal')?.classList.remove('is-open'); }))

    })();
    </script>
