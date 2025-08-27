<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionRoleResource\Pages;
use App\Filament\Resources\PermissionRoleResource\RelationManagers;
use App\Models\Permission;
use App\Models\PermissionRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
class PermissionRoleResource extends Resource
{
    protected static ?string $model = PermissionRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup() : string {
        return  __('Users');
    }
    public static function getModelLabel():string
    {
        return  __('PermissionRole');
    }
    public static function getPluralModelLabel():string
    {
        return  __('PermissionRoles');
    }
    
    public static function getNavigationLabel():string
    {
        return  __('PermissionRole');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('role_id')
                        ->relationship('role', 'title')
                        ->required()
                        ->label(__('role'))
                        ->createOptionForm([
                            Forms\Components\TextInput::make(__('role'))
                                ->required()
                                ->maxLength(255),
                        ]),
                    Forms\Components\Select::make('permission_id')
                        ->relationship('permission','name' )
                        ->getOptionLabelFromRecordUsing(function (Model $record) {
                            $ar = explode('.',$record->name);
                            return $record->name.' -- '.__(\Illuminate\Support\Str::studly( $ar[0])) . ' -  '.__('filament.fields.'.$ar[1]) ;
                        } )
                        ->searchPrompt(__('search is done by the name of the permission'))

                        ->optionsLimit(20)
                        ->searchable(['name'])
                        ->required()
                        ->label(__('permission'))
                        ,
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('filament.fields.created_at'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('role.title')
                    ->label(__('role'))
                    ->searchable()
                    ,
                Tables\Columns\TextColumn::make('permission.name')
                     ->label(__('filament.fields.title'))
                    ->searchable()
                    ,
                Tables\Columns\TextColumn::make('title')
                    ->label(__('permission'))
                    ->state(function ( $record) {
                        $ar = explode('.',$record->permission->name);
                        return __(\Illuminate\Support\Str::studly( $ar[0])) . ' -  '.__('filament.fields.'.$ar[1]) ;
                    })
                    
                ,
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
            'index' => Pages\ListPermissionRoles::route('/'),
            'create' => Pages\CreatePermissionRole::route('/create'),
            'edit' => Pages\EditPermissionRole::route('/{record}/edit'),
        ];
    }
}
