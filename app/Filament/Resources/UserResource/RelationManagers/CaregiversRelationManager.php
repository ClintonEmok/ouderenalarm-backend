<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Mail\CaregiverInvitationMail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CaregiversRelationManager extends RelationManager
{
    protected static string $relationship = 'caregivers';
    protected static ?string $title = "Contactpersonen";


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->preloadRecordSelect()->multiple(),
                Tables\Actions\CreateAction::make()
                    ->label('Nieuwe contactpersoon aanmaken')
                    ->using(function (array $data, $livewire) {
                        $inviter = $livewire->ownerRecord;
                        $email = $data['email'];
                        $phone = $data['phone_number'];
                        $name = $data['name'];

                        // Check if user already exists
                        $user = User::where('email', $email)
                            ->orWhere('phone_number', $phone)
                            ->first();

                        if ($user) {
                            Notification::make()
                                ->title('Gebruiker bestaat al')
                                ->body("De gebruiker met dit e-mailadres of telefoonnummer bestaat al.")
                                ->warning()
                                ->send();
                            return null;
                        }

                        // Generate temp password
                        $generatedPassword = Str::random(12);

                        // Create the new caregiver user
                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'phone_number' => $phone,
                            'password' => Hash::make($generatedPassword),
                        ]);

                        // Optional: assign caregiver role
                        $user->assignRole('customer');

                        // Attach user to patient as caregiver
                        $inviter->caregivers()->attach($user->id);

                        // Send caregiver invite email with password
                        Mail::to($email)->queue(new CaregiverInvitationMail($user, $generatedPassword));

                        // Confirm to admin
                        Notification::make()
                            ->title('Contactpersoon aangemaakt')
                            ->body("{$name} is aangemaakt en gekoppeld als contactpersoon.")
                            ->success()
                            ->send();

                        return $user;
                    })
                    ->form([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('email')->required()->email(),
                        PhoneInput::make('phone_number')->label('Telefoonnummer')->required(),
                    ]),

            ])
            ->actions([
                Tables\Actions\DetachAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                 Tables\Actions\DetachBulkAction::make(),
                ]),
            ])->inverseRelationship('patients');
    }
}
