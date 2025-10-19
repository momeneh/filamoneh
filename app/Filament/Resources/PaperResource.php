<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PaperExporter;
use App\Filament\Resources\PaperResource\Pages;
use App\Models\Paper;
use App\Models\Tag;
use App\Services\OpenAiService;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Morilog\Jalali\Jalalian;

class PaperResource extends BaseResource
{
    protected static ?string $model = Paper::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup() : string {
        return  __('Papers');
    }
    public static function getModelLabel():string
    {
        return  __('Paper');
    }
    public static function getPluralModelLabel():string
    {
        return  __('Papers');
    }
    
    public static function getNavigationLabel():string
    {
        return  __('Papers');
    }

    public static function form(Form $form): Form
    {
        // info(Paper::select('tag')->whereNotNull('tag')->pluck('tag')->toArray());
        return $form
            ->schema([
                Section::make('')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('created_by')
                            ->label(__('Created By'))
                            ->content(fn ($record) => $record?->inserter?->fullName ),
                        
                        \Filament\Forms\Components\Placeholder::make('created_at')
                            ->label(__('Created At'))
                            ->content(fn ($record) => $record?->created_at ?   self::convertNumbers(Jalalian::fromCarbon($record->created_at)->format('Y/m/d H:i'))  : null),
                        
                        \Filament\Forms\Components\Placeholder::make('updated_by')
                            ->label(__('Updated By'))
                            ->content(fn ($record) => $record?->updater?->fullName ),
                        
                        \Filament\Forms\Components\Placeholder::make('updated_at')
                            ->label(__('Last Updated'))
                            ->content(fn ($record) => $record?->updated_at ? self::convertNumbers( Jalalian::fromCarbon($record->updated_at)->format('Y/m/d H:i')) : null),
                    ])
                    ->columns(4)
                    ->visible(fn ($record) => $record !== null), // Only show for existing records
                
                Section::make(__('Basic Information'))
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('title')
                            ->label(__('filament.fields.title'))
                            ->required()
                            ->maxLength(256),
                        
                        \Filament\Forms\Components\Select::make('paper_type_id')
                            ->label(__('Paper Type'))
                            ->relationship('paperType', 'title')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('Paper Type'))
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->preload(),
                        
                        \Filament\Forms\Components\Select::make('country_id')
                            ->label(__('Country'))
                            ->relationship('country', 'title')
                            ->searchable()
                            ->preload(),
                        
                        \Filament\Forms\Components\TextInput::make('title_url')
                            ->label(__('Title URL'))
                            ->maxLength(256)
                            ->helperText(__('URL-friendly version of the title')),
                        
                        \Filament\Forms\Components\TextInput::make('priority')
                            ->label(__('Display Priority'))
                            ->numeric()
                            ->helperText(__('Order for display (lower numbers appear first)')),

