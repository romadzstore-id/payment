const texts = {
  id:{
    title:"Payment Romadz Store",
    subtitle:"Halaman Pembayaran Resmi & Aman",
    intro:"Silakan lakukan pembayaran melalui metode berikut",
    copy:"Salin Nomor",
    download:"Download QRIS",
    info1:"Pastikan nominal pembayaran sesuai",
    info2:"Simpan bukti transfer Anda",
    info3:"Kesalahan transfer bukan tanggung jawab kami",
    confirmText:"Setelah melakukan pembayaran, silakan lakukan konfirmasi"
  },
  en:{
    title:"Payment Romadz Store",
    subtitle:"Official & Secure Payment Page",
    intro:"Please complete your payment using one of the methods below",
    copy:"Copy Number",
    download:"Download QR Code",
    info1:"Make sure the payment amount is correct",
    info2:"Keep your transfer receipt",
    info3:"Transfer mistakes are not our responsibility",
    confirmText:"After payment, please contact us for confirmation"
  }
};

let currentLang="id";

document.getElementById("langToggle").onclick=()=>{
  currentLang=currentLang==="id"?"en":"id";
  document.getElementById("langToggle").innerText=currentLang.toUpperCase();
  document.querySelectorAll("[data-id]").forEach(el=>{
    el.innerText=texts[currentLang][el.dataset.id];
  });
};

document.getElementById("themeToggle").onclick=()=>{
  document.body.classList.toggle("dark");
  document.body.classList.toggle("light");
};

function copyText(id,btn){
  navigator.clipboard.writeText(document.getElementById(id).innerText);
  btn.innerHTML="✔ Disalin";
  setTimeout(()=>btn.innerHTML='<i class="fa-regular fa-copy"></i> Salin Nomor',1500);
}

function downloadQRIS(){
  const link=document.createElement("a");
  link.href="qris.png";
  link.download="QRIS-RomadzStore.png";
  link.click();
}