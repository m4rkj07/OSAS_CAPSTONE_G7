<x-admin-layout>
    <div class="p-6">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    All Users
                </h3>
                <p class="text-sm text-gray-500">
                    View and manage users.
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <input type="text" id="search-input" placeholder="Search..."
                    class="w-full sm:w-56 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                    oninput="searchUsers()">

                <select id="role-filter" onchange="searchUsers()"
                    class="w-full sm:w-48 px-3 py-1.5 text-sm text-gray-500 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                    <option value="">All Roles</option>
                    @if (auth()->user()->role === 'super_admin')
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                        <option value="prefect">Prefect</option>
                        <option value="super_admin">Super Admin</option>
                    @elseif (auth()->user()->role === 'admin')
                        <option value="officer">Officer</option>
                        <option value="staff">Staff</option>
                        <option value="prefect">Prefect</option>
                        <option value="teacher">Teacher</option>
                    @elseif (auth()->user()->role === 'officer')
                        <option value="staff">Staff</option>
                        <option value="teacher">Teacher</option>
                    @elseif (auth()->user()->role === 'prefect')
                        <option value="student">Student</option>
                    @endif
                </select>
            </div>
            @if (auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin']))
                <button id="createUserBtn" type="button"
                    class="flex items-center justify-center gap-2 w-full sm:w-auto px-8 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create User
                </button>
            @endif    
        </div>

        <div class="relative shadow-md border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-y-auto max-h-[65vh]">
                <table class="min-w-full text-sm text-left bg-white">
                    <thead class="text-xs font-semibold text-gray-600 bg-gray-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-4">Username</th>
                            <th class="px-4 py-4">Name</th>
                            <th class="px-4 py-4 text-center">Role</th>
                            <th class="px-4 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body" class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            @php
                                $fullName = ucwords(implode(' ', array_filter([$user->name, $user->middle_name, $user->last_name, $user->suffix])));
                                $role = strtolower($user->role);

                                $roleLabel = match($role) {
                                    'teacher' => 'Teacher',
                                    'student' => 'Student',
                                    'admin' => 'Admin',
                                    'super_admin' => 'Super Admin',
                                    'prefect' => 'Prefect',
                                    'staff' => 'Staff',
                                    'officer' => 'Officer',
                                    default => ucfirst($role),
                                };

                                $roleClasses = match($role) {
                                    'teacher' => 'bg-yellow-100 text-yellow-700',
                                    'student' => 'bg-blue-100 text-blue-700',
                                    'admin' => 'bg-red-100 text-red-700',
                                    'super_admin' => 'bg-purple-100 text-purple-700',
                                    'prefect' => 'bg-green-100 text-green-700',
                                    'officer' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <tr class="user-row hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-4 text-gray-600" data-id="{{ $user->id }}">{{ $user->username }}</td>
                                <td class="px-4 py-4 font-medium text-gray-800 whitespace-nowrap">{{ $fullName }}</td>
                                <td class="px-4 py-4 text-center" data-role="{{ $role }}">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full shadow-sm inline-block {{ $roleClasses }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2">
                                        {{-- View button - Available for all roles --}}
                                        <button type="button" data-id="{{ $user->id }}"
                                            data-username="{{ $user->username }}"
                                            data-name="{{ $user->name }}"
                                            data-middle_name="{{ $user->middle_name }}"
                                            data-last_name="{{ $user->last_name }}"
                                            data-suffix="{{ $user->suffix }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ $user->role }}"
                                            data-employee_id="{{ $user->employee_id }}"
                                            class="view-user-btn text-gray-600 hover:text-gray-800 transition duration-150"
                                            title="View User">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        
                                        {{-- Edit button - Only for super_admin and admin --}}
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                            <button type="button" data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-middle_name="{{ $user->middle_name }}"
                                                data-last_name="{{ $user->last_name }}"
                                                data-suffix="{{ $user->suffix }}"
                                                data-username="{{ $user->username }}"
                                                data-email="{{ $user->email }}"
                                                data-role="{{ $user->role }}"
                                                data-employee_id="{{ $user->employee_id }}"
                                                class="edit-user-btn text-gray-600 hover:text-gray-800 transition duration-150"
                                                title="Edit User">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        @endif
                                        
                                        <!-- {{-- Delete button - Only for super_admin --}}
                                        @if(Auth::user()->role === 'super_admin')
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="delete-btn text-gray-600 hover:text-gray-800 transition duration-150" title="Delete User">
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
                            <tr id="no-users-initial">
                                <td colspan="4" class="text-center px-4 py-4 text-sm text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="no-results" class="hidden">
                           <td colspan="4" class="text-center px-4 py-4 text-sm text-gray-500">
                                No matching users found.
                           </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create User Modal - Only for super_admin and admin --}}
    @if (in_array(auth()->user()->role, ['super_admin', 'admin']))
    <div id="createUserModal"
        class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-3 sm:p-5 overflow-y-auto">

        <div
            class="bg-white shadow-lg border border-gray-200 w-full max-w-lg sm:max-w-xl md:max-w-2xl lg:max-w-3xl transition-all duration-300 scale-95 max-h-[90vh] overflow-y-auto">

            <div
                class="flex justify-between items-center border-b border-gray-200 p-5 sticky top-0 bg-white rounded-t-xl">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                        Create New User
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Please fill in the details for the new user account.</p>
                </div>
            </div>

            <form id="createUserForm" action="{{ route('users.store') }}" method="POST"
                class="p-5 space-y-2" novalidate>
                @csrf

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" placeholder="Enter username" required
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition {{ $errors->has('username') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}" value="{{ old('username') }}">
                        @error('username')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                        <input type="text" name="employee_id" placeholder="Enter employee ID"
                            class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" value="{{ old('employee_id') }}">
                        @error('employee_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="Enter first name" required
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}" style="text-transform: capitalize;" value="{{ old('name') }}">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Enter middle name"
                            class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" style="text-transform: capitalize;" value="{{ old('middle_name') }}">
                        @error('middle_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" placeholder="Enter last name" required
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition {{ $errors->has('last_name') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}" style="text-transform: capitalize;" value="{{ old('last_name') }}">
                        @error('last_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Suffix</label>
                        <input type="text" name="suffix" placeholder="e.g., Jr., III"
                            class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" value="{{ old('suffix') }}">
                        @error('suffix')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" placeholder="Enter email address" required
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition {{ $errors->has('email') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}" value="{{ old('email') }}">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                        <select name="role" required
                            class="w-full border px-3 py-2.5 rounded-md text-sm focus:ring-2 focus:outline-none focus:border-transparent transition {{ $errors->has('role') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}">
                            <option value="" disabled {{ old('role') == '' ? 'selected' : '' }}>Select Role</option>
                            @if (auth()->user()->role === 'super_admin')
                                <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            @endif
                            @if (auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                                <option value="officer" {{ old('role') == 'officer' ? 'selected' : '' }}>Officer</option>
                                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                                <option value="prefect" {{ old('role') == 'prefect' ? 'selected' : '' }}>Prefect</option>
                            @endif
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                        </select>
                        @error('role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" placeholder="Enter password" required minlength="8"
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" placeholder="Confirm password" required
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition {{ $errors->has('password_confirmation') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}">
                        @error('password_confirmation')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button"
                        class="w-full sm:w-auto px-8 py-2.5 text-sm text-gray rounded-full font-medium hover:bg-gray-300 transition close-modal">
                        Cancel
                    </button>
                    <button type="button" id="submitCreateUser"
                        class="w-full sm:w-auto px-8 py-2.5 bg-blue-600 text-white text-sm rounded-full font-medium hover:bg-blue-700 transition">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit User Modal - Only for super_admin and admin --}}
    @if (in_array(auth()->user()->role, ['super_admin', 'admin']))
    <div id="editUserModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-3 sm:p-5 overflow-y-auto">
        <div class="bg-white shadow-lg border border-gray-200 w-full max-w-lg sm:max-w-xl md:max-w-2xl lg:max-w-3xl transition-all duration-300 scale-95 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-gray-200 p-5 sticky top-0 bg-white rounded-t-xl">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                        Edit User
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Update the user's information.</p>
                </div>
            </div>

            <form id="editUserForm" method="POST" class="p-5 space-y-2" novalidate>
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="edit-username" placeholder="Enter username"
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition border-gray-300 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                        <input type="text" name="employee_id" id="edit-employee_id" placeholder="Enter employee ID"
                            class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit-name" placeholder="Enter first name"
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition border-gray-300 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                        <input type="text" name="middle_name" id="edit-middle_name" placeholder="Enter middle name"
                            class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" id="edit-last_name" placeholder="Enter last name"
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition border-gray-300 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Suffix</label>
                        <input type="text" name="suffix" id="edit-suffix" placeholder="e.g., Jr., III"
                            class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="edit-email" placeholder="Enter email address"
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition border-gray-300 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                        <select name="role" id="edit-role"
                            class="w-full border px-3 py-2.5 rounded-md text-sm focus:ring-2 focus:outline-none focus:border-transparent transition border-gray-300 focus:ring-blue-500">
                            <option value="" disabled>Select Role</option>
                            @if (auth()->user()->role === 'super_admin')
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                            @endif
                            @if (auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                                <option value="officer">Officer</option>
                                <option value="teacher">Teacher</option>
                                <option value="prefect">Prefect</option>
                                <option value="staff">Staff</option>
                            @endif
                            <option value="student">Student</option>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" id="edit-password" placeholder="Leave blank to keep current password" minlength="8"
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition border-gray-300 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="edit-password_confirmation" placeholder="Confirm new password"
                            class="w-full border px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:outline-none transition border-gray-300 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" class="w-full sm:w-auto px-8 py-2.5 text-sm text-gray rounded-full font-medium hover:bg-gray-300 transition close-edit-modal">
                        Cancel
                    </button>
                    <button type="button" id="submitEditUser" class="w-full sm:w-auto px-8 py-2.5 bg-blue-600 text-white text-sm rounded-full font-medium hover:bg-blue-700 transition">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- View User Modal - Available for all roles --}}
    <div id="viewUserModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-3 sm:p-5 overflow-y-auto">
        <div class="bg-white shadow-lg border border-gray-200 w-full max-w-lg sm:max-w-xl md:max-w-2xl lg:max-w-3xl transition-all duration-300 scale-95 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-gray-200 p-5 sticky top-0 bg-white rounded-t-xl">
                <div>
                    <h3 id="view-user-name-title" class="text-lg font-medium text-gray-900 flex items-center gap-2"></h3>
                    <p class="text-sm text-gray-500 mt-1">Detailed information about the user.</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition duration-150 close-view-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <p id="view-username" class="w-full bg-gray-100 px-3 py-2.5 rounded-md text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                        <p id="view-employee_id" class="w-full bg-gray-100 px-3 py-2.5 rounded-md text-sm text-gray-800"></p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                        <p id="view-name" class="w-full bg-gray-100 px-3 py-2.5 rounded-md text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                        <p id="view-middle_name" class="w-full bg-gray-100 px-3 py-2.5 rounded-md text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                        <p id="view-last_name" class="w-full bg-gray-100 px-3 py-2.5 rounded-md text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Suffix</label>
                        <p id="view-suffix" class="w-full bg-gray-100 px-3 py-2.5 rounded-md text-sm text-gray-800"></p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <p id="view-email" class="w-full bg-gray-100 px-3 py-2.5 rounded-md text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <p id="view-role" class="w-full bg-gray-100 px-3 py-2.5 rounded-md text-sm text-gray-800"></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100 p-5">
                <button type="button" class="w-full sm:w-auto px-8 py-2.5 text-sm text-gray rounded-full font-medium hover:bg-gray-300 transition close-view-modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</x-admin-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delete button functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            const form = event.target.closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Submit',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Show create modal if there are validation errors
    @if($errors->any())
        const createUserModal = document.getElementById('createUserModal');
        if (createUserModal) {
            createUserModal.classList.remove('hidden');
            createUserModal.classList.add('flex');
        }
    @endif

    // Modal elements
    const createUserModal = document.getElementById('createUserModal');
    const editUserModal = document.getElementById('editUserModal');
    const viewUserModal = document.getElementById('viewUserModal');
    const createUserBtn = document.getElementById('createUserBtn');

    // 🔹 Password verification function
    function verifyPassword() {
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
                .then(res => res.json())
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
            return result.isConfirmed && result.value === true;
        });
    }

    // 🔹 View Modal Functions
    function openViewUserModal(dataset) {
        // Build full name
        let fullName = dataset.name || '';
        if (dataset.middle_name) fullName += ' ' + dataset.middle_name;
        if (dataset.last_name) fullName += ' ' + dataset.last_name;
        if (dataset.suffix) fullName += ' ' + dataset.suffix;
        
        // Function to capitalize properly
        function toTitleCase(str) {
            return str.replace(/\w\S*/g, function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
        }
        
        function capitalize(str) {
            if (!str) return 'N/A';
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        }
        
        // Format role for display
        let roleDisplay = dataset.role;
        switch(dataset.role) {
            case 'super_admin':
                roleDisplay = 'Super Admin';
                break;
            case 'admin':
                roleDisplay = 'Admin';
                break;
            case 'teacher':
                roleDisplay = 'Teacher';
                break;
            case 'student':
                roleDisplay = 'Student';
                break;
            case 'prefect':
                roleDisplay = 'Prefect';
                break;
            case 'staff':
                roleDisplay = 'Staff';
                break;
            case 'officer':
                roleDisplay = 'Officer';
                break;
            default:
                roleDisplay = dataset.role ? dataset.role.charAt(0).toUpperCase() + dataset.role.slice(1) : 'N/A';
        }
        
        // Populate modal fields
        document.getElementById('view-user-name-title').textContent = toTitleCase(fullName) || 'N/A';
        document.getElementById('view-username').textContent = dataset.username || 'N/A';
        document.getElementById('view-employee_id').textContent = dataset.employee_id || 'N/A';
        document.getElementById('view-name').textContent = capitalize(dataset.name);
        document.getElementById('view-middle_name').textContent = capitalize(dataset.middle_name);
        document.getElementById('view-last_name').textContent = capitalize(dataset.last_name);
        document.getElementById('view-suffix').textContent = dataset.suffix || 'N/A';
        document.getElementById('view-email').textContent = dataset.email || 'N/A';
        document.getElementById('view-role').textContent = roleDisplay;
        
        // Show modal
        viewUserModal.classList.remove("hidden");
        viewUserModal.classList.add("flex");
    }

    // 🔹 Edit Modal Functions
    function openEditUserModal(dataset) {
        // Store original values for change detection
        window.originalUserValues = {
            username: dataset.username || '',
            employee_id: dataset.employee_id || '',
            name: dataset.name || '',
            middle_name: dataset.middle_name || '',
            last_name: dataset.last_name || '',
            suffix: dataset.suffix || '',
            email: dataset.email || '',
            role: dataset.role || ''
        };
        
        // Populate form fields
        document.getElementById('edit-username').value = dataset.username || '';
        document.getElementById('edit-employee_id').value = dataset.employee_id || '';
        function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

document.getElementById('edit-name').value = ucfirst(dataset.name) || '';
document.getElementById('edit-middle_name').value = ucfirst(dataset.middle_name) || '';
document.getElementById('edit-last_name').value = ucfirst(dataset.last_name) || '';
document.getElementById('edit-suffix').value = ucfirst(dataset.suffix) || '';

        document.getElementById('edit-email').value = dataset.email || '';
        document.getElementById('edit-role').value = dataset.role || '';
        
        // Clear password fields
        document.getElementById('edit-password').value = '';
        document.getElementById('edit-password_confirmation').value = '';
        
        // Set form action
        const editForm = document.getElementById('editUserForm');
        const editFormAction = "{{ route('users.update', ':id') }}";
        editForm.action = editFormAction.replace(':id', dataset.id);
        
        // Show modal
        editUserModal.classList.remove("hidden");
        editUserModal.classList.add("flex");
    }

    // Only initialize create and edit modals if they exist (for super_admin and admin)
    if (createUserModal && editUserModal && createUserBtn) {
        const closeModalBtns = createUserModal.querySelectorAll('.close-modal');
        const closeEditModalBtns = editUserModal.querySelectorAll('.close-edit-modal');
        const submitCreateBtn = document.getElementById('submitCreateUser');
        const submitEditBtn = document.getElementById('submitEditUser');
        const createForm = document.getElementById('createUserForm');
        const editForm = document.getElementById('editUserForm');
        const requiredCreateFields = createForm.querySelectorAll('[required]');
        const requiredEditFields = editForm.querySelectorAll('[required]');

        // --- CREATE MODAL LOGIC ---
        createUserBtn.addEventListener('click', () => {
            createUserModal.classList.remove('hidden');
            createUserModal.classList.add('flex');
        });

        function resetCreateForm() {
            createForm.reset();
            requiredCreateFields.forEach(field => {
                field.classList.remove('border-red-500', 'focus:ring-red-500');
                field.classList.add('border-gray-300', 'focus:ring-blue-500');
                const errorEl = field.parentElement.querySelector('.error-message');
                if (errorEl) errorEl.remove();
            });
        }

        function validateCreateFields() {
            let isValid = true;
            let firstInvalid = null;
            requiredCreateFields.forEach(field => {
                let errorEl = field.parentElement.querySelector('.error-message');
                if (!errorEl) {
                    errorEl = document.createElement('span');
                    errorEl.className = 'error-message text-red-500 text-sm mt-1 block';
                    field.parentElement.appendChild(errorEl);
                }
                if (!field.value.trim()) {
                    field.classList.add('border-red-500', 'focus:ring-red-500');
                    field.classList.remove('border-gray-300', 'focus:ring-blue-500');
                    errorEl.textContent = 'This field is required.';
                    if (!firstInvalid) firstInvalid = field;
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500', 'focus:ring-red-500');
                    field.classList.add('border-gray-300', 'focus:ring-blue-500');
                    errorEl.textContent = '';
                }
            });

            const password = createForm.querySelector('[name="password"]');
            const passwordConfirmation = createForm.querySelector('[name="password_confirmation"]');
            if (password.value !== passwordConfirmation.value) {
                isValid = false;
                passwordConfirmation.classList.add('border-red-500', 'focus:ring-red-500');
                passwordConfirmation.classList.remove('border-gray-300', 'focus:ring-blue-500');
                let errorEl = passwordConfirmation.parentElement.querySelector('.error-message');
                if (!errorEl) {
                    errorEl = document.createElement('span');
                    errorEl.className = 'error-message text-red-500 text-sm mt-1 block';
                    passwordConfirmation.parentElement.appendChild(errorEl);
                }
                errorEl.textContent = 'Passwords do not match.';
                if (!firstInvalid) firstInvalid = passwordConfirmation;
            }

            if (firstInvalid) firstInvalid.focus();
            return isValid;
        }

        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                createUserModal.classList.add('hidden');
                createUserModal.classList.remove('flex');
                resetCreateForm();
            });
        });

        createUserModal.addEventListener('click', e => {
            if (e.target === createUserModal) {
                createUserModal.classList.add('hidden');
                createUserModal.classList.remove('flex');
                resetCreateForm();
            }
        });

        submitCreateBtn.addEventListener('click', function () {
            if (!validateCreateFields()) return;
            Swal.fire({
                title: 'Save new user?',
                text: "Please confirm you want to create this user.",
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    createForm.submit();
                }
            });
        });

        // --- EDIT BUTTON HANDLERS WITH PASSWORD VERIFICATION ---
        const editUserBtns = document.querySelectorAll('.edit-user-btn');
        editUserBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const userData = e.currentTarget.dataset;
                
                verifyPassword().then(valid => {
                    if (valid) {
                        openEditUserModal(userData);
                    }
                });
            });
        });

        function resetEditForm() {
            editForm.reset();
            requiredEditFields.forEach(field => {
                field.classList.remove('border-red-500', 'focus:ring-red-500');
                field.classList.add('border-gray-300', 'focus:ring-blue-500');
                const errorEl = field.parentElement.querySelector('.error-message');
                if (errorEl) errorEl.remove();
            });
        }

        closeEditModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                editUserModal.classList.add('hidden');
                editUserModal.classList.remove('flex');
                resetEditForm();
            });
        });

        editUserModal.addEventListener('click', e => {
            if (e.target === editUserModal) {
                editUserModal.classList.add('hidden');
                editUserModal.classList.remove('flex');
                resetEditForm();
            }
        });

        submitEditBtn.addEventListener('click', function () {
            let isValid = true;
            let firstInvalid = null;
            
            const currentValues = {
                username: document.getElementById('edit-username').value,
                employee_id: document.getElementById('edit-employee_id').value,
                name: document.getElementById('edit-name').value,
                middle_name: document.getElementById('edit-middle_name').value,
                last_name: document.getElementById('edit-last_name').value,
                suffix: document.getElementById('edit-suffix').value,
                email: document.getElementById('edit-email').value,
                role: document.getElementById('edit-role').value
            };
            const newPassword = document.getElementById('edit-password').value;

            let hasChanges = Object.keys(window.originalUserValues).some(key => {
                return window.originalUserValues[key] != currentValues[key];
            });

            if (newPassword.trim() !== '') {
                hasChanges = true;
            }

            if (!hasChanges) {
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

            requiredEditFields.forEach(field => {
                let errorEl = field.parentElement.querySelector('.error-message');
                if (!errorEl) {
                    errorEl = document.createElement('span');
                    errorEl.className = 'error-message text-red-500 text-sm mt-1 block';
                    field.parentElement.appendChild(errorEl);
                }
                if (!field.value.trim()) {
                    field.classList.add('border-red-500', 'focus:ring-red-500');
                    field.classList.remove('border-gray-300', 'focus:ring-blue-500');
                    errorEl.textContent = 'This field is required.';
                    if (!firstInvalid) firstInvalid = field;
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500', 'focus:ring-red-500');
                    field.classList.add('border-gray-300', 'focus:ring-blue-500');
                    errorEl.textContent = '';
                }
            });
            
            const newPasswordConfirmation = document.getElementById('edit-password_confirmation');
            if (newPassword && newPassword !== newPasswordConfirmation.value) {
                isValid = false;
                newPasswordConfirmation.classList.add('border-red-500', 'focus:ring-red-500');
                newPasswordConfirmation.classList.remove('border-gray-300', 'focus:ring-blue-500');
                let errorEl = newPasswordConfirmation.parentElement.querySelector('.error-message');
                if (!errorEl) {
                    errorEl = document.createElement('span');
                    errorEl.className = 'error-message text-red-500 text-sm mt-1 block';
                    newPasswordConfirmation.parentElement.appendChild(errorEl);
                }
                errorEl.textContent = 'Passwords do not match.';
                if (!firstInvalid) firstInvalid = newPasswordConfirmation;
            }
            
            if (!isValid) {
                if (firstInvalid) firstInvalid.focus();
                return;
            }
            
            Swal.fire({
                title: 'Update user?',
                text: "Are you sure you want to save these changes?",
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    editForm.submit();
                }
            });
        });
    }

    // --- VIEW MODAL LOGIC (Available for all roles) ---
    if (viewUserModal) {
        const viewUserBtns = document.querySelectorAll('.view-user-btn');
        const closeViewModalBtns = viewUserModal.querySelectorAll('.close-view-modal');

        // View button handlers with password verification
        viewUserBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const userData = e.currentTarget.dataset;
                
                verifyPassword().then(valid => {
                    if (valid) {
                        openViewUserModal(userData);
                    }
                });
            });
        });

        closeViewModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                viewUserModal.classList.add('hidden');
                viewUserModal.classList.remove('flex');
            });
        });

        viewUserModal.addEventListener('click', e => {
            if (e.target === viewUserModal) {
                viewUserModal.classList.add('hidden');
                viewUserModal.classList.remove('flex');
            }
        });
    }

    // Escape key handler for all modals
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // Close create modal
            if (createUserModal && !createUserModal.classList.contains('hidden')) {
                createUserModal.classList.add('hidden');
                createUserModal.classList.remove('flex');
                if (typeof resetCreateForm === 'function') resetCreateForm();
            }
            // Close edit modal
            if (editUserModal && !editUserModal.classList.contains('hidden')) {
                editUserModal.classList.add('hidden');
                editUserModal.classList.remove('flex');
                if (typeof resetEditForm === 'function') resetEditForm();
            }
            // Close view modal
            if (viewUserModal && !viewUserModal.classList.contains('hidden')) {
                viewUserModal.classList.add('hidden');
                viewUserModal.classList.remove('flex');
            }
        }
    });

    // Success message
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success'
        });
    @endif
});

