// Scroll reveal animation
function revealOnScroll() {
    const reveals = document.querySelectorAll('.reveal');
    
    for (let i = 0; i < reveals.length; i++) {
        const windowHeight = window.innerHeight;
        const revealTop = reveals[i].getBoundingClientRect().top;
        const revealPoint = 150;
        
        if (revealTop < windowHeight - revealPoint) {
            reveals[i].classList.add('active');
        }
    }
}

// Add 'reveal' class to elements you want to animate on scroll
// For example, product cards, headings, sections
document.addEventListener('DOMContentLoaded', function() {
    // Add reveal class to product grid and other sections
    const productGrids = document.querySelectorAll('.product-grid');
    productGrids.forEach(grid => grid.classList.add('reveal'));
    
    const headings = document.querySelectorAll('h2');
    headings.forEach(heading => heading.classList.add('reveal'));
    
    const contactStrip = document.querySelector('.contact-strip');
    if (contactStrip) contactStrip.classList.add('reveal');
    
    // Initial check
    revealOnScroll();
    
    // Throttle scroll event for performance
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                revealOnScroll();
                ticking = false;
            });
            ticking = true;
        }
    });
});