/* ==========================================================================
   app.js — “centro de mandos” del front
   Cosas que hace:
   - Abre/cierra sheets (<dialog>) por data-atributos (sin lios de IDs raros)
   - Trap de foco suave y ESC para accesibilidad normalita pero efectiva
   - OTP mimado: avanza solo, backspace con cariño y pegar completo
   - Llama a la API con fetch POST JSON (sin dramas)
   - Guarda Idioma / Categoría / Password / Reset / OTP / Logout
   - Guarda también Nombre / Email (con OTP) / Teléfono / Género
   NOTA: mínimo de dependencias y todo en delegación para que no falle si cambias el DOM
   ========================================================================== */


/* ---------------------------------------------
   0) Mini helpers de batalla
   --------------------------------------------- */

// Selectores cortitos para no escribir siempre document.querySelector
const $  = (sel, ctx=document) => ctx.querySelector(sel);
const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

/**
 * Calcula la URL real de la API teniendo en cuenta el “base path”.
 * - Si en <head> pones: <script>window.APP_BASE="/espacio-liminal/";</script>
 *   esto se respeta aquí. Si no existe, asume "/".
 * - Acepta tanto 'api/...' como '/api/...'
 */
function apiUrl(path){
  const base = (window.APP_BASE || '/').replace(/\/+$/,'') + '/';
  return path.startsWith('/') ? base + path.slice(1) : base + path;
}

/**
 * POST JSON sin sorpresas.
 * - Siempre Content-Type: application/json
 * - Si la respuesta no es JSON válido, devolvemos un objeto {ok:false, error:'...'}
 * - Puedes interceptar aquí 401/403 globales si más adelante metes auth real.
 */
async function postJSON(url, data){
  const res = await fetch(apiUrl(url), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data || {})
    // credentials: 'include'  // descomenta si tu sesión va por cookie y estás en subdominios
  });
  try {
    return await res.json();
  } catch {
    return { ok:false, error:'Respuesta no válida del servidor' };
  }
}

/** Cierra el <dialog> del botón que lanzó la acción */
function closeDialog(btn){ btn.closest('dialog')?.close('ok'); }


/* ---------------------------------------------
   1) Sheets (<dialog>): abrir/cerrar con estilo
   --------------------------------------------- */

/**
 * Trap de foco: al abrir un dialog, que el tab no “escape” y
 * se mueva entre los focuseables de dentro. Sin florituras.
 */
