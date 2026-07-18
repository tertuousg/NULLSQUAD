    </main>
    <footer class="site-footer mt-5">
        <div class="container py-4">
            <div class="row align-items-center g-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="<?= asset('images/logo.svg') ?>" alt="<?= e(APP_NAME) ?> logo" width="40" height="40">
                        <strong><?= e(APP_NAME) ?></strong>
                    </div>
                    <p class="mb-0 small"><?= e(EDUCATIONAL_FOOTER) ?></p>
                </div>
                <div class="col-md-6 text-md-end small">
                    <div><?= e(GROUP_NAME) ?></div>
                    <div>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</div>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>

