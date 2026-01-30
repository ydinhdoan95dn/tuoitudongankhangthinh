/**
 * Garden Tools - Premium JavaScript
 * Modern interactions and animations
 */

(function() {
    'use strict';

    // =========================================================
    // HEADER SCROLL EFFECT (Glassmorphism)
    // =========================================================
    const header = document.querySelector('.header');
    let lastScrollY = 0;

    function handleHeaderScroll() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 50) {
            header?.classList.add('scrolled');
        } else {
            header?.classList.remove('scrolled');
        }
        
        lastScrollY = currentScrollY;
    }

    // =========================================================
    // STAGGER ANIMATION ON SCROLL
    // =========================================================
    function initScrollAnimations() {
        const animatedElements = document.querySelectorAll('.category-card, .product-card, .badge-item, .testimonial-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    // Add staggered delay based on index within visible items
                    const el = entry.target;
                    const siblings = Array.from(el.parentElement?.children || []);
                    const visibleIndex = siblings.indexOf(el);
                    
                    setTimeout(() => {
                        el.classList.add('visible');
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    }, visibleIndex * 100);
                    
                    observer.unobserve(el);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        animatedElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            observer.observe(el);
        });
    }

    // =========================================================
    // BACK TO TOP BUTTON
    // =========================================================
    function createBackToTop() {
        const btn = document.createElement('button');
        btn.className = 'back-to-top';
        btn.innerHTML = '<i class="fas fa-chevron-up"></i>';
        btn.setAttribute('aria-label', 'Quay lại đầu trang');
        document.body.appendChild(btn);

        // Show/hide based on scroll
        function toggleBackToTop() {
            if (window.scrollY > 500) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }

        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        window.addEventListener('scroll', toggleBackToTop, { passive: true });
    }

    // =========================================================
    // FLOATING CONTACT BUTTONS
    // =========================================================
    function createFloatingContact() {
        const container = document.createElement('div');
        container.className = 'floating-contact';
        container.innerHTML = `
            <a href="tel:0944379078" class="floating-btn phone" aria-label="Gọi điện">
                <i class="fas fa-phone-alt"></i>
                <span class="tooltip">Gọi ngay</span>
            </a>
            <a href="https://zalo.me/0944379078" class="floating-btn zalo" target="_blank" rel="noopener" aria-label="Chat Zalo">
                <svg viewBox="0 0 48 48" width="24" height="24" fill="currentColor">
                    <path d="M24 4C12.95 4 4 12.95 4 24c0 5.05 1.87 9.66 4.96 13.17l-2.68 7.87 8.22-2.63C17.54 44.13 20.67 45 24 45c11.05 0 20-8.95 20-20S35.05 4 24 4zm10.12 26.73c-.41 1.15-2.38 2.26-3.29 2.35-.82.08-1.86.12-3-.59-.69-.43-1.58-.95-2.73-1.81-4.83-3.55-7.98-8.69-8.22-9.09-.24-.4-1.96-2.61-1.96-4.98s1.24-3.54 1.68-4.02c.44-.48 1-.6 1.33-.6.33 0 .67 0 .96.01.31.01.72-.12 1.13.86.41 1 1.39 3.39 1.51 3.64.12.25.2.54.04.87-.16.33-.24.53-.47.82-.23.29-.49.65-.7.87-.23.24-.47.5-.2.98.27.48 1.2 1.98 2.58 3.2 1.77 1.57 3.26 2.06 3.73 2.29.47.23.74.2.99-.12.25-.32 1.07-1.25 1.35-1.68.28-.43.57-.36.96-.22.39.14 2.47 1.17 2.9 1.38.43.21.71.32.82.49.11.17.11 1-.29 2.15z"/>
                </svg>
                <span class="tooltip">Chat Zalo</span>
            </a>
        `;
        document.body.appendChild(container);
    }

    // =========================================================
    // SCROLL PROGRESS INDICATOR
    // =========================================================
    function createScrollProgress() {
        const progressBar = document.createElement('div');
        progressBar.className = 'scroll-progress';
        document.body.appendChild(progressBar);

        function updateProgress() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = (scrollTop / docHeight) * 100;
            progressBar.style.width = `${progress}%`;
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
    }

    // =========================================================
    // LAZY LOAD IMAGES
    // =========================================================
    function initLazyLoad() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px'
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // =========================================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // =========================================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // =========================================================
    // MOBILE MENU ENHANCEMENT
    // =========================================================
    function initMobileMenu() {
        const toggle = document.getElementById('mobileMenuToggle');
        const nav = document.querySelector('.main-nav');
        
        if (toggle && nav) {
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'mobile-menu-overlay';
            document.body.appendChild(overlay);

            toggle.addEventListener('click', () => {
                nav.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });

            overlay.addEventListener('click', () => {
                nav.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        }
    }

    // =========================================================
    // INIT ALL
    // =========================================================
    function init() {
        // Scroll event listener (throttled)
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    handleHeaderScroll();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // Initialize components
        initScrollAnimations();
        createBackToTop();
        createFloatingContact();
        createScrollProgress();
        initLazyLoad();
        initSmoothScroll();
        initMobileMenu();

        // Log initialization
        console.log('🌿 Garden Tools Premium JS initialized');
    }

    // Wait for DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
