<?php
// public/theme-settings.php

// Detect if we’re on HTTPS (Secure cookies require HTTPS; dev on localhost usually isn't)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

// Handle form submission (must run before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $theme = $_POST['theme'] === 'dark' ? 'dark' : 'default'; // simple whitelist

    // 30 days
    $expires = time() + (86400 * 30);

    // Set cookie with modern options; Secure is true only if HTTPS detected
    setcookie('theme', $theme, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => $isHttps,     // true in prod (HTTPS), false on localhost
        'httponly' => true,         // not accessible to JS
        'samesite' => 'Lax',        // good default, helps mitigate CSRF
    ]);

    // PRG pattern to ensure the cookie is available on the next GET
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Read cookie (server-side render uses whatever the client sent)
$theme = isset($_COOKIE['theme']) ? ($_COOKIE['theme'] === 'dark' ? 'dark' : 'default') : 'default';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Theme Page Example</title>
  <style>
    body.default { background-color: white; color: black; }
    body.dark    { background-color: #333; color: white; }
    .box { max-width: 720px; margin: 2rem auto; font-family: system-ui, sans-serif; }
    select, button, input[type="submit"] { padding: .4rem .6rem; }
  </style>
</head>
<body class="<?= htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') ?>">
  <div class="box">
    <h1>Theme Page Example</h1>
    <p>Current theme: <strong><?= htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') ?></strong></p>

    <form method="post">
      <label>
        Select your preferred theme:
        <select name="theme">
          <option value="default" <?= $theme === 'default' ? 'selected' : '' ?>>Default Theme</option>
          <option value="dark"    <?= $theme === 'dark'    ? 'selected' : '' ?>>Dark Theme</option>
        </select>
      </label>
      <input type="submit" name="changeTheme" value="Change Theme">
    </form>

    <form method="post" style="margin-top:1rem">
      <input type="hidden" name="theme" value="default">
      <button type="submit">Reset to Default</button>
    </form>
  </div>
</body>
</html>
