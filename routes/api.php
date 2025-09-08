<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\IncidentMapController;
use App\Models\Report;

// Remove the prefix since Laravel already adds /api/
Route::middleware(['throttle:chatbot'])->group(function () {
    Route::post('/chatbot/report', [ChatbotController::class, 'report'])->name('api.chatbot.report');
    Route::get('/chatbot/history', [ChatbotController::class, 'history'])->name('api.chatbot.history');
    Route::get('/chatbot/incident-data', [ChatbotController::class, 'getIncidentData'])->name('api.chatbot.incident-data');
    Route::post('/chatbot/upload-image', [ChatbotController::class, 'uploadImage'])->name('api.chatbot.upload-image');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports', [ReportController::class, 'apiReports']);
    Route::get('/reports/{report}', [ReportController::class, 'apiReport']);
});

Route::get('/incidents/active', function() {
    try {
        $reports = Report::where('status', '!=', 'completed')
                        ->where('archived', 0)
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        // Transform each report to include the category
        $transformedReports = $reports->map(function($report) {
            $reportArray = $report->toArray();
            $reportArray['incident_category'] = getIncidentCategory($report->incident_type);
            return $reportArray;
        });
        
        return $transformedReports;
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

function getIncidentCategory(string $incidentType): string
{
    $categories = [
        'medical' => [
            'medical_emergency', 'injury_serious', 'injury_moderate', 'injury_minor',
            'illness_contagious', 'illness_general', 'mental_health_crisis', 'sports_injury'
        ],
        'safety' => [
            'fire_emergency', 'chemical_hazard', 'structural_hazard', 'environmental_hazard',
            'earthquake', 'severe_weather', 'food_safety', 'equipment_failure'
        ],
        'security' => [
            'theft_major', 'theft_minor', 'missing_property', 'weapons', 'drugs_alcohol',
            'prohibited_items', 'cyber_security', 'visitor_incident', 'evacuation'
        ],
        'behavioral' => [
            'violence_serious', 'violence_moderate', 'bullying_physical', 'bullying_verbal',
            'bullying_cyber', 'sexual_misconduct', 'academic_misconduct'
        ],
        'operational' => [
            'system_outage', 'vehicle_accident', 'transport_issue', 'cafeteria_incident'
        ]
    ];
    
    foreach ($categories as $category => $types) {
        if (in_array($incidentType, $types)) {
            // Map to JavaScript-friendly names
            switch($category) {
                case 'security': return 'safety';
                case 'operational': return 'technology';
                default: return $category;
            }
        }
    }
    
    return 'administrative'; // Default fallback
}