<?php
 require_once 'includes/config.php'; 
 redirectAdminFromCustomerPages();
include 'includes/header.php'; ?>

<div class="page-hero size-hero">
    <div class="container">
        <h1 class="fade-in">SIZE GUIDE</h1>
        <div class="hero-line"></div>
        <p class="fade-in delay-1">Find your perfect fit. Measurements are in centimeters.</p>
    </div>
</div>

<div class="container">
    <!-- Category Tabs -->
    <div class="size-tabs slide-up">
        <button class="size-tab active" data-category="men">Men / Unisex</button>
        <button class="size-tab" data-category="women">Women</button>
        <button class="size-tab" data-category="bottoms">Bottoms</button>
    </div>

    <!-- Men's Size Chart -->
    <div class="size-chart active" id="men-chart">
        <h2>Men's / Unisex Tops</h2>
        <table class="size-table">
            <tr><th>Size</th><th>Chest</th><th>Waist</th><th>Hip</th><th>Shoulder</th></tr>
            <tr><td>XS</td><td>86-91</td><td>71-76</td><td>86-91</td><td>42-44</td></tr>
            <tr><td>S</td><td>91-96</td><td>76-81</td><td>91-96</td><td>44-46</td></tr>
            <tr><td>M</td><td>96-101</td><td>81-86</td><td>96-101</td><td>46-48</td></tr>
            <tr><td>L</td><td>101-106</td><td>86-91</td><td>101-106</td><td>48-50</td></tr>
            <tr><td>XL</td><td>106-111</td><td>91-96</td><td>106-111</td><td>50-52</td></tr>
            <tr><td>XXL</td><td>111-117</td><td>96-102</td><td>111-117</td><td>52-54</td></tr>
        </table>
    </div>

    <!-- Women's Size Chart -->
    <div class="size-chart" id="women-chart">
        <h2>Women's Tops</h2>
        <table class="size-table">
            <tr><th>Size</th><th>Bust</th><th>Waist</th><th>Hip</th></tr>
            <tr><td>XS</td><td>81-86</td><td>61-66</td><td>86-91</td></tr>
            <tr><td>S</td><td>86-91</td><td>66-71</td><td>91-96</td></tr>
            <tr><td>M</td><td>91-96</td><td>71-76</td><td>96-101</td></tr>
            <tr><td>L</td><td>96-101</td><td>76-81</td><td>101-106</td></tr>
            <tr><td>XL</td><td>101-106</td><td>81-86</td><td>106-111</td></tr>
        </table>
    </div>

    <!-- Bottoms Size Chart -->
    <div class="size-chart" id="bottoms-chart">
        <h2>Bottoms (Waist)</h2>
        <table class="size-table">
            <tr><th>Size</th><th>Waist (cm)</th><th>Inches</th></tr>
            <tr><td>XS</td><td>71-76</td><td>28-30</td></tr>
            <tr><td>S</td><td>76-81</td><td>30-32</td></tr>
            <tr><td>M</td><td>81-86</td><td>32-34</td></tr>
            <tr><td>L</td><td>86-91</td><td>34-36</td></tr>
            <tr><td>XL</td><td>91-96</td><td>36-38</td></tr>
            <tr><td>XXL</td><td>96-102</td><td>38-40</td></tr>
        </table>
    </div>

    <!-- Fit Tips & Measuring Guide -->
    <div class="fit-tips-grid">
        <div class="fit-tip-card slide-up">
            <i class="fas fa-tshirt"></i>
            <h3>Fit Tips</h3>
            <p>Our standard fit is true to size. For an oversized look, go one size up. Jackets are designed to layer; consider sizing up if you plan to wear a hoodie underneath.</p>
        </div>
        <div class="fit-tip-card slide-up delay-1">
            <i class="fas fa-ruler"></i>
            <h3>How to Measure</h3>
            <ul>
                <li><strong>Chest:</strong> Measure around the fullest part of your chest, keeping the tape horizontal.</li>
                <li><strong>Waist:</strong> Measure around your natural waistline, just above your belly button.</li>
                <li><strong>Hip:</strong> Stand with feet together and measure around the fullest part of your hips.</li>
            </ul>
        </div>
    </div>

    <div class="size-note">
        <p>If you're between sizes or have any questions, <a href="contact.php">contact us</a> – we're happy to help!</p>
    </div>
</div>

<script>
// Tab functionality
const tabs = document.querySelectorAll('.size-tab');
const charts = document.querySelectorAll('.size-chart');

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        // Remove active class from all tabs and charts
        tabs.forEach(t => t.classList.remove('active'));
        charts.forEach(c => c.classList.remove('active'));

        // Add active class to clicked tab
        tab.classList.add('active');

        // Show corresponding chart
        const category = tab.dataset.category;
        document.getElementById(`${category}-chart`).classList.add('active');
    });
});
</script>
 <script src="/extreme/ethics/assets/js/script.js"></script>
    <script src="/extreme/ethics/assets/js/animations.js"></script>
<?php include 'includes/footer.php'; ?>