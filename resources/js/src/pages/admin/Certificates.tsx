import React, { useEffect, useState } from 'react';

// Helper: read CSRF token robustly from meta tag, window variable, or XSRF cookie
function getCookie(name: string) {
  const match = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
  return match ? decodeURIComponent(match.pop() || '') : null;
}

function getCsrfToken(): string {
  const meta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
  if (meta && meta.getAttribute('content')) return meta.getAttribute('content') as string;
  if ((window as any).Laravel && (window as any).Laravel.csrfToken) return (window as any).Laravel.csrfToken;
  const xsrf = getCookie('XSRF-TOKEN');
  if (xsrf) return xsrf;
  return '';
}

interface Certificate {
  id: number;
  certificate_data?: any;
  created_at: string;
  user?: { id?: number; fullname?: string; email?: string };
  course?: { id?: number; title?: string };
}

interface Props { onOpenEditor?: (id:number)=>void }

export default function Certificates({ onOpenEditor }: Props) {
  const [certificates, setCertificates] = useState<Certificate[]>([]);
  const [loading, setLoading] = useState(true);
  const [assets, setAssets] = useState<any>({});
  const [uploading, setUploading] = useState(false);
  const [showAssetsModal, setShowAssetsModal] = useState(false);
  const [modalAssets, setModalAssets] = useState<any>({});
  const [modalLocalPreviews, setModalLocalPreviews] = useState<any>({});
  const localPreviewRefs = React.useRef<Record<string,string>>({});
  const canvasRefs = React.useRef<Record<string, HTMLCanvasElement | null>>({});
  const [isDrawing, setIsDrawing] = useState<Record<string, boolean>>({});

  // Initialize canvas drawing handlers when modal opens
  useEffect(() => {
    const keys = ['signature_president','signature_instructor','signature_collaborator'];
    const ratio = window.devicePixelRatio || 1;

    function setupCanvas(c: HTMLCanvasElement | null) {
      if (!c) return;
      const ctx = c.getContext('2d');
      if (!ctx) return;

      // scale for high DPI
      const w = c.clientWidth;
      const h = c.clientHeight;
      c.width = Math.floor(w * ratio);
      c.height = Math.floor(h * ratio);
      ctx.scale(ratio, ratio);
      ctx.lineJoin = 'round';
      ctx.lineCap = 'round';
      ctx.strokeStyle = '#111827';
      ctx.lineWidth = 2;

      let drawing = false;
      let lastX = 0;
      let lastY = 0;

      function getPos(evt: MouseEvent) {
        const rect = c.getBoundingClientRect();
        return { x: (evt.clientX - rect.left), y: (evt.clientY - rect.top) };
      }

      function onDown(e: MouseEvent) { drawing = true; const p = getPos(e); lastX = p.x; lastY = p.y; }
      function onMove(e: MouseEvent) { if (!drawing) return; const p = getPos(e); ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(p.x, p.y); ctx.stroke(); lastX = p.x; lastY = p.y; }
      function onUp() { drawing = false; }

      c.addEventListener('mousedown', onDown);
      c.addEventListener('mousemove', onMove);
      window.addEventListener('mouseup', onUp);
      c.addEventListener('mouseleave', onUp);

      // touch support (optional)
      c.addEventListener('touchstart', (ev)=>{ ev.preventDefault(); const t = ev.touches[0]; drawing = true; const rect = c.getBoundingClientRect(); lastX = t.clientX - rect.left; lastY = t.clientY - rect.top; });
      c.addEventListener('touchmove', (ev)=>{ ev.preventDefault(); if(!drawing) return; const t = ev.touches[0]; const rect = c.getBoundingClientRect(); const x = t.clientX - rect.left; const y = t.clientY - rect.top; ctx.beginPath(); ctx.moveTo(lastX,lastY); ctx.lineTo(x,y); ctx.stroke(); lastX = x; lastY = y; });
      c.addEventListener('touchend', (ev)=>{ ev.preventDefault(); drawing = false; });

      // store a clear function on the element for external use
      (c as any).__clear = function(){ ctx.clearRect(0,0,c.width, c.height); };
    }

    if (showAssetsModal) {
      // small delay to ensure DOM/canvas sizes stabilized
      setTimeout(()=>{
        keys.forEach(k => setupCanvas(canvasRefs.current[k] ?? null));
      }, 50);
    }

    return () => {
      // nothing to teardown (listeners bound to elements will be GCed with elements)
    };
  }, [showAssetsModal]);

  useEffect(() => {
    const fetchCerts = async () => {
      try {
        const res = await fetch('/api/admin/certificates', {
          credentials: 'include',
          headers: { 'Accept': 'application/json' }
        });
        if (res.ok) {
          const data = await res.json();
          setCertificates(data);
        } else {
          console.error('Failed to fetch certificates', res.status);
        }
      } catch (err) {
        console.error('Error fetching certificates', err);
      } finally {
        setLoading(false);
      }
    };
    fetchCerts();
    // fetch current assets for preview
    const fetchAssets = async () => {
      try {
        const r = await fetch('/admin/certificates/assets', { credentials: 'include', headers: { 'Accept': 'application/json' } });
        if (r.ok) setAssets(await r.json());
      } catch (e) { console.error('Failed to load assets', e); }
    };
    fetchAssets();
  }, []);

  // cleanup object URLs when modal closes
  useEffect(() => {
    if (!showAssetsModal) {
      // revoke any object URLs
      try{
        Object.values(localPreviewRefs.current || {}).forEach((u) => { try{ URL.revokeObjectURL(u); }catch(e){} });
      }catch(e){}
      localPreviewRefs.current = {};
      setModalLocalPreviews({});
    }
  }, [showAssetsModal]);

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6 flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Certificates</h1>
          <p className="text-sm text-slate-500">Manage generated certificates and preview or download as needed.</p>
        </div>
        <div className="flex items-center space-x-4">
          <button onClick={async ()=>{
            // open modal: fetch assets then show
            try{ const r = await fetch('/admin/certificates/assets', { credentials:'include', headers:{'Accept':'application/json'} }); if (r.ok) setModalAssets(await r.json()); }catch(e){console.error(e)}
            setShowAssetsModal(true);
          }} className="bg-indigo-600 text-white px-3 py-2 rounded">Logos & Signatures</button>
          <div className="bg-white p-3 rounded shadow flex items-center space-x-2">
            <select className="border rounded px-3 py-2 text-sm">
              <option>Select user</option>
            </select>
            <select className="border rounded px-3 py-2 text-sm">
              <option>Select course</option>
            </select>
            <button className="bg-green-600 text-white px-4 py-2 rounded">Generate</button>
          </div>
        </div>
      </div>

      {showAssetsModal && (
        <div className="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-40 p-4">
          <div className="bg-white rounded shadow-lg w-full max-w-5xl p-6 overflow-auto max-h-[90vh]">
            <div className="flex items-start justify-between mb-4">
              <div>
                <h3 className="text-lg font-semibold">Logos & Signatures</h3>
                <p className="text-sm text-slate-500">Upload, draw, or replace company logo and e-signatures. Saved assets are used when generating certificates.</p>
              </div>
              <div>
                <button onClick={()=>setShowAssetsModal(false)} className="text-slate-600 hover:text-slate-800">Close</button>
              </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div className="lg:col-span-1">
                <div className="mb-4">
                  <label className="block text-sm font-medium mb-2">Current Company Logo</label>
                  <div className="bg-gray-50 p-3 rounded flex items-center space-x-3">
                    <img src={modalLocalPreviews.company_logo || modalAssets.company_logo || assets.company_logo || '/assets/Maptech-Official-Logo.png'} alt="logo" className="h-20 object-contain" />
                    <div className="text-sm text-slate-600">{modalAssets.company_logo ? 'Saved' : 'Default'}</div>
                  </div>
                </div>

                <div className="mb-4">
                  <label className="block text-sm font-medium mb-2">Partner Logo</label>
                  <div className="bg-gray-50 p-3 rounded flex items-center space-x-3">
                    <img src={modalLocalPreviews.partner_logo || modalAssets.partner_logo || assets.partner_logo || ''} alt="partner" className="h-16 object-contain" />
                    <div className="text-sm text-slate-600">{modalAssets.partner_logo ? 'Saved' : 'None'}</div>
                  </div>
                </div>

                <div className="space-y-3">
                  <label className="block text-sm font-medium">Saved Signatures</label>
                  <div className="grid grid-cols-1 gap-2">
                    {['signature_president','signature_instructor','signature_collaborator'].map((k)=> (
                      <div key={`preview-${k}`} className="flex items-center space-x-3 p-2 border rounded">
                        <img src={(modalAssets as any)[k] || (assets as any)[k] || `/assets/${k}.png`} alt={k} className="h-12 object-contain" />
                        <div className="text-sm text-slate-600">{(modalAssets as any)[k] ? 'Saved' : 'None'}</div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              <div className="lg:col-span-2">
                <form onSubmit={async (e)=>{
                  e.preventDefault();
                  const form = e.currentTarget as HTMLFormElement;
                  setUploading(true);
                    try{
                    const token = getCsrfToken();
                    const logo = (form.querySelector('input[name="asset_company_logo"]') as HTMLInputElement).files?.[0];
                    if (logo){ const fd = new FormData(); fd.append('asset', logo); fd.append('type','logo'); await fetch('/admin/certificates/upload-asset',{method:'POST',credentials:'include',headers:{'X-CSRF-TOKEN':token||''},body:fd}); }
                    const otherName = (form.querySelector('#otherAssetName') as HTMLInputElement).value.trim();
                    const otherFile = (form.querySelector('#otherAssetFile') as HTMLInputElement).files?.[0];
                    if (otherName && otherFile){ const fd = new FormData(); fd.append('asset', otherFile); fd.append('type','other'); fd.append('other_name', otherName); await fetch('/admin/certificates/upload-asset',{method:'POST',credentials:'include',headers:{'X-CSRF-TOKEN':token||''},body:fd}); }
                    // refresh modal assets
                    const r = await fetch('/admin/certificates/assets',{credentials:'include',headers:{'Accept':'application/json'}});
                    if (r.ok) setModalAssets(await r.json());
                    setAssets(await (await fetch('/admin/certificates/assets',{credentials:'include',headers:{'Accept':'application/json'}})).json());
                    alert('Uploaded');
                  }catch(err){console.error(err); alert('Upload failed');}
                  setUploading(false);
                }}>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium mb-1">Change Company Logo</label>
                      <input type="file" name="asset_company_logo" accept="image/*" onChange={(e)=>{
                        const f = e.target.files?.[0];
                        if (f){
                          try{ if (localPreviewRefs.current.company_logo) URL.revokeObjectURL(localPreviewRefs.current.company_logo); }catch(e){}
                          const url = URL.createObjectURL(f);
                          localPreviewRefs.current.company_logo = url;
                          setModalLocalPreviews((s:any)=>({ ...s, company_logo: url }));
                        }
                      }} />
                      <div className="mt-3">
                        <label className="block text-sm font-medium mb-1">Partner Logo (upload)</label>
                        <input type="file" id="asset_partner_logo" accept="image/*" onChange={(e)=>{ const f = e.target.files?.[0]; if (f){ try{ if(localPreviewRefs.current.partner_logo) URL.revokeObjectURL(localPreviewRefs.current.partner_logo);}catch(e){} const url = URL.createObjectURL(f); localPreviewRefs.current.partner_logo = url; setModalLocalPreviews((s:any)=>({...s, partner_logo: url})); } }} />
                        <div className="mt-2">
                          <button type="button" onClick={async ()=>{
                            const input = document.getElementById('asset_partner_logo') as HTMLInputElement; if(!input || !input.files?.[0]) return alert('Choose a partner logo first');
                            const f = input.files[0]; const fd = new FormData(); fd.append('file', f); fd.append('key', 'partner_logo');
                            try{ const token = getCsrfToken(); const res = await fetch('/admin/certificate-assets/store',{ method:'POST', credentials:'include', headers:{'X-CSRF-TOKEN': token||''}, body: fd }); if(!res.ok) throw new Error('upload failed'); const j = await res.json(); setModalAssets((s:any)=>({ ...s, partner_logo: j.url })); setAssets(await (await fetch('/admin/certificates/assets',{credentials:'include',headers:{'Accept':'application/json'}})).json()); alert('Partner logo uploaded'); }catch(err){ console.error(err); alert('Upload failed'); }
                          }} className="mt-1 bg-blue-600 text-white px-3 py-1 rounded">Upload Partner Logo</button>
                        </div>
                      </div>
                    </div>

                    <div>
                      <label className="block text-sm font-medium mb-1">Other Asset</label>
                      <input type="text" id="otherAssetName" placeholder="Asset base name (e.g. partner_logo)" className="block w-full border rounded px-2 py-1 mb-2" />
                      <input type="file" id="otherAssetFile" accept="image/*" />
                    </div>
                  </div>

                  <div className="mt-6 space-y-6">
                    {['signature_president','signature_instructor','signature_collaborator'].map((key)=> (
                      <div key={`sig-${key}`} className="border rounded p-4">
                        <div className="flex items-center justify-between mb-2">
                          <div className="font-medium">{key === 'signature_president' ? 'President' : key === 'signature_instructor' ? 'Instructor' : 'Collaborator'} Signature</div>
                          <div className="text-sm text-slate-500">Preview below, draw or upload</div>
                        </div>
                        <div className="mb-3">
                            {key === 'signature_president' && (
                              <div className="mb-2">
                                <label className="block text-sm font-medium mb-1">President Printed Name</label>
                                <input id="president_printed_name" name="president_printed_name" defaultValue={modalAssets.president_name ?? 'Prud De Leon'} type="text" className="w-full border rounded px-2 py-1 text-sm" />
                              </div>
                            )}
                            {key === 'signature_collaborator' && (
                              <div className="mb-2">
                                <label className="block text-sm font-medium mb-1">Collaborator Printed Name</label>
                                <input id="collaborator_display_name" type="text" placeholder="Printed name to show under signature" className="w-full border rounded px-2 py-1 text-sm" defaultValue={modalAssets.collaborator_name ?? ''} />
                              </div>
                            )}
                            <canvas ref={(el)=>{ canvasRefs.current[key] = el; }} id={`canvas-${key}`} width={800} height={200} style={{border:'1px solid #e5e7eb', width:'100%', height:120}} />
                        </div>
                        <div className="flex items-center space-x-3">
                          <button type="button" onClick={()=>{ const c = canvasRefs.current[key]; if(!c) return; const ctx = c.getContext('2d'); ctx && ctx.clearRect(0,0,c.width,c.height); }} className="px-3 py-1 bg-gray-200 rounded">Clear</button>
                          <button type="button" onClick={async ()=>{
                            const c = canvasRefs.current[key]; if(!c) return; const dataUrl = c.toDataURL('image/png');
                            try{
                              const token = getCsrfToken();
                              const payload: any = { key: key, image: dataUrl };
                              if (key === 'signature_collaborator' || key === 'signature_president') {
                                const fieldId = key === 'signature_collaborator' ? 'collaborator_display_name' : 'president_printed_name';
                                const dn = (document.getElementById(fieldId) as HTMLInputElement)?.value || '';
                                if (dn) payload.display_name = dn;
                              }
                              const res = await fetch('/admin/certificate-assets/store',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token||'', 'Accept':'application/json'},body: JSON.stringify(payload)});
                              if (!res.ok) throw new Error('Save failed');
                              const json = await res.json();
                              setModalAssets((s:any)=>({ ...s, [key]: json.url }));
                              setAssets(await (await fetch('/admin/certificates/assets',{credentials:'include',headers:{'Accept':'application/json'}})).json());
                              alert('Signature saved');
                            }catch(err){ console.error(err); alert('Failed to save signature'); }
                          }} className="px-3 py-1 bg-green-600 text-white rounded">Save Signature</button>
                          <div className="ml-4 text-sm text-slate-600">Or upload file:</div>
                          <input type="file" name={`asset_${key}`} accept="image/*" onChange={async (e)=>{
                            const f = (e.target as HTMLInputElement).files?.[0]; if(!f) return;
                            try{
                              const token = getCsrfToken();
                              const fd = new FormData(); fd.append('file', f); fd.append('key', key);
                              if (key === 'signature_collaborator' || key === 'signature_president') {
                                const fieldId = key === 'signature_collaborator' ? 'collaborator_display_name' : 'president_printed_name';
                                const dn = (document.getElementById(fieldId) as HTMLInputElement)?.value || '';
                                if (dn) fd.append('display_name', dn);
                              }
                              const res = await fetch('/admin/certificate-assets/store',{method:'POST',credentials:'include',headers:{'X-CSRF-TOKEN': token||''},body: fd});
                              if(!res.ok) throw new Error('upload failed');
                              const j = await res.json();
                              setModalAssets((s:any)=>({ ...s, [key]: j.url }));
                              setAssets(await (await fetch('/admin/certificates/assets',{credentials:'include',headers:{'Accept':'application/json'}})).json());
                              alert('Uploaded');
                            }catch(err){ console.error(err); alert('Upload failed'); }
                          }} />
                        </div>
                      </div>
                    ))}
                  </div>

                  <div className="mt-4 flex items-center justify-end space-x-3">
                    <button type="button" onClick={()=>setShowAssetsModal(false)} className="px-3 py-2 rounded border">Cancel</button>
                    <button type="submit" disabled={uploading} className="bg-green-600 text-white px-4 py-2 rounded">Upload / Save</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="bg-white rounded shadow overflow-hidden">
        <div className="p-4 border-b flex items-center justify-between">
          <div className="text-sm text-slate-600">Showing {certificates.length} certificates</div>
          <div>
            <input type="search" placeholder="Search by user or course" className="border rounded px-2 py-1 text-sm" />
          </div>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Completed</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Issued</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificate ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {loading ? (
                <tr><td colSpan={5} className="p-6 text-center text-slate-500">Loading...</td></tr>
              ) : certificates.length === 0 ? (
                <tr><td colSpan={5} className="p-6 text-center text-slate-500">No certificates found.</td></tr>
              ) : (
                certificates.map((cert) => (
                  <tr key={cert.id}>
                    <td className="px-6 py-4 whitespace-nowrap">{cert.user?.fullname}</td>
                    <td className="px-6 py-4 whitespace-nowrap">{cert.course?.title}</td>
                    <td className="px-6 py-4 whitespace-nowrap">{(cert.certificate_data && cert.certificate_data.issued_at) || new Date(cert.created_at).toLocaleDateString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap">{(cert.certificate_data && cert.certificate_data.certificate_id) || cert.id}</td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <GenerateButton cert={cert} onDone={async ()=>{
                        // refresh list after generation
                        setLoading(true);
                        try{
                          const r = await fetch('/api/admin/certificates', { credentials: 'include', headers: { 'Accept': 'application/json' } });
                          if (r.ok) setCertificates(await r.json());
                        }catch(err){console.error(err)}finally{setLoading(false)}
                      }} />
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

function GenerateButton({ cert, onDone }: { cert: any, onDone?: ()=>void }){
  const [busy, setBusy] = useState(false);
  const generate = async () => {
    if (!cert.user?.id || !cert.course?.id) return;
    setBusy(true);
    try{
      const token = getCsrfToken();
      const res = await fetch('/admin/certificates/generate', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token || ''
        },
        body: JSON.stringify({ user_id: cert.user.id, course_id: cert.course.id })
      });
      if (!res.ok) {
        let errMsg = `Server responded with ${res.status}`;
        try {
          const json = await res.json();
          errMsg = json.error || JSON.stringify(json);
        } catch (e) {
          try { errMsg = await res.text(); } catch {}
        }
        throw new Error(errMsg);
      }
      // success: try to parse JSON, but gracefully handle HTML fallback
      let payload: any = null;
      try {
        payload = await res.json();
      } catch (e) {
        // Not JSON — try to get text and open in new tab if it looks like HTML
        try {
          const txt = await res.text();
          if (txt && txt.trim().startsWith('<')) {
            const w = window.open();
            if (w) {
              w.document.open();
              w.document.write(txt);
              w.document.close();
            } else {
              // fallback: notify user
              alert('Certificate generated (HTML). Please check the Certificates folder.');
            }
          }
        } catch (ie) { /* ignore */ }
      }
      // If server returned JSON with a download_url, open it
      if (payload && payload.download_url) {
        window.open(payload.download_url, '_blank');
      }
      if (onDone) await onDone();
      // show non-blocking success
      console.log('Certificate created', payload.certificate);
      alert('Certificate generated successfully.');
    }catch(err){
      console.error('Generate error:', err);
      alert('Failed to generate certificate: ' + (err.message || err));
    }finally{setBusy(false)}
  };

  return (
    <button onClick={(e)=>{e.preventDefault(); generate();}} disabled={busy} className="bg-blue-600 text-white px-3 py-1 rounded">
      {busy ? 'Generating...' : 'Generate Certificate'}
    </button>
  );
}
