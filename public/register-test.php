<?php
// ---- Session cookie settings (set BEFORE session_start) ----
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

session_set_cookie_params([
    'lifetime' => 1800,   // 30 min server-side session (adjust as needed)
    'path'     => '/',
    'secure'   => $isHttps,   // true in production (HTTPS), false on localhost
    'httponly' => true,       // JS cannot read PHPSESSID
    'samesite' => 'Lax',      // good default to mitigate CSRF
]);

session_start(); // must be first output-producing line

$errors  = [];
$success = false;

// Pull any old input saved in a previous failed POST
$old = $_SESSION['old'] ?? [
    'username'   => '',
    'email'      => '',
    'agreement'  => false,
    'newsletter' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read inputs from POST (don’t default to $_SESSION here; we’ll save errors back)
    $username        = trim($_POST['username'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $retype_password = $_POST['retype_password'] ?? '';
    $agreement       = isset($_POST['agreement']);
    $newsletter      = isset($_POST['newsletter']);

    // Validate
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $retype_password) {
        $errors[] = 'Passwords do not match.';
    }
    if (!$agreement) {
        $errors[] = 'You must agree to the terms and conditions.';
    }

    $success = count($errors) === 0;

    if (!$success) {
        // Save ONLY non-sensitive fields to session so we can repopulate the form
        $_SESSION['old'] = [
            'username'   => $username,
            'email'      => $email,
            'agreement'  => $agreement,
            'newsletter' => $newsletter,
        ];
        // Refresh $old for this render
        $old = $_SESSION['old'];
    } else {
        // ✔ Success: clear old input and (optionally) regenerate session ID
        unset($_SESSION['old']);
        session_regenerate_id(true); // good practice after auth-like flows
        // Here you’d normally create the user, hash password, etc.
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <style>
    label { display:block; margin-top:.5rem; }
  </style>
</head>
<body>

<?php if ($success): ?>
  <p style="color: green;">successful</p>
<?php else: ?>
  <form action="" method="post" novalidate>
    <label for="username">Username:</label>
    <input
      type="text" id="username" name="username" required
      value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
    >

    <label for="email">Email:</label>
    <input
      type="email" id="email" name="email" required
      value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
    >

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <label for="retype_password">Retype Password:</label>
    <input type="password" id="retype_password" name="retype_password" required>

    <label>
      <input type="checkbox" id="agreement" name="agreement"
             <?= !empty($old['agreement']) ? 'checked' : '' ?>>
      I agree to the terms and conditions
    </label>

    <label>
      <input type="checkbox" id="newsletter" name="newsletter"
             <?= !empty($old['newsletter']) ? 'checked' : '' ?>>
      Subscribe to newsletter
    </label>

    <div style="margin-top: .75rem;">
      <input type="submit" value="Register">
    </div>
  </form>

  <?php if (!empty($errors)): ?>
    <div style="color: red; margin-top: .75rem;">
      <p>Error:</p>
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

<?php endif; ?>

</body>
</html>
