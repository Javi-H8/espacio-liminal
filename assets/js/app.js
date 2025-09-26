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