                        // \Filament\Forms\Components\TagsInput::make('tag')
                        // ->suggestions(fn(): array => Paper::pluck('tag')->toArray())
                    ])
                    ->columns(2),
                
                Section::make(__('Paper Details'))
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('paper_date')
                            ->label(__('Paper Date'))
                            ->timezone('Asia/Tehran')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->displayFormat('Y/m/d')
                            ->format('Y-m-d')
                            ->jalali(),
                        
                        \Filament\Forms\Components\TextInput::make('doi')
                            ->label('DOI')
                            ->maxLength(256)
                            ->helperText('Digital Object Identifier'),
                        
                        \Filament\Forms\Components\TextInput::make('count_page')
                            ->label(__('Page Count'))
                            ->numeric()
                            ->helperText(__('Number of pages in the paper')),
                        
                        \Filament\Forms\Components\TextInput::make('refrence_link')
                            ->label(__('Reference Link'))
                            ->url()
                            ->maxLength(256)
                            ->helperText(__('Link to the original paper')),
                        \Filament\Forms\Components\Fieldset::make(__('subject'))
                            ->schema([
                                \Filament\Forms\Components\CheckboxList::make('subjects')
                                    ->relationship('paperSubject', 'title')
                                    ->columns(2)
                                    ->bulkToggleable()
                                    ->label(''),
                            ])
                            ->columns(1)

                            // ->columnSpan(['md' => 1, 'xl' => 1])
                       
                    ])
                    ->columns(2),
                
                Section::make(__('Content'))
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('abstract')
                            ->label(__('Abstract'))
                            ->rows(4)
                            ->maxLength(65535)
                            ->helperText(__('Brief summary of the paper')),
                        
                        \Filament\Forms\Components\RichEditor::make('description')
                            ->label(__('Description'))
                            ->columnSpanFull()
                            ->helperText(__('Full content of the paper')),
                            
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('extractTags')
                                ->label(__('Extract Tags from Description'))
                                ->icon('heroicon-o-sparkles')
                                ->color('success')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    $description = $get('description');
                                    if (!empty($description)) {
                                        $openAiService = new OpenAiService();
                                        $extractedTags = $openAiService->retrieveTagsForDescription($description);
                                        $tags = [];
                                        if($openAiService->wasFallbackUsed()){
                                            \Filament\Notifications\Notification::make()
                                                ->title(__('Tags could not be extracted'))
                                                ->body(__('could not connect to AI Please See the log for more info'))
                                                ->warning()
                                                ->send();
                                        }else {
                                            foreach($extractedTags as $tag){
                                                $tags[] = Tag::firstOrCreate(['name'=>$tag],['name'=>$tag])->id; 
                                            }
                                            $set('tags', array_merge($get('tags'),$tags));
                                        }
                                       
                                    }
                                })
                                ->requiresConfirmation()
                                ->modalHeading(__('Extract Tags'))
                                ->modalDescription(__('This will analyze the description and extract relevant tags using AI. Continue?'))
                        ])->columnSpan(2),
                        Forms\Components\Select::make('tags')
                            ->columnSpan(2)
                            ->label(__('Tags'))
                            ->relationship('tags', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Tag Name'))
                                    ->required()
                                    ->maxLength(255)
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Tag::create($data)->id;
                            })
                            ->multiple()
                            ->helperText(__('Select or create new tags for this paper')),
                    ])
                    ->columns(1),
                
                Section::make(__('Files'))
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('paper_file')
                            ->label(__('Paper File (PDF)'))
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->openable()
                            ->helperText(__('Upload the main paper file (PDF)')),
                        
                        \Filament\Forms\Components\FileUpload::make('paper_word_file')
                            ->label(__('Paper Word File'))
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->openable()
                            ->helperText(__('Upload the Word version of the paper')),
                        
                        \Filament\Forms\Components\FileUpload::make('image_path1')
                            ->label(__('Image 1'))
                            ->image()
                            ->imagePreviewHeight('250')
                            ->maxSize(5120) // 5MB
                            ->downloadable()
                            ->openable()
                            ->helperText(__('First image for the paper')),
                        
                        \Filament\Forms\Components\FileUpload::make('image_path2')
                            ->label(__('Image 2'))
                            ->image()
                            ->imagePreviewHeight('250')
                            ->maxSize(5120) // 5MB
                            ->downloadable()
                            ->openable()
                            ->helperText(__('Second image for the paper')),
                    ])
                    ->columns(2),
                
                Section::make(__('Status'))
                    ->schema([
                        \Filament\Forms\Components\Toggle::make('is_accepted')
                            ->label(__('Is Accepted'))
                            ->helperText(__('Whether the paper has been accepted')),
                        
                        \Filament\Forms\Components\Toggle::make('is_visible')
                            ->label(__('Is Visible'))
                            ->helperText(__('Whether the paper is visible to users'))
                            ->default(true),
                        
                        \Filament\Forms\Components\Toggle::make('is_archived')
                            ->label(__('Is Archived'))
                            ->helperText(__('Whether the paper is archived')),
                    ])
                    ->columns(3),
                Section::make()
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('paperResource')
                        ->relationship()
                        ->label(__('Resources'))
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('title')->label(__('filament.fields.title'))->required(),
                            \Filament\Forms\Components\TextInput::make('link')->label(__('link'))->required(),
                        ])
                        ->columns(2),
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
                            ->maxValue(function (HasTable $livewire) {
                                $perPage = (int) ($livewire->tableRecordsPerPage ?? 25);
                                if ($perPage <= 0) {
                                    $perPage = 25;
                                }
                                $total = Paper::query()->count();
                                return max(1, (int) ceil($total / $perPage));
                            })
                            ->helperText('شماره صفحه مورد نظر را وارد کنید'),
                    ])
                    ->action(function (array $data, HasTable $livewire) {
                        $perPage = (int) ($livewire->tableRecordsPerPage ?? 25);
                        if ($perPage <= 0) {
                            $perPage = 25;
                        }
                        $total = Paper::query()->count();
                        $lastPage = max(1, (int) ceil($total / $perPage));
                        $page = (int) ($data['page'] ?? 1);
                        $page = max(1, min($page, $lastPage));

                        return redirect()->to(url('admin/papers') . '?page=' . $page);
                    }),
                \Filament\Tables\Actions\ExportAction::make()
                ->exporter(PaperExporter::class)
                ->formats([
                    \Filament\Actions\Exports\Enums\ExportFormat::Xlsx,
                ])
                ->outlined()
                ->icon('heroicon-s-bars-arrow-down')
                ->iconButton()
                ->size(\Filament\Support\Enums\ActionSize::Large)
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label(__('filament.fields.id'))
                ->searchable()
                ->sortable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label(__('filament.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('paperType.title')
                    ->label(__('Paper Type'))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Display Priority'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\IconColumn::make('is_accepted')
                    ->label(__('Is Accepted'))
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('Is Visible'))
                    ->boolean()
                    ->sortable(),
            
                Tables\Columns\IconColumn::make('is_archived')
                    ->label(__('Is Archived'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('inserter.fullName')
                    ->label(__('Created By'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
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
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('paper_type_id')
                    ->label(__('Paper Type'))
                    ->relationship('paperType', 'title'),
                
                Tables\Filters\SelectFilter::make('country_id')
                    ->label(__('Country'))
                    ->relationship('country', 'title'),
                
                Tables\Filters\TernaryFilter::make('is_accepted')
                    ->label(__('Is Accepted')),
                
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('Is Visible')),
                
                Tables\Filters\TernaryFilter::make('is_archived')
                    ->label(__('Is Archived')),
            ])
            ->defaultSort('id', 'desc') 
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ], position: \Filament\Tables\Enums\ActionsPosition::BeforeCells)
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
            'index' => Pages\ListPapers::route('/'),
            'create' => Pages\CreatePaper::route('/create'),
            'edit' => Pages\EditPaper::route('/{record}/edit'),
        ];
    }
}


 // Get existing tags
                                        // $existingTags = $get('tags') ?? [];
                                        
                                        // // Process extracted tags - create new ones and get their IDs
                                        // $newTagIds = [];
                                        // $createdTags = [];
                                        // $existingTagsFound = [];
                                        
                                        // foreach ($extractedTags as $tagName) {
                                        //     // Check if tag already exists
                                        //     $existingTag = \App\Models\Tag::where('name', $tagName)->first();
                                            
                                        //     if ($existingTag) {
                                        //         // Tag exists, add its ID
                                        //         $newTagIds[] = $existingTag->id;
                                        //         $existingTagsFound[] = $tagName;
                                        //     } else {
                                        //         // Create new tag and add its ID
                                        //         $newTag = \App\Models\Tag::create(['name' => $tagName]);
                                        //         $newTagIds[] = $newTag->id;
                                        //         $createdTags[] = $tagName;
                                        //     }
                                        // }
                                        
                                        // // Merge existing and new tag IDs, remove duplicates
                                        // $allTagIds = array_unique(array_merge($existingTags, $newTagIds));
                                        
                                        // // Set the merged tag IDs
                                        // $set('tags', $allTagIds);
                                        // info('Processed tags:', ['extracted' => $extractedTags, 'final_ids' => $allTagIds]);
                                        
                                        // // Debug: Check what's in the form after setting
                                        // info('Form tags after setting:', ['form_tags' => $get('tags')]);
                                        

                                        // // Check if fallback was used
                                        // if ($openAiService->wasFallbackUsed()) {
                                        //     $fallbackReason = $openAiService->getFallbackReason();
                                        //     // Show warning that fallback was used
                                        //     \Filament\Notifications\Notification::make()
                                        //         ->title(__('Tags extracted (using fallback method)'))
                                        //         ->body(__(':count tags extracted using keyword analysis. Reason: :reason', [
                                        //             'count' => count($extractedTags),
                                        //             'reason' => $fallbackReason
                                        //         ]))
                                        //         ->warning()
                                        //         ->send();
                                        // } else {
                                        //     // Show success notification
                                        //     \Filament\Notifications\Notification::make()
                                        //         ->title(__('Tags extracted successfully'))
                                        //         ->body(__(':count new tags extracted from description', ['count' => count($extractedTags)]))
                                        //         ->success()
                                        //         ->send();
                                        // }
                                        
                                        // // Show detailed tag information
                                        // $tagInfo = [];
                                        // if (!empty($createdTags)) {
                                        //     $tagInfo[] = __(':count new tags created', ['count' => count($createdTags)]);
                                        // }
                                        // if (!empty($existingTagsFound)) {
                                        //     $tagInfo[] = __(':count existing tags found', ['count' => count($existingTagsFound)]);
                                        // }
                                        
                                        // if (!empty($tagInfo)) {
                                        //     \Filament\Notifications\Notification::make()
                                        //         ->title(__('Tag Details'))
                                        //         ->body(implode(', ', $tagInfo))
                                        //         ->info()
                                        //         ->send();
                                        // }