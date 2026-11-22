// =====================================================
// LICÃO 4: JavaScript - A Consciência Interativa
// =====================================================

// ------------------------------------------------------------
// CONCEITO 1: Variáveis - As "Sementes" do Código
// ------------------------------------------------------------

// Na Teosofia, o Karma plantado hoje floresce no futuro.
// Em JavaScript, variáveis guardam valores que usamos depois.

let atmico = "Atma - O Espírito Divino";
const buddhi = "Buddhi - A Alma Espiritual";
let manas = "Manas - A Mente";

// Demonstrando variáveis
document.querySelectorAll('.var-box').forEach(box => {
    box.addEventListener('click', () => {
        const name = box.dataset.name;
        let message = '';
        
        switch(name) {
            case 'atman':
                message = 'O núcleo divino que nunca morre. É a centelha espiritual.';
                break;
            case 'buddhi':
                message = 'O veículo da Alma Espiritual, que transmuta o intelectual em intuitivo.';
                break;
            case 'manas':
                message = 'A mente individual que conecta o mundo físico ao espiritual.';
                break;
        }
        
        alert(message);
    });
});

// ------------------------------------------------------------
// CONCEITO 2: Funções - As "Meditações" do Código
// ------------------------------------------------------------

// Funções são como práticas de meditação:
// você Define (cria) e pode executar repetidamente.

function executarMeditacao() {
    const tempo = 15;
    const elementos = [
        "Respiração consciente",
        "Observação dos pensamentos",
        "Foco na essência interior",
        "Conexão com o TODO"
    ];
    
    let mensagem = `🧘 Meditação de ${tempo} minutos:\n\n`;
    elementos.forEach((etapa, i) => {
        mensagem += `${i + 1}. ${etapa}\n`;
    });
    
    document.getElementById('meditacao-resultado').innerHTML = `
        <div class="meditation-result">
            ${mensagem}
        </div>
    `;
}

// ------------------------------------------------------------
// CONCEITO 3: Eventos - O "Karma" do JavaScript
// ------------------------------------------------------------

// Na Lei do Karma: toda ação gera uma reação.
// Em JavaScript: Eventos (ações) triggers Respostas (handlers).

const acoesKarmicas = {
    estudo: {
        emoji: "📖",
        texto: "Estudar a Sabedoria Antiga",
        consequencia: "O conhecimento ilumina a consciência"
    },
    meditar: {
        emoji: "🧘", 
        texto: "Meditar em silêncio",
        consequencia: "A mente encontra paz interior"
    },
    servir: {
        emoji: "🤝",
        texto: "Servir ao próximo",
        consequencia: "O amor se manifesta em gratidão"
    }
};

document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const acao = btn.dataset.action;
        const dados = acoesKarmicas[acao];
        
        // O "Karma" - a consequência
        const resultado = document.getElementById('karma-resultado');
        resultado.innerHTML = `
            <div class="karma-reaction">
                <span class="acao-emoji">${dados.emoji}</span>
                <strong>${dados.texto}</strong>
                <span class="consequencia">💫 ${dados.consequencia}</span>
            </div>
        `;
        
        // Animar o resultado
        resultado.style.animation = 'none';
        resultado.offsetHeight; // Trigger reflow
        resultado.style.animation = 'karma-pulse 0.5s ease-out';
    });
});

// ------------------------------------------------------------
// CONCEITO 4: Objetos - As "Hierarquias" do Código
// ------------------------------------------------------------

// Objetos são como hierarquias espirituais:
// contêm múltiplas propriedades e métodos relacionados.

const hierarquiaEspiritual = {
    mahatma: {
        nivel: 7,
        titulo: "Mahatma",
        descricao: "Ser que alcançou a iluminação completa",
        cor: "#9b59b6"
    },
    adepto: {
        nivel: 6,
        titulo: "Adepto",
        descricao: "Mestre nas ciências ocultas",
        cor: "#8e44ad"
    },
    iniciado: {
        nivel: 5,
        titulo: "Iniciado",
        descricao: "Estudante dedicados com compreensão profunda",
        cor: "#3498db"
    },
    estudante: {
        nivel: 4,
        titulo: "Estudante",
        descricao: "Buscador sincero da verdade",
        cor: "#2980b9"
    }
};

console.log("=== Hierarquia Espiritual ===");
for (let nivel in hierarquiaEspiritual) {
    console.log(`Nível ${hierarquiaEspiritual[nivel].nivel}: ${hierarquiaEspiritual[nivel].titulo}`);
}

// ------------------------------------------------------------
// CONCEITO 5: Arrays - As "Encarnações" Sucessivas
// ------------------------------------------------------------

// Arrays são como as séries de encarnações que a alma passa.

const rodadasEvolutivas = [
    "1ª Ronda - Homens-Renda (Roupa de Pele)",
    "2ª Ronda - Hyperbóreos",
    "3ª Ronda - Lemurianos",
    "4ª Ronda - Atlantes", 
    "5ª Ronda - Arianos (Atual)",
    "6ª Ronda - Quase-Póstuma",
    "7ª Ronda - Final"
];

console.log("=== Rodadas Evolutivas ===");
rodadasEvolutivas.forEach((rodada, index) => {
    console.log(`Rodada ${index + 1}: ${rodada}`);
});

// ------------------------------------------------------------
// EXEMPLO INTERATIVO: O Ciclo do Karma
// ------------------------------------------------------------

let positivo = 0;
let negativo = 0;

function addKarma(tipo) {
    const log = document.getElementById('karma-log-list');
    const timestamp = new Date().toLocaleTimeString();
    
    if (tipo === 'positive') {
        positivo++;
        document.getElementById('positive-count').textContent = positivo;
        
        const item = document.createElement('li');
        item.textContent = `✓ [${timestamp}] Ação positiva - gera luz`;
        item.style.color = '#27ae60';
        log.insertBefore(item, log.firstChild);
    } else {
        negativo++;
        document.getElementById('negative-count').textContent = negativo;
        
        const item = document.createElement('li');
        item.textContent = `✗ [${timestamp}] Ação negativa - gera sombra`;
        item.style.color = '#e74c3c';
        log.insertBefore(item, log.firstChild);
    }
    
    // Limitar日志最多10条
    while (log.children.length > 10) {
        log.removeChild(log.lastChild);
    }
}

function resetKarma() {
    positivo = 0;
    negativo = 0;
    document.getElementById('positive-count').textContent = '0';
    document.getElementById('negative-count').textContent = '0';
    document.getElementById('karma-log-list').innerHTML = '';
}

// ------------------------------------------------------------
// MENSAGEM FINAL
// ------------------------------------------------------------

console.log("%c🧘 JavaScript e o Caminho Teosófico 🧘", 
    "font-size: 20px; color: #27ae60; font-weight: bold;");
console.log("%c\"O conhecimento sem prática é como uma flor sem perfume.\"", 
    "font-style: italic; color: #8e44ad;");
console.log("%c- H.P. Blavatsky", 
    "font-style: italic; color: #8e44ad;");

// Adicionar animação CSS dinamicamente
const style = document.createElement('style');
style.textContent = `
    @keyframes karma-pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .karma-reaction {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }
    
    .acao-emoji {
        font-size: 2rem;
    }
    
    .consequencia {
        color: #27ae60;
        font-style: italic;
    }
`;
document.head.appendChild(style);