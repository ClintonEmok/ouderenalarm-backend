<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\AcceptCaregiverInvitation;

Route::get('/', function () {
    return redirect('/dashboard');
});
Route::middleware('signed')
    ->get('invitation/{invitation}/accept', AcceptCaregiverInvitation::class)
    ->name('invitation.accept');

// TODO: Add register for customers
require __DIR__.'/auth.php';