function focusTrap(dlg){
  const selectors = 'a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])';
  const nodes = $$(selectors, dlg);
  if (!nodes.length) return;
  const first = nodes[0], last = nodes[nodes.length-1];

  dlg.addEventListener('keydown', (e)=>{
    if (e.key !== 'Tab') return;
    // SHIFT+TAB desde el primero → salta al último
    if (e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
    // TAB desde el último → vuelve al primero
    else if (!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
  }, { once:true }); // con once basta; al reabrir se vuelve a instalar
}

/** Abre por id (data-dialog-open="ID" → sin #) */
function openDialogById(id){
  const dlg = document.getElementById(id);
  if (!dlg || !dlg.showModal) return;
  dlg.showModal();
  focusTrap(dlg);
  // primer foco “amable”
  const first = $('input,button,[href],[tabindex]:not([tabindex="-1"])', dlg);
  first && first.focus();
}

/** Abre por selector CSS (compat con data-open="#id" antiguo) */
function openDialogBySelector(sel){
  const dlg = document.querySelector(sel);
  if (!dlg || !dlg.showModal) return;
  dlg.showModal();
  focusTrap(dlg);
  const first = $('input,button,[href],[tabindex]:not([tabindex="-1"])', dlg);
  first && first.focus();
}

/** Delegación global de clicks: abrir/cerrar sin atar eventos a cada botón */
document.addEventListener('click', (e)=>{
  // Nuevo patrón recomendado
  const openerNew = e.target.closest('[data-dialog-open]');
  if (openerNew){
    e.preventDefault();
    const id = openerNew.getAttribute('data-dialog-open');
    if (id) openDialogById(id);
    return;
  }
  // Compat con el viejo
  const openerOld = e.target.closest('[data-open]');
  if (openerOld){
    e.preventDefault();
    const sel = openerOld.getAttribute('data-open');
    if (sel) openDialogBySelector(sel);
    return;
  }
  // Cerrar el dialog (botoncito con data-dialog-close)
  const closer = e.target.closest('[data-dialog-close]');
  if (closer){
    e.preventDefault();
    closer.closest('dialog')?.close('cancel');
  }
});

// ESC cierra “bonito” (evitamos comportamiento raro de algunos navegadores)
$$('dialog').forEach(dlg=>{
  dlg.addEventListener('cancel', (ev)=>{ ev.preventDefault(); dlg.close('cancel'); });
});


/* ---------------------------------------------
   2) “Cambiar foto” (de momento demo)
   --------------------------------------------- */
$('#btnChangePhoto')?.addEventListener('click', ()=>{
  alert('Cambiar foto (demo)');
  // futuro: abrir file input, previsualizar, subir a /api/profile/upload-avatar.php …
});


/* ---------------------------------------------
   3) OTP con cariño (autoadvance, backspace, pegar)
   --------------------------------------------- */
(function initOTP(){
  // Hoja OTP “genérica” (la que usas para reset de password)
  const otpBox = $('#sheetOTP .otp');
  if (!otpBox) return;

  const inputs = $$('.otp__digit', otpBox);

  inputs.forEach((inp, idx)=>{
    // 1 número y avanza
    inp.addEventListener('input', ()=>{
      inp.value = (inp.value || '').replace(/\D/g,'').slice(0,1);
      if (inp.value && inputs[idx+1]) inputs[idx+1].focus();
    });
    // backspace en vacío → vuelve atrás
    inp.addEventListener('keydown', (ev)=>{
      if (ev.key === 'Backspace' && !inp.value && inputs[idx-1]) inputs[idx-1].focus();
    });
  });

  // pegar código completo “1234”
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

// Sugerencia futura: WebOTP API (solo HTTPS, móvil y con SMS formateado)
// navigator.credentials.get({ otp: { transport: ['sms'] } }).then(c => { … });


/* ---------------------------------------------
   4) Compat forms antiguos (si queda alguno suelto)
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
   5) Acciones REALES — Perfil y compañía
   --------------------------------------------- */

/* === Guardar idioma ===
   - Espera <input type="radio" name="lang" …> marcado
   - Actualiza .js-lang-sub si existe en la vista */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-lang"]'); if(!b) return;
  e.preventDefault();

  const f = b.closest('form');
  const lang = f.querySelector('input[name="lang"]:checked')?.value;
  const csrf = f.elements['csrf']?.value || '';
  if (!lang) return alert('Selecciona un idioma');

  const r = await postJSON('api/profile/update-lang.php', { lang, csrf });
  if (r.ok){
    const map = { es:'Español', en:'Inglés', fr:'Francés', it:'Italiano' };
    { const el = $('.js-lang-sub'); if (el) el.textContent = map[lang] || lang; }
    closeDialog(b);
  } else alert(r.error || 'No se pudo guardar');
});

/* === Guardar categoría ===
   - Igual que idioma pero para name="cat"
   - Actualiza .js-cat-sub si existe */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-cat"]'); if(!b) return;
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
    { const el = $('.js-cat-sub'); if (el) el.textContent = map[cat] || cat; }
    closeDialog(b);
  } else alert(r.error || 'No se pudo guardar');
});

/* === Cambiar contraseña ===
   - Comprueba que coinciden y mínima longitud */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-password"]'); if(!b) return;
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

/* === Reset por email → abre hoja OTP genérica === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="reset-pass"]'); if(!b) return;
  e.preventDefault();

  const f     = b.closest('form');
  const email = f.elements['email'].value;
  const csrf  = f.elements['csrf']?.value || '';

  const r = await postJSON('api/profile/reset-password.php', { email, csrf });
  if (r.ok){ closeDialog(b); $('#sheetOTP')?.showModal(); }
  else alert(r.error || 'No se pudo enviar el código');
});

/* === Verificar OTP (hoja genérica) === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="verify-code"]'); if(!b) return;
  e.preventDefault();

  const inputs = $$('#sheetOTP .otp__digit');
  const code = inputs.map(i => i.value).join('');
  if (code.length < inputs.length) return alert('Completa el código');

  const r = await postJSON('api/profile/verify-code.php', { code });
  if (r.ok){ closeDialog(b); alert('Verificación correcta'); }
  else alert(r.error || 'Código incorrecto');
});

/* === Logout “de verdad” (endpoint) === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="logout"]'); if(!b) return;
  e.preventDefault();

  const r = await postJSON('api/auth/logout.php', {});
  if (r.ok){ location.href = (window.APP_BASE || '/'); }
  else alert('No se pudo cerrar sesión');
});

/* === Botón demo de logout antiguo (compat) === */
document.addEventListener('click', (e)=>{
  if (e.target?.id === 'btnLogout'){
    alert('Sesión cerrada (demo)');
    e.target.closest('dialog')?.close();
  }
});


/* ---------------------------------------------
   6) Acciones REALES — Perfil avanzado (nombre/email/teléfono/género)
   --------------------------------------------- */

