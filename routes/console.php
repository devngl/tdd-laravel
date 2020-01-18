<?php

use App\Facades\InvitationCode;
use App\Invitation;

Artisan::command('invite-promoter {email}', function ($email) {
    Invitation::create([
        'email' => $email,
        'code'  => InvitationCode::generate(),
    ])->send();
})->describe('Invite a new promoter to create an account');
