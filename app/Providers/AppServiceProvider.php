<?php

namespace App\Providers;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Models\Country;
use App\Observers\CountryObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Http\Resources\Json\JsonResource::withoutWrapping();
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        TextColumn::configureUsing(function (TextColumn $textColumn): void {
            $textColumn->timezone('Europe/Amsterdam');
        });


        Country::observe(CountryObserver::class);
    }
}
