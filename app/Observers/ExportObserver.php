<?php

namespace App\Observers;
use App\Models\Export;
use Illuminate\Support\Facades\Storage;

class ExportObserver
{
     /**
     * Static property to store old file paths temporarily
     */
    private static $oldFilePaths = [];

    
    /**
     * Handle the Export "deleted" event.
     */
    public function deleted(Export $export): void
    {
        if ($export->id && Storage::disk('local')->exists('filament_exports\\'.$export->id)) {
            Storage::disk('local')->deleteDirectory('filament_exports\\'.$export->id);
        }else info('could not find folder'.'filament_exports\\'.$export->id.'for remove the exported files');
    }

    
    
}
