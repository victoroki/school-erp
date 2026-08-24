<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Portal | {{ config('app.name') }}</title>
    <meta name="description" content="Create the administrator account for {{ config('app.name') }}.">
    <meta name="robots" content="noindex, nofollow">

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

            --ease-out-strong:    cubic-bezier(0.23, 1, 0.32, 1);
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
            transition: background-color 0.3s var(--ease-out-strong),
                        color 0.3s var(--ease-out-strong);
        }

        /* ── Card ──────────────────────────────────────────────────── */
        .card {
            width: 100%;
            max-width: 440px;
            background: var(--surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(0,0,0,0.04),
                0 4px 6px rgba(0,0,0,0.04),
                0 20px 40px rgba(0,0,0,0.08);

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
            padding: 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

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

        .logo-wrap {
            width: 68px;
            height: 68px;
            background: rgba(255,255,255,0.95);
            border-radius: 18px;
            margin: 0 auto 0.95rem;
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

        .brand-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.01em;
            margin-bottom: 0.3rem;
            animation: staggerItem 0.5s var(--ease-out-strong) 0.17s both;
        }

        .brand-sub {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gold);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            animation: staggerItem 0.5s var(--ease-out-strong) 0.24s both;
        }

        .accent-bar {
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        /* ── Form body ─────────────────────────────────────────────── */
        .card-body {
            padding: 1.75rem 2rem 1.75rem;
        }

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
            margin-bottom: 1.5rem;
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
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-subtle);
            font-size: 0.82rem;
            pointer-events: none;
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

        .field-input:focus ~ .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--gold);
        }

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

        .password-hint {
            font-size: 0.72rem;
            color: var(--text-subtle);
            margin-top: 0.4rem;
        }

        /* ── One-time notice ───────────────────────────────────────── */
        .setup-notice {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            background: rgba(200,165,90,0.08);
            border: 1px solid rgba(200,165,90,0.25);
            border-radius: var(--radius-md);
            padding: 0.65rem 0.8rem;
            margin-bottom: 1.25rem;
            animation: staggerItem 0.5s var(--ease-out-strong) 0.36s both;
        }

        .setup-notice i {
            color: var(--gold);
            font-size: 0.85rem;
            margin-top: 0.1rem;
        }

        .setup-notice p {
            font-size: 0.75rem;
            line-height: 1.45;
            color: var(--text-muted);
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
            margin-top: 0.25rem;
            transition: background-color 180ms var(--ease-out-strong),
                        transform 160ms var(--ease-out-strong),
                        box-shadow 180ms var(--ease-out-strong);
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 4px 8px rgba(17,24,39,0.18);

            animation: staggerItem 0.5s var(--ease-out-strong) 0.6s both;
        }

        .btn-submit[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        @media (hover: hover) and (pointer: fine) {
            .btn-submit:hover {
                background: #1a2540;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 8px 20px rgba(17,24,39,0.22);
            }
        }

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

        .theme-btn:active {
            transform: scale(0.93);
        }

        /* ── Stagger delays per field (up to 5) ────────────────────── */
        .card-body .field:nth-child(1) .field-label,
        .card-body .field:nth-child(1) .input-wrap { animation: staggerItem 0.5s var(--ease-out-strong) 0.36s both; }
        .card-body .field:nth-child(2) .field-label,
        .card-body .field:nth-child(2) .input-wrap { animation: staggerItem 0.5s var(--ease-out-strong) 0.4s both; }
        .card-body .field:nth-child(3) .field-label,
        .card-body .field:nth-child(3) .input-wrap { animation: staggerItem 0.5s var(--ease-out-strong) 0.45s both; }
        .card-body .field:nth-child(4) .field-label,
        .card-body .field:nth-child(4) .input-wrap { animation: staggerItem 0.5s var(--ease-out-strong) 0.5s both; }
        .card-body .field:nth-child(5) .field-label,
        .card-body .field:nth-child(5) .input-wrap { animation: staggerItem 0.5s var(--ease-out-strong) 0.55s both; }

        /* ── Reduced motion ────────────────────────────────────────── */
        @media (prefers-reduced-motion: reduce) {
            .card,
            .logo-wrap,
            .brand-name,
            .brand-sub,
            .form-heading,
            .form-sub,
            .setup-notice,
            .field-label,
            .input-wrap,
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
            <p class="brand-name">{{ old('school_name', school_name()) }}</p>
            <p class="brand-sub">School Onboarding</p>
        </div>

        <div class="accent-bar"></div>

        <!-- Form -->
        <div class="card-body">
            <h1 class="form-heading">Create your administrator account</h1>
            <p class="form-sub">Set up your school portal. This is a one-time step.</p>

            <div class="setup-notice">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <p>This setup link is single-use. Once you create the account it can no longer be opened.</p>
            </div>

            <form method="POST" action="{{ route('setup.store', ['token' => $token]) }}" novalidate>
                @csrf

                <!-- School name -->
                <div class="field">
                    <label class="field-label" for="setup-school">School name</label>
                    <div class="input-wrap">
                        <input
                            id="setup-school"
                            type="text"
                            name="school_name"
                            class="field-input"
                            placeholder="e.g. Riverside Primary School"
                            value="{{ old('school_name') }}"
                            autocomplete="organization"
                            required
                            autofocus
                        >
                        <i class="fa-solid fa-school input-icon" aria-hidden="true"></i>
                    </div>
                    @error('school_name')
                        <span class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Name -->
                <div class="field">
                    <label class="field-label" for="setup-name">Full name</label>
                    <div class="input-wrap">
                        <input
                            id="setup-name"
                            type="text"
                            name="name"
                            class="field-input"
                            placeholder="e.g. Jane Wanjiku"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            required
                        >
                        <i class="fa-regular fa-user input-icon" aria-hidden="true"></i>
                    </div>
                    @error('name')
                        <span class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div class="field">
                    <label class="field-label" for="setup-email">Email address</label>
                    <div class="input-wrap">
                        <input
                            id="setup-email"
                            type="email"
                            name="email"
                            class="field-input"
                            placeholder="name@school.edu"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                        >
                        <i class="fa-regular fa-envelope input-icon" aria-hidden="true"></i>
                    </div>
                    @error('email')
                        <span class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="field">
                    <label class="field-label" for="setup-password">Password</label>
                    <div class="input-wrap">
                        <input
                            id="setup-password"
                            type="password"
                            name="password"
                            class="field-input"
                            placeholder="••••••••••••"
                            autocomplete="new-password"
                            minlength="8"
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

                <!-- Confirm password -->
                <div class="field">
                    <label class="field-label" for="setup-password-confirm">Confirm password</label>
                    <div class="input-wrap">
                        <input
                            id="setup-password-confirm"
                            type="password"
                            name="password_confirmation"
                            class="field-input"
                            placeholder="••••••••••••"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                        <i class="fa-solid fa-lock input-icon" aria-hidden="true"></i>
                        <button type="button" class="pw-toggle" id="pwToggleConfirm" aria-label="Show password">
                            <i class="fa-regular fa-eye" id="pwIconConfirm"></i>
                        </button>
                    </div>
                </div>

                <p class="password-hint">Use at least 8 characters.</p>

                <!-- Submit -->
                <button type="submit" class="btn-submit" id="setupBtn">
                    <i class="fa-solid fa-rocket" aria-hidden="true"></i>
                    Create account &amp; continue
                </button>

            </form>
        </div>

        <!-- Footer -->
        <div class="card-footer">
            <p class="footer-motto">Excellence Through Knowledge</p>
            <p class="footer-copy">© {{ date('Y') }} {{ school_name() }} Educational Systems</p>
        </div>

    </div>

    <!-- Dark mode toggle -->
    <button class="theme-btn" id="themeBtn" aria-label="Toggle dark mode">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
    </button>

    <script>
        /* ── Password visibility toggle ─────────────────────────── */
        function bindPwToggle(btnId, inputId, iconId) {
            const btn  = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);

            btn.addEventListener('click', () => {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                icon.className = isHidden ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
                btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        }
        bindPwToggle('pwToggle', 'setup-password', 'pwIcon');
        bindPwToggle('pwToggleConfirm', 'setup-password-confirm', 'pwIconConfirm');

        /* ── Prevent double-submit ──────────────────────────────── */
        const setupForm = document.querySelector('form');
        const setupBtn  = document.getElementById('setupBtn');

        setupForm.addEventListener('submit', () => {
            setupBtn.disabled = true;
            setupBtn.querySelector('i').className = 'fa-solid fa-circle-notch fa-spin';
        });

        /* ── Dark mode ──────────────────────────────────────────── */
        const body      = document.getElementById('body');
        const themeBtn  = document.getElementById('themeBtn');
        const themeIcon = document.getElementById('themeIcon');

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
