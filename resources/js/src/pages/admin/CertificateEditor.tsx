import React, { useEffect, useRef, useState } from 'react';

interface Props { certificateId?: string | null }

export default function CertificateEditor({ certificateId }: Props){
  const containerRef = useRef<HTMLDivElement | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(()=>{
    if(!certificateId) return;
    const url = `/admin/certificates/${certificateId}/editor-partial`;
    let canceled = false;

    (async ()=>{
      try{
        const res = await fetch(url, { credentials: 'include', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if(!res.ok) throw new Error('Failed to load editor HTML');
        const html = await res.text();
        if(canceled) return;
        if(containerRef.current){
          containerRef.current.innerHTML = html;
          // ensure existing elements are accessible to the editor script
          try{
            const els = JSON.parse((containerRef.current.querySelector('#elementsInput') as HTMLInputElement)?.value || '[]');
            (window as any).__CERT_EXISTING_ELEMENTS = els;
          }catch(e){}
          // load editor script
          const existing = document.querySelector('script[data-editor-js]');
          if(!existing){
            const s = document.createElement('script');
            s.src = '/js/certificate-editor.js';
            s.setAttribute('data-editor-js','1');
            document.body.appendChild(s);
          } else {
            // re-run if necessary by creating a new script node
            const s = document.createElement('script');
            s.src = '/js/certificate-editor.js';
            s.setAttribute('data-editor-js','1');
            document.body.appendChild(s);
          }
        }
      }catch(err){
        console.error(err);
      }finally{ if(!canceled) setLoading(false); }
    })();

    return ()=>{ canceled = true; };
  }, [certificateId]);

  return (
    <div className="max-w-7xl mx-auto">
      {loading && <div className="text-slate-600">Loading editor...</div>}
      <div ref={containerRef}></div>
    </div>
  );
}
