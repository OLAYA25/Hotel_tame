<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Hotel Management System'; ?></title>
    <meta name="description" content="<?php echo $pageDescription ?? 'Sistema de Gestión Hotelera'; ?>">
    <meta name="author" content="Hotel Management System">
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
}
.select2-container--bootstrap-5 .select2-dropdown {
    z-index: 9999;
}
.select2-container {
    width: 100% !important;
}
.select2-search__field {
    width: 100% !important;
    pointer-events: auto !important;
}
.select2-dropdown {
    z-index: 9999 !important;
}
</style>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <!-- Sistema de Notificaciones -->
    <?php include_once __DIR__ . '/../../components/notifications_simple.php'; ?>
    <div class="d-flex">
