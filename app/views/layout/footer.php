<?php
// views/layout/footer.php: penutup halaman + script.
// Controller bisa mengirim $jsTambahan = ['nama-file.js'].
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
