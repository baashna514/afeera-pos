<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-[#070b14]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - SmartPOS Portal</title>

    <!-- Tailwind CSS & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #e9f1fa inset !important;
            -webkit-text-fill-color: #0f172a !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 py-8 sm:py-12 bg-[#070b14] text-slate-100 antialiased selection:bg-[#da1705] selection:text-white">
    <div class="w-full max-w-[460px] my-auto">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center mb-3">
                <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-2xl bg-[#0c101b] border-2 border-[#da1705] p-2.5 shadow-xl shadow-[#da1705]/20 flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" class="w-full h-full object-contain" alt="Application Logo">
                </div>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center justify-center gap-1.5">
                <span>Smart<span class="text-[#da1705]">POS</span></span>
                <span class="text-slate-300 font-bold text-xl">Portal</span>
            </h1>
            <p class="text-xs font-medium text-slate-400 mt-1">Enterprise Point of Sale & Inventory Management</p>
        </div>

        <!-- Login Card -->
        <div class="bg-[#0d1527]/95 border border-slate-800/90 rounded-3xl shadow-2xl p-6 sm:p-8 backdrop-blur">
            <div class="mb-5">
                <h2 class="text-xl font-extrabold text-white tracking-tight">Sign In to Your Account</h2>
                <p class="text-xs text-slate-400 mt-1">Please enter your authorized login credentials below</p>
            </div>

            <!-- Flash Alerts & Session Status -->
            @if(session('status'))
                <div class="mb-4 p-3 bg-[#092233] border border-[#0d4a6b] text-[#38bdf8] rounded-xl text-xs flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-info text-[#38bdf8] text-sm shrink-0"></i>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-4 p-3 bg-[#092233] border border-[#0d4a6b] text-[#38bdf8] rounded-xl text-xs flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-info text-[#38bdf8] text-sm shrink-0"></i>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3.5 bg-rose-950/80 border border-rose-800/80 text-rose-300 rounded-xl text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 mt-0.5 text-sm shrink-0"></i>
                    <div>
                        <span class="font-bold block mb-0.5">Authentication Failed</span>
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-regular fa-envelope text-sm"></i>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 bg-[#e9f1fa] text-slate-900 border-2 border-transparent focus:border-[#da1705] focus:bg-white rounded-xl text-sm font-medium focus:outline-none transition shadow-sm placeholder-slate-400"
                               placeholder="admin@example.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                               class="w-full pl-10 pr-10 py-2.5 bg-[#e9f1fa] text-slate-900 border-2 border-transparent focus:border-[#da1705] focus:bg-white rounded-xl text-sm font-medium focus:outline-none transition shadow-sm placeholder-slate-400"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-800 transition">
                            <i id="password-toggle-icon" class="fa-regular fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-300 select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#e9f1fa] border-slate-400 text-[#da1705] focus:ring-[#da1705] focus:ring-offset-[#0d1527] cursor-pointer">
                        <span class="text-slate-300 font-normal">Remember me on this device</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full flex items-center justify-center gap-2.5 py-3 px-4 bg-[#da1705] hover:bg-[#b81204] active:scale-[0.99] text-white font-bold text-base rounded-xl shadow-xl shadow-[#da1705]/30 hover:shadow-[#da1705]/50 transition-all duration-200 mt-2">
                    <i class="fa-solid fa-right-to-bracket text-base"></i>
                    <span>Sign In</span>
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-xs text-slate-500">
            SmartPOS System &copy; {{ date('Y') }} • All rights reserved
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const icon = document.getElementById('password-toggle-icon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwdInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
