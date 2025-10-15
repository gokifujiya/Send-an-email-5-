<?php
$success = \Response\FlashData::getFlashData('success');
$error   = \Response\FlashData::getFlashData('error');
?>
<div class="container mt-5 mb-5">
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
</div>

