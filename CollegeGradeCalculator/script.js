document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#grade-form');
  const gradeInputs = Array.from(document.querySelectorAll('.grade-input'));
  const liveAverageEl = document.querySelector('#live-average');
  const resultPanel = document.querySelector('.result');
  const resetBtn = document.querySelector('#reset-btn');

  const clamp = (n, min, max) => Math.min(Math.max(n, min), max);

  function readGrades() {
    return gradeInputs.map((input) => {
      const value = input.value.trim();
      return value === '' || isNaN(Number(value)) ? null : Number(value);
    });
  }

  function markValidity(input) {
    const value = input.value.trim();
    const num = Number(value);
    const invalid = value !== '' && (isNaN(num) || num < 0 || num > 100);
    input.setAttribute('aria-invalid', invalid ? 'true' : 'false');

    const errorEl = document.querySelector(`[data-error-for="${input.name}"]`);
    if (errorEl) {
      errorEl.textContent = invalid ? 'Enter a number between 0 and 100.' : '';
    }
  }

  function updateLiveAverage() {
    if (!liveAverageEl) return;
    const grades = readGrades();
    const complete = grades.every((g) => g !== null);

    if (!complete) {
      liveAverageEl.innerHTML = 'Live average will appear here as you type.';
      return;
    }

    const avg = grades.reduce((sum, g) => sum + g, 0) / grades.length;
    const rounded = Math.round(avg * 100) / 100;
    liveAverageEl.innerHTML = `Live average: <strong>${rounded.toFixed(2)}</strong>`;
  }

  gradeInputs.forEach((input) => {
    input.addEventListener('input', () => {
      // Keep the value sane as the user types, without fighting them mid-edit.
      if (input.value !== '' && !isNaN(Number(input.value))) {
        const clamped = clamp(Number(input.value), 0, 999);
        if (clamped !== Number(input.value) && input.value.length > 3) {
          input.value = String(clamped);
        }
      }
      markValidity(input);
      updateLiveAverage();
    });
    input.addEventListener('blur', () => markValidity(input));
  });

  updateLiveAverage();

  if (form) {
    form.addEventListener('submit', (event) => {
      let hasError = false;
      const nameInput = document.querySelector('#name');

      if (nameInput && nameInput.value.trim() === '') {
        nameInput.setAttribute('aria-invalid', 'true');
        hasError = true;
      }

      gradeInputs.forEach((input) => {
        const value = input.value.trim();
        const num = Number(value);
        if (value === '' || isNaN(num) || num < 0 || num > 100) {
          input.setAttribute('aria-invalid', 'true');
          hasError = true;
        }
      });

      if (hasError) {
        event.preventDefault();
        const firstInvalid = form.querySelector('[aria-invalid="true"]');
        if (firstInvalid) firstInvalid.focus();
      }
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      window.location.href = window.location.pathname;
    });
  }

  if (resultPanel) {
    resultPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
});
