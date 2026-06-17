</div><!-- /.auth-card -->
  </main>

</div><!-- /.auth-split -->

<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var input = document.getElementById(btn.getAttribute('data-toggle-password'));
    if (!input) return;
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('bi-eye-slash', 'bi-eye');
    } else {
      input.type = 'password';
      icon.classList.replace('bi-eye', 'bi-eye-slash');
    }
  });
});
</script>

</body>
</html>