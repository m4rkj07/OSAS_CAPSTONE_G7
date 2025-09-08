<x-admin-layout>
<div id="student-container" class="{{ request()->get('moduleLocked') ? 'blur-sm pointer-events-none' : '' }}">    
    <div class="p-6">
        <div class="mb-4">
            <h3 class="text-xl font-semibold text-gray-800">
                Student Records
            </h3>
            <p class="text-sm text-gray-500 mt-1">
                Records from MIS Department
            </p>
        </div>

        <!-- Search + Program Filter + Clear -->
        <form method="GET" action="{{ route('student.index') }}" class="mt-3 mb-4 flex gap-2 max-w-xl">
            <!-- Search input -->
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Search students..."
                class="flex-1 border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">

            <!-- Program filter with fixed width -->
            <select name="program"
                class="w-40 border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="" disabled selected>All Programs</option>
                <option value="BSBA" {{ request('program') == 'BSBA' ? 'selected' : '' }}>BSBA</option>
                <option value="BSOA" {{ request('program') == 'BSOA' ? 'selected' : '' }}>BSOA</option>
                <option value="BSIT" {{ request('program') == 'BSIT' ? 'selected' : '' }}>BSIT</option>
                <option value="BSCpE" {{ request('program') == 'BSCpE' ? 'selected' : '' }}>BSCpE</option>
                <option value="BSTM" {{ request('program') == 'BSTM' ? 'selected' : '' }}>BSTM</option>
                <option value="BSCrim" {{ request('program') == 'BSCrim' ? 'selected' : '' }}>BSCrim</option>
                <option value="BSPsy" {{ request('program') == 'BSPsy' ? 'selected' : '' }}>BSPsy</option>
                <option value="BLIS" {{ request('program') == 'BLIS' ? 'selected' : '' }}>BLIS</option>
            </select>

            <!-- Search button -->
            <button type="submit"
                class="bg-blue-600 text-white px-3 py-1 text-sm rounded-md hover:bg-blue-700">
                Search
            </button>

            <!-- Clear button -->
            <a href="{{ route('student.index') }}"
                class="bg-gray-300 text-gray-700 px-3 py-1 text-sm rounded-md hover:bg-gray-400">
                Clear
            </a>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm text-left bg-white">
                <thead class="text-xs font-semibold text-gray-600 bg-gray-50 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-3 py-2">Student Number</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-3 py-2">Section</th>
                        <th class="px-3 py-2">Year Level</th>
                        <th class="px-3 py-2">Program</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($students as $index => $student)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-3 py-2 font-medium">{{ $student['student_number'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2 flex items-center gap-2">
                                @php
                                    $nameParts = explode(' ', trim($student['name'] ?? ''));
                                    $firstInitial = strtoupper(substr($nameParts[0] ?? '', 0, 1));
                                    $lastInitial = strtoupper(substr(end($nameParts) ?: '', 0, 1));
                                    $initials = $firstInitial . $lastInitial;
                                @endphp
                                <div class="h-8 w-8 bg-blue-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                    {{ $initials }}
                                </div>
                                <span class="truncate max-w-[12rem]">{{ $student['name'] ?? 'N/A' }}</span>
                            </td>

                            <td class="px-4 py-2 truncate max-w-[12rem]">{{ $student['email'] ?? 'N/A' }}</td>
                            <td class="px-3 py-2">{{ $student['section'] ?? 'N/A' }}</td>
                            <td class="px-3 py-2">{{ $student['year_level'] ?? 'N/A' }}</td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                    {{ $student['program'] ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">
                                No students found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if(request()->get('moduleLocked'))
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const reportContainer = document.getElementById("student-container");
        const moduleKey = 'reports'; // current module
        let inactivityTimer;

        function lockModule() {
            reportContainer.classList.add("blur-sm", "pointer-events-none");

            Swal.fire({
                title: "Enter Password to Access!",
                input: "password",
                inputPlaceholder: "Your account password",
                inputAttributes: { autocapitalize: "off", required: "true" },
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: "Unlock",
                cancelButtonText: "Cancel",
                preConfirm: (password) => {
                    return fetch("{{ route('module.unlock', 'student') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ password })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error("Wrong password");
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage("Invalid password");
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    reportContainer.classList.remove("blur-sm", "pointer-events-none");
                    sessionStorage.setItem(`unlocked_${moduleKey}`, 'true');
                    resetInactivityTimer();
                } else if (result.isDismissed) {
                    window.location.href = "{{ route('dashboard') }}";
                }
            });
        }

        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                sessionStorage.removeItem(`unlocked_${moduleKey}`);
                lockModule();
            }, 5000); // lock after 5 sec inactivity
        }

        // If module is already unlocked in this session, skip password
        if (sessionStorage.getItem(`unlocked_${moduleKey}`) === 'true') {
            reportContainer.classList.remove("blur-sm", "pointer-events-none");
            resetInactivityTimer();
        } else {
            lockModule();
        }

        // Reset inactivity timer on user activity inside the module
        reportContainer.addEventListener("mousemove", resetInactivityTimer);
        reportContainer.addEventListener("click", resetInactivityTimer);
        reportContainer.addEventListener("keypress", resetInactivityTimer);

    });
    </script>
    @endif
</x-admin-layout>
