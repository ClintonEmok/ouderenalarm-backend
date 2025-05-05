<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\AcceptCaregiverInvitation;

Route::get('/', function () {
    return redirect('/dashboard');
});
Route::middleware('signed')
    ->get('invitation/{invitation}/accept', AcceptCaregiverInvitation::class)
    ->name('invitation.accept');

require __DIR__.'/auth.php';
