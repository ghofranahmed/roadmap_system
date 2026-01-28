<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Mail;

Route::get('/send-test-email', function () {
    Mail::raw('Hello from Mailtrap', function ($message) {
        $message->to('test@example.com')
                ->subject('Test Mail');
    });

    return 'Mail sent!';
});

