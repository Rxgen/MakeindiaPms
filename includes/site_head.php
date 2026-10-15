<?php
require_once __DIR__ . '/site_config.php';
if (!isset($page_title) || $page_title === '') {
    $page_title = 'Make in India Capital PMS';
}
$extra_css = isset($extra_css) ? (array) $extra_css : array();
$extra_js = isset($extra_js) ? (array) $extra_js : array();
$body_class = isset($body_class) ? trim((string) $body_class) : '';
$body_id = isset($body_id) ? trim((string) $body_id) : 'top';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= pms_h($page_title) ?></title>
  <link rel="stylesheet" href="<?= pms_h(pms_url('style.css')) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php foreach ($extra_css as $cssHref): ?>
  <link rel="stylesheet" href="<?= pms_h($cssHref) ?>">
  <?php endforeach; ?>
  <script defer src="<?= pms_h(pms_url('script.js')) ?>"></script>
  <?php foreach ($extra_js as $jsHref): ?>
  <script defer src="<?= pms_h($jsHref) ?>"></script>
  <?php endforeach; ?>
</head>
<body<?= $body_id !== '' ? ' id="' . pms_h($body_id) . '"' : '' ?><?= $body_class !== '' ? ' class="' . pms_h($body_class) . '"' : '' ?>>
<?php require __DIR__ . '/site_header.php'; ?>
