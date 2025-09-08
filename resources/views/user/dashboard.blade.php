@extends('layouts.user')

@section('content')
<div class="px-6 pt-6">
    <h3 class="text-4xl font-extrabold text-black mb-3 border-b-4 border-blue-600 inline-block pb-2">
        📊 Dashboard
    </h3>
    <p class="text-xl text-black mb-8">Welcome! Here's our Vision and Mission.</p>
</div>
<!-- Dashboard Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-10 px-6 pb-6">
    <!-- Vision Card -->
    <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center justify-center mb-6">
            <x-application-logo class="h-12 w-auto fill-current text-blue-600 mr-4" />
            <h2 class="text-3xl font-bold text-gray-800">Vision</h2>
        </div>
        <p class="text-lg text-gray-700 leading-relaxed text-center">
            Bestlink College of the Philippines is committed to provide and promote quality education with a unique, modern and research-based curriculum with delivery systems geared towards excellence.
        </p>
    </div>

    <!-- Mission Card -->
    <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center justify-center mb-6">
            <x-application-logo class="h-12 w-auto fill-current text-blue-600 mr-4" />
            <h2 class="text-3xl font-bold text-gray-800">Mission</h2>
        </div>
        <p class="text-lg text-gray-700 leading-relaxed text-center">
            To produce self-motivated and self-directed individuals who aim for academic excellence, are God-fearing, peaceful, healthy, and productive successful citizens.
        </p>
    </div>
</div>
@endsection