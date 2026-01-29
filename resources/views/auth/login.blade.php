<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .login-logo img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 5px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
        }

        .remember-me label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
            margin: 0;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            padding: 20px 30px 30px;
            border-top: 1px solid #f0f0f0;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            padding: 0 15px;
            color: #999;
            font-size: 13px;
        }

        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
            }

            .login-body {
                padding: 30px 20px;
            }

            .login-header {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="{{ asset('garikon-white-bg.png') }}" alt="Logo">
                </div>
                <h1>{{ config('app.name') }}</h1>
                <p>School Management System</p>
            </div>

            <div class="login-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input 
                                type="email" 
                                id="email"
                                name="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email') }}" 
                                placeholder="Enter your email"
                                required 
                                autofocus
                            >
                        </div>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password"
                                name="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                placeholder="Enter your password"
                                required
                            >
                        </div>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Keep me signed in</label>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                    </button>
                </form>
            </div>

            <div class="login-footer">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                        <i class="fas fa-key mr-1"></i> Forgot your password?
                    </a>
                @endif
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; color: white; font-size: 13px; opacity: 0.9;">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
