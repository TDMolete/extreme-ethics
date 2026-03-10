<?php require_once 'includes/config.php'; 
redirectAdminFromCustomerPages();
include 'includes/header.php'; ?>

<div class="page-hero faq-hero">
    <div class="container">
        <h1 class="fade-in">FREQUENTLY ASKED QUESTIONS</h1>
        <div class="hero-line"></div>
        <p class="fade-in delay-1">Find answers to common questions below.</p>
    </div>
</div>

<div class="container">
    <!-- Category Filters -->
    <div class="faq-categories slide-up">
        <button class="faq-category active" data-category="all">All</button>
        <button class="faq-category" data-category="orders">Orders</button>
        <button class="faq-category" data-category="shipping">Shipping</button>
        <button class="faq-category" data-category="returns">Returns</button>
        <button class="faq-category" data-category="products">Products</button>
    </div>

    <!-- FAQ Accordion -->
    <div class="faq-accordion">
        <!-- Orders -->
        <div class="faq-item" data-category="orders">
            <div class="faq-question">
                <h3>How do I place an order?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Simply browse our products, click "Add to Cart", then proceed to checkout. You can also order directly via WhatsApp by clicking the green button on any page.</p>
            </div>
        </div>

        <div class="faq-item" data-category="orders">
            <div class="faq-question">
                <h3>Can I modify or cancel my order?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Please contact us immediately via WhatsApp. We can only modify orders before they are processed for shipping (usually within 2 hours).</p>
            </div>
        </div>

        <!-- Shipping -->
        <div class="faq-item" data-category="shipping">
            <div class="faq-question">
                <h3>Do you ship internationally?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Currently we ship only within South Africa. International shipping is coming soon – follow us on Instagram for updates.</p>
            </div>
        </div>

        <div class="faq-item" data-category="shipping">
            <div class="faq-question">
                <h3>How much is shipping?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Shipping within South Africa is a flat rate of R80 for orders under R1000. Orders over R1000 ship free.</p>
            </div>
        </div>

        <div class="faq-item" data-category="shipping">
            <div class="faq-question">
                <h3>How long does delivery take?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Metro areas: 2-3 business days. Regional areas: 3-5 business days.</p>
            </div>
        </div>

        <!-- Returns -->
        <div class="faq-item" data-category="returns">
            <div class="faq-question">
                <h3>What is your return policy?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>We accept returns within 14 days of delivery. Items must be unworn, unwashed, and with original tags. Contact us via WhatsApp to initiate a return.</p>
            </div>
        </div>

        <div class="faq-item" data-category="returns">
            <div class="faq-question">
                <h3>Who pays for return shipping?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Customers are responsible for return shipping costs unless the item is faulty.</p>
            </div>
        </div>

        <!-- Products -->
        <div class="faq-item" data-category="products">
            <div class="faq-question">
                <h3>How do I find my size?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Check our <a href="size-guide.php">Size Guide</a>. If you're still unsure, message us on WhatsApp – we're happy to help!</p>
            </div>
        </div>

        <div class="faq-item" data-category="products">
            <div class="faq-question">
                <h3>Are your products ethically made?</h3>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Absolutely. We produce locally in Cape Town with fair wages and sustainable materials. Read more on our <a href="about.php">About page</a>.</p>
            </div>
        </div>
    </div>
</div>

<script>
// FAQ Accordion
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', () => {
        const item = question.parentElement;
        item.classList.toggle('active');
    });
});

// Category Filtering
const categoryBtns = document.querySelectorAll('.faq-category');
const faqItems = document.querySelectorAll('.faq-item');

categoryBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const category = btn.dataset.category;

        // Update active button
        categoryBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Filter items
        faqItems.forEach(item => {
            if (category === 'all' || item.dataset.category === category) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
 <script src="/extreme/ethics/assets/js/script.js"></script>
    <script src="/extreme/ethics/assets/js/animations.js"></script>
<?php include 'includes/footer.php'; ?>