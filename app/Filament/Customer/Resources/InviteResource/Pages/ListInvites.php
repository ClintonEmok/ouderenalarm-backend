<?php

namespace App\Filament\Customer\Resources\InviteResource\Pages;

use App\Filament\Customer\Resources\InviteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvites extends ListRecords
{
    protected static string $resource = InviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
