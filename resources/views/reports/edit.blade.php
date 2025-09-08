<x-admin-layout>
    <div class="bg-gray-100 antialiased">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 hover:text-blue-600 transition">
                            ← Back to Reports
                        </a>
                        <h1 class="text-3xl font-bold text-gray-800 mt-3">{{ $report->description }}</h1>
                        <p class="mt-1 text-gray-500">Modify the details and status of this incident.</p>
                    </div>
                </div>
                <hr>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 lg:gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <form id="pdf-form" method="POST" action="{{ route('reports.view-pdf') }}" target="_blank">
                        @csrf
                        <input type="hidden" name="report" id="pdf-report-data" value="{{ json_encode($report) }}">

                        <button type="button"
                            onclick="document.getElementById('pdf-form').submit()"
                            class="inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-file-pdf mr-2"></i> Generate PDF
                        </button>
                    </form>

                    <form id="edit-report-form" method="POST" action="{{ route('reports.update', $report->id) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between mb-4 border-b pb-3">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-edit text-gray-400 mr-3"></i>
                                    Report Information
                                </h3>

                                <div class="flex items-center gap-4">
                                    <!-- Assign Officer -->
                                    <div class="flex items-center gap-2">
                                        <label for="assigned_officer_id" class="text-sm font-medium text-gray-700 whitespace-nowrap">
                                            Assign Officer:
                                        </label>
                                        <select id="assigned_officer_id" name="assigned_officer_id"
                                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <option value="">Assign Officer</option>
                                            @foreach($officers as $officer)
                                                <option value="{{ $officer->id }}" 
                                                    {{ $report->assigned_officer_id == $officer->id ? 'selected' : '' }}>
                                                    {{ Str::title(trim("{$officer->name} {$officer->middle_name} {$officer->last_name} {$officer->suffix}")) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Update Button -->
                                    <button type="button" id="update-button"
                                        class="inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        Update
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                                <div>
                                    <label for="transfer_report" class="block text-sm font-medium text-gray-700">Transfer Report</label>
                                    <select id="transfer_report" name="transfer_report"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value=""{{ empty($report->transfer_report) ? 'selected' : '' }}>Select Transfer</option>
                                        <option value="Prefect" {{ $report->transfer_report === 'Prefect' ? 'selected' : '' }}>Prefect</option>
                                        <option value="Clinic" {{ $report->transfer_report === 'Clinic' ? 'selected' : '' }}>Clinic</option>
                                    </select>
                                    @error('transfer_report')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Incident Type</label>
                                    <input type="text" name="incident_type" value="{{ ucfirst($report->incident_type) }}" 
                                           class="mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700 cursor-not-allowed text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Descriptive Title</label>
                                    <input type="text" name="description" value="{{ ucfirst($report->description) }}" 
                                           class="mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700 cursor-not-allowed text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Location</label>
                                    <input type="text" name="location" value="{{ $report->location }}" 
                                           class="mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700 cursor-not-allowed text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reporter</label>
                                    <input type="text" name="reported_by" value="{{ $report->reported_by }}" 
                                           class="mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700 cursor-not-allowed text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Contact</label>
                                    <input type="text" name="contact_info" value="{{ $report->contact_info }}" 
                                           class="mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700 cursor-not-allowed text-sm">
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select id="status" name="status"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in progress" {{ $report->status == 'in progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $report->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="deny" {{ $report->status == 'deny' ? 'selected' : '' }}>Denied</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="esi_level" class="block text-sm font-medium text-gray-700">ISI Level</label>
                                    <select id="esi_level" name="esi_level"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        @for ($i = 1; $i <= 4; $i++)
                                            <option value="{{ $i }}" {{ $report->esi_level == $i ? 'selected' : '' }}>
                                                {{ $i }} - {{ ['Critical', 'High', 'Medium', 'Low'][$i - 1] }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('esi_level')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-4 flex items-center">
                                <i class="fas fa-align-left text-gray-400 mr-3"></i>
                                Full Description
                            </h3>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{ $report->full_description }}</p>
                        </div>
                        
                        @if ($report->evidence_image)
                        <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-4 flex items-center">
                                <i class="fas fa-paperclip text-gray-400 mr-3"></i>
                                Attached Evidence
                            </h3>
                            <div>
                                <img src="{{ asset('storage/' . $report->evidence_image) }}"
                                     alt="Evidence Documentation"
                                     class="rounded-md border border-gray-200 max-w-lg w-full cursor-pointer transition-transform duration-200 hover:scale-105"
                                     onclick="openFullscreen('{{ asset('storage/' . $report->evidence_image) }}')">
                                <p class="text-xs text-gray-500 mt-2">Click image to enlarge.</p>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
                
                <div class="lg:col-span-1 space-y-6 mt-8 lg:mt-0">
                    <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Post a Comment</h3>
                        <form method="POST" action="{{ route('comments.store', $report->id) }}">
                            @csrf
                            <textarea name="message" required
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                      rows="4"
                                      placeholder="Add your thoughts or updates..."></textarea>
                            <button type="submit"
                                    class="mt-3 inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Submit
                            </button>
                        </form>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 p-6 border-b border-gray-200">
                            Discussion ({{ $report->comments->count() }})
                        </h3>
                        <div class="divide-y divide-gray-200">
                            @forelse ($report->comments->sortByDesc('created_at') as $comment)
                                <div class="p-6">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold">
                                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ Str::title(trim(implode(' ', [
                                                    $comment->user->name ?? '',
                                                    $comment->user->middle_name ?? '',
                                                    $comment->user->last_name ?? '',
                                                    $comment->user->suffix ?? '',
                                                ]))) }}
                                                
                                                @php
                                                    $role = $comment->user->role;
                                                    $badgeClass = '';
                                                    $displayText = '';
                                                    
                                                    // Check if the comment author is the original reporter
                                                    if ($comment->user->id === $report->user_id) {
                                                        $badgeClass = 'bg-green-100 text-green-800';
                                                        $displayText = 'Reporter';
                                                    } 
                                                    // Check for specific staff roles
                                                    elseif (in_array($role, ['officer', 'admin', 'super_admin'])) {
                                                        $roleClasses = [
                                                            'officer' => 'bg-blue-100 text-blue-800',
                                                            'admin' => 'bg-purple-100 text-purple-800',
                                                            'super_admin' => 'bg-yellow-100 text-yellow-800',
                                                        ];
                                                        $badgeClass = $roleClasses[$role];
                                                        $displayText = ucwords(str_replace('_', ' ', $role));
                                                    }
                                                @endphp

                                                @if($badgeClass)
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                        {{ $displayText }}
                                                    </span>
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-500 mt-0.5">
                                                <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
                                            </p>
                                            <div class="mt-3 text-sm text-gray-700 break-words">
                                                <p class="whitespace-pre-wrap break-words">{{ $comment->message }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <i class="fas fa-comments text-gray-300 text-4xl mx-auto"></i>
                                    <p class="mt-4 text-sm text-gray-600">No comments yet.</p>
                                    <p class="text-xs text-gray-400">Be the first to contribute to the discussion.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="fullscreen-modal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4 hidden" onclick="closeFullscreen()">
        <div class="relative max-w-4xl max-h-full" onclick="event.stopPropagation()">
            <img id="fullscreen-image" class="block max-w-full max-h-[90vh] rounded-lg shadow-xl">
            <button onclick="closeFullscreen()"
                    class="absolute -top-3 -right-3 h-8 w-8 rounded-full bg-white text-gray-700 hover:bg-gray-200 flex items-center justify-center text-2xl font-light leading-none">
                &times;
            </button>
        </div>
    </div>

    <script>

        
    function openFullscreen(src) {
        document.getElementById('fullscreen-image').src = src;
        document.getElementById('fullscreen-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeFullscreen() {
        document.getElementById('fullscreen-modal').classList.add('hidden');
        document.getElementById('fullscreen-image').src = '';
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => (e.key === "Escape") && closeFullscreen());

    document.addEventListener('DOMContentLoaded', () => {
    const editForm = document.getElementById('edit-report-form');
    const updateButton = editForm.querySelector('button[type="button"]'); // Updated selector
    
    // Get initial form values
    const initialFormData = {};
    const formElements = editForm.querySelectorAll('select, input, textarea');
    formElements.forEach(element => {
        initialFormData[element.name] = element.value;
    });

    function hasFormChanged() {
        return Array.from(formElements).some(element => {
            return initialFormData[element.name] !== element.value;
        });
    }

    // Now listen for a click event on the button, not a submit on the form
    updateButton.addEventListener('click', async (e) => {
        e.preventDefault();
        
        // Disable submit button to prevent double submission
        updateButton.disabled = true; // Use the correct variable name
        
        try {
            // Check for changes
            if (!hasFormChanged()) {
                await Swal.fire({
                    icon: 'info',
                    title: 'No Changes Detected',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
                updateButton.disabled = false; // Use the correct variable name
                return;
            }

            // Confirm update
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to update this report.',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Submit',
                reverseButtons: true
            });

            if (!result.isConfirmed) {
                updateButton.disabled = false; // Use the correct variable name
                return;
            }

            // Get form data
            const formData = new FormData(editForm);
            
            // Submit form
            const response = await fetch(editForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Report updated successfully!'
                });
                window.location.reload();
            } else {
                throw new Error(data.message || 'Failed to update report');
            }
            
        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to update the report.'
            });
        } finally {
            updateButton.disabled = false; // Use the correct variable name
        }
    });
});
    </script>
</x-admin-layout>