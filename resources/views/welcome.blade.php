<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>COOP - Cooperative Management System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; background-color: #ffffff; color: #111827; }
        a { color: inherit; text-decoration: none; }

        /* Layout */
        .fixed { position: fixed; }
        .w-full { width: 100%; }
        .max-w-6xl { max-width: 72rem; }
        .max-w-4xl { max-width: 56rem; }
        .max-w-2xl { max-width: 42rem; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .py-20 { padding-top: 5rem; padding-bottom: 5rem; }
        .px-8 { padding-left: 2rem; padding-right: 2rem; }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .pt-20 { padding-top: 5rem; }
        .p-8 { padding: 2rem; }

        /* Flex */
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .gap-3 { gap: 0.75rem; }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .gap-8 { gap: 2rem; }
        .gap-12 { gap: 3rem; }

        /* Grid */
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: 1fr; }

        /* Colors */
        .bg-white { background-color: #ffffff; }
        .bg-gray-50 { background-color: #f9fafb; }
        .bg-gray-900 { background-color: #111827; }
        .bg-green-600 { background-color: #16a34a; }
        .text-white { color: #ffffff; }
        .text-gray-700 { color: #374151; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-900 { color: #111827; }
        .text-green-600 { color: #16a34a; }
        .text-green-400 { color: #4ade80; }

        /* Sizing */
        .min-h-screen { min-height: 100vh; }
        .text-sm { font-size: 0.875rem; }
        .text-lg { font-size: 1.125rem; }
        .text-xl { font-size: 1.25rem; }
        .text-2xl { font-size: 1.5rem; }
        .text-4xl { font-size: 2.25rem; }
        .text-5xl { font-size: 3rem; }

        /* Font */
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }

        /* Margin */
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mb-16 { margin-bottom: 4rem; }
        .mt-16 { margin-top: 4rem; }
        .ml-1 { margin-left: 0.25rem; }

        /* Borders */
        .border { border: 1px solid #e5e7eb; }
        .border-2 { border: 2px solid #16a34a; }
        .rounded-lg { border-radius: 0.5rem; }

        /* Shadows */
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }

        /* Display */
        .hidden { display: none; }
        .inline-block { display: inline-block; }
        .text-center { text-align: center; }

        /* Transitions */
        .transition { transition: all 0.15s cubic-bezier(0.4,0,0.2,1); }

        /* Opacity */
        .opacity-90 { opacity: 0.9; }

        /* Hover states */
        .hover\:text-green-600:hover { color: #16a34a; }
        .hover\:bg-green-700:hover { background-color: #15803d; }
        .hover\:bg-green-50:hover { background-color: #f0fdf4; }
        .hover\:bg-gray-100:hover { background-color: #f3f4f6; }
        .hover\:shadow-lg:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .hover\:text-green-400:hover { color: #4ade80; }

        /* Header */
        header { top: 0; z-index: 50; }

        /* Gradients */
        .bg-gradient-to-br {
            background: linear-gradient(to bottom right, #f0fdf4, #eff6ff);
        }
        .bg-gradient-to-r {
            background: linear-gradient(to right, #16a34a, #2563eb);
        }

        /* Spacing */
        .space-y-4 > * + * { margin-top: 1rem; }

        /* Responsive */
        @media (min-width: 768px) {
            .md\:flex { display: flex; }
            .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .md\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .md\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .md\:flex-row { flex-direction: row; }
            .md\:text-2xl { font-size: 1.5rem; }
            .md\:text-6xl { font-size: 3.75rem; }
        }

        @media (max-width: 767px) {
            .hidden { display: none; }
            .md\:flex { display: none; }
        }
    </style>
</head>
<body class="bg-white text-gray-900">
    <!-- Navigation Header -->
    <header class="fixed w-full top-0 z-50 bg-white shadow-sm">
        <nav class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="text-2xl font-bold text-green-600">🤝 COOP</div>

            <div class="hidden md:flex gap-8">
                <a href="#features" class="text-gray-700 hover:text-green-600 transition">Features</a>
                <a href="#about" class="text-gray-700 hover:text-green-600 transition">About</a>
                <a href="#stats" class="text-gray-700 hover:text-green-600 transition">Stats</a>
            </div>

            @if (Route::has('login'))
                <div class="flex gap-4 items-center">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="px-6 py-2 rounded-lg text-gray-600 hover:text-red-600 transition font-semibold border-none bg-none cursor-pointer" style="font-family: inherit;">
                                Log Out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2 rounded-lg border-2 border-green-600 text-green-600 hover:bg-green-50 transition font-semibold">
                            Log In
                        </a>
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="min-h-screen pt-20 flex items-center justify-center bg-gradient-to-br">
        <div class="max-w-6xl mx-auto px-4 py-20 text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 text-gray-900">
                Welcome to <span class="text-green-600">COOP</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-2xl mx-auto">
                Empower your cooperative with modern management solutions. Streamline operations, connect members, and grow together.
            </p>

            <div class="flex flex-col md:flex-row gap-4 justify-center">
                @if (Route::has('login'))
                    @auth
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="px-8 py-3 rounded-lg bg-red-600 text-white hover:bg-red-700 transition font-semibold border-none cursor-pointer" style="font-family: inherit;">
                                Log Out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-8 py-3 rounded-lg border-2 border-green-600 text-green-600 hover:bg-green-50 transition font-semibold text-center">
                            Sign In
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-900">
                Key <span class="text-green-600">Features</span>
            </h2>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-8 rounded-lg border hover:shadow-lg transition">
                    <div class="text-4xl mb-4">👥</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">Member Management</h3>
                    <p class="text-gray-600">
                        Easily manage member profiles, roles, and permissions. Keep your cooperative organized and connected.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="p-8 rounded-lg border hover:shadow-lg transition">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">Analytics Dashboard</h3>
                    <p class="text-gray-600">
                        Get real-time insights with powerful analytics. Make data-driven decisions for your cooperative.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-8 rounded-lg border hover:shadow-lg transition">
                    <div class="text-4xl mb-4">💰</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">Financial Tracking</h3>
                    <p class="text-gray-600">
                        Track transactions, manage budgets, and maintain financial transparency across your organization.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="p-8 rounded-lg border hover:shadow-lg transition">
                    <div class="text-4xl mb-4">📅</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">Event Planning</h3>
                    <p class="text-gray-600">
                        Schedule meetings, organize events, and keep members informed with integrated event management.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="p-8 rounded-lg border hover:shadow-lg transition">
                    <div class="text-4xl mb-4">📱</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">Mobile Friendly</h3>
                    <p class="text-gray-600">
                        Access your cooperative management tools on any device. Work anytime, anywhere.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="p-8 rounded-lg border hover:shadow-lg transition">
                    <div class="text-4xl mb-4">🔐</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900">Secure & Reliable</h3>
                    <p class="text-gray-600">
                        Enterprise-grade security ensures your cooperative data is always protected and backed up.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section id="stats" class="py-20 bg-gradient-to-r">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8 text-center text-white">
                <div>
                    <div class="text-5xl font-bold mb-2">100+</div>
                    <p class="text-lg opacity-90">Active Cooperatives</p>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">10K+</div>
                    <p class="text-lg opacity-90">Total Members</p>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">99.9%</div>
                    <p class="text-lg opacity-90">Uptime</p>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">24/7</div>
                    <p class="text-lg opacity-90">Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold mb-6 text-gray-900">About COOP</h2>
                    <p class="text-gray-600 mb-4 text-lg">
                        COOP is a comprehensive cooperative management system designed to help organizations like yours succeed in the digital age.
                    </p>
                    <p class="text-gray-600 mb-4 text-lg">
                        We provide tools for member management, financial tracking, event planning, and much more. Our mission is to empower cooperatives with technology that brings members together and drives growth.
                    </p>
                    <p class="text-gray-600 text-lg">
                        Whether you're just starting out or scaling an established cooperative, COOP has the features you need to thrive.
                    </p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="text-2xl">✓</div>
                            <div>
                                <h3 class="font-bold text-gray-900">Easy to Use</h3>
                                <p class="text-gray-600">Intuitive interface designed for all skill levels</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="text-2xl">✓</div>
                            <div>
                                <h3 class="font-bold text-gray-900">Scalable</h3>
                                <p class="text-gray-600">Grows with your cooperative's needs</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="text-2xl">✓</div>
                            <div>
                                <h3 class="font-bold text-gray-900">Community Focused</h3>
                                <p class="text-gray-600">Built with cooperative values in mind</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="text-2xl">✓</div>
                            <div>
                                <h3 class="font-bold text-gray-900">Dedicated Support</h3>
                                <p class="text-gray-600">Expert assistance when you need it</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-green-600">
        <div class="max-w-4xl mx-auto px-4 text-center text-white">
            <h2 class="text-4xl font-bold mb-6">Ready to Transform Your Cooperative?</h2>
            <p class="text-xl mb-8 opacity-90">
                Join COOP and start managing your cooperative today.
            </p>

            @if (Route::has('login'))
                @auth
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="inline-block px-8 py-3 rounded-lg bg-white text-red-600 hover:bg-gray-100 transition font-semibold border-none cursor-pointer" style="font-family: inherit;">
                            Log Out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-block px-8 py-3 rounded-lg bg-white text-green-600 hover:bg-gray-100 transition font-semibold">
                        Sign In
                    </a>
                @endauth
            @endif
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="mb-2">&copy; 2026 COOP - Cooperative Management System. All rights reserved.</p>
            <div class="flex justify-center gap-6 text-sm">
                <a href="#" class="hover:text-green-400 transition">Privacy</a>
                <a href="#" class="hover:text-green-400 transition">Terms</a>
                <a href="#" class="hover:text-green-400 transition">Contact</a>
            </div>
        </div>
    </footer>
</body>
</html>