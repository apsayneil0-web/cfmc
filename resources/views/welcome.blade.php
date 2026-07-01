<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Farming Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('css/login.css') }}">
</head>
<body>
<header class="landing-header">
    <div class="logo">
        <div class="logo-mark">SF</div>
        <span>SmartFarm</span>
    </div>
    <nav class="landing-nav">
        <a href="#about">About</a>
        <a href="#features">Features</a>
        <a href="#benefits">Benefits</a>
        <a href="#stats">Statistics</a>
        <a href="#testimonials">Testimonials</a>
        <a href="#contact">Contact</a>
    </nav>
    <a href="{{ url('/login') }}" class="login-button">Login</a>
</header>

<section class="hero-section" id="home">
    <div class="hero-overlay"></div>
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="hero-label">Smart Farming Management</span>
            <h1>Empowering Farmers Through Smart Technology</h1>
            <p class="hero-description">Optimize farm operations with crop monitoring, weather intelligence, management tools, and reporting—all from one modern platform.</p>
            <div class="hero-actions">
                <a href="{{ url('/login') }}" class="btn btn-primary">Get Started</a>
                <a href="#features" class="btn btn-secondary">View Features</a>
            </div>
        </div>
        <div class="glass-card hero-panel-card">
            <div class="hero-feature">
                <div>
                    <h3>Crop Monitoring</h3>
                    <p>Keep track of field health and soil conditions in real time.</p>
                </div>
                <div class="hero-icon"><i class="fa-solid fa-seedling"></i></div>
            </div>
            <div class="hero-feature">
                <div>
                    <h3>Weather Alerts</h3>
                    <p>Receive timely guidance for planting, irrigation, and harvesting.</p>
                </div>
                <div class="hero-icon"><i class="fa-solid fa-cloud-sun"></i></div>
            </div>
            <div class="hero-quality">
                <span>Trusted by farms for clearer decisions</span>
                <div class="hero-quality-grid">
                    <div><strong>24/7</strong><span>Support</span></div>
                    <div><strong>120+</strong><span>Reports</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="about" class="section about-section">
    <div class="container section-grid">
        <div class="section-copy">
            <span class="section-label">About the System</span>
            <h2>Designed to make farm management smarter and more sustainable.</h2>
            <p>SmartFarm brings farmers, managers, and administrators together with simple workflows, clear insight, and secure role-based access.</p>
            <ul class="about-list">
                <li>One login for every role</li>
                <li>Automatic dashboard routing</li>
                <li>Reliable farm intelligence tools</li>
            </ul>
        </div>
        <div class="glass-card about-card">
            <h3>Why SmartFarm?</h3>
            <p>We combine modern analytics with agricultural insight to help you manage operations, maximize yields, and reduce waste.</p>
            <ul>
                <li>Fast setup and easy access</li>
                <li>Role-aware dashboards</li>
                <li>Responsive support and reporting</li>
            </ul>
        </div>
    </div>
</section>

<section id="features" class="section features-section">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Features</span>
            <h2>All the tools your farm needs</h2>
        </div>
        <div class="feature-grid">
            <div class="glass-card feature-card">
                <div class="feature-icon"><i class="fa-solid fa-leaf"></i></div>
                <h3>Crop Monitoring</h3>
                <p>Visualize crop conditions, irrigation status, and soil metrics in one view.</p>
            </div>
            <div class="glass-card feature-card">
                <div class="feature-icon"><i class="fa-solid fa-tractor"></i></div>
                <h3>Farm Management</h3>
                <p>Plan tasks, track assets, and coordinate teams from a single dashboard.</p>
            </div>
            <div class="glass-card feature-card">
                <div class="feature-icon"><i class="fa-solid fa-cloud-bolt"></i></div>
                <h3>Weather Insights</h3>
                <p>Receive localized weather alerts for every stage of your growing cycle.</p>
            </div>
            <div class="glass-card feature-card">
                <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                <h3>Reporting</h3>
                <p>Generate quick yield, cost, and performance reports for smarter decisions.</p>
            </div>
        </div>
    </div>
