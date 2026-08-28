<?php
/**
 * includes/header.php
 * En-tête HTML commun à toutes les pages.
 * Variables attendues (optionnelles) : $pageTitle, $assetsPath
 * $assetsPath doit contenir le chemin relatif vers le dossier assets/ (ex: "assets/" ou "../assets/")
 */
if (!isset($assetsPath)) {
    $assetsPath = 'assets/';
}
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= $assetsPath ?>css/style.css">
</head>
<body>
