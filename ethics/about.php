<?php
require_once 'includes/config.php';
redirectAdminFromCustomerPages();
include 'includes/header.php';
?>

<div class="container">
    <h1 class="slide-up">ABOUT EXTREME ETHICS</h1>
    
    <div class="about-grid">
        <div class="about-text slide-up delay-1">
            <h2>Our Story</h2>
            <p>Born on the streets of Cape Town in 2020, Extreme Ethics fuses raw urban energy with uncompromising ethical production. Our founders, a collective of local designers and activists, envisioned a brand that would empower the youth while respecting both people and planet.</p>
            <p>The name "Extreme Ethics" reflects our dual commitment: pushing creative boundaries while maintaining the highest standards of social and environmental responsibility. Every piece is a statement – wearable art that challenges conventions.</p>
            <p>Our motto, <strong>"wear with confined space"</strong>, speaks to the paradox of modern urban life: finding freedom within constraints, expressing individuality while belonging to a community. It's about owning your limits and pushing beyond them.</p>
        </div>
        <div class="about-image slide-up delay-2">
            <img src="assets/images/about-studio.jpg" alt="Extreme Ethics studio" onerror="this.src='https://via.placeholder.com/600x400?text=Studio'">
        </div>
    </div>

    <div class="values-section">
        <h2>Our Values</h2>
        <div class="values-grid">
            <div class="value-card zoom-in">
                <i class="fas fa-hand-holding-heart"></i>
                <h3>Ethical</h3>
                <p>Local production, fair wages, sustainable materials.</p>
            </div>
            <div class="value-card zoom-in delay-1">
                <i class="fas fa-skull"></i>
                <h3>Bold</h3>
                <p>Unapologetic design that stands out.</p>
            </div>
            <div class="value-card zoom-in delay-2">
                <i class="fas fa-people-group"></i>
                <h3>Community</h3>
                <p>Built by and for the South African youth.</p>
            </div>
            <div class="value-card zoom-in delay-3">
                <i class="fas fa-map-pin"></i>
                <h3>Soweto</h3>
                <p>Our flagship studio is based in Braamfischerville, Soweto, where every piece is designed and crafted.</p>            </div>
        </div>
    </div>

    <div class="craftsmanship-section">
        <h2>Our Craftsmanship</h2>
        <p>Each garment is produced in small batches in our Cape Town atelier, working with local artisans who share our passion for quality. We use premium fabrics – organic cotton, recycled polyesters, and deadstock materials – to create pieces that last.</p>
        <p>From initial sketch to final stitch, every detail is considered. Our signature bold graphics are screen-printed by hand, ensuring each piece has unique character.</p>
    </div>
</div>
 <script src="/extreme/ethics/assets/js/script.js"></script>
    <script src="/extreme/ethics/assets/js/animations.js"></script>
<?php include 'includes/footer.php'; ?>