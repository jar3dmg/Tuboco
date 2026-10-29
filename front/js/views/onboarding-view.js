import { goals, interests } from '../catalog.js';
import { escapeHtml, icon } from './view-utils.js';

/** Muestra un paso del cuestionario inicial y conecta sus acciones. */
export function renderOnboarding(container, step, answers, { submit, back, logout }) {
    const content = step === 1
        ? `<span class="eyebrow">Paso 1 · Tu entorno</span><h1>Cuéntanos dónde crece tu vida verde.</h1><p class="hint">Esto nos ayuda a recomendarte mejores cursos.</p><div class="choices"><label class="choice"><span class="choice-icon">${icon('garden','icon-lg')}</span><input type="checkbox" name="has_garden" ${answers.has_garden?'checked':''}><b>Tengo jardín</b><small>Plantas al aire libre</small></label><label class="choice"><span class="choice-icon">${icon('pot','icon-lg')}</span><input type="checkbox" name="has_pots" ${answers.has_pots?'checked':''}><b>Tengo macetas</b><small>Plantas dentro o fuera de casa</small></label></div>`
        : step === 2
            ? `<span class="eyebrow">Paso 2 · Tus intereses</span><h1>¿Qué te gustaría explorar?</h1><p class="hint">Elige todos los temas que despierten tu curiosidad.</p><div class="choices">${interests.map((interest,index)=>`<label class="choice"><span class="choice-icon">${icon(['leaf','garden','pot','sun','heart','book'][index],'icon-lg')}</span><input type="checkbox" name="interest" value="${escapeHtml(interest)}" ${answers.interests.includes(interest)?'checked':''}><b>${escapeHtml(interest)}</b></label>`).join('')}</div>`
            : `<span class="eyebrow">Paso 3 · Tu intención</span><h1>¿Qué quieres lograr con Tuboco?</h1><p class="hint">Elegiremos cursos que se adapten a ti.</p><div class="choices">${goals.map((goal,index)=>`<label class="choice"><span class="choice-icon">${icon(['leaf','sun','garden','book'][index],'icon-lg')}</span><input type="radio" name="goal" value="${escapeHtml(goal)}" ${answers.goal===goal?'checked':''}><b>${escapeHtml(goal)}</b></label>`).join('')}</div>`;

    container.innerHTML = `<header class="topbar"><span class="logo"><img class="project-logo-image" src="front/images/tuboco-logo.png" alt=""><span>TUBOCO<small>tu botánica de confianza</small></span></span><button class="secondary" data-logout>Salir</button></header><section class="center cold-wrap"><div class="steps">${[1,2,3].map(number=>`<i class="step ${number<=step?'on':''}"></i>`).join('')}</div><article class="card"><form data-onboarding>${content}<div data-form-message></div><div class="actions">${step>1?'<button type="button" class="secondary" data-back>Regresar</button>':'<span></span>'}<button class="primary">${step===3?'Ver mis cursos':`Continuar ${icon('arrow')}`}</button></div></form></article></section>`;
    container.querySelector('[data-logout]').onclick = logout;
    container.querySelector('[data-back]')?.addEventListener('click', back);
    container.querySelector('[data-onboarding]').onsubmit = event => { event.preventDefault(); submit(new FormData(event.currentTarget)); };
}
