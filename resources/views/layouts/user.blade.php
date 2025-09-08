<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('logo/bcplogin.png') }}">
    <title>Office of the Safety and Security</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>

        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        aside::-webkit-scrollbar {
            width: 8px;
        }
        aside::-webkit-scrollbar-thumb {
            border-radius: 6px;
            transition: background 0.2s ease-in-out;
        }

        aside::-webkit-scrollbar-track,
        aside::-webkit-scrollbar-thumb {
            background: transparent;
        }

        aside:hover::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }
        aside:hover::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
        }
        aside:hover::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        body::-webkit-scrollbar {
            width: 6px;
        }

        body::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        body::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        body::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Sidebar Animations */
        .sidebar-hidden {
            transform: translateX(-100%);
        }

        .sidebar-mini {
            width: 70px !important;
        }

        .sidebar-mini .sidebar-text {
            opacity: 0;
            transform: translateX(-10px);
        }

        .sidebar-mini .mini-icon {
            transform: scale(1.1);
        }

        /* Navigation Enhancements */
        .nav-item {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 0;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            border-radius: 0 2px 2px 0;
            transition: height 0.3s ease;
        }

        .nav-item.active::before,
        .nav-item:hover::before {
            height: 70%;
        }

        .nav-item:hover {
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.1), rgba(59, 130, 246, 0.05));
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.15), rgba(59, 130, 246, 0.1));
            border-right: 3px solid #3b82f6;
        }

        /* Dropdown Animations */
        .dropdown-enter {
            opacity: 0;
            transform: translateY(-10px);
        }

        .dropdown-enter-active {
            opacity: 1;
            transform: translateY(0);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Glass Effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Loading States */
        .loading-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Floating Action Styles */
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

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .sidebar-hidden {
                transform: translateX(-100%);
            }
            
            .mobile-optimized {
                padding: 0.75rem;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .auto-dark {
                background-color: #1f2937;
                color: #f9fafb;
            }
        }

        
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            animation: pulse 2s infinite;
        }

        /* Enhanced Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        /* Enhanced Focus States */
        .focus-ring:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        .page-loading-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease-in-out;
}

.page-loading-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Update your existing loading-container and loading-spinner if needed */
.loading-container {
    position: relative;
    width: 60px;
    height: 60px;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #2563eb;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    position: absolute;
}

