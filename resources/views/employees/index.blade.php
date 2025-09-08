<x-admin-layout>
    <div class="p-6">
        <!-- Header -->
        <div class="mb-2">
            <h3 class="text-lg font-semibold border-b pb-2">Employee List</h3>
        </div>

        <!-- Search and Add Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="w-full sm:w-56">
                <input id="search-input" oninput="searchReports()" type="text" placeholder="Search employees..."
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
            </div>
            <a href="{{ route('employees.create') }}"
            class="flex items-center gap-2 px-8 py-1.5 text-sm bg-blue-600 text-white rounded-full shadow hover:bg-blue-700 transition w-full sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Employee
            </a>
        </div>

        <!-- Table -->
        <div class="relative shadow-md border border-gray-200 rounded-lg" id="employee-list">
            <div class="overflow-x-auto overflow-y-auto">
                <table class="min-w-full text-sm text-left bg-white">
                    <thead class="text-xs font-semibold text-gray-600 bg-gray-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Profile</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Position</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($employees as $employee)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $employee->id }}</td>
                                <td class="px-4 py-3">
                                    <img src="{{ $employee->profile_image ? asset('storage/' . $employee->profile_image) : asset('images/default-profile.png') }}"
                                        alt="Profile" class="h-10 w-10 rounded-full object-cover">
                                </td>
                                <td class="px-4 py-3 text-gray-800 font-bold">{{ $employee->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full shadow-sm inline-block bg-blue-100 text-blue-700">
                                        {{ ucfirst($employee->position) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        @if(Auth::user()->role === 'super_admin')
                                            <a href="{{ route('employees.show', $employee->id) }}"
                                            class="text-gray-600 hover:text-gray-800 transition-colors"
                                            title="View">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        @else
                                            <button onclick="promptPasswordAndRedirect('{{ route('employees.show', $employee->id) }}')"
                                                class="text-gray-600 hover:text-gray-800 transition-colors"
                                                title="View">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        @endif

                                        @if(Auth::user()->role === 'super_admin')
                                            <a href="{{ route('employees.edit', $employee->id) }}"
                                            class="text-gray-600 hover:text-gray-800 transition-colors"
                                            title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @else
                                            <button onclick="promptPasswordAndRedirect('{{ route('employees.edit', $employee->id) }}')"
                                                class="text-gray-600 hover:text-gray-800 transition-colors"
                                                title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        @endif

                                        <!-- @if(auth()->user()->role === 'super_admin')
                                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                                                class="inline" onsubmit="return confirmDelete(event)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                        onclick="confirmDelete(event)"
                                                        class="text-gray-600 hover:text-gray-800 transition-colors"
                                                        title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif -->
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 py-4">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    <script>
        function confirmDelete(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Delete Confirmation',
                text: 'This action cannot be undone. Do you still want to proceed?',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                showClass: {
                    popup: 'animate__animated animate__shakeX'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.closest('form').submit();
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const successMessage = "{{ session('success') }}";

            if (successMessage) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: successMessage,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeInRight'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutRight'
                    }
                });
            }
        });

        let debounceTimeout;

        function searchReports() {
            clearTimeout(debounceTimeout);

            debounceTimeout = setTimeout(() => {
                const input = document.getElementById("search-input");
                const tbody = document.querySelector("#employee-list tbody");
                if (!input || !tbody) return;

                const search = input.value.trim().toLowerCase();
                const rows = tbody.querySelectorAll("tr");
                let hasResults = false;

                rows.forEach(row => {
                    if (row.id === "no-results") return;

                    const text = row.textContent.toLowerCase();
                    const match = text.includes(search);
                    row.style.display = match ? "" : "none";
                    if (match) hasResults = true;
                });

                const existingNoResults = document.getElementById("no-results");

                if (!hasResults) {
                    if (!existingNoResults) {
                        const tr = document.createElement("tr");
                        tr.id = "no-results";
                        tr.innerHTML = `<td colspan="100%" class="text-center py-4 text-gray-500">No matching employees found.</td>`;
                        tbody.appendChild(tr);
                    }
                } else if (existingNoResults) {
                    existingNoResults.remove();
                }
            }, 200);
        }

        function promptPasswordAndRedirect(url) {
            return Swal.fire({
                title: 'Enter your password',
                input: 'password',
                inputLabel: 'Verification required',
                inputPlaceholder: 'Enter your password',
                inputAttributes: { 
                    autocapitalize: 'off', 
                    autocorrect: 'off' 
                },
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                allowEscapeKey: true,
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    if (!password) {
                        Swal.showValidationMessage('Password is required');
                        return false;
                    }

                    return fetch("{{ route('verify.password') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ password })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Password verification failed');
                        return res.json();
                    })
                    .then(data => {
                        if (!data.valid) {
                            Swal.showValidationMessage("Incorrect password");
                            return false;
                        }
                        return true;
                    })
                    .catch(() => {
                        Swal.showValidationMessage("Server error. Please try again.");
                        return false;
                    });
                }
            }).then(result => {
                if (result.isConfirmed && result.value === true) {
                    window.location.href = url;
                }
            });
        }



    </script>
</x-admin-layout>