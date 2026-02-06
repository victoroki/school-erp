<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-navy: #1a263e;
            --accent-gold: #c5a059;
            --text-muted: #8e9aaf;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
            --card-bg: #ffffff;
            --input-bg: #ffffff;
            --input-text: #334155;
            --body-text: #1e293b;
            --dots-color: #cbd5e1;
        }

        body.dark-mode {
            --primary-navy: #0f172a;
            --accent-gold: #d4af37;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --bg-light: #0f172a;
            --card-bg: #1e293b;
            --input-bg: #0f172a;
            --input-text: #f1f5f9;
            --body-text: #f1f5f9;
            --dots-color: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            background-image: radial-gradient(var(--dots-color) 1px, transparent 1px);
            background-size: 24px 24px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--body-text);
            transition: background-color 0.3s, color 0.3s;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05), 0 20px 48px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            transition: background-color 0.3s;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .login-header {
            background-color: var(--primary-navy);
            padding: 40px 20px;
            text-align: center;
            border-bottom: 4px solid var(--accent-gold);
            position: relative;
        }

        .logo-container {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--accent-gold);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .logo-container i {
            font-size: 40px;
            color: var(--primary-navy);
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 28px;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-style: italic;
        }

        .portal-subtitle {
            color: var(--accent-gold);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .login-body {
            padding: 40px 45px;
        }

        .form-label {
            display: block;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 10px 15px 10px 45px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
            color: var(--input-text);
            transition: all 0.2s;
            background-color: var(--input-bg);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1);
        }

        .form-control::placeholder {
            color: #cbd5e1;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            cursor: pointer;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            cursor: pointer;
        }

        .forgot-password {
            font-size: 13px;
            color: var(--accent-gold);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            height: 52px;
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background-color 0.2s, transform 0.1s;
        }

        .btn-submit:hover {
            background-color: #111a2c;
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        .login-footer {
            margin-top: 40px;
            text-align: center;
        }

        .motto {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-muted);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .copyright {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: -15px;
            margin-bottom: 15px;
            display: block;
        }

        /* Dark mode toggle mock */
        .theme-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: var(--card-bg);
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--accent-gold);
            z-index: 1000;
            transition: all 0.3s;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
        }

    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="logo-container">
                <img src="{{ asset('garikon-white-bg.png') }}" alt="Garikon Logo" style="width: 80%; height: 80%; object-fit: contain;">
            </div>
            <h1 class="brand-title">{{ config('app.name', 'Garikon Academy') }}</h1>
            <p class="portal-subtitle">School Management Portal</p>
        </div>

        <div class="login-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Institutional Email Address</label>
                    <div class="input-group">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="name@school.edu" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Security Credentials</label>
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Keep me authenticated
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-password">Forgot Password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">
                    Enter Portal <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>

                <div class="login-footer">
                    <p class="motto">Excellence Through Knowledge</p>
                    <p class="copyright">© {{ date('Y') }} {{ config('app.name') }} Educational Systems</p>
                </div>
            </form>
        </div>
    </div>

    <div class="theme-toggle" id="themeToggle">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;

        // Check for saved theme
        const currentTheme = localStorage.getItem('theme');
        if (currentTheme === 'dark') {
            body.classList.add('dark-mode');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            }
        });
    </script>

</body>
</html>
