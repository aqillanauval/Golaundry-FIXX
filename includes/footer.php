<?php
// footer.php: penutup halaman + script.
// Halaman bisa mengisi $jsTambahan = ['nama-file.js'] sebelum include.
?>
    </div><!-- /.content -->
  </main>
</div><!-- /.app -->

<script src="assets/js/app.js"></script>
<?php if (!empty($jsTambahan)): foreach ($jsTambahan as $js): ?>
<script src="assets/js/<?= e($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
