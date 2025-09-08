<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class GeminiAnalyticsService
{
    public function analyzeReports(array $data)
    {
        $currentMonth = Carbon::now()->format('F Y'); 

        $prompt = "
        You are an AI data analyst for a school reporting system.
        Analyze the following data for {$currentMonth}: " . json_encode($data) . ".

        Important rules for interpretation:
        - Use 'Risk Level' instead of 'ESI Level'.
        - Risk Levels: 1 = Critical, 2 = High, 3 = Medium, 4 = Low.
        - Growth rate:
        • Positive (rise) = BAD (incidents increasing).
        • Negative (decline) = GOOD (incidents decreasing).
        - Status:
        • 'completed' = GOOD (report resolved quickly).
        • Any other status = needs improvement.

        Provide insights in this strict JSON structure:
        {
            \"trend_summary\": \"string - summary of major trends and % changes, highlight if good/bad\",
            \"predictions\": \"string - forecast for next month (incident types, expected counts)\",
            \"recommendations\": [\"string - actionable advice 1\", \"string - advice 2\", \"string - advice 3\"]
        }
        Only return JSON. No explanations, no extra text.
        ";



        $url = env('GEMINI_BASE_URL') 
             . "/models/" . env('GEMINI_MODEL') 
             . ":generateContent?key=" . env('GEMINI_API_KEY');

        $response = Http::post($url, [
            "contents" => [[
                "role" => "user",
                "parts" => [["text" => $prompt]]
            ]]
        ]);

        $result = $response->json();

        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $output = $result['candidates'][0]['content']['parts'][0]['text'];

            // Extract JSON safely
            preg_match('/\{[\s\S]*\}/', $output, $matches);
            if (!empty($matches)) {
                $decoded = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }

            return ["error" => "Invalid JSON", "raw" => $output];
        }

        return ["error" => "No AI analysis available", "debug" => $result];
    }
}
