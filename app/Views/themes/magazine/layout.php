<?= $this->include($viewsPath . '/partials/_head') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.addEventListener('selectstart', function(e) {
            if (!e.target.closest('.allow-copy')) {
                e.preventDefault();
            }
        });

        document.addEventListener('copy', function(e) {
            if (!e.target.closest('.allow-copy')) {
                e.preventDefault();
            }
        });

        document.addEventListener('cut', function(e) {
            if (!e.target.closest('.allow-copy')) {
                e.preventDefault();
            }
        });

        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
        });

        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

    });

    document.addEventListener('keydown', function(e) {

        if (
            (e.ctrlKey || e.metaKey) && ['c', 'x', 'a', 'u', 's'].includes(e.key.toLowerCase())
        ) {
            e.preventDefault();
        }

        if (e.key === 'F12') {
            e.preventDefault();
        }

    });
    
</script>
<body<?= !empty($bodyClass) ? ' class="' . $bodyClass . '"' : ''; ?>>
    <div class="d-none d-lg-block">
        <?= loadCommonView('nav/nav_top'); ?>
    </div>

    <div id="sticky-menu-wrapper" class="sticky-top d-none d-lg-block">
        <?= loadCommonView('nav/nav_main'); ?>
    </div>

    <?= loadCommonView('nav/nav_mobile'); ?>

    <?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'header', 'class' => 'mb-3']); ?>

    <main>
        <?= $this->renderSection('content'); ?>
    </main>

    <?= loadCommonView('partials/_modals'); ?>

    <?= $this->include($viewsPath . '/partials/_footer'); ?>