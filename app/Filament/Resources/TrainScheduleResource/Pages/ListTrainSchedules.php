<?php

namespace App\Filament\Resources\TrainScheduleResource\Pages;

use App\Filament\Resources\TrainScheduleResource;
use App\Support\JadwalImporter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTrainSchedules extends ListRecords
{
    protected static string $resource = TrainScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import Jadwal (Excel)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('File Excel (.xlsx)')
                        ->disk('local')
                        ->directory('jadwal-import')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal Jadwal')
                        ->required()
                        ->default(now()),
                    Forms\Components\TextInput::make('sheet')
                        ->label('Nama Sheet')
                        ->default('Sheet1'),
                ])
                ->action(function (array $data) {
                    $path = storage_path('app/'.$data['file']);

                    $count = JadwalImporter::importFromFile(
                        $path,
                        $data['tanggal'],
                        $data['sheet'] ?: 'Sheet1'
                    );

                    Notification::make()
                        ->title("Berhasil import {$count} baris jadwal")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
