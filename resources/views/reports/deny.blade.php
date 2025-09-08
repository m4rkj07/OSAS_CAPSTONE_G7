<x-admin-layout>
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
        <h3 class="text-lg font-semibold border-b pb-2">Denied Report</h3>
        <div class="relative w-full sm:w-64">
            <input type="text" id="search-input" placeholder="Search reports..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                value="{{ request('search') }}" oninput="searchReports()">
        </div>
    </div>

    <!-- Table -->
    <div class="relative border border-gray-300 shadow-xl">
        <div class="overflow-x-auto overflow-y-auto" style="max-height: calc(100vh - 200px);">
            <table class="min-w-full text-sm text-left bg-white border-separate border-spacing-0 shadow-lg">
                <thead class="text-xs font-semibold text-gray-600 bg-gray-50 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-4 py-3">Report No.</th>
                        <th class="px-4 py-3">Descriptive Title</th>
                        <th class="px-4 py-3">Reporter</th>
                        <th class="px-1 py-3 text-center">ISI Level</th>
                        <th class="px-1 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Date</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="report-list" class="divide-y divide-gray-100">
                    @foreach ($reports as $report)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-4 py-3 text-gray-800 font-medium">{{ $report->id }}</td>
                            <td class="px-4 py-3 text-blue-700 max-w-sm whitespace-normal">{{ Str::ucfirst($report->description) }}</td>
                            @php
                                $initials = collect(explode(' ', $report->reported_by))->map(fn($word) => strtoupper($word[0]))->implode('');
                            @endphp

                            <td class="px-2 py-2 text-gray-600 flex items-center space-x-2">
                                <div class="h-8 w-8 bg-blue-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                    {{ $initials }}
                                </div>
                                <span class="truncate max-w-[10rem]">{{ $report->reported_by }}</span>
                            </td>

                            <!-- ISI Level -->
                            <td class="px-2 py-3 text-center">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full shadow-sm inline-block {{
                                    [
                                        1 => 'bg-red-100 text-red-700',
                                        2 => 'bg-orange-100 text-orange-700',
                                        3 => 'bg-yellow-100 text-yellow-800',
                                        4 => 'bg-green-100 text-green-700',
                                        5 => 'bg-blue-100 text-blue-700'
                                    ][$report->esi_level] ?? 'bg-gray-200 text-gray-700'
                                }}" data-esi="{{ $report->esi_level }}">
                                    {{
                                        [
                                            1 => "Immediate",
                                            2 => "Emergency",
                                            3 => "Urgent",
                                            4 => "Semi-Urgent",
                                            5 => "Non-Urgent"
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
                                <span class="inline-flex items-center gap-1 text-xs font-medium uppercase {{ $statusLabels[$report->status] ?? 'text-gray-700' }}">
                                    <i class="fas {{ $statusIcon }}"></i> {{ $statusText }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3 text-center text-gray-500">
                                {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y - h:i A') }}
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <button
                                        class="px-3 py-1.5 text-xs font-semibold bg-green-100 text-green-700 rounded hover:bg-green-200 view-report"
                                        data-report="{{ json_encode($report) }}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


            <div id="view-modal" class="fixed inset-0 hidden flex items-center justify-center bg-black bg-opacity-70 z-50 backdrop-blur-md">
                <div id="modal-container" class="relative bg-white p-6 rounded-xl shadow-2xl w-full max-w-4xl mx-auto transform scale-95 opacity-0 transition-all duration-300 border border-gray-300">

                    <!-- Close Button -->
                    <button id="close-modal" class="absolute top-3 right-3 text-black rounded-full p-2 transition text-xl">
                        ✕
                    </button>

                    <!-- Header -->
                    <div class="text-center border-b pb-4 mb-4">
                        <h3 class="text-xl font-semibold text-gray-900">Report Details</h3>
                    </div>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Left Content (Report Info) -->
                        <div class="md:col-span-2 bg-white p-4 rounded-lg shadow border border-gray-300 space-y-3 text-[15px] w-full">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-700 w-24">Title:</span>
                                    <span id="modal-title" class="flex-1 border border-gray-300 p-1 rounded-md"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-700 w-24">Location:</span>
                                    <span id="modal-location" class="flex-1 border border-gray-300 p-1 rounded-md"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-700 w-24">Date:</span>
                                    <span id="modal-date" class="flex-1 border border-gray-300 p-1 rounded-md"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-700 w-24">Reported By:</span>
                                    <span id="modal-reported-by" class="flex-1 border border-gray-300 p-1 rounded-md"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-700 w-24">Contact:</span>
                                    <span id="modal-contact-info" class="flex-1 border border-gray-300 p-1 rounded-md"></span>
                                </div>
                            </div>

                            <!-- Scrollable Full Description -->
                            <div class="bg-white p-3 border border-gray-300 rounded-lg">
                                <span class="block font-semibold text-gray-800 mb-2">Description:</span>
                                <p id="modal-full-description" class="text-gray-900 break-words border border-gray-300 p-2 rounded-md max-h-40 overflow-y-auto"></p>
                            </div>
                        </div>

                        <!-- Right Content (Status, ISI Level & Evidence) -->
                        <div class="flex flex-col items-center w-full md:w-[250px] space-y-4">
                            <!-- Status & ISI Level -->
                            <div class="w-full space-y-2">
                                <div class="bg-white border border-gray-300 p-3 rounded-md text-center shadow-md">
                                    <span class="font-semibold text-gray-700">Status:</span>
                                    <span id="modal-status" class="px-3 py-1 rounded-full text-white text-sm"></span>
                                </div>
                                <div class="bg-white border border-gray-300 p-3 rounded-md text-center shadow-md">
                                    <span class="font-semibold text-gray-700">ISI Level:</span>
                                    <span id="modal-esi-level" class="px-3 py-1 rounded-full text-white text-sm"></span>
                                </div>
                            </div>

                            <!-- Evidence Image -->
                            <div class="bg-white p-4 rounded-lg border border-gray-300 shadow-md flex flex-col items-center w-full">
                                <span class="block font-semibold text-gray-700 text-sm mb-2">Evidence:</span>
                                <img id="modal-evidence" class="w-[200px] h-[200px] object-cover rounded-lg shadow-md hover:shadow-lg transition-transform transform hover:scale-105 cursor-pointer border border-gray-300">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <!-- Fullscreen Image Modal -->
            <div id="fullscreen-modal" class="fixed inset-0 hidden bg-black bg-opacity-80 flex items-center justify-center z-50">
                <img id="fullscreen-image" class="max-w-[90%] max-h-[90%] rounded-lg shadow-2xl">
                <button id="close-fullscreen" class="absolute top-5 right-5 text-white p-2 rounded-full text-xl hover:bg-gray-600">✕</button>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
            <script>
                let debounceTimeout;

                function searchReports() {
                    clearTimeout(debounceTimeout);

                    debounceTimeout = setTimeout(() => {
                        const input = document.getElementById("search-input");
                        const tbody = document.getElementById("report-list");
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
                                tr.innerHTML = `<td colspan="100%" class="text-center py-4 text-gray-500">No matching reports found.</td>`;
                                tbody.appendChild(tr);
                            }
                        } else if (existingNoResults) {
                            existingNoResults.remove();
                        }
                    }, 200);
                }

                $(document).ready(function() {
    $(".view-report").on("click", function() {
        let report = $(this).data("report");

        console.log("Report Data:", report);
        console.log("Created At:", report.created_at);

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
                            1: { label: "Immediate", color: "bg-red-500" },
                            2: { label: "Emergency", color: "bg-orange-500" },
                            3: { label: "Urgent", color: "bg-yellow-500" },
                            4: { label: "Semi-Urgent", color: "bg-green-500" },
                            5: { label: "Non-Urgent", color: "bg-blue-500" }
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
            </script>
</x-admin-layout>
