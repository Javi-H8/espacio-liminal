document.addEventListener("click",(e)=>{
  const openSel = e.target.closest("[data-open]");
  if(openSel){ document.querySelector(openSel.getAttribute("data-open"))?.showModal(); }
  if(e.target.matches("[data-close]")) e.target.closest("dialog")?.close();
  if(e.target.id==="btnLogout"){ alert("Sesión cerrada (demo)"); e.target.closest("dialog")?.close(); }
});

document.getElementById("btnChangePhoto")?.addEventListener("click", ()=>alert("Cambiar foto (demo)"));

const otp = document.querySelector("#formOtp");
if(otp){
  const inputs = otp.querySelectorAll("input");
  inputs.forEach((inp,idx)=>{
    inp.addEventListener("input",()=>{ if(inp.value && idx<inputs.length-1) inputs[idx+1].focus(); });
    inp.addEventListener("keydown",(ev)=>{ if(ev.key==="Backspace" && !inp.value && idx>0) inputs[idx-1].focus(); });
  });
}

["formForgot","formPassword","formIdioma","formCategoria"].forEach(id=>{
  document.getElementById(id)?.addEventListener("submit",(e)=>{
    e.preventDefault(); alert("Guardado (demo)"); e.target.closest("dialog")?.close();
  });
});
document.getElementById("formOtp")?.addEventListener("submit",(e)=>{ e.preventDefault(); alert("Código verificado (demo)"); e.target.closest("dialog")?.close(); });

// Utilidad POST JSON
async function postJSON(url, data){
  const res = await fetch(url, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(data||{})
  });
  return res.json().catch(()=>({ok:false,error:'Respuesta no válida'}));
}
function closeDialog(btn){ btn.closest('dialog')?.close('ok'); }

/* === Guardar idioma === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-lang"]'); if(!b) return;
  e.preventDefault();
  const f = b.closest('form');
  const lang = f.querySelector('input[name="lang"]:checked')?.value;
  const csrf = f.elements['csrf']?.value || '';
  if(!lang) return alert('Selecciona un idioma');
  const r = await postJSON('api/profile/update-lang.php', {lang, csrf});
  if(r.ok){
    const map={es:'Español',en:'Inglés',fr:'Francés',it:'Italiano'};
    document.querySelector('.js-lang-sub')?.textContent = map[lang]||lang;
    closeDialog(b);
  } else alert(r.error||'No se pudo guardar');
});

/* === Guardar categoría === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-cat"]'); if(!b) return;
  e.preventDefault();
  const f = b.closest('form');
  const cat = f.querySelector('input[name="cat"]:checked')?.value;
  const csrf = f.elements['csrf']?.value || '';
  if(!cat) return alert('Selecciona una categoría');
  const r = await postJSON('api/profile/update-category.php', {cat, csrf});
  if(r.ok){
    const map={naturaleza:'Naturaleza',ocio:'Ocio',aventura:'Aventura',cultural:'Cultural',todos:'Todos',custom:'Selección personalizada'};
    // si quieres reflejarlo en el hub, añade .js-cat-sub allí
    document.querySelector('.js-cat-sub')?.textContent = map[cat]||cat;
    closeDialog(b);
  } else alert(r.error||'No se pudo guardar');
});

/* === Cambiar contraseña === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="save-password"]'); if(!b) return;
  e.preventDefault();
  const f=b.closest('form');
  const old=f.elements['old'].value, n1=f.elements['new'].value, n2=f.elements['new2'].value;
  const csrf=f.elements['csrf']?.value||'';
  if(n1!==n2) return alert('Las contraseñas no coinciden');
  const r=await postJSON('api/profile/update-password.php',{old,n1,csrf});
  if(r.ok){ closeDialog(b); alert('Contraseña actualizada'); }
  else alert(r.error||'No se pudo guardar');
});

/* === Reset por email === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="reset-pass"]'); if(!b) return;
  e.preventDefault();
  const f=b.closest('form');
  const email=f.elements['email'].value, csrf=f.elements['csrf']?.value||'';
  const r=await postJSON('api/profile/reset-password.php',{email,csrf});
  if(r.ok){ closeDialog(b); document.getElementById('sheetOTP')?.showModal(); }
  else alert(r.error||'No se pudo enviar el código');
});

/* === Verificar OTP === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="verify-code"]'); if(!b) return;
  e.preventDefault();
  const inputs=[...document.querySelectorAll('#sheetOTP .otp__digit')];
  const code=inputs.map(i=>i.value).join('');
  if(code.length<inputs.length) return alert('Completa el código');
  const r=await postJSON('api/profile/verify-code.php',{code});
  if(r.ok){ closeDialog(b); alert('Verificación correcta'); }
  else alert(r.error||'Código incorrecto');
});

/* === Logout === */
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('[data-action="logout"]'); if(!b) return;
  e.preventDefault();
  const r = await postJSON('api/auth/logout.php',{});
  if(r.ok){ location.href = '/espacio-liminal/'; }
  else alert('No se pudo cerrar sesión');
});

