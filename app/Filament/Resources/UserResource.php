<?php

namespace App\Filament\Resources;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;


class UserResource extends BaseResource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission(static::getPermissionName('delete')) && $record->id !== auth()->user()->id;
    }
    public static function getNavigationGroup() : string {
        return  __('Users');
    }
    public static function getModelLabel():string
    {
        return  __('User');
    }
    public static function getPluralModelLabel():string
    {
        return  __('Users');
    }
    
    public static function getNavigationLabel():string
    {
        return  __('Users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')
                ->columns(1)
                ->schema([
                    Section::make(__('filament.fields.usersInfo'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label(__('filament.fields.name')),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->label(__('filament.fields.email'))
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('mobile')
                            ->label(__('filament.fields.mobile'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->visible(fn (Page $livewire): bool => $livewire instanceof CreateUser)
                    ]),
                    Repeater::make('roleUser')
                        ->relationship()
                        ->minItems(1)
                        ->label(__('filament.fields.roles'))
                        ->addActionAlignment(Alignment::End)
                        ->simple(
                            Select::make('role')
                                ->relationship('role','title')
                                ->options(Role::all()->pluck('title','id'))
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('title')
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->required(),
                        )
                        ->columns(1)
                ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
           ->headerActions([
                \Filament\Tables\Actions\ExportAction::make()
                ->exporter(UserExporter::class)
                ->formats([
                    \Filament\Actions\Exports\Enums\ExportFormat::Xlsx,
                ])
                ->outlined()
                ->icon('heroicon-s-bars-arrow-down')
                ->iconButton()
                ->size(\Filament\Support\Enums\ActionSize::Large)
           ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('mobile')
                    ->label(__('filament.fields.mobile'))
                    ->state(function ( $record) {
                        return self::convertNumbers($record->mobile)  ;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.fields.created_at'))
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->jalaliDateTime()
                    ->label(__('filament.fields.updated_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    

}
