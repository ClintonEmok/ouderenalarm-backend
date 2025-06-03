<?php

namespace App\Filament\Customer\Pages\Auth;

use App\Models\UserInvitation;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;


class Register extends BaseRegister
{



    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body',
                    __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body',
                    [
                        'seconds' => $exception->secondsUntilAvailable,
                        'minutes' => ceil($exception->secondsUntilAvailable / 60),
                    ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();
        $user = $this->getUserModel()::create($data);
        $user->assignRole('customer');
        $panel = Filament::getPanel('customer');
        Filament::setCurrentPanel($panel);
//        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
//                        $this->getPrivacyFormComponent(),
//                        $this->getTCFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

//    protected function getPrivacyFormComponent(): Component
//    {
//        return Toggle::make('privacy')
//            ->label(new HtmlString(Blade::render('I agree to the ' . '<x-filament::link target="_blank" href="https://www.dr-pitt.com/privacy-policy">' . __('Privacy Policy') . ' </x-filament::link> ')))
//            ->validationMessages([
//                'accepted' => 'Please confirm you have read the Privacy Policy.',
//            ])
//            ->accepted();
//    }

//    protected function getTCFormComponent(): Component
//    {
//        return Toggle::make('tc')
//            ->label(new HtmlString(Blade::render('I agree to the ' . '<x-filament::link target="_blank" href="https://www.dr-pitt.com/terms-conditions">' . __('Terms and Conditions') . ' </x-filament::link> ')))
//            ->validationMessages([
//                'accepted' => 'Please confirm you have read the Terms and Conditions.',
//            ])
//            ->accepted();
//    }
}
