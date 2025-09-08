<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Existing boot code...
        
        // Ensure incident images directory exists
        $incidentImagesPath = storage_path('app/public/incident_images');
        if (!is_dir($incidentImagesPath)) {
            mkdir($incidentImagesPath, 0755, true);
            file_put_contents($incidentImagesPath . '/.gitignore', "*\n!.gitignore\n");
        }
    }
}
