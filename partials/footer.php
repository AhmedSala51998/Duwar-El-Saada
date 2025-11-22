    </main>
  </div>
</div>

<!-- ====== Footer ====== -->
<footer class="custom-footer text-center py-3">
  <div class="container">
    <span><?= esc(getSystemSettings('footer_text') ?: 'Default footer text') ?><b>مطعم دوار السعادة</b> <?= date('Y') ?></span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
.custom-footer {
  background: rgba(255,106,0,0.05); /* برتقالي خفيف جدًا */
  border-top: 1px solid rgba(0,0,0,0.08); /* خط أنيق */
  backdrop-filter: blur(6px);
  font-size: 14px;
  color: #555;
}
.custom-footer b {
  color: #ff6a00;
}
</style>
</body>
</html>
