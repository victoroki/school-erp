<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | {{ config('app.name') }}</title>
    <meta name="description" content="Sign in to {{ config('app.name') }} school management portal.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ── Design Tokens ─────────────────────────────────────────── */
        :root {
            --navy:          #111827;
            --navy-deep:     #0a0f1a;
            --gold:          #c8a55a;
            --gold-light:    #e2c97e;
            --surface:       #ffffff;
            --surface-2:     #f8fafc;
            --border:        #e4e8f0;
            --text:          #0f172a;
            --text-muted:    #64748b;
            --text-subtle:   #94a3b8;
            --error:         #ef4444;
            --error-bg:      #fef2f2;
            --input-focus:   #c8a55a;
            --radius-sm:     6px;
            --radius-md:     10px;
            --radius-lg:     16px;
            --radius-xl:     20px;

            /* Emil's custom easing curves */
            --ease-out-strong:   cubic-bezier(0.23, 1, 0.32, 1);
            --ease-in-out-strong: cubic-bezier(0.77, 0, 0.175, 1);
        }

        body.dark {
            --navy:          #0a0f1a;
            --navy-deep:     #050810;
            --surface:       #1a1f2e;
            --surface-2:     #111827;
            --border:        #2d3748;
            --text:          #f1f5f9;
            --text-muted:    #94a3b8;
            --text-subtle:   #64748b;
            --error-bg:      #2d1515;
        }

        /* ── Reset ─────────────────────────────────────────────────── */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ── Body / Background ─────────────────────────────────────── */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            background-color: var(--surface-2);
            background-image:
                radial-gradient(circle at 20% 20%, rgba(200,165,90,0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(17,24,39,0.08) 0%, transparent 50%);
            padding: 1.25rem;
            color: var(--text);
            /* Transition only background-color — never `all` */
            transition: background-color 0.3s var(--ease-out-strong),
                        color 0.3s var(--ease-out-strong);
        }

        /* ── Card ──────────────────────────────────────────────────── */
        .card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(0,0,0,0.04),
                0 4px 6px rgba(0,0,0,0.04),
                0 20px 40px rgba(0,0,0,0.08);

            /* Entry: starts from scale(0.95)+opacity 0 — nothing from scale(0) */
            animation: cardEnter 0.55s var(--ease-out-strong) both;
            transition: background-color 0.3s var(--ease-out-strong),
                        border-color 0.3s var(--ease-out-strong),
                        box-shadow 0.3s var(--ease-out-strong);
        }

        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(12px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* ── Header ────────────────────────────────────────────────── */
        .card-header {
            background: linear-gradient(145deg, var(--navy) 0%, var(--navy-deep) 100%);
            padding: 2.25rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Subtle decorative rings */
        .card-header::before,
        .card-header::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(200,165,90,0.12);
            pointer-events: none;
        }
        .card-header::before {
            width: 200px;
            height: 200px;
            top: -60px;
            right: -60px;
        }
        .card-header::after {
            width: 120px;
            height: 120px;
            bottom: -30px;
            left: -30px;
        }

        /* Logo — stagger item 1 */
        .logo-wrap {
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.95);
            border-radius: 18px;
            margin: 0 auto 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid rgba(200,165,90,0.4);
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);

            animation: staggerItem 0.5s var(--ease-out-strong) 0.1s both;
        }

        .logo-wrap img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        /* Brand title — stagger item 2 */
        .brand-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.01em;
            margin-bottom: 0.3rem;
            animation: staggerItem 0.5s var(--ease-out-strong) 0.17s both;
        }

        /* Subtitle — stagger item 3 */
        .brand-sub {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gold);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            animation: staggerItem 0.5s var(--ease-out-strong) 0.24s both;
        }

        /* Gold accent bar */
        .accent-bar {
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        /* ── Form body ─────────────────────────────────────────────── */
        .card-body {
            padding: 2rem 2rem 1.75rem;
        }

        /* Form title — stagger item 4 */
        .form-heading {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.2rem;
            animation: staggerItem 0.5s var(--ease-out-strong) 0.31s both;
        }
        .form-sub {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-bottom: 1.75rem;
            animation: staggerItem 0.5s var(--ease-out-strong) 0.35s both;
        }

        /* ── Field ─────────────────────────────────────────────────── */
        .field {
            margin-bottom: 1.1rem;
        }

        .field-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.45rem;
            /* Stagger items 5 and 6 */
        }
        .field:nth-child(1) .field-label { animation: staggerItem 0.5s var(--ease-out-strong) 0.38s both; }
        .field:nth-child(2) .field-label { animation: staggerItem 0.5s var(--ease-out-strong) 0.44s both; }

        .input-wrap {
            position: relative;
        }
        .field:nth-child(1) .input-wrap { animation: staggerItem 0.5s var(--ease-out-strong) 0.41s both; }
        .field:nth-child(2) .input-wrap { animation: staggerItem 0.5s var(--ease-out-strong) 0.47s both; }

        .input-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-subtle);
            font-size: 0.82rem;
            pointer-events: none;
            /* Specify exact property — not `all` */
            transition: color 180ms var(--ease-out-strong);
        }

        .field-input {
            width: 100%;
            height: 44px;
            padding: 0 1rem 0 2.6rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.875rem;
            color: var(--text);
            background: var(--surface);
            /* Specify exact properties */
            transition: border-color 180ms var(--ease-out-strong),
                        box-shadow 180ms var(--ease-out-strong),
                        background-color 0.3s var(--ease-out-strong);
            outline: none;
            appearance: none;
        }

        .field-input::placeholder {
            color: var(--text-subtle);
        }

        .field-input:focus {
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px rgba(200,165,90,0.15);
        }

        /* When focused, tint the adjacent icon */
        .field-input:focus ~ .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--gold);
        }

        /* Password toggle */
        .pw-toggle {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-subtle);
            font-size: 0.82rem;
            padding: 4px;
            transition: color 150ms var(--ease-out-strong),
                        transform 150ms var(--ease-out-strong);
        }
        @media (hover: hover) and (pointer: fine) {
            .pw-toggle:hover { color: var(--text-muted); }
        }
        .pw-toggle:active { transform: translateY(-50%) scale(0.9); }

        /* Error */
        .field-error {
            display: block;
            font-size: 0.75rem;
            color: var(--error);
            margin-top: 0.35rem;
            padding: 0.4rem 0.65rem;
            background: var(--error-bg);
            border-radius: var(--radius-sm);
            border-left: 2px solid var(--error);

            animation: errorEnter 0.25s var(--ease-out-strong) both;
        }

        @keyframes errorEnter {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Options row ───────────────────────────────────────────── */
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            animation: staggerItem 0.5s var(--ease-out-strong) 0.52s both;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--navy);
            cursor: pointer;
        }

        .forgot-link {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--gold);
            text-decoration: none;
            transition: color 150ms var(--ease-out-strong),
                        opacity 150ms var(--ease-out-strong);
        }
        @media (hover: hover) and (pointer: fine) {
            .forgot-link:hover { opacity: 0.75; }
        }

        /* ── Submit button ─────────────────────────────────────────── */
        .btn-submit {
            width: 100%;
            height: 46px;
            background: var(--navy);
            color: #ffffff;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            /* Specify exact properties — not `all` */
            transition: background-color 180ms var(--ease-out-strong),
                        transform 160ms var(--ease-out-strong),
                        box-shadow 180ms var(--ease-out-strong);
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 4px 8px rgba(17,24,39,0.18);

            animation: staggerItem 0.5s var(--ease-out-strong) 0.57s both;
        }

        /* Responsive hover only — no false positives on touch */
        @media (hover: hover) and (pointer: fine) {
            .btn-submit:hover {
                background: #1a2540;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 8px 20px rgba(17,24,39,0.22);
            }
        }

        /* Scale-on-press feedback — Emil's core principle */
        .btn-submit:active {
            transform: scale(0.97);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .btn-icon {
            transition: transform 200ms var(--ease-out-strong);
        }
        @media (hover: hover) and (pointer: fine) {
            .btn-submit:hover .btn-icon {
                transform: translateX(2px);
            }
        }

        /* ── Footer ────────────────────────────────────────────────── */
        .card-footer {
            text-align: center;
            padding: 1rem 2rem 1.5rem;
            border-top: 1px solid var(--border);
            animation: staggerItem 0.5s var(--ease-out-strong) 0.62s both;
            transition: border-color 0.3s var(--ease-out-strong);
        }

        .footer-motto {
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-subtle);
            margin-bottom: 0.3rem;
        }
        .footer-copy {
            font-size: 0.65rem;
            color: var(--text-subtle);
        }

        /* ── Stagger animation keyframes ───────────────────────────── */
        @keyframes staggerItem {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Dark mode toggle ──────────────────────────────────────── */
        .theme-btn {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            /* Only the properties that change */
            transition: background-color 0.3s var(--ease-out-strong),
                        border-color 0.3s var(--ease-out-strong),
                        color 0.3s var(--ease-out-strong),
                        transform 160ms var(--ease-out-strong),
                        box-shadow 180ms var(--ease-out-strong);
        }

        @media (hover: hover) and (pointer: fine) {
            .theme-btn:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                color: var(--gold);
            }
        }

        /* Scale-on-press on toggle too */
        .theme-btn:active {
            transform: scale(0.93);
        }

        /* ── Reduced motion ────────────────────────────────────────── */
        @media (prefers-reduced-motion: reduce) {
            .card,
            .logo-wrap,
            .brand-name,
            .brand-sub,
            .form-heading,
            .form-sub,
            .field-label,
            .input-wrap,
            .options-row,
            .btn-submit,
            .card-footer {
                animation: none;
                opacity: 1;
                transform: none;
            }
        }
    </style>
