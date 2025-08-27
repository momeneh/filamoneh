<?php
namespace App\Filament\Resources;

use App\Models\User;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

abstract class BaseResource extends Resource
{
    public static function canViewAny(): bool
    {
        // info(static::getPermissionName('viewAny'));
        return auth()->user()->hasPermission(static::getPermissionName('viewAny'));
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->hasPermission(static::getPermissionName('view'));
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission(static::getPermissionName('update'));
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission(static::getPermissionName('delete'));
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission(static::getPermissionName('create'));
    }

    protected static function getPermissionName(string $action): string
    {
        // مثلاً: post.viewAny یا user.delete
        $modelName = class_basename(static::$model);
        return strtolower($modelName) . '.' . $action;
    }

    public static function convertNumbers($string, $toLatin = false)
    {
        $farsi_array = array("۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹");
        $english_array = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        if (!$toLatin) {
            return str_replace($english_array, $farsi_array, $string);
        }
        return str_replace($farsi_array, $english_array, $string);
    }
}
