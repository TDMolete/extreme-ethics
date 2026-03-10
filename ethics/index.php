<?php 
require_once 'includes/config.php';
require_once 'includes/auth.php'; 
redirectAdminFromCustomerPages();
// Enable preview mode if requested by admin
if (isset($_GET['preview']) && $_GET['preview'] == 1 && isLoggedIn() && isAdmin()) {
    enablePreview();
    header('Location: index.php');
    exit;
}
include 'includes/header.php';
 ?>

<!-- Hero Slideshow (images from New folder) -->
<div class="hero-slideshow">
    <?php for ($i = 1; $i <= 8; $i++): ?>
    <div class="slide <?= $i == 1 ? 'active' : '' ?>" style="background-image: url('assets/images/display/<?= $i ?>.jpg');"></div>
    <?php endfor; ?>
    <button class="slide-prev"><i class="fas fa-chevron-left"></i></button>
    <button class="slide-next"><i class="fas fa-chevron-right"></i></button>
</div>

<!-- Brand Strip -->
<div class="brand-strip">
    <img src="assets/images/logo/EE-logo.jpg" alt="Extreme Ethics">
</div>

<!-- Video & Stories Section -->
<div class="container">
    <div class="video-stories-grid">
        <div class="video-column">
            <video class="urban-video" autoplay muted loop playsinline poster="assets/images/video/thumb.jpg">
                <source src="assets/images/video/vid1.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="stories-column">
            <h2 class="stories-title">OUR STORY</h2>
            <p class="stories-text">Born on the streets of Soweto, Extreme Ethics fuses raw urban energy with uncompromising quality. Every piece is a statement – wearable art that challenges conventions.</p>
            <p class="stories-text">From our flagship studio in Braamfischerville, we design limited drops that celebrate South African street culture. Join the movement.</p>
            <a href="about.php" class="btn stories-btn">READ MORE</a>
        </div>
    </div>
</div>

<!-- WIDGET 1: TRENDING NOW -->
<div class="container">
    <h2 class="section-title"><span>TRENDING</span> NOW</h2>
    <div class="product-grid trending-grid">
        <?php
        $stmt = $pdo->query("SELECT * FROM products ORDER BY RAND() LIMIT 4");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            $images = getProductImages($row['name']);
            $image = !empty($images) ? $images[0] : 'assets/images/products/placeholder.jpg';
        ?>
        <div class="product-card urban-card trending-card">
            <span class="trending-badge">TRENDING</span>
            <img src="<?= $image ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="product-image">
            <h3><a href="product.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></h3>
            <p class="price">R<?= $row['price'] ?></p>
            <a href="product.php?id=<?= $row['id'] ?>" class="btn-wa">VIEW DETAILS</a>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- WIDGET 2: CUSTOMER STORIES (TESTIMONIAL CAROUSEL) -->
<div class="testimonials-section">
    <div class="container">
        <h2 class="section-title"><span>CUSTOMER</span> STORIES</h2>
        <div class="testimonials-carousel">
            <div class="testimonial-track" id="testimonialTrack">
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"The quality is unmatched. My hoodie feels premium and the fit is perfect. Definitely my new go‑to brand."</p>
                    <div class="customer-info">
                        <img src="assets/images/testimonials/customer1.jpg" alt="Thabo M." class="customer-avatar">
                        <div>
                            <h4>Thabo M.</h4>
                            <p>Soweto</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"Extreme Ethics represents the future of South African streetwear. Bold designs, ethical production – I'm a fan for life."</p>
                    <div class="customer-info">
                        <img src="assets/images/testimonials/customer2.jpg" alt="Lerato K." class="customer-avatar">
                        <div>
                            <h4>Lerato K.</h4>
                            <p>Cape Town</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"Shipping was fast, and the customer service via WhatsApp was amazing. The puffer jacket is 🔥."</p>
                    <div class="customer-info">
                        <img src="assets/images/testimonials/customer3.jpg" alt="Sipho D." class="customer-avatar">
                        <div>
                            <h4>Sipho D.</h4>
                            <p>Durban</p>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-next"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<!-- WIDGET 3: FOLLOW US (INSTAGRAM FEED) -->
