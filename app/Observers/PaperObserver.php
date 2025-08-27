<?php

namespace App\Observers;

use App\Models\Paper;
use Illuminate\Support\Facades\Storage;

class PaperObserver
{
    /**
     * Static property to store old file paths temporarily
     */
    private static $oldFilePaths = [];

    /**
     * Handle the Paper "created" event.
     */
    public function created(Paper $paper): void
    {
        // Set the insert_user_id to the current authenticated user
        if (auth()->check()) {
            $paper->insert_user_id = auth()->id();
            $paper->saveQuietly(); // Save without triggering events
        }
    }

    /**
     * Handle the Paper "updating" event.
     */
    public function updating(Paper $paper): void
    {
        // Store old file paths before they get updated
        $this->storeOldFilePaths($paper);
    }

    /**
     * Handle the Paper "updated" event.
     */
    public function updated(Paper $paper): void
    {
        // Set the edit_user_id to the current authenticated user
        if (auth()->check()) {
            $paper->edit_user_id = auth()->id();
            $paper->saveQuietly(); // Save without triggering events
        }

        // Delete old files that were replaced
        $this->deleteOldFiles($paper);
        
        // Clear the stored old file paths
        $this->clearOldFilePaths($paper);
    }

    /**
     * Handle the Paper "deleted" event.
     */
    public function deleted(Paper $paper): void
    {
        $this->deleteAssociatedFiles($paper);
    }

    /**
     * Handle the Paper "restored" event.
     */
    public function restored(Paper $paper): void
    {
        //
    }

    /**
     * Handle the Paper "force deleted" event.
     */
    public function forceDeleted(Paper $paper): void
    {
        $this->deleteAssociatedFiles($paper);
    }

    /**
     * Store old file paths before update
     */
    private function storeOldFilePaths(Paper $paper): void
    {
        $fileFields = [
            'paper_file',
            'paper_word_file', 
            'image_path1',
            'image_path2'
        ];

        $paperId = $paper->id;
        self::$oldFilePaths[$paperId] = [];

        foreach ($fileFields as $field) {
            $oldValue = $paper->getRawOriginal($field);
            if ($oldValue) {
                // Store the old file path in static property
                self::$oldFilePaths[$paperId][$field] = $oldValue;
            }
        }
    }

    /**
     * Delete old files that were replaced during update
     */
    private function deleteOldFiles(Paper $paper): void
    {
        $paperId = $paper->id;
        $oldPaths = self::$oldFilePaths[$paperId] ?? [];

        $fileFields = [
            'paper_file',
            'paper_word_file', 
            'image_path1',
            'image_path2'
        ];

        foreach ($fileFields as $field) {
            $oldValue = $oldPaths[$field] ?? null;
            $newValue = $paper->$field;
            
            // info("Field: {$field}");
            // info("Old value: " . ($oldValue ?? 'null'));
            // info("New value: " . ($newValue ?? 'null'));
            
            // If there was an old file and it's different from the new one, delete it
            if ($oldValue  && $oldValue !== $newValue) {
                // Remove the old file from storage
                if (Storage::disk('public')->exists($oldValue)) {
                    Storage::disk('public')->delete($oldValue);
                    // info("Deleted old file: {$oldValue}");
                } else {
                    info("File not found in storage: {$oldValue}");
                }
            }
        }
    }

    /**
     * Clear stored old file paths
     */
    private function clearOldFilePaths(Paper $paper): void
    {
        $paperId = $paper->id;
        unset(self::$oldFilePaths[$paperId]);
    }

    /**
     * Delete all files associated with the paper
     */
    private function deleteAssociatedFiles(Paper $paper): void
    {
        $fileFields = [
            'paper_file',
            'paper_word_file', 
            'image_path1',
            'image_path2'
        ];

        foreach ($fileFields as $field) {
            if ($paper->$field && Storage::disk('public')->exists($paper->$field)) {
                Storage::disk('public')->delete($paper->$field);
            }
        }
    }
}
