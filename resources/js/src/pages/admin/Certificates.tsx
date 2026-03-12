import React, { useEffect, useState } from 'react';

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
        <div className="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-40">
          <div className="bg-white rounded shadow-lg w-full max-w-2xl p-6">
            <div className="flex items-start justify-between mb-4">
              <div>
                <h3 className="text-lg font-semibold">Logos & Signatures</h3>
                <p className="text-sm text-slate-500">Upload or replace company logo and e-signatures.</p>
              </div>
              <div>
                <button onClick={()=>setShowAssetsModal(false)} className="text-slate-600 hover:text-slate-800">Close</button>
              </div>
            </div>

            <form onSubmit={async (e)=>{
              e.preventDefault();
              const form = e.currentTarget as HTMLFormElement;
              setUploading(true);
              try{
                const token = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
                // upload each selected file separately so backend mapping is simple
                const logo = (form.querySelector('input[name="asset_company_logo"]') as HTMLInputElement).files?.[0];
                if (logo){ const fd = new FormData(); fd.append('asset', logo); fd.append('type','logo'); await fetch('/admin/certificates/upload-asset',{method:'POST',credentials:'include',headers:{'X-CSRF-TOKEN':token||''},body:fd}); }
                const pres = (form.querySelector('input[name="asset_signature_president"]') as HTMLInputElement).files?.[0];
                if (pres){ const fd = new FormData(); fd.append('asset', pres); fd.append('type','signature_president'); await fetch('/admin/certificates/upload-asset',{method:'POST',credentials:'include',headers:{'X-CSRF-TOKEN':token||''},body:fd}); }
                const instr = (form.querySelector('input[name="asset_signature_instructor"]') as HTMLInputElement).files?.[0];
                if (instr){ const fd = new FormData(); fd.append('asset', instr); fd.append('type','signature_instructor'); await fetch('/admin/certificates/upload-asset',{method:'POST',credentials:'include',headers:{'X-CSRF-TOKEN':token||''},body:fd}); }
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
                  <label className="block text-sm font-medium mb-1">Company Logo (preview)</label>
                  <div className="mb-2 flex items-center gap-3">
                    <img src={modalLocalPreviews.company_logo || modalAssets.company_logo || assets.company_logo || '/assets/Maptech-Official-Logo.png'} alt="logo" className="h-12" />
                    <button type="button" onClick={()=>{ const el = document.querySelector('input[name="asset_company_logo"]') as HTMLInputElement; if(el) el.click(); }} className="px-2 py-1 bg-gray-100 border rounded text-sm">Change Logo</button>
                  </div>
                  <input type="file" name="asset_company_logo" accept="image/*" onChange={(e)=>{
                    const f = e.target.files?.[0];
                    if (f){
                      try{ if (localPreviewRefs.current.company_logo) URL.revokeObjectURL(localPreviewRefs.current.company_logo); }catch(e){}
                      const url = URL.createObjectURL(f);
                      localPreviewRefs.current.company_logo = url;
                      setModalLocalPreviews((s:any)=>({ ...s, company_logo: url }));
                    }
                  }} />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">President Signature (preview)</label>
                  <div className="mb-2"><img src={modalAssets.signature_president || assets.signature_president || '/assets/signature_president.png'} alt="pres" className="h-12" /></div>
                  <input type="file" name="asset_signature_president" accept="image/*" />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">Instructor Signature (preview)</label>
                  <div className="mb-2"><img src={modalAssets.signature_instructor || assets.signature_instructor || '/assets/signature_instructor.png'} alt="instr" className="h-12" /></div>
                  <input type="file" name="asset_signature_instructor" accept="image/*" />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1">Other Asset</label>
                  <input type="text" id="otherAssetName" placeholder="Asset base name (e.g. partner_logo)" className="block w-full border rounded px-2 py-1 mb-2" />
                  <input type="file" id="otherAssetFile" accept="image/*" />
                </div>
              </div>
              <div className="mt-4 flex items-center justify-end space-x-3">
                <button type="button" onClick={()=>setShowAssetsModal(false)} className="px-3 py-2 rounded border">Cancel</button>
                <button type="submit" disabled={uploading} className="bg-green-600 text-white px-4 py-2 rounded">Upload / Save</button>
              </div>
            </form>
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
      const token = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
      const res = await fetch('/admin/certificates/generate', {
        method: 'POST',
        credentials: 'include',
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
