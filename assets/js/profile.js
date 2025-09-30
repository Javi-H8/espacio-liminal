// assets/js/profile.js
// ================================================================
// Perfil · un solo script “apañao” para:
// - abrir/cerrar panes con data-edit / data-toggle (sin IDs duplicados)
// - enviar los cambios por fetch al endpoint único (update.php)
// - subir avatar (FormData) al endpoint avatar.php
// - pintar mensajes OK/ERROR y cerrar panes después
// ================================================================
(() => {
  const ok = (el,msg)=>{ if(!el)return; el.style.color='#6cf56c'; el.textContent=msg; };
  const ko = (el,msg)=>{ if(!el)return; el.style.color='#ff7676'; el.textContent=msg; };

  // Abrir/cerrar panes
  document.addEventListener('click', (ev)=>{
    const btn = ev.target.closest('[data-edit],[data-toggle]');
    if(!btn) return;
    const field = btn.getAttribute('data-edit') || btn.getAttribute('data-toggle');
    const pane = document.querySelector(`[data-field="${field}"] .edit-pane`);
    const ctrl = document.querySelector(`[data-edit="${field}"]`);
    if(!pane) return;
    pane.classList.toggle('open');
    if(ctrl) ctrl.setAttribute('aria-expanded', pane.classList.contains('open')?'true':'false');
  });

  // Guardar (submit) – endpoint único
  document.addEventListener('submit', async (ev)=>{
    const form = ev.target.closest('form[data-submit]');
    if(!form) return;
    ev.preventDefault();

    const action = form.getAttribute('data-submit'); // name|phone|gender|locale|category|password
    const msg = form.querySelector('.msg'); if(msg){ msg.textContent='Guardando...'; msg.style.color='#ddd'; }

    // preparo body con lo justo (así no envío basura)
    const fd = new FormData(form);
    const payload = { csrf: (fd.get('csrf')||'').toString(), action };

    if(action==='name')     payload.name   = (fd.get('name')||'').toString().trim();
    if(action==='phone')    payload.phone  = (fd.get('phone')||'').toString().trim();
    if(action==='gender')   payload.gender = (fd.get('gender')||'').toString();
    if(action==='locale')   payload.locale = (fd.get('locale')||'').toString();
    if(action==='category') payload.preferred_category = (fd.get('preferred_category')||'').toString();
    if(action==='password'){
      payload.current = (fd.get('current')||'').toString();
      payload.new     = (fd.get('new')||'').toString();
      payload.new2    = (fd.get('new2')||'').toString();
    }

    try{
      const res = await fetch('api/profile/update.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        body: JSON.stringify(payload)
      });
      const r = await res.json().catch(()=>null);
      if(!r || !r.ok){ ko(msg, (r && r.error) ? r.error : 'No se pudo guardar'); return; }

      ok(msg, 'Guardado ');

      // actualizo el valor visible en la fila
      const field = form.closest('[data-field]')?.getAttribute('data-field');
      const out   = field ? document.querySelector(`[data-value="${field}"]`) : null;
      if(out){
        if(field==='name')     out.textContent = payload.name;
        if(field==='phone')    out.textContent = payload.phone || '—';
        if(field==='gender')   out.textContent = payload.gender || '—';
        if(field==='locale')   out.textContent = (payload.locale||'es').toUpperCase();
        if(field==='category') out.textContent = payload.preferred_category || 'todos';
      }

      // cierro el pane tras 400ms
      setTimeout(()=>{
        form.closest('.edit-pane')?.classList.remove('open');
        if(msg) msg.textContent='';
        form.reset?.();
      }, 400);

    }catch(e){
      ko(msg, 'Error de red');
    }
  });

  // Subida de avatar (form con [data-avatar])
  document.addEventListener('submit', async (ev)=>{
    const form = ev.target.closest('form[data-avatar]');
    if(!form) return;
    ev.preventDefault();
    const msg = form.querySelector('.msg'); if(msg){ msg.textContent='Subiendo...'; msg.style.color='#ddd'; }

    try{
      const res = await fetch('api/profile/avatar.php', { method:'POST', credentials:'same-origin', body:new FormData(form) });
      const r = await res.json().catch(()=>null);
      if(!r || !r.ok){ ko(msg, (r && r.error) ? r.error : 'No se pudo subir'); return; }
      ok(msg,'Avatar actualizado ');
      if(r.url){ const img = document.getElementById('avatarPreview'); if(img){ img.src = r.url + '?v=' + Date.now(); } }
      setTimeout(()=>{ if(msg) msg.textContent=''; },400);
    }catch(e){
      ko(msg,'Error de red');
    }
  });
})();
