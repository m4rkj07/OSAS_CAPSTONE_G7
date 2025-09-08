<x-admin-layout>
<div class="p-6">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">
                Archived Logs
            </h3>
            <p class="text-sm text-gray-500">
                View and manage reports that have been archived from the system.
            </p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 flex-wrap">
        <!-- Left Column: Search, Filters, Downloads -->
        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 w-full lg:w-auto">

            <!-- Search -->
            <div class="w-full sm:w-56">
                <input type="text" id="search-input" placeholder="Search reports..."
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    value="{{ request('search') }}" oninput="applyFilters()">
            </div>

            <!-- ESI Filter Dropdown -->
            <div class="relative w-full sm:w-32">
                <button id="esi-filter-button" onclick="toggleDropdown('esi-options')" type="button"
                    class="w-full flex justify-between items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-500">
                    ISI Levels
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="esi-options"
                    class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg p-2 space-y-1">
                    @for($i = 1; $i <= 3; $i++)
                        <label class="flex items-center space-x-2 text-sm text-gray-700">
                            <input type="checkbox" class="esi-checkbox" value="{{ $i }}" onchange="applyFilters()">
                            <span>
                                {{
                                    [
                                        1 => 'Emergency',
                                        2 => 'Urgent',
                                        3 => 'Non-Urgent'
                                    ][$i]
                                }}
                            </span>
                        </label>
                    @endfor
                </div>
            </div>

            <!-- Status Filter Dropdown -->
            <div class="relative w-full sm:w-28">
               <button id="status-filter-button" onclick="toggleDropdown('status-options')" type="button"
                    class="w-full flex justify-between items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-500">
                    Status
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="status-options"
                    class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg p-2 space-y-1">
                    @foreach(['pending', 'in progress', 'completed', 'deny'] as $status)
                        <label class="flex items-center space-x-2 text-sm text-gray-700 capitalize">
                            <input type="checkbox" class="status-checkbox" value="{{ $status }}" onchange="applyFilters()">
                            <span>{{ $status }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Month Filter -->
            <div class="relative w-full sm:w-36">
                <button id="monthFilterBtn" type="button"
                    class="w-full flex justify-between items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-500">
                    Month
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="monthDropdown"
                    class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg p-2 space-y-1 max-h-60 overflow-y-auto">
                    @for($m = 1; $m <= 12; $m++)
                        <label class="flex items-center space-x-2 text-sm text-gray-700 capitalize">
                            <input type="checkbox" class="month-checkbox" value="{{ $m }}" onchange="applyFilters()">
                            <span>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</span>
                        </label>
                    @endfor
                </div>
            </div>

            <!-- Download Buttons -->
            <div class="flex items-center w-full sm:w-auto gap-1">
                <a href="#" onclick="downloadFilteredPDF()"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-md hover:bg-gray-100 transition">
                    <i class="fas fa-file-pdf text-red-600"></i>
                    <span class="text-sm font-medium">PDF</span>
                </a>
                <a href="javascript:void(0)" onclick="downloadFilteredFile('excel')"
                    class="flex items-center gap-2 px-3 py-1 rounded-md hover:bg-gray-100 transition">
                    <i class="fas fa-file-csv text-green-600"></i>
                    <span class="text-sm font-medium">CSV</span>
                </a>
                <a href="javascript:void(0)" onclick="downloadFilteredFile('xls')"
                    class="flex items-center gap-2 px-3 py-1 rounded-md hover:bg-gray-100 transition">
                    <i class="fas fa-file-excel text-green-600"></i>
                    <span class="text-sm font-medium">XLS</span>
                </a>
            </div>
        </div>
    </div>
    <!-- Bulk Action Bar -->
    <div id="bulk-action-bar"
        class="opacity-0 pointer-events-none transition-all duration-300 ease-in-out transform -translate-y-6 fixed top-4 left-1/2 -translate-x-1/2 bg-white shadow-lg border rounded-md px-4 py-2 z-50 flex gap-3 items-center">
        <span id="selected-count" class="text-sm text-gray-700">0 selected</span>
        <button onclick="bulkUnarchive()" class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Unarchive</button>
        @if(Auth::user()->role === 'super_admin')
            <button onclick="handleBulkDelete()" class="text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                Delete
            </button>
        @endif
    </div>
    <!-- Table -->
    <div class="relative shadow-md border border-gray-200 rounded-lg">
        <div class="overflow-x-auto overflow-y-auto">
            <table class="min-w-full text-sm text-left bg-white">
                <thead class="text-xs font-semibold text-gray-600 bg-gray-50 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-2 resizable"><input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"></th>
                        <th class="px-4 py-3">Report No.</th>
                        <th class="px-4 py-3">Descriptive Title</th>
                        <th class="px-4 py-3">Reporter</th>
                        <th class="px-1 py-2 text-center">
                            Report Type
                        </th>
                        <th class="px-4 py-3">Assigned Officer</th>
                        <th class="px-1 py-2 text-center cursor-pointer" data-sort="esi">
                            ISI Level <i class="fas fa-sort text-blue-400"></i>
                        </th>
                        <th class="px-1 py-2 text-center cursor-pointer" data-sort="status">
                            Status <i class="fas fa-sort text-blue-400"></i>
                        </th>
                        <th class="px-1 py-2 text-center cursor-pointer" data-sort="date">
                            Date <i class="fas fa-sort text-blue-400"></i>
                        </th>

                        <!-- <th class="px-4 py-3 text-center">Action</th> -->
                    </tr>
                </thead>
                <tbody id="report-list" class="divide-y divide-gray-100">
                    @foreach ($reports as $report)
                        <tr class="hover:bg-gray-50 transition duration-150"
                            data-month="{{ \Carbon\Carbon::parse($report->created_at)->format('n') }}">
                            <td class="px-2"><input type="checkbox" class="row-checkbox" value="{{ $report->id }}"></td>
                            <td class="px-4 py-3 text-gray-800 font-medium">{{ $report->id }}</td>
                            <td class="px-4 py-3 text-blue-700 max-w-sm whitespace-normal">
                                <button
                                    class="text-left text-blue-700 hover:underline view-report"
                                    data-report="{{ json_encode($report) }}"
                                    title="View Report">
                                    {{ Str::ucfirst($report->description) }}
                                </button>
                            </td>

                            @php
                                $nameParts = explode(' ', $report->reported_by);
                                $initials = collect($nameParts)->map(fn($word) => strtoupper(substr($word, 0, 1)))->implode('');
                                $displayName = Str::title($report->reported_by);
                                $role = optional($report->user)->role ? Str::title(optional($report->user)->role) : 'N/A';
                            @endphp


                            <td class="px-2 py-2 text-gray-600 flex items-center space-x-2">
                                <div class="h-8 w-8 bg-blue-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                    {{ $initials }}
                                </div>
                                <span class="truncate max-w-[10rem]">
                                    {{ $displayName }} - {{ $role }}
                                </span>
                            </td>

                            <!-- Incident Type -->
                            <td class="px-2 py-2 text-center text-gray-700" data-incident-type="{{ $report->incident_type }}">
                                {{ $report->incident_type ?? 'N/A' }}
                            </td>

                            <!-- Assigned Officer -->
                            <td class="px-2 py-2 text-gray-700">
                                @if ($report->assignedOfficer)
                                    {{ Str::title(trim("{$report->assignedOfficer->name} {$report->assignedOfficer->last_name}")) }}
                                @else
                                    <span class="text-gray-400 italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- ISI Level -->
                            <td class="px-2 py-3 text-center">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full shadow-sm inline-block {{
                                    [
                                        1 => 'bg-red-100 text-red-700',
                                        2 => 'bg-yellow-100 text-yellow-800',
                                        3 => 'bg-blue-100 text-blue-700'
                                    ][$report->esi_level] ?? 'bg-gray-200 text-gray-700'
                                }}" data-esi="{{ $report->esi_level }}">
                                    {{
                                        [
                                            1 => "Emergency",
                                            2 => "Urgent",
                                            3 => "Non-Urgent",
                                        ][$report->esi_level] ?? "Unknown"
                                    }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-2 py-3 text-center">
                                @php
                                    $statusLabels = [
                                        'pending' => 'text-gray-800',
                                        'in progress' => 'text-blue-700',
                                        'completed' => 'text-green-700',
                                        'deny' => 'text-red-600',
                                    ];
                                    $statusIcons = [
                                        'pending' => 'fa-clock text-yellow-500',
                                        'in progress' => 'fa-spinner text-blue-400',
                                        'completed' => 'fa-check-circle text-green-500',
                                        'deny' => 'fa-times-circle text-red-600',
                                    ];
                                    $statusText = strtoupper($report->status == 'deny' ? 'DENIED' : str_replace('_', ' ', $report->status));
                                    $statusIcon = $statusIcons[$report->status] ?? 'fa-question-circle';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-medium uppercase {{ $statusLabels[$report->status] ?? 'text-gray-700' }}"
                                    data-status="{{ strtolower($report->status) }}">
                                    <i class="fas {{ $statusIcon }}"></i> {{ $statusText }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3 text-center text-gray-500">
                                {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y - h:i A') }}
                            </td>

                            <!-- Actions -->
                            <!-- <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <button
                                        class="px-3 py-1.5 text-xs font-semibold bg-green-100 text-green-700 rounded hover:bg-green-200 view-report"
                                        data-report="{{ json_encode($report) }}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td> -->
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>

            <div id="view-modal" class="fixed inset-0 hidden flex items-center justify-center bg-black bg-opacity-60 z-50 p-4 backdrop-blur-sm">
    <div id="modal-container" class="relative bg-gray-50 p-6 shadow-2xl w-full max-w-5xl mx-auto transform scale-95 opacity-0 transition-all duration-300 max-h-[90vh] overflow-y-auto custom-scrollbar">

        <button id="close-modal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="flex flex-col gap-6">

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-gray-200 pb-4 mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Report Details</h3>
                <div class="flex items-center gap-4 w-full sm:w-auto">
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h4 class="flex items-center gap-2 text-lg font-semibold text-gray-800 mb-6">
                    Report Information
                </h4>
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <label class="text-sm font-medium text-gray-700">Incident Type:</label>
                        <span id="modal-incident-type" class="col-span-2 text-gray-800 font-medium"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="text-sm font-medium text-gray-700">Descriptive Title:</label>
                        <span id="modal-title" class="col-span-2 text-gray-800"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="text-sm font-medium text-gray-700">Location:</label>
                        <span id="modal-location" class="col-span-2 text-gray-800"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="text-sm font-medium text-gray-700">Reported by:</label>
                        <span id="modal-reported-by" class="col-span-2 text-gray-800"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="text-sm font-medium text-gray-700">Contact:</label>
                        <span id="modal-contact-info" class="col-span-2 text-gray-800"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="text-sm font-medium text-gray-700">Date:</label>
                        <span id="modal-date" class="col-span-2 text-gray-800"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="text-sm font-medium text-gray-700">Status:</label>
                        <span id="modal-status" class="col-span-2 inline-block px-3 py-1 rounded-full text-white text-sm bg-blue-600"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="text-sm font-medium text-gray-700">ISI Level:</label>
                        <span id="modal-esi-level" class="col-span-2 inline-block px-3 py-1 rounded-full text-white text-sm bg-red-600"></span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h4 class="flex items-center gap-2 text-lg font-semibold text-gray-800 mb-4">
                    Full Description
                </h4>
                <textarea id="modal-full-description" class="w-full text-gray-800 leading-relaxed custom-scrollbar p-3 border border-gray-200 rounded-md bg-gray-50 whitespace-pre-wrap break-words resize-y min-h-32" readonly></textarea>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h4 class="flex items-center gap-2 text-lg font-semibold text-gray-800 mb-4">
                    Attached Evidence
                </h4>
                <img id="modal-evidence" class="w-[200px] h-[200px] object-cover rounded-lg shadow-md hover:shadow-lg transition-transform transform hover:scale-105 cursor-pointer border border-gray-300" alt="Evidence">
            </div>
        </div>
    </div>
</div>
<!-- Fullscreen Image Modal -->
<div id="fullscreen-modal" class="fixed inset-0 hidden bg-black bg-opacity-80 flex items-center justify-center z-50">
    <img id="fullscreen-image" class="max-w-[90%] max-h-[90%] rounded-lg shadow-2xl" alt="Full screen evidence">
    <button id="close-fullscreen" class="absolute top-5 right-5 text-white p-2 rounded-full text-xl hover:bg-gray-600 transition-colors">✕</button>
</div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const btn = document.getElementById("monthFilterBtn");
                    const dropdown = document.getElementById("monthDropdown");

                    btn.addEventListener("click", function () {
                        dropdown.classList.toggle("hidden");
                    });

                    document.addEventListener("click", function (event) {
                        if (!btn.contains(event.target) && !dropdown.contains(event.target)) {
                            dropdown.classList.add("hidden");
                        }
                    });
                });

                $(document).ready(function() {
    $(".view-report").on("click", function() {
        let report = $(this).data("report");

        console.log("Report Data:", report);
        console.log("Created At:", report.created_at);

        $("#modal-title").text(report.description);
        $("#modal-incident-type").text(report.incident_type);
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
                            1: { label: "Emergency", color: "bg-red-500" },
                            2: { label: "Urgent", color: "bg-yellow-500" },
                            3: { label: "Non-Urgent", color: "bg-blue-500" }
                        };

                        if (esiLevels[report.esi_level]) {
                            $("#modal-esi-level").text(esiLevels[report.esi_level].label)
                                .attr("class", "px-3 py-1 rounded-full text-white text-sm " + esiLevels[report.esi_level].color);
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

                function downloadFilteredPDF() {
                    const esiCheckboxes = document.querySelectorAll('.esi-checkbox:checked');
                    const statusCheckboxes = document.querySelectorAll('.status-checkbox:checked');
                    const reportCheckboxes = document.querySelectorAll('.row-checkbox:checked');
                    const monthCheckboxes = document.querySelectorAll('.month-checkbox:checked');

                    const esiValues = Array.from(esiCheckboxes).map(cb => cb.value);
                    const statusValues = Array.from(statusCheckboxes).map(cb => cb.value);
                    const selectedIds = Array.from(reportCheckboxes).map(cb => cb.value);
                    const monthValues = Array.from(monthCheckboxes).map(cb => cb.value);

                    const params = new URLSearchParams();
                    params.append('archived', 'true');

                    if (esiValues.length) params.append('esi', esiValues.join(','));
                    if (statusValues.length) params.append('status', statusValues.join(','));
                    if (selectedIds.length) params.append('ids', selectedIds.join(','));
                    if (monthValues.length) params.append('month', monthValues.join(','));

                    const url = `{{ route('reports.export.pdf') }}?${params.toString()}`;
                    window.open(url, '_blank');
                }

                function downloadFilteredFile(type) {
                    const esiValues = Array.from(document.querySelectorAll('.esi-checkbox:checked')).map(cb => cb.value);
                    const statusValues = Array.from(document.querySelectorAll('.status-checkbox:checked')).map(cb => cb.value);
                    const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                    const monthValues = Array.from(document.querySelectorAll('.month-checkbox:checked')).map(cb => cb.value);

                    const params = new URLSearchParams();

                    if (esiValues.length) params.append('esi', esiValues.join(','));
                    if (statusValues.length) params.append('status', statusValues.join(','));
                    if (selectedIds.length) params.append('ids', selectedIds.join(','));
                    if (monthValues.length) params.append('month', monthValues.join(','));

                    const url = `/reports/export-${type}?${params.toString()}`;

                    document.getElementById('pageLoadingOverlay').style.display = 'flex';

                    fetch(url)
                        .then(response => response.blob())
                        .then(blob => {
                            const link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = `report-summary.${type}`;
                            link.click();
                            window.URL.revokeObjectURL(link.href);
                        })
                        .catch(err => console.error('Download failed', err))
                        .finally(() => {
                            document.getElementById('pageLoadingOverlay').style.display = 'none';
                        });
                }

                document.addEventListener('click', function (event) {
                    const esiButton = document.getElementById('esi-filter-button');
                    const esiDropdown = document.getElementById('esi-options');
                    const statusButton = document.getElementById('status-filter-button');
                    const statusDropdown = document.getElementById('status-options');

                    // Check if the clicked target is inside ESI dropdown or button
                    const clickedInsideEsi = esiDropdown.contains(event.target) || esiButton.contains(event.target);
                    const clickedInsideStatus = statusDropdown.contains(event.target) || statusButton.contains(event.target);

                    // Toggle ESI dropdown
                    if (!clickedInsideEsi) {
                        esiDropdown.classList.add('hidden');
                    }

                    // Toggle Status dropdown
                    if (!clickedInsideStatus) {
                        statusDropdown.classList.add('hidden');
                    }
                });

                function toggleDropdown(id) {
                    const dropdown = document.getElementById(id);
                    dropdown.classList.toggle('hidden');
                }

                let debounceTimeout;

                function toggleDropdown(id) {
                    const el = document.getElementById(id);
                    el.classList.toggle('hidden');
                }

                function applyFilters() {
                    clearTimeout(debounceTimeout);

                    debounceTimeout = setTimeout(() => {
                        const search = document.getElementById("search-input")?.value.trim().toLowerCase() || "";

                        // Get selected filters
                        const esiSelected = Array.from(document.querySelectorAll(".esi-checkbox:checked")).map(cb => cb.value);
                        const statusSelected = Array.from(document.querySelectorAll(".status-checkbox:checked")).map(cb => cb.value);
                        const monthSelected = Array.from(document.querySelectorAll(".month-checkbox:checked")).map(cb => cb.value);

                        const tbody = document.getElementById("report-list");
                        const rows = tbody.querySelectorAll("tr");

                        let hasResults = false;

                        rows.forEach(row => {
                            if (row.id === "no-results") return;

                            const reportNo = row.cells[0]?.textContent.toLowerCase();
                            const description = row.cells[1]?.textContent.toLowerCase();
                            const reporter = row.cells[2]?.textContent.toLowerCase();

                            const rowText = [reportNo, description, reporter].join(" ");
                            const rowEsi = row.querySelector("[data-esi]")?.getAttribute("data-esi");
                            const rowStatus = row.querySelector("span[data-status]")?.getAttribute("data-status") || "";
                            const rowMonth = row.getAttribute("data-month"); // Ensure this is set in each row

                            const matchesSearch = rowText.includes(search);
                            const matchesEsi = esiSelected.length === 0 || esiSelected.includes(rowEsi);
                            const matchesStatus = statusSelected.length === 0 || statusSelected.includes(rowStatus);
                            const matchesMonth = monthSelected.length === 0 || monthSelected.includes(rowMonth);

                            const match = matchesSearch && matchesEsi && matchesStatus && matchesMonth;
                            row.style.display = match ? "" : "none";

                            if (match) hasResults = true;
                        });

                        const noResultsRow = document.getElementById("no-results");
                        if (!hasResults) {
                            if (!noResultsRow) {
                                const tr = document.createElement("tr");
                                tr.id = "no-results";
                                tr.innerHTML = `<td colspan="100%" class="text-center py-4 text-gray-500">No matching reports found.</td>`;
                                tbody.appendChild(tr);
                            }
                        } else if (noResultsRow) {
                            noResultsRow.remove();
                        }
                    }, 200);
                }

                function toggleAllCheckboxes(source) {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(cb => cb.checked = source.checked);
                }

                function getSelectedIds() {
                    return Array.from(document.querySelectorAll('.row-checkbox:checked'))
                                .map(cb => cb.value);
                }

                function updateBulkBar() {
                    const bulkBar = document.getElementById('bulk-action-bar');
                    const selectedCount = document.getElementById('selected-count');

                    // Only count checkboxes that are both checked AND visible
                    const selected = Array.from(document.querySelectorAll('.row-checkbox'))
                        .filter(cb => cb.checked && cb.closest('tr')?.offsetParent !== null);

                    const count = selected.length;

                    if (count > 0) {
                        bulkBar.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-6');
                        bulkBar.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
                        selectedCount.textContent = `${count} selected`;
                    } else {
                        bulkBar.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
                        bulkBar.classList.add('opacity-0', 'pointer-events-none', '-translate-y-6');
                        selectedCount.textContent = `0 selected`;
                    }
                }

                document.addEventListener('DOMContentLoaded', () => {
                    const checkboxes = document.querySelectorAll('.row-checkbox');

                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', updateBulkBar);
                    });
                });

                function toggleSelectAll(master) {
                    const allCheckboxes = document.querySelectorAll('.row-checkbox');
                    let visibleCheckedCount = 0;

                    allCheckboxes.forEach(cb => {
                        const row = cb.closest('tr');

                        if (row && row.offsetParent !== null) {
                            cb.checked = master.checked;

                            if (master.checked) {
                                visibleCheckedCount++;
                            }
                        }
                    });

                    updateBulkBar();
                }

                function bulkUnarchive() {
                    const ids = getSelectedIds();

                    if (ids.length === 0) {
                        Swal.fire({
                            icon: "info",
                            title: "No Selection",
                            text: "Please select at least one report to unarchive.",
                        });
                        return;
                    }

                    Swal.fire({
                        title: "Confirm Bulk Unarchive",
                        text: `Are you sure you want to unarchive ${ids.length} selected report(s)?`,
                        showCancelButton: true,
                        confirmButtonText: "Submit",
                        cancelButtonText: "Cancel",
                        confirmButtonColor: "#2563eb",
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("{{ route('reports.bulk-unarchive') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ ids })
                            })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire({
                                    icon: "success",
                                    title: "Unarchived!",
                                    text: `${ids.length} report(s) were successfully unarchived.`,
                                }).then(() => {
                                    location.reload();
                                });
                            })
                            .catch(() => {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error!",
                                    text: "Something went wrong while unarchiving the reports.",
                                });
                            });
                        }
                    });
                }

                function handleBulkDelete() {
                    const ids = getSelectedIds();
                    if (ids.length === 0) {
                        Swal.fire({
                            icon: "info",
                            title: "No Selection",
                            text: "Please select at least one report to delete.",
                        });
                        return;
                    }

                    Swal.fire({
                        title: "Confirm Bulk Deletion",
                        text: `Are you sure you want to delete ${ids.length} selected report(s)?`,
                        showCancelButton: true,
                        confirmButtonText: "Submit",
                        cancelButtonText: "Cancel",
                        confirmButtonColor: "#2563eb",
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("{{ route('reports.bulkDelete') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: JSON.stringify({ ids })
                            })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted!",
                                    text: `${ids.length} report(s) were successfully deleted.`,
                                }).then(() => {
                                    location.reload();
                                });
                            })
                            .catch(() => {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error!",
                                    text: "Something went wrong while deleting the reports.",
                                });
                            });
                        }
                    });
                }

                document.addEventListener("DOMContentLoaded", function () {
                    const tableBody = document.getElementById("report-list");
                    const headers = document.querySelectorAll("th[data-sort]");
                    let sortDirection = {};

                    headers.forEach(header => {
                        sortDirection[header.dataset.sort] = "asc"; // default ascending

                        header.addEventListener("click", function () {
                            const type = header.dataset.sort;
                            const rows = Array.from(tableBody.querySelectorAll("tr"));

                            let sortedRows = rows.sort((a, b) => {
                                let aVal, bVal;

                                if (type === "esi") {
                                    aVal = parseInt(a.querySelector("[data-esi]").dataset.esi);
                                    bVal = parseInt(b.querySelector("[data-esi]").dataset.esi);
                                } 
                                else if (type === "status") {
                                    aVal = a.querySelector("[data-status]").dataset.status;
                                    bVal = b.querySelector("[data-status]").dataset.status;
                                } 
                                else if (type === "date") {
                                    aVal = new Date(a.cells[a.cells.length - 1].innerText.trim());
                                    bVal = new Date(b.cells[b.cells.length - 1].innerText.trim());
                                }

                                if (aVal < bVal) return sortDirection[type] === "asc" ? -1 : 1;
                                if (aVal > bVal) return sortDirection[type] === "asc" ? 1 : -1;
                                return 0;
                            });

                            // Toggle sort direction
                            sortDirection[type] = sortDirection[type] === "asc" ? "desc" : "asc";

                            // Re-append sorted rows
                            tableBody.innerHTML = "";
                            sortedRows.forEach(row => tableBody.appendChild(row));
                        });
                    });
                });
            </script>
</x-admin-layout>