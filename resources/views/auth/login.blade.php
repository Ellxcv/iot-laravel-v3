<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 flex items-center justify-center p-4">
    
    <!-- Login Container -->
    <div class="w-full max-w-md">
        <!-- Logo/Brand Area -->
        <div class="text-center mb-8 animate-fade-in">
            <div class="inline-block p-4 bg-white/10 backdrop-blur-lg rounded-2xl mb-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Welcome Back</h1>
            <p class="text-indigo-200">Sign in to your account</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20 animate-slide-up">
            
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-xl text-green-100 text-sm animate-fade-in">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-xl text-red-100 text-sm animate-shake">
                    @if($errors->has('error'))
                        {{ $errors->first('error') }}
                    @else
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Input -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-white">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300"
                            placeholder="you@example.com"
                        >
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-white">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember" 
                        value="1"
                        class="w-4 h-4 rounded border-white/20 bg-white/10 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0"
                    >
                    <label for="remember" class="ml-2 text-sm text-indigo-200">Remember me</label>
                </div>

                <!-- Login Button -->
                <button 
                    type="submit"
                    class="w-full py-3 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transform transition-all duration-300 hover:scale-[1.02] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-transparent"
                >
                    Sign In
                </button>
            </form>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-indigo-200 text-sm">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-white font-semibold hover:text-indigo-300 transition-colors duration-300">
                        Create one now
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-indigo-300 text-sm">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slide-up {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .animate-fade-in {
            animation: fade-in 0.8s ease-out;
        }
        
        .animate-slide-up {
            animation: slide-up 0.6s ease-out;
        }
        
        .animate-shake {
            animation: shake 0.4s ease-in-out;
        }
    </style>
</body>
</html>
