<x-admin-layout>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ auth()->user()->createToken('map')->plainTextToken ?? '' }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Incident Map - Multi Floor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #1e3a8a;
            --secondary-blue: #1e40af;
            --light-blue: #3b82f6;
            --bg-blue: #f0f4ff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--bg-blue) 0%, #e0e7ff 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .map-container {
            display: flex;
            height: 100vh;
            max-width: 1600px;
            margin: 0 auto;
            padding: 1rem;
            gap: 1rem;
        }

        .map-panel {
            flex: 1;
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            position: relative;
        }

        .map-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .floor-plan {
            position: relative;
            width: 1200px;
            height: 800px;
            background: url('{{ asset("storage/floorplans/ground-floor.png") }}') center/cover no-repeat;
            flex-shrink: 0;
            transform: translateZ(0);
            backface-visibility: hidden;
            transition: background-image 0.3s ease;
        }

        .sidebar {
            width: 380px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            padding: 1rem;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .status-indicator.connected {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .status-indicator.disconnected {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .sidebar-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--gray-50);
            min-height: 0;
        }

        .tab-navigation {
            display: flex;
            background: var(--white);
            border-bottom: 2px solid var(--gray-200);
            flex-shrink: 0;
        }

        #floorPlan {
            visibility: hidden;
        }
        #floorPlan.initialized {
            visibility: visible;
        }


        .tab-button {
            flex: 1;
            padding: 0.75rem 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .tab-button:hover {
            background: var(--bg-blue);
            color: var(--primary-blue);
        }

        .tab-button.active {
            background: var(--light-blue);
            color: white;
        }

        .tab-content {
            display: none;
            flex: 1;
            flex-direction: column;
            min-height: 0;
        }

        .tab-content.active {
            display: flex;
        }

        .control-section {
            padding: 1rem;
            flex-shrink: 0;
            overflow-y: auto;
            max-height: 100%;
        }

        .section-group {
            margin-bottom: 1rem;
        }

        .section-group:last-child {
            margin-bottom: 0;
        }

        /* Floor Selection Styles */
        .floor-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .floor-option {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 8px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            background: var(--white);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .floor-option:hover {
            border-color: var(--light-blue);
            background: var(--bg-blue);
        }

        .floor-option.active {
            border-color: var(--primary-blue);
            background: var(--primary-blue);
            color: white;
        }

        .floor-option input[type="radio"] {
            display: none;
        }

        .current-floor-indicator {
            background: var(--primary-blue);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
        }

        .incidents-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            padding: 0;
        }

        .incidents-header {
            color: var(--primary-blue);
            font-size: 12px;
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
            background: var(--gray-50);
            flex-shrink: 0;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .incident-count-badge {
            background: var(--primary-blue);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            min-width: 20px;
            text-align: center;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 0.75rem;
            box-shadow: var(--shadow);
        }

        .card h3, .card h4 {
            color: var(--primary-blue);
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 0.75rem;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .stat-card .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            display: block;
        }

        .stat-card .stat-label {
            font-size: 10px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .debug-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
        }

        .debug-stat {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: var(--text-secondary);
        }

        .debug-stat span:last-child {
            font-weight: 600;
            color: var(--text-primary);
        }

        .filter-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 12px;
            font-weight: 500;
        }

        .filter-checkbox:hover {
            background: var(--bg-blue);
        }

        .filter-checkbox input {
            margin-right: 10px;
            accent-color: var(--primary-blue);
            transform: scale(1.1);
            width: 16px;
            height: 16px;
        }

        .filter-checkbox .filter-icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            color: var(--primary-blue);
            flex-shrink: 0;
            font-size: 14px;
            text-align: center;
        }

        .filter-checkbox .filter-text {
            flex: 1;
            line-height: 1.3;
        }

        .incident-list-content {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem;
            min-height: 0;
            background: var(--gray-50);
        }

        .incident-item {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            will-change: transform;
        }

        .incident-item:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
            border-color: var(--light-blue);
        }

        .incident-item.selected {
            background: linear-gradient(135deg, var(--bg-blue), #e0e7ff);
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.1);
        }

        .incident-time {
            font-size: 10px;
            color: var(--text-secondary);
            margin-bottom: 4px;
            font-weight: 500;
        }

        .incident-type {
            font-size: 11px;
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 4px;
        }

        .incident-location {
            font-size: 10px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .incident-priority {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 8px;
        }

        /* Enhanced Incident Markers with Clustering */
        .incident-marker {
            position: absolute;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 3px solid white;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25), 0 0 0 0 rgba(59, 130, 246, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: 700;
            will-change: transform;
            backface-visibility: hidden;
        }
        

        .incident-marker.clustered {
            width: 40px;
            height: 40px;
            font-size: 14px;
            border-width: 4px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3), 0 0 0 0 rgba(59, 130, 246, 0.4);
        }

        .incident-marker:hover {
            transform: scale(1.1);
            z-index: 20;
        }

        .incident-marker.clustered:hover {
            transform: scale(1.15);
        }

        .incident-marker.selected {
            transform: scale(1.15);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3), 0 0 0 6px rgba(245, 158, 11, 0.4);
            border-color: #f59e0b;
        }

        .incident-marker.pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { 
                box-shadow: 0 4px 12px rgba(0,0,0,0.25), 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            70% { 
                box-shadow: 0 4px 12px rgba(0,0,0,0.25), 0 0 0 12px rgba(239, 68, 68, 0);
            }
            100% { 
                box-shadow: 0 4px 12px rgba(0,0,0,0.25), 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }


        .incident-marker i {
            font-size: 12px;
        }

        .incident-marker.clustered i {
            font-size: 12px;
        }
        
        /* Severity-based marker colors */
        .incident-marker.severity-4 { 
            background: linear-gradient(135deg, #10b981, #059669); /* Green - Low */
        }
        .incident-marker.severity-3 { 
            background: linear-gradient(135deg, #f59e0b, #d97706); /* Yellow - Medium */
        }
        .incident-marker.severity-2 { 
            background: linear-gradient(135deg, #ea580c, #c2410c); /* Orange - High */
        }
        .incident-marker.severity-1 { 
            background: linear-gradient(135deg, #ef4444, #dc2626); /* Red - Critical */
        }

        .incident-marker.clustered.severity-4 { 
            background: linear-gradient(135deg, #059669, #047857); 
        }
        .incident-marker.clustered.severity-3 { 
            background: linear-gradient(135deg, #d97706, #b45309); 
        }
        .incident-marker.clustered.severity-2 { 
            background: linear-gradient(135deg, #c2410c, #9a3412); 
        }
        .incident-marker.clustered.severity-1 { 
            background: linear-gradient(135deg, #dc2626, #b91c1c); 
        }

        /* Mixed cluster - for multiple incident types at same location */
        .mixed-cluster {
            background: linear-gradient(135deg, #8b5cf6, #f59e0b, #ef4444);
            background-size: 300% 300%;
            animation: gradient-shift 3s ease infinite;
        }

        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .incident-tooltip {
            position: absolute;
            background: var(--white);
            color: var(--text-primary);
            padding: 1rem;
            border-radius: 12px;
            font-size: 14px;
            max-width: 320px;
            z-index: 100;
            pointer-events: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.2s ease;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--gray-200);
            will-change: opacity, transform;
        }

        .incident-tooltip.show {
            opacity: 1;
            transform: translateY(0);
        }

        .incident-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 20px;
            border: 8px solid transparent;
            border-top-color: var(--white);
        }

        .incident-tooltip::before {
            content: '';
            position: absolute;
            top: 100%;
            left: 19px;
            border: 9px solid transparent;
            border-top-color: var(--gray-200);
        }

        .tooltip-header {
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 8px;
            font-size: 15px;
        }

        .tooltip-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .tooltip-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .tooltip-value {
            color: var(--text-primary);
            font-weight: 600;
        }

        .tooltip-description {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--gray-200);
            font-style: italic;
            color: var(--text-secondary);
            font-size: 12px;
            line-height: 1.4;
        }

        /* Cluster tooltip styles */
        .cluster-incident-list {
            margin-top: 8px;
        }

        .cluster-incident-item {
            padding: 6px 0;
            border-bottom: 1px solid var(--gray-200);
            font-size: 12px;
        }

        .cluster-incident-item:last-child {
            border-bottom: none;
        }

        .cluster-incident-type {
            font-weight: 600;
            color: var(--primary-blue);
        }

        .cluster-incident-time {
            font-size: 11px;
            color: var(--text-secondary);
        }

        .modal-incident-item:hover {
        background: var(--bg-blue) !important;
        border-color: var(--light-blue) !important;
        transform: translateY(-1px);
        }

        .severity-indicator.severity-4 { background: #10b981; }
        .severity-indicator.severity-3 { background: #f59e0b; }
        .severity-indicator.severity-2 { background: #ea580c; }
        .severity-indicator.severity-1 { background: #ef4444; }

        @media (max-width: 1024px) {
            .map-container {
                flex-direction: column;
                height: auto;
            }
            
            .sidebar {
                width: 100%;
                height: 500px;
            }
            
            .map-panel {
                height: 600px;
            }

            .floor-selector {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .map-container {
                padding: 0.5rem;
            }
            
            .sidebar {
                height: 450px;
            }

            .filter-container {
                gap: 6px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .floor-selector {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }

            .floor-option {
                padding: 10px 6px;
                font-size: 10px;
            }
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state p {
            margin-bottom: 0.5rem;
        }

        .empty-state p:last-child {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="map-container">
        <!-- Main Map Panel -->
        <div class="map-panel">
            <div class="map-wrapper">
                <div class="floor-plan" id="floorPlan">
                    <!-- Floor plan background and incident markers will be added here -->
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>
                    <i class="fas fa-exclamation-triangle"></i>
                    Incident Control Center
                </h2>
                <div class="status-indicator connected" id="connectionStatus">
                    <i class="fas fa-circle"></i>
                    Real-time Connected
                </div>
            </div>
            
            <div class="sidebar-content">
                <div class="tab-navigation">
                    <button class="tab-button active" data-tab="controls">
                        <i class="fas fa-cog"></i>
                        Controls & Stats
                    </button>
                    <button class="tab-button" data-tab="incidents">
                        <i class="fas fa-list"></i>
                        Incidents
                    </button>
                </div>

                <!-- Controls & Stats Tab -->
                <div class="tab-content active" id="controls-tab">
                    <div class="control-section">
                        <!-- Floor Selection -->
                        <div class="section-group">
                            <div class="card">
                                <h3><i class="fas fa-building"></i> Floor Selection</h3>
                                <div class="current-floor-indicator" id="currentFloorIndicator">
                                    Ground Floor
                                </div>
                                <div class="floor-selector">
                                    <label class="floor-option active">
                                        <input type="radio" name="floor" value="ground" checked>
                                        <span>Ground</span>
                                    </label>
                                    <label class="floor-option">
                                        <input type="radio" name="floor" value="second">
                                        <span>2nd Floor</span>
                                    </label>
                                    <label class="floor-option">
                                        <input type="radio" name="floor" value="third">
                                        <span>3rd Floor</span>
                                    </label>
                                    <label class="floor-option">
                                        <input type="radio" name="floor" value="fourth">
                                        <span>4th Floor</span>
                                    </label>
                                    <label class="floor-option">
                                        <input type="radio" name="floor" value="fifth">
                                        <span>5th Floor</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Grid -->
                        <div class="section-group">
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <span class="stat-number" id="activeCount">0</span>
                                    <div class="stat-label">Active</div>
                                </div>
                                <div class="stat-card">
                                    <span class="stat-number" id="highPriorityCount">0</span>
                                    <div class="stat-label">High Priority</div>
                                </div>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="section-group">
                            <div class="card">
                                <h4><i class="fas fa-info-circle"></i> System Status</h4>
                                <div class="debug-stats">
                                    <div class="debug-stat">
                                        <span>Floor:</span>
                                        <span id="currentFloor">Ground</span>
                                    </div>
                                    <div class="debug-stat">
                                        <span>Total:</span>
                                        <span id="totalIncidents">0</span>
                                    </div>
                                    <div class="debug-stat">
                                        <span>Visible:</span>
                                        <span id="visibleIncidents">0</span>
                                    </div>
                                    <div class="debug-stat">
                                        <span>Clusters:</span>
                                        <span id="clusterCount">0</span>
                                    </div>
                                    <div class="debug-stat">
                                        <span>API:</span>
                                        <span id="apiStatus">Loading...</span>
                                    </div>
                                    <div class="debug-stat">
                                        <span>Updated:</span>
                                        <span id="lastUpdated">--:--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filters -->
                        <div class="section-group">
                            <div class="card">
                                <h3><i class="fas fa-filter"></i> Filter by Type</h3>
                                <div class="filter-container">
                                    <label class="filter-checkbox">
                                        <input type="checkbox" value="Medical / Health" checked>
                                        <i class="fas fa-heartbeat filter-icon"></i>
                                        <span class="filter-text">Medical / Health</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" value="Behavioral / Disciplinary" checked>
                                        <i class="fas fa-user-slash filter-icon"></i>
                                        <span class="filter-text">Behavioral / Disciplinary</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" value="Safety / Security" checked>
                                        <i class="fas fa-shield-alt filter-icon"></i>
                                        <span class="filter-text">Safety / Security</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" value="Environmental / Facility-Related Incident" checked>
                                        <i class="fas fa-leaf filter-icon"></i>
                                        <span class="filter-text">Environmental / Facility</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" value="Natural Disasters & Emergency Events" checked>
                                        <i class="fas fa-bolt filter-icon"></i>
                                        <span class="filter-text">Natural Disasters & Emergency</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" value="Technology / Cyber Incident" checked>
                                        <i class="fas fa-desktop filter-icon"></i>
                                        <span class="filter-text">Technology / Cyber</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" value="Administrative / Policy Violations" checked>
                                        <i class="fas fa-file-contract filter-icon"></i>
                                        <span class="filter-text">Administrative / Policy</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" value="Lost and Found" checked>
                                        <i class="fas fa-search filter-icon"></i>
                                        <span class="filter-text">Lost & Found</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Incidents Tab -->
                <div class="tab-content" id="incidents-tab">
                    <div class="incidents-section">
                        <div class="incidents-header">
                            <span><i class="fas fa-list"></i> Active Incidents</span>
                            <span class="incident-count-badge" id="incidentCountBadge">0</span>
                        </div>
                        <div class="incident-list-content" id="incidentListContent">
                            <!-- Incidents will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Tooltip -->
    <div class="incident-tooltip" id="tooltip"></div>

    <script>
        
       (function() {
            // Get floor from URL immediately when script loads
            const urlParams = new URLSearchParams(window.location.search);
            const initialFloor = urlParams.get('floor');
            const floor = initialFloor && ['ground', 'second', 'third', 'fourth', 'fifth'].includes(initialFloor) ? initialFloor : 'ground';
            
            // Set global variable immediately
            window.currentFloor = floor;
            
            console.log('Pre-DOM floor detection:', floor);
            
            // CRITICAL: Set initial state immediately when DOM is ready but before rendering
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    setInitialFloorStateImmediate(floor);
                });
            } else {
                // DOM already loaded
                setInitialFloorStateImmediate(floor);
            }
        })();

        // Multi-Floor Coordinate System with Mock Data for Floors 2-5
        const FLOOR_COORDINATES = {
            ground: {
                'RESTROOM 1 (Ground Floor)': { x: 45, y: 705 },
                'FOODCOURT 5': { x: 44, y: 610 },
                'FOODCOURT 6': { x: 44, y: 560 },
                'FOODCOURT 7': { x: 43, y: 504 },
                'STAIRS 2 (Ground Floor)': { x: 60, y: 393 },
                'FOODCOURT 8': { x: 45, y: 340 },
                'FOODCOURT 9': { x: 44, y: 294 },
                'FOODCOURT 10': { x: 44, y: 241 },
                'FOODCOURT 11': { x: 65, y: 174 },
                'STAIRS 3 (Ground Floor)': { x: 45, y: 120 },
                'RESTROOM 2 (Ground Floor)': { x: 60, y: 56 },
                'CENTER FOR RESEARCH AND DEVELOPMENT': { x: 274, y: 55 },
                'OFFICE OF THE VICE PRESIDENT': { x: 467, y: 55 },
                'STAIRS 4 (Ground Floor)': { x: 550, y: 55 },
                'ELECTRICAL ROOM': { x: 615, y: 50 },
                'SOCIAL MEDIA DEPARTMENT': { x: 710, y: 65 },
                'TLE 1': { x: 837, y: 65 },
                'TLE 2': { x: 921, y: 66 },
                'GUIDANCE OFFICE': { x: 985, y: 65 },
                'CLINIC': { x: 1045, y: 65 },
                'RESTROOM 3 (Ground Floor)': { x: 1115, y: 67 },
                'SSC & SSG OFFICE': { x: 1115, y: 110 },
                'STAIRS 5 (Ground Floor)': { x: 1115, y: 155 },
                'GYM': { x: 1003, y: 287 },
                'STORAGE GF': { x: 1111, y: 415 },
                'RESTROOM 4 (Ground Floor)': { x: 1034, y: 443 },
                'STAIRS 6 (Ground Floor)': { x: 940, y: 468 },
                'PRINCIPAL OFFICE': { x: 875, y: 485 },
                'ASCENDENS ASIA': { x: 790, y: 510 },
                'CHAPEL': { x: 705, y: 532 },
                'PREFECT OF DISCIPLINE': { x: 660, y: 545 },
                'SAFETY AND SECURITY': { x: 615, y: 560 },
                'FOODCOURT 1': { x: 395, y: 606 },
                'FOODCOURT 2': { x: 332, y: 624 },
                'FOODCOURT 3': { x: 270, y: 637 },
                'FOODCOURT 4': { x: 206, y: 652 },
                'STAIRS 1 (Ground Floor)': { x: 150, y: 666 }
            },
            second: {
                'RESTROOM 1 (SECOND FLOOR)': { x: 25, y: 715 },
                'ROOM 211': { x: 45, y: 655 },
                'ROOM 212': { x: 45, y: 605 },
                'ROOM 213': { x: 45, y: 560 },
                'ROOM 214': { x: 45, y: 505 },
                'STAIRS 2 (SECOND FLOOR)': { x: 45, y: 455 },
                'ROOM 215': { x: 45, y: 404 },
                'ROOM 216': { x: 45, y: 357 },
                'ROOM 217': { x: 45, y: 310 },
                'ROOM 218': { x: 45, y: 252 },
                'ROOM 219': { x: 45, y: 207 },
                'ROOM 220': { x: 45, y: 157 },
                'STAIRS 3 (SECOND FLOOR)': { x: 45, y: 107 },
                'SPEECH LAB': { x: 65, y: 45 },
                'ROOM 221': { x: 154, y: 58 },
                'ROOM 222': { x: 215, y: 58 },
                'ROOM 223': { x: 285, y: 58 },
                'ROOM 224': { x: 350, y: 58 },
                'ROOM 225': { x: 415, y: 58 },
                'ROOM 226': { x: 480, y: 58 },
                'STAIRS 4 (SECOND FLOOR)': { x: 595, y: 58 },
                'ROOM 227': { x: 715, y: 58 },
                'ROOM 228': { x: 775, y: 58 },
                'ROOM 229': { x: 845, y: 58 },
                'ROOM 230': { x: 917, y: 58 },
                'ROOM 231': { x: 977, y: 58 },
                'ROOM 232': { x: 1042, y: 58 },
                'COMPUTER LAB': { x: 1115, y: 58},
                'FACULTY ROOM (SECOND FLOOR)': { x: 1115, y: 115 },
                'STAIRS 5 (SECOND FLOOR)': { x: 1115, y: 155 },
                'STAIRS 6 (SECOND FLOOR)': { x: 909, y: 475 },
                'STRAND HEAD': { x: 1069, y: 435 },
                'ROOM 201': { x: 832, y: 503 },
                'GENDER AND DEVELOPMENT': { x: 857, y: 425 },
                'ROOM 202': { x: 777, y: 518 },
                'ROOM 203': { x: 710, y: 534 },
                'ROOM 204': { x: 655, y: 550 },
                'ROOM 205': { x: 590, y: 564 },
                'ACER': { x: 530, y: 580 },
                'ROOM 206': { x: 480, y: 595 },
                'ROOM 207': { x: 415, y: 615 },
                'ROOM 208': { x: 355, y: 635 },
                'ROOM 209': { x: 295, y: 650 },
                'ROOM 210': { x: 225, y: 670 },
                'ITS': { x: 95, y: 715 },
                'STAIRS 1 (SECOND FLOOR)': { x: 155, y: 685 }
            
            },
            third: {
                'RESTROOM 1 (THIRD FLOOR)': { x: 25, y: 715 },
                'ROOM 311': { x: 45, y: 655 },
                'ROOM 312': { x: 45, y: 605 },
                'ROOM 313': { x: 45, y: 560 },
                'ROOM 314': { x: 45, y: 505 },
                'STAIRS 2 (THIRD FLOOR)': { x: 45, y: 455 },
                'ROOM 315': { x: 45, y: 404 },
                'ROOM 316': { x: 45, y: 357 },
                'ROOM 317': { x: 45, y: 310 },
                'ROOM 318': { x: 45, y: 252 },
                'ROOM 319': { x: 45, y: 207 },
                'ROOM 320': { x: 45, y: 157 },
                'STAIRS 3 (THIRD FLOOR)': { x: 45, y: 107 },
                'RESTROOM 2 (THIRD FLOOR)': { x: 65, y: 45 },
                'ROOM 321': { x: 154, y: 58 },
                'ROOM 322': { x: 215, y: 58 },
                'ROOM 323': { x: 285, y: 58 },
                'ROOM 324': { x: 350, y: 58 },
                'ROOM 325': { x: 415, y: 58 },
                'ROOM 326': { x: 480, y: 58 },
                'STAIRS 4 (THIRD FLOOR)': { x: 580, y: 58 },
                'BSBA DEPARTMENT': { x: 675, y: 58 },
                'FACULTY ROOM 1 (THIRD FLOOR)': { x: 725, y:  58 },
                'ROOM 327': { x: 784, y: 58 },
                'ROOM 328': { x: 851, y: 58 },
                'ROOM 329': { x: 914, y: 58 },
                'ROOM 330': { x: 977, y: 58},
                'ROOM 331': { x: 1041, y: 58 },
                'ROOM 332': { x: 1113, y: 58 },
                'FACULTY ROOM 2 (THIRD FLOOR)': { x: 1120, y: 115 },
                'STAIRS 5 (THIRD FLOOR)': { x: 1120, y: 155 },
                'RESTROOM 3 (THIRD FLOOR)': { x: 1026, y: 456 },
                'LIBRARY (THIRD FLOOR)': { x: 922, y: 304 },
                'STAIRS 6 (THIRD FLOOR)': { x: 911, y: 478 },
                'ROOM 301': { x: 833, y: 502 },
                'ROOM 302': { x: 779, y: 518 },
                'ROOM 303': { x: 715, y: 533 },
                'ROOM 304': { x: 655, y: 545 },
                'ROOM 305': { x: 590, y: 565 },
                'PHYSICS LAB': { x: 525, y: 585 },
                'ROOM 306': { x: 475, y: 600 },
                'ROOM 307': { x: 420, y: 615 },
                'ROOM 308': { x: 360, y: 630 },
                'ROOM 309': { x: 295, y: 645 },
                'ROOM 310': { x: 225, y: 665 },
                'STAIRS 1 (THIRD FLOOR)': { x: 155, y: 680 }
            },
            fourth: {
                'RESTROOM 1 (FOURTH FLOOR)': { x: 25, y: 715 },
                'ROOM 411': { x: 45, y: 655 },
                'ROOM 412': { x: 45, y: 605 },
                'ROOM 413': { x: 45, y: 560 },
                'ROOM 414': { x: 45, y: 505 },
                'STAIRS 2 (FOURTH FLOOR)': { x: 45, y: 455 },
                'ROOM 415': { x: 45, y: 404 },
                'ROOM 416': { x: 45, y: 357 },
                'ROOM 417': { x: 45, y: 310 },
                'ROOM 418': { x: 45, y: 252 },
                'ROOM 419': { x: 45, y: 207 },
                'ROOM 420': { x: 45, y: 157 },
                'STAIRS 3 (FOURTH FLOOR)': { x: 45, y: 107 },
                'ROOM 421': { x: 65, y: 45 },
                'ROOM 422': { x: 154, y: 58 },
                'ROOM 423': { x: 215, y: 58 },
                'ROOM 424': { x: 285, y: 58 },
                'ROOM 425': { x: 350, y: 58 },
                'ROOM 426': { x: 415, y: 58 },
                'ROOM 427': { x: 480, y: 58 },
                'STAIRS 4 (FOURTH FLOOR)': { x: 580, y: 58 },
                'HBM HEAD (FOURTH FLOOR)': { x: 675, y: 58 },
                'ROOM 428': { x: 725, y:  58 },
                'ROOM 429': { x: 784, y: 58 },
                'ROOM 430': { x: 851, y: 58 },
                'ROOM 431': { x: 914, y: 58 },
                'ROOM 432': { x: 977, y: 58},
                'ROOM 433': { x: 1041, y: 58 },
                'ROOM 434': { x: 1113, y: 58 },
                'FACULTY ROOM (FOURTH FLOOR)': { x: 1120, y: 115 },
                'STAIRS 5 (FOURTH FLOOR)': { x: 1120, y: 155 },
                'RESTROOM 3 (FOURTH FLOOR)': { x: 1026, y: 456 },
                'LIBRARY (FOURTH FLOOR)': { x: 1000, y: 314 },
                'STAIRS 6 (FOURTH FLOOR)': { x: 911, y: 478 },
                'ROOM 401': { x: 833, y: 502 },
                'ROOM 402': { x: 779, y: 518 },
                'ROOM 403': { x: 715, y: 533 },
                'ROOM 404': { x: 655, y: 545 },
                'ROOM 405': { x: 590, y: 565 },
                'CHEMISTRY LAB': { x: 525, y: 585 },
                'ROOM 406': { x: 475, y: 600 },
                'ROOM 407': { x: 420, y: 615 },
                'ROOM 408': { x: 360, y: 630 },
                'ROOM 409': { x: 295, y: 645 },
                'ROOM 410': { x: 225, y: 665 },
                'STAIRS 1 (FOURTH FLOOR)': { x: 155, y: 680 }
            },
            fifth: {
                'ROOM 510': { x: 45, y: 655 },
                'ROOM 511': { x: 45, y: 605 },
                'ROOM 512': { x: 45, y: 560 },
                'ROOM 513': { x: 45, y: 505 },
                'STAIRS 2 (FIFTH FLOOR)': { x: 45, y: 455 },
                'ROOM 514': { x: 45, y: 404 },
                'ROOM 515': { x: 45, y: 357 },
                'ROOM 516': { x: 45, y: 310 },
                'ROOM 517': { x: 45, y: 252 },
                'ROOM 518': { x: 45, y: 207 },
                'ROOM 519': { x: 45, y: 157 },
                'STAIRS 3 (FIFTH FLOOR)': { x: 45, y: 107 },
                'ROOM 520': { x: 155, y: 58 },
                'ROOM 521': { x: 220, y: 58 },
                'ROOM 522': { x: 285, y: 58 },
                'ROOM 523': { x: 345, y: 58 },
                'ROOM 524': { x: 415, y: 58 },
                'ROOM 525': { x: 480, y: 58 },
                'ABM FACULTY': { x: 535, y: 58 },
                'STAIRS 4 (FIFTH FLOOR)': { x: 605, y: 58 },
                'HBM HEAD (FIFTH FLOOR)': { x: 675, y: 58 },
                'ROOM 526': { x: 725, y: 58 },
                'ROOM 527': { x: 780, y: 58 },
                'ROOM 528': { x: 845, y: 58 },
                'ROOM 529': { x: 910, y: 58 },
                'ROOM 530': { x: 975, y: 58 },
                'ROOM 531': { x: 1042, y: 58 },
                'ROOM 532': { x: 1116, y: 58 },
                'PUBLICATION': { x: 1116, y: 115 },
                'STAIRS 5 (FIFTH FLOOR)': { x: 1118, y: 155 },
                'STAIRS 6 (FIFTH FLOOR)': { x: 906, y: 469 },
                'ROOM 501': { x: 833, y: 502 },
                'ROOM 502': { x: 779, y: 518 },
                'ROOM 503': { x: 715, y: 533 },
                'ROOM 504': { x: 655, y: 545 },
                'CSS DEPARTMENT FACULTY ROOM': { x: 576, y: 572 },
                'ROOM 505': { x: 475, y: 600 },
                'ROOM 506': { x: 420, y: 615 },
                'ROOM 507': { x: 360, y: 630 },
                'ROOM 508': { x: 295, y: 645 },
                'ROOM 509': { x: 225, y: 665 },
                'LAB 3 (FIFTH FLOOR)': { x: 120, y: 216 },
                'LAB 4 (FIFTH FLOOR)': { x: 755, y: 435 },
                'LAB 5 (FIFTH FLOOR)': { x: 630, y: 475 },
                'CSS DEPARTMENT': { x: 420, y: 535 },
                'LAB 6 (FIFTH FLOOR)': { x: 315, y: 562 },
                'LAB 7 (FIFTH FLOOR)': { x: 200, y: 595 },
                'STAIRS 1 (FIFTH FLOOR)': { x: 155, y: 680 },
                'LAB 8 (FIFTH FLOOR)': { x: 120, y: 561 },
                'LAB 1 (FIFTH FLOOR)': { x: 120, y: 384 },
                'LAB 2 (FIFTH FLOOR)': { x: 120, y: 296 },
                'CASE DEPARTMENT FR': { x: 120, y: 450 },
                'CASE DEPARTMENT HO': { x: 120, y: 500 },
            }
        };

        // Floor images mapping
        const FLOOR_IMAGES = {
            ground: 'storage/floorplans/ground-floor.png',
            second: 'storage/floorplans/second-floor.png',
            third: 'storage/floorplans/third-floor.png',
            fourth: 'storage/floorplans/fourth-floor.png',
            fifth: 'storage/floorplans/fifth-floor.png'
        };

        // Floor display names
        const FLOOR_NAMES = {
            ground: 'Ground Floor',
            second: '2nd Floor',
            third: '3rd Floor', 
            fourth: '4th Floor',
            fifth: '5th Floor'
        };
        function setInitialFloorStateImmediate(floor) {
            console.log('Setting immediate floor state to:', floor);
            
            // Update global variable first
            currentFloor = floor;
            window.currentFloor = floor;
            
            // Update text elements immediately
            const floorIndicator = document.getElementById('currentFloorIndicator');
            const currentFloorEl = document.getElementById('currentFloor');
            
            if (floorIndicator) {
                floorIndicator.textContent = FLOOR_NAMES[floor];
            }
            if (currentFloorEl) {
                currentFloorEl.textContent = FLOOR_NAMES[floor].replace(' Floor', '');
            }
            
            // Set background image immediately without any transition
            const floorPlan = document.getElementById('floorPlan');
            if (floorPlan) {
                const imagePath = `/storage/floorplans/${FLOOR_IMAGES[floor].split('/').pop()}`;
                floorPlan.style.transition = 'none';
                floorPlan.style.backgroundImage = `url('${imagePath}')`;
                floorPlan.style.visibility = 'visible';
                floorPlan.classList.add('initialized');
                floorPlan.offsetHeight; // Force immediate render
            }
            
            // Set radio button states immediately - THIS IS CRITICAL
            document.querySelectorAll('.floor-option').forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                if (radio) {
                    option.classList.remove('active');
                    radio.checked = false;
                    
                    if (radio.value === floor) {
                        radio.checked = true;
                        option.classList.add('active');
                    }
                }
            });
            
            // IMMEDIATELY update stats and controls for the correct floor
            // This prevents the flash of ground floor stats
            if (window.incidents && window.incidents.length > 0) {
                updateStats();
                renderIncidents();
            }
        }

        // Global variables
        let incidents = [];
        let activeFilters = new Set([
            'Medical / Health',
            'Behavioral / Disciplinary', 
            'Safety / Security',
            'Environmental / Facility-Related Incident',
            'Natural Disasters & Emergency Events',
            'Technology / Cyber Incident',
            'Administrative / Policy Violations',
            'Lost & Found'
        ]);
        let selectedIncident = null;
        let tooltip = document.getElementById('tooltip');
        let updateInterval = null;
        let timeInterval = null;
        let currentFloor = 'ground';
        let clusterData = new Map(); // For storing clustered incidents

        function determineFloorFromLocation(location) {
        if (!location) return 'ground';
        
        const normalizedLocation = location.toString().toUpperCase().trim();
        
        // Check all floor coordinate systems to see where this location exists
        for (const [floorKey, floorCoords] of Object.entries(FLOOR_COORDINATES)) {
            // Direct match
            if (floorCoords[normalizedLocation]) {
                return floorKey;
            }
            
            // Fuzzy matching
            for (const roomName of Object.keys(floorCoords)) {
                if (normalizedLocation.includes(roomName) || roomName.includes(normalizedLocation)) {
                    return floorKey;
                }
            }
        }
        
        // Pattern-based detection as fallback
        // Second floor patterns
        if (normalizedLocation.match(/^ROOM 2\d+/) || 
            normalizedLocation.includes('COMPUTER LAB') ||
            normalizedLocation.includes('FACULTY ROOM') ||
            normalizedLocation.includes('SPEECH LAB')) {
            return 'second';
        }
        
        // Third floor patterns  
        if (normalizedLocation.match(/^ROOM 3\d+/) ||
            normalizedLocation.match(/^CLASSROOM 3\d+/) ||
            normalizedLocation.includes('CHEMISTRY LAB') ||
            normalizedLocation.includes('PHYSICS LAB') ||
            normalizedLocation.includes('ART ROOM') ||
            normalizedLocation.includes('MUSIC ROOM')) {
            return 'third';
        }
        
        // Fourth floor patterns
        if (normalizedLocation.match(/^ROOM 4\d+/) ||
            normalizedLocation.match(/^CLASSROOM 4\d+/) ||
            normalizedLocation.includes('LANGUAGE LAB') ||
            normalizedLocation.includes('CONFERENCE ROOM')) {
            return 'fourth';
        }
        
        // Fifth floor patterns
        if (normalizedLocation.match(/^ROOM 5\d+/) ||
            normalizedLocation.match(/^CLASSROOM 5\d+/) ||
            normalizedLocation.includes('AUDITORIUM') ||
            normalizedLocation.includes('BOARD ROOM')) {
            return 'fifth';
        }
        
        // Default to ground floor
        return 'ground';
    }

        const INCIDENT_TYPES = {
            'Medical / Health': 'Medical / Health',
            'Behavioral / Disciplinary': 'Behavioral / Disciplinary',
            'Safety / Security': 'Safety / Security',
            'Environmental / Facility-Related Incident': 'Environmental / Facility',
            'Natural Disasters & Emergency Events': 'Natural Disasters & Emergency',
            'Technology / Cyber Incident': 'Technology / Cyber',
            'Administrative / Policy Violations': 'Administrative / Policy',
            'Lost & Found': 'Lost and Found'
        };

        // Function to normalize incident type for CSS class names
        function getIncidentCssClass(incidentType) {
            const typeMapping = {
                'Medical / Health': 'medical-health',
                'Behavioral / Disciplinary': 'behavioral-disciplinary',
                'Safety / Security': 'safety-security',
                'Environmental / Facility-Related Incident': 'environmental-facility',
                'Natural Disasters & Emergency Events': 'natural-disasters-emergency',
                'Technology / Cyber Incident': 'technology-cyber',
                'Administrative / Policy Violations': 'administrative-policy',
                'Lost & Found': 'lost-found'
            };
            return typeMapping[incidentType] || 'administrative-policy';
        }
        

        function getFloorFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const floor = urlParams.get('floor');
            return floor && FLOOR_COORDINATES[floor] ? floor : 'ground';
        }

        // Function to update URL with current floor
        function updateURL(floor) {
            const url = new URL(window.location);
            url.searchParams.set('floor', floor);
            window.history.replaceState({}, '', url);
        }


        // Floor switching functionality
        function switchFloor(newFloor) {
            if (newFloor === currentFloor) return;
            
            console.log(`Switching from ${currentFloor} to ${newFloor}`);
            
            // Update global state immediately
            currentFloor = newFloor;
            window.currentFloor = newFloor;
            updateURL(newFloor);
            
            // Update all UI elements synchronously
            updateFloorUISync(newFloor);
            
            // Update data-dependent elements immediately if data is available
            if (incidents && incidents.length > 0) {
                updateStats();
                renderIncidents();
                renderIncidentList();
            }
        }

        function setInitialFloorState(floor) {
            console.log('Setting initial floor state to:', floor);
            
            // Update text elements immediately
            const floorIndicator = document.getElementById('currentFloorIndicator');
            const currentFloorEl = document.getElementById('currentFloor');
            
            if (floorIndicator) {
                floorIndicator.textContent = FLOOR_NAMES[floor];
            }
            if (currentFloorEl) {
                currentFloorEl.textContent = FLOOR_NAMES[floor].replace(' Floor', '');
            }
            
            // Set background image immediately without transition
            const floorPlan = document.getElementById('floorPlan');
            if (floorPlan) {
                const imagePath = `{{ asset('storage/floorplans/') }}/${FLOOR_IMAGES[floor].split('/').pop()}`;
                floorPlan.style.backgroundImage = `url('${imagePath}')`;
                floorPlan.style.opacity = '1';
            }
            
            // Set radio button states immediately
            document.querySelectorAll('.floor-option').forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                option.classList.remove('active');
                radio.checked = false;
                
                if (radio.value === floor) {
                    radio.checked = true;
                    option.classList.add('active');
                }
            });
        }

        // Enhanced coordinate lookup with floor support
        function findLocationCoordinates(location, floor = currentFloor) {
            if (!location) return null;
            
            const floorCoordinates = FLOOR_COORDINATES[floor];
            if (!floorCoordinates) {
                console.warn('No coordinates defined for floor:', floor);
                return null;
            }
            
            // Try exact match first (case-sensitive)
            if (floorCoordinates[location]) {
                console.log(`Exact match found for "${location}" on ${floor} floor`);
                return floorCoordinates[location];
            }
            
            // Try case-insensitive exact match
            const locationUpper = location.toUpperCase();
            for (const [roomName, roomCoords] of Object.entries(floorCoordinates)) {
                if (roomName.toUpperCase() === locationUpper) {
                    console.log(`Case-insensitive match found: "${location}" matches "${roomName}" on ${floor} floor`);
                    return roomCoords;
                }
            }
            
            // Fuzzy matching as fallback
            for (const [roomName, roomCoords] of Object.entries(floorCoordinates)) {
                if (locationUpper.includes(roomName.toUpperCase()) || roomName.toUpperCase().includes(locationUpper)) {
                    console.log(`Fuzzy match found: "${location}" matches "${roomName}" on ${floor} floor`);
                    return roomCoords;
                }
            }
            
            console.warn(`No coordinates found for location: "${location}" on floor: ${floor}`);
            return null;
        }
        // Clustering algorithm - groups incidents by location
        function clusterIncidentsByLocation(incidents) {
            const clusters = new Map();
            
            incidents.forEach(incident => {
                const coordinates = findLocationCoordinates(incident.location, incident.floor);
                if (!coordinates) return;
                
                // Create a unique key for this location on this floor
                const locationKey = `${incident.floor}-${incident.location}`;
                
                if (!clusters.has(locationKey)) {
                    clusters.set(locationKey, {
                        location: incident.location,
                        floor: incident.floor,
                        coordinates: coordinates,
                        incidents: []
                    });
                }
                
                clusters.get(locationKey).incidents.push(incident);
            });
            
            return clusters;
        }

        // Create clustered marker
       function createClusteredMarker(cluster) {
            const marker = document.createElement('div');
            const incidents = cluster.incidents;
            const incidentCount = incidents.length;
            
            // Determine cluster styling based on highest severity
            const highestSeverity = Math.max(...incidents.map(i => i.esi_level || 1));
            const severityClass = `severity-${highestSeverity}`;
            
            if (incidentCount === 1) {
                // Single incident
                const incident = incidents[0];
                marker.className = `incident-marker ${severityClass}`;
                
                const incidentType = incident.incident_type || incident.incident_category;
                const typeIcon = getIncidentTypeIcon(incidentType);
                marker.innerHTML = typeIcon;
                
                if ((incident.esi_level || 1) <= 2) {
                    marker.classList.add('pulse');
                }
                } else {
                    // Multiple incidents - clustered marker
                    marker.className = `incident-marker clustered ${severityClass}`;
                    
                    // Check if all incidents are the same type
                    const uniqueTypes = [...new Set(incidents.map(i => i.incident_type || i.incident_category))];
                    
                    if (uniqueTypes.length === 1) {
                        // All same type - show type icon with count
                        const typeIcon = getIncidentTypeIcon(uniqueTypes[0]);
                        marker.innerHTML = `
                            <div style="display: flex; flex-direction: column; align-items: center; font-size: 10px;">
                                ${typeIcon}
                                <span style="font-size: 8px; font-weight: bold;">${incidentCount}</span>
                            </div>
                        `;
                    } else {
                        // Mixed types - show count only
                        marker.innerHTML = incidentCount.toString();
                        marker.classList.add('mixed-cluster');
                    }
                
                // Add pulse if any incident is high priority
                if (incidents.some(i => (i.esi_level || 1) <= 2)) {
                    marker.classList.add('pulse');
                }
            }
            
            marker.dataset.clusterKey = `${cluster.floor}-${cluster.location}`;
            marker.dataset.incidentCount = incidentCount;
            
            // Position marker
            marker.style.transform = `translate(${cluster.coordinates.x}px, ${cluster.coordinates.y}px)`;
            
            // Event listeners
            marker.addEventListener('mouseenter', (e) => showClusterTooltip(e, cluster), { passive: true });
            marker.addEventListener('mouseleave', hideTooltip, { passive: true });
            marker.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (incidentCount === 1) {
                    redirectToReport(cluster.incidents[0].id);
                } else {
                    showIncidentSelectionModal(cluster);
                }
            });
            
            return marker;
        }

        // Handle cluster click - if single incident, go to report; if multiple, show options
        function handleClusterClick(cluster) {
            if (cluster.incidents.length === 1) {
                redirectToReport(cluster.incidents[0].id);
            } else {
                // For multiple incidents, you could show a popup or redirect to first one
                // For now, redirect to the most recent (highest priority) incident
                const sortedIncidents = cluster.incidents.sort((a, b) => {
                    const esiA = a.esi_level || 1;
                    const esiB = b.esi_level || 1;
                    if (esiB !== esiA) return esiB - esiA;
                    return new Date(b.created_at) - new Date(a.created_at);
                });
                redirectToReport(sortedIncidents[0].id);
            }
        }
        function showIncidentSelectionModal(cluster) {
            // Remove any existing modal
            const existingModal = document.getElementById('incidentSelectionModal');
            if (existingModal) existingModal.remove();
            
            const modal = document.createElement('div');
            modal.id = 'incidentSelectionModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                max-width: 400px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            `;
            
            const incidents = cluster.incidents.sort((a, b) => {
                const esiA = a.esi_level || 1;
                const esiB = b.esi_level || 1;
                if (esiB !== esiA) return esiB - esiA;
                return new Date(b.created_at) - new Date(a.created_at);
            });
            
            modalContent.innerHTML = `
                <h3 style="margin: 0 0 1rem 0; color: var(--primary-blue);">
                    <i class="fas fa-map-marker-alt"></i>
                    ${cluster.incidents.length} Incidents at ${cluster.location}
                </h3>
                <div class="incident-selection-list">
                    ${incidents.map(incident => {
                        const timeAgo = getTimeAgo(new Date(incident.created_at));
                        const incidentType = incident.incident_type || incident.incident_category;
                        const displayType = INCIDENT_TYPES[incidentType] || incidentType;
                        const priorityText = getPriorityText(incident.esi_level || 1);
                        const severityClass = `severity-${incident.esi_level || 1}`;
                        
                        return `
                            <div class="modal-incident-item" onclick="redirectToReport(${incident.id})" style="
                                padding: 12px;
                                border: 1px solid var(--gray-200);
                                border-radius: 8px;
                                margin-bottom: 8px;
                                cursor: pointer;
                                transition: all 0.2s ease;
                            ">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="severity-indicator ${severityClass}" style="
                                        width: 24px;
                                        height: 24px;
                                        border-radius: 50%;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        color: white;
                                        font-size: 11px;
                                        font-weight: bold;
                                                            ">
                                    ${getIncidentTypeIcon(incidentType)}</div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: var(--primary-blue); font-size: 14px;">
                                            ${displayType}
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-secondary);">
                                            ${priorityText} • ${timeAgo}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
                <button onclick="document.getElementById('incidentSelectionModal').remove()" style="
                    margin-top: 1rem;
                    padding: 8px 16px;
                    background: var(--gray-300);
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    width: 100%;
                ">Cancel</button>
            `;
            
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            
            // Close on background click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
        // Enhanced tooltip for clusters
        function showClusterTooltip(event, cluster) {
            clearTimeout(tooltipTimeout);
            
            const tooltip = document.getElementById('tooltip');
            const incidents = cluster.incidents;
            
            if (incidents.length === 1) {
                // Single incident tooltip (existing logic)
                const incident = incidents[0];
                showTooltip(event, incident);
            } else {
                // Multiple incidents tooltip
                const location = cluster.location;
                const floor = FLOOR_NAMES[cluster.floor];
                const highPriorityCount = incidents.filter(i => (i.esi_level || 1) >= 3).length;
                
                let tooltipHTML = `
                    <div class="tooltip-header">${incidents.length} Incidents at ${location}</div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">Floor:</span>
                        <span class="tooltip-value">${floor}</span>
                    </div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">High Priority:</span>
                        <span class="tooltip-value">${highPriorityCount}</span>
                    </div>
                    <div class="cluster-incident-list">
                `;
                
                // Show up to 5 incidents in tooltip
                const displayIncidents = incidents.slice(0, 5);
                displayIncidents.forEach(incident => {
                    const timeAgo = getTimeAgo(new Date(incident.created_at));
                    const incidentType = incident.incident_type || incident.incident_category;
                    const displayType = INCIDENT_TYPES[incidentType] || incidentType;
                    
                    tooltipHTML += `
                        <div class="cluster-incident-item">
                            <div class="cluster-incident-type">${displayType}</div>
                            <div class="cluster-incident-time">${timeAgo}</div>
                        </div>
                    `;
                });
                
                if (incidents.length > 5) {
                    tooltipHTML += `<div class="cluster-incident-item">...and ${incidents.length - 5} more</div>`;
                }
                
                tooltipHTML += `
                    </div>
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--gray-200); font-size: 12px; color: var(--text-secondary);">
                        <i class="fas fa-mouse-pointer"></i> Click to view highest priority incident
                    </div>
                `;
                
                tooltip.innerHTML = tooltipHTML;
                tooltip.style.left = event.pageX + 15 + 'px';
                tooltip.style.top = event.pageY - 10 + 'px';
                tooltip.classList.add('show');
            }
        }

        // Enhanced rendering with clustering
        function renderIncidents() {
            const floorPlan = document.getElementById('floorPlan');
            
            // Clear existing markers
            const existingMarkers = floorPlan.querySelectorAll('.incident-marker');
            existingMarkers.forEach(marker => marker.remove());

            // Filter incidents for current floor and active filters
            const filteredIncidents = incidents.filter(incident => {
                const incidentType = incident.incident_type || incident.incident_category;
                return incident.floor === currentFloor &&
                       activeFilters.has(incidentType) && 
                       incident.status !== 'completed' && 
                       incident.status !== 'deny' && 
                       incident.status !== 'DENY' && 
                       !incident.archived;
            });

            // Cluster incidents by location
            const clusters = clusterIncidentsByLocation(filteredIncidents);
            clusterData = clusters; // Store for later use
            
            let visibleCount = 0;
            const fragment = document.createDocumentFragment();

            // Create markers for each cluster
            clusters.forEach(cluster => {
                const marker = createClusteredMarker(cluster);
                fragment.appendChild(marker);
                visibleCount += cluster.incidents.length;
            });

            // Single DOM update
            floorPlan.appendChild(fragment);
            
            // Update debug info
            updateDebugInfo('visibleIncidents', visibleCount);
            updateDebugInfo('clusterCount', clusters.size);
            
            renderIncidentList();
            updateIncidentCountBadge(visibleCount);
        }

        // Setup floor selection listeners
       function setupFloorSelection() {
            const floorOptions = document.querySelectorAll('.floor-option');
            console.log('Setting up floor selection for', floorOptions.length, 'options');
            
            floorOptions.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const radio = option.querySelector('input[type="radio"]');
                    const floor = radio.value;
                    
                    console.log('Floor option clicked:', floor);
                    
                    // Immediately check the radio button
                    radio.checked = true;
                    
                    // Switch floor immediately
                    switchFloor(floor);
                });
                
                // Also handle direct radio button clicks
                const radio = option.querySelector('input[type="radio"]');
                if (radio) {
                    radio.addEventListener('change', (e) => {
                        if (e.target.checked) {
                            console.log('Radio button changed to:', e.target.value);
                            switchFloor(e.target.value);
                        }
                    });
                }
            });
        }

        // Rest of your existing functions with minimal modifications...
        function setupTabs() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.dataset.tab;
                    
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    button.classList.add('active');
                    document.getElementById(targetTab + '-tab').classList.add('active');
                });
            });
        }

        function redirectToReport(reportId) {
            const baseUrl = window.location.origin;
            const editUrl = `${baseUrl}/reports/${reportId}/edit`;
            window.location.href = editUrl;
        }

        function initializeMap() {
            console.log('Initializing multi-floor incident dashboard...');
            
            // Use the floor that was set when script first loaded - DON'T change this
            currentFloor = window.currentFloor || 'ground';
            console.log('Initializing with floor:', currentFloor);
            
            // Set initial state BEFORE any data operations
            setInitialFloorStateImmediate(currentFloor);
            
            // Set up event handlers
            setupTabs();
            setupFloorSelection();
            setupFilters();
            
            // Defer data fetching but ensure it updates the correct floor
            setTimeout(() => {
                fetchIncidents().then(() => {
                    // After fetching, ensure we're still showing the correct floor
                    if (currentFloor !== 'ground' || window.currentFloor !== 'ground') {
                        const targetFloor = window.currentFloor || currentFloor;
                        setInitialFloorStateImmediate(targetFloor);
                    }
                });
                updateLastUpdated();
                
                // Set up intervals
                if (updateInterval) clearInterval(updateInterval);
                if (timeInterval) clearInterval(timeInterval);
                
                updateInterval = setInterval(fetchIncidents, 30000);
                timeInterval = setInterval(updateLastUpdated, 60000);
            }, 0);
        }
        function setInitialFloorStateSync(floor) {
            console.log('Setting initial floor state synchronously to:', floor);
            
            // Update text elements immediately
            const floorIndicator = document.getElementById('currentFloorIndicator');
            const currentFloorEl = document.getElementById('currentFloor');
            
            if (floorIndicator) {
                floorIndicator.textContent = FLOOR_NAMES[floor];
            }
            if (currentFloorEl) {
                currentFloorEl.textContent = FLOOR_NAMES[floor].replace(' Floor', '');
            }
            
            // Set background image immediately
            const floorPlan = document.getElementById('floorPlan');
                if (floorPlan) {
                    const imagePath = `/storage/floorplans/${FLOOR_IMAGES[floor].split('/').pop()}`;
                    floorPlan.style.transition = 'none';
                    floorPlan.style.backgroundImage = `url('${imagePath}')`;
                    floorPlan.style.visibility = 'visible';
                    floorPlan.classList.add('initialized');
                    floorPlan.offsetHeight; // Trigger reflow
                }
            
            // Set radio button states immediately
            document.querySelectorAll('.floor-option').forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                if (radio) {
                    option.classList.remove('active');
                    radio.checked = false;
                    
                    if (radio.value === floor) {
                        radio.checked = true;
                        option.classList.add('active');
                    }
                }
            });
        }
        function updateFloorUISync(floor) {
            console.log('Updating floor UI synchronously to:', floor);
            
            // Update text elements
            const floorIndicator = document.getElementById('currentFloorIndicator');
            const currentFloorEl = document.getElementById('currentFloor');
            
            if (floorIndicator) {
                floorIndicator.textContent = FLOOR_NAMES[floor];
            }
            if (currentFloorEl) {
                currentFloorEl.textContent = FLOOR_NAMES[floor].replace(' Floor', '');
            }
            
            // Update background image
            const floorPlan = document.getElementById('floorPlan');
            if (floorPlan) {
                const imagePath = `/storage/floorplans/${FLOOR_IMAGES[floor].split('/').pop()}`;
                floorPlan.style.transition = 'none';
                floorPlan.style.backgroundImage = `url('${imagePath}')`;
                floorPlan.style.visibility = 'visible';
                // Force reflow
                floorPlan.offsetHeight;
            }
            
            // Update radio buttons
            document.querySelectorAll('.floor-option').forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                if (radio) {
                    option.classList.remove('active');
                    radio.checked = false;
                    
                    if (radio.value === floor) {
                        radio.checked = true;
                        option.classList.add('active');
                    }
                }
            });
        }

        async function fetchIncidents() {
            try {
                updateDebugInfo('apiStatus', 'Loading...');
                
                const token = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
                const headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                };
                
                if (token) {
                    headers['Authorization'] = 'Bearer ' + token;
                }
                
                const response = await fetch('/api/incidents/active', { headers });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Fetched incidents:', data);
                
                incidents = Array.isArray(data) ? data : (data.data || []);
                
                // Enhanced floor assignment using location-based detection
                incidents.forEach(incident => {
                    incident.floor = determineFloorFromLocation(incident.location);
                    console.log(`Incident ${incident.id} at "${incident.location}" assigned to floor: ${incident.floor}`);
                });
                
                updateDebugInfo('apiStatus', 'Connected');
                updateDebugInfo('totalIncidents', incidents.length);
                
                // IMPORTANT: Don't change currentFloor here, just update the display for the current floor
                const displayFloor = currentFloor || window.currentFloor || 'ground';
                
                console.log(`Rendering for floor: ${displayFloor}`);
                
                requestAnimationFrame(() => {
                    renderIncidents();
                    updateStats();
                });
                updateConnectionStatus(true);
                
            } catch (error) {
                console.error('Error fetching incidents:', error);
                updateDebugInfo('apiStatus', `Error: ${error.message}`);
                updateConnectionStatus(false);
                
                // Use sample data for testing
                incidents = createSampleIncidents();
                
                // Apply floor detection to sample data too
                incidents.forEach(incident => {
                    if (!incident.floor) {
                        incident.floor = determineFloorFromLocation(incident.location);
                    }
                });
                
                requestAnimationFrame(() => {
                    renderIncidents();
                    updateStats();
                });
            }
        }

        function renderIncidentList() {
            const incidentListContent = document.getElementById('incidentListContent');
            
            // Filter incidents for current floor and active filters
            const filteredIncidents = incidents.filter(incident => {
                const incidentType = incident.incident_type || incident.incident_category;
                return incident.floor === currentFloor &&
                       activeFilters.has(incidentType) && 
                       incident.status !== 'completed' && 
                       incident.status !== 'deny' && 
                       incident.status !== 'DENY' && 
                       !incident.archived;
            });

            if (filteredIncidents.length === 0) {
                incidentListContent.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>No active incidents on ${FLOOR_NAMES[currentFloor]}</p>
                        <p>All clear!</p>
                    </div>
                `;
                return;
            }

            // Sort incidents by priority and time
            const sortedIncidents = filteredIncidents.sort((a, b) => {
                const esiA = a.esi_level || 1;
                const esiB = b.esi_level || 1;
                if (esiB !== esiA) return esiB - esiA;
                return new Date(b.created_at) - new Date(a.created_at);
            });

            const template = document.createElement('template');
            const listHTML = sortedIncidents.map(incident => {
                const timeAgo = getTimeAgo(new Date(incident.created_at));
                const incidentType = incident.incident_type || incident.incident_category;
                const displayType = INCIDENT_TYPES[incidentType] || incidentType;
                const priorityIcon = getPriorityIcon(incident.esi_level || 1);
                const priorityClass = getPriorityClass(incident.esi_level || 1);
                
                return `
                    <div class="incident-item" data-incident-id="${incident.id}" onclick="redirectToReport(${incident.id})">
                        <div class="incident-priority ${priorityClass}">
                            ${priorityIcon}
                        </div>
                        <div class="incident-time">
                            <i class="fas fa-clock"></i> ${timeAgo}
                        </div>
                        <div class="incident-type">
                            ${displayType}
                        </div>
                        <div class="incident-location">
                            <i class="fas fa-map-marker-alt"></i> ${incident.location}
                        </div>
                    </div>
                `;
            }).join('');

            template.innerHTML = listHTML;
            incidentListContent.replaceChildren(...template.content.childNodes);
        }

        function setupFilters() {
            const filterContainer = document.querySelector('.filter-container');
            filterContainer.addEventListener('change', (e) => {
                if (e.target.type === 'checkbox') {
                    if (e.target.checked) {
                        activeFilters.add(e.target.value);
                    } else {
                        activeFilters.delete(e.target.value);
                    }
                    clearTimeout(renderTimeout);
                    renderTimeout = setTimeout(() => {
                        renderIncidents();
                        updateStats();
                    }, 150);
                }
            });
        }

       function updateStats() {
            // Use the current floor variable, not ground floor as default
            const targetFloor = currentFloor || window.currentFloor || 'ground';
            
            console.log('Updating stats for floor:', targetFloor);
            
            // Filter for current floor
            const floorIncidents = incidents.filter(i => i.floor === targetFloor);
            
            const activeIncidents = floorIncidents.filter(i => {
                const incidentType = i.incident_type || i.incident_category;
                return i.status !== 'completed' && 
                    i.status !== 'deny' && 
                    i.status !== 'DENY' && 
                    !i.archived &&
                    activeFilters.has(incidentType);
            });
            
            const highPriorityIncidents = activeIncidents.filter(i => (i.esi_level || 1) >= 4);
            
            const activeCountEl = document.getElementById('activeCount');
            const highPriorityCountEl = document.getElementById('highPriorityCount');
            
            if (activeCountEl && activeCountEl.textContent !== activeIncidents.length.toString()) {
                activeCountEl.textContent = activeIncidents.length;
            }
            if (highPriorityCountEl && highPriorityCountEl.textContent !== highPriorityIncidents.length.toString()) {
                highPriorityCountEl.textContent = highPriorityIncidents.length;
            }
            
            console.log(`Stats updated - Active: ${activeIncidents.length}, High Priority: ${highPriorityIncidents.length}`);
        }

        function updateDebugInfo(field, value) {
            const element = document.getElementById(field);
            if (element && element.textContent !== value.toString()) {
                element.textContent = value;
            }
        }

        function updateConnectionStatus(connected) {
            const status = document.getElementById('connectionStatus');
            const newClass = connected ? 'status-indicator connected' : 'status-indicator disconnected';
            const newHTML = connected ? 
                '<i class="fas fa-circle"></i> Real-time Connected' : 
                '<i class="fas fa-exclamation-triangle"></i> Connection Failed';
            
            if (status.className !== newClass) {
                status.className = newClass;
                status.innerHTML = newHTML;
            }
        }

        function updateLastUpdated() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: false, 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            updateDebugInfo('lastUpdated', timeString);
        }

        function updateIncidentCountBadge(count) {
            const badge = document.getElementById('incidentCountBadge');
            if (badge && badge.textContent !== count.toString()) {
                badge.textContent = count;
            }
        }

        function getPriorityIcon(esiLevel) {
            const icons = {
                1: '<i class="fas fa-info"></i>',
                2: '<i class="fas fa-exclamation"></i>',
                3: '<i class="fas fa-exclamation-triangle"></i>',
                4: '<i class="fas fa-fire"></i>'
            };
            return icons[esiLevel] || icons[1];
        }
        function getIncidentTypeIcon(incidentType) {
            const iconMapping = {
                'Medical / Health': '<i class="fas fa-heartbeat"></i>',
                'Behavioral / Disciplinary': '<i class="fas fa-user-slash"></i>',
                'Safety / Security': '<i class="fas fa-shield-alt"></i>',
                'Environmental / Facility-Related Incident': '<i class="fas fa-leaf"></i>',
                'Natural Disasters & Emergency Events': '<i class="fas fa-bolt"></i>',
                'Technology / Cyber Incident': '<i class="fas fa-desktop"></i>',
                'Administrative / Policy Violations': '<i class="fas fa-file-contract"></i>',
                'Lost & Found': '<i class="fas fa-search"></i>'
            };
            return iconMapping[incidentType] || '<i class="fas fa-exclamation"></i>';
        }

        function getPriorityClass(esiLevel) {
            const classes = {
                1: 'medical-health',
                2: 'behavioral-disciplinary', 
                3: 'safety-security',
                4: 'medical-health'
            };
            return classes[esiLevel] || classes[1];
        }

        function getPriorityText(esiLevel) {
            const priorities = {
                1: '1 - Critical',
                2: '2 - High', 
                3: '3 - Medium',
                4: '4 - Low'
            };
            return priorities[esiLevel] || '4 - Low';
        }

        let tooltipTimeout = null;
        let renderTimeout = null;
        
        function showTooltip(event, incident) {
            clearTimeout(tooltipTimeout);
            
            const tooltip = document.getElementById('tooltip');
            const timeAgo = getTimeAgo(new Date(incident.created_at));
            const incidentType = incident.incident_type || incident.incident_category;
            const displayType = INCIDENT_TYPES[incidentType] || incidentType;
            const priorityText = getPriorityText(incident.esi_level || 1);
            const floorName = FLOOR_NAMES[incident.floor] || incident.floor;
            
            tooltip.innerHTML = `
                <div class="tooltip-header">${displayType}</div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Location:</span>
                    <span class="tooltip-value">${incident.location}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Floor:</span>
                    <span class="tooltip-value">${floorName}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Status:</span>
                    <span class="tooltip-value">${incident.status.toUpperCase()}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Priority:</span>
                    <span class="tooltip-value">${priorityText}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Reported:</span>
                    <span class="tooltip-value">${timeAgo}</span>
                </div>
                ${incident.description ? `<div class="tooltip-description">${incident.description}</div>` : ''}
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--gray-200); font-size: 12px; color: var(--text-secondary);">
                    <i class="fas fa-mouse-pointer"></i> Click to view details
                </div>
            `;
            
            tooltip.style.left = event.pageX + 15 + 'px';
            tooltip.style.top = event.pageY - 10 + 'px';
            tooltip.classList.add('show');
        }

        function hideTooltip() {
            tooltipTimeout = setTimeout(() => {
                tooltip.classList.remove('show');
            }, 100);
        }

        const timeAgoCache = new Map();
        
        function getTimeAgo(date) {
            const cacheKey = date.getTime();
            const now = Date.now();
            
            if (timeAgoCache.has(cacheKey)) {
                const cached = timeAgoCache.get(cacheKey);
                if (now - cached.timestamp < 30000) {
                    return cached.value;
                }
            }
            
            const diffInSeconds = Math.floor((now - date) / 1000);
            let result;
            
            if (diffInSeconds < 60) result = 'Just now';
            else if (diffInSeconds < 3600) result = `${Math.floor(diffInSeconds / 60)}m ago`;
            else if (diffInSeconds < 86400) result = `${Math.floor(diffInSeconds / 3600)}h ago`;
            else result = `${Math.floor(diffInSeconds / 86400)}d ago`;
            
            timeAgoCache.set(cacheKey, { value: result, timestamp: now });
            return result;
        }

        function cleanup() {
            if (updateInterval) clearInterval(updateInterval);
            if (timeInterval) clearInterval(timeInterval);
            if (renderTimeout) clearTimeout(renderTimeout);
            if (tooltipTimeout) clearTimeout(tooltipTimeout);
            
            timeAgoCache.clear();
            clusterData.clear();
        }

        // Initialize when page loads
       document.addEventListener('DOMContentLoaded', () => {
            try {
                // Small delay to ensure all elements are rendered
                setTimeout(() => {
                    initializeMap();
                }, 10);
            } catch (error) {
                console.error('Failed to initialize dashboard:', error);
            }
        });

        window.addEventListener('beforeunload', cleanup);
        
        // Make functions globally available
        window.redirectToReport = redirectToReport;

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideTooltip();
                selectedIncident = null;
                const selectedElements = document.querySelectorAll('.selected');
                selectedElements.forEach(el => el.classList.remove('selected'));
            }
        }, { passive: true });

        console.log('Multi-Floor Incident Dashboard with Clustering loaded successfully!');
    </script>
</body>
</html>
</x-admin-layout>