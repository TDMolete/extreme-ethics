<?php
require_once 'includes/config.php';
redirectAdminFromCustomerPages();
include 'includes/header.php';
?>

<div class="page-hero contact-hero">
    <div class="container">
        <h1 class="fade-in">GET IN TOUCH</h1>
        <div class="hero-line"></div>
        <p class="fade-in delay-1">We'd love to hear from you. Whether you have a question about our products, need styling advice, or just want to say hello – we're here.</p>
    </div>
</div>

<div class="container">
    <!-- Contact Info Cards -->
    <div class="contact-cards-grid">
        <div class="contact-card slide-up">
            <div class="card-icon"><i class="fas fa-phone-alt"></i></div>
            <h3>Call Us</h3>
            <p><a href="tel:0692070042">069-207-0042</a></p>
            <p class="card-note">Mon–Sat, 9am–6pm</p>
        </div>
        <div class="contact-card slide-up delay-1">
            <div class="card-icon"><i class="fas fa-envelope"></i></div>
            <h3>Email</h3>
            <p><a href="mailto:info@extremeethics.co.za">info@extremeethics.co.za</a></p>
            <p class="card-note">We reply within 24h</p>
        </div>
        <div class="contact-card slide-up delay-2">
            <div class="card-icon"><i class="fas fa-map-marker-alt"></i></div>
            <h3>Visit</h3>
            <p>Braamfischerville, Soweto, 1875</p>
            <p class="card-note">By appointment only</p>
        </div>
        <div class="contact-card slide-up delay-3">
            <div class="card-icon"><i class="fab fa-whatsapp"></i></div>
            <h3>WhatsApp</h3>
            <p><a href="https://wa.me/27692070042" target="_blank">069-207-0042</a></p>
            <p class="card-note">Quickest response</p>
        </div>
    </div>

    <!-- Main Contact Area -->
    <div class="contact-main-grid">
        <!-- Left Column: Map & Hours -->
        <div class="contact-left">
            <div class="map-container fade-in">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3579.502589311292!2d27.861456314733!3d-26.224786983428!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1e95a1a1a1a1a1a1%3A0x7b7b7b7b7b7b7b7b!2sBraamfischerville%2C%20Soweto%2C%201875!5e0!3m2!1sen!2sza!4v1620000000000!5m2!1sen!2sza" 
                    width="100%" 
                    height="350" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
            <div class="hours-box slide-up">
                <h3><i class="fas fa-clock"></i> Business Hours</h3>
                <ul>
                    <li><span>Monday – Friday:</span> 9:00 – 18:00</li>
                    <li><span>Saturday:</span> 9:00 – 15:00</li>
                    <li><span>Sunday & Public Holidays:</span> Closed</li>
                </ul>
            </div>
        </div>

        <!-- Right Column: Contact Form -->
        <div class="contact-right slide-up">
            <h2>Send a Message</h2>
            <form id="advancedContactForm" class="luxury-form">
                <div class="form-group floating">
                    <input type="text" id="name" name="name" required placeholder=" ">
                    <label for="name">Full Name</label>
                </div>
                <div class="form-group floating">
                    <input type="email" id="email" name="email" required placeholder=" ">
                    <label for="email">Email Address</label>
                </div>
                <div class="form-group floating">
                    <input type="tel" id="phone" name="phone" placeholder=" ">
                    <label for="phone">Phone (optional)</label>
                </div>
                <div class="form-group floating">
                    <textarea id="message" name="message" rows="5" required placeholder=" "></textarea>
                    <label for="message">Your Message</label>
                </div>
                <div class="form-group checkbox">
                    <input type="checkbox" id="privacy" name="privacy" required>
                    <label for="privacy">I agree to the <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                </div>
                <button type="submit" class="btn form-btn">SEND MESSAGE <i class="fas fa-paper-plane"></i></button>
            </form>
            <div class="form-message" id="formMessage"></div>
        </div>
    </div>

    <!-- FAQ Snippet -->
    <div class="faq-snippet">
        <h2>Quick Answers</h2>
        <div class="faq-grid">
            <div class="faq-item">
                <h4>How quickly do you respond?</h4>
                <p>We aim to reply within 24 hours on weekdays, often sooner via WhatsApp.</p>
            </div>
            <div class="faq-item">
                <h4>Do you have a physical store?</h4>
                <p>We operate by appointment only at our Braamfischerville studio. Please contact us to schedule a visit.</p>
            </div>
            <div class="faq-item">
                <h4>Can I track my order?</h4>
                <p>Yes, once your order ships you'll receive a tracking link via WhatsApp.</p>
            </div>
            <div class="faq-item">
                <h4>Do you offer international shipping?</h4>
                <p>Currently we ship only within South Africa, but international shipping is coming soon.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Advanced contact form handling
document.getElementById('advancedContactForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formMessage = document.getElementById('formMessage');
    formMessage.innerHTML = '<p style="color: var(--lux-gold);">Thank you! We\'ll get back to you within 24 hours.</p>';
    this.reset();
});
</script>
 <script src="/extreme/ethics/assets/js/script.js"></script>
    <script src="/extreme/ethics/assets/js/animations.js"></script>
<?php include 'includes/footer.php'; ?>