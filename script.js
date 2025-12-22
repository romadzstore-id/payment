// =========================================================================
// RomadzStore — Modern Payment Methods (New Concept)
// =========================================================================

// Configuration
const WA_NUMBER = "6285119509493";
const METHODS = [
    { 
        id: "dana",  
        name: "Dana",  
        logo: "https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_dana_blue.svg/512px-Logo_dana_blue.svg.png",  
        kind: "number",      
        number: "081239977516", 
        badge: "E‑Wallet",
        badgeType: "wallet"
    },
    { 
        id: "gopay", 
        name: "GoPay", 
        logo: "https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/GoPay_Logo.svg/512px-GoPay_Logo.svg.png", 
        kind: "number",      
        number: "081239977516", 
        badge: "E‑Wallet",
        badgeType: "wallet"
    },
    { 
        id: "ovo",   
        name: "OVO",   
        logo: "https://upload.wikimedia.org/wikipedia/commons/thumb/6/6a/OVO_%28logo%29.svg/512px-OVO_%28logo%29.svg.png",   
        kind: "unavailable", 
        badge: "Unavailable",
        badgeType: "unavailable"
    },
    { 
        id: "qris",  
        name: "QRIS",  
        logo: "https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/QRIS_logo.svg/512px-QRIS_logo.svg.png",  
        kind: "qris",        
        qrSrc: "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=RomadzStorePayment", 
        badge: "QR",
        badgeType: "qr"
    }
];

// State
let currentLanguage = 'id'; // 'id' or 'en'
let currentTheme = 'light'; // 'light' or 'dark'

// DOM Helper
function $(id) { 
    return document.getElementById(id); 
}

// ---------- Language Management ----------
function initLanguage() {
    const savedLanguage = localStorage.getItem('yop-language');
    if (savedLanguage) {
        currentLanguage = savedLanguage;
    } else {
        // Default to browser language if Indonesian or English
        const browserLang = navigator.language.substring(0, 2);
        currentLanguage = (browserLang === 'id' || browserLang === 'en') ? browserLang : 'id';
    }
    
    applyLanguage(currentLanguage);
    updateLanguageToggle();
}

function applyLanguage(lang) {
    currentLanguage = lang;
    localStorage.setItem('yop-language', lang);
    
    // Update all elements with data attributes
    document.querySelectorAll('[data-id], [data-en]').forEach(element => {
        const idText = element.getAttribute('data-id');
        const enText = element.getAttribute('data-en');
        
        if (lang === 'id' && idText) {
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.placeholder = idText;
            } else {
                element.textContent = idText;
            }
        } else if (lang === 'en' && enText) {
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.placeholder = enText;
            } else {
                element.textContent = enText;
            }
        }
    });
    
    // Update document language attribute
    document.documentElement.lang = lang;
}

function updateLanguageToggle() {
    const toggle = $('languageToggle');
    if (!toggle) return;
    
    const flag = toggle.querySelector('.lang-flag');
    const text = toggle.querySelector('.lang-text');
    
    if (currentLanguage === 'id') {
        flag.textContent = '🇮🇩';
        text.textContent = 'ID';
    } else {
        flag.textContent = '🇬🇧';
        text.textContent = 'EN';
    }
}

function initLanguageToggle() {
    const toggle = $('languageToggle');
    if (!toggle) return;
    
    toggle.addEventListener('click', () => {
        const newLang = currentLanguage === 'id' ? 'en' : 'id';
        applyLanguage(newLang);
        updateLanguageToggle();
        
        // Show language change toast
        toast(currentLanguage === 'id' ? 'Bahasa diubah ke Indonesia' : 'Language changed to English');
    });
}

// ---------- Theme Management ----------
function initTheme() {
    const savedTheme = localStorage.getItem('yop-theme');
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme) {
        currentTheme = savedTheme;
    } else {
        currentTheme = systemPrefersDark ? 'dark' : 'light';
    }
    
    applyTheme(currentTheme);
    updateThemeIcon();
}

function applyTheme(theme) {
    currentTheme = theme;
    localStorage.setItem('yop-theme', theme);
    
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

function updateThemeIcon() {
    const icon = $('themeIcon');
    if (!icon) return;
    
    if (currentTheme === 'dark') {
        icon.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
        icon.innerHTML = '<i class="fas fa-moon"></i>';
    }
}

function initThemeToggle() {
    const toggle = $('themeToggle');
    if (!toggle) return;
    
    toggle.addEventListener('click', () => {
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        applyTheme(newTheme);
        updateThemeIcon();
        
        // Show theme change toast
        toast(currentTheme === 'dark' 
            ? (currentLanguage === 'id' ? 'Tema diubah ke Gelap' : 'Theme changed to Dark') 
            : (currentLanguage === 'id' ? 'Tema diubah ke Terang' : 'Theme changed to Light'));
    });
    
    // Listen for system theme changes
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', (e) => {
        // Only update if no explicit theme is set
        if (!localStorage.getItem('yop-theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
            updateThemeIcon();
        }
    });
}

