<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Sign in | {{ config('app.name') }}</title>
    <meta name="description" content="Sign in to {{ config('app.name') }} for staff, parents and students.">
    <meta name="theme-color" content="#17325f">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css"
          integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog=="
          crossorigin="anonymous">

    <style>
        :root {
            --navy-900: #0e2140;
            --navy-800: #12294f;
            --navy-700: #17325f;
            --navy-600: #1d3f7a;
            --navy-50:  #eef3fa;
            --ink:      #142033;
            --ink-2:    #3b4658;
            --muted:    #6b7587;
            --faint:    #98a1b0;
            --line:     #d9dfe8;
            --canvas:   #f5f7fa;
            --white:    #ffffff;
            --rose:     #b42335;
            --rose-bg:  #fdf1f2;
            --green:    #0f6b4b;
            --green-bg: #eefaf4;
            --ring:     0 0 0 3px rgba(29, 63, 122, 0.22);
            --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
        }

        * { box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            margin: 0;
            font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--ink);
            background: var(--canvas);
            -webkit-font-smoothing: antialiased;
        }

        .page {
            min-height: 100%;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
        }

        /* ── Brand panel ─────────────────────────────────────────── */

        .brand {
            background: var(--navy-700);
            color: #e6ecf6;
            padding: 48px 56px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 40px;
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #fff;
            padding: 4px;
        }

        .brand-name {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.01em;
        }

        .brand-body h2 {
            margin: 0 0 14px;
            font-size: clamp(26px, 2.6vw, 34px);
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #fff;
            max-width: 15ch;
        }

        .brand-body p {
            margin: 0 0 28px;
            font-size: 15px;
            line-height: 1.6;
            color: #c3d0e5;
            max-width: 40ch;
        }

        .brand-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 14px;
        }

        .brand-list li {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            font-size: 14px;
            line-height: 1.5;
            color: #dfe6f2;
        }

        .brand-list i {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .brand-list strong {
            display: block;
            color: #fff;
            font-weight: 600;
        }

        .brand-foot {
            font-size: 12.5px;
            color: #9fb2d1;
        }

        /* ── Form panel ──────────────────────────────────────────── */

        .form-side {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            background: var(--white);
        }

        .form-box {
            width: 100%;
            max-width: 380px;
        }

        .form-box h1 {
            margin: 0 0 6px;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .form-intro {
            margin: 0 0 28px;
            font-size: 14.5px;
            color: var(--muted);
            line-height: 1.5;
        }

        .notice {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            line-height: 1.45;
            margin-bottom: 20px;
        }

        .notice-error { background: var(--rose-bg); color: var(--rose); border: 1px solid #f6d3d8; }
        .notice-ok    { background: var(--green-bg); color: var(--green); border: 1px solid #cdeedd; }

        .field { margin-bottom: 18px; }

        .field-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 7px;
        }

        label.field-label {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--ink-2);
        }

        .input {
            position: relative;
        }

        .input input {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            font: inherit;
            font-size: 15px;
            color: var(--ink);
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 10px;
            outline: none;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .input input::placeholder { color: var(--faint); }

        .input input:focus {
            border-color: var(--navy-600);
            box-shadow: var(--ring);
        }

        .input input[aria-invalid="true"] {
            border-color: var(--rose);
        }

        .input.has-toggle input { padding-right: 48px; }

        .pw-toggle {
            position: absolute;
            right: 4px;
            top: 4px;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: background-color 120ms ease, color 120ms ease;
        }

        .pw-toggle:hover { background: var(--canvas); color: var(--ink); }
        .pw-toggle:focus-visible { outline: 2px solid var(--navy-600); outline-offset: 0; }

        .field-error {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            color: var(--rose);
        }

        .link {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--navy-600);
            text-decoration: none;
        }

        .link:hover { text-decoration: underline; }
        .link:focus-visible { outline: 2px solid var(--navy-600); outline-offset: 2px; border-radius: 4px; }

        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 4px 0 24px;
            font-size: 14px;
            color: var(--ink-2);
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 17px;
            height: 17px;
            accent-color: var(--navy-600);
            margin: 0;
        }

        .submit {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 10px;
            background: var(--navy-600);
            color: #fff;
            font: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 150ms ease, transform 160ms var(--ease-out);
        }

        .submit:hover { background: var(--navy-700); }
        .submit:active { transform: scale(0.98); }
        .submit:focus-visible { outline: none; box-shadow: var(--ring); }
        .submit[disabled] { opacity: 0.75; cursor: progress; }

        .form-foot {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #edf0f4;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5;
        }

        /* ── Small screens ───────────────────────────────────────── */

        @media (max-width: 900px) {
            .page {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
            }

            .brand {
                padding: 20px 24px;
                gap: 0;
            }

            .brand-body,
            .brand-foot { display: none; }

            .form-side {
                align-items: flex-start;
                padding: 32px 20px 40px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .submit, .input input { transition: none; }
            .submit:active { transform: none; }
        }
    </style>
</head>
<body>
<div class="page">

    <section class="brand" aria-label="{{ school_name() }}">
        <div class="brand-top">
            <img class="brand-mark" src="{{ asset('garikon-school.svg') }}" alt="">
            <span class="brand-name">{{ school_name() }}</span>
        </div>

        <div class="brand-body">
            <h2>Everything your school runs, in one place.</h2>
            <p>Fees, academics, attendance and communication for staff, with a portal for parents and students.</p>
            <ul class="brand-list">
                <li><i class="fas fa-receipt" aria-hidden="true"></i><span><strong>Fees and receipts</strong>Collect by cash, bank or M-Pesa and issue receipts on the spot.</span></li>
                <li><i class="fas fa-book-open" aria-hidden="true"></i><span><strong>Academics</strong>Classes, CBC assessments, exams and report cards.</span></li>
                <li><i class="fas fa-users" aria-hidden="true"></i><span><strong>Parents and students</strong>Balances, attendance and results, on the web or the app.</span></li>
            </ul>
        </div>

        <p class="brand-foot">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </section>

    <main class="form-side">
        <div class="form-box">
            <h1>Sign in</h1>
            <p class="form-intro">Use the email and password your school gave you.</p>

            @if (session('status'))
                <div class="notice notice-ok" role="status">
                    <i class="fas fa-check-circle" aria-hidden="true"></i><span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
                <div class="notice notice-error" role="alert">
                    <i class="fas fa-exclamation-circle" aria-hidden="true"></i><span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                @csrf

                <div class="field">
                    <div class="field-head">
                        <label class="field-label" for="login-email">Email</label>
                    </div>
                    <div class="input">
                        <input id="login-email" type="email" name="email" value="{{ old('email') }}"
                               placeholder="you@school.ac.ke" autocomplete="username" inputmode="email"
                               required autofocus
                               @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    </div>
                    @error('email')
                        <span class="field-error" id="email-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <div class="field-head">
                        <label class="field-label" for="login-password">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="link">Forgot password?</a>
                        @endif
                    </div>
                    <div class="input has-toggle">
                        <input id="login-password" type="password" name="password"
                               autocomplete="current-password" required
                               @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show password" aria-pressed="false">
                            <i class="far fa-eye" id="pwIcon" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-error" id="password-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Keep me signed in on this device
                </label>

                <button type="submit" class="submit" id="loginBtn">Sign in</button>
            </form>

            <p class="form-foot">Staff, parents and students all sign in here. Can't get in? Ask the school office to reset your account.</p>
        </div>
    </main>

</div>

<script>
    (function () {
        var toggle = document.getElementById('pwToggle');
        var input = document.getElementById('login-password');
        var icon = document.getElementById('pwIcon');

        toggle.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'far fa-eye-slash' : 'far fa-eye';
            toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
        });

        // One submit only: the button says it is working and cannot be pressed twice.
        document.getElementById('loginForm').addEventListener('submit', function () {
            var button = document.getElementById('loginBtn');
            button.disabled = true;
            button.textContent = 'Signing in…';
        });
    })();
</script>
</body>
</html>