</head>
<body id="body">

    <div class="card" role="main">

        <!-- Header -->
        <div class="card-header">
            <div class="logo-wrap" aria-hidden="true">
                <img src="{{ asset('garikon-white-bg.png') }}" alt="{{ config('app.name') }} logo">
            </div>
            <p class="brand-name">{{ school_name() }}</p>
            <p class="brand-sub">School Management Portal</p>
        </div>

        <div class="accent-bar"></div>

        <!-- Form -->
        <div class="card-body">
            <h1 class="form-heading">Welcome back</h1>
            <p class="form-sub">Sign in to access your portal</p>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <!-- Email -->
                <div class="field">
                    <label class="field-label" for="login-email">Email address</label>
                    <div class="input-wrap">
                        <input
                            id="login-email"
                            type="email"
                            name="email"
                            class="field-input"
                            placeholder="name@school.edu"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                        >
                        <i class="fa-regular fa-envelope input-icon" aria-hidden="true"></i>
                    </div>
                    @error('email')
                        <span class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="field">
                    <label class="field-label" for="login-password">Password</label>
                    <div class="input-wrap">
                        <input
                            id="login-password"
                            type="password"
                            name="password"
                            class="field-input"
                            placeholder="••••••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <i class="fa-solid fa-lock input-icon" aria-hidden="true"></i>
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show password">
                            <i class="fa-regular fa-eye" id="pwIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Options -->
                <div class="options-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Keep me signed in
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-submit" id="loginBtn">
                    Sign in
                    <i class="fa-solid fa-arrow-right btn-icon" aria-hidden="true"></i>
                </button>

            </form>
        </div>

        <!-- Footer -->
        <div class="card-footer">
            <p class="footer-motto">Excellence Through Knowledge</p>
            <p class="footer-copy">© {{ date('Y') }} {{ config('app.name') }} Educational Systems</p>
        </div>

    </div>

    <!-- Dark mode toggle -->
    <button class="theme-btn" id="themeBtn" aria-label="Toggle dark mode">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
    </button>

    <script>
        /* ── Password visibility toggle ─────────────────────────── */
        const pwToggle = document.getElementById('pwToggle');
        const pwInput  = document.getElementById('login-password');
        const pwIcon   = document.getElementById('pwIcon');

        pwToggle.addEventListener('click', () => {
            const isHidden = pwInput.type === 'password';
            pwInput.type = isHidden ? 'text' : 'password';
            pwIcon.className = isHidden ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            pwToggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });

        /* ── Dark mode ──────────────────────────────────────────── */
        const body      = document.getElementById('body');
        const themeBtn  = document.getElementById('themeBtn');
        const themeIcon = document.getElementById('themeIcon');

        // Restore saved preference without animation flash
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') {
            body.classList.add('dark');
            themeIcon.className = 'fa-solid fa-sun';
        }

        themeBtn.addEventListener('click', () => {
            const isDark = body.classList.toggle('dark');
            themeIcon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>

</body>
</html>
