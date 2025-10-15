<!-- Views/component/navigator.php -->
<?php
// $user is already provided by HTMLRenderer::getHeader(); but fall back if needed.
if (!isset($user) && class_exists('\Helpers\Authenticate')) {
    $user = \Helpers\Authenticate::getAuthenticatedUser();
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="/random/part">Parts</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto">
        <?php if (!empty($user)): ?>
          <li class="nav-item"><a class="nav-link" href="/update/part">New Part</a></li>
          <li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="/register">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

