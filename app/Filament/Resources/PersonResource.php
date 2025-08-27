<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PersonExporter;
use App\Filament\Resources\PersonResource\Pages;
use App\Filament\Resources\PersonResource\RelationManagers;
use App\Models\Center;
use App\Models\City;
use App\Models\Person;
use App\Models\Province;
use App\Rules\Mobile;
use App\Rules\NationalCode;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Exports\PaperExporter;
use Illuminate\Validation\Rules\Exists;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationGroup() : string {
        return  __('People');
    }
    public static function getModelLabel():string
    {
        return  __('Person');
    }
    public static function getPluralModelLabel():string
    {
        return  __('People');
    }
    
    public static function getNavigationLabel():string
    {
        return  __('People');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Basic Information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.fields.name'))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('family')
                            ->label(__('filament.fields.family'))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('gender')
                            ->label(__('filament.fields.gender'))
                            ->required()
                            ->in([1,2])
                            ->options([1=>__('filament.fields.woman'),2=>__('filament.fields.man')]),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(table:'people',column:'email',ignoreRecord: true)
                            ->label(__('filament.fields.email'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('national_code')
                            ->label(__('filament.fields.national_code'))
                            ->required()
                            ->unique(table:'people',column:'national_code',ignoreRecord: true)
                            ->rules([new NationalCode()])
                            ->maxLength(255),

                        Forms\Components\TextInput::make('shenasname')
                            ->label(__('filament.fields.shenasname'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('passport_number')
                            ->label(__('filament.fields.passport_number'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('father_name')
                            ->label(__('filament.fields.father_name'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('birth_year')
                            ->label(__('filament.fields.birth_year'))
                            ->minValue(1300)
                            ->maxValue(1500)
                            ->numeric(),

                        Forms\Components\FileUpload::make('photo')
                            ->label(__('filament.fields.photo'))
                            ->image()
                            ->imagePreviewHeight('250')
                            ->maxSize(5120) // 5MB
                            ->downloadable()
                            ->openable(),
                       
                    ])->columns(2),
                Section::make(__('Contact Information'))
                    ->schema([
                        Forms\Components\TextInput::make('website')
                            ->label(__('filament.fields.website'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('mobile')
                            ->label(__('filament.fields.mobile'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('mobile')
                            ->label(__('filament.fields.mobile'))
                            ->tel()
                            ->rules([new Mobile()])
                            ->maxLength(255),

                        Forms\Components\TextInput::make('fax')
                            ->label(__('filament.fields.fax'))
                            ->maxLength(255),

                        Forms\Components\Textarea::make('addr')
                            ->label(__('filament.fields.addr'))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('postalcode')
                            ->label(__('filament.fields.postalcode'))
                            ->maxLength(255),

                        Forms\Components\Select::make('country_id')
                            ->label(__('Country'))
                            ->relationship('country', 'title')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->exists('countries','id')
                            ->afterStateUpdated(function (Set $set) {
                                 $set('province_id', null);
                                 $set('city_id', null);
                            }),

                        Forms\Components\Select::make('province_id')
                            ->label(__('filament.fields.province'))
                            ->options(fn (Get $get) => Province::where('country_id', $get('country_id'))->pluck('title', 'id'))
                            ->exists(table:'provinces',column:'id',modifyRuleUsing: fn (Exists $rule,Get $get) => $rule->where('country_id', $get('country_id') ))//validation
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                            ->disabled(fn (Get $get) => $get('country_id') != '105'),

                        Forms\Components\Select::make('city_id')
                            ->label(__('filament.fields.city'))
                            ->options(fn (Get $get) => City::where('province_id', $get('province_id'))->pluck('title', 'id'))
                            ->disabled(fn (Get $get) => $get('country_id') != '105')
                            ->exists(table:'cities',column:'id',modifyRuleUsing :fn (Exists $rule,Get $get) => $rule->where('province_id', $get('province_id') ))//validation
                            ->reactive()
                            ->searchable(),

                    ])
                ->columns(2),

                Section::make()
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('PersonEducation')
                        ->relationship()
                        ->label(__('Person Education'))
                        ->schema([
                            Forms\Components\Select::make('grade_id')
                            ->label(__('Grade'))
                            ->relationship('grade', 'title')
                            ->searchable()
                            ->preload()
                            ->exists('grades','id'),

                            Forms\Components\Select::make('field_id')
                            ->label(__('Field'))
                            ->relationship('field', 'title')
                            ->searchable()
                            ->preload()
                            ->exists('fields','id'),

                            Forms\Components\TextInput::make('start_year')
                            ->label(__('filament.fields.start_year'))
                            ->numeric()
                            ->minValue(1300)
                            ->maxValue(1500),

                            Forms\Components\TextInput::make('end_year')
                            ->label(__('filament.fields.end_year'))
                            ->numeric()
                            ->minValue(1300)
                            ->maxValue(1500),

                            Forms\Components\Select::make('center_id')
                            ->label(__('Center'))
                            ->relationship(name:'center',titleAttribute:'path',modifyQueryUsing: fn (Builder $query) => $query->whereNotNull('path')->where('path','<>',''))
                            ->searchable()
                            ->preload()
                            ->exists('centers','id')
                            ->disabled(fn (Get $get): bool => $get('not_in_list')),

                            Forms\Components\Checkbox::make('not_in_list')
                            ->label(__('not found in Centers'))
                            ->live() 
                            ->afterStateUpdated(function (Set $set) {
                                $set('center_id', null);
                                $set('other_center', null);
                            }),

                            Forms\Components\TextInput::make('other_center') 
                            ->label(__('others'))
                            ->visible(fn (Get $get): bool => $get('not_in_list'))

                        ])
                        ->columns(4),
                        ]),

                        Section::make()
                        ->schema([
                            \Filament\Forms\Components\Repeater::make('PersonExperience')
                            ->relationship()
                            ->label(__('Person Experience'))
                            ->schema([
                                Forms\Components\TextInput::make('job_title') 
                                ->label(__('filament.fields.title'))
                                ->helperText(__('job title or position in company'))
                                ->required(),

                                Forms\Components\Select::make('center_id')
                                ->label(__('Center'))
                                ->relationship(name:'center',titleAttribute:'path',modifyQueryUsing: fn (Builder $query) => $query->whereNotNull('path')->where('path','<>',''))
                                ->searchable()
                                ->preload()
                                ->exists('centers','id'),

                                Forms\Components\TextInput::make('job_start_year')
                                ->label(__('filament.fields.start_year'))
                                ->numeric()
                                ->minValue(1300)
                                ->maxValue(1500),

                                Forms\Components\TextInput::make('job_end_date')
                                ->label(__('filament.fields.end_year'))
                                ->numeric()
                                ->minValue(1300)
                                ->maxValue(1500),

                            ])->columns(2)
                        ])

             
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('goToPage')
                    ->label('برو به صفحه')
                    ->color('success')
                    ->outlined()
                    ->icon('heroicon-o-arrow-right')
                    ->form([
                        Forms\Components\TextInput::make('page')
                            ->label('شماره صفحه')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(function (\Filament\Tables\Contracts\HasTable $livewire) {
                                $perPage = (int) ($livewire->tableRecordsPerPage ?? 25);
                                if ($perPage <= 0) {
                                    $perPage = 25;
                                }
                                $total = Person::query()->count();
                                return max(1, (int) ceil($total / $perPage));
                            })
                            ->helperText('شماره صفحه مورد نظر را وارد کنید'),
                    ])
                    ->action(function (array $data, \Filament\Tables\Contracts\HasTable $livewire) {
                        $perPage = (int) ($livewire->tableRecordsPerPage ?? 25);
                        if ($perPage <= 0) {
                            $perPage = 25;
                        }
                        $total = Person::query()->count();
                        $lastPage = max(1, (int) ceil($total / $perPage));
                        $page = (int) ($data['page'] ?? 1);
                        $page = max(1, min($page, $lastPage));

                        return redirect()->to(url('admin/people') . '?page=' . $page);
                    }),
                \Filament\Tables\Actions\ExportAction::make()
                ->exporter(PersonExporter::class)
                ->formats([
                    \Filament\Actions\Exports\Enums\ExportFormat::Xlsx,
                ])
                ->outlined()
                ->icon('heroicon-s-bars-arrow-down')
                ->iconButton()
                ->size(\Filament\Support\Enums\ActionSize::Large)
            ])
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.fields.created_at'))
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament.fields.updated_at'))
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ImageColumn::make('photo')
                ->label(__('filament.fields.photo'))
                ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('family')
                    ->label(__('filament.fields.family'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.fields.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('national_code')
                    ->label(__('filament.fields.national_code'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shenasname')
                    ->searchable()
                    ->label(__('filament.fields.shenasname'))
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('passport_number')
                    ->searchable()
                    ->label(__('filament.fields.passport_number'))
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('father_name')
                     ->label(__('filament.fields.father_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_year')
                    ->searchable()
                    ->label(__('filament.fields.birth_year'))
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('mobile')
                    ->searchable()
                    ->label(__('filament.fields.mobile'))
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('tel')
                    ->searchable()
                    ->label(__('filament.fields.tel'))
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('fax')
                    ->searchable()
                    ->label(__('filament.fields.fax'))
                    ->toggleable(isToggledHiddenByDefault:true)
               
               ,
            ])
            ->filters([
               
                Filter::make('field_id')
                ->form([
                    Forms\Components\Select::make('field_id')
                    ->label(__('Field')) 
                    ->relationship('PersonEducation.field', 'title')
                    ->searchable()
                    ->preload()
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['field_id'],
                            fn (Builder $query, $id): Builder => $query->whereHas('PersonEducation', function($q)use($id){
                                $q->where('field_id',$id);
                            }),
                        );
                }) ,

                Filter::make('center_id')
                ->form([
                    Forms\Components\Select::make('center_id')
                    ->label(__('Center')) 
                    ->relationship(name:'PersonEducation.center',titleAttribute:'path',modifyQueryUsing: fn (Builder $query) => $query->whereNotNull('path')->where('path','<>',''))
                    ->searchable()
                    ->preload()
                ])
                ->query(function (Builder $query, array $data): Builder {

                    return $query
                        ->when(
                            $data['center_id'],
                            fn (Builder $query, $id): Builder => $query->whereHas('PersonEducation', function($q)use($id){
                                $q->where('center_id',$id);
                            })
                            ->orWhereHas('PersonExperience', function($q)use($id){
                                $q->where('center_id',$id);
                            }),
                        );

                }) ,

                Filter::make('gender')
                ->form([
                    Forms\Components\Select::make('gender')
                    ->label(__('filament.fields.gender'))
                    ->options([1=>__('filament.fields.woman'),2=>__('filament.fields.man')]),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['gender'],
                            fn (Builder $query, $id): Builder => $query->where('gender', $data['gender'])
                        );
                }) 
            ],layout: Tables\Enums\FiltersLayout::AboveContentCollapsible 
            )
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ], position: \Filament\Tables\Enums\ActionsPosition::BeforeCells)
            ->recordUrl(null) // This line disables row clicks
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([10, 25, 50, 100])
            ->paginatedWhileReordering()
            ->persistFiltersInSession()
            ->persistSortInSession();
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
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
