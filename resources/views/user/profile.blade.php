@extends('layouts.user')

@section('content')
<div class="p-6 py-6 mt-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-1">Profile</h2>
    <p class="text-sm text-gray-500 mb-6">Following information is publicly displayed.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- First Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600" value="{{ ucfirst($user->name ?? '') }}" disabled>
            </div>
        </div>

        <!-- Middle Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600" value="{{ ucfirst($user->middle_name ?? '') }}" disabled>
            </div>
        </div>

        <!-- Last Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600" value="{{ ucfirst($user->last_name ?? '') }}" disabled>
            </div>
        </div>

        <!-- Suffix -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600" value="{{ ucfirst($user->suffix ?? '') }}" disabled>
            </div>
        </div>

        <!-- Username -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-briefcase"></i>
                </span>
                <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600" value="{{ $user->username ?? '' }}" disabled>
            </div>
        </div>

        <!-- Role -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role/s</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-briefcase"></i>
                </span>
                <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600" value="{{ ucfirst($user->role ?? '') }}" disabled>
            </div>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-envelope"></i>
                </span>
                <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600" value="{{ $user->email ?? '' }}" disabled>
            </div>
        </div>

        <!-- Company / School -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company / School</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-building"></i>
                </span>
                <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600" value="Bestlink College of the Philippines" disabled>
            </div>
        </div>
    </div>
</div>
   
@endsection