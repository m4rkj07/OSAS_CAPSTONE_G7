<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('logo/bcplogin.png') }}">
    <title>Office of Safety and Security</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    /* Floating button style (always visible) */
    .floating-button {
        position: fixed;
        right: 20px;
        bottom: 24px;
        z-index: 60;
        box-shadow: 0 6px 18px rgba(20, 20, 40, 0.12);
        transition: transform .12s ease, opacity .12s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: floatMove 3s ease-in-out infinite; /* Added animation */
    }

    /* Click feedback */
    .floating-button:active {
        transform: scale(.95);
    }

    .focus-ring {
        outline: none;
    }

    /* Floating animation keyframes */
    @keyframes floatMove {
        0% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
        100% { transform: translateY(0); }
    }
</style>

</head>

<body class="min-h-screen font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <a href="/">
                <x-application-logo class="w-40 h-40 fill-current text-gray-500" />
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

    <!-- Quick Action Floating Button -->
    <button class="floating-button w-14 h-14 rounded-full text-white flex items-center justify-center focus-ring bg-blue-600"
        id="quickAction" title="Quick Actions">
        <i class="fas fa-shield-alt text-white text-lg"></i>
    </button>
    <script>
    document.getElementById('quickAction').addEventListener('click', function () {
        Swal.fire({
            title: 'Office of Safety and Security',
            html: `
                <div class="grid grid-cols-1 mt-4 place-items-center">
                    <button id="openChatbot" 
                        class="quick-action-btn p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200 text-center">
                        <i class="fas fa-robot text-blue-600 text-2xl mb-2"></i>
                        <p class="text-sm font-medium text-blue-800">OSAS AI Reporter</p>
                    </button>
                </div>
            `,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-xl shadow-2xl'
            },
            didOpen: () => {
                document.getElementById('openChatbot').addEventListener('click', function () {
                    window.open('/chatbot', '_blank');
                });
            }
        });
    });
    </script>

</body>

</html>