.loading-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #2563eb;
    font-size: 20px;
    z-index: 1;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

        [x-cloak] { display: none; }
    </style>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    

    <div class="flex relative">
        <!-- Enhanced Mobile Overlay -->
        <div id="sidebar-overlay" 
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 opacity-0 invisible transition-all duration-300 md:hidden">
        </div>

        <!-- Enhanced Sidebar -->
        <aside id="sidebar"
               class="overflow-y-auto bg-blue-900 to-blue-900 text-blue-50 shadow-2xl fixed top-0 left-0 h-screen w-[280px] z-50 transition-all duration-300 ease-in-out transform -translate-x-full md:translate-x-0 border-r border-blue-700/50"
               data-state="visible" aria-label="Main navigation">
            
            <!-- Sidebar Header -->
            <div class="px-6 py-6 border-b border-blue-700/30 bg-blue-900/50">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-shield-alt text-white text-lg"></i>
                        </div>
                        <div class="sidebar-text">
                            <h1 class="font-bold text-sm leading-tight">OSAS SERVICE</h1>
                            <p class="text-blue-300 text-xs">MANAGEMENT</p>
                        </div>
                    </div>
                    <button id="closeSidebar" 
                            class="md:hidden text-blue-200 hover:text-white hover:bg-blue-700/50 p-2 rounded-lg transition-all duration-200 focus-ring" 
                            aria-label="Close sidebar">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="flex-1 px-4 py-4 space-y-1">
                <!-- Main Menu Section -->
                <div>
                    <div class="flex items-center space-x-2 px-4 py-2">
                        
                        <h3 class="text-xs font-semibold text-blue-300 uppercase tracking-wider sidebar-text">Main Menu</h3>
                    </div>

                    
                        <x-side-nav-link href="{{ route('user.dashboard') }}" 
                                         :active="request()->routeIs('user.dashboard')" 
                                         class="nav-item flex items-center px-4 py-3 rounded-xl mb-7 transition-all duration-200" 
                                         >
                            <div class="flex items-center space-x-3 w-full">
                                <div class="mini-icon w-6 flex justify-center transition-transform duration-200">
                                    <i class="fas fa-home text-blue-200 group-hover:text-white"></i>
                                </div>
                                <span class="sidebar-text font-medium text-blue-100 group-hover:text-white transition-colors duration-200">Dashboard</span>
                            </div>
                        </x-side-nav-link>
                </div>
                <div class="border-b border-blue-800 mb-4 md:mb-7"></div>
                <!-- Account Section -->
                <div>
                    <div class="flex items-center space-x-2 px-4 py-2">
                        
                        <h3 class="text-xs font-semibold text-blue-300 uppercase tracking-wider sidebar-text mt-4">Account</h3>
                    </div>
                    
                    <x-side-nav-link href="{{ route('user.profile') }}" 
                                     :active="request()->routeIs('user.profile')" 
                                     class="nav-item flex items-center px-4 py-3 rounded-xl mb-7" 
                                     >
                        <div class="flex items-center space-x-3 w-full">
                            <div class="mini-icon w-6 flex justify-center transition-transform duration-200">
                                <i class="fas fa-user text-blue-200 group-hover:text-white"></i>
                            </div>
                            <span class="sidebar-text font-medium text-blue-100 group-hover:text-white transition-colors duration-200">Profile</span>
                        </div>
                    </x-side-nav-link>
                </div>
                <div class="border-b border-blue-800 mb-4 md:mb-7"></div>
                <!-- Report Section -->
                <div>
                    <div class="flex items-center space-x-2 px-4 py-2">
                        
                        <h3 class="text-xs font-semibold text-blue-300 uppercase tracking-wider sidebar-text mt-4">Reports</h3>
                    </div>
                    
                    <x-side-nav-link href="{{ route('user.report') }}" 
                                        :active="request()->routeIs('user.report')" 
                                        class="nav-item flex items-center px-4 py-3 rounded-xl mb-7" 
                                        >
                        <div class="flex items-center space-x-3 w-full">
                            <div class="mini-icon w-6 flex justify-center transition-transform duration-200">
                                <i class="fas fa-file-alt text-blue-200 group-hover:text-white"></i>
                            </div>
                            <span class="sidebar-text font-medium text-blue-100 group-hover:text-white transition-colors duration-200">Report</span>
                        </div>
                    </x-side-nav-link>
                </div>
            </div>
        </aside>

        <!-- Enhanced Main Content -->
        <div id="main-content" class="flex-1 min-h-screen md:ml-[280px] transition-all duration-300">
            <!-- Enhanced Top Navigation -->
            <nav class="bg-white/95 backdrop-blur-lg shadow-lg sticky top-0 z-30 border-b border-gray-200/50">
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="relative flex items-center justify-between h-16">
                        <!-- Left Side Controls -->
                        <div class="flex items-center space-x-4">
                            <button id="toggleSidebar"
                                    class="md:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-all duration-200"
                                    aria-label="Open sidebar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>
                            
                            <button id="toggleSidebarDesktop"
                                    class="hidden md:flex p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-all duration-200"
                                    
                                    aria-label="Toggle sidebar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>

                            <!-- Breadcrumb or Page Title could go here -->
                            <div class="hidden md:block">
                                {{-- Minimal Flat Breadcrumb --}}
                                <nav class="flex items-center text-sm text-gray-500" aria-label="Breadcrumb">
                                    <ol class="inline-flex items-center space-x-1">
                                        {{-- Home --}}
                                        <li>
                                            <span class="text-gray-700 font-medium">Dashboard</span>
                                        </li>

                                        {{-- Dynamic Segments --}}
                                        @php
                                            $segments = request()->segments();
                                        @endphp

                                        @foreach($segments as $index => $segment)
                                            @continue($index === 0 && $segment === 'user')
                                            @continue($segment === 'dashboard')
                                            
                                            @php
                                                $name = ucwords(str_replace('-', ' ', $segment));
                                            @endphp

                                            <li class="flex items-center">
                                                <span class="mx-2 text-gray-400">&gt;</span>
                                                <span class="text-gray-700 font-medium">{{ $name }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Right Side Controls -->
                        <div class="flex items-center space-x-4">
                            <!-- User Dropdown -->
                            <div class="relative">
                                <button id="userDropdownButton"
                                        class="flex items-center space-x-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200"
                                        aria-haspopup="true" aria-expanded="false">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-md">
                                        <span class="text-white text-sm font-semibold">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <div class="truncate max-w-32">
                                            {{ Str::title(Auth::user()->name . ' ' . Auth::user()->middle_name . ' ' . Auth::user()->last_name . ' ' . Auth::user()->suffix) }}
                                        </div>


                                        <div class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', Auth::user()->role) }}</div>
                                    </div>
                                    <svg id="dropdownArrow"
                                         class="ml-2 h-4 w-4 transition-transform duration-200 text-gray-400"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                              clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <div id="userDropdownMenu"
                                     class="glass-effect absolute right-0 top-full mt-2 w-56 rounded-xl shadow-xl py-2 z-50 hidden"
                                     role="menu">
                                    <div class="px-4 py-3 border-b border-gray-200/50">
                                        <p class="text-sm font-medium text-gray-900">{{ Str::title(Auth::user()->name . ' ' . Auth::user()->middle_name . ' ' . Auth::user()->last_name . ' ' . Auth::user()->suffix) }}</p>
                                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                                    </div>
                                    <a href="{{ route('user.profile') }}"
                                       class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" 
                                       role="menuitem">
                                        <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                                        {{ __('Profile') }}
                                    </a>
                                    <div class="border-t border-gray-200/50 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                                        @csrf
                                        <a href="#" id="logoutButton"
                                           class="flex items-center px-4 py-3 text-sm text-gary-700 hover:bg-gray-50 transition-colors duration-200" 
                                           role="menuitem">
                                            <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                                            {{ __('Log Out') }}
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Enhanced Content Area -->
            <main class="flex-1">
                <div>
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Enhanced Logout Loader -->
    <div id="logoutLoader"
         class="fixed inset-0 bg-white/95 backdrop-blur-lg flex flex-col items-center justify-center z-[9999] hidden opacity-0 transition-all duration-500">
        <div class="text-center">
            <div class="w-20 h-20 mx-auto mb-6 relative">
                <div class="w-20 h-20 border-4 border-blue-200 rounded-full animate-spin border-t-blue-600"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Signing Out</h3>
            <p class="text-gray-600">Please wait while we secure your session...</p>
            <div class="mt-6 w-48 h-2 bg-gray-200 rounded-full overflow-hidden mx-auto">
                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
            </div>
        </div>
    </div>

    <!-- Hidden Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div id="pageLoadingOverlay" class="page-loading-overlay">
        <div class="loading-container">
            <div class="loading-spinner"></div>
            <i class="fas fa-shield-alt loading-icon"></i>
        </div>
    </div>

    <!-- Quick Action Floating Button -->
    <button class="floating-button w-14 h-14 rounded-full text-white flex items-center justify-center focus-ring bg-blue-600"
        id="quickAction" title="Quick Actions">
        <i class="fas fa-shield-alt text-white text-lg"></i>
    </button>

    <!-- Enhanced JavaScript -->
    <script>
        // Enhanced Session Management
        let warningTimeout = 25 * 60 * 1000; // 25 minutes
        let logoutTimeout = 5 * 60 * 1000;   // 5 minutes
        let warningTimer, logoutTimer;

        function showWarningModal() {
            Swal.fire({
                title: "⚠️ Session Expiring Soon",
                html: `
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="fas fa-clock text-orange-500 text-4xl mb-3"></i>
                        </div>
                        <p class="text-gray-600 mb-4">Your session will expire in <strong>5 minutes</strong> due to inactivity.</p>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                            <p class="text-sm text-orange-800">Click "Stay Logged In" to continue your session.</p>
                        </div>
                    </div>
                `,
                icon: null,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-refresh mr-2"></i>Stay Logged In',
                cancelButtonText: '<i class="fas fa-sign-out-alt mr-2"></i>Logout Now',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#ef4444',
                allowOutsideClick: false,
                customClass: {
                    popup: 'rounded-xl shadow-2xl',
                    confirmButton: 'rounded-lg font-medium',
                    cancelButton: 'rounded-lg font-medium'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    resetTimers();
                    showNotification('Session extended successfully!', 'success');
                } else {
                    logoutUser();
                }
            });
            logoutTimer = setTimeout(logoutUser, logoutTimeout);
        }

        function logoutUser() {
            document.getElementById('logoutLoader').classList.remove('hidden');
            document.getElementById('logoutLoader').classList.add('opacity-100');
            setTimeout(() => {
                document.getElementById('logout-form').submit();
            }, 0);
        }

        function resetTimers() {
            clearTimeout(warningTimer);
            clearTimeout(logoutTimer);
            warningTimer = setTimeout(showWarningModal, warningTimeout);
        }

        // Enhanced Activity Detection
        const activityEvents = ['click', 'mousemove', 'keydown', 'scroll', 'touchstart', 'focus'];
        activityEvents.forEach(event => {
            document.addEventListener(event, resetTimers, { passive: true });
        });

        // Initialize session timer
        warningTimer = setTimeout(showWarningModal, warningTimeout);

        // Enhanced Notification System
        function showNotification(message, type = 'info') {
            const bgColors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-orange-500',
                info: 'bg-blue-500'
            };
            
            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-triangle',
                warning: 'fas fa-exclamation-circle',
                info: 'fas fa-info-circle'
            };

            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-6 ${bgColors[type]} text-white px-6 py-4 rounded-xl shadow-lg z-[9999] transform translate-x-full transition-all duration-300 flex items-center space-x-3 max-w-sm`;
            notification.innerHTML = `
                <i class="${icons[type]}"></i>
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 text-white/80 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        $(document).ready(function () {
            // Enhanced Sidebar State Management
            const sidebarState = localStorage.getItem('sidebarState') || 'visible';
            const $sidebar = $('#sidebar');
            const $mainContent = $('#main-content');
            const $overlay = $('#sidebar-overlay');

            // Apply saved state on desktop
            if (window.innerWidth >= 768) {
                if (sidebarState === 'hidden') {
                    $sidebar.addClass('sidebar-hidden').attr('data-state', 'hidden');
                    $mainContent.removeClass('md:ml-[280px]').addClass('md:ml-0');
                }
            }

            // Enhanced Mobile Sidebar Toggle
            $("#toggleSidebar").click(function () {
                $sidebar.removeClass("-translate-x-full");
                $overlay.removeClass("opacity-0 invisible").addClass("opacity-50 visible");
                $("body").addClass("overflow-hidden");
            });

            // Enhanced Desktop Sidebar Toggle
            $("#toggleSidebarDesktop").click(function () {
                const isHidden = $sidebar.hasClass('sidebar-hidden');
                
                if (isHidden) {
                    $sidebar.removeClass('sidebar-hidden').attr('data-state', 'visible');
                    $mainContent.removeClass('md:ml-0').addClass('md:ml-[280px]');
                    localStorage.setItem('sidebarState', 'visible');
                    
                } else {
                    $sidebar.addClass('sidebar-hidden').attr('data-state', 'hidden');
                    $mainContent.removeClass('md:ml-[280px]').addClass('md:ml-0');
                    localStorage.setItem('sidebarState', 'hidden');
                    
                }
            });

            // Enhanced Sidebar Close
            $("#closeSidebar, #sidebar-overlay").click(function () {
                $sidebar.addClass("-translate-x-full");
                $overlay.removeClass("opacity-50 visible").addClass("opacity-0 invisible");
                $("body").removeClass("overflow-hidden");
            });

            // Enhanced Window Resize Handler
            $(window).resize(function () {
                const width = $(window).width();
                if (width >= 768) {
                    // Desktop view
                    $sidebar.removeClass("-translate-x-full");
                    $overlay.removeClass("opacity-50 visible").addClass("opacity-0 invisible");
                    $("body").removeClass("overflow-hidden");
                    
                    const sidebarState = localStorage.getItem('sidebarState') || 'visible';
                    if (sidebarState === 'hidden') {
                        $sidebar.addClass('sidebar-hidden');
                        $mainContent.removeClass('md:ml-[280px]').addClass('md:ml-0');
                    } else {
                        $sidebar.removeClass('sidebar-hidden');
                        $mainContent.removeClass('md:ml-0').addClass('md:ml-[280px]');
                    }
                } else {
                    // Mobile view
                    $sidebar.removeClass('sidebar-hidden').addClass("-translate-x-full");
                    $mainContent.removeClass('md:ml-0 md:ml-[280px]');
                }
            });

            // Enhanced Reports Dropdown
            $("#toggleReports").click(function () {
                const $dropdown = $("#reportsDropdown");
                const $arrow = $("#reportsArrow");
                const isVisible = !$dropdown.hasClass("hidden");
                
                if (isVisible) {
                    $dropdown.slideUp(300, function() {
                        $(this).addClass("hidden");
                    });
                    $arrow.removeClass("rotate-180");
                    $(this).attr('aria-expanded', 'false');
                } else {
                    $dropdown.removeClass("hidden").hide().slideDown(300);
                    $arrow.addClass("rotate-180");
                    $(this).attr('aria-expanded', 'true');
                }
            });

            // Enhanced User Dropdown
            $("#userDropdownButton").click(function (event) {
                event.stopPropagation();
                const $menu = $("#userDropdownMenu");
                const $arrow = $("#dropdownArrow");
                const isVisible = !$menu.hasClass("hidden");
                
                if (isVisible) {
                    $menu.addClass("hidden");
                    $arrow.removeClass("rotate-180");
                    $(this).attr('aria-expanded', 'false');
                } else {
                    $menu.removeClass("hidden");
                    $arrow.addClass("rotate-180");
                    $(this).attr('aria-expanded', 'true');
                }
            });

            // Enhanced Click Outside Handler
            $(document).click(function (event) {
                if (!$(event.target).closest("#userDropdownButton, #userDropdownMenu").length) {
                    $("#userDropdownMenu").addClass("hidden");
                    $("#dropdownArrow").removeClass("rotate-180");
                    $("#userDropdownButton").attr('aria-expanded', 'false');
                }
            });

            $("#logoutButton").click(function (event) {
                event.preventDefault();
                $("#logoutLoader").removeClass("hidden").addClass("opacity-100");
                setTimeout(() => {
                    $("#logoutForm").submit();
                }, 0);
            });


            // Enhanced Form Submission Handler
            document.getElementById("logoutForm").addEventListener("submit", function () {
                document.getElementById("logoutLoader").classList.remove("hidden");
                document.getElementById("logoutLoader").classList.add("opacity-100");
            });

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

            // Enhanced Navigation Highlighting
            $('.nav-item').hover(
                function() {
                    $(this).addClass('transform scale-[1.02]');
                },
                function() {
                    $(this).removeClass('transform scale-[1.02]');
                }
            );

            // Keyboard Navigation Support
            $(document).keydown(function(e) {
                // Alt + S to toggle sidebar
                if (e.altKey && e.keyCode === 83) {
                    e.preventDefault();
                    if ($(window).width() >= 768) {
                        $("#toggleSidebarDesktop").click();
                    } else {
                        $("#toggleSidebar").click();
                    }
                }
                
                // Esc to close dropdowns/modals
                if (e.keyCode === 27) {
                    $("#userDropdownMenu").addClass("hidden");
                    $("#reportsDropdown").slideUp(200);
                    $("#sidebar-overlay").click();
                }
            });

            // Enhanced Loading States
            $('form').submit(function() {
                const $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true).addClass('loading-pulse');
            });

            // Smooth scroll for internal links
            $('a[href^="#"]').click(function(e) {
                e.preventDefault();
                const target = $($(this).attr('href'));
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 500);
                }
            });

            console.log('🚀 Enhanced OSAS Management System loaded successfully!');
        });

        const PageLoader = {
            overlay: null,
            isLogoutInProgress: false,
            
            init() {
                this.overlay = document.getElementById('pageLoadingOverlay');
                if (!this.overlay) {
                    console.warn('Page loading overlay not found!');
                }
            },
            
            show() {
                // Don't show page loader if logout is in progress
                if (this.isLogoutInProgress) {
                    return this;
                }
                
                if (!this.overlay) this.init();
                
                // Show overlay
                this.overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                return this;
            },
            
            hide() {
                if (!this.overlay) return this;
                
                this.overlay.classList.remove('active');
                document.body.style.overflow = '';
                
                return this;
            },
            
            // Show loader and reload page after delay
            showAndReload(delay = 1500) {
                if (this.isLogoutInProgress) return this;
                
                this.show();
                setTimeout(() => {
                    window.location.reload();
                }, delay);
                
                return this;
            },
            
            // Show loader and redirect to URL
            showAndRedirect(url, delay = 1500) {
                if (this.isLogoutInProgress) return this;
                
                this.show();
                setTimeout(() => {
                    window.location.href = url;
                }, delay);
                
                return this;
            },
            
            // Set logout status
            setLogoutInProgress(status) {
                this.isLogoutInProgress = status;
                if (status) {
                    // Hide page loader if it's showing
                    this.hide();
                }
            }
        };

        // Initialize on document ready
        $(document).ready(function() {
            PageLoader.init();
            
            // Auto-show loader on form submissions that might cause page reload
            $('form').submit(function(e) {
                const form = $(this);
                
                // Skip logout forms, AJAX forms or forms with specific classes
                if (form.hasClass('no-loader') || 
                    form.attr('data-ajax') === 'true' || 
                    form.hasClass('logout-form') ||
                    form.find('[name="logout"]').length > 0 ||
                    form.attr('action')?.includes('logout')) {
                    return;
                }
                
                // Show loader for regular form submissions
                setTimeout(() => {
                    PageLoader.show();
                }, 100);
            });
            
            // Auto-show loader on navigation links that cause page reloads
            $('a:not([target="_blank"]):not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"])').click(function(e) {
                const link = $(this);
                
                // Skip logout links, links with specific classes or attributes
                if (link.hasClass('no-loader') || 
                    link.attr('data-ajax') === 'true' ||
                    link.hasClass('logout-link') ||
                    link.attr('href')?.includes('logout') ||
                    link.text().toLowerCase().includes('logout') ||
                    link.text().toLowerCase().includes('sign out')) {
                    return;
                }
                
                // Skip external links
                const href = link.attr('href');
                if (href && (href.startsWith('http') && !href.includes(window.location.hostname))) {
                    return;
                }
                
                // Show loader for internal navigation
                PageLoader.show();
            });
        });

        // Enhanced usage functions
        function showPageLoader() {
            PageLoader.show();
        }

        function hidePageLoader() {
            PageLoader.hide();
        }

        function reloadWithLoader(delay = 1500) {
            PageLoader.showAndReload(delay);
        }

        function redirectWithLoader(url, delay = 1500) {
            PageLoader.showAndRedirect(url, delay);
        }

        // Logout-specific functions
        function startLogout() {
            // Set logout in progress to prevent page loader
            PageLoader.setLogoutInProgress(true);
            
            // Show your dedicated logout loader
            const logoutLoader = document.getElementById('logoutLoader');
            if (logoutLoader) {
                logoutLoader.classList.remove('hidden');
                setTimeout(() => {
                    logoutLoader.classList.remove('opacity-0');
                }, 10);
            }
        }

        function endLogout() {
            // Reset logout status
            PageLoader.setLogoutInProgress(false);
            
            // Hide logout loader
            const logoutLoader = document.getElementById('logoutLoader');
            if (logoutLoader) {
                logoutLoader.classList.add('opacity-0');
                setTimeout(() => {
                    logoutLoader.classList.add('hidden');
                }, 500);
            }
        }
    </script>
</body>
</html>