<div class="instagram-section">
    <div class="container">
        <h2 class="section-title"><span>FOLLOW</span> US @EXTREMEETHICS</h2>
        <div class="instagram-grid">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <a href="https://instagram.com/extremeethicsclothing" target="_blank" class="instagram-item">
                <img src="assets/images/instagram/<?= $i ?>.jpg" alt="Instagram post">
                <div class="instagram-overlay">
                    <i class="fab fa-instagram"></i>
                </div>
            </a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- Recently Viewed Section -->
<div class="container">
    <h2 class="section-title"><span>RECENTLY</span> VIEWED</h2>
    <div class="recently-viewed-wrapper">
        <div id="recentlyViewed" class="recently-viewed-container"></div>
        <button class="recent-nav prev" id="recentPrev" style="display:none;"><i class="fas fa-chevron-left"></i></button>
        <button class="recent-nav next" id="recentNext" style="display:none;"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<script>
// Render recently viewed on homepage
renderRecentlyViewed();
initRecentCarousel();
</script>

<!-- Video & Stories Section -->
<div class="container">
    <div class="video-stories-grid">
        <div class="video-column">
            <video class="urban-video" autoplay muted loop playsinline poster="assets/images/video/thumb.jpg">
                <source src="assets/images/video/vid3.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="stories-column">
            <h2 class="stories-title">OUR STORY</h2>
            <p class="stories-text">Born on the streets of Soweto, Extreme Ethics fuses raw urban energy with uncompromising quality. Every piece is a statement – wearable art that challenges conventions.</p>
            <p class="stories-text">From our flagship studio in Braamfischerville, we design limited drops that celebrate South African street culture. Join the movement.</p>
            <a href="about.php" class="btn stories-btn">READ MORE</a>
        </div>
    </div>
</div>

<!-- Urban Contact Strip -->
<div class="urban-strip">
    <div class="strip-item"><i class="fas fa-phone-alt"></i> 069-207-0042</div>
    <div class="strip-item"><i class="fab fa-instagram"></i> @extremeethicsclothing</div>
    <div class="strip-item"><i class="fab fa-facebook"></i> Extreme Ethics Clothing</div>
    <div class="strip-item"><i class="fas fa-map-marker-alt"></i> Braamfischerville, Soweto, 1875</div>
</div>

<script>
// Slideshow (existing)
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slideshow .slide');
const prev = document.querySelector('.slide-prev');
const next = document.querySelector('.slide-next');
function showSlide(index) {
    if (index < 0) index = slides.length - 1;
    if (index >= slides.length) index = 0;
    slides.forEach(s => s.classList.remove('active'));
    slides[index].classList.add('active');
    currentSlide = index;
}
if (prev && next) {
    prev.addEventListener('click', () => showSlide(currentSlide - 1));
    next.addEventListener('click', () => showSlide(currentSlide + 1));
    setInterval(() => showSlide(currentSlide + 1), 5000);
}

// Testimonials carousel
const track = document.getElementById('testimonialTrack');
const carouselPrev = document.querySelector('.carousel-prev');
const carouselNext = document.querySelector('.carousel-next');
let position = 0;
const cardWidth = 380; // approximate
if (track && carouselPrev && carouselNext) {
    carouselNext.addEventListener('click', () => {
        position = Math.max(position - (cardWidth + 20), -((track.children.length - 1) * (cardWidth + 20)));
        track.style.transform = `translateX(${position}px)`;
    });
    carouselPrev.addEventListener('click', () => {
        position = Math.min(position + (cardWidth + 20), 0);
        track.style.transform = `translateX(${position}px)`;
    });
}
</script>
 <script src="/extreme/ethics/assets/js/script.js"></script>
    <script src="/extreme/ethics/assets/js/animations.js"></script>
<?php include 'includes/footer.php'; ?>