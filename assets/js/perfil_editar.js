// assets/js/perfil_editar.js
// ======================================================================
// Perfil · toggles + fetch, PERO ahora apuntando a las rutas correctas:
//   - UPDATE:  api/profile/update.php   (antes llamaba a /api/perfil/actualizar.php y daba 404)
//   - AVATAR:  api/profile/avatar.php
// ======================================================================
(() => {
  const ok  = (el,msg)=>{ if(!el)return; el.style.color='#6cf56c'; el.textContent=msg; };
  const ko  = (el,msg)=>{ if(!el)return; el.style.color='#ff7676'; el.textContent=msg; };

  // Etiquetas bonitas (la BBDD guarda ENUMs, yo aquí pinto en “humano”)
  const GENDER_LABEL   = { male:'Masculino', female:'Femenino', unspecified:'Sin especificar', other:'Otro' };
  const CATEGORY_LABEL = { naturaleza:'Naturaleza', ocio:'Ocio', aventura:'Aventura', cultural:'Cultural', todos:'Todos', custom:'Personalizada' };

  // *** IMPORTANTÍSIMO: rutas buenas para no volver a ver 404 ***
  const API_UPDATE = 'api/profile/update.php';   // <- aquí está tu PHP de update
  const API_AVATAR = 'api/profile/avatar.php';   // <- y aquí el de avatar

  // Abre/cierra panes (delegación para no liarnos con IDs)
  document.addEventListener('click', (ev)=>{
    const btn = ev.target.closest('[data-edit],[data-toggle]'); if(!btn) return;
    const field = btn.getAttribute('data-edit') || btn.getAttribute('data-toggle');
    const pane  = document.querySelector(`[data-field="${field}"] .edit-pane`);
    const ctrl  = document.querySelector(`[data-edit="${field}"]`);
    if(!pane) return;
    pane.classList.toggle('open');
    if(ctrl) ctrl.setAttribute('aria-expanded', pane.classList.contains('open')?'true':'false');
  });

  // Guardar cualquier campo (un solo endpoint = un solo fetch = menos drama)
  document.addEventListener('submit', async (ev)=>{
    const form = ev.target.closest('form[data-submit]'); if(!form) return;
    ev.preventDefault();

    const action = form.getAttribute('data-submit'); // name|phone|gender|locale|category|password
    const msg = form.querySelector('.msg'); if(msg){ msg.textContent='Guardando...'; msg.style.color='#ddd'; }

    // payload variable según lo que estoy guardando
    const fd = new FormData(form);
    const payload = { csrf:(fd.get('csrf')||'').toString(), action };

    if(action==='name')     payload.name   = (fd.get('name')||'').toString().trim();
    if(action==='phone')    payload.phone  = (fd.get('phone')||'').toString().trim();
    if(action==='gender')   payload.gender = (fd.get('gender')||'').toString();                 // ENUM real: male|female|unspecified|other
    if(action==='locale')   payload.locale = (fd.get('locale')||'').toString();                 // es|en|fr|it
    if(action==='category') payload.preferred_category = (fd.get('preferred_category')||'').toString(); // ENUM real: naturaleza|...
    if(action==='password'){
      payload.current = (fd.get('current')||'').toString();
      payload.new     = (fd.get('new')||'').toString();
      payload.new2    = (fd.get('new2')||'').toString();
    }

    try{
      const res = await fetch(API_UPDATE, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        body: JSON.stringify(payload)
      });

      // Intento parsear JSON; si vuelve HTML (404/500), lo enseño tal cual (adiós a “respuesta no válida”)
      const ct = res.headers.get('content-type') || '';
      let r = null;
      if (ct.includes('application/json')) r = await res.json().catch(()=>null);
      else { const txt = await res.text(); throw new Error('Respuesta no JSON: ' + txt.slice(0,200)); }

      if(!r || !r.ok){ ko(msg, (r && r.error) ? r.error : 'No se pudo guardar'); return; }

      ok(msg, 'Guardado');

      // refresco visual
      const field = form.closest('[data-field]')?.getAttribute('data-field');
      const out   = field ? document.querySelector(`[data-value="${field}"]`) : null;
      if(out){
        if(field==='name')     out.textContent = payload.name;
        if(field==='phone')    out.textContent = payload.phone || '—';
        if(field==='gender')   out.textContent = GENDER_LABEL[payload.gender] || 'Sin especificar';
        if(field==='locale')   out.textContent = (payload.locale||'es').toUpperCase();
        if(field==='category') out.textContent = CATEGORY_LABEL[payload.preferred_category] || 'Todos';
      }

      setTimeout(()=>{ form.closest('.edit-pane')?.classList.remove('open'); if(msg) msg.textContent=''; form.reset?.(); },400);

    }catch(e){
      ko(msg, 'Servidor dijo: ' + (e.message || 'error'));
    }
  });

  // Subida de avatar (FormData sin tocar Content-Type)
  document.addEventListener('submit', async (ev)=>{
    const form = ev.target.closest('form[data-avatar]'); if(!form) return;
    ev.preventDefault();
    const msg = form.querySelector('.msg'); if(msg){ msg.textContent='Subiendo...'; msg.style.color='#ddd'; }

    try{
      const res = await fetch(API_AVATAR, { method:'POST', credentials:'same-origin', body: new FormData(form) });
      const ct  = res.headers.get('content-type') || '';
      let r = null;
      if (ct.includes('application/json')) r = await res.json().catch(()=>null);
      else { const t = await res.text(); throw new Error('Respuesta no JSON: ' + t.slice(0,200)); }

      if(!r || !r.ok){ ko(msg, (r && r.error) ? r.error : 'No se pudo subir'); return; }
      ok(msg, 'Avatar actualizado');
      if(r.url){ const img=document.getElementById('avatarPreview'); if(img) img.src = r.url + '?v=' + Date.now(); }
      setTimeout(()=>{ if(msg) msg.textContent=''; },400);
    }catch(e){
      ko(msg, 'Servidor dijo: ' + (e.message || 'error'));
    }
  });
})();