// Search functionality
let debounceTimeout;
function searchUsers() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        const searchInput = document.getElementById("search-input").value.trim().toLowerCase();
        const selectedRole = document.getElementById("role-filter").value.toLowerCase();
        const tbody = document.getElementById("user-table-body");
        const rows = tbody.querySelectorAll("tr");
        let hasResults = false;
        
        rows.forEach(row => {
            if (row.id === "no-results" || row.id === "no-users-initial") return;
            const username = row.querySelector("td:nth-child(1)")?.textContent.toLowerCase() || "";
            const name = row.querySelector("td:nth-child(2)")?.textContent.toLowerCase() || "";
            const role = row.querySelector("td:nth-child(3)")?.getAttribute("data-role") || "";
            const matchesSearch = username.includes(searchInput) || name.includes(searchInput);
            const matchesRole = !selectedRole || role === selectedRole;
            const shouldShow = matchesSearch && matchesRole;
            row.style.display = shouldShow ? "" : "none";
            if (shouldShow) hasResults = true;
        });
        
        const noResultsRow = document.getElementById("no-results");
        if (!hasResults) {
            if (!noResultsRow) {
                const tr = document.createElement("tr");
                tr.id = "no-results";
                tr.innerHTML = `<td colspan="4" class="text-center px-4 py-4 text-sm text-gray-500">No matching users found.</td>`;
                tbody.appendChild(tr);
            } else {
                noResultsRow.style.display = "";
            }
        } else if (noResultsRow) {
            noResultsRow.style.display = "none";
        }
        
        const noUsersInitialRow = document.getElementById("no-users-initial");
        if (noUsersInitialRow) {
            noUsersInitialRow.style.display = "none";
        }
    }, 200);
}
</script>