<?php
if (!defined('ASSETS_URL')) {
    require_once __DIR__ . '/../../config/env.php';
    hotel_tame_define_web_constants();
}
?>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
