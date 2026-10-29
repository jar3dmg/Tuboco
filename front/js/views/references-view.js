import { escapeHtml } from './view-utils.js';

const groups = [
    { id: 'courses', title: 'Cursos', description: 'Fuentes que respaldan las lecciones.' },
    { id: 'platform', title: 'Contenido de la plataforma', description: 'Asistencia de IA en el contenido.' },
    { id: 'images', title: 'Im&#225;genes/iconos', description: 'Portadas y recursos visuales.' },
];

/** Agrupa las referencias recibidas para mostrarlas dentro del diálogo. */
export function renderReferenceGroups(references) {
    return groups.map(group => {
        const entries = references.filter(reference => reference.reference_group === group.id);
        const content = entries.length
            ? entries.map(reference => `<article class="reference-entry"><h4>${reference.url ? `<a href="${escapeHtml(reference.url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(reference.title)}</a>` : escapeHtml(reference.title)}</h4><p class="reference-context">${escapeHtml(reference.course_title)}</p><p>${escapeHtml(reference.notes || '')}</p></article>`).join('')
            : '<p class="reference-group-empty">No hay referencias en esta secci&#243;n.</p>';
        return `<section class="reference-group"><header class="reference-group-head"><div><h3>${group.title}</h3><p>${group.description}</p></div><span>${entries.length}</span></header><div class="reference-group-list">${content}</div></section>`;
    }).join('');
}