// ---------- Toast System ----------
function toast(message) {
    const viewport = $('toastViewport');
    if (!viewport) return;
    
    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = 'toast';
    toastEl.textContent = message;
    
    // Add to viewport
    viewport.appendChild(toastEl);
    
    // Remove after animation
    setTimeout(() => {
        toastEl.style.opacity = '0';
        toastEl.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toastEl.parentNode === viewport) {
                viewport.removeChild(toastEl);
            }
        }, 300);
    }, 3000);
}

// ---------- Utilities ----------
function makeWaLink(methodName) {
    let text;
    if (currentLanguage === 'id') {
        text = methodName 
            ? `Halo admin RomadzStore, saya ingin konfirmasi pembayaran via ${methodName}.`
            : "Halo admin RomadzStore, saya ingin konfirmasi pembayaran.";
    } else {
        text = methodName
            ? `Hello RomadzStore admin, I want to confirm payment via ${methodName}.`
            : "Hello RomadzStore admin, I want to confirm payment.";
    }
    
    return `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(text)}`;
}

async function copyToClipboard(text) {
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(text);
            return true;
        }
    } catch (e) {
        console.warn('Clipboard API failed:', e);
    }
    
    // Fallback for older browsers
    try {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.top = '-9999px';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        const successful = document.execCommand('copy');
        document.body.removeChild(textArea);
        return successful;
    } catch (e) {
        console.warn('Fallback copy failed:', e);
        return false;
    }
}

