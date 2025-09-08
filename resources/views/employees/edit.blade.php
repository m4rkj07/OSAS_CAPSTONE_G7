<x-admin-layout>
    <div class="max-w-7xl mx-auto px-6 py-10">
        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-red-100 text-red-800 border border-red-300">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('employees.update', $employee->id) }}" id="edit-employee-form" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-lg px-8 py-5">

            @csrf
            @method('PUT')

            <!-- Header -->
            <div class="border-b border-gray-200 pb-4">
                <h2 class="text-3xl font-bold text-gray-800">Edit Employee</h2>
                <p class="text-sm text-gray-500 mt-1">Update employee details using the fields below.</p>
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
                        src="{{ $employee->profile_image ? asset('storage/' . $employee->profile_image) : '' }}"
                        class="w-32 h-32 rounded-full object-cover border-4 border-blue-100 shadow-md {{ $employee->profile_image ? '' : 'hidden' }}">
                        
                    <p id="imageLabel" class="text-sm text-gray-500 {{ $employee->profile_image ? '' : 'hidden' }}">Current Image</p>
                </div>
                <!-- Form Fields -->
                <div class="md:col-span-9 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" id="name" required value="{{ $employee->name }}"
                                   class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" required value="{{ $employee->email }}"
                                   class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                            <input type="text" name="mobile_number" id="mobile_number" required value="{{ $employee->mobile_number }}"
                                   class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                            <input type="text" name="position" id="position" required value="{{ $employee->position }}"
                                   class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="sex" class="block text-sm font-medium text-gray-700">Sex</label>
                            <select name="sex" id="sex" required
                                    class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                                <option value="Male" {{ $employee->sex === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $employee->sex === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <div>
                            <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status</label>
                            <select name="marital_status" id="marital_status" required
                                    class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                                <option value="Single" {{ $employee->marital_status === 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ $employee->marital_status === 'Married' ? 'selected' : '' }}>Married</option>
                            </select>
                        </div>

                        <div>
                            <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                            <input type="number" name="age" id="age" min="18" max="100" required value="{{ $employee->age }}"
                                   class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" id="address" rows="3" required
                                  class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">{{ $employee->address }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <a href="{{ route('employees.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 font-medium transition">
                    ← Back to Employee List
                </a>
                <button type="button" id="update-button"
                    class="px-8 py-2 bg-blue-600 text-white rounded-full text-sm font-medium hover:bg-blue-700 transition">
                    Update
                </button>
            </div>
        </form>
    </div>
<script>
    function previewImage(event) {
        const fileInput = event.target;
        const preview = document.getElementById('imagePreview');
        const label = document.getElementById('imageLabel');
        
        const file = fileInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                label.classList.remove('hidden');
                label.textContent = "Preview Image (Not Yet Updated)";
            };
            reader.readAsDataURL(file);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('edit-employee-form');
        const updateButton = document.getElementById('update-button');

        // Store initial form values
        const initialFormData = {};
        const formElements = form.querySelectorAll('select, input, textarea');
        formElements.forEach(element => {
            // Special handling for file input since its value is not a string
            if (element.type === 'file') {
                initialFormData[element.name] = null;
            } else {
                initialFormData[element.name] = element.value;
            }
        });

        function hasFormChanged() {
            // Check for a new file selection
            const fileInput = document.getElementById('profile_image');
            if (fileInput.files.length > 0) {
                return true;
            }
            
            // Check for changes in other fields
            return Array.from(formElements).some(element => {
                if (element.type !== 'file') {
                    return initialFormData[element.name] !== element.value;
                }
                return false;
            });
        }

        updateButton.addEventListener('click', function(event) {
            event.preventDefault();
            
            if (!hasFormChanged()) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Changes Detected',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
                return;
            }

            confirmSubmit(event, 'edit');
        });

        function confirmSubmit(event, action) {
            Swal.fire({
                title: 'Confirm Action',
                text: 'You are about to update this employee. Proceed?',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    
</script>
</x-admin-layout>
