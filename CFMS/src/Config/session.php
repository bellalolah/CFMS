<?php
// config/session.php



if (isset($_SESSION["user_role"]) && isset($_SESSION["user_id"])) {
    if (!isset($_SESSION['last_regeneration'])) {
        regenerateSessionIdLoggedin();
    } else {
        $interval = 60 * 300;
        if (time() - $_SESSION['last_regeneration'] >= $interval) {
            regenerateSessionIdLoggedin();
        }
    }
} else {
    if (!isset($_SESSION['last_regeneration'])) {
        regenerateSessionId();
    } else {
        $interval = 60 * 300;
        if (time() - $_SESSION['last_regeneration'] >= $interval) {
            regenerateSessionId();
        }
    }
}

function regenerateSessionId()
{
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

function regenerateSessionIdLoggedin()
{
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
