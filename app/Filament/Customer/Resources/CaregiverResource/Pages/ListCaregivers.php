<?php

namespace App\Filament\Customer\Resources\CaregiverResource\Pages;

use App\Filament\Customer\Resources\CaregiverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCaregivers extends ListRecords
{
    protected static string $resource = CaregiverResource::class;

//    protected function getHeaderActions(): array
//    {
//        return [
//            Actions\CreateAction::make(),
//        ];
//    }

    protected function getFooterWidgets(): array
    {
        return [
CaregiverResource\Widgets\CaregiversInvitation::class
        ];
    }
}
