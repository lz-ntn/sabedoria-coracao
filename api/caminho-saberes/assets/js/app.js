/* ==========================================
   Aplicação Principal - O Caminho
   PHP + MySQL | Versão 2.0
   ========================================== */

(function() {
    'use strict';

    // ==========================================
    // UTILITÁRIOS
    // ==========================================
    const Utils = {
        escaparHTML(texto) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(texto));
            return div.innerHTML;
        },

        debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    };

    // ==========================================
    // TEMA (Dark/Light)
    // ==========================================
    const Tema = {
        init() {
            const temaSalvo = localStorage.getItem('tema');
            const toggleBtn = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');

            if (temaSalvo === 'claro') {
                document.body.classList.add('modo-claro');
                if (themeIcon) themeIcon.className = 'bi bi-sun';
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    const isClaro = document.body.classList.contains('modo-claro');
                    document.body.classList.toggle('modo-claro');
                    const novo = isClaro ? 'escuro' : 'claro';
                    localStorage.setItem('tema', novo);
                    if (themeIcon) {
                        themeIcon.className = isClaro ? 'bi bi-moon-stars' : 'bi bi-sun';
                    }
                });
            }
        }
    };

    // ==========================================
    // ACCORDION
    // ==========================================
    const Accordion = {
        init(selector) {
            const items = document.querySelectorAll(selector);
            if (!items.length) return;

            items.forEach(item => {
                const header = item.querySelector('.accordion-header');
                const content = item.querySelector('.accordion-content');
                if (!header || !content) return;

                if (item.classList.contains('active')) {
                    content.style.maxHeight = content.scrollHeight + 'px';
                    header.setAttribute('aria-expanded', 'true');
                }

                header.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');

                    // Fechar todos
                    items.forEach(i => {
                        i.classList.remove('active');
                        const c = i.querySelector('.accordion-content');
                        if (c) c.style.maxHeight = '0';
                        const h = i.querySelector('.accordion-header');
                        if (h) h.setAttribute('aria-expanded', 'false');
                    });

                    // Abrir o clicado
                    if (!isActive) {
                        item.classList.add('active');
                        content.style.maxHeight = content.scrollHeight + 'px';
                        header.setAttribute('aria-expanded', 'true');
                    }
                });
            });
        }
    };

    // ==========================================
    // TABS
    // ==========================================
    const Tabs = {
        init(containerSelector) {
            const containers = document.querySelectorAll(containerSelector);
            if (!containers.length) return;

            containers.forEach(container => {
                const botoes = container.querySelectorAll('.tab-btn');
                const contents = container.querySelectorAll('.tab-content');

                botoes.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const tabTarget = btn.dataset.tabTarget;
                        botoes.forEach(b => b.classList.remove('active'));
                        contents.forEach(c => c.classList.remove('active'));
                        btn.classList.add('active');

                        const content = container.querySelector(`[data-tab="${tabTarget}"]`);
                        if (content) content.classList.add('active');
                    });
                });
            });
        }
    };

    // ==========================================
    // SCROLL SPY
    // ==========================================
    const ScrollSpy = {
        init() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');
            if (!sections.length || !navLinks.length) return;

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        navLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.dataset.section === id) {
                                link.classList.add('active');
                            }
                        });

                        // Atualizar breadcrumb
                        const bc = document.getElementById('breadcrumb-current');
                        if (bc) {
                            const h2 = entry.target.querySelector('h2');
                            if (h2) bc.textContent = h2.textContent.trim();
                        }
                    }
                });
            }, { threshold: 0.3, rootMargin: '-80px 0px -50% 0px' });

            sections.forEach(section => observer.observe(section));
        }
    };

    // ==========================================
    // ANIMAÇÕES DE SCROLL
    // ==========================================
    const AnimacoesScroll = {
        init() {
            const elementos = document.querySelectorAll('.animacao-entrada');
            if (!elementos.length) return;

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            elementos.forEach(el => observer.observe(el));
        }
    };

    // ==========================================
    // TIMER DE MEDITAÇÃO
    // ==========================================
    const TimerMeditacao = {
        minutos: 10,
        segundos: 0,
        intervalo: null,
        rodando: false,

        init() {
            const display = document.getElementById('timer-display');
            const startBtn = document.getElementById('timer-start');
            const resetBtn = document.getElementById('timer-reset');
            const presets = document.querySelectorAll('.timer-preset');

            if (!display) return;

            this.atualizarDisplay();

            presets.forEach(btn => {
                btn.addEventListener('click', () => {
                    if (this.rodando) return;
                    presets.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    this.minutos = parseInt(btn.dataset.minutes);
                    this.segundos = 0;
                    this.atualizarDisplay();
                });
            });

            if (startBtn) {
                startBtn.addEventListener('click', () => {
                    if (this.rodando) {
                        this.parar();
                        startBtn.innerHTML = '<i class="bi bi-play-fill"></i> Continuar';
                    } else {
                        this.iniciar();
                        startBtn.innerHTML = '<i class="bi bi-pause-fill"></i> Pausar';
                    }
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    this.parar();
                    this.minutos = parseInt(document.querySelector('.timer-preset.active')?.dataset.minutes || '10');
                    this.segundos = 0;
                    this.atualizarDisplay();
                    if (startBtn) startBtn.innerHTML = '<i class="bi bi-play-fill"></i> Iniciar';
                });
            }
        },

        iniciar() {
            this.rodando = true;
            this.intervalo = setInterval(() => {
                if (this.segundos === 0) {
                    if (this.minutos === 0) {
                        this.finalizar();
                        return;
                    }
                    this.minutos--;
                    this.segundos = 59;
                } else {
                    this.segundos--;
                }
                this.atualizarDisplay();
            }, 1000);
        },

        parar() {
            this.rodando = false;
            if (this.intervalo) {
                clearInterval(this.intervalo);
                this.intervalo = null;
            }
        },

        finalizar() {
            this.parar();
            this.atualizarDisplay();
            const startBtn = document.getElementById('timer-start');
            if (startBtn) startBtn.innerHTML = '<i class="bi bi-play-fill"></i> Iniciar';

            // Notificar
            if (Notification.permission === 'granted') {
                new Notification('🧘 Meditação concluída!', {
                    body: 'Bom trabalho! Aproveite esse estado de presença.'
                });
            }
        },

        atualizarDisplay() {
            const display = document.getElementById('timer-display');
            if (display) {
                const m = String(this.minutos).padStart(2, '0');
                const s = String(this.segundos).padStart(2, '0');
                display.textContent = `${m}:${s}`;
            }
        }
    };

    // ==========================================
    // BARRA DE PROGRESSO (scroll)
    // ==========================================
    const BarraProgresso = {
        init() {
            if (document.getElementById('barra-progresso')) return;
            const barra = document.createElement('div');
            barra.id = 'barra-progresso';
            barra.innerHTML = '<div id="barra-progresso-fill"></div>';
            document.body.appendChild(barra);

            window.addEventListener('scroll', () => {
                const scrollTop = window.scrollY;
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const progresso = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                const fill = document.getElementById('barra-progresso-fill');
                if (fill) fill.style.width = Math.min(progresso, 100) + '%';
            });
        }
    };

    // ==========================================
    // BACK TO TOP
    // ==========================================
    const BackToTop = {
        init() {
            const btn = document.getElementById('back-to-top');
            if (!btn) return;

            window.addEventListener('scroll', () => {
                btn.classList.toggle('visible', window.scrollY > 500);
            });

            btn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    };

    // ==========================================
    // SISTEMA DE PROGRESSO
    // ==========================================
    const Aprendizado = {
        init() {
            this.inicializarBotoes();
            this.atualizarProgresso();
            this.carregarDoServidor();
        },

        inicializarBotoes() {
            document.querySelectorAll('.btn-marcar').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const lesson = e.currentTarget.dataset.lesson;
                    if (lesson) {
                        await this.marcarConcluido(lesson, e.currentTarget);
                    }
                });
            });
        },

        async carregarDoServidor() {
            try {
                const data = await window.API.buscarProgresso();
                if (data && data.categorias) {
                    localStorage.setItem('progressoSaberes_php', JSON.stringify(data));
                    this.atualizarProgresso();
                }
            } catch (e) {
                console.log('Usando localStorage como fallback');
            }
        },

        async marcarConcluido(lesson, btn) {
            const categoria = btn.dataset.categoria;

            // Atualizar UI imediatamente
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Concluído';
            btn.disabled = true;
            btn.classList.add('completed');

            // Salvar no servidor
            try {
                await window.API.salvarProgresso(lesson, categoria);
            } catch (e) {
                console.log('Progresso salvo apenas localmente');
            }

            // Salvar no localStorage (fallback)
            const progresso = JSON.parse(localStorage.getItem('progressoSaberes') || '{}');
            if (!progresso[lesson]) {
                progresso[lesson] = { concluido: true, data: new Date().toISOString() };
                localStorage.setItem('progressoSaberes', JSON.stringify(progresso));
            }

            this.atualizarProgresso();
        },

        atualizarProgresso() {
            // Atualizar indicadores das lições
            document.querySelectorAll('.lesson-indicator').forEach(ind => {
                const item = ind.closest('.accordion-item');
                if (item) {
                    const lessonId = item.dataset.lesson;
                    const progresso = JSON.parse(localStorage.getItem('progressoSaberes') || '{}');
                    if (lessonId && progresso[lessonId]) {
                        ind.innerHTML = '<i class="bi bi-check-circle-fill" style="color:var(--cor-destaque)"></i>';
                    }
                }
            });

            // Atualizar barras de progresso por categoria
            const categorias = {};
            document.querySelectorAll('.accordion-item').forEach(item => {
                const cat = item.dataset.category;
                if (cat) {
                    if (!categorias[cat]) categorias[cat] = [];
                    categorias[cat].push(item.dataset.lesson);
                }
            });

            const progresso = JSON.parse(localStorage.getItem('progressoSaberes') || '{}');

            for (const [cat, licoes] of Object.entries(categorias)) {
                const total = licoes.length;
                const concluidas = licoes.filter(id => progresso[id]).length;
                const pct = total > 0 ? Math.round((concluidas / total) * 100) : 0;

                const statusEl = document.getElementById(`status-${cat}`);
                if (statusEl) {
                    statusEl.textContent = pct + '%';
                    statusEl.classList.toggle('completed', pct === 100);
                }

                const fillEl = document.querySelector(`[data-category="${cat}"] .progress-fill`);
                if (fillEl) fillEl.style.width = pct + '%';
            }
        }
    };

    // ==========================================
    // BUSCA
    // ==========================================
    const Search = {
        data: [],

        init() {
            const toggle = document.getElementById('search-toggle');
            const modal = document.getElementById('search-modal');
            const input = document.getElementById('search-input');
            const results = document.getElementById('search-results');
            const close = modal?.querySelector('.modal-close');

            if (!toggle || !modal) return;

            // Construir dados de busca a partir do APP_DATA
            this.data = (window.APP_DATA?.licoes || []).map(l => ({
                title: l.titulo,
                desc: l.conteudo?.substring(0, 100) || '',
                categoria: l.categoria_nome,
                link: `#conhecimento`
            }));

            toggle.addEventListener('click', () => {
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                setTimeout(() => input?.focus(), 100);
            });

            if (close) {
                close.addEventListener('click', () => this.close());
            }

            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.close();
            });

            if (input) {
                input.addEventListener('input', Utils.debounce(function() {
                    const query = this.value.toLowerCase().trim();
                    results.innerHTML = '';

                    if (query.length < 2) return;

                    const filtered = Search.data.filter(item =>
                        item.title.toLowerCase().includes(query) ||
                        item.desc.toLowerCase().includes(query) ||
                        (item.categoria && item.categoria.toLowerCase().includes(query))
                    );

                    if (filtered.length === 0) {
                        results.innerHTML = '<p style="text-align:center;opacity:0.6">Nenhum resultado encontrado</p>';
                        return;
                    }

                    filtered.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'search-result-item';
                        div.addEventListener('click', () => {
                            window.location.hash = item.link;
                            Search.close();
                        });

                        const h4 = document.createElement('h4');
                        h4.textContent = item.title;
                        const p = document.createElement('p');
                        p.textContent = item.desc;
                        const span = document.createElement('span');
                        span.className = 'search-categoria';
                        span.textContent = item.categoria || '';

                        div.appendChild(h4);
                        div.appendChild(p);
                        if (item.categoria) div.appendChild(span);
                        results.appendChild(div);
                    });
                }, 300));
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('open')) {
                    this.close();
                }
            });
        },

        close() {
            const modal = document.getElementById('search-modal');
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            }
        }
    };

    // ==========================================
    // NEWSLETTER
    // ==========================================
    const Newsletter = {
        init() {
            const form = document.getElementById('newsletter-form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('newsletter-email').value;
                const messageEl = document.getElementById('newsletter-message');

                if (!email || !email.includes('@')) {
                    this.mostrarMensagem('Por favor, insira um email válido.', 'error');
                    return;
                }

                try {
                    await window.API.inscreverNewsletter(email);
                    this.mostrarMensagem('Obrigado! Inscrição realizada com sucesso!', 'success');
                    form.reset();
                } catch (e) {
                    if (e.message.includes('já existe') || e.message.includes('já inscrito')) {
                        this.mostrarMensagem('Este email já está inscrito.', 'error');
                    } else {
                        // Fallback localStorage
                        this.salvarLocal(email);
                    }
                }
            });
        },

        salvarLocal(email) {
            const subs = JSON.parse(localStorage.getItem('newsletterSubs') || '[]');
            if (!subs.includes(email)) {
                subs.push(email);
                localStorage.setItem('newsletterSubs', JSON.stringify(subs));
            }
            this.mostrarMensagem('Inscrição salva localmente!', 'success');
        },

        mostrarMensagem(texto, tipo) {
            const el = document.getElementById('newsletter-message');
            if (!el) return;
            el.textContent = texto;
            el.className = `newsletter-message ${tipo}`;
            setTimeout(() => {
                el.textContent = '';
                el.className = 'newsletter-message';
            }, 5000);
        }
    };

    // ==========================================
    // QUIZ
    // ==========================================
    const Quiz = {
        container: null,
        perguntas: [],
        indice: 0,
        pontuacao: 0,
        acertos: 0,
        respostas: [],

        async init() {
            this.container = document.getElementById('quiz-container');
            if (!this.container) return;

            this.container.innerHTML = '<div class="quiz-loading"><i class="bi bi-arrow-clockwise"></i> Carregando perguntas...</div>';

            try {
                const data = await window.API.buscarPerguntas();
                if (data && data.perguntas) {
                    this.perguntas = data.perguntas;
                    this.indice = 0;
                    this.pontuacao = 0;
                    this.acertos = 0;
                    this.respostas = [];
                    this.renderizar();
                } else {
                    this.container.innerHTML = '<p>Não foi possível carregar o quiz. Tente novamente.</p>';
                }
            } catch (e) {
                this.container.innerHTML = '<p>Erro ao carregar quiz. Servidor offline?</p>';
            }
        },

        renderizar() {
            if (this.indice >= this.perguntas.length) {
                this.mostrarResultado();
                return;
            }

            const p = this.perguntas[this.indice];
            const progresso = ((this.indice) / this.perguntas.length) * 100;

            this.container.innerHTML = `
                <div class="quiz-wrapper">
                    <div class="quiz-progresso">
                        <div class="quiz-progress-bar">
                            <div class="quiz-progress-fill" style="width:${progresso}%"></div>
                        </div>
                        <div class="quiz-info">
                            <span>Pergunta ${this.indice + 1} de ${this.perguntas.length}</span>
                            <span>Pontos: ${this.pontuacao}</span>
                        </div>
                    </div>
                    <div class="quiz-pergunta">${Utils.escaparHTML(p.pergunta)}</div>
                    <div class="quiz-opcoes">
                        ${p.opcoes.map((op, i) => `
                            <button class="quiz-opcao" data-indice="${i}">
                                <span class="opcao-letra">${String.fromCharCode(65 + i)}</span>
                                ${Utils.escaparHTML(op)}
                            </button>
                        `).join('')}
                    </div>
                    <div class="quiz-feedback"></div>
                </div>
            `;

            this.container.querySelectorAll('.quiz-opcao').forEach(btn => {
                btn.addEventListener('click', (e) => this.responder(parseInt(e.currentTarget.dataset.indice)));
            });
        },

        responder(escolhida) {
            const p = this.perguntas[this.indice];
            const opcoes = this.container.querySelectorAll('.quiz-opcao');
            const feedback = this.container.querySelector('.quiz-feedback');

            opcoes.forEach(op => op.disabled = true);

            const correto = escolhida === p.correta;
            if (correto) {
                this.pontuacao += 10;
                this.acertos++;
                opcoes[escolhida].classList.add('correto');
                feedback.innerHTML = '<div class="quiz-feedback correto"><i class="bi bi-check-circle-fill"></i> Correto! +10 pontos</div>';
            } else {
                opcoes[escolhida].classList.add('incorreto');
                opcoes[p.correta].classList.add('correto');
                feedback.innerHTML = `<div class="quiz-feedback incorreto"><i class="bi bi-x-circle-fill"></i> Resposta: ${Utils.escaparHTML(p.opcoes[p.correta])}</div>`;
            }

            this.respostas.push({
                pergunta: p.pergunta,
                escolhida: p.opcoes[escolhida],
                correta: p.opcoes[p.correta],
                acertou: correto
            });

            // Avançar após 1.5s
            setTimeout(() => {
                this.indice++;
                this.renderizar();
            }, 1500);
        },

        mostrarResultado() {
            const total = this.perguntas.length;
            const pct = total > 0 ? Math.round((this.acertos / total) * 100) : 0;

            let emoji = '💪', mensagem = 'Continue estudando!';
            if (pct >= 80) { emoji = '🌟'; mensagem = 'Excelente! Você domina o conteúdo!'; }
            else if (pct >= 60) { emoji = '👍'; mensagem = 'Bom trabalho! Continue assim.'; }
            else if (pct >= 40) { emoji = '📖'; mensagem = 'Você está no caminho certo!'; }

            // Salvar resultado no servidor
            window.API.salvarResultadoQuiz(this.acertos, total, this.pontuacao, this.respostas);

            this.container.innerHTML = `
                <div class="quiz-resultado">
                    <div class="resultado-emoji">${emoji}</div>
                    <h3>${mensagem}</h3>
                    <div class="resultado-estatisticas">
                        <div class="stat-item">
                            <span class="stat-num">${this.acertos}</span>
                            <span class="stat-label">Acertos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-num">${total - this.acertos}</span>
                            <span class="stat-label">Erros</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-num">${this.pontuacao}</span>
                            <span class="stat-label">Pontos</span>
                        </div>
                    </div>
                    <div class="resultado-botoes">
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="bi bi-arrow-counterclockwise"></i> Tentar Novamente
                        </button>
                    </div>
                </div>
            `;
        }
    };

    // ==========================================
    // INICIALIZAÇÃO GLOBAL
    // ==========================================
    function inicializar() {
        BarraProgresso.init();
        Tema.init();
        Accordion.init('.accordion-item');
        Tabs.init('.tabs-container');
        ScrollSpy.init();
        AnimacoesScroll.init();
        Aprendizado.init();
        Search.init();
        Newsletter.init();
        TimerMeditacao.init();
        BackToTop.init();
        Quiz.init();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }
})();
