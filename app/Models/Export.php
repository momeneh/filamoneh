<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;

use App\Observers\ExportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
 
#[ObservedBy([ExportObserver::class])]
class Export extends Model
{
    use Prunable;
 
    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subHour());
    }

    protected function pruning(): void
    {
    //    info('pruning');
    }
}
