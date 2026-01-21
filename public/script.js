document.addEventListener('DOMContentLoaded', () => {
  // Crée le switch Universe.io si pas déjà présent
  if (!document.querySelector('.switch')) {
    const label = document.createElement('label');
    label.className = 'switch';

    const input = document.createElement('input');
    input.type = 'checkbox';

    const span = document.createElement('span');
    span.className = 'slider';

    label.appendChild(input);
    label.appendChild(span);
    document.body.appendChild(label);

    // Par défaut : mode clair (soleil visible)
    input.checked = false;
    document.body.classList.remove('dark-mode');

    // Action au clic
    input.addEventListener('change', () => {
      if (input.checked) {
        // Mode sombre activé → lune
        document.body.classList.add('dark-mode');
      } else {
        // Mode clair activé → soleil
        document.body.classList.remove('dark-mode');
      }
    });
  }
});