<?php

namespace App\Livewire;

use App\Enums\InviteStatus;
use Livewire\Component;
use App\Models\Invite;
use App\Models\User;use Filament\Actions\Action;use Filament\Actions\ActionGroup;use Filament\Forms\Components\TextInput;use Filament\Forms\Concerns\InteractsWithForms;use Filament\Forms\Form;use Filament\Pages\Concerns\InteractsWithFormActions;use Filament\Pages\Dashboard;use Filament\Pages\SimplePage;use Illuminate\Validation\Rules\Password;

class AcceptCaregiverInvitation extends SimplePage
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static string $view = 'livewire.accept-caregiver-invitation';
    protected static ?string $title = "Accepteer Mantelzorg uitnodiging";

    public int $invitation;
    private Invite $invitationModel;

    public ?array $data = [];


//    TODO: Add custom error page
    public function mount(): void
    {
        $this->invitationModel = Invite::query()
            ->where('id', $this->invitation)
            ->where('status', InviteStatus::Pending)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereNull('invited_user_id')
            ->firstOrFail();

        $this->form->fill([
            'email' => $this->invitationModel->email,
        ]);
    }

    public function getTitle(): string
    {
        if (! isset($this->invitationModel)) {
            return 'Accepteer Mantelzorg uitnodiging';
        }

        return 'Accepteer Mantelzorg uitnodiging van ' . ($this->invitationModel->inviter->name ?? '');
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('filament-panels::pages/auth/register.form.name.label'))
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),
                TextInput::make('email')
                    ->label(__('filament-panels::pages/auth/register.form.email.label'))
                    ->disabled(),
                TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/register.form.password.label'))
                    ->password()
                    ->required()
                    ->rule(Password::default())
                    ->same('passwordConfirmation')
                    ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),
                TextInput::make('passwordConfirmation')
                    ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
                    ->password()
                    ->required()
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $this->invitationModel = Invite::find($this->invitation);

        $user = User::create([
            'name' => $this->form->getState()['name'],
            'password' => $this->form->getState()['password'],
            'email' => $this->invitationModel->email,
        ]);

// TODO: Add a role of customer
        $user->assignRole('customer');
        auth()->login($user);
        $this->invitationModel->acceptNew($user);
//        $this->invitationModel->delete();
//        TODO: Redirect to customer panel
        $this->redirect(Dashboard::getUrl());
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction(),
        ];
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(__('filament-panels::pages/auth/register.form.actions.register.label'))
            ->submit('register');
    }
    public function getHeading(): string
    {
        if (! isset($this->invitationModel)) {
            return 'Accepteer uitnodiging';
        }

        return 'Accepteer uitnodiging van ' . ($this->invitationModel->inviter->name ?? '');
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function getSubHeading(): string
    {
        return 'Maak een account aan om de uitnodiging te accepteren.';
    }
}
