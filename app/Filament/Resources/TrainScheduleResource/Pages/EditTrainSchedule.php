<?php

namespace App\Filament\Resources\TrainScheduleResource\Pages;

use App\Filament\Resources\TrainScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainSchedule extends EditRecord
{
    protected static string $resource = TrainScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
