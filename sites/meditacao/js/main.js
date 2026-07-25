// ================================================
// PROJETO: Meditação - Autoconhecimento
// ================================================

// ================================================
// PARTE 1: DADOS
// ================================================
// Array (lista) de objetos, um para cada chakra
const chakras = [
  {
    nome: "Coronário",
    silaba: "I",
    cor: "#c084fc",
    corNome: "Violeta",
    som: "Iii",
    descricao: "Mais suave, como um eco",
    pergunta: "Quem eu sou além do que penso que sou?",
  },
  {
    nome: "Frontal",
    silaba: "I",
    cor: "#a78bfa",
    corNome: "Índigo",
    som: "Iii",
    descricao: "Agudo, como um zumbido",
    pergunta: "O que eu vejo além do que meus olhos mostram?",
  },
  {
    nome: "Laríngeo",
    silaba: "E",
    cor: "#60a5fa",
    corNome: "Azul",
    som: "Eee",
    descricao: "Claro, como um canto de pássaro",
    pergunta: "O que eu preciso dizer? O que me cala?",
  },
  {
    nome: "Cardíaco",
    silaba: "O",
    cor: "#f472b6",
    corNome: "Rosa",
    som: "Ooo",
    descricao: "Suave, como um canto",
    pergunta: "Como eu me amo? O que eu ofereço ao mundo?",
  },
  {
    nome: "Plexo Solar",
    silaba: "U",
    cor: "#4ade80",
    corNome: "Verde",
    som: "Uuu",
    descricao: "Como um vento suave",
    pergunta: "Onde eu sinto meu poder? O que me move?",
  },
  {
    nome: "Pulmonares",
    silaba: "A",
    cor: "#facc15",
    corNome: "Amarelo",
    som: "Aaa",
    descricao: "Aberto, como um suspiro",
    pergunta: "O que eu preciso respirar profundamente?",
  },
  {
    nome: "Prostático/Uterino",
    silaba: "M",
    cor: "#fb923c",
    corNome: "Laranja",
    som: "Mmm",
    descricao: "Vibração nasal",
    pergunta:
      "O que eu preciso liberar ou acolher no meu corpo?",
  },
  {
    nome: "Coccígeo",
    silaba: "S",
    cor: "#f87171",
    corNome: "Vermelho",
    som: "Sss",
    descricao: "Como um sussurro",
    pergunta: "Eu me sinto seguro? Onde preciso me ancorar?",
  },
];

// ================================================
// PARTE 2: SELECIONAR ELEMENTOS DO HTML
// ================================================
// document.getElementById() busca um elemento pelo seu "id"
const divPainel = document.getElementById("painel");
const btnMostrar = document.getElementById("btnMudar");
const timerContainer = document.getElementById(
  "timer-container",
);
const timerNumero = document.getElementById("timer-numero");
const timerLabel = document.getElementById("timer-label");
const timerProgress = document.getElementById(
  "timer-progress",
);
const timerInstrucao = document.getElementById(
  "timer-instrucao",
);
const btnIniciar = document.getElementById("btnTimer");
const btnResetar = document.getElementById("btnReset");

// ================================================
// PARTE 3: VARIÁVEIS DE ESTADO
// ================================================
// Estas variáveis guardam o "estado atual" do programa
let chakraAtual = 0; // Índice do chakra (0 a 7)
let segundosRestantes = 60; // Segundos no timer
let intervaloTimer = null; // ID do setInterval (para poder pará-lo depois)
let timerRodando = false; // O timer está ativo?
let painelEstaAberto = false;

// Constantes (não mudam)
const DURACAO_POR_CHAKRA = 60; // 1 minuto
const DURACAO_SILENCIO = 120; // 2 minutos

// ================================================
// PARTE 4: FUNÇÕES AUXILIARES
// ================================================

function atualizarIndicadoresChakra() {
  const container = document.getElementById("chakra-dots");
  if (!container) return;

  container.innerHTML = chakras
    .map((chakra, i) => {
      const isActive = i === chakraAtual;
      const isPast = i < chakraAtual;
      return `<span class="chakra-dot ${isActive ? "active" : ""} ${isPast ? "past" : ""}" style="--dot-color: ${chakra.cor}"></span>`;
    })
    .join("");
}

