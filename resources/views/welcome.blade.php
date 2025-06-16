<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3a3845;
            --secondary: #826f66;
            --accent: #c69b7b;
            --light: #f7f5f2;
            --dark: #1a1a1a;
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--dark);
            background-color: var(--light);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        h1, h2, h3, h4 {
            font-family: 'EB Garamond', serif;
            font-weight: 500;
            letter-spacing: -0.5px;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            border-radius: 2px;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.85rem;
            transition: var(--transition);
            text-align: center;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--light);
            border: 1px solid var(--primary);
        }
        
        .btn-primary:hover {
            background-color: transparent;
            color: var(--primary);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--light);
        }
        
        .header {
            background-color: var(--light);
            padding: 1.5rem 0;
            position: fixed;
            width: 100%;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .hero {
            padding: 12rem 0 8rem;
            background: linear-gradient(rgba(247, 245, 242, 0.9), rgba(247, 245, 242, 0.9)), 
                        url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            text-align: center;
        }
        
        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 2px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            height: 100%;
            border-top: 3px solid var(--accent);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .footer {
            background-color: var(--primary);
            color: var(--light);
            padding: 3rem 0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container flex justify-between items-center">
            <a href="/" class="text-2xl font-serif font-medium text-primary">
                {{ config('app.name', 'Academie') }}
            </a>
            <nav class="hidden md:flex items-center space-x-8">
                <a href="#features" class="text-dark hover:text-accent transition">Features</a>
                @auth
                    <a href="{{ url('/home') }}" class="btn btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-dark hover:text-accent transition">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-outline">Register</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h1 class="text-4xl md:text-5xl font-serif mb-6 leading-tight">
                    Elevating Education Through<br>Thoughtful Design
                </h1>
                <p class="text-lg max-w-2xl mx-auto mb-10">
                    A sophisticated school management system designed for institutions that value both form and function.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('register') }}" class="btn btn-primary">Request Demo</a>
                    <a href="#features" class="btn btn-outline">Explore Features</a>
                </div>
            </div>
        </section>

        <section id="features" class="py-20 bg-white">
            <div class="container">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-serif mb-4">Designed for Excellence</h2>
                    <p class="max-w-2xl mx-auto">Thoughtfully crafted features that enhance the educational experience</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="feature-card">
                        <h3 class="text-xl font-serif mb-4">Student Management</h3>
                        <p>Comprehensive profiles with academic history, attendance records, and personalized learning plans.</p>
                    </div>
                    <div class="feature-card">
                        <h3 class="text-xl font-serif mb-4">Faculty Portal</h3>
                        <p>Streamlined tools for lesson planning, grade management, and student communication.</p>
                    </div>
                    <div class="feature-card">
                        <h3 class="text-xl font-serif mb-4">Administrative Suite</h3>
                        <p>Powerful analytics and reporting for data-driven decision making at all levels.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Academie') }}. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>