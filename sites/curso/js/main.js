// ============================================================================
// main.js — renderiza o curso a partir de js/conteudo.js (fonte única).
// Não edite conteúdo aqui: edite apenas js/conteudo.js.
// ============================================================================

let progresso = 0;

const el = {
    main: document.getElementById('curso'),
    progressoBarra: document.getElementById('progressBar'),
    progressoTexto: document.getElementById('progressText'),
    botaoIniciar: document.getElementById('btnIniciar'),
    resultado: null,
    certificado: null,
    dataCertificado: null
};

// ---- Renderização dos módulos ---------------------------------------------

function renderModulos() {
    el.main.innerHTML = CURSO.modulos.map((mod, i) => `
        <section id="${mod.id}" class="modulo" aria-labelledby="${mod.id}-titulo">
            <article class="card">
                <header class="card-header ${mod.cor}">
                    <h2 id="${mod.id}-titulo" class="mb-0 h3">
                        <i class="bi ${mod.icone}" aria-hidden="true"></i> Módulo ${mod.numero}: ${mod.titulo}
                    </h2>
                </header>
                <div class="card-body">
                    <p class="fs-5">${mod.lead}</p>
                    <button id="btnMod${mod.numero}" class="btn btn-primary btn-lg" aria-expanded="false" aria-controls="mostraMod${mod.numero}">
                        ${mod.botaoFechado} <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                    </button>
                    <div id="mostraMod${mod.numero}" class="conteudo-expandido mt-3" aria-hidden="true" aria-live="polite"></div>
                </div>
            </article>
        </section>
    `).join('');
}

function renderQuiz() {
    const perguntas = CURSO.quiz.perguntas.map(q => `
        <div id="pergunta-${q.id}" class="mb-4" role="group" aria-labelledby="pergunta-${q.id}-titulo">
            <h3 id="pergunta-${q.id}-titulo" class="h6 mb-3">${q.pergunta}</h3>
            ${q.opcoes.map((o, i) => `
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="${q.id}" id="${q.id}${i}" value="${o.valor}">
                    <label class="form-check-label" for="${q.id}${i}">${o.texto}</label>
                </div>
            `).join('')}
        </div>
    `).join('');

    el.main.insertAdjacentHTML('beforeend', `
        <section id="quiz" class="modulo" aria-labelledby="quiz-titulo">
            <article class="card">
                <header class="card-header bg-danger">
                    <h2 id="quiz-titulo" class="mb-0 h3"><i class="bi bi-question-circle" aria-hidden="true"></i> ${CURSO.quiz.titulo}</h2>
                </header>
                <div class="card-body">
                    <p class="fs-5 mb-4">${CURSO.quiz.introducao}</p>
                    ${perguntas}
                    <button id="btnVerificar" class="btn btn-success btn-lg" aria-label="Verificar respostas do quiz">
                        ${CURSO.quiz.botao} <i class="bi bi-check-circle" aria-hidden="true"></i>
                    </button>
                    <div id="resultado" class="alert mt-3 d-none" role="alert" aria-live="polite"></div>
                </div>
            </article>
        </section>
    `);
}

function renderCertificado() {
    el.main.insertAdjacentHTML('beforeend', `
        <section id="certificado" class="modulo d-none" aria-labelledby="certificado-titulo">
            <article class="card border-warning">
                <header class="card-header bg-warning text-dark text-center">
                    <h2 id="certificado-titulo" class="mb-0"><i class="bi bi-award" aria-hidden="true"></i> ${CURSO.certificado.titulo}</h2>
                </header>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-shield-check display-1 text-warning" aria-hidden="true"></i>
                    </div>
                    <p class="lead fw-bold">Certificamos que este ser humano</p>
                    <h1 class="display-5 text-warning mb-3">enfrentou a realidade sem filtros</h1>
                    <p class="fs-5">e sobreviveu ao curso</p>
                    <h2 class="text-dark">"A Jornada da Consciência"</h2>
                    <p class="mt-4 fs-6">Comprovou compreensão brutal sobre:</p>
                    <ul class="list-unstyled fs-5">
                        ${CURSO.certificado.verificacoes.map(v => `
                            <li><i class="bi bi-check-circle text-success" aria-hidden="true"></i> ${v}</li>
                        `).join('')}
                    </ul>
                    <div class="alert alert-dark mt-4">
                        <strong>Realização:</strong> ${CURSO.certificado.realizacao}
                    </div>
                    <p class="mt-4 text-muted"><small>Data da confrontação: <span id="dataCertificado"></span></small></p>
                    <button onclick="window.print()" class="btn btn-primary btn-lg mt-3" aria-label="Imprimir certificado">
                        <i class="bi bi-printer" aria-hidden="true"></i> ${CURSO.certificado.botaoImprimir}
                    </button>
                </div>
            </article>
        </section>
    `);
    el.resultado = document.getElementById('resultado');
    el.certificado = document.getElementById('certificado');
    el.dataCertificado = document.getElementById('dataCertificado');
}

// ---- Interatividade ---------------------------------------------------------

