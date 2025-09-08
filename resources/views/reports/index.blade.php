<x-admin-layout>
<div id="report-container" class="{{ $moduleLocked ? 'blur-sm pointer-events-none' : '' }}">
    <div class="p-6">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Incident Report Management
                </h3>
                <p class="text-sm text-gray-500">
                    Manage and monitor all submitted incident reports.
                </p>
            </div>
            <div class="w-full sm:w-auto">
                <button id="openCreateModal"
                    class="w-full sm:w-auto px-8 py-1.5 text-sm bg-blue-600 text-white rounded-full shadow hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 font-bold" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Report
                </button>
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
                    <button onclick="toggleDropdown('status-options')" type="button" id="status-filter-button"
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

                <!-- Incident Type Filter Dropdown -->
                <div class="relative w-full sm:w-56">
                    <button id="incidentTypeBtn" type="button"
                        class="w-full flex justify-between items-center px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-500">
                        Filter Report
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="incidentTypeDropdown"
                        class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg p-2 space-y-1 max-h-60 overflow-y-auto">

                        @foreach([
                            'Medical / Health',
                            'Behavioral / Disciplinary',
                            'Safety / Security',
                            'Environmental / Facility-Related Incident',
                            'Natural Disasters & Emergency Events',
                            'Technology / Cyber Incident',
                            'Administrative / Policy Violations',
                            'Lost & Found'                            
                        ] as $type)
                            <label class="flex items-center space-x-2 px-2 py-1 rounded hover:bg-blue-100">
                                <input type="checkbox" name="incident_type[]" value="{{ $type }}"
                                    class="incident-type-checkbox"
                                    {{ in_array($type, (array) request('incident_type')) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $type }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Month Filter Dropdown -->
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

                <!-- Bulk Action Bar -->
                <div id="bulk-action-bar"
                    class="opacity-0 pointer-events-none transition-all duration-300 ease-in-out transform -translate-y-6 fixed top-4 left-1/2 -translate-x-1/2 bg-white shadow-lg border rounded-md px-4 py-2 z-50 flex gap-3 items-center">
                    <span id="selected-count" class="text-sm text-gray-700">0 selected</span>
                    <button onclick="handleBulkArchive()" class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Archive</button>
                    @if(Auth::user()->role === 'super_admin')
                        <button onclick="handleBulkDelete()" class="text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                            Delete
                        </button>
                    @endif
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

        <!-- Table -->
        <div class="relative shadow-md border border-gray-200 rounded-lg">
            <div class="overflow-x-auto overflow-y-auto">
                <table class="min-w-full text-sm text-left bg-white">
                    <thead class="text-xs font-semibold text-gray-600 bg-gray-50 sticky top-0 z-10 shadow-sm">
                            @php
                                $currentSort = request('sort');
                                $currentDirection = request('direction') === 'asc' ? 'desc' : 'asc';
                            @endphp
                        <tr>
                            <th class="px-2"><input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"></th>
                            <th class="px-1">Report No.</th>
                            <th class="px-4 py-3">Descriptive Title</th>
                            <th class="px-2 py-2">Reporter</th>
                            <th class="px-1 py-2 text-center">
                                Report Type
                            </th>
                            <th class="px-2 py-2">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'assigned_officer', 'direction' => $currentSort === 'assigned_officer' ? $currentDirection : 'asc']) }}"
                                >
                                    Assigned Officer
                                    @if($currentSort === 'assigned_officer')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-blue-400"></i>
                                    @else
                                        <i class="fas fa-sort text-blue-400"></i>
                                    @endif
                                </a>
                            </th>

                        
                            <th class="px-1 py-2 text-center">
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
                            <th class="px-1 py-2 text-center">
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
                            <th class="px-1 py-2 text-center">
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
                            <th class="px-1 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="report-list" class="divide-y divide-gray-100">
                        @foreach ($reports as $report)
                            <tr class="hover:bg-gray-50 transition duration-150"
                                data-month="{{ \Carbon\Carbon::parse($report->created_at)->format('n') }}"
                                data-incident-type="{{ $report->incident_type }}">

                                <td class="px-2"><input type="checkbox" class="row-checkbox" value="{{ $report->id }}"></td>
                                <td class="px-1 font-medium text-gray-800">
                                    {{ $report->id }}
                                </td>
                                <td class="px-4 py-1 text-blue-700 max-w-sm whitespace-normal">
                                    <a href="{{ route('reports.edit', $report->id) }}"
                                    class="flex items-center gap-2 text-blue-700 hover:text-blue-900 w-full text-left hover:underline"
                                        data-report="{{ json_encode($report) }}"
                                    title="Edit Report">
                                        <span class="truncate">{{ Str::ucfirst($report->description) }}</span>
                                    </a>
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

                                <td class="px-2 py-2 text-center">
                                    <div class="flex justify-center items-center">
                                        <!-- <button class="p-1.5 text-gray-600 hover:text-gray-800 view-report"
                                                title="View" data-report="{{ json_encode($report) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button> -->
                                        <a href="{{ route('reports.edit', $report->id) }}"
                                            class="p-1.5 text-gray-600 hover:text-gray-800"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('reports.archive', $report->id) }}" method="POST" class="archive-form p-1.5 flex items-center">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button" title="Archive"
                                                class="text-gray-600 hover:text-gray-800 archive-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                            </button>
                                        </form>
                                        <!-- @if(Auth::user()->role === 'super_admin')
                                            <button type="button" title="Delete"
                                                class="p-1.5 text-gray-600 hover:text-gray-800 delete-report"
                                                data-id="{{ $report->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif -->
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="view-modal" class="fixed inset-0 hidden flex items-center justify-center bg-black bg-opacity-70 z-50 backdrop-blur-md">
    <div id="modal-container" class="relative bg-gray-200 p-6 rounded-xl shadow-2xl w-full max-w-4xl mx-auto transform scale-95 opacity-0 transition-all duration-300 border border-gray-300">

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
                        <span class="font-semibold text-gray-700 w-24">Incident Type:</span>
                        <span id="modal-incident-type" class="flex-1 border border-gray-300 p-1 rounded-md"></span>
                    </div>
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
                    <div 
                        id="modal-full-description"
                        class="w-full text-gray-900 break-words border border-gray-300 p-2 rounded-md max-h-60 overflow-y-auto bg-gray-50"
                    ></div>
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
<!-- Fullscreen Image Modal -->
<div id="fullscreen-modal" class="fixed inset-0 hidden bg-black bg-opacity-80 flex items-center justify-center z-50">
    <img id="fullscreen-image" class="max-w-[90%] max-h-[90%] rounded-lg shadow-2xl">
    <button id="close-fullscreen" class="absolute top-5 right-5 text-white p-2 rounded-full text-xl hover:bg-gray-600">✕</button>
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
                    <option value="Safety / Security Incidents">Safety / Security</option>
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
                    <input type="text" name="reported_by" placeholder="Full name" required
                        class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Info <span class="text-red-500">*</span></label>
                    <input type="text" name="contact_info" placeholder="09********* or Email" required
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        label: "Critical",
                        color: "bg-red-500"
                    },
                    2: {
                        label: "High",
                        color: "bg-orange-500"
                    },
                    3: {
                        label: "Medium",
                        color: "bg-yellow-500"
                    },
                    4: {
                        label: "Low",
                        color: "bg-green-500"
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

        function toggleAllCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
        }

        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.row-checkbox:checked'))
                        .map(cb => cb.value);
        }

        function handleBulkArchive() {
            const ids = getSelectedIds();
            if (ids.length === 0) {
                Swal.fire({
                    icon: "info",
                    title: "No Selection",
                    text: "Please select at least one report to archive.",
                });
                return;
            }

            Swal.fire({
                title: "Confirm Bulk Archive",
                text: `Are you sure you want to archive ${ids.length} selected report(s)?`,
                showCancelButton: true,
                confirmButtonText: "Submit",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#2563eb",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('reports.bulkArchive') }}", {
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
                            title: "Archived!",
                            text: `${ids.length} report(s) were successfully archived.`,
                        }).then(() => {
                            location.reload();
                        });
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Something went wrong while archiving the reports.",
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
            const selectAll = document.getElementById('select-all'); // if you have a master checkbox

            // Individual checkbox highlight
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    this.closest('tr').classList.toggle('bg-blue-100', this.checked);
                    updateBulkBar(); // keep your bulk bar logic intact
                });
            });

            // "Select All" highlight
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(cb => {
                        cb.checked = selectAll.checked;
                        cb.closest('tr').classList.toggle('bg-blue-100', cb.checked);
                    });
                    updateBulkBar();
                });
            }
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


        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById("incidentTypeBtn");
            const dropdown = document.getElementById("incidentTypeDropdown");

            // Toggle dropdown
            btn.addEventListener("click", function () {
                dropdown.classList.toggle("hidden");
            });

            // Close when clicking outside
            document.addEventListener("click", function (event) {
                if (!btn.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.classList.add("hidden");
                }
            });

            // Track selections
            document.querySelectorAll(".incident-type-checkbox").forEach(cb => {
                cb.addEventListener("change", function () {
                    applyFilters();
                });
            });
        });

        let debounceTimeout;

        function applyFilters() {
            clearTimeout(debounceTimeout);

            debounceTimeout = setTimeout(() => {
                const search = document.getElementById("search-input")?.value.trim().toLowerCase() || "";

                const esiSelected = Array.from(document.querySelectorAll(".esi-checkbox:checked")).map(cb => cb.value);
                const statusSelected = Array.from(document.querySelectorAll(".status-checkbox:checked")).map(cb => cb.value);
                const monthSelected = Array.from(document.querySelectorAll(".month-checkbox:checked")).map(cb => cb.value);
                const incidentTypeSelected = Array.from(document.querySelectorAll(".incident-type-checkbox:checked")).map(cb => cb.value);

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
                    const rowMonth = row.getAttribute("data-month"); 
                    const rowIncidentType = row.getAttribute("data-incident-type") || "";

                    const matchesSearch = rowText.includes(search);
                    const matchesEsi = esiSelected.length === 0 || esiSelected.includes(rowEsi);
                    const matchesStatus = statusSelected.length === 0 || statusSelected.includes(rowStatus);
                    const matchesMonth = monthSelected.length === 0 || monthSelected.includes(rowMonth);
                    const matchesIncidentType = incidentTypeSelected.length === 0 || incidentTypeSelected.includes(rowIncidentType);

                    const match = matchesSearch && matchesEsi && matchesStatus && matchesMonth && matchesIncidentType;
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

                // 🔹 Auto-submit via query string (so backend remembers after refresh)
                const params = new URLSearchParams(window.location.search);

                params.delete("incident_type[]");
                incidentTypeSelected.forEach(v => params.append("incident_type[]", v));

                window.history.replaceState({}, "", `${location.pathname}?${params.toString()}`);
            }, 200);
        }


        document.addEventListener('DOMContentLoaded', () => {
            const archiveButtons = document.querySelectorAll('.archive-btn');

            archiveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default button click behavior

                    const form = this.closest('form'); // Find the closest parent form

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to archive this report!",
                        showCancelButton: true,
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'Submit',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                body: new URLSearchParams(new FormData(form))
                            })
                            .then(response => {
                                if (!response.ok) throw new Error('Network response was not ok');
                                return response.json();
                            })
                            .then(data => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Archived!',
                                    text: data.message || 'Report archived successfully',
                                });

                                const row = form.closest('tr');
                                if (row) {
                                    row.style.transition = 'opacity 0.5s ease';
                                    row.style.opacity = '0';
                                    setTimeout(() => row.remove(), 500);
                                }
                            })
                            .catch(() => {
                                Swal.fire('Error', 'Failed to archive the report', 'error');
                            });
                        }
                    });
                });
            });
        });

        $(document).on('click', '.delete-report', function () {
            let reportId = $(this).data('id');

            Swal.fire({
                title: "Confirm Deletion",
                text: "Are you sure you want to permanently delete this report?",
                showCancelButton: true,    
                confirmButtonColor: "#2563eb",
                confirmButtonText: "Submit",
                cancelButtonText: "Cancel",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/reports/${reportId}`,
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: "The report has been successfully deleted.",
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function () {
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: "Something went wrong while deleting the report.",
                            });
                        }
                    });
                }
            });
        });

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

    @if(request()->get('moduleLocked'))
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const reportContainer = document.getElementById("report-container");
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
                    return fetch("{{ route('module.unlock', 'reports') }}", {
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