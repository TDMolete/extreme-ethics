<?php
// No closing tag
?>
    </main>
    <footer class="luxury-footer">
        <div class="footer-container">
            <!-- Column 1: Brand Info -->
            <div class="footer-col brand-col">
                <div class="footer-logo">EXTREME ETHICS</div>
                <p class="footer-tagline">Wear with confined space.</p>
                <p class="footer-description">Authentic South African streetwear, crafted in Soweto since 2020.</p>
                <div class="footer-social">
                    <a href="https://facebook.com/ExtremeEthicsClothing" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://instagram.com/ExtremeEthicsClothing" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" target="_blank" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    <a href="https://wa.me/27692070042" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-col links-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="collections.php">Collections</a></li>
                    <li><a href="products.php">Shop</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="size-guide.php">Size Guide</a></li>
                </ul>
            </div>

            <!-- Column 3: Contact Info -->
            <div class="footer-col contact-col">
                <h4>Contact</h4>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> Braamfischerville, Soweto, 1875</li>
                    <li><i class="fas fa-phone-alt"></i> <a href="tel:0692070042">069-207-0042</a></li>
                    <li><i class="fas fa-envelope"></i> <a href="mailto:info@extremeethics.co.za">info@extremeethics.co.za</a></li>
                    <li><i class="fab fa-whatsapp"></i> <a href="https://wa.me/27692070042" target="_blank">WhatsApp</a></li>
                </ul>
            </div>

            <!-- Column 4: Newsletter -->
            <div class="footer-col newsletter-col">
                <h4>Newsletter</h4>
                <p>Get exclusive drops and offers.</p>
                <form class="newsletter-form" id="newsletterForm">
                    <div class="newsletter-input-group">
                        <input type="email" placeholder="Your email" required>
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
                <p class="newsletter-message"></p>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="footer-bottom">
            <div class="footer-bottom-container">
                <p class="copyright">&copy; 2025 Extreme Ethics. All rights reserved.</p>
                <ul class="footer-bottom-links">
                    <li><a href="terms.php">Terms</a></li>
                    <li><a href="privacy.php">Privacy</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="#" id="back-to-top">Back to Top <i class="fas fa-arrow-up"></i></a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Back to Top Script -->
    <script>
        document.getElementById('back-to-top')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>

    <script src="/extreme/ethics/assets/js/script.js"></script>
    <script src="/extreme/ethics/assets/js/animations.js"></script>
</body>
</html>