function formatarTempo(segundos) {
  const minutos = Math.floor(segundos / 60);
  const segs = segundos % 60;
  // padStart garante que tenha 2 dígitos (ex: "05" em vez de "5")
  return `${minutos}:${segs.toString().padStart(2, "0")}`;
}

// Atualiza tudo que aparece na tela do timer
function atualizarTela() {
  const chakra = chakras[chakraAtual];

  timerNumero.textContent = formatarTempo(segundosRestantes);
  timerLabel.textContent = chakra.nome;
  timerInstrucao.innerHTML = `Repita: <em>"${chakra.som}"</em> — ${chakra.pergunta}`;

  const porcentagem =
    (segundosRestantes / DURACAO_POR_CHAKRA) * 100;
  timerProgress.style.width = `${porcentagem}%`;

  timerContainer.style.borderColor = chakra.cor;
  timerNumero.style.color = chakra.cor;
  timerNumero.style.textShadow = `0 0 30px ${chakra.cor}80`;
  timerProgress.style.background = `linear-gradient(90deg, ${chakra.cor}, ${chakra.cor}80)`;
}

// ================================================
// PARTE 5: LÓGICA DO TIMER
// ================================================

// Passa para o próximo chakra ou inicia silêncio
function avancarChakra() {
  chakraAtual++;

  if (chakraAtual >= chakras.length) {
    iniciarSilencio();
  } else {
    segundosRestantes = DURACAO_POR_CHAKRA;
    atualizarTela();
    atualizarIndicadoresChakra();
  }
}

// Modo silêncio final (2 minutos)
function iniciarSilencio() {
  pararTimer();

  segundosRestantes = DURACAO_SILENCIO;
  timerNumero.textContent = formatarTempo(segundosRestantes);
  timerLabel.textContent = "Silêncio";
  timerInstrucao.innerHTML = `<em>Respire naturalmente. Observe o que surgiu.</em>`;
  timerProgress.style.width = "100%";

  timerContainer.style.borderColor = "#a855f7";
  timerNumero.style.color = "#a855f7";
  timerNumero.style.textShadow =
    "0 0 30px rgba(168, 85, 247, 0.5)";
  timerProgress.style.background =
    "linear-gradient(90deg, #a855f7, #6366f1)";

  let segundosSilencio = DURACAO_SILENCIO;

  // Cria novo timer para o silêncio
  intervaloTimer = setInterval(() => {
    segundosSilencio--;
    timerNumero.textContent = formatarTempo(segundosSilencio);
    timerProgress.style.width = `${(segundosSilencio / DURACAO_SILENCIO) * 100}%`;

    if (segundosSilencio <= 0) {
      pararTimer();
      timerNumero.textContent = "FIM";
      timerLabel.textContent = "Exercício Concluído";
      timerInstrucao.innerHTML = `<em>✨ Parabéns! Anote suas reflexões.</em>`;
      timerRodando = false;
      btnIniciar.textContent = "Iniciar";
    }
  }, 1000);
}

// Para o timer (sem mudar o texto do botão)
function pararTimer() {
  if (intervaloTimer) {
    clearInterval(intervaloTimer);
    intervaloTimer = null;
  }
}

// Iniciar ou pausar o timer
function toggleTimer() {
  if (timerRodando) {
    // Se está rodando → PAUSAR
    pararTimer();
    timerRodando = false;
    btnIniciar.textContent = "Continuar";
  } else {
    // Se está parado → INICIAR
    timerRodando = true;
    btnIniciar.textContent = "Pausar";

    // setInterval executa a função a cada 1000ms (1 segundo)
    intervaloTimer = setInterval(() => {
      segundosRestantes--;
      atualizarTela();

      if (segundosRestantes <= 0) {
        avancarChakra();
      }
    }, 1000);
  }
}

// Reseta tudo para o início
function resetar() {
  pararTimer();
  chakraAtual = 0;
  segundosRestantes = DURACAO_POR_CHAKRA;
  timerRodando = false;
  btnIniciar.textContent = "Iniciar";
  atualizarTela();
  atualizarIndicadoresChakra();
}

