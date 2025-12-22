// ============================================
// DATA TRANSLATION
// ============================================
const translations = {
    id: {
        pageTitle: "Payment Romadz Store",
        pageSubtitle: "Halaman Pembayaran Resmi & Aman",
        btnCopy: "Salin Nomor",
        btnCopied: "Berhasil Disalin!",
        scanText: "Scan QRIS di atas dengan E-Wallet",
        btnDownload: "Download QRIS",
        infoTitle: "Petunjuk Penting",
        info1: "Pastikan nominal transfer sesuai.",
        info2: "Simpan bukti transfer sebagai referensi.",
        info3: "Kesalahan transfer bukan tanggung jawab kami.",
        confirmText: "Setelah melakukan pembayaran, silakan lakukan konfirmasi.",
        langBtn: "EN"
    },
    en: {
        pageTitle: "Romadz Store Payment",
        pageSubtitle: "Official & Secure Payment Page",
        btnCopy: "Copy Number",
        btnCopied: "Copied!",
        scanText: "Scan QRIS above with any E-Wallet",
        btnDownload: "Download QRIS",
        infoTitle: "Important Instructions",
        info1: "Ensure the transfer amount is correct.",
        info2: "Keep the transfer proof for reference.",
        info3: "We are not responsible for transfer errors.",
        confirmText: "After completing payment, please confirm below.",
        langBtn: "ID"
    }
};

// ============================================
// STATE MANAGEMENT
// ============================================
let currentLang = 'id';
let currentTheme = localStorage.getItem('theme') || 'light';

// Elements
const themeToggle = document.getElementById('theme-toggle');
const themeIcon = themeToggle.querySelector('i');
const langToggle = document.getElementById('lang-toggle');
const langText = document.getElementById('lang-text');
const translatableElements = document.querySelectorAll('[data-i18n]');

// ============================================
// FUNCTIONS
// ============================================

// 1. Initialize Theme
function initTheme() {
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
    }
}

// 2. Toggle Theme
themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    
    // Add simple rotation animation to icon
    themeIcon.style.transform = "rotate(360deg)";
    setTimeout(() => themeIcon.style.transform = "rotate(0deg)", 300);

    if (document.body.classList.contains('dark-mode')) {
        currentTheme = 'dark';
        themeIcon.classList.replace('fa-moon', 'fa-sun');
    } else {
        currentTheme = 'light';
        themeIcon.classList.replace('fa-sun', 'fa-moon');
    }
    localStorage.setItem('theme', currentTheme);
});

// 3. Toggle Language
langToggle.addEventListener('click', () => {
    currentLang = currentLang === 'id' ? 'en' : 'id';
    updateLanguage();
    
    // Button animation
    langToggle.style.transform = "scale(0.9)";
    setTimeout(() => langToggle.style.transform = "scale(1)", 150);
});

function updateLanguage() {
    langText.textContent = translations[currentLang].langBtn;
    
    translatableElements.forEach(el => {
        const key = el.getAttribute('data-i18n');
        
        // Handle fade effect for text change
        el.style.opacity = 0;
        setTimeout(() => {
            el.textContent = translations[currentLang][key];
            el.style.opacity = 1;
        }, 200);
    });
}

// 4. Copy to Clipboard
const copyButtons = document.querySelectorAll('.copy-btn');

copyButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const number = btn.getAttribute('data-number');
        const originalText = btn.innerHTML;
        
        // Copy logic
        navigator.clipboard.writeText(number).then(() => {
            // Visual Feedback
            btn.innerHTML = `<i class="fa-solid fa-check"></i> ${translations[currentLang].btnCopied}`;
            btn.style.backgroundColor = "var(--success)";
            btn.style.color = "white";
            
            // Revert after 2 seconds
            setTimeout(() => {
                btn.innerHTML = `<i class="fa-regular fa-copy"></i> ${translations[currentLang].btnCopy}`;
                btn.style.backgroundColor = ""; // Reset to CSS default
                btn.style.color = "";
            }, 2000);
        }).catch(err => {
            console.error('Gagal menyalin: ', err);
        });
    });
});

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    // Add transitions to text elements specifically for language switch
    translatableElements.forEach(el => el.style.transition = "opacity 0.2s ease");
});
