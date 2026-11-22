// =====================================================
// PROJETO FINAL: Portal Teosófico
// Integrando HTML, CSS e JavaScript
// =====================================================

document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initPrinciplesTable();
    initKarmaWheel();
    initMeditation();
});

// ------------------------------------------------------------
// NAVEGAÇÃO COM EVENTOS
// ------------------------------------------------------------

function initNavigation() {
    const navButtons = document.querySelectorAll('.nav-btn');
    const sections = document.querySelectorAll('.content-section');
    
    navButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active de todos
            navButtons.forEach(b => b.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            
            // Ativa o clicked
            btn.classList.add('active');
            const sectionId = btn.dataset.section;
            document.getElementById(sectionId).classList.add('active');
            
            // Salva no histórico
            saveToHistory(sectionId);
        });
    });
    
    // Verifica URL para restaurar seção
    restoreFromURL();
}

function saveToHistory(sectionId) {
    const url = new URL(window.location);
    url.searchParams.set('section', sectionId);
    window.history.replaceState({}, '', url);
}

function restoreFromURL() {
    const params = new URLSearchParams(window.location.search);
    const section = params.get('section');
    
    if (section) {
        const btn = document.querySelector(`[data-section="${section}"]`);
        if (btn) {
            btn.click();
        }
    }
}

// ------------------------------------------------------------
// TABELA INTERATIVA DOS PRINCÍPIOS
// ------------------------------------------------------------

function initPrinciplesTable() {
    const rows = document.querySelectorAll('.table-row');
    
    const principleDetails = {
        atma: {
            title: "Atma - O Espírito Divino",
            desc: "O sétimo princípio, a centelha divina que é parte do Absoluto. Não pode ser destruído, pois é eterno.",
            color: "#9b59b6"
        },
        buddhi: {
            title: "Buddhi - Alma Espiritual", 
            desc: "O veículo da iluminação. Transmuta o conhecimento intelectual em sabedoria intuitiva.",
            color: "#8e44ad"
        },
        manas: {
            title: "Manas - Mente",
            desc: "O Pensador, a individualidade que conecta o mundo físico ao espiritual.",
            color: "#3498db"
        },
        kama: {
            title: "Kama - Desejo/Vontade",
            desc: "O princípio do desejo e da vontade que impulsiona a busca pela evolução.",
            color: "#e67e22"
        },
        linga: {
            title: "Linga Sharira - Corpo Astral",
            desc: "O modelo ou molde do corpo físico, existente no plano astral.",
            color: "#27ae60"
        },
        prana: {
            title: "Prana - Princípio Vital",
            desc: "A energia vital que sostiene todos os processos de vida.",
            color: "#f39c12"
        },
        sthula: {
            title: "Sthula Sharira - Corpo Físico",
            desc: "O veículo material, composto pelos cinco elementos sutis.",
            color: "#c0392b"
        }
    };
    
    rows.forEach(row => {
        row.addEventListener('click', () => {
            const principle = row.dataset.principle;
            const details = principleDetails[principle];
            
            if (details) {
                showPrincipleModal(details);
            }
        });
        
        row.style.cursor = 'pointer';
    });
}

function showPrincipleModal(details) {
    // Remove.modal anterior se existir
    const existing = document.querySelector('.principle-modal');
    if (existing) existing.remove();
    
    const modal = document.createElement('div');
    modal.className = 'principle-modal';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="this.remove()"></div>
        <div class="modal-content">
            <button class="modal-close" onclick="this.parentElement.parentElement.remove()">×</button>
            <h3 style="color: ${details.color}">${details.title}</h3>
            <p>${details.desc}</p>
        </div>
    `;
    
    // Estilos do modal
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    const overlay = modal.querySelector('.modal-overlay');
    overlay.style.cssText = `
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
    `;
    
    const content = modal.querySelector('.modal-content');
    content.style.cssText = `
        background: white;
        padding: 2rem;
        border-radius: 12px;
        max-width: 400px;
        position: relative;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    `;
    
    const closeBtn = modal.querySelector('.modal-close');
    closeBtn.style.cssText = `
        position: absolute;
        top: 10px;
        right: 15px;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
    `;
    
    document.body.appendChild(modal);
}

// ------------------------------------------------------------
// RODA DO KARMA ANIMADA
// ------------------------------------------------------------

function initKarmaWheel() {
    const wheel = document.querySelector('.karma-wheel');
    
    if (!wheel) return;
    
    wheel.addEventListener('click', () => {
        // Rotação aleatória para simular o destino
        const randomRotation = 360 + Math.floor(Math.random() * 720);
        wheel.style.transition = 'transform 1s ease-out';
        wheel.style.transform = `rotate(${randomRotation}deg)`;
        
        setTimeout(() => {
            wheel.style.transition = 'none';
        }, 1000);
    });
    
    wheel.style.cursor = 'pointer';
}

// ------------------------------------------------------------
// MEDITAÇÃO GUIADA
// ------------------------------------------------------------

function initMeditation() {
    const steps = document.querySelectorAll('.meditation-steps li');
    let currentStep = 0;
    
    const timerDisplay = document.createElement('div');
    timerDisplay.className = 'meditation-timer';
    timerDisplay.innerHTML = `
        <div class="timer-display">
            <span class="timer-label">Tempo restante:</span>
            <span class="timer-value">15:00</span>
        </div>
        <button class="timer-btn" onclick="toggleTimer()">
            🧘 Iniciar Meditação
        </button>
    `;
    
    // Estilos
    timerDisplay.style.cssText = `
        margin-top: 1.5rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #1a5276, #27ae60);
        border-radius: 12px;
        color: white;
        text-align: center;
    `;
    
    const guideSection = document.querySelector('#pratica .meditation-guide');
    if (guideSection) {
        guideSection.appendChild(timerDisplay);
    }
}

// ------------------------------------------------------------
// LOG NO CONSOLE
// ------------------------------------------------------------

console.log('%c🧘 PORTAL TEOSÓFICO - PROJETO FINAL 🧘', 
    'font-size: 24px; color: #27ae60; font-weight: bold;');
console.log('%cIntegrando: HTML, CSS e JavaScript', 
    'font-size: 14px; color: #1a5276;');
console.log('%c"Estudo, Meditação e Serviço"', 
    'font-style: italic; color: #8e44ad;');

// ------------------------------------------------------------
// VERIFICAÇÃO FINAL
// ------------------------------------------------------------

function verifyProject() {
    const checkList = {
        'HTML Semântico': document.querySelectorAll('header, main, section, article, nav, footer').length > 0,
        'CSS Avançado': !!document.querySelector('.principles-grid'),
        'JavaScript Interativo': typeof initNavigation === 'function',
        'Conteúdo Teosófico': document.body.textContent.includes('Karma') || 
                             document.body.textContent.includes('reencarnação')
    };
    
    console.log('%c=== Verificação do Projeto ===', 'font-size: 16px; font-weight: bold;');
    
    for (let [item, passed] of Object.entries(checkList)) {
        console.log(`${passed ? '✓' : '✗'} ${item}`);
    }
}

// Executa verificação
verifyProject();