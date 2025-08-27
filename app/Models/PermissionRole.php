<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionRole extends Model
{
    protected $fillable = ['role_id','permission_id'];
    protected $table = 'permission_role';

    public function permission(): BelongsTo
    {
       return $this->belongsTo(Permission::class);
    }

    public function role(): BelongsTo{
        return $this->belongsTo(Role::class);
    }
}
