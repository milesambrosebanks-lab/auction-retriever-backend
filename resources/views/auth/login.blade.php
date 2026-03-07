@extends('auth.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">

        <!-- Brand -->
        <div class="brand-header">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" fill="#C6F432"/>
                <path d="M8 12L11 15L16 9"
                      stroke="#1a1a1a"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"/>
            </svg>
            <span class="brand-name">{{ config('app.name', 'YourApp') }}</span>
        </div>

        <!-- Title -->
        <h2 class="form-title">Sign in to your account</h2>

        <!-- Form -->
        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            <!-- Email -->
            <div class="form-group">
                <label>Email</label>
                <div class="input-group">
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter your email"
                           class="form-input @error('email') input-error @enderror"
                           required autofocus>
                </div>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label>Password</label>
                <div class="input-group">
                    <input type="password"
                           name="password"
                           id="password"
                           placeholder="Enter your password"
                           class="form-input @error('password') input-error @enderror"
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">👁</button>
                </div>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- reCAPTCHA -->
            @if(config('settings.recaptcha') === 'yes')
                <div class="recaptcha-wrapper">
                    {!! htmlFormSnippet() !!}
                    @error('g-recaptcha-response')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            <!-- Submit -->
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
    </div>
</div>

<style>
*{box-sizing:border-box}

body{
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    background:#1a1a1a;
}

/* Center container */
.auth-container{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

/* Card */
.auth-card{
    width:100%;
    max-width:420px;
    background:#2a2a2a;
    padding:40px;
    border-radius:16px;
    box-shadow:0 30px 60px rgba(0,0,0,.5);
}

/* Brand */
.brand-header{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    margin-bottom:24px;
}

.brand-name{
    color:#fff;
    font-size:18px;
    font-weight:600;
}

/* Title */
.form-title{
    text-align:center;
    color:#fff;
    font-size:20px;
    margin-bottom:28px;
}

/* Form */
.form-group{margin-bottom:20px}

label{
    display:block;
    color:#ccc;
    font-size:13px;
    margin-bottom:8px;
}

.input-group{position:relative}

.form-input{
    width:100%;
    padding:14px;
    background:#3a3a3a;
    border:1px solid #444;
    border-radius:8px;
    color:#fff;
    font-size:14px;
}

.form-input:focus{
    outline:none;
    border-color:#C6F432;
    background:#404040;
}

.input-error{border-color:#ff4444}

.password-toggle{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    background:none;
    border:none;
    color:#aaa;
    cursor:pointer;
}

/* Button */
.btn-submit{
    width:100%;
    padding:14px;
    background:#C6F432;
    color:#1a1a1a;
    border:none;
    border-radius:8px;
    font-weight:600;
    font-size:15px;
    cursor:pointer;
    transition:.2s;
}

.btn-submit:hover{
    background:#d4ff4d;
    transform:translateY(-1px);
}

/* Errors */
.error-message{
    display:block;
    margin-top:6px;
    font-size:12px;
    color:#ff5a5a;
}

@media(max-width:480px){
    .auth-card{padding:28px}
}
</style>

<script>
function togglePassword(){
    const input=document.getElementById('password');
    input.type=input.type==='password'?'text':'password';
}
</script>
@endsection
