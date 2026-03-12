@php
    $data = $certificate->certificate_data ?? [];
@endphp

<div class="max-w-4xl mx-auto px-4">
    <div class="flex items-center justify-between mb-4 mt-6">
        <div class="text-left">
            <h1 class="text-xl font-semibold text-slate-800">Certificate Preview</h1>
            <p class="text-sm text-slate-500 mt-1">Printable view for verification</p>
        </div>
        <div class="text-right space-x-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded shadow text-sm">Print</button>
            <a href="{{ route('certificates.download', $certificate->id) }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded shadow text-sm">
                Download
            </a>
        </div>
    </div>

    <div class="bg-white rounded shadow p-4">

    <div class="text-center p-6 border rounded">
        @php $elements = $data['elements'] ?? null; @endphp
        @if($elements && is_array($elements) && count($elements))
            <div id="previewCanvasWrapper" style="display:flex;justify-content:center;">
                <canvas id="previewCanvas" width="820" height="560" style="border:1px solid #e5e7eb;background:#fff;max-width:100%;height:auto"></canvas>
            </div>
            <noscript>
                <div class="mb-6">
                    <img src="{{ $data['company_logo'] ?? url('/assets/Maptech-Official-Logo.png') }}" alt="Maptech" class="mx-auto h-16" onerror="this.style.display='none'">
                </div>
                <div class="text-lg font-semibold mb-1">Maptech Information Solutions Inc.</div>
                <h1 class="text-3xl font-bold mb-4">Certificate of Completion</h1>
            </noscript>
            <script>
                (function(){
                    function loadScript(src){ return new Promise((res,rej)=>{ const s=document.createElement('script'); s.src=src; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); }
                    (async function(){
                        if(typeof window.fabric === 'undefined'){
                            try{ await loadScript('https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js'); }catch(e){ console.error(e); return; }
                        }
                        const raw = @json($elements);
                        const canvas = new fabric.StaticCanvas('previewCanvas', { backgroundColor:'#ffffff' });
                        // set size to match editor aspect
                        const w = 900, h = 640; canvas.setWidth(w); canvas.setHeight(h);
                        // rebuild objects
                        raw.forEach(el=>{
                            try{
                                if(el.type === 'text'){
                                    const t = new fabric.Textbox(el.text||'', { left: el.left||50, top: el.top||50, fontSize: el.fontSize||18, fill: el.color||'#111827', fontFamily: el.fontFamily || 'Arial', selectable:false });
                                    canvas.add(t);
                                } else if(el.type === 'image'){
                                    fabric.Image.fromURL(el.src, function(img){ img.set({ left: el.left||50, top: el.top||50 }); if(el.width) img.scaleToWidth(el.width); canvas.add(img); canvas.requestRenderAll(); });
                                }
                            }catch(e){ console.error('rebuild err', e); }
                        });
                        canvas.requestRenderAll();
                    })();
                })();
            </script>
        @else
            <div style="max-width:820px;margin:0 auto;background:#ffffff;padding:20px;border:1px solid #e6e8eb;box-shadow:0 6px 18px rgba(15,23,42,0.04);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:20px;">
                    <div style="flex:1 1 60%;padding-right:18px;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
                            <img src="{{ $data['company_logo'] ?? url('/assets/Maptech-Official-Logo.png') }}" alt="Maptech" style="height:46px;object-fit:contain;" onerror="this.style.display='none'">
                            <div style="font-size:13px;color:#374151;font-weight:700;">Maptech Information Solutions Inc.</div>
                        </div>

                        <h2 style="font-size:22px;color:#0b2540;margin:14px 0 6px;font-weight:700;">Certificate of Completion</h2>

                        <p style="margin:8px 0;color:#374151;">This is to certify that</p>
                        <div style="font-size:20px;color:#0da6d6;font-weight:700;margin-bottom:8px;">{{ $data['recipient'] ?? optional($certificate->user)->fullname }}</div>

                        <p style="margin:8px 0;color:#374151;">has successfully achieved student level credential for completing the</p>
                        <div style="font-size:15px;color:#0b2540;margin-bottom:10px;font-weight:600;">{{ $data['course'] ?? optional($certificate->course)->title }}</div>

                        <div style="color:#374151;font-size:12px;line-height:1.45;margin-top:8px;">
                            <p style="margin:0 0 8px;">The student was able to demonstrate proficiency in:</p>
                            <ul style="margin:0 0 8px 18px;padding:0;color:#374151;">
                                <li>Explain components of a hierarchical network design.</li>
                                <li>Calculate numbers between decimal, binary, and hexadecimal systems.</li>
                                <li>Explain how IPv4 subnetting schemes enable local area segmentation.</li>
                            </ul>
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:70px;height:70px;border:1px solid #e6e7eb;background:#f8fafc;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:11px;">QR</div>
                                <div style="font-size:11px;color:#64748b;">Scan to verify<br><span style="color:#9aa6b3">Certificate ID: {{ $data['certificate_id'] ?? $certificate->id }}</span></div>
                            </div>

                            <div style="text-align:right;">
                                @if(!empty($data['signature_president']))
                                    <img src="{{ $data['signature_president'] }}" alt="President Signature" style="height:40px;object-fit:contain;margin-bottom:6px;" onerror="this.style.display='none'" />
                                @else
                                    <div style="border-top:2px solid #111827;width:160px;margin-left:auto;margin-bottom:6px;"></div>
                                @endif
                                <div style="font-size:12px;color:#111827;font-weight:600;">President</div>
                                <div style="font-size:11px;color:#475569;">President, Maptech Information Solutions Inc.</div>
                            </div>
                        </div>
                    </div>

                    <div style="flex:0 0 200px;display:flex;flex-direction:column;align-items:flex-end;gap:10px;">
                        @if(!empty($data['partner_logo']))
                            <div style="width:180px;height:90px;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fff;">
                                <img src="{{ $data['partner_logo'] }}" alt="Partner Logo" style="max-width:100%;max-height:100%;object-fit:contain;" onerror="this.style.display='none'" />
                            </div>
                        @else
                            <div style="width:180px;height:90px;background:linear-gradient(180deg,#f7fcff,#eef7ff);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#0b63a7;font-weight:700;">Partner<br>Logo</div>
                        @endif
                        <div style="width:180px;height:90px;background:linear-gradient(180deg,#fffaf2,#fff5ea);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#b8872b;font-weight:700;">Maptech Seal</div>
                        <div style="font-size:11px;color:#94a3b8;text-align:right;">Issued: {{ $data['issued_at'] ?? $certificate->created_at->toDateString() }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
