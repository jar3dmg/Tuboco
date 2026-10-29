/** Convierte texto externo en contenido seguro para una plantilla HTML. */
export const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
export const icon = (name, size = '') => `<img class="icon ${size}" src="front/icons/${name}.svg" alt="" aria-hidden="true">`;

const courseCovers = {
    'botanica-desde-cero': 'front/images/botanica-desde-cero-cover.png',
    'plantas-felices-en-casa': 'front/images/plantas-felices-en-casa-cover.png',
    'tu-primer-huerto': 'front/images/tu-primer-huerto-cover.png',
};

export const courseCoverUrl = course => course?.cover_image_url || courseCovers[course?.slug] || '';

/** Inserta o reemplaza el mensaje contextual de una sección. */
export function showMessage(container, message, kind = 'error') {
    const existing = container.querySelector('[data-message]');
    if (existing) existing.remove();
    const node = document.createElement('p');
    node.dataset.message = kind;
    node.className = kind === 'error' ? 'error' : 'success-message';
    node.textContent = message;
    container.prepend(node);
}
