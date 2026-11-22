<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Sign in | {{ config('app.name') }}</title>
    <meta name="description" content="Sign in to {{ config('app.name') }} for staff, parents and students.">
    <meta name="theme-color" content="#F7F6F3">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Geist+Mono:wght@400&family=Newsreader:opsz,wght@6..72,400&display=swap">

    <style>
        :root {
            /* Warm neutrals, never pure black or white. */
            --canvas:    #F7F6F3;
            --surface:   #FEFEFD;
            --field:     #FBFBFA;
            --line:      #EAEAE7;
            --ink:       #151514;
            --ink-2:     #2F3437;
            --muted:     #787774;
            --faint:     #A5A49F;
            --btn-hover: #333331;

            --red-bg:   #FDEBEC; --red:   #9F2F2D;
            --green-bg: #EDF3EC; --green: #346538;

            --sans:  "Geist", "SF Pro Display", "Helvetica Neue", system-ui, sans-serif;
            --serif: "Newsreader", "Lyon Text", Georgia, serif;
            --mono:  "Geist Mono", "SF Mono", ui-monospace, monospace;
            --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; }

        body {
            margin: 0;
            font-family: var(--sans);
            font-size: 15px;
            line-height: 1.6;
            color: var(--ink-2);
            background: var(--canvas);
            -webkit-font-smoothing: antialiased;
        }

        .page {
            min-height: 100%;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
        }

        /* ── School panel ────────────────────────────────────────── */

        .brand {
            background: var(--canvas);
            border-right: 1px solid var(--line);
            padding: 40px 56px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 56px;
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--surface);
            border: 1px solid var(--line);
            padding: 4px;
        }

        .brand-name {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.01em;
        }

        .brand-body h2 {
            margin: 0 0 48px;
            font-family: var(--serif);
            font-weight: 400;
            font-size: clamp(32px, 3.4vw, 46px);
            line-height: 1.1;
            letter-spacing: -0.025em;
            color: var(--ink);
            max-width: 16ch;
        }

        /* Plain definition list, divided by hairlines. No cards. */
        .facts {
            margin: 0;
            max-width: 480px;
            border-top: 1px solid var(--line);
        }

        .facts > div {
            display: grid;
            grid-template-columns: 112px 1fr;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid var(--line);
        }

        .facts dt {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--ink);
        }

        .facts dd {
            margin: 0;
            font-size: 13.5px;
            line-height: 1.55;
            color: var(--muted);
        }

        .brand-foot {
            margin: 0;
            font-family: var(--mono);
            font-size: 11.5px;
            color: var(--faint);
        }

        /* ── Form panel ──────────────────────────────────────────── */

        .form-side {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            background: var(--surface);
        }

        .form-box {
            width: 100%;
            max-width: 360px;
        }

        .form-box h1 {
            margin: 0 0 8px;
            font-family: var(--serif);
            font-weight: 400;
            font-size: 36px;
            line-height: 1.1;
            letter-spacing: -0.025em;
            color: var(--ink);
        }

        .form-intro {
            margin: 0 0 32px;
            font-size: 14.5px;
            color: var(--muted);
        }

        .notice {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            line-height: 1.45;
            margin-bottom: 24px;
        }

        .notice-error { background: var(--red-bg);   color: var(--red); }
        .notice-ok    { background: var(--green-bg); color: var(--green); }

        .field { margin-bottom: 20px; }

        .field-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 8px;
        }

        label.field-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--ink);
        }

        .input { position: relative; }

        .input input {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            font: inherit;
            font-size: 15px;
            color: var(--ink);
            background: var(--field);
            border: 1px solid var(--line);
            border-radius: 6px;
            outline: none;
            transition: border-color 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
        }

        .input input::placeholder { color: var(--faint); }

        @media (hover: hover) and (pointer: fine) {
            .input input:hover { border-color: #DADAD6; }
        }

        .input input:focus {
            background: var(--surface);
            border-color: var(--ink);
            box-shadow: 0 0 0 3px rgba(21, 21, 20, 0.06);
        }

        .input input[aria-invalid="true"] { border-color: var(--red); }
        .input.has-toggle input { padding-right: 46px; }

        .pw-toggle {
            position: absolute;
            right: 4px;
            top: 4px;
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 120ms ease, color 120ms ease;
        }

        .pw-toggle svg { width: 17px; height: 17px; }
        .pw-toggle:hover { background: var(--canvas); color: var(--ink); }
        .pw-toggle:focus-visible { outline: 2px solid var(--ink); outline-offset: 0; }

        .field-error {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            color: var(--red);
        }

        .link {
            font-size: 13px;
            color: var(--muted);
            text-decoration: underline;
            text-decoration-color: var(--line);
            text-underline-offset: 3px;
            transition: color 120ms ease, text-decoration-color 120ms ease;
        }

        .link:hover { color: var(--ink); text-decoration-color: var(--ink); }
        .link:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; border-radius: 2px; }

        .remember {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 4px 0 28px;
            font-size: 14px;
            color: var(--ink-2);
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: var(--ink);
            margin: 0;
        }

        .submit {
            width: 100%;
            height: 44px;
            border: 0;
            border-radius: 6px;
            background: var(--ink);
            color: var(--surface);
            font: inherit;
            font-size: 14.5px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 150ms ease, transform 160ms var(--ease-out);
        }

        .submit:hover { background: var(--btn-hover); }
        .submit:active { transform: scale(0.97); }
        .submit:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; }
        .submit[disabled] { opacity: 0.7; cursor: progress; }

        .form-foot {
            margin: 32px 0 0;
            padding-top: 20px;
            border-top: 1px solid var(--line);
            font-size: 13px;
            color: var(--muted);
            line-height: 1.55;
        }

        @media (prefers-reduced-motion: reduce) {
            .submit, .input input { transition: none; }
            .submit:active { transform: none; }
        }

        /* ── Small screens ───────────────────────────────────────── */

        @media (max-width: 960px) {
            .page {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
            }

            .brand {
                padding: 18px 20px;
                gap: 0;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .brand-body,
            .brand-foot { display: none; }

            .form-side {
                align-items: flex-start;
                padding: 40px 20px 48px;
            }

            .form-box h1 { font-size: 32px; }
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
            <h2>Fees, classes and results for the whole school.</h2>

            <dl class="facts">
                <div>
                    <dt>Fees</dt>
                    <dd>Cash, bank or M-Pesa, with a receipt printed on the spot.</dd>
                </div>
                <div>
                    <dt>Academics</dt>
                    <dd>CBC assessments, exams and report cards.</dd>
                </div>
                <div>
                    <dt>Families</dt>
                    <dd>Parents and students see balances, attendance and results.</dd>
                </div>
            </dl>
        </div>

        <p class="brand-foot">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </section>

    <main class="form-side">
        <div class="form-box">
            <h1>Sign in</h1>
            <p class="form-intro">Use the email and password your school gave you.</p>

            @if (session('status'))
                <div class="notice notice-ok" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
                <div class="notice notice-error" role="alert">{{ $errors->first() }}</div>
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
                            <svg id="pwShow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="pwHide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M3 3l18 18"/><path d="M10.6 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6C3.9 8.4 2 12 2 12s3.5 7 10 7c1.9 0 3.5-.5 4.9-1.3"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
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

            <p class="form-foot">Can't get in? Ask the school office to reset your account.</p>
        </div>
    </main>

</div>

<script>
    (function () {
        var toggle = document.getElementById('pwToggle');
        var input = document.getElementById('login-password');
        var showIcon = document.getElementById('pwShow');
        var hideIcon = document.getElementById('pwHide');

        toggle.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            showIcon.hidden = show;
            hideIcon.hidden = !show;
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
