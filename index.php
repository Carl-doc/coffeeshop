<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Cruise</title>
    <link rel="stylesheet" href="assets/css/landingpage.css">
</head>
<body class="landing-body">

    <header class="landing-header">
        <div class="landing-logo">Cafe Cruise</div>

        <nav class="landing-nav">
            <a href="#home">Home</a>
            <a href="#features">Why Us</a>
            <a href="#menu-preview">Menu</a>
            <a href="#about">About</a>
            <a href="#location">location</a>
            <a href="customer/login.php" class="nav-login-btn">Login</a>
        </nav>
    </header>

    <section class="hero-section" id="home">
        <div class="hero-content">
            <span class="hero-badge">Black • Minimal • Grab-and-Go Coffee</span>
            <h1>Coffee That Fits Your Everyday Budget</h1>
            <p>
                Welcome to Cafe Cruise, a simple and convenient coffee stall
                serving affordable drinks for students, commuters, and daily coffee lovers.
                Fast service, straightforward ordering, and satisfying flavors in every cup.
            </p>

            <div class="hero-buttons">
                <a href="customer/login.php" class="hero-btn primary-btn">Order Now</a>
                <a href="#menu-preview" class="hero-btn secondary-btn">See Menu</a>
            </div>
        </div>
    </section>

    <section class="features-section" id="features">
        <div class="feature-card">
            <h3>Affordable Coffee</h3>
            <p>Quality drinks at budget-friendly prices for everyday coffee cravings.</p>
        </div>

        <div class="feature-card">
            <h3>Quick Service</h3>
            <p>Fast ordering and smooth claiming, perfect for customers on the go.</p>
        </div>

        <div class="feature-card">
            <h3>Simple &amp; Convenient</h3>
            <p>A practical coffee stop where you can easily grab your favorite drink anytime.</p>
        </div>
    </section>

    <section class="menu-preview-section" id="menu-preview">
        <div class="section-heading">
            <span>Customer Favorites</span>
            <h2>Popular Drinks</h2>
            <p>
                Refreshing and affordable drinks that match the simple and modern
                style of Cafe Cruise.
            </p>
        </div>

        <div class="menu-grid">
            <div class="menu-card">
                <div class="menu-image placeholder-one"></div>
                <h3>Iced Coffee</h3>
                <p>A cool and satisfying coffee drink for everyday energy.</p>
                <span>₱39 - ₱49</span>
                 
            </div>

            <div class="menu-card">
                <div class="menu-image placeholder-two"></div>
                <h3>Coffee-Based Series</h3>
                <p>Sweet and flavorful coffee choices served chilled and ready to go.</p>
                <span>₱39 - ₱59</span>
                
            </div>

            <div class="menu-card">
                <div class="menu-image placeholder-three"></div>
                <h3>Non-Coffee Series</h3>
                <p>Refreshing options for customers who want something smooth and different.</p>
                <span>₱39 - ₱59</span>
                 
            </div>

            <div class="menu-card">
                <div class="menu-image placeholder-four"></div>
                <h3>Specialty Matcha Series</h3>
                <p>Modern iced matcha drinks with a bold and creamy taste.</p>
                <span>₱49 - ₱59</span>
                 
            </div>
        </div>
    </section>

    <section class="about-section" id="about">
        <div class="about-text">
            <span>About Cafe Cruise</span>
            <h2>A Simple Coffee Stall for Everyday Customers</h2>
            <p>
                Cafe Cruise offers a practical coffee experience focused on affordability,
                quick service, and accessible drinks. Its kiosk-style setup makes it easy
                for customers to order and enjoy coffee without the formality of a large café.
                The brand is modern, minimal, and built for convenience.
            </p>
            <a href="login.php" class="about-btn">Get Started</a>
        </div>

        <div class="about-box">
            <div class="about-mini-card">
                <h3>Budget-Friendly Choices</h3>
                <p>Drinks designed to stay affordable for students and regular customers.</p>
            </div>

            <div class="about-mini-card">
                <h3>Modern Stall Setup</h3>
                <p>A black-and-white kiosk look that feels clean, simple, and easy to recognize.</p>
            </div>

            <div class="about-mini-card">
                <h3>Made for Daily Orders</h3>
                <p>Ideal for grab-and-go transactions, repeat buyers, and fast service flow.</p>
            </div>
        </div>
    </section>

    <section class="location-section" id="location">
        <div class="section-heading">
            <span>Visit Us</span>
            <h2>Our Location</h2>
            <p>Find Cafe Cruise in Tacloban City. Drop by and grab your favorite drink anytime.</p>
        </div>

        <div class="location-container">
            <div class="location-info">
                <h3>Cafe Cruise</h3>
                <p>Paterno St. Barangay 29</p>
                <p>Tacloban City, Philippines</p>
                <p>6500</p>

                <div class="location-meta">
                    <p><strong>Status:</strong> Open Daily</p>
                    <p><strong>Type:</strong> Coffee Stall</p>
                </div>

                <a
                    href="https://maps.google.com/?q=Paterno%20St.%20Barangay%2029,%20Tacloban%20City"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="location-btn"
                >
                    View on Map
                </a>
            </div>

            <div class="location-map">
                <iframe
                    src="https://maps.google.com/maps?q=Paterno%20St.%20Barangay%2029,%20Tacloban%20City&t=&z=15&ie=UTF8&iwloc=&output=embed"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <footer class="landing-footer">
        <p>© 2026 Cafe Cruise. All rights reserved.</p>
    </footer>

</body>
</html>