</section>

<section id="benefits" class="section benefits-section">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Benefits for Farmers</span>
            <h2>Practical gains for every farming operation</h2>
        </div>
        <div class="benefits-grid">
            <div class="glass-card benefit-card">
                <h3>Informed decisions</h3>
                <p>Act on data-driven recommendations to improve crop timing and resource use.</p>
            </div>
            <div class="glass-card benefit-card">
                <h3>Less waste</h3>
                <p>Use resources more efficiently and reduce overhead across your fields.</p>
            </div>
            <div class="glass-card benefit-card">
                <h3>Better yields</h3>
                <p>Increase productivity with ongoing monitoring and smarter planning.</p>
            </div>
        </div>
    </div>
</section>

<section id="stats" class="section stats-section">
    <div class="container stats-grid">
        <div class="glass-card stat-card">
            <div class="stat-icon"><i class="fa-solid fa-field"></i></div>
            <h3>1,200+</h3>
            <p>Fields monitored</p>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <h3>8,500+</h3>
            <p>Farmers onboarded</p>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-icon"><i class="fa-solid fa-cloud-sun"></i></div>
            <h3>99.9%</h3>
            <p>Forecast reliability</p>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-icon"><i class="fa-solid fa-leaf-heart"></i></div>
            <h3>4.8/5</h3>
            <p>Customer satisfaction</p>
        </div>
    </div>
</section>

<section id="testimonials" class="section testimonials-section">
    <div class="container">
        <div class="section-header center">
            <span class="section-label">Testimonials</span>
            <h2>What farmers are saying</h2>
        </div>
        <div class="testimonials-grid">
            <div class="glass-card testimonial-card">
                <p>"SmartFarm helped us manage our fields and weather risks more confidently. The platform is intuitive and reliable."</p>
                <div class="testimonial-author">
                    <strong>Mary O.</strong>
                    <span>Organic Farmer</span>
                </div>
            </div>
            <div class="glass-card testimonial-card">
                <p>"The crop monitoring dashboard gives me quick insight into each field. It's a game-changer for planning."</p>
                <div class="testimonial-author">
                    <strong>James L.</strong>
                    <span>Farm Manager</span>
                </div>
            </div>
            <div class="glass-card testimonial-card">
                <p>"Weather alerts have helped us avoid costly mistakes. SmartFarm brings confidence to our daily operations."</p>
                <div class="testimonial-author">
                    <strong>Rina P.</strong>
                    <span>Smallholder Farmer</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="section contact-section">
    <div class="container contact-grid">
        <div>
            <span class="section-label">Contact</span>
            <h2>Let's build a smarter farm together</h2>
            <p>Contact our team to request a demo or learn how SmartFarm can help your agriculture business thrive.</p>
            <div class="contact-info">
                <div><i class="fa-solid fa-phone"></i> +1 800 123 4567</div>
                <div><i class="fa-solid fa-envelope"></i> hello@smartfarm.com</div>
                <div><i class="fa-solid fa-location-dot"></i> 380 Farm Road, Green Valley</div>
            </div>
        </div>
        <form class="glass-card contact-form">
            <label>Name</label>
            <input type="text" placeholder="Your name" required>
            <label>Email</label>
            <input type="email" placeholder="Your email" required>
            <label>Message</label>
            <textarea placeholder="How can we help you?" rows="5" required></textarea>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</section>

<footer class="footer-section">
    <div class="container footer-grid">
        <div>
            <div class="footer-logo">SmartFarm</div>
            <p>Smart farming solutions for modern agriculture.</p>
        </div>
        <div class="social-links">
            <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#"><i class="fa-brands fa-twitter"></i></a>
            <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
            <a href="#"><i class="fa-brands fa-instagram"></i></a>
        </div>
    </div>
    <div class="footer-copy">&copy; 2026 SmartFarm. All rights reserved.</div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</body>
</html>