// ---------- Payment Methods Rendering ----------
function renderPaymentMethods() {
    const list = $('paymentList');
    if (!list) return;
    
    // Clear list
    list.innerHTML = '';
    
    const confirmBtn = $('confirmBtn');
    let openId = null;
    
    // Function to close all items
    const closeAll = () => {
        document.querySelectorAll('.payment-item').forEach(item => {
            item.dataset.open = 'false';
            const header = item.querySelector('.item-header');
            if (header) header.setAttribute('aria-expanded', 'false');
        });
        openId = null;
        
        // Reset confirmation button to generic message
        if (confirmBtn) {
            confirmBtn.href = makeWaLink(null);
        }
    };
    
    // Function to update confirmation button
    const updateConfirmButton = (methodName) => {
        if (!confirmBtn) return;
        confirmBtn.href = makeWaLink(methodName);
    };
    
    // Create each payment method item
    METHODS.forEach(method => {
        // Create item container
        const item = document.createElement('div');
        item.className = 'payment-item';
        item.dataset.open = 'false';
        item.id = `pm-${method.id}`;
        
        // Create header
        const header = document.createElement('button');
        header.className = 'item-header';
        header.type = 'button';
        header.setAttribute('aria-expanded', 'false');
        header.setAttribute('aria-controls', `panel-${method.id}`);
        
        // Logo
        const logoDiv = document.createElement('div');
        logoDiv.className = 'logo';
        const logoImg = document.createElement('img');
        logoImg.src = method.logo;
        logoImg.alt = `${method.name} logo`;
        logoImg.loading = 'lazy';
        logoDiv.appendChild(logoImg);
        
        // Main content
        const mainDiv = document.createElement('div');
        mainDiv.className = 'item-main';
        
        const nameDiv = document.createElement('div');
        nameDiv.className = 'item-name';
        nameDiv.textContent = method.name;
        
        // Badge
        if (method.badge) {
            const badge = document.createElement('span');
            badge.className = 'badge';
            badge.textContent = method.badge;
            badge.setAttribute('data-type', method.badgeType);
            nameDiv.appendChild(badge);
        }
        
        const subDiv = document.createElement('div');
        subDiv.className = 'item-sub';
        
        // Set subtitle based on method kind and language
        if (method.kind === 'number') {
            subDiv.textContent = currentLanguage === 'id' 
                ? 'Salin nomor tujuan, lalu bayar dari aplikasi Anda' 
                : 'Copy destination number, then pay from your app';
        } else if (method.kind === 'qris') {
            subDiv.textContent = currentLanguage === 'id'
                ? 'Scan QRIS dari aplikasi pembayaran'
                : 'Scan QRIS from your payment app';
        } else if (method.kind === 'unavailable') {
            subDiv.textContent = currentLanguage === 'id'
                ? 'Metode ini sedang tidak tersedia'
                : 'This method is currently unavailable';
        }
        
        mainDiv.appendChild(nameDiv);
        mainDiv.appendChild(subDiv);
        
        // Chevron
        const chevronDiv = document.createElement('div');
        chevronDiv.className = 'chev';
        chevronDiv.innerHTML = '<i class="fas fa-chevron-down"></i>';
        
        // Assemble header
        header.appendChild(logoDiv);
        header.appendChild(mainDiv);
        header.appendChild(chevronDiv);
        
        // Create panel
        const panel = document.createElement('div');
        panel.className = 'item-panel';
        panel.id = `panel-${method.id}`;
        panel.setAttribute('role', 'region');
        panel.setAttribute('aria-labelledby', `pm-${method.id}`);
        
        // Panel content based on method kind
        if (method.kind === 'number') {
            // Description
            const desc = document.createElement('p');
            desc.className = 'panel-text';
            desc.innerHTML = currentLanguage === 'id'
                ? `Gunakan nomor berikut untuk pembayaran melalui <strong>${method.name}</strong>.`
                : `Use the following number for payment via <strong>${method.name}</strong>.`;
            
            // Copy row
            const row = document.createElement('div');
            row.className = 'row';
            
            // Copy area
            const copyArea = document.createElement('div');
            copyArea.className = 'copy-area';
            copyArea.setAttribute('role', 'button');
            copyArea.tabIndex = 0;
            copyArea.setAttribute('aria-label', currentLanguage === 'id' 
                ? `Klik untuk salin nomor ${method.name}` 
                : `Click to copy ${method.name} number`);
            
            const code = document.createElement('code');
            code.textContent = method.number;
            
            const hint = document.createElement('span');
            hint.className = 'copy-hint';
            hint.textContent = currentLanguage === 'id' ? 'Klik untuk salin' : 'Click to copy';
            
            copyArea.appendChild(code);
            copyArea.appendChild(hint);
            
            // Copy button
            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'btn btn-primary btn-inline';
            copyBtn.textContent = currentLanguage === 'id' ? 'Salin' : 'Copy';
            
            // Copy function
            const performCopy = async () => {
                const success = await copyToClipboard(method.number);
                if (success) {
                    toast(currentLanguage === 'id' ? 'Nomor berhasil disalin!' : 'Number copied successfully!');
                    
                    // Visual feedback
                    copyArea.style.borderColor = 'var(--success-color)';
                    copyBtn.style.backgroundColor = 'var(--success-color)';
                    copyBtn.textContent = currentLanguage === 'id' ? 'Tersalin!' : 'Copied!';
                    
                    setTimeout(() => {
                        copyArea.style.borderColor = '';
                        copyBtn.style.backgroundColor = '';
                        copyBtn.textContent = currentLanguage === 'id' ? 'Salin' : 'Copy';
                    }, 2000);
                } else {
                    toast(currentLanguage === 'id' ? 'Gagal menyalin nomor' : 'Failed to copy number');
                }
            };
            
            copyArea.addEventListener('click', performCopy);
            copyArea.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    performCopy();
                }
            });
            
            copyBtn.addEventListener('click', performCopy);
            
            row.appendChild(copyArea);
            row.appendChild(copyBtn);
            
            panel.appendChild(desc);
            panel.appendChild(row);
        }
        else if (method.kind === 'unavailable') {
            const desc = document.createElement('p');
            desc.className = 'panel-text';
            desc.innerHTML = currentLanguage === 'id'
                ? `Metode <strong>${method.name}</strong> saat ini <strong>tidak tersedia</strong>.`
                : `The <strong>${method.name}</strong> method is currently <strong>unavailable</strong>.`;
            
            const row = document.createElement('div');
            row.className = 'row';
            
            const unavailableArea = document.createElement('div');
            unavailableArea.className = 'copy-area';
            unavailableArea.style.cursor = 'not-allowed';
            unavailableArea.innerHTML = `<span style="color: var(--text-muted)">${currentLanguage === 'id' ? 'Tidak tersedia' : 'Unavailable'}</span>`;
            
            const unavailableBtn = document.createElement('button');
            unavailableBtn.type = 'button';
            unavailableBtn.className = 'btn btn-secondary btn-inline';
            unavailableBtn.disabled = true;
            unavailableBtn.textContent = currentLanguage === 'id' ? 'Salin' : 'Copy';
            unavailableBtn.style.opacity = '0.5';
            unavailableBtn.style.cursor = 'not-allowed';
            
            row.appendChild(unavailableArea);
            row.appendChild(unavailableBtn);
            
            panel.appendChild(desc);
            panel.appendChild(row);
        }
        else if (method.kind === 'qris') {
            const desc = document.createElement('p');
            desc.className = 'panel-text';
            desc.textContent = currentLanguage === 'id'
                ? 'Scan QR berikut dari aplikasi pembayaran Anda.'
                : 'Scan the following QR from your payment app.';
            
            // QR Container
            const qrContainer = document.createElement('div');
            qrContainer.className = 'qr';
            
            const qrImg = document.createElement('img');
            qrImg.src = method.qrSrc;
            qrImg.alt = currentLanguage === 'id' ? 'Kode QRIS' : 'QRIS Code';
            qrImg.loading = 'lazy';
            
            qrContainer.appendChild(qrImg);
            
            // Action buttons
            const row = document.createElement('div');
            row.className = 'row';
            
            const openBtn = document.createElement('a');
            openBtn.className = 'btn btn-secondary btn-inline';
            openBtn.href = method.qrSrc;
            openBtn.target = '_blank';
            openBtn.rel = 'noopener noreferrer';
            openBtn.innerHTML = '<i class="fas fa-external-link-alt"></i> ' + (currentLanguage === 'id' ? 'Buka' : 'Open');
            
            const downloadBtn = document.createElement('a');
            downloadBtn.className = 'btn btn-primary btn-inline';
            downloadBtn.href = method.qrSrc;
            downloadBtn.setAttribute('download', `QRIS-${method.name}.png`);
            downloadBtn.innerHTML = '<i class="fas fa-download"></i> ' + (currentLanguage === 'id' ? 'Unduh' : 'Download');
            
            downloadBtn.addEventListener('click', () => {
                toast(currentLanguage === 'id' ? 'QR berhasil diunduh' : 'QR downloaded successfully');
            });
            
            row.appendChild(openBtn);
            row.appendChild(downloadBtn);
            
            panel.appendChild(desc);
            panel.appendChild(qrContainer);
            panel.appendChild(document.createElement('br'));
            panel.appendChild(row);
        }
        
        // Assemble item
        item.appendChild(header);
        item.appendChild(panel);
        
        // Toggle functionality
        header.addEventListener('click', () => {
            const isOpen = item.dataset.open === 'true';
            
            if (isOpen) {
                item.dataset.open = 'false';
                header.setAttribute('aria-expanded', 'false');
                openId = null;
                updateConfirmButton(null);
            } else {
                closeAll();
                item.dataset.open = 'true';
                header.setAttribute('aria-expanded', 'true');
                openId = method.id;
                updateConfirmButton(method.name);
                
                // Scroll into view if needed (on mobile)
                if (window.innerWidth < 768) {
                    setTimeout(() => {
                        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 300);
                }
            }
        });
        
        // Add to list
        list.appendChild(item);
    });
    
    // Initialize with first method open
    if (METHODS.length > 0 && METHODS[0].kind !== 'unavailable') {
        setTimeout(() => {
            const firstItem = $('pm-dana');
            if (firstItem) {
                firstItem.querySelector('.item-header').click();
            }
        }, 500);
    }
}

