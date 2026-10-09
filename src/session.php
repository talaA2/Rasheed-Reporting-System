<?php
// Start the login session with safer cookie settings.
// HttpOnly: page scripts cannot read the cookie.
// SameSite=Lax: other websites cannot submit forms here using the user's login.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
