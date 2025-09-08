<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $systems = [
            [
                'name' => 'Registrar System',
                'description' => 'Streamline student records, transcripts, and academic documentation with our comprehensive registrar management platform.',
                'url' => 'https://registrar.bestlink-sms.com/',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'color' => 'from-blue-600 to-purple-600'
            ],
            [
                'name' => 'CRAD System',
                'description' => 'Center for Research and Development portal for managing academic research, publications, and development initiatives.',
                'url' => 'https://crad.bestlink-sms.com/',
                'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547A1.07 1.07 0 014 16.75v4.5C4 22.213 4.787 23 5.75 23h12.5c.963 0 1.75-.787 1.75-1.75v-4.5c0-.525-.196-1.024-.572-1.322z',
                'color' => 'from-green-600 to-teal-600'
            ],
            [
                'name' => 'Student Portal',
                'description' => 'Your gateway to academic life - access grades, schedules, assignments, and communicate with faculty seamlessly.',
                'url' => 'https://studentportal.bestlink-sms.com/',
                'icon' => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
                'color' => 'from-purple-600 to-pink-600'
            ],
            [
                'name' => 'PMED System',
                'description' => 'Supports planning, monitoring, and evaluation processes to ensure effective project management and decision-making within the institution.',
                'url' => 'https://pmed.bestlink-sms.com/',
                'icon' => 'M3 3v18h18V3H3zm2 2h14v14H5V5zm3 2v10h2V7H8zm4 4v6h2v-6h-2zm4-2v8h2V9h-2z',
                'color' => 'from-blue-600 to-cyan-600'
            ],
            [
                'name' => 'Event Management',
                'description' => 'Organize, schedule, and manage all campus events, activities, and celebrations with our integrated event platform.',
                'url' => 'https://event.bestlink-sms.com/',
                'icon' => 'M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1m-6 0h6M5 21a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v12z',
                'color' => 'from-yellow-600 to-orange-600'
            ],
            [
                'name' => 'Enrollment System',
                'description' => 'Simplified registration and enrollment process for prospective and returning students with real-time updates.',
                'url' => 'https://enrollment.bestlink-sms.com/',
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'color' => 'from-indigo-600 to-blue-600'
            ],
            [
                'name' => 'Safety & Security',
                'description' => 'Smart reporting powered by AI and NLP, allowing users to describe incidents in plain language for automatic categorization and secure logging.',
                'url' => 'https://safetysecurity.bestlink-sms.com/',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'color' => 'from-gray-700 to-gray-900'
            ],
            [
                'name' => 'Clinic System',
                'description' => 'Digital health records, appointment scheduling, and medical services management for campus healthcare.',
                'url' => 'https://clinic.bestlink-sms.com/',
                'icon' => 'M12 2a10 10 0 100 20 10 10 0 000-20zm1 5h-2v4H7v2h4v4h2v-4h4v-2h-4V7z',
                'color' => 'from-emerald-600 to-green-600'
            ],
            [
                'name' => 'IT Support',
                'description' => 'Information Technology Services for technical support, system maintenance, and digital infrastructure management.',
                'url' => 'https://its.bestlink-sms.com/',
                'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'color' => 'from-cyan-600 to-blue-600'
            ],
            [
                'name' => 'MIS System',
                'description' => 'Management Information System for data analytics, reporting, and strategic decision-making support.',
                'url' => 'https://mis.bestlink-sms.com/',
                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'color' => 'from-violet-600 to-purple-600'
            ],
            [
                'name' => 'Alumni Network',
                'description' => 'Connect with graduates, track career progress, and maintain lifelong relationships with our alumni community.',
                'url' => 'https://alumni.bestlink-sms.com/',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m0 0a4 4 0 017 0M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'color' => 'from-rose-600 to-pink-600'
            ],
            [
                'name' => 'Faculty System',
                'description' => 'Comprehensive faculty management including scheduling, performance tracking, and academic resource coordination.',
                'url' => 'https://faculty.bestlink-sms.com/',
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'color' => 'from-orange-600 to-red-600'
            ],
            [
                'name' => 'Cashier System',
                'description' => 'Financial transaction management, fee collection, and payment processing with secure digital receipts.',
                'url' => 'https://cashier.bestlink-sms.com/',
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'from-green-600 to-emerald-600'
            ],
        ];

        return view('landing', compact('systems'));
    }
}