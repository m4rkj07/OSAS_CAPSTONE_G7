@extends('layouts.user')

@section('content')
    <div class="p-6 space-y-6">
        <!-- Header & Create Button -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h2 class="text-xl font-semibold text-gray-800">My Reports</h2>
            <button id="openCreateModal"
                class="w-full sm:w-auto px-8 py-1.5 text-sm bg-blue-600 text-white rounded-full shadow hover:bg-blue-700 transition flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 font-bold" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Create Report
            </button>
        </div>

        <!-- Table Wrapper -->
        <div class="bg-white border border-gray-200 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-700 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Report No.</th>
                        <th class="px-6 py-3 font-semibold">Descriptive Title</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($reports as $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $report->id }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('user.reports.show', $report->id) }}" class="text-blue-600 hover:underline">
                                    {{ ucfirst($report->description) }}
                                </a>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusText = strtoupper($report->status == 'deny' ? 'DENIED' : str_replace('_', ' ', $report->status));
                                    $statusIcons = [
                                        'pending' => 'fa-clock text-yellow-500',
                                        'in progress' => 'fa-spinner text-blue-500',
                                        'completed' => 'fa-check-circle text-green-500',
                                        'deny' => 'fa-times-circle text-red-500',
                                    ];
                                    $statusIcon = $statusIcons[$report->status] ?? 'fa-question-circle text-gray-500';
                                @endphp
                                <span class="inline-flex items-center gap-1 text-xs font-semibold uppercase text-gray-700">
                                    <i class="fas {{ $statusIcon }}"></i>
                                    {{ $statusText }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <!-- <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button class="p-2 bg-green-100 hover:bg-green-200 text-green-600 rounded-md view-report"
                                        data-report='@json($report)'>
                                        <i class="fas fa-eye"></i>
                                    </button> -->

                                    <!-- Delete -->
                                    <!-- <form action="{{ route('user.reports.destroy', $report->id) }}" method="POST"
                                        class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-md">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form> -->
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center px-6 py-6 text-gray-500">No reports found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

            <div id="view-modal" class="fixed inset-0 hidden flex items-center justify-center bg-black bg-opacity-70 z-50 backdrop-blur-md p-2 sm:p-4">
                <div id="modal-container" class="relative bg-gray-200 p-4 sm:p-6 rounded-xl shadow-2xl w-full max-w-4xl mx-auto transform scale-95 opacity-0 transition-all duration-300 border border-gray-300 max-h-[95vh] overflow-y-auto">

                    <!-- Close Button -->
                    <button id="close-modal" class="absolute top-2 right-2 sm:top-3 sm:right-3 text-black rounded-full p-2 transition text-xl z-10 bg-white bg-opacity-80 hover:bg-opacity-100">
                        ✕
                    </button>

                    <!-- Header -->
                    <div class="text-center border-b pb-3 sm:pb-4 mb-3 sm:mb-4 pr-10">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Report Details</h3>
                    </div>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

                        <!-- Left Content (Report Info) -->
                        <div class="lg:col-span-2 bg-white p-3 sm:p-4 rounded-lg shadow border border-gray-300 space-y-3 text-sm sm:text-[15px] w-full">
                            <div class="space-y-2 sm:space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                    <span class="font-semibold text-gray-700 sm:w-24 text-xs sm:text-sm">Incident Type:</span>
                                    <span id="modal-incident-type" class="flex-1 border border-gray-300 p-2 rounded-md text-sm"></span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                    <span class="font-semibold text-gray-700 sm:w-24 text-xs sm:text-sm">Title:</span>
                                    <span id="modal-title" class="flex-1 border border-gray-300 p-2 rounded-md text-sm"></span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                    <span class="font-semibold text-gray-700 sm:w-24 text-xs sm:text-sm">Location:</span>
                                    <span id="modal-location" class="flex-1 border border-gray-300 p-2 rounded-md text-sm"></span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                    <span class="font-semibold text-gray-700 sm:w-24 text-xs sm:text-sm">Date:</span>
                                    <span id="modal-date" class="flex-1 border border-gray-300 p-2 rounded-md text-sm"></span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                    <span class="font-semibold text-gray-700 sm:w-24 text-xs sm:text-sm">Reported By:</span>
                                    <span id="modal-reported-by" class="flex-1 border border-gray-300 p-2 rounded-md text-sm"></span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                    <span class="font-semibold text-gray-700 sm:w-24 text-xs sm:text-sm">Contact:</span>
                                    <span id="modal-contact-info" class="flex-1 border border-gray-300 p-2 rounded-md text-sm"></span>
                                </div>
                            </div>

                            <!-- Scrollable Full Description -->
                            <div class="bg-white p-3 border border-gray-300 rounded-lg">
                                <span class="block font-semibold text-gray-800 mb-2 text-xs sm:text-sm">Description:</span>
                                <p id="modal-full-description" class="text-gray-900 break-words border border-gray-300 p-2 rounded-md max-h-32 sm:max-h-40 overflow-y-auto text-sm"></p>
                            </div>
                        </div>

                        <!-- Right Content (Status, ISI Level & Evidence) -->
                        <div class="flex flex-col items-center w-full space-y-3 sm:space-y-4">
                            <!-- Status & ISI Level -->
                            <div class="w-full space-y-2">
                                <div class="bg-white border border-gray-300 p-2 sm:p-3 rounded-md text-center shadow-md">
                                    <span class="font-semibold text-gray-700 text-xs sm:text-sm block mb-1">Status:</span>
                                    <span id="modal-status" class="px-2 sm:px-3 py-1 rounded-full text-white text-xs sm:text-sm inline-block"></span>
                                </div>
                                <div class="bg-white border border-gray-300 p-2 sm:p-3 rounded-md text-center shadow-md">
                                    <span class="font-semibold text-gray-700 text-xs sm:text-sm block mb-1">ISI Level:</span>
                                    <span id="modal-esi-level" class="px-2 sm:px-3 py-1 rounded-full text-white text-xs sm:text-sm inline-block"></span>
                                </div>
                            </div>

                            <!-- Evidence Image -->
                            <div class="bg-white p-3 sm:p-4 rounded-lg border border-gray-300 shadow-md flex flex-col items-center w-full">
                                <span class="block font-semibold text-gray-700 text-xs sm:text-sm mb-2">Evidence:</span>
                                <img id="modal-evidence" class="w-full max-w-[200px] h-[150px] sm:h-[200px] object-cover rounded-lg shadow-md hover:shadow-lg transition-transform transform hover:scale-105 cursor-pointer border border-gray-300">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fullscreen Image Modal -->
            <div id="fullscreen-modal" class="fixed inset-0 hidden bg-black bg-opacity-80 flex items-center justify-center z-50">
                <img id="fullscreen-image" class="max-w-[90%] max-h-[90%] rounded-lg shadow-2xl">
                <button id="close-fullscreen" class="absolute top-5 right-5 rounded-full text-white p-2 text-xl hover:bg-gray-600">✕</button>
            </div>

<!-- Create Modal -->
<div id="createModal"
    class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-3 sm:p-5 overflow-y-auto">

    <div
        class="bg-white shadow-lg border border-gray-200 w-full max-w-lg sm:max-w-xl md:max-w-2xl lg:max-w-3xl transition-all duration-300 scale-95 max-h-[90vh] overflow-y-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-90 translate-y-4">

        <!-- Header -->
        <div
            class="flex justify-between items-center border-b border-gray-200 p-5 sticky top-0 bg-white rounded-t-xl">
            <div>
                <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                    Create New Report
                </h3>
                <p class="text-sm text-gray-500 mt-1">Provide clear details to help us act quickly and accurately.</p>
            </div>
        </div>

        <!-- Form -->
        <form id="createReportForm" action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data"
            class="p-5 space-y-5">
            @csrf

            <!-- Incident Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Incident Type <span class="text-red-500">*</span>
                </label>
                <select name="incident_type" required
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none focus:border-transparent transition placeholder:text-gray-400">
                    <option value="" disabled selected>Select Incident Type</option>

                    <option value="Medical / Health">Medical / Health</option>
                    <option value="Behavioral / Disciplinary">Behavioral / Disciplinary</option>
                    <option value="Safety / Security Incidents">Safety / Security Incidents</option>
                    <option value="Environmental / Facility-Related Incident">Environmental / Facility-Related Incident</option>
                    <option value="Natural Disasters & Emergency Events">Natural Disasters & Emergency Events</option>
                    <option value="Technology / Cyber Incident">Technology / Cyber Incident</option>
                    <option value="Administrative / Policy Violations">Administrative / Policy Violations</option>
                    <option value="Lost & Found">Lost & Found</option>
                    <!-- <option value="Others">Others(Specify in Descriptive Title)</option> -->
                </select>
            </div>

            <!-- Report Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descriptive Title <span class="text-red-500">*</span></label>
                <input type="text" name="description" placeholder="Brief description of the incident" required
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
            </div>

            <!-- Full Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                <textarea name="full_description" rows="4"
                    placeholder="Provide detailed information about the incident"
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition resize"></textarea>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Location <span class="text-red-500">*</span></label>
                <input type="text" name="location" placeholder="Building, room, or area" required
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
            </div>

            <!-- Reporter & Contact Info -->
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reporter <span class="text-red-500">*</span></label>
                    <input type="text" name="reported_by" value="{{ auth()->user()->name }}" readonly
                        class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Info <span class="text-red-500">*</span></label>
                    <input type="text" name="contact_info" value="{{ auth()->user()->email }}" readonly
                        class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
            </div>

            <!-- ISI Level -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ISI Level <span class="text-red-500">*</span></label>
                <select name="esi_level" required
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition placeholder:text-gray-400">
                    <option value="" disabled selected>Select Severity Level</option>
                    <option value="1">Critical</option>
                    <option value="2">High</option>
                    <option value="3">Medium</option>
                    <option value="4">Low</option>
                </select>
            </div>

            <!-- Evidence Image Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Evidence Image</label>
                <input type="file" name="evidence_image" accept="image/*"
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF up to 10MB</p>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button"
                    class="w-full sm:w-auto px-8 py-2.5 text-sm text-gray rounded-full font-medium hover:bg-gray-300 transition close-modal">
                    Cancel
                </button>
                <button type="button" id="submitCreateReport"
                    class="w-full sm:w-auto px-8 py-2.5 bg-blue-600 text-white text-sm rounded-full font-medium hover:bg-blue-700 transition">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const evidenceImage = document.getElementById("modal-evidence");
        const fullscreenModal = document.getElementById("fullscreen-modal");
        const fullscreenImage = document.getElementById("fullscreen-image");
        const closeFullscreen = document.getElementById("close-fullscreen");

        evidenceImage.addEventListener("click", function () {
            if (evidenceImage.src) {
                fullscreenImage.src = evidenceImage.src;
                fullscreenModal.classList.remove("hidden");
            }
        });

        closeFullscreen.addEventListener("click", function () {
            fullscreenModal.classList.add("hidden");
        });

        fullscreenModal.addEventListener("click", function (event) {
            if (event.target === fullscreenModal) {
                fullscreenModal.classList.add("hidden");
            }
        });
    });

    $(document).ready(function() {
    $(".view-report").on("click", function() {
        let report = $(this).data("report");

        console.log("Report Data:", report);
        console.log("Created At:", report.created_at);

        $("#modal-incident-type").text(report.incident_type);
        $("#modal-title").text(report.description);
        $("#modal-location").text(report.location);
        $("#modal-date").text(moment(report.created_at).format("MMM DD, YYYY - hh:mm A"));
        $("#modal-status").text(report.status);
        $("#modal-reported-by").text(report.reported_by);
        $("#modal-contact-info").text(report.contact_info);
        $("#modal-full-description").text(report.full_description);

        // Status color and icon mapping
        let statusData = {
            "pending": {
                color: "bg-white uppercase text-black font-semibold",
                icon: "fas fa-clock text-yellow-500"
            },
            "in progress": {
                color: "bg-white uppercase text-black font-semibold",
                icon: "fas fa-spinner text-blue-400"
            },
            "completed": {
                color: "bg-white uppercase text-black font-semibold",
                icon: "fas fa-check-circle text-green-500"
            },
            "deny": {
                color: "bg-white uppercase text-black font-semibold",
                icon: "fas fa-times-circle text-red-600"
            }
        };

        // Format the status and assign the correct color and icon
        let formattedStatus = report.status === "deny" ? "DENIED" : report.status.replace("_", " ").toUpperCase();
        let statusClass = statusData[report.status] || { color: "bg-white", icon: "fas fa-question-circle" }; // Default icon if status doesn't match

        // Set the status with icon and color
        $("#modal-status")
            .html(`<i class="${statusClass.icon} mr-2"></i>${formattedStatus}`) // Add icon before text
            .attr("class", "px-3 py-1 rounded-full text-sm " + statusClass.color);


                // ISI Level color mapping
                let esiLevels = {
                    1: {
                        label: "Immediate",
                        color: "bg-red-500"
                    },
                    2: {
                        label: "Emergency",
                        color: "bg-orange-500"
                    },
                    3: {
                        label: "Urgent",
                        color: "bg-yellow-500"
                    },
                    4: {
                        label: "Semi-Urgent",
                        color: "bg-green-500"
                    },
                    5: {
                        label: "Non-Urgent",
                        color: "bg-blue-500"
                    }
                };

                if (esiLevels[report.esi_level]) {
                    $("#modal-esi-level").text(esiLevels[report.esi_level].label)
                        .attr("class", "px-3 py-1 rounded-full text-white text-sm " + esiLevels[report
                            .esi_level].color);
                } else {
                    $("#modal-esi-level").text("Unknown")
                        .attr("class", "px-3 py-1 rounded-full bg-gray-500 text-white text-sm");
                }

                // Show evidence image if available
                if (report.evidence_image) {
                    $("#modal-evidence").attr("src", "/storage/" + report.evidence_image).show();
                } else {
                    $("#modal-evidence").hide();
                }

                // Open modal with animation
                $("#view-modal").removeClass("hidden").addClass("flex");
                $("#modal-container").removeClass("scale-95 opacity-0").addClass("scale-100 opacity-100");
            });


            // Close Modal with animation
            function closeModal() {
                $("#modal-container").removeClass("scale-100 opacity-100").addClass("scale-95 opacity-0");
                setTimeout(() => {
                    $("#view-modal").addClass("hidden").removeClass("flex");
                }, 300);
            }

            $("#close-modal").on("click", closeModal);

            // Close modal when clicking outside the modal content
            $("#view-modal").on("click", function(e) {
                if ($(e.target).is("#view-modal")) {
                    closeModal();
                }
            });
        });

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                confirmDelete(form);
            });
        });

        // Show SweetAlert2 confirmation dialog
        function confirmDelete(form) {
            Swal.fire({
                title: 'Confirm Deletion',
                text: 'Are you sure you want to delete this report? This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#2563eb',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-lg shadow-lg p-6',
                    confirmButton: 'px-5 py-2 rounded-lg text-white bg-red-600 hover:bg-red-700',
                    cancelButton: 'px-5 py-2 rounded-lg bg-gray-300 text-gray-800 hover:bg-gray-400'
                }
            }).then(result => {
                if (result.isConfirmed) {
                    handleDelete(form);
                }
            });
        }

        // Perform the delete request
        function handleDelete(form) {
            fetch(form.action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete the report.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message, form);
                    } else {
                        showError(data.message);
                    }
                })
                .catch(() => showError('An error occurred while deleting the report.'));
        }

        // Show success toast and remove the row dynamically
        function showSuccess(message, form) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: message,
                showConfirmButton: false,
                timer: 1000,
                toast: true,
                position: 'top-end'
            });

            // Remove deleted report row from the table
            let reportRow = form.closest('tr'); // Finds the closest table row
            if (reportRow) {
                setTimeout(() => reportRow.remove(), 500); // Smooth removal effect
            }
        }

         document.addEventListener('DOMContentLoaded', function () {
            const openModalBtn = document.getElementById('openCreateModal');
            const modal = document.getElementById('createModal');
            const closeModalBtns = document.querySelectorAll('.close-modal');
            const submitBtn = document.getElementById('submitCreateReport');
            const form = document.getElementById('createReportForm');
            const requiredFields = form.querySelectorAll('[required]');

            // 🔹 Reset form function (clears values, removes error spans and all border classes)
            function resetForm() {
                form.reset(); // clear all input values (including file inputs)
                requiredFields.forEach(field => {
                    // remove any error/validation classes
                    field.classList.remove(
                        'border-red-500', 'focus:ring-red-500',
                        'border-blue-500', 'focus:ring-blue-500',
                        'border-gray-300'
                    );

                    // restore default border / focus ring (adjust if your base class differs)
                    field.classList.add('border-gray-300', 'focus:ring-blue-500');

                    // remove the inline error element if present
                    const errorEl = field.parentElement.querySelector('.error-message');
                    if (errorEl) errorEl.remove();
                });
            }

            function validateFields() {
                let isValid = true;
                let firstInvalid = null;

                requiredFields.forEach(field => {
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

                if (firstInvalid) firstInvalid.focus();
                return isValid;
            }

            // 🔹 Real-time removal of error when user types/selects
            requiredFields.forEach(field => {
                field.addEventListener('input', () => {
                    if (field.value.trim()) {
                        field.classList.remove('border-red-500', 'focus:ring-red-500');
                        field.classList.add('border-gray-300', 'focus:ring-blue-500');
                        const errorEl = field.parentElement.querySelector('.error-message');
                        if (errorEl) errorEl.textContent = '';
                    }
                });
                field.addEventListener('change', () => {
                    if (field.value.trim()) {
                        field.classList.remove('border-red-500', 'focus:ring-red-500');
                        field.classList.add('border-gray-300', 'focus:ring-blue-500');
                        const errorEl = field.parentElement.querySelector('.error-message');
                        if (errorEl) errorEl.textContent = '';
                    }
                });
            });

            // Open modal (optionally reset on open so it's always clean)
            openModalBtn.addEventListener('click', () => {
                resetForm();
                modal.classList.remove('hidden');
            });

            // Close modal → reset form
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    resetForm();
                });
            });

            // Click outside to close → reset
            modal.addEventListener('click', e => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    resetForm();
                }
            });

            // Escape key to close → reset
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    resetForm();
                }
            });

            // SweetAlert confirm before submit
            submitBtn.addEventListener('click', function () {
                if (!validateFields()) return;

                Swal.fire({
                    title: 'Submit Report?',
                    text: "Please make sure all details are correct before submitting.",
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });

            @if(session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    icon: 'success'
                });
            @endif
        });
</script>
@endsection