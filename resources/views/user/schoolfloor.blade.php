@extends('layouts.user')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Map Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        🏫 School Floor Layout
                    </h1>
                </div>
            </div>

            <!-- Floor Selector -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                <div class="flex flex-wrap gap-2">
                    <button class="floor-btn active px-4 py-2 border-2 border-blue-500 bg-blue-500 text-white rounded-lg font-medium transition-all duration-300 hover:bg-blue-600" 
                            data-floor="ground">
                        Ground Floor
                    </button>
                    <button class="floor-btn px-4 py-2 border-2 border-gray-200 bg-white text-gray-700 rounded-lg font-medium transition-all duration-300 hover:border-blue-500 hover:bg-blue-50" 
                            data-floor="second">
                        2nd Floor
                    </button>
                    <button class="floor-btn px-4 py-2 border-2 border-gray-200 bg-white text-gray-700 rounded-lg font-medium transition-all duration-300 hover:border-blue-500 hover:bg-blue-50" 
                            data-floor="third">
                        3rd Floor
                    </button>
                    <button class="floor-btn px-4 py-2 border-2 border-gray-200 bg-white text-gray-700 rounded-lg font-medium transition-all duration-300 hover:border-blue-500 hover:bg-blue-50" 
                            data-floor="fourth">
                        4th Floor
                    </button>
                     <button class="floor-btn px-4 py-2 border-2 border-gray-200 bg-white text-gray-700 rounded-lg font-medium transition-all duration-300 hover:border-blue-500 hover:bg-blue-50" 
                            data-floor="fifth">
                        5th Floor
                    </button>
                </div>
            </div>

            <!-- Map View -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden relative">
                <div class="relative w-full h-96 md:h-[600px] overflow-hidden cursor-grab active:cursor-grabbing" id="svg-container">
                    <!-- Loading State -->
                    <div id="map-loading" class="absolute inset-0 flex items-center justify-center bg-gray-100">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                            <p class="text-gray-600">Loading floor plan...</p>
                        </div>
                    </div>

                    <!-- SVG Floor Plan will be injected here -->
                    <div id="floor-plan-svg" class="w-full h-full hidden">
                        <!-- Dynamic SVG content -->
                    </div>

                    <!-- Map Controls -->
                    <div class="absolute top-4 right-4 bg-white rounded-lg shadow-lg p-2 flex flex-col gap-2">
                        <button id="zoom-in" class="p-2 hover:bg-gray-100 rounded transition-colors" title="Zoom In">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>
                        <button id="zoom-out" class="p-2 hover:bg-gray-100 rounded transition-colors" title="Zoom Out">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <button id="reset-view" class="p-2 hover:bg-gray-100 rounded transition-colors" title="Reset View">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
            </div>

            
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        class SchoolFloorMap {
            constructor() {
                this.currentFloor = 'ground';
                this.currentZoom = 1;
                this.currentPan = { x: 0, y: 0 };
                this.incidents = [];
                this.isDragging = false;
                this.lastMousePos = { x: 0, y: 0 };
                
                this.init();
            }

            init() {
                this.bindEvents();
                this.loadFloorPlan(this.currentFloor);
                
                
            }

            bindEvents() {
                // Floor selector buttons
                document.querySelectorAll('.floor-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        this.switchFloor(e.target.dataset.floor);
                    });
                });

                // Map controls
                document.getElementById('zoom-in').addEventListener('click', () => this.zoomIn());
                document.getElementById('zoom-out').addEventListener('click', () => this.zoomOut());
                document.getElementById('reset-view').addEventListener('click', () => this.resetView());

                // Mouse events for panning
                const container = document.getElementById('svg-container');
                container.addEventListener('mousedown', (e) => this.startDrag(e));
                container.addEventListener('mousemove', (e) => this.drag(e));
                container.addEventListener('mouseup', () => this.endDrag());
                container.addEventListener('mouseleave', () => this.endDrag());

                // Prevent context menu
                container.addEventListener('contextmenu', (e) => e.preventDefault());
            }

            async switchFloor(floor) {
                // Update active button
                document.querySelectorAll('.floor-btn').forEach(btn => {
                    btn.classList.remove('active', 'bg-blue-500', 'text-white', 'border-blue-500');
                    btn.classList.add('bg-white', 'text-gray-700', 'border-gray-200');
                });
                
                document.querySelector(`[data-floor="${floor}"]`).classList.add('active', 'bg-blue-500', 'text-white', 'border-blue-500');
                document.querySelector(`[data-floor="${floor}"]`).classList.remove('bg-white', 'text-gray-700', 'border-gray-200');

                this.currentFloor = floor;
                this.resetView();
                await this.loadFloorPlan(floor);
            }

            async loadFloorPlan(floor) {
                const loading = document.getElementById('map-loading');
                const svgContainer = document.getElementById('floor-plan-svg');
                
                loading.classList.remove('hidden');
                svgContainer.classList.add('hidden');

                try {
                    // Load SVG floor plan
                    const response = await fetch(`/api/floorplan/${floor}`);
                    const svgContent = await response.text();
                    
                    svgContainer.innerHTML = svgContent;
                    
                    // Add incident markers
                    await this.addIncidentMarkers();
                    
                    loading.classList.add('hidden');
                    svgContainer.classList.remove('hidden');
                    
                } catch (error) {
                    console.error('Failed to load floor plan:', error);
                    svgContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Failed to load floor plan</div>';
                    loading.classList.add('hidden');
                    svgContainer.classList.remove('hidden');
                }
            }


            async addIncidentMarkers() {
                const svg = document.querySelector('#floor-plan-svg svg');
                if (!svg) return;

                const floorIncidents = this.incidents.filter(incident => 
                    incident.floor_name === this.currentFloor && incident.svg_x && incident.svg_y
                );

            }

            

            // Map interaction methods
            startDrag(e) {
                this.isDragging = true;
                this.lastMousePos = { x: e.clientX, y: e.clientY };
                document.getElementById('svg-container').style.cursor = 'grabbing';
            }

            drag(e) {
                if (!this.isDragging) return;
                
                const deltaX = e.clientX - this.lastMousePos.x;
                const deltaY = e.clientY - this.lastMousePos.y;
                
                this.currentPan.x += deltaX;
                this.currentPan.y += deltaY;
                
                this.updateTransform();
                
                this.lastMousePos = { x: e.clientX, y: e.clientY };
            }

            endDrag() {
                this.isDragging = false;
                document.getElementById('svg-container').style.cursor = 'grab';
            }

            zoomIn() {
                this.currentZoom = Math.min(this.currentZoom * 1.2, 5);
                this.updateTransform();
            }

            zoomOut() {
                this.currentZoom = Math.max(this.currentZoom / 1.2, 0.5);
                this.updateTransform();
            }

            resetView() {
                this.currentZoom = 1;
                this.currentPan = { x: 0, y: 0 };
                this.updateTransform();
            }

            updateTransform() {
                const svg = document.querySelector('#floor-plan-svg svg');
                if (svg) {
                    svg.style.transform = `translate(${this.currentPan.x}px, ${this.currentPan.y}px) scale(${this.currentZoom})`;
                }
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.schoolFloorMap = new SchoolFloorMap();
        });
    </script>

    <!-- Additional styles -->
    <style>
        
        .drop-shadow-lg {
            filter: drop-shadow(0 10px 8px rgb(0 0 0 / 0.04)) drop-shadow(0 4px 3px rgb(0 0 0 / 0.1));
        }
        
        #svg-container {
            background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .incident-marker {
            transition: all 0.2s ease;
        }
    </style>
@endsection