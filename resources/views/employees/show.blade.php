<x-admin-layout>
    <div class="max-w-7xl mx-auto px-6 py-10">
        <!-- Card Container -->
        <div class="bg-white rounded-2xl shadow-lg px-8 py-5">
            <!-- Section Title -->
            <div class="mb-10 border-b border-gray-200 pb-4">
                <h2 class="text-3xl font-bold text-gray-800">Employee Profile</h2>
                <p class="text-sm text-gray-500 mt-1">Overview of employee personal and work-related details.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 items-start">
                <!-- Profile Image Section -->
                <div class="md:col-span-3 flex justify-center md:justify-start">
                    @if ($employee->profile_image)
                        <img src="{{ asset('storage/' . $employee->profile_image) }}"
                            alt="Profile Image"
                            class="w-48 h-48 rounded-full object-cover border-4 border-blue-100 shadow-md hover:border-blue-400 transition duration-300 cursor-pointer"
                            onclick="openImageModal('{{ asset('storage/' . $employee->profile_image) }}')">
                    @else
                        <div class="w-48 h-48 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-lg font-semibold rounded-full shadow-md">
                            No Image
                        </div>
                    @endif
                </div>

                <!-- Employee Details -->
                <div class="md:col-span-9 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-detail label="Full Name" value="{{ $employee->name }}" />
                        <x-detail label="Email Address" value="{{ $employee->email }}" />
                        <x-detail label="Sex" value="{{ ucfirst($employee->sex) }}" />
                        <x-detail label="Marital Status" value="{{ $employee->marital_status }}" />
                        <x-detail label="Age" value="{{ $employee->age }}" />
                        <x-detail label="Mobile Number" value="{{ $employee->mobile_number }}" />
                        <x-detail label="Position" value="{{ $employee->position }}" />
                        <x-detail label="Address" value="{{ $employee->address }}" />
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-12">
                <a href="{{ route('employees.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 font-medium transition">
                    ← Back to Employee List
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
