<?php

namespace App\Filament\Exports;

use App\Models\Person;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Morilog\Jalali\Jalalian;


class PersonExporter extends Exporter 
{
    protected static ?string $model = Person::class;

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
            ExportColumn::make('name')
                ->label(__('filament.fields.name')) ,
            
            ExportColumn::make('family')
                ->label(__('filament.fields.family')),
            
            ExportColumn::make('gender')
                ->label(__('filament.fields.gender')),

            ExportColumn::make('email')
                ->label(__('filament.fields.email')),

            ExportColumn::make('national_code')
                ->label(__('filament.fields.national_code')) ,

            ExportColumn::make('shenasname')
                ->label(__('filament.fields.shenasname')),

            ExportColumn::make('passport_number')
                ->label(__('filament.fields.passport_number')),

            ExportColumn::make('father_name')
                ->label(__('filament.fields.father_name')),

            ExportColumn::make('birth_year')
                ->label(__('filament.fields.birth_year')),

            ExportColumn::make('website')
                ->label(__('filament.fields.website')) ,

            ExportColumn::make('mobile')
                ->label(__('filament.fields.mobile')),

            ExportColumn::make('mobile')
                ->label(__('filament.fields.mobile')),

            ExportColumn::make('fax')
                ->label(__('filament.fields.fax')),


            ExportColumn::make('postalcode')
                ->label(__('filament.fields.postalcode')),

            ExportColumn::make('country.title')
                ->label(__('Country')),

            ExportColumn::make('province.title')
                ->label(__('filament.fields.province')),

            ExportColumn::make('city.title')
                ->label(__('filament.fields.city')),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('filament.messages.export_completed',['model'=>__('Persons')]) . number_format($export->successful_rows) . ' (' .__('Files only remain on the system for up to an hour.').')';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