// ---------- Host Hint ----------
function setHostHint() {
    const hostEl = $('siteHost');
    if (!hostEl) return;
    
    // Use actual host if available
    if (window.location && window.location.hostname) {
        hostEl.textContent = window.location.hostname;
    }
}

// ---------- Initialize Everything ----------
document.addEventListener('DOMContentLoaded', () => {
    // Set host hint
    setHostHint();
    
    // Initialize language and theme
    initLanguage();
    initTheme();
    
    // Initialize toggles
    initLanguageToggle();
    initThemeToggle();
    
    // Render payment methods
    renderPaymentMethods();
    
    // Add some interactive effects
    addInteractiveEffects();
    
    // Log initialization
    console.log('RomadzStore Payment UI initialized successfully!');
});

// ---------- Interactive Effects ----------
function addInteractiveEffects() {
    // Add hover effects to buttons
    document.addEventListener('mouseover', (e) => {
        if (e.target.classList.contains('btn') || e.target.closest('.btn')) {
            const btn = e.target.classList.contains('btn') ? e.target : e.target.closest('.btn');
            btn.style.transform = 'translateY(-2px)';
        }
        
        if (e.target.classList.contains('payment-item') || e.target.closest('.payment-item')) {
            const item = e.target.classList.contains('payment-item') ? e.target : e.target.closest('.payment-item');
            if (item.dataset.open === 'false') {
                item.style.transform = 'translateY(-2px)';
            }
        }
    }, true);
    
    document.addEventListener('mouseout', (e) => {
        if (e.target.classList.contains('btn') || e.target.closest('.btn')) {
            const btn = e.target.classList.contains('btn') ? e.target : e.target.closest('.btn');
            btn.style.transform = '';
        }
        
        if (e.target.classList.contains('payment-item') || e.target.closest('.payment-item')) {
            const item = e.target.classList.contains('payment-item') ? e.target : e.target.closest('.payment-item');
            if (item.dataset.open === 'false') {
                item.style.transform = '';
            }
        }
    }, true);
}