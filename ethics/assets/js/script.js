/**
 * Extreme Ethics – Main JavaScript
 * All site‑wide functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ========== MOBILE MENU (SLIDE‑OUT) ==========
    const menuToggle = document.getElementById('menuToggle');
    const mobileNav = document.getElementById('mobileNav');
    const mobileOverlay = document.getElementById('mobileNavOverlay');
    const closeMobile = document.getElementById('closeMobile');

    if (menuToggle && mobileNav && mobileOverlay) {
        menuToggle.addEventListener('click', openMobileMenu);
    }
    if (closeMobile && mobileOverlay) {
        closeMobile.addEventListener('click', closeMobileMenu);
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }

    function openMobileMenu() {
        mobileNav.classList.add('open');
        mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        mobileNav.classList.remove('open');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    // ========== SEARCH BAR TOGGLE ==========
    const searchToggle = document.getElementById('searchToggle');
    const searchBar = document.getElementById('searchBar');
    const searchInput = document.getElementById('searchInput');

    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', function() {
            searchBar.classList.toggle('active');
            if (searchBar.classList.contains('active') && searchInput) {
                searchInput.focus();
            }
        });
    }

    // ========== AUTOCOMPLETE ==========
    const suggestionsBox = document.getElementById('autocompleteSuggestions');
    if (searchInput && suggestionsBox) {
        let timeout = null;

        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const term = this.value.trim();
            if (term.length < 2) {
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';
                return;
            }

            timeout = setTimeout(() => {
                fetch(`/extreme/ethics/search_autocomplete.php?term=${encodeURIComponent(term)}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(data => {
                        if (data.length > 0) {
                            let html = '';
                            data.forEach(item => {
                                html += `<div class="suggestion-item" data-id="${item.id}" data-name="${item.name}">
                                            <span class="suggestion-name">${item.name}</span>
                                            <span class="suggestion-price">R${item.price}</span>
                                        </div>`;
                            });
                            suggestionsBox.innerHTML = html;
                            suggestionsBox.style.display = 'block';
                        } else {
                            suggestionsBox.innerHTML = '';
                            suggestionsBox.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error('Autocomplete error:', err);
                        suggestionsBox.style.display = 'none';
                    });
            }, 300);
        });

        // Click on suggestion
        suggestionsBox.addEventListener('click', function(e) {
            const item = e.target.closest('.suggestion-item');
            if (item) {
                const productId = item.dataset.id;
                window.location.href = `/extreme/ethics/product.php?id=${productId}`;
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.style.display = 'none';
            }
        });
    }

    // ========== RECENTLY VIEWED FUNCTIONS ==========
    window.addToRecentlyViewed = function(product) {
        let recent = JSON.parse(localStorage.getItem('recentProducts')) || [];
        recent = recent.filter(p => p.id != product.id);
        recent.unshift(product);
        recent = recent.slice(0, 8);
        localStorage.setItem('recentProducts', JSON.stringify(recent));
    };

    window.renderRecentlyViewed = function() {
        const container = document.getElementById('recentlyViewed');
        if (!container) return;

        const recent = JSON.parse(localStorage.getItem('recentProducts')) || [];
        if (recent.length === 0) {
            container.innerHTML = '<p class="no-recent">No recently viewed items.</p>';
            return;
        }

        let html = '<div class="recent-carousel">';
        recent.forEach(item => {
            html += `
                <div class="recent-card">
                    <img src="${item.image}" alt="${item.name}" class="recent-image"
                         onerror="this.onerror=null; this.src='/extreme/ethics/assets/images/products/placeholder.jpg';">
                    <h4><a href="/extreme/ethics/product.php?id=${item.id}">${item.name}</a></h4>
                    <p class="recent-price">R${item.price}</p>
                </div>
            `;
        });
        html += '</div>';
        html += '<button class="recent-clear" id="clearRecent">Clear</button>';
        container.innerHTML = html;

        const clearBtn = document.getElementById('clearRecent');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                localStorage.removeItem('recentProducts');
                renderRecentlyViewed();
                // Hide navigation arrows if any
                const prev = document.getElementById('recentPrev');
                const next = document.getElementById('recentNext');
                if (prev && next) {
                    prev.style.display = 'none';
                    next.style.display = 'none';
                }
            });
        }

        // Re‑initialize carousel arrows
        initRecentCarousel();
    };

    window.initRecentCarousel = function() {
        const container = document.getElementById('recentlyViewed');
        if (!container) return;
        const recent = JSON.parse(localStorage.getItem('recentProducts')) || [];
        const prev = document.getElementById('recentPrev');
        const next = document.getElementById('recentNext');
        if (!prev || !next) return;

        if (recent.length === 0) {
            prev.style.display = 'none';
            next.style.display = 'none';
            return;
        }

        prev.style.display = 'flex';
        next.style.display = 'flex';

        const scrollAmount = 300;
        prev.replaceWith(prev.cloneNode(true)); // remove old listeners
        next.replaceWith(next.cloneNode(true));
        const newPrev = document.getElementById('recentPrev');
        const newNext = document.getElementById('recentNext');

        newPrev.addEventListener('click', () => {
            container.scrollLeft -= scrollAmount;
        });
        newNext.addEventListener('click', () => {
            container.scrollLeft += scrollAmount;
        });
    };

    // Call recently viewed functions if container exists
    if (document.getElementById('recentlyViewed')) {
        renderRecentlyViewed();
    }

    // ========== TESTIMONIALS CAROUSEL ==========
    const testimonialTrack = document.getElementById('testimonialTrack');
    const carouselPrev = document.querySelector('.carousel-prev');
    const carouselNext = document.querySelector('.carousel-next');

    if (testimonialTrack && carouselPrev && carouselNext) {
        let position = 0;
        const cardWidth = 380; // approximate, adjust as needed
        const maxScroll = -(testimonialTrack.children.length - 1) * (cardWidth + 20);

        carouselNext.addEventListener('click', () => {
            position = Math.max(position - (cardWidth + 20), maxScroll);
            testimonialTrack.style.transform = `translateX(${position}px)`;
        });

        carouselPrev.addEventListener('click', () => {
            position = Math.min(position + (cardWidth + 20), 0);
            testimonialTrack.style.transform = `translateX(${position}px)`;
        });
    }

    // ========== HERO SLIDESHOW ==========
    const slides = document.querySelectorAll('.hero-slideshow .slide');
    const slidePrev = document.querySelector('.slide-prev');
    const slideNext = document.querySelector('.slide-next');
    if (slides.length && slidePrev && slideNext) {
        let currentSlide = 0;
        function showSlide(index) {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;
            slides.forEach(s => s.classList.remove('active'));
            slides[index].classList.add('active');
            currentSlide = index;
        }
        slidePrev.addEventListener('click', () => showSlide(currentSlide - 1));
        slideNext.addEventListener('click', () => showSlide(currentSlide + 1));
        setInterval(() => showSlide(currentSlide + 1), 5000);
    }

    // ========== ACTIVE PAGE HIGHLIGHT ==========
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-links a, .mobile-nav-links a').forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

    // ========== BACK TO TOP ==========
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ========== NEWSLETTER FORM (MOCK) ==========
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            const messageEl = this.parentElement.querySelector('.newsletter-message');
            if (messageEl) {
                messageEl.textContent = `Thanks! ${email} has been subscribed.`;
                messageEl.classList.add('success');
                setTimeout(() => {
                    messageEl.textContent = '';
                    messageEl.classList.remove('success');
                }, 5000);
            }
            this.reset();
        });
    }

    // ========== SIZE GUIDE MODAL ==========
    const modal = document.getElementById('sizeGuideModal');
    const modalBtn = document.getElementById('sizeGuideBtn');
    const closeModal = document.querySelector('.close-modal');
    if (modal && modalBtn && closeModal) {
        modalBtn.addEventListener('click', () => {
            modal.classList.add('show');
        });
        closeModal.addEventListener('click', () => {
            modal.classList.remove('show');
        });
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    }
});