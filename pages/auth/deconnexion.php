<?php

declare(strict_types=1);

use App\Auth;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort(400);
}
Auth::logout();
session_start();
flash('success', 'Vous êtes déconnecté. À bientôt !');
redirect('/');
