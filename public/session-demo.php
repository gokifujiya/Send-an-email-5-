<?php
// Secure cookie setup (same as before)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

session_set_cookie_params([
    'lifetime' => 600, // 10 minutes
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

// Log current session ID
error_log("Current Session ID: " . session_id());

// Example trigger: if user clicks regenerate
if (isset($_GET['regenerate'])) {
    session_regenerate_id(true);
    error_log("New Session ID generated: " . session_id());
}

// Example trigger: if user clicks destroy
if (isset($_GET['destroy'])) {
    session_unset();
    session_destroy();
    error_log("Session data deleted. No session data " . session_id() . "!");
    // Also delete the cookie on client
    setcookie(session_name(), "", time() - 3600, "/");
    error_log(sprintf("Session cookie %s unset.", session_name()));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Session Demo</title>
</head>
<body>
    <h2>Session Demo Page</h2>
    <p>Session ID: <?= htmlspecialchars(session_id(), ENT_QUOTES, 'UTF-8') ?></p>

    <p><a href="?regenerate=1">Regenerate Session ID</a></p>
    <p><a href="?destroy=1">Destroy Session</a></p>
</body>
</html>