/* Guardar nombre */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-name"]'); if(!b) return;
  e.preventDefault();

  const f = b.closest('form');
  const name = f.elements['name'].value.trim();
  if(!name) return alert('Escribe un nombre');

  const r = await postJSON('api/profile/update-name.php', { name });
  if(r.ok){
    { const el = $('.js-name'); if (el) el.textContent = name; }  
    closeDialog(b);
  } else alert(r.error || 'No se pudo guardar');
});

/* Solicitar OTP para cambiar email (paso 1) */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="request-email-otp"]'); if(!b) return;
  e.preventDefault();

  const f = b.closest('form');
  const email = f.elements['email'].value.trim();
  if(!email) return alert('Escribe un email válido');

  const r = await postJSON('api/profile/update-email-request.php', { email });
  if(r.ok){
    closeDialog(b);
    $('#sheetEmailOTP')?.showModal(); // paso 2: metemos el código
  } else alert(r.error || 'No se pudo enviar el código');
});

/* Confirmar OTP y aplicar cambio de email (paso 2) */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="confirm-email-otp"]'); if(!b) return;
  e.preventDefault();

  const inputs = [...document.querySelectorAll('#sheetEmailOTP .otp__digit')];
  const code = inputs.map(i=>i.value).join('');
  if(code.length < inputs.length) return alert('Completa el código');

  const r = await postJSON('api/profile/update-email-confirm.php', { code });
  if(r.ok){
    { const el = $('.js-email'); if (el) el.textContent = r.email; }
    closeDialog(b);
  } else alert(r.error || 'Código incorrecto');
});

/* Guardar teléfono */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-phone"]'); if(!b) return;
  e.preventDefault();

  const f = b.closest('form');
  const phone = f.elements['phone'].value.trim();
  if(!phone) return alert('Escribe un número');

  const r = await postJSON('api/profile/update-phone.php', { phone });
  if(r.ok){
    { const el = $('.js-phone'); if (el) el.textContent = phone; }
    closeDialog(b);
  } else alert(r.error || 'No se pudo guardar');
});

/* Guardar género */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-gender"]'); if(!b) return;
  e.preventDefault();

  const f = b.closest('form');
  const gender = f.querySelector('input[name="gender"]:checked')?.value;
  if(!gender) return alert('Selecciona una opción');

  const r = await postJSON('api/profile/update-gender.php', { gender });
  if(r.ok){
    { const el = $('.js-gender'); if (el) el.textContent = gender; }
    closeDialog(b);
  } else alert(r.error || 'No se pudo guardar');
});

// =====================================================
// NAV INFERIOR CON AUTOCULTADO SEGÚN SCROLL
// -----------------------------------------------------
// - Al bajar (scroll down) → escondo la nav para ganar espacio.
// - Al subir (scroll up)   → la muestro para navegar rápido.
// - Si estoy arriba del todo (y <= 0) → siempre visible.
// - Uso requestAnimationFrame para que vaya fino y no laggee.
// - Umbral (threshold) para evitar parpadeos en micro-scroll.
// =====================================================
(() => {
  const nav = document.getElementById('bottomNav');
  if (!nav) return; // si en esta vista no hay nav, salgo y no molesto

  let lastY = window.scrollY;  // guardo la posición anterior
  let ticking = false;         // bloqueo para no spamear el main thread
  const threshold = 8;         // 8px de margen (ajústalo si lo notas “nervioso”)

  // Lógica principal: decidir si escondo o muestro
  const applyAutoHide = () => {
    const y = window.scrollY;
    const delta = y - lastY;

    // 1) En la parte de arriba del todo → la nav SIEMPRE visible
    if (y <= 0) {
      nav.classList.remove('is-hidden');
      lastY = y;
      return;
    }

    // 2) Si he bajado lo suficiente → ¡ocúltate, bicho!
    if (delta > threshold) {
      nav.classList.add('is-hidden');
      lastY = y;
      return;
    }

    // 3) Si he subido lo suficiente → asoma otra vez
    if (delta < -threshold) {
      nav.classList.remove('is-hidden');
      lastY = y;
      return;
    }

    // 4) Si el movimiento es mínimo, actualizo lastY y no hago nada más
    lastY = y;
  };

  // Listener de scroll con rAF para que vaya mantequilla
  window.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      applyAutoHide();
      ticking = false;
    });
  }, { passive: true });

  // BONUS: si se cambia de orientación o se hace zoom raro, recalculo
  window.addEventListener('resize', () => {
    lastY = window.scrollY;
    nav.classList.remove('is-hidden');
  });
})();
