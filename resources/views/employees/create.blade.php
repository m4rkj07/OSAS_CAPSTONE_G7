<x-admin-layout>
<div class="max-w-7xl mx-auto px-6 py-10">
    <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data"
        id="employeeForm"
        class="bg-white rounded-2xl shadow-lg px-8 py-5">

        @csrf

        <!-- Header -->
        <div class="border-b border-gray-200 pb-4">
            <h2 class="text-3xl font-bold text-gray-800">Add New Employee</h2>
            <p class="text-sm text-gray-500 mt-1">Fill in the employee details below.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pt-4">
            <!-- Profile Image -->
            <div class="md:col-span-3 flex flex-col items-center md:items-start gap-4">
                <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>

                <input type="file" name="profile_image" id="profile_image"
                    accept="image/*"
                    class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                    onchange="previewImage(event)">

                <!-- Image Preview -->
                <img id="imagePreview"
                    src=""
                    class="w-32 h-32 rounded-full object-cover border-4 border-blue-100 shadow-md hidden">

                <p id="imageLabel" class="text-sm text-gray-500 hidden">Preview Image</p>
            </div>

            <!-- Form Fields -->
            <div class="md:col-span-9 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                        <input type="text" name="mobile_number" id="mobile_number" value="{{ old('mobile_number') }}" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        @error('mobile_number')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                        <input type="text" name="position" id="position" value="{{ old('position') }}" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            @error('position')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="sex" class="block text-sm font-medium text-gray-700">Sex</label>
                        <select name="sex" id="sex" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('sex')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status</label>
                        <select name="marital_status" id="marital_status" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="" disabled selected>Select Status</option>
                            <option value="Single" {{ old('marital_status') == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                        </select>
                        @error('marital_status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                        <input type="number" name="age" id="age" min="18" max="100" value="{{ old('age') }}" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            @error('age')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3" required
                        class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
            <a href="{{ route('employees.index') }}"
                class="text-sm text-gray-600 hover:text-gray-800 font-medium transition">
                ← Back to Employee List
            </a>
            <button type="button"
                onclick="confirmSubmit(event, 'create')"
                class="px-8 py-2 bg-blue-600 text-white rounded-full text-sm font-medium hover:bg-blue-700 transition">
                Add Employee
            </button>
        </div>
    </form>
</div>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');
        const label = document.getElementById('imageLabel');

        if (file) {
            const reader = new FileReader();
            reader.onload = function () {
                preview.src = reader.result;
                preview.classList.remove('hidden');
                label.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }

        if (!file) {
            preview.classList.add('hidden');
            label.classList.add('hidden');
            return;
        }
    }

    function confirmSubmit(event, action) {
        event.preventDefault();
        const message = action === 'create'
            ? 'You are about to add a new employee. Proceed?'
            : 'You are about to update this employee. Proceed?';

        Swal.fire({
            title: 'Confirm Action',
            text: message,
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('employeeForm').submit(); 
            }
        });
    }
</script>
</x-admin-layout>
