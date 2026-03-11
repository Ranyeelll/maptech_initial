// Certificate editor script extracted from Blade view
(function(){
    function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
    function loadScript(src){ return new Promise((res, rej)=>{ const s=document.createElement('script'); s.src=src; s.async=true; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); }

    ready(async function(){
        const canvasEl = document.getElementById('fabricCanvas'); if(!canvasEl) return;

        if(typeof window.fabric === 'undefined'){
            try{ await loadScript('https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js'); }catch(e){ console.error('Failed to load Fabric.js', e); return; }
        }

        // compute available width from wrapper so canvas fits without horizontal scroll
        const innerWrapper = document.getElementById('canvasInner') || document.getElementById('canvasContainer');
        const available = innerWrapper ? Math.max(400, Math.floor(innerWrapper.clientWidth)) : 900;
        let canvasWidth = Math.min(1000, available);
        let canvasHeight = Math.round(canvasWidth * 0.711); // maintain ~900x640 ratio

        const canvas = new fabric.Canvas('fabricCanvas', {
            preserveObjectStacking: true,
            selection: true
        });
        canvas.setWidth(canvasWidth); canvas.setHeight(canvasHeight);
        canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));

        // handle window resize: scale canvas and objects to fit available width
        let lastCanvasWidth = canvasWidth;
        function debounce(fn, wait){ let t; return (...args)=>{ clearTimeout(t); t = setTimeout(()=>fn(...args), wait); }; }
        function resizeCanvasToAvailable(){
            const inner = document.getElementById('canvasInner') || document.getElementById('canvasContainer');
            if(!inner) return;
            const avail = Math.max(400, Math.floor(inner.clientWidth));
            const newWidth = Math.min(1000, avail);
            if(newWidth === lastCanvasWidth) return;
            const scale = newWidth / lastCanvasWidth;
            const newHeight = Math.round(newWidth * 0.711);
            // scale objects
            canvas.getObjects().forEach(o=>{
                // scale position
                o.left = (o.left || 0) * scale;
                o.top = (o.top || 0) * scale;
                // scale sizes
                if(o.scaleX !== undefined) o.scaleX = (o.scaleX || 1) * scale;
                if(o.scaleY !== undefined) o.scaleY = (o.scaleY || 1) * scale;
                if(o.fontSize) o.fontSize = Math.max(8, Math.round(o.fontSize * scale));
                o.setCoords && o.setCoords();
            });
            canvas.setWidth(newWidth); canvas.setHeight(newHeight);
            lastCanvasWidth = newWidth;
            canvas.requestRenderAll();
        }
        window.addEventListener('resize', debounce(resizeCanvasToAvailable, 150));

        // utility to create editable text
        function addEditableText(text, opts = {}){
            const defaults = { left: (canvas.getWidth()/2), top: (canvas.getHeight()/2), originX:'center', originY:'center', fontSize:24, fill:'#111827', selectable:true, editable:true };
            const cfg = Object.assign({}, defaults, opts);
            const itext = new fabric.IText(text, cfg);
            itext.setControlsVisibility({ mtr:true });
            canvas.add(itext).setActiveObject(itext);
            canvas.requestRenderAll();
            return itext;
        }

        function addImageFromDataUrl(dataUrl, opts = {}){
            fabric.Image.fromURL(dataUrl, function(img){
                img.set({ left: opts.left || (canvas.getWidth()/2), top: opts.top || 120, originX:'center', originY:'center' });
                if(opts.width) img.scaleToWidth(opts.width);
                img.hasControls = true; img.selectable = true;
                canvas.add(img).setActiveObject(img);
                canvas.requestRenderAll();
            });
        }

        // Template builder: title, recipient, course, date, logo placeholder
        function buildTemplate(kind='classic'){
            if(kind === 'default') kind = 'classic';
            canvas.clear(); canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));

            const recipientVal = document.getElementById('recipient')?.value || 'Recipient Name';
            const courseVal = document.getElementById('courseName')?.value || 'Course Title';
            const dateVal = document.getElementById('issuedAt')?.value || new Date().toLocaleDateString();

            if(kind === 'classic'){
                // decorative gold border
                const border = new fabric.Rect({ left:20, top:20, width: canvas.getWidth()-40, height: canvas.getHeight()-40, fill:'transparent', stroke:'#d4af37', strokeWidth:6, selectable:false, evented:false });
                const inner = new fabric.Rect({ left:36, top:36, width: canvas.getWidth()-72, height: canvas.getHeight()-72, fill:'#fff', selectable:false, evented:false });
                canvas.add(border); canvas.add(inner);

                addEditableText('Certificate of Completion', { top: 120, fontSize: 36, fontWeight: '700', fontFamily: 'Georgia' , fill:'#1f2937' });
                addEditableText('This certificate is proudly presented to', { top: 180, fontSize: 14, fontFamily: 'Georgia', fill:'#374151' });

                const recipient = addEditableText(recipientVal, { top: 320, fontSize: 28, fontFamily: 'Georgia', fill:'#111827' }); recipient.fieldName='recipient';
                const course = addEditableText(courseVal, { top: 380, fontSize: 18, fontFamily: 'Georgia', fill:'#374151' }); course.fieldName='course';
                const date = addEditableText(dateVal, { top: 440, fontSize: 14, fontFamily: 'Georgia', fill:'#6b7280' }); date.fieldName='date';

                // signature area (decorative)
                const sigRect = new fabric.Rect({ left: canvas.getWidth()-260, top: canvas.getHeight()-140, width:200, height:40, rx:4, ry:4, fill:'transparent', stroke:'#d1c4a6', strokeWidth:1, selectable:false, evented:false });
                const sigText = new fabric.Text('Signature', { left: canvas.getWidth()-200, top: canvas.getHeight()-135, fontSize:12, fill:'#374151', selectable:false });
                canvas.add(sigRect); canvas.add(sigText);
            } else if(kind === 'modern'){
                // modern minimal: strong top rule and centered content
                const topBar = new fabric.Rect({ left:0, top:0, width: canvas.getWidth(), height:26, fill:'#111827', selectable:false, evented:false });
                canvas.add(topBar);
                addEditableText('CERTIFICATE', { top: 80, fontSize: 20, fontFamily: 'Arial', fill:'#111827', fontWeight:700 });
                addEditableText('of Completion', { top: 110, fontSize: 30, fontFamily: 'Georgia', fill:'#111827' });

                const recipient = addEditableText(recipientVal, { top: 280, fontSize: 26, fontFamily: 'Arial', fill:'#0b1220' }); recipient.fieldName='recipient';
                const course = addEditableText(courseVal, { top: 340, fontSize: 16, fontFamily: 'Arial', fill:'#374151' }); course.fieldName='course';
                const date = addEditableText(dateVal, { top: 420, fontSize: 12, fontFamily: 'Arial', fill:'#6b7280' }); date.fieldName='date';

                // subtle frame
                const frame = new fabric.Rect({ left:30, top:30, width: canvas.getWidth()-60, height: canvas.getHeight()-60, fill:'transparent', stroke:'#e6e9ee', strokeWidth:2, selectable:false, evented:false });
                canvas.add(frame);
            } else if(kind === 'professional'){
                // professional with blue accent and right logo area
                const accent = new fabric.Rect({ left:0, top:0, width:12, height: canvas.getHeight(), fill:'#0b63a7', selectable:false, evented:false });
                canvas.add(accent);
                addEditableText('Certificate of Completion', { top: 120, fontSize: 32, fontFamily: 'Times New Roman', fill:'#0b2540' });

                const recipient = addEditableText(recipientVal, { top: 300, fontSize: 26, fontFamily: 'Times New Roman', fill:'#0b2540' }); recipient.fieldName='recipient';
                const course = addEditableText(courseVal, { top: 360, fontSize: 16, fontFamily: 'Times New Roman', fill:'#374151' }); course.fieldName='course';
                const date = addEditableText(dateVal, { top: 420, fontSize: 12, fontFamily: 'Times New Roman', fill:'#6b7280' }); date.fieldName='date';

                // placeholder logo area on right
                const logoBox = new fabric.Rect({ left: canvas.getWidth()-160, top: 80, width:120, height:80, fill:'#f3f6fb', stroke:'#cbdff0', rx:6, ry:6, selectable:false, evented:false });
                const logoTxt = new fabric.Text('Logo', { left: canvas.getWidth()-120, top: 110, fontSize:12, selectable:false });
                canvas.add(logoBox); canvas.add(logoTxt);
            } else if(kind === 'classic_golden'){
                // Classic Golden: ornate gold border, medallions, ribbon and warm background
                const border = new fabric.Rect({ left:12, top:12, width: canvas.getWidth()-24, height: canvas.getHeight()-24, fill:'transparent', stroke:'#b8872b', strokeWidth:10, rx:12, ry:12, selectable:false, evented:false });
                const inner = new fabric.Rect({ left:36, top:36, width: canvas.getWidth()-72, height: canvas.getHeight()-72, fill:'#fffaf2', selectable:false, evented:false });
                canvas.add(border); canvas.add(inner);

                // corner medallions
                function makeMedallion(left, top){
                    const outer = new fabric.Circle({ left:left, top:top, radius:20, fill:'#d4af37', selectable:false, evented:false });
                    const innerC = new fabric.Circle({ left:left+6, top:top+6, radius:12, fill:'#fff6ea', selectable:false, evented:false });
                    const grp = new fabric.Group([outer, innerC], { left:left, top:top, selectable:false, evented:false });
                    return grp;
                }
                canvas.add(makeMedallion(28,28));
                canvas.add(makeMedallion(canvas.getWidth()-68,28));
                canvas.add(makeMedallion(28,canvas.getHeight()-68));
                canvas.add(makeMedallion(canvas.getWidth()-68,canvas.getHeight()-68));

                addEditableText('Certificate of Achievement', { top: 120, fontSize: 36, fontFamily: 'Georgia', fill:'#3a2b1f' });
                addEditableText('Awarded to', { top: 180, fontSize: 14, fontFamily: 'Georgia', fill:'#7a6a54' });

                const recipient = addEditableText(recipientVal, { top: 320, fontSize: 30, fontFamily: 'Georgia', fill:'#2b1f12' }); recipient.fieldName='recipient';
                const course = addEditableText(courseVal, { top: 380, fontSize: 16, fontFamily: 'Georgia', fill:'#5b4b3a' }); course.fieldName='course';
                const date = addEditableText(dateVal, { top: 440, fontSize: 12, fontFamily: 'Georgia', fill:'#6b5b4a' }); date.fieldName='date';

                // bottom ribbon / signature area
                const ribbon = new fabric.Rect({ left: (canvas.getWidth()/2)-220, top: canvas.getHeight()-120, width:440, height:48, fill:'#d4af37', rx:6, ry:6, selectable:false, evented:false });
                canvas.add(ribbon);
                const ribbonText = new fabric.Text('Authorized Signature', { left: canvas.getWidth()-260, top: canvas.getHeight()-110, fontSize:14, fill:'#3a2b1f', selectable:false });
                canvas.add(ribbonText);
            } else if(kind === 'cisco'){
                // Cisco-style detailed certificate: title left, colored right decoration, recipient highlighted, description and bullet list, QR and signature
                const w = canvas.getWidth(), h = canvas.getHeight();
                canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));

                // right decorative shapes (stacked rounded rectangles)
                function addDecor(x, y, width, height, color, angle){
                    const r = new fabric.Rect({ left: x, top: y, width: width, height: height, rx: height/2, ry: height/2, fill: color, selectable:false, evented:false, angle: angle||0 });
                    canvas.add(r);
                }
                addDecor(w-160, 20, 140, 28, '#2db2a8', -18);
                addDecor(w-120, 70, 160, 36, '#2b9be3', -18);
                addDecor(w-100, 120, 180, 44, '#16a34a', -18);

                // Top-left small logo text
                const logoTxt = new fabric.Textbox('Cisco\nAcademy', { left: 28, top: 18, fontSize: 12, fontFamily:'Arial', fill:'#0b2540', fontWeight:700 });
                canvas.add(logoTxt);

                // Title
                addEditableText('Certificate of Course Completion', { left: 28, top: 48, originX:'left', originY:'top', fontSize:26, fontFamily:'Arial', fill:'#0b2540', fontWeight:700 });

                // Recipient (prominent, cyan/blue)
                const rec = addEditableText(recipientVal, { left: 28, top: 110, originX:'left', originY:'top', fontSize:28, fontFamily:'Georgia', fill:'#0da6d6' }); rec.fieldName='recipient';

                // Short description paragraph
                const desc = new fabric.Textbox('has successfully achieved student level credential for completing the course.', { left:28, top:160, width: w-220, fontSize:13, fontFamily:'Arial', fill:'#374151', selectable:false });
                canvas.add(desc);

                // Bulleted list (sample items) - use a Textbox with bullets
                const bullets = '• Explain components of a hierarchical network design.\n• Calculate numbers between decimal, binary, and hexadecimal systems.\n• Explain how IPv4 subnetting enables local area segmentation.';
                const list = new fabric.Textbox(bullets, { left:28, top:220, width: w-320, fontSize:12, fontFamily:'Arial', fill:'#374151', selectable:false, lineHeight:1.3 });
                canvas.add(list);

                // QR placeholder bottom-left
                const qrBox = new fabric.Rect({ left:28, top:h-120, width:100, height:100, fill:'#f3f4f6', stroke:'#e5e7eb', selectable:false, evented:false });
                const qrTxt = new fabric.Text('QR', { left:60, top:h-80, fontSize:10, selectable:false });
                canvas.add(qrBox); canvas.add(qrTxt);

                // Signature block bottom-right
                const sigLine = new fabric.Line([w-320, h-60, w-140, h-60], { stroke:'#111827', strokeWidth:1, selectable:false, evented:false });
                const signer = new fabric.Text('Laura Quintana\nVice President and General Manager', { left: w-320, top: h-58, fontSize:11, fontFamily:'Georgia', fill:'#111827', selectable:false });
                canvas.add(sigLine); canvas.add(signer);
            }
            canvas.requestRenderAll();
        }

        // Find field by fieldName
        function findField(name){ return canvas.getObjects().find(o => o.fieldName === name); }

        // Insert named field helper
        function insertField(name, defaultText){
            const existing = findField(name);
            if(existing){ canvas.setActiveObject(existing); canvas.requestRenderAll(); return existing; }
            const obj = addEditableText(defaultText, { top: canvasHeight/2 }); obj.fieldName = name; return obj;
        }

        // Wire left toolbar (ids from Blade)
        const addTextBtn = document.getElementById('addText');
        const insertNameLeft = document.getElementById('insertNameFieldLeft');
        const insertCourseLeft = document.getElementById('insertCourseFieldLeft');
        const insertDateLeft = document.getElementById('insertDateFieldLeft');
        const chooseLogoLeft = document.getElementById('chooseLogoLeft');
        const uploadLogoLeft = document.getElementById('uploadLogoLeft');
        const resetLeft = document.getElementById('resetCanvasLeft');
        const templateSelect = document.getElementById('templateSelect');
        const exportPngBtn = document.getElementById('exportPng');
        const exportPdfBtn = document.getElementById('exportPdf');
        const elementsInput = document.getElementById('elementsInput');
        const form = document.getElementById('editorForm');

        if(addTextBtn) addTextBtn.addEventListener('click', ()=> addEditableText('New Text', { fontSize: 20 }));
        if(insertNameLeft) insertNameLeft.addEventListener('click', ()=> insertField('recipient', document.getElementById('recipient')?.value || 'Recipient Name'));
        if(insertCourseLeft) insertCourseLeft.addEventListener('click', ()=> insertField('course', document.getElementById('courseName')?.value || 'Course Title'));
        if(insertDateLeft) insertDateLeft.addEventListener('click', ()=> insertField('date', document.getElementById('issuedAt')?.value || new Date().toLocaleDateString()));
        if(chooseLogoLeft && uploadLogoLeft) chooseLogoLeft.addEventListener('click', ()=> uploadLogoLeft.click());
        if(uploadLogoLeft) uploadLogoLeft.addEventListener('change', (ev)=>{ const f = ev.target.files[0]; if(!f) return; const r = new FileReader(); r.onload = e=> addImageFromDataUrl(e.target.result, { width: 160 }); r.readAsDataURL(f); });
        if(resetLeft) resetLeft.addEventListener('click', ()=> buildTemplate(templateSelect?.value || 'default'));
        if(templateSelect) templateSelect.addEventListener('change', ()=> buildTemplate(templateSelect.value));

        // Template thumbnails click handlers
        const thumbs = document.querySelectorAll('.template-thumb');
        thumbs.forEach(btn=>{
            btn.addEventListener('click', (e)=>{
                const t = btn.getAttribute('data-template');
                if(t && templateSelect){ templateSelect.value = t; }
                buildTemplate(t);
                // highlight active
                thumbs.forEach(b=> b.classList.remove('ring-2','ring-indigo-400'));
                btn.classList.add('ring-2','ring-indigo-400');
            });
        });

        // Selection sync with properties panel
        const propText = document.getElementById('propText');
        const propFontSize = document.getElementById('propFontSize');
        const propColor = document.getElementById('propColor');
        const elementProps = document.getElementById('elementProps');
        const removeElementBtn = document.getElementById('removeElement');

        let activeObj = null;
        function showElementProps(obj){
            activeObj = obj;
            if(!obj) { elementProps.style.display = 'none'; return; }
            elementProps.style.display = '';
            propText.value = obj.text || '';
            propFontSize.value = obj.fontSize || 24;
            propColor.value = obj.fill || '#111827';
        }

        canvas.on('selection:created', function(e){ showElementProps(e.selected && e.selected[0] ? e.selected[0] : null); });
        canvas.on('selection:updated', function(e){ showElementProps(e.selected && e.selected[0] ? e.selected[0] : null); });
        canvas.on('selection:cleared', function(){ showElementProps(null); });

        if(propText) propText.addEventListener('input', ()=>{ if(activeObj){ activeObj.text = propText.value; canvas.requestRenderAll(); } });
        if(propFontSize) propFontSize.addEventListener('input', ()=>{ if(activeObj){ activeObj.set('fontSize', parseInt(propFontSize.value) || 12); canvas.requestRenderAll(); } });
        if(propColor) propColor.addEventListener('input', ()=>{ if(activeObj){ activeObj.set('fill', propColor.value); canvas.requestRenderAll(); } });
        if(removeElementBtn) removeElementBtn.addEventListener('click', ()=>{ if(activeObj){ canvas.remove(activeObj); activeObj = null; elementProps.style.display='none'; } });

        // Sync top-level properties (recipient, course, issuedAt) to any matching field objects
        function syncTopFields(){
            const recVal = document.getElementById('recipient')?.value || '';
            const courseVal = document.getElementById('courseName')?.value || '';
            const dateVal = document.getElementById('issuedAt')?.value || '';
            const r = findField('recipient'); if(r) { r.text = recVal; }
            const c = findField('course'); if(c) { c.text = courseVal; }
            const d = findField('date'); if(d) { d.text = dateVal; }
            canvas.requestRenderAll();
        }
        ['recipient','courseName','issuedAt'].forEach(id => { const el = document.getElementById(id); if(el) el.addEventListener('input', syncTopFields); });

        // Export helpers
        function downloadDataUrl(dataUrl, filename){ const a = document.createElement('a'); a.href = dataUrl; a.download = filename; document.body.appendChild(a); a.click(); a.remove(); }
        if(exportPngBtn) exportPngBtn.addEventListener('click', ()=>{ const dataUrl = canvas.toDataURL({ format:'png', multiplier:2 }); const filename = 'certificate_'+(document.getElementById('certificate_id')?.value||'export')+'.png'; downloadDataUrl(dataUrl, filename); });
        async function exportPdf(){ if(typeof window.jspdf === 'undefined' && typeof window.jsPDF === 'undefined'){ try{ await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js'); }catch(e){ alert('PDF export failed'); return; } }
            const jsPDFCtor = window.jspdf && window.jspdf.jsPDF ? window.jspdf.jsPDF : (window.jsPDF ? window.jsPDF : null);
            if(!jsPDFCtor){ alert('PDF export unavailable'); return; }
            const w = canvas.getWidth(), h = canvas.getHeight(); const dataUrl = canvas.toDataURL({ format:'png', multiplier:2 }); const doc = new jsPDFCtor({ unit:'px', format:[w,h] }); doc.addImage(dataUrl,'PNG',0,0,w,h); doc.save('certificate_'+(document.getElementById('certificate_id')?.value||'export')+'.pdf'); }
        if(exportPdfBtn) exportPdfBtn.addEventListener('click', exportPdf);

        // Keyboard delete/backspace
        document.addEventListener('keydown', (ev)=>{ if((ev.key==='Delete' || ev.key==='Backspace') && canvas.getActiveObject()){ canvas.remove(canvas.getActiveObject()); } });

        // Load existing elements or create template
        try{
            const existingMeta = window.__CERT_EXISTING_ELEMENTS || null;
            if(existingMeta && existingMeta.elements && Array.isArray(existingMeta.elements) && existingMeta.elements.length){
                canvas.clear(); canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));
                existingMeta.elements.forEach(el=>{
                    if(el.type === 'text'){
                        const o = new fabric.IText(el.text||'', { left: parseFloat(el.left)||100, top: parseFloat(el.top)||100, fontSize: el.fontSize||20, fill: el.color||'#111827', originX:'left', originY:'top' });
                        if(el.fieldName) o.fieldName = el.fieldName;
                        canvas.add(o);
                    } else if(el.type === 'image'){
                        fabric.Image.fromURL(el.src, img=>{ img.set({ left: parseFloat(el.left)||100, top: parseFloat(el.top)||40 }); if(el.width) img.scaleToWidth(parseFloat(el.width)); canvas.add(img); });
                    }
                });
            } else {
                buildTemplate(templateSelect?.value || 'default');
            }
        }catch(e){ console.error('Load existing elements error', e); buildTemplate('default'); }

        // serialize on submit
        if(form){ form.addEventListener('submit', ()=>{
            const objs = canvas.getObjects();
            const out = objs.map(o=>{
                if(o.type === 'image'){
                    return { type:'image', src: o.getSrc ? o.getSrc() : (o._element && o._element.src) || '', left: o.left||0, top: o.top||0, width: o.width ? (o.width*(o.scaleX||1)) : (o.getScaledWidth ? o.getScaledWidth() : null) };
                }
                return { type:'text', text: o.text||'', left: o.left||0, top: o.top||0, fontSize: o.fontSize||20, color: o.fill||'#111827', fieldName: o.fieldName || null };
            });
            const metadata = { recipient: document.getElementById('recipient')?.value || '', course: document.getElementById('courseName')?.value || '', issued_at: document.getElementById('issuedAt')?.value || '', certificate_id: document.getElementById('certificate_id')?.value || '', elements: out };
            if(elementsInput) elementsInput.value = JSON.stringify(metadata);
        }); }
    });
})();
