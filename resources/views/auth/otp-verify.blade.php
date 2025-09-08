<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo/bcplogin.png') }}">
    <title>OSAS: Two-Factor Authentication</title>
</head>
<body>
    <x-guest-layout>
    <x-slot name="logo">
        {{-- Optional logo --}}
    </x-slot>

    <h2 class="text-2xl font-bold text-gray-900 mb-2">2-step verification</h2>
    <p class="text-sm text-gray-600 mb-6">
        To keep your account safe, we need to check that this is really you.
    </p>

    <div class="flex items-center space-x-3 bg-gray-50 p-4 rounded mb-6">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16 12H8m0 0V8m0 4v4m8-4a4 4 0 00-8 0 4 4 0 008 0z" />
        </svg>
        <div>
            <p class="text-sm font-medium text-gray-800">Verify it's you by email</p>
            <p class="text-sm text-gray-600">
                We've just sent a 6-digit code to your email.
            </p>
        </div>
    </div>

    {{-- Show only when OTP is resent --}}
    @if (session('otp_resent'))
        <div id="success-message" class="mb-4 rounded-md bg-green-50 p-4 transition-opacity duration-500">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-4.41l5.3-5.3a1 1 0 10-1.4-1.42L9 11.17 7.1 9.3a1 1 0 00-1.4 1.42l2.6 2.58a1 1 0 001.4 0z"
                        clip-rule="evenodd" />
                </svg>
                <p class="ml-2 text-sm font-medium text-green-800">
                    {{ session('otp_resent') }}
                </p>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                setTimeout(() => {
                    let el = document.getElementById("success-message");
                    if (el) {
                        el.classList.add("opacity-0");
                        setTimeout(() => el.remove(), 500);
                    }
                }, 3000);
            });
        </script>
    @endif

    {{-- Error Message --}}
    @if (session('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-8-4a1 1 0 100 2 1 1 0 000-2zm1 4a1 1 0 10-2 0v4a1 1 0 102 0v-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="ml-2 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </p>
            </div>
        </div>
    @endif


    {{-- OTP Verification Form --}}
    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-1">Enter code</label>
        <input id="otp_code" name="otp_code" type="text" autofocus
            class="block w-full mb-1 px-4 py-2 border rounded-md shadow-sm
            {{ $errors->has('otp_code') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500' }}" />

        @error('otp_code')
            <p class="text-sm text-red-600 mb-4">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md hover:bg-blue-700">
            Continue
        </button>
    </form>

    {{-- Resend OTP --}}
    <form action="{{ route('otp.resend') }}" method="POST" class="mt-3 text-center">
        @csrf
        <button id="resend-btn" type="submit" class="text-sm text-blue-600 hover:text-blue-800">
            Didn’t get the code? Resend OTP
        </button>
        <p id="cooldown-text" class="text-sm text-gray-500 mt-2 hidden"></p>
    </form>


    {{-- Separate Cancel Form --}}
    <form action="{{ route('otp.cancel') }}" method="POST" class="mt-4 text-center">
        @csrf
        <button type="submit"
            class="text-sm text-gray-600 hover:text-gray-800">
            Cancel login
        </button>
    </form>
    <x-slot name="footer">
        <p class="text-center text-sm text-gray-500 mt-6">
            &copy; {{ date('Y') }} OSAS. All rights reserved.
        </p>
    </x-slot>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let cooldown = {{ (int)(session('last_otp_sent') ? max(0, 30 - now()->diffInSeconds(session('last_otp_sent'))) : 0) }};

            const resendBtn = document.getElementById("resend-btn");
            const cooldownText = document.getElementById("cooldown-text");

            if (cooldown > 0) {
                startCooldown(cooldown);
            }

            function startCooldown(seconds) {
                resendBtn.disabled = true;
                resendBtn.classList.add("opacity-50", "cursor-not-allowed");
                cooldownText.classList.remove("hidden");

                let interval = setInterval(() => {
                    cooldownText.innerText = `Please wait ${seconds} seconds before resending.`;
                    seconds--;

                    if (seconds < 0) {
                        clearInterval(interval);
                        resendBtn.disabled = false;
                        resendBtn.classList.remove("opacity-50", "cursor-not-allowed");
                        cooldownText.classList.add("hidden");
                    }
                }, 1000);
            }
        });
    </script>
    </x-guest-layout>
</body>
</html>
    