// ================================================
// PARTE 6: ACCORDION (Mostrar/Ocultar)
// ================================================

// Alterna entre mostrar e ocultar o painel
function alternarPainel() {
  if (!painelEstaAberto) {
    // ABRIR O PAINEL

    // Cria o HTML da lista de chakras usando .map() e .join()
    const listaHtml = chakras
      .map((chakra, indice) => {
        return `
                <li class="list-group-item bg-dark" style="border-left: 4px solid ${chakra.cor}">
                    <strong>${indice + 1}. ${chakra.nome} (${chakra.silaba})</strong>
                    <p><em>Cor: ${chakra.corNome}</em></p>
                    <p>Som: "${chakra.som}" — ${chakra.descricao}</p>
                </li>
            `;
      })
      .join("");

    // Insere o HTML no painel
    divPainel.innerHTML = `
            <p class="text-info"><strong>Posição:</strong> Sentado, costas eretas, olhos fechados.</p>
            <div class="progress-chakras mb-3">
                <div class="chakra-dots" id="chakra-dots"></div>
            </div>
            <ul class="list-group mb-3">${listaHtml}</ul>
            <div class="alert alert-info">
                <strong>Silêncio final:</strong> 2 minutos de observação
            </div>
        `;

    atualizarIndicadoresChakra();

    // Mostra o timer e adiciona a classe 'open'
    timerContainer.style.display = "block";
    divPainel.classList.add("open");

    // Muda o texto e estado do botão
    btnMostrar.textContent = "Fechar";
    painelEstaAberto = true;

    // Atualiza o display com o primeiro chakra
    atualizarTela();
  } else {
    // FECHAR O PAINEL

    divPainel.classList.remove("open");
    btnMostrar.textContent = "Iniciar Exercício";
    timerContainer.style.display = "none";
    resetar(); // Reseta o timer
    painelEstaAberto = false;

    // Depois da animação, limpa o conteúdo
    setTimeout(() => {
      if (!painelEstaAberto) {
        divPainel.innerHTML = "";
      }
    }, 400);
  }
}

// ================================================
// PARTE 7: EVENT LISTENERS (Conexão com usuário)
// ================================================

// Quando clicar no botão "Mostrar/Ocultar"
btnMostrar.addEventListener("click", alternarPainel);

// Quando clicar no botão "Iniciar/Pausar"
btnIniciar.addEventListener("click", toggleTimer);

// Quando clicar no botão "Resetar"
btnResetar.addEventListener("click", resetar);

// ================================================
// PARTE 8: INICIALIZAÇÃO
// ================================================

// Quando a página carrega, atualiza a tela uma vez
atualizarTela();

console.log("✅ JavaScript carregado com sucesso!");
console.log(`📊 Total de chakras: ${chakras.length}`);

// Seção akrash
const divMostraContent1 = document.getElementById('mostraC1');
const botaoAkrash = document.getElementById('btnRs');

function mostrAtcP() {
  
  divMostraContent1.innerHTML = '';

  // divMostraContent1.classList.add('ativo');

  // botaoAkrash.textContent = 'Plena atenção!';

  divMostraContent1.className = 'bg-info';

  const h3 = document.createElement('h3');
  const ul = document.createElement('ul');
  const li = document.createElement('li');
  const li1 = document.createElement('li');
  const li2 = document.createElement('li');
  const p = document.createElement('p');

  h3.textContent = 'Comece com mindfulness (atenção plena)';
  li.textContent = 'Foco na respiração - observe a entrada e saída de ar';
  li1.textContent = 'Quando a mente vagar, traga-a de volta com gentileza';
  li2.textContent = 'Isso treina a atenção sustentada - base para estados mais profundos';
  p.textContent = 'Efeito: Reduz a "tagarelice mental" e aumenta a clareza - o que permite peceber sensações sutis (como calor, vibração, luz interior)'

  ul.appendChild(li, li1, li2);

  divMostraContent1.append(h3, ul, p);

}