function alternarElemento(botao, div, conteudo, textoAberto, textoFechado) {
    const aberto = div.classList.contains('ativar');
    const seta = aberto ? 'bi-arrow-up-circle' : 'bi-arrow-right-circle';

    if (aberto) {
        div.classList.remove('ativar');
        div.innerHTML = '';
        botao.classList.remove('btn-warning');
    } else {
        div.classList.add('ativar');
        div.innerHTML = conteudo;
        botao.classList.add('btn-warning');
    }
    botao.innerHTML = (aberto ? textoFechado : textoAberto) + ` <i class="bi ${seta}" aria-hidden="true"></i>`;
    div.setAttribute('aria-hidden', aberto ? 'true' : 'false');

    if (!aberto && conteudo.includes('accordion')) {
        div.querySelectorAll('.accordion-button').forEach(btn => {
            btn.setAttribute('aria-expanded', btn.classList.contains('collapsed') ? 'false' : 'true');
        });
    }
}

function bindModulos() {
    CURSO.modulos.forEach(mod => {
        const btn = document.getElementById(`btnMod${mod.numero}`);
        const div = document.getElementById(`mostraMod${mod.numero}`);
        btn.addEventListener('click', () => {
            const isExpanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!isExpanded));
            alternarElemento(btn, div, mod.html, mod.botaoAberto, mod.botaoFechado);
            atualizarProgresso();
            if (!isExpanded) {
                setTimeout(() => div.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
            }
        });
    });
}

function atualizarProgresso() {
    const total = CURSO.modulos.length;
    progresso = Math.min(progresso + (100 / total), 100);
    el.progressoBarra.style.width = progresso + '%';
    el.progressoTexto.textContent = Math.round(progresso) + '%';
    el.progressoBarra.setAttribute('aria-valuenow', String(Math.round(progresso)));
}

// ---- Tema claro / escuro ----------------------------------------------------

function temaEscuro() {
    document.body.style.backgroundColor = '#1a1a2e';
    document.body.style.color = '#e8e8e8';
    document.documentElement.setAttribute('data-theme', 'dark');
    localStorage.setItem('tema', 'escuro');
}

function temaClaro() {
    document.body.style.backgroundColor = '';
    document.body.style.color = '';
    document.documentElement.setAttribute('data-theme', 'light');
    localStorage.setItem('tema', 'claro');
}

// ---- Quiz -------------------------------------------------------------------

function verificarRespostas() {
    const perguntas = CURSO.quiz.perguntas;
    let acertos = 0;
    const feedback = [];

    perguntas.forEach((q, idx) => {
        const marcada = document.querySelector(`input[name="${q.id}"]:checked`);
        if (!marcada) {
            feedback.push(`⚠️ Você não respondeu a pergunta ${idx + 1}`);
            return;
        }
        if (marcada.value === 'certo') {
            acertos++;
            feedback.push(`✅ Pergunta ${idx + 1} correta`);
        } else {
            feedback.push(`❌ Pergunta ${idx + 1} errada`);
        }
    });

    const div = el.resultado;
    div.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');

    let titulo, classe;
    if (acertos === perguntas.length) {
        titulo = CURSO.quiz.feedbackSucesso;
        classe = 'alert-success';
    } else if (acertos >= perguntas.length - 1) {
        titulo = CURSO.quiz.feedbackQuase;
        classe = 'alert-warning';
    } else {
        titulo = CURSO.quiz.feedbackFalhou;
        classe = 'alert-danger';
    }

    div.className = `alert mt-3 ${classe}`;
    div.innerHTML = `
        <h5 class="alert-heading"><i class="bi ${acertos === perguntas.length ? 'bi-trophy' : acertos >= perguntas.length - 1 ? 'bi-exclamation-triangle' : 'bi-x-circle'}"></i> ${titulo}</h5>
        <p><strong>Acertos:</strong> ${acertos}/${perguntas.length}</p>
        <hr>
        <ul class="mb-0">${feedback.map(f => `<li>${f}</li>`).join('')}</ul>
    `;

    if (acertos === perguntas.length) {
        setTimeout(() => {
            el.certificado.classList.remove('d-none');
            el.dataCertificado.textContent = new Date().toLocaleDateString('pt-BR');
            window.scrollTo({ top: el.certificado.offsetTop, behavior: 'smooth' });
        }, 2000);
    }
}

// ---- Inicialização -----------------------------------------------------------

function inicializarEventListeners() {
    if (localStorage.getItem('tema') === 'escuro') {
        temaEscuro();
    }

    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, observerOptions);
    document.querySelectorAll('.modulo').forEach(modulo => observer.observe(modulo));

    document.querySelectorAll('#quiz input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const grupo = this.closest('.mb-4');
            grupo.querySelectorAll('.form-check-label').forEach(label => {
                label.classList.remove('fw-bold', 'text-success', 'text-danger');
            });
            this.nextElementSibling.classList.add('fw-bold', this.value === 'certo' ? 'text-success' : 'text-danger');
        });
    });

    el.botaoIniciar.addEventListener('click', () => {
        window.scrollTo({ top: document.getElementById('modulo1').offsetTop, behavior: 'smooth' });
    });

    document.getElementById('btnEscuro').addEventListener('click', temaEscuro);
    document.getElementById('btnClaro').addEventListener('click', temaClaro);

    document.getElementById('btnVerificar').addEventListener('click', verificarRespostas);
    bindModulos();
}

document.addEventListener('DOMContentLoaded', () => {
    renderModulos();
    renderQuiz();
    renderCertificado();
    inicializarEventListeners();
});
