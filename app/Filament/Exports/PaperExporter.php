<?php

namespace App\Filament\Exports;

use App\Models\Paper;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Morilog\Jalali\Jalalian;


class PaperExporter extends Exporter 
{
    protected static ?string $model = Paper::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('created_at')
                ->formatStateUsing(fn ( $state): string => !empty($state) ? Jalalian::fromCarbon($state)->format('Y/m/d H:i') : '')
                ->label(__('filament.fields.created_at')),
            ExportColumn::make('updated_at')
                ->label(__('filament.fields.updated_at'))
                ->formatStateUsing(fn ( $state): string => !empty($state) ? Jalalian::fromCarbon($state)->format('Y/m/d H:i') : ''),
            ExportColumn::make('title')
                ->co
                ->label(__('filament.fields.title')),
            ExportColumn::make('paperType.title')
                ->label(__('Paper Type')),  
            ExportColumn::make('country.title')
                ->label(__('Country')),  
            ExportColumn::make('title_url')
                ->label(__('Title URL')),
            ExportColumn::make('priority')
                ->label(__('Display Priority')),
            ExportColumn::make('paper_date')
                ->label(__('Paper Date')),
            ExportColumn::make('doi')
                ->label('DOI'),
            ExportColumn::make('count_page')
                ->label(__('Page Count')),
            ExportColumn::make('refrence_link')
                ->label(__('Reference Link')),
            ExportColumn::make('is_accepted')
                ->label(__('Is Accepted')),
            ExportColumn::make('is_visible')
                ->label(__('Is Visible')),
            ExportColumn::make('is_archived')
                ->label(__('Is Archived')),
            ExportColumn::make('abstract')->words(10)
                ->label(__('Abstract')),
            ExportColumn::make('description') ->words(10)
                ->label(__('Description')),
            ExportColumn::make('inserter.fullName')
                ->label(__('Created By')),
            ExportColumn::make('updater.fullName')
                ->label(__('Updated By'))

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('filament.messages.export_completed',['model'=>__('Papers')]) . number_format($export->successful_rows) . ' (' .__('Files only remain on the system for up to an hour.').')';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
