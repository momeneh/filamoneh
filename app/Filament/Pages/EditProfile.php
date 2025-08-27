<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class EditProfile extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    // protected static ?string $navigationLabel = 'Edit Profile';
    protected static bool $shouldRegisterNavigation = false;


    protected static string $view = 'filament.pages.edit-profile';

    public ?array $data = [];

    public  function getTitle(): string
    {
        return __( trans('Edit Profile'));
    }
    public function mount(): void
    {
        $user = auth()->user();
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'password' => null,
            'password_confirmation' => null,
            'current_password' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label(__('filament.fields.name'))
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->label(__('filament.fields.email'))
                    ->maxLength(255),
                TextInput::make('mobile')
                    ->label(__('filament.fields.mobile'))
                    // ->numeric()
                    ->placeholder('09xxxxxxxxx')
                    ->helperText('شماره موبایل باید با 0 شروع شود و فقط شامل ارقام باشد')
                    ->rule('regex:/^0\\d{10}$/')
                    ->maxLength(11),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->label(__('filament.fields.password'))
                    ->confirmed()
                    ->autocomplete('new-password')
                    ->dehydrated(fn ($state) => filled($state)),
                TextInput::make('password_confirmation')
                    ->password()
                    ->dehydrated(false)
                    ->label(__('filament.fields.password_confirmation'))
                    ->requiredWith('password')
                    ->maxLength(255),
                TextInput::make('current_password')
                    ->password()
                    ->dehydrated(false)
                    ->label(__('filament.fields.current_password'))
                    ->requiredWith('password')
                    ->rule('current_password')
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        auth()->user()->update($data);

        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }
} 