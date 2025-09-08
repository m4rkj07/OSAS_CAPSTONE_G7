<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/logo/bcplogin.png?v=2">
    <title>School Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'montserrat': ['Montserrat', 'sans-serif'],
                        'inter': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.8s ease-out',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(30px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        },
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0px)'
                            },
                            '50%': {
                                transform: 'translateY(-20px)'
                            }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .hero-bg {
            background: #1e3a8a;
        }
        
        .system-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .system-card:hover {
            transform: translateY(-12px) scale(1.02);
        }

        .logo-text {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
    </style>
</head>
<body class="font-inter antialiased">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Professional Logo -->
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <div class="w-10 h-10 bg-blue-900 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-900 rounded-full flex items-center justify-center">
                            <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <span class="logo-text text-2xl text-white">School Management System</span>
                        <div class="text-xs text-blue-200 -mt-1"></div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-8">
                    <a href="#home" class="text-white hover:text-blue-200 transition-colors duration-300 font-medium">Home</a>
                    <a href="#systems" class="text-white hover:text-blue-200 transition-colors duration-300 font-medium">Systems</a>
                    <a href="#about" class="text-white hover:text-blue-200 transition-colors duration-300 font-medium">About</a>
                    
                </div>
                
                   <!-- Login Button -->
                    <a href="/login" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="bg-white text-blue-900 px-6 py-2 rounded-full font-semibold hover:bg-blue-50 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Login
                    </a>
                </div>


                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button class="text-white focus:outline-none" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="min-h-screen hero-bg flex items-center justify-center relative overflow-hidden">
        <div class="relative z-10 text-center px-4 max-w-6xl mx-auto">
            <div class="animate-fade-in-up">
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight font-montserrat">
                    <span class="text-white">
                       SCHOOL MANAGEMENT SYSTEM
                    </span>
                    <br>
                    <span class="text-4xl md:text-5xl text-blue-200 font-montserrat">
                    </span>
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto leading-relaxed font-inter">
                    This project aims to enhance learning by integrating digital tools with modern tech platforms.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="#systems" class="bg-blue-900 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-2xl font-inter">
                        Explore Our Systems
                    </a>
                    <a href="#about" class="glass text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white hover:bg-opacity-20 transition-all duration-300 font-inter">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Systems Section -->
    <section id="systems" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 font-montserrat">
                    Our Comprehensive System Suite
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto font-inter">
                    Discover our integrated platforms designed to streamline every aspect of educational management
                </p>
                <div class="mt-8 w-24 h-1 bg-blue-900 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Registrar System -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Registrar System
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Streamline student records, transcripts, and academic documentation with our comprehensive registrar management platform.
                    </p>
                    
                    <a href="https://registrar.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- CRAD System -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547A1.07 1.07 0 014 16.75v4.5C4 22.213 4.787 23 5.75 23h12.5c.963 0 1.75-.787 1.75-1.75v-4.5c0-.525-.196-1.024-.572-1.322z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        CRAD System
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Center for Research and Development portal for managing academic research, publications, and development initiatives.
                    </p>
                    
                    <a href="https://crad.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- Student Portal -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Student Portal
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Your gateway to academic life - access grades, schedules, assignments, and communicate with faculty seamlessly.
                    </p>
                    
                    <a href="https://studentportal.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- PMED System -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18V3H3zm2 2h14v14H5V5zm3 2v10h2V7H8zm4 4v6h2v-6h-2zm4-2v8h2V9h-2z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        PMED System
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Supports planning, monitoring, and evaluation processes to ensure effective project management and decision-making within the institution.
                    </p>
                    
                    <a href="https://pmed.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- Event Management -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1m-6 0h6M5 21a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v12z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Event Management
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Organize, schedule, and manage all campus events, activities, and celebrations with our integrated event platform.
                    </p>
                    
                    <a href="https://event.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- Enrollment System -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Enrollment System
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Simplified registration and enrollment process for prospective and returning students with real-time updates.
                    </p>
                    
                    <a href="https://enrollment.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- Safety & Security -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Safety & Security
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Smart reporting powered by AI and NLP, allowing users to describe incidents in plain language for automatic categorization and secure logging.
                    </p>
                    
                    <a href="https://safetysecurity.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- Clinic System -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5h-2v4H7v2h4v4h2v-4h4v-2h-4V7z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Clinic System
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Digital health records, appointment scheduling, and medical services management for campus healthcare.
                    </p>
                    
                    <a href="https://clinic.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- IT Support -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        IT Support
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Information Technology Services for technical support, system maintenance, and digital infrastructure management.
                    </p>
                    
                    <a href="https://its.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- MIS System -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        MIS System
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Management Information System for data analytics, reporting, and strategic decision-making support.
                    </p>
                    
                    <a href="https://mis.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- Alumni Network -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m0 0a4 4 0 017 0M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Alumni Network
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Connect with graduates, track career progress, and maintain lifelong relationships with our alumni community.
                    </p>
                    
                    <a href="https://alumni.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- Faculty System -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Faculty System
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Comprehensive faculty management including scheduling, performance tracking, and academic resource coordination.
                    </p>
                    
                    <a href="https://faculty.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>

                <!-- Cashier System -->
                <div class="system-card bg-white rounded-2xl shadow-xl hover:shadow-2xl p-8 border border-gray-100 group">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-900 rounded-xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300 font-montserrat">
                        Cashier System
                    </h3>
                    
                    <p class="text-gray-600 mb-6 leading-relaxed font-inter">
                        Financial transaction management, fee collection, and payment processing with secure digital receipts.
                    </p>
                    
                    <a href="https://cashier.bestlink-sms.com/" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center px-6 py-3 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transform hover:scale-105 transition-all duration-300 shadow-lg font-inter">
                        Access System
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-blue-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-white">
                    <h2 class="text-4xl md:text-5xl font-bold mb-6 font-montserrat">
                        Transforming Education Through Technology
                    </h2>
                    <p class="text-xl text-blue-100 mb-8 leading-relaxed font-inter">
                        Our comprehensive school management system integrates all aspects of educational administration, 
                        providing seamless connectivity between students, faculty, and administrative staff.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            <span class="text-blue-100 font-inter">Real-time Data Management</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            <span class="text-blue-100 font-inter">Secure & Reliable</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            <span class="text-blue-100 font-inter">User-Friendly Interface</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            <span class="text-blue-100 font-inter">24/7 Support</span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="glass rounded-2xl p-8 transform rotate-3 hover:rotate-0 transition-transform duration-500">
                        <div class="bg-white bg-opacity-10 rounded-xl p-6">
                            <h3 class="text-2xl font-bold text-white mb-4 font-montserrat">Key Benefits</h3>
                            <ul class="space-y-3 text-blue-100">
                                <li class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-inter">Streamlined Operations</span>
                                </li>
                               
                                <li class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-inter">Data-Driven Decisions</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-inter">Improved Efficiency</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <span class="logo-text text-xl">SMS</span>
                    </div>
                    <p class="text-gray-400 mb-4 font-inter">
                        The system is developed to support educational institutions with digital solutions for modern learning environments.
                    </p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4 font-montserrat">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#systems" class="text-gray-400 hover:text-white transition-colors duration-300 font-inter">Our Systems</a></li>
                        <li><a href="#about" class="text-gray-400 hover:text-white transition-colors duration-300 font-inter">About Us</a></li>
                        <li><a href="" class="text-gray-400 hover:text-white transition-colors duration-300 font-inter">Support</a></li>
                        <li><a href="" class="text-gray-400 hover:text-white transition-colors duration-300 font-inter">Documentation</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4 font-montserrat">Contact Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-400 font-inter">support@example-sms.com</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-gray-400 font-inter">09XX-XXX-XXXX </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-gray-400 font-inter">Quezon City, Philippines</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400 font-inter">
                    &copy; 2025 School Management System. All rights reserved.
                </p>
                <p class="text-sm text-gray-500 mt-2 font-inter">
                    
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(30, 58, 138, 0.95)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.1)';
            }
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe system cards for animation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.system-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `all 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            // Mobile menu implementation would go here
            console.log('Mobile menu toggle');
        }

        // Parallax effect for hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('#home');
            if (hero) {
                const rate = scrolled * -0.5;
                hero.style.transform = `translate3d(0, ${rate}px, 0)`;
            }
        });

        // System card hover effects
        document.querySelectorAll('.system-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02)';
                this.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.25)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
            });
        });

        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '1';
        });

        // Performance optimization for scroll events
        let ticking = false;
        
        function updateScrollEffects() {
            const scrolled = window.pageYOffset;
            
            // Update navbar
            const navbar = document.getElementById('navbar');
            if (scrolled > 50) {
                navbar.style.background = 'rgba(30, 58, 138, 0.95)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.1)';
            }
            
            // Update parallax
            const hero = document.querySelector('#home');
            if (hero) {
                const rate = scrolled * -0.3;
                hero.style.transform = `translate3d(0, ${rate}px, 0)`;
            }
            
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateScrollEffects);
                ticking = true;
            }
        });
    </script>
</body>
</html>