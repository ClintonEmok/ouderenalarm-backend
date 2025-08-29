<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Mail\CustomerInvitationMail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('resetPassword')
                ->label('Wachtwoord resetten')
                ->icon('heroicon-m-key')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Weet je zeker dat je het wachtwoord wilt resetten?')
                ->modalDescription('Dit genereert een nieuw willekeurig wachtwoord en verstuurt het naar de klant per e-mail.')
                ->modalSubmitActionLabel('Bevestigen')
                ->modalCancelActionLabel('Annuleren')
                ->action(function (): void {
                    // 1) Nieuw wachtwoord genereren & opslaan
                    $nieuwWachtwoord = Str::random(12);

                    $this->record->forceFill([
                        'password' => Hash::make($nieuwWachtwoord),
                    ])->save();

                    // 2) E-mail sturen naar klant
                    Mail::to($this->record->email)
                        ->queue(new CustomerInvitationMail($this->record, $nieuwWachtwoord));

                    // 3) Succesnotificatie in Filament
                    Notification::make()
                        ->success()
                        ->title('Wachtwoord opnieuw ingesteld')
                        ->body("Er is een nieuw wachtwoord gegenereerd en verstuurd naar {$this->record->email}.")
                        ->send();
                }),
        ];
    }
}