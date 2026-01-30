/**
 * Garden Tools - Mobile-First JavaScript
 * Version: 3.0 - App-like Mobile Experience
 */

(function() {
    'use strict';

    // =========================================================
    // MOBILE DETECTION & UTILITIES
    // =========================================================
    const isMobile = window.innerWidth < 992;
    const isTouch = 'ontouchstart' in window;

    // =========================================================
    // MOBILE HEADER - Auto Hide on Scroll
    // =========================================================
    function initMobileHeader() {
        const header = document.querySelector('.mobile-header');
        if (!header) return;

        let lastScrollY = 0;
        let ticking = false;

        function updateHeader() {
            const currentScrollY = window.scrollY;

            if (currentScrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            // Auto hide/show on scroll direction
            if (currentScrollY > lastScrollY && currentScrollY > 150) {
                header.classList.add('hidden');
            } else {
                header.classList.remove('hidden');
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }, { passive: true });
    }

    // =========================================================
    // MOBILE DRAWER MENU
    // =========================================================
    function initDrawerMenu() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const drawer = document.querySelector('.mobile-drawer');
        const overlay = document.querySelector('.drawer-overlay');
        const closeBtn = document.querySelector('.drawer-close');

        if (!drawer) return;

        function openDrawer() {
            drawer.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('menu-open');
        }

        function closeDrawer() {
            drawer.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('menu-open');
        }

        // Toggle button
        if (menuToggle) {
            menuToggle.addEventListener('click', openDrawer);
        }

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', closeDrawer);
        }

        // Overlay click
        if (overlay) {
            overlay.addEventListener('click', closeDrawer);
        }

        // Swipe to close
        let startX = 0;
        let currentX = 0;

        drawer.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
        }, { passive: true });

        drawer.addEventListener('touchmove', function(e) {
            currentX = e.touches[0].clientX;
            const diff = startX - currentX;

            if (diff > 0) {
                drawer.style.transform = `translateX(-${Math.min(diff, 100)}px)`;
            }
        }, { passive: true });

        drawer.addEventListener('touchend', function() {
            const diff = startX - currentX;

            if (diff > 80) {
                closeDrawer();
            }
            drawer.style.transform = '';
        });

        // Submenu accordion
        const hasChildren = drawer.querySelectorAll('.drawer-nav-item.has-children');
        hasChildren.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                const submenu = this.nextElementSibling;
                if (submenu && submenu.classList.contains('drawer-submenu')) {
                    submenu.classList.toggle('active');
                }
            });
        });
    }

    // =========================================================
    // MOBILE SEARCH OVERLAY
    // =========================================================
    function initMobileSearch() {
        const searchToggle = document.querySelector('.mobile-search-toggle');
        const searchOverlay = document.querySelector('.mobile-search-overlay');
        const backBtn = document.querySelector('.search-header .back-btn');
        const searchInput = document.querySelector('.mobile-search-overlay input');

        if (!searchOverlay) return;

        function openSearch() {
            searchOverlay.classList.add('active');
            document.body.classList.add('menu-open');
            setTimeout(() => {
                if (searchInput) searchInput.focus();
            }, 300);
        }

        function closeSearch() {
            searchOverlay.classList.remove('active');
            document.body.classList.remove('menu-open');
        }

        if (searchToggle) {
            searchToggle.addEventListener('click', openSearch);
        }

        if (backBtn) {
            backBtn.addEventListener('click', closeSearch);
        }

        // Search history click
        const historyItems = document.querySelectorAll('.search-history-item');
        historyItems.forEach(item => {
            item.addEventListener('click', function() {
                if (searchInput) {
                    searchInput.value = this.textContent.trim();
                    searchInput.form.submit();
                }
            });
        });

        // Search tags click
        const searchTags = document.querySelectorAll('.search-tag');
        searchTags.forEach(tag => {
            tag.addEventListener('click', function() {
                if (searchInput) {
                    searchInput.value = this.textContent.trim();
                    searchInput.form.submit();
                }
            });
        });
    }

    // =========================================================
    // BOTTOM NAVIGATION - Active State
    // =========================================================
    function initBottomNav() {
        const navItems = document.querySelectorAll('.mobile-bottom-nav .mobile-nav-item:not(.special)');
        const currentPath = window.location.pathname;

        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && (currentPath.includes(href) || (href === '/' && currentPath === '/'))) {
                item.classList.add('active');
            }
        });
    }

    // =========================================================
    // TOUCH RIPPLE EFFECT
    // =========================================================
    function initRippleEffect() {
        const elements = document.querySelectorAll('.btn, .product-card, .category-card, .mobile-nav-item');

        elements.forEach(el => {
            el.addEventListener('touchstart', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.touches[0].clientX - rect.left;
                const y = e.touches[0].clientY - rect.top;

                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';

                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            }, { passive: true });
        });

        // Add ripple styles if not exist
        if (!document.querySelector('#ripple-styles')) {
            const style = document.createElement('style');
            style.id = 'ripple-styles';
            style.textContent = `
                .ripple-effect {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(0,0,0,0.1);
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                }
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // =========================================================
    // SCROLL PROGRESS BAR
    // =========================================================
    function initScrollProgress() {
        let progressBar = document.querySelector('.scroll-progress');

        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.className = 'scroll-progress';
            document.body.appendChild(progressBar);
        }

        function updateProgress() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = (scrollTop / docHeight) * 100;
            progressBar.style.width = progress + '%';
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
    }

    // =========================================================
    // BACK TO TOP BUTTON
    // =========================================================
    function initBackToTop() {
        let btn = document.querySelector('.back-to-top');

        if (!btn) {
            btn = document.createElement('button');
            btn.className = 'back-to-top';
            btn.innerHTML = '<i class="fas fa-chevron-up"></i>';
            btn.setAttribute('aria-label', 'Quay lại đầu trang');
            document.body.appendChild(btn);
        }

        function toggleButton() {
            if (window.scrollY > 500) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }

        btn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', toggleButton, { passive: true });
    }

    // =========================================================
    // FLOATING CONTACT BUTTONS
    // =========================================================
    function initFloatingContact() {
        let container = document.querySelector('.floating-contact');

        if (!container) {
            container = document.createElement('div');
            container.className = 'floating-contact';
            container.innerHTML = `
                <a href="tel:0944379078" class="floating-btn phone" aria-label="Gọi điện">
                    <i class="fas fa-phone-alt"></i>
                    <span class="tooltip">Gọi ngay</span>
                </a>
                <a href="https://zalo.me/0944379078" class="floating-btn zalo" target="_blank" rel="noopener" aria-label="Chat Zalo">
                    <svg viewBox="0 0 48 48" width="24" height="24" fill="currentColor">
                        <path d="M24 4C12.95 4 4 12.95 4 24c0 5.05 1.87 9.66 4.96 13.17l-2.68 7.87 8.22-2.63C17.54 44.13 20.67 45 24 45c11.05 0 20-8.95 20-20S35.05 4 24 4z"/>
                    </svg>
                    <span class="tooltip">Chat Zalo</span>
                </a>
            `;
            document.body.appendChild(container);
        }
    }

    // =========================================================
    // LAZY LOAD IMAGES
    // =========================================================
    function initLazyLoad() {
        const images = document.querySelectorAll('img[data-src]');

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        if (img.dataset.srcset) {
                            img.srcset = img.dataset.srcset;
                        }
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: '50px 0px' });

            images.forEach(img => observer.observe(img));
        } else {
            // Fallback
            images.forEach(img => {
                img.src = img.dataset.src;
            });
        }
    }

    // =========================================================
    // SCROLL ANIMATIONS
    // =========================================================
    function initScrollAnimations() {
        const elements = document.querySelectorAll('.category-card, .product-card, .badge-item');

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const siblings = Array.from(el.parentElement?.children || []);
                        const visibleIndex = siblings.indexOf(el);

                        setTimeout(() => {
                            el.style.opacity = '1';
                            el.style.transform = 'translateY(0)';
                        }, visibleIndex * 50);

                        observer.unobserve(el);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

            elements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                observer.observe(el);
            });
        }
    }

    // =========================================================
    // HORIZONTAL SCROLL SNAP
    // =========================================================
    function initHorizontalScroll() {
        const containers = document.querySelectorAll('.categories-grid, .badges-grid');

        containers.forEach(container => {
            let isDown = false;
            let startX;
            let scrollLeft;

            container.addEventListener('mousedown', (e) => {
                isDown = true;
                startX = e.pageX - container.offsetLeft;
                scrollLeft = container.scrollLeft;
            });

            container.addEventListener('mouseleave', () => {
                isDown = false;
            });

            container.addEventListener('mouseup', () => {
                isDown = false;
            });

            container.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const walk = (x - startX) * 2;
                container.scrollLeft = scrollLeft - walk;
            });
        });
    }

    // =========================================================
    // FILTER MODAL
    // =========================================================
    function initFilterModal() {
        const filterBtn = document.querySelector('.filter-btn');
        const filterModal = document.querySelector('.filter-modal');
        const filterOverlay = document.querySelector('.filter-overlay');
        const closeBtn = filterModal?.querySelector('.filter-close');
        const applyBtn = filterModal?.querySelector('.filter-apply');

        if (!filterModal) return;

        function openFilter() {
            filterModal.classList.add('active');
            if (filterOverlay) filterOverlay.classList.add('active');
            document.body.classList.add('menu-open');
        }

        function closeFilter() {
            filterModal.classList.remove('active');
            if (filterOverlay) filterOverlay.classList.remove('active');
            document.body.classList.remove('menu-open');
        }

        if (filterBtn) {
            filterBtn.addEventListener('click', openFilter);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeFilter);
        }

        if (filterOverlay) {
            filterOverlay.addEventListener('click', closeFilter);
        }

        if (applyBtn) {
            applyBtn.addEventListener('click', closeFilter);
        }

        // Filter option toggle
        const options = filterModal.querySelectorAll('.filter-option');
        options.forEach(opt => {
            opt.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });
    }

    // =========================================================
    // PULL TO REFRESH (Optional)
    // =========================================================
    function initPullToRefresh() {
        if (!isMobile) return;

        let startY = 0;
        let pulling = false;
        const threshold = 80;
        const indicator = document.createElement('div');
        indicator.className = 'pull-to-refresh';
        indicator.innerHTML = '<i class="fas fa-sync-alt"></i> <span>Đang tải...</span>';
        document.body.appendChild(indicator);

        document.addEventListener('touchstart', function(e) {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
                pulling = true;
            }
        }, { passive: true });

        document.addEventListener('touchmove', function(e) {
            if (!pulling) return;

            const currentY = e.touches[0].clientY;
            const diff = currentY - startY;

            if (diff > 0 && diff < threshold * 2) {
                indicator.style.transform = `translateX(-50%) translateY(${Math.min(diff - threshold, 20)}px)`;
                if (diff > threshold) {
                    indicator.classList.add('visible');
                }
            }
        }, { passive: true });

        document.addEventListener('touchend', function() {
            if (indicator.classList.contains('visible')) {
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                indicator.style.transform = '';
            }
            pulling = false;
        });
    }

    // =========================================================
    // WISHLIST TOGGLE
    // =========================================================
    function initWishlist() {
        const wishlistBtns = document.querySelectorAll('.product-wishlist');

        wishlistBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.toggle('active');

                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('far');
                    icon.classList.toggle('fas');
                }
            });
        });
    }

    // =========================================================
    // SMOOTH SCROLL
    // =========================================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // =========================================================
    // DESKTOP HEADER SCROLL EFFECT
    // =========================================================
    function initDesktopHeader() {
        if (isMobile) return;

        const header = document.querySelector('.header');
        if (!header) return;

        function handleScroll() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }

        window.addEventListener('scroll', handleScroll, { passive: true });

        // Categories dropdown
        const catToggle = document.querySelector('.nav-categories-toggle');
        if (catToggle) {
            catToggle.addEventListener('click', function() {
                this.parentElement.classList.toggle('active');
            });
        }
    }

    // =========================================================
    // INITIALIZE ALL
    // =========================================================
    function init() {
        // Mobile specific
        if (isMobile) {
            initMobileHeader();
            initDrawerMenu();
            initMobileSearch();
            initBottomNav();
            // initPullToRefresh(); // Đã tắt - không cần thiết cho website bán hàng
        } else {
            initDesktopHeader();
        }

        // Common
        initRippleEffect();
        initScrollProgress();
        initBackToTop();
        initFloatingContact();
        initLazyLoad();
        initScrollAnimations();
        initHorizontalScroll();
        initFilterModal();
        initWishlist();
        initSmoothScroll();

        console.log('🌿 Garden Tools Mobile JS v3.0 initialized');
    }

    // Wait for DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Handle resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            window.location.reload();
        }, 500);
    });

})();
