/* ==========================================================================
   app.js – comportamiento común de UI
   - Apertura/cierre de sheets (dialog) con delegación
   - Trap de foco básico y ESC
   - OTP: autoadvance, backspace inteligente y pegar
   - Handlers para guardar (idioma / categoría / contraseña / reset / OTP / logout)
   - Utilidad postJSON
   ========================================================================== */

/* ---------------------------------------------
   0) Helpers chiquitos
   --------------------------------------------- */
const $  = (sel, ctx=document) => ctx.querySelector(sel);
const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

/** POST JSON “sin sorpresas” */
async function postJSON(url, data){
  const res = await fetch(url, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(data || {})
  });
  try { return await res.json(); }
  catch { return { ok:false, error:'Respuesta no válida del servidor' }; }
}

/** Cierra el dialog asociado a un botón/enlace */
function closeDialog(btn){ btn.closest('dialog')?.close('ok'); }

/* ---------------------------------------------
   1) Apertura / cierre de sheets (dialog)
   - data-dialog-open="ID" (recomendado)
   - data-open="#selector" (compatibilidad)
   - [data-dialog-close] para cerrar
   - ESC cierra y trap de foco suave
   --------------------------------------------- */
function focusTrap(dlg){
  const selectors = 'a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])';
  const nodes = $$(selectors, dlg);
  if (!nodes.length) return;
  const first = nodes[0], last = nodes[nodes.length-1];
  dlg.addEventListener('keydown', (e)=>{
    if (e.key === 'Tab') {
      if (e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
    }
  }, { once:true });
}

function openDialogById(id){
  const dlg = document.getElementById(id);
  if (!dlg || !dlg.showModal) return;
  dlg.showModal();
  focusTrap(dlg);
  const first = $('input,button,[href],[tabindex]:not([tabindex="-1"])', dlg);
  first && first.focus();
}
function openDialogBySelector(sel){
  const dlg = document.querySelector(sel);
  if (!dlg || !dlg.showModal) return;
  dlg.showModal();
  focusTrap(dlg);
  const first = $('input,button,[href],[tabindex]:not([tabindex="-1"])', dlg);
  first && first.focus();
}

document.addEventListener('click', (e)=>{
  const openerNew = e.target.closest('[data-dialog-open]');
  if (openerNew){
    e.preventDefault();
    const id = openerNew.getAttribute('data-dialog-open');
    if (id) openDialogById(id);
    return;
  }
  const openerOld = e.target.closest('[data-open]');
  if (openerOld){
    e.preventDefault();
    const sel = openerOld.getAttribute('data-open');
    if (sel) openDialogBySelector(sel);
    return;
  }
  const closer = e.target.closest('[data-dialog-close]');
  if (closer){
    e.preventDefault();
    closer.closest('dialog')?.close('cancel');
  }
});

// ESC cierra de forma limpia
$$('dialog').forEach(dlg=>{
  dlg.addEventListener('cancel', (ev)=>{ ev.preventDefault(); dlg.close('cancel'); });
});

/* ---------------------------------------------
   2) “Cambiar foto” (demo)
   --------------------------------------------- */
$('#btnChangePhoto')?.addEventListener('click', ()=>{ alert('Cambiar foto (demo)'); });

/* ---------------------------------------------
   3) OTP UX (autoadvance, backspace, pegar)
   --------------------------------------------- */
(function initOTP(){
  const otpBox = $('#sheetOTP .otp');
  if (!otpBox) return;
  const inputs = $$('.otp__digit', otpBox);

  inputs.forEach((inp, idx)=>{
    inp.addEventListener('input', ()=>{
      inp.value = (inp.value || '').replace(/\D/g,'').slice(0,1);
      if (inp.value && inputs[idx+1]) inputs[idx+1].focus();
    });
    inp.addEventListener('keydown', (ev)=>{
      if (ev.key === 'Backspace' && !inp.value && inputs[idx-1]) inputs[idx-1].focus();
    });
  });

  otpBox.addEventListener('paste', (e)=>{
    const t = (e.clipboardData || window.clipboardData).getData('text')
                .replace(/\D/g,'')
                .slice(0, inputs.length);
    if (!t) return;
    e.preventDefault();
    t.split('').forEach((d,i)=>{ inputs[i].value = d; });
    inputs[Math.min(t.length, inputs.length)-1].focus();
  });
})();

/* ---------------------------------------------
   4) Compat forms antiguos (si quedan)
   --------------------------------------------- */
['formForgot','formPassword','formIdioma','formCategoria'].forEach(id=>{
  const form = document.getElementById(id);
  if (!form) return;
  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    alert('Guardado (demo antiguo)');
    form.closest('dialog')?.close();
  });
});
document.getElementById('formOtp')?.addEventListener('submit', (e)=>{
  e.preventDefault();
  alert('Código verificado (demo antiguo)');
  e.target.closest('dialog')?.close();
});

