<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\EditProfile;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;

class AdminPanelProvider extends PanelProvider
{
    public function boot():void{
        DeleteAction::configureUsing(function ($action) {
            $action->using(static function (Model $record) {
                try {
                    return $record->delete();
                } catch (\Exception $e) {
                    $entity='';

                    if(isset($e->errorInfo) && is_array($e->errorInfo) && isset($e->errorInfo[1]) && $e->errorInfo[1] ==1451){
        
                        preg_match('/\.`.*`,/', $e->errorInfo[2], $match);
                        $entity = (isset($match[0])) ? str_replace(['`',',', '.'], '', $match[0]) : '';

                        Notification::make()
                        ->title(__('Delete is not possible').__(',the record is used in other object(s)')."(".__("filament.tables.$entity").')')
                        ->danger()
                        ->send();
                        
                    }
                    else{
                        Notification::make()
                        ->title( __('Delete is not possible')."\n". $entity)
                        ->danger()
                        ->send();
                    }
                    
                    return false;
                }
            });
        });


    }
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->brandName('فیلامونه') // <-- Your Farsi project name here
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                EditProfile::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->databaseNotifications()
            ->userMenuItems([
                MenuItem::make()
                   ->label(__('Edit Profile'))
                   ->url(fn (): string => EditProfile::getUrl())
                   ->icon('heroicon-o-pencil-square')
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
