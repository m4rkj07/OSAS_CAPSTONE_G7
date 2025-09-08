<x-admin-layout>
<div id="clinic-container" class="{{ request()->get('moduleLocked') ? 'blur-sm pointer-events-none' : '' }}">  
    <div class="p-6">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Clinic Report Details
                </h3>
                <p class="text-sm text-gray-500">
                    View full details of incident reports submitted under the Clinic category.
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
                <div class="relative w-full sm:w-40">
                    <button onclick="toggleDropdown('esi-options')" type="button" id="esi-filter-button"
                        class="w-full flex justify-between items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-500">
                        Risk Levels
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="esi-options"
                        class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg p-2 space-y-1">
                        @for($i = 1; $i <= 4; $i++)
                            <label class="flex items-center space-x-2 text-sm text-gray-700">
                                <input type="checkbox" class="esi-checkbox" value="{{ $i }}" onchange="applyFilters()">
                                <span>
                                    {{
                                        [
                                            1 => '1 - Critical',
                                            2 => '2 - High',
                                            3 => '3 - Medium',
                                            4 => '4 - Low',
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
            </div>
        </div>
        <!-- Table -->
        <div class="relative shadow-md border border-gray-200 rounded-lg">
            <div class="overflow-x-auto overflow-y-auto">
                <table class="min-w-full text-sm text-left bg-white">
                    <thead class="text-xs font-semibold text-gray-600 bg-gray-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-1">Report No.</th>
                            <th class="px-4 py-3">Descriptive Title</th>
                            <th class="px-2 py-2">Reporter</th>
                            <th class="px-2 py-2">Assigned Officer</th>
                            @php
                                $currentSort = request('sort');
                                $currentDirection = request('direction') === 'asc' ? 'desc' : 'asc';
                            @endphp

                            <th class="px-1 py-2 text-center resizable">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'esi_level', 'direction' => $currentSort === 'esi_level' ? $currentDirection : 'asc']) }}"
                                class="flex items-center justify-center gap-1">
                                    Risk Level
                                    @if($currentSort === 'esi_level')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-blue-400"></i>
                                    @else
                                        <i class="fas fa-sort text-blue-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-1 py-2 text-center resizable">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => $currentSort === 'status' ? $currentDirection : 'asc']) }}"
                                class="flex items-center justify-center gap-1">
                                    Status
                                    @if($currentSort === 'status')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-blue-400"></i>
                                    @else
                                        <i class="fas fa-sort text-blue-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-1 py-2 text-center resizable">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => $currentSort === 'created_at' ? $currentDirection : 'asc']) }}"
                                class="flex items-center justify-center gap-1">
                                    Date
                                    @if($currentSort === 'created_at')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-blue-400"></i>
                                    @else
                                        <i class="fas fa-sort text-blue-400"></i>
                                    @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="report-list" class="divide-y divide-gray-100">
                        @foreach ($reports as $report)
                            <tr class="hover:bg-gray-50 transition duration-150"
                                data-month="{{ \Carbon\Carbon::parse($report->created_at)->format('n') }}">
                                <td class="px-1 font-medium text-gray-800">
                                    {{ $report->id }}
                                </td>
                                <td class="px-4 py-1 text-blue-700 max-w-sm whitespace-normal">
                                    <button
                                        type="button"
                                        class="flex items-center gap-2 text-blue-700 hover:text-blue-900 w-full text-left hover:underline view-report"
                                        data-report="{{ json_encode($report) }}"
                                        title="View Report">
                                        <span class="truncate">{{ Str::ucfirst($report->description) }}</span>
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


                                <!-- Assigned Officer -->
                                <td class="px-2 py-2 text-gray-700">
                                    @if ($report->assignedOfficer)
                                        {{ Str::title(trim("{$report->assignedOfficer->name} {$report->assignedOfficer->last_name}")) }}
                                    @else
                                        <span class="text-gray-400 italic">Unassigned</span>
                                    @endif
                                </td>

                                <!-- ISI Level -->
                                <td class="px-2 py-2 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold 
                                        {{
                                            [
                                                1 => 'text-red-600',
                                                2 => 'text-orange-600',
                                                3 => 'text-yellow-600',
                                                4 => 'text-green-600',
                                            ][$report->esi_level] ?? 'text-gray-600'
                                        }}"
                                    >
                                        <i class="fas {{
                                            [
                                                1 => 'fa-exclamation-circle',   // Critical
                                                2 => 'fa-exclamation-triangle', // High
                                                3 => 'fa-minus-circle',         // Medium
                                                4 => 'fa-check-circle',         // Low
                                            ][$report->esi_level] ?? 'fa-question-circle'
                                        }}"></i>
                                        {{
                                            [
                                                1 => 'Critical',
                                                2 => 'High',
                                                3 => 'Medium',
                                                4 => 'Low'
                                            ][$report->esi_level] ?? 'Unknown'
                                        }}
                                    </span>

                                </td>

                                <!-- Status -->
                                <td class="px-2 py-2 text-center">
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
                                <td class="px-1 py-2 text-center text-gray-500">
                                    {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y - h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>    
<!-- View Modal -->
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
                <form id="pdf-form" method="POST" action="{{ route('reports.view-pdf') }}" target="_blank">
                    @csrf
                    <input type="hidden" name="report" id="pdf-report-data">
                    <button type="button"
                        onclick="document.getElementById('pdf-form').submit()"
                        class="mt-3 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        PDF
                    </button>
                </form>
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
                <div id="modal-full-description" 
                    class="w-full text-gray-800 leading-relaxed p-3 border border-gray-200 rounded-md bg-gray-50 whitespace-pre-wrap break-words">
                </div>
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
        $("#modal-reported-by").text(
            report.reported_by
                .toLowerCase()                          
                .replace(/\b\w/g, char => char.toUpperCase()) 
        );

        $("#modal-contact-info").text(report.contact_info);
        $("#modal-full-description").text(report.full_description);

        let pdfReport = { ...report };
        if (pdfReport.evidence_image) {
            const parts = pdfReport.evidence_image.split('/');
            pdfReport.evidence_image = parts[parts.length - 1];
        }
        $("#pdf-report-data").val(JSON.stringify(pdfReport));


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

        let formattedStatus = report.status === "deny" ? "DENIED" : report.status.replace("_", " ").toUpperCase();
        let statusClass = statusData[report.status] || { color: "bg-white", icon: "fas fa-question-circle" };
        $("#modal-status")
            .html(`<i class="${statusClass.icon} mr-2"></i>${formattedStatus}`)
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
</script>
@if(request()->get('moduleLocked'))
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const reportContainer = document.getElementById("clinic-container");
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