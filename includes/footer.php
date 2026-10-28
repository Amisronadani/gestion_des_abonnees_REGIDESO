        </div> <!-- End of container-fluid -->
    </main> <!-- End of main-content -->
</div> <!-- End of premium-wrapper -->

<!-- Footer -->
<footer style="padding: 1.5rem; text-align: center; background: var(--bg-card); border-top: 1px solid var(--border); transition: var(--transition); color: var(--text-muted); font-size: 0.85rem;">
    <strong>&copy; <?= date('Y') ?> <span style="color: var(--primary);">REGIDESO</span></strong> — Régie de Production et de Distribution d'Eau et d'Électricité · Bujumbura, Burundi
</footer>

<!-- Modern JS Dependencies -->
<!-- We keep jQuery for some basic interactions if needed by other scripts, but try to avoid it for new features -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- UI Enhancement Scripts -->
<script src="<?= BASE_URL ?>/assets/js/theme.js"></script>

<!-- html2canvas (capture d'écran pour partage) -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<!-- Custom/Specific JS -->
<?php if (file_exists(__DIR__ . '/../assets/js/custom.js')): ?>
<script src="<?= BASE_URL ?>/assets/js/custom.js"></script>
<?php endif; ?>

</body>
</html>
