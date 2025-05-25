<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Mail\CustomerInvitationMail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected ?string $generatedPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            $this->generatedPassword = Str::random(12);
            $data['password'] = Hash::make($this->generatedPassword);
        }

        return $data;
    }

    protected function afterCreate(): void
    {   
        $this->record->assignRole('customer');
        if ($this->generatedPassword) {
            Mail::to($this->record->email)
                ->queue(new CustomerInvitationMail($this->record, $this->generatedPassword));
        }
    }
}