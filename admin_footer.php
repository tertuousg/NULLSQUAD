            <footer class="border-top mt-5 pt-3 small text-muted">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <img src="<?= asset('images/logo.svg') ?>" alt="<?= e(APP_NAME) ?> logo" width="32" height="32">
                        <span><?= e(EDUCATIONAL_FOOTER) ?></span>
                    </div>
                    <div><?= e(GROUP_NAME) ?> &copy; <?= date('Y') ?></div>
                </div>
            </footer>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
