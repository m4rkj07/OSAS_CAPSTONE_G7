<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div class="mt-4">
            <x-input-label for="login" :value="__('Username')" />
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 10a4 4 0 100-8 4 4 0 000 8z" />
                        <path fill-rule="evenodd" d="M2 18a8 8 0 1116 0H2z" clip-rule="evenodd" />
                    </svg>
                </span>
                <x-text-input id="login" class="block mt-1 w-full pl-10" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 1110 0v2h1a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1v-8a1 1 0 011-1h1zm2-2a3 3 0 116 0v2H7V7z" clip-rule="evenodd" />
                    </svg>
                </span>

                <input id="password" class="block mt-1 w-full pl-10 pr-10 border-gray-300 rounded-md shadow-sm"
                    type="password"
                    name="password"
                    required autocomplete="current-password" />

                <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"
                        id="togglePassword">
                    <svg id="eyeIcon" class="h-6 w-6 transition duration-300" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z" />
                        <line id="eyeSlashLine" x1="4" y1="4" x2="20" y2="20" stroke="currentColor" stroke-width="2"
                            class="transition duration-300" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-col mt-4">
            <div class="w-full">
                <x-primary-button class="w-full flex items-center justify-center">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
            <div class = "mt-4">
                <a class="text-sm rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"  
                    href="{{ route('password.request') }}"
                    style="color: #1447e6 !important;">
                    {{ __('Forgot your password?') }}
                </a>
            </div>
        </div>
    </form>

    <div id="loginLoader"
         class="fixed inset-0 bg-white/95 backdrop-blur-lg flex flex-col items-center justify-center z-[9999] hidden opacity-0 transition-all duration-500">
        <div class="text-center">
            <div class="w-20 h-20 mx-auto mb-6 relative">
                <div class="w-20 h-20 border-4 border-blue-200 rounded-full animate-spin border-t-blue-600"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Logging In</h3>
            <p class="text-gray-600">Please wait while we secure your session...</p>
            <div class="mt-6 w-48 h-2 bg-gray-200 rounded-full overflow-hidden mx-auto">
                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordField = document.getElementById("password");
            const toggleButton = document.getElementById("togglePassword");
            const eyeSlashLine = document.getElementById("eyeSlashLine");

            // Show eye slash line when password field is of type password
            if (passwordField.type === "password") {
                eyeSlashLine.classList.remove("hidden");
            }

            // Toggle password visibility
            toggleButton.addEventListener("click", function () {
                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    eyeSlashLine.classList.add("hidden");
                } else {
                    passwordField.type = "password";
                    eyeSlashLine.classList.remove("hidden");
                }
            });

            const form = document.getElementById("loginForm");
            const loader = document.getElementById("loginLoader");

            form.addEventListener("submit", function () {
                loader.classList.remove("hidden");
                loader.classList.add("opacity-100");
            });
        });
    </script>
</x-guest-layout>