/* ---------------------------------------------
   5) Acciones REALES de las sheets
   --------------------------------------------- */
// Guardar idioma
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-lang"]');
  if (!b) return;
  e.preventDefault();
  const f = b.closest('form');
  const lang = f.querySelector('input[name="lang"]:checked')?.value;
  const csrf = f.elements['csrf']?.value || '';
  if (!lang) return alert('Selecciona un idioma');

  const r = await postJSON('api/profile/update-lang.php', { lang, csrf });
  if (r.ok){
    const map = { es:'Español', en:'Inglés', fr:'Francés', it:'Italiano' };
    $('.js-lang-sub')?.textContent = map[lang] || lang;
    closeDialog(b);
  } else alert(r.error || 'No se pudo guardar');
});

// Guardar categoría
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-cat"]');
  if (!b) return;
  e.preventDefault();
  const f = b.closest('form');
  const cat  = f.querySelector('input[name="cat"]:checked')?.value;
  const csrf = f.elements['csrf']?.value || '';
  if (!cat) return alert('Selecciona una categoría');

  const r = await postJSON('api/profile/update-category.php', { cat, csrf });
  if (r.ok){
    const map = {
      naturaleza:'Naturaleza', ocio:'Ocio', aventura:'Aventura',
      cultural:'Cultural', todos:'Todos', custom:'Selección personalizada'
    };
    $('.js-cat-sub')?.textContent = map[cat] || cat;
    closeDialog(b);
  } else alert(r.error || 'No se pudo guardar');
});

// Cambiar contraseña
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-password"]');
  if (!b) return;
  e.preventDefault();
  const f   = b.closest('form');
  const old = f.elements['old'].value;
  const n1  = f.elements['new'].value;
  const n2  = f.elements['new2'].value;
  const csrf= f.elements['csrf']?.value || '';

  if (n1 !== n2) return alert('Las contraseñas no coinciden');
  if (!n1 || n1.length < 8) return alert('La contraseña debe tener al menos 8 caracteres');

  const r = await postJSON('api/profile/update-password.php', { old, n1, csrf });
  if (r.ok){ closeDialog(b); alert('Contraseña actualizada'); }
  else alert(r.error || 'No se pudo guardar');
});

// Reset por email → abre OTP
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="reset-pass"]');
  if (!b) return;
  e.preventDefault();
  const f     = b.closest('form');
  const email = f.elements['email'].value;
  const csrf  = f.elements['csrf']?.value || '';

  const r = await postJSON('api/profile/reset-password.php', { email, csrf });
  if (r.ok){ closeDialog(b); $('#sheetOTP')?.showModal(); }
  else alert(r.error || 'No se pudo enviar el código');
});

// Verificar OTP
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="verify-code"]');
  if (!b) return;
  e.preventDefault();
  const inputs = $$('#sheetOTP .otp__digit');
  const code = inputs.map(i => i.value).join('');
  if (code.length < inputs.length) return alert('Completa el código');

  const r = await postJSON('api/profile/verify-code.php', { code });
  if (r.ok){ closeDialog(b); alert('Verificación correcta'); }
  else alert(r.error || 'Código incorrecto');
});

// Logout
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="logout"]');
  if (!b) return;
  e.preventDefault();
  const r = await postJSON('api/auth/logout.php', {});
  if (r.ok){ location.href = '/espacio-liminal/'; }
  else alert('No se pudo cerrar sesión');
});

/* ---------------------------------------------
   6) Botón demo de logout antiguo (compat)
   --------------------------------------------- */
document.addEventListener('click', (e)=>{
  if (e.target?.id === 'btnLogout'){
    alert('Sesión cerrada (demo)');
    e.target.closest('dialog')?.close();
  }
});
