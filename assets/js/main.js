// Pencarian inline navbar (desktop)
(function () {
  var toggle = document.getElementById("searchToggle");
  var form = document.getElementById("searchForm");
  var close = document.getElementById("searchClose");
  var input = document.getElementById("searchInput");
  if (!toggle || !form) return;

  toggle.addEventListener("click", function () {
    toggle.classList.add("d-none");
    form.classList.remove("d-none");
    form.classList.add("d-flex");
    if (input) input.focus();
  });

  if (close) {
    close.addEventListener("click", function () {
      form.classList.add("d-none");
      form.classList.remove("d-flex");
      toggle.classList.remove("d-none");
      if (input) input.value = "";
    });
  }
})();
