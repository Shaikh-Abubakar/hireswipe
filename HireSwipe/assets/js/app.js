(() => {
  const toast = document.getElementById('toast');
  let toastTimer;
  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
  }
  document.querySelectorAll('[data-oauth]').forEach(button => {
    button.addEventListener('click', () => showToast(`${button.dataset.oauth} sign-in needs OAuth credentials and server setup.`));
  });
  document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', event => {
      const password = form.querySelector('[name="password"]');
      const confirm = form.querySelector('[name="confirm_password"]');
      if (password && !/(?=.*[A-Za-z])(?=.*(?:\d|[^A-Za-z0-9])).{8,}/.test(password.value)) {
        event.preventDefault(); showToast('Use 8+ characters with a letter and a number or symbol.'); password.focus(); return;
      }
      if (password && confirm && password.value !== confirm.value) {
        event.preventDefault(); showToast('Your passwords do not match.'); confirm.focus();
      }
    });
  });
  document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (file && file.size > (input.name === 'cv' ? 5 : 2) * 1024 * 1024) {
        showToast(input.name === 'cv' ? 'CV must be under 5 MB.' : 'Image must be under 2 MB.');
        input.value = '';
      }
    });
  });
})();
