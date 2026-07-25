---
tipo: guia
criado_em: 2026-06-01
atualizado_em: 2026-07-25
tags:
  - javascript
  - meditacao
  - guia
parent: tecnica-meditacao
---

# Guia Didático: Lógica JavaScript do Projeto Meditação

Este documento explica, passo a passo, como funciona o código JavaScript do exercício de autoconhecimento com chakras.

---

## Índice

1. [Visão Geral do Projeto](#visão-geral)
2. [Estrutura de Dados: Array de Chakras](#estrutura-de-dados)
3. [Seleção de Elementos do DOM](#seleção-dom)
4. [Estado da Aplicação](#estado-da-aplicação)
5. [Funções de Formatação](#funções-de-formatação)
6. [Lógica do Timer](#lógica-do-timer)
7. [Accordion (Abrir/Fechar)](#accordion)
8. [Event Listeners](#event-listeners)
9. [Fluxo Completo da Aplicação](#fluxo-completo)

---

## 1. Visão Geral do Projeto <a name="visão-geral"></a>

O projeto é uma página de meditação que:
- Mostra informações sobre 8 chakras
- Permite visualizar os passos do exercício
- Inclui um **timer automático** que:
  - Conta 1 minuto para cada chakra (8 chakras = 8 min)
  - Ao final, faz 2 minutos de silêncio
  - Avança automaticamente para o próximo chakra

### Arquitetura do JavaScript

```
┌─────────────────────────────────────────────────────────┐
│  DADOS (Array de objetos)                               │
│  └── chakras[] - informações dos 8 chakras               │
├─────────────────────────────────────────────────────────┤
│  ELEMENTOS (Referências do HTML)                        │
│  └── divPainel, botaoPainel, timerNumero, etc.          │
├─────────────────────────────────────────────────────────┤
│  ESTADO (Variáveis que mudam)                            │
│  └── chakraAtual, tempoRestante, timerAtivo, etc.       │
├─────────────────────────────────────────────────────────┤
│  FUNÇÕES (Ações que o código executa)                   │
│  └── formatarTempo(), atualizarDisplay(), toggleTimer() │
├─────────────────────────────────────────────────────────┤
│  EVENT LISTENERS (Conexão com usuário)                   │
│  └── Clique nos botões                                  │
└─────────────────────────────────────────────────────────┘
```

---

## 2. Estrutura de Dados: Array de Chakras <a name="estrutura-de-dados"></a>

```js
const chakras = [
    { nome: "Coccígeo", silaba: "S", cor: "Vermelho", som: "Sss", descricao: "Como um sussurro", pergunta: "Eu me sinto seguro?" },
    { nome: "Prostático/Uterino", silaba: "M", cor: "Laranja", som: "Mmm", descricao: "Vibração nasal", pergunta: "O que eu preciso liberar?" },
    // ... mais 6 chakras
];
```

### O que é um Array?

Um **array** é uma lista de itens. Cada item pode ser qualquer tipo de dado (número, texto, objeto).

```js
// Array simples de números
const numeros = [1, 2, 3, 4, 5];

// Array de texto (strings)
const cores = ["vermelho", "azul", "verde"];

// Array de OBJETOS (como no nosso código)
const pessoas = [
    { nome: "Ana", idade: 25 },
    { nome: "João", idade: 30 }
];
```

### Acessando itens do array

Cada item tem um **índice** (posição), começando em **0**:

```js
const frutas = ["maçã", "banana", "uva"];

frutas[0]  // → "maçã" (primeiro item)
frutas[1]  // → "banana" (segundo item)
frutas[2]  // → "uva" (terceiro item)
```

No nosso código:
```js
chakras[0]  // → dados do primeiro chakra (Coccígeo)
chakras[7]  // → dados do último chakra (Coronário)
```

### O que é um Objeto?

Um **objeto** é um conjunto de **propriedades** (chave: valor):

```js
const carro = {
    marca: "Toyota",
    cor: "vermelho",
    ano: 2023
};

carro.marca    // → "Toyota"
carro.cor      // → "vermelho"
```

No nosso código, cada chakra é um objeto com propriedades:
- `nome` - nome do chakra
- `silaba` - letra do mantra
- `cor` - cor associada
- `som` - como pronunciar
- `descricao` - explicação
- `pergunta` - reflexão

---

## 3. Seleção de Elementos do DOM <a name="seleção-dom"></a>

**DOM** = Document Object Model. É a representação do HTML como objetos que o JavaScript pode manipular.

```js
const divPainel = document.getElementById('painel');
const botaoPainel = document.getElementById('btnMudar');
const timerNumero = document.getElementById('timer-numero');
```

### `document.getElementById()`

Busca um elemento HTML pelo seu atributo `id`:

```html
<!-- HTML -->
<div id="painel">...</div>
```

```js
// JavaScript: seleciona esse elemento
const divPainel = document.getElementById('painel');
```

Agora podemos usar `divPainel` para:
- Mudar o conteúdo: `divPainel.innerHTML = "novo conteúdo"`
- Mudar estilos: `divPainel.style.color = "red"`
- Adicionar classes: `divPainel.classList.add("open")`

---

## 4. Estado da Aplicação <a name="estado-da-aplicação"></a>

**Estado** são variáveis que guardam a "situação atual" do programa. Elas mudam conforme o usuário interage.

```js
let chakraAtual = 0;      // Qual chakra está ativo? (0 a 7)
let tempoRestante = 60;   // Segundos restantes no timer
let timerAtivo = false;    // O timer está rodando?
let painelAberto = false;  // O painel está aberto?
let timerIntervalo = null; // Referência do setInterval (explicado depois)
```

### Por que usar `let` em vez de `const`?

- `const` → valor **nunca** muda
- `let` → valor **pode** mudar

```js
const PI = 3.14159;     // Nunca muda
let pontos = 0;          // Vai aumentar
pontos = pontos + 10;    // OK! "pontos" pode mudar
```

### Por que o estado é importante?

Imagine o timer:
- Se o usuário pause, precisamos **lembrar** que está pausado
- Se avançar para próximo chakra, precisamos **lembrar** qual é o próximo
- Se fechar o painel, precisamos **lembrar** que está fechado

Sem estado, o programa "esquece" o que aconteceu antes!

---

## 5. Funções de Formatação <a name="funções-de-formatação"></a>

### 5.1 formatarTempo()

Converte segundos em formato "MM:SS":

```js
function formatarTempo(segundos) {
    // math.floor = arredonda para baixo
    const mins = Math.floor(segundos / 60);
    
    // % = resto da divisão
    const segs = segundos % 60;
    
    // padStart adiciona "0" se necessário
    return `${mins}:${segs.toString().padStart(2, '0')}`;
}
```

**Exemplos:**
```js
formatarTempo(65)   // → "1:05"
formatarTempo(30)   // → "0:30"
formatarTempo(5)    // → "0:05"
formatarTempo(120)  // → "2:00"
```

### math.floor() vs math.ceil() vs Math.round()

```js
Math.floor(3.7)   // → 3  (arredonda para BAIXO)
Math.ceil(3.2)    // → 4  (arredonda para CIMA)
Math.round(3.5)   // → 4  (arredonda para o mais próximo)
```

### Operador % (módulo/resto)

Retorna o resto da divisão:

```js
10 % 3   // → 1 (10 dividido por 3 = 3, resta 1)
15 % 5   // → 0 (15 dividido por 5 = 3, resta 0)
65 % 60  // → 5 (65 dividido por 60 = 1, resta 5)
```

### Template Literals (Template Strings)

Usamos crases (backticks) em vez de aspas:

```js
// Aspas normais - difícil de ler
const mensagem = "Olá " + nome + ", você tem " + idade + " anos.";

// Template literal - mais fácil
const mensagem = `Olá ${nome}, você tem ${idade} anos.`;
```

Dentro de `${}` podemos colocar qualquer expressão JavaScript!

### padStart()

Adiciona caracteres no início até atingir o tamanho desejado:

```js
"5".padStart(2, '0')     // → "05"
"42".padStart(2, '0')    // → "42" (já tem 2 caracteres)
"hello".padStart(10, '*') // → "*****hello"
```

---

## 6. Lógica do Timer <a name="lógica-do-timer"></a>

### 6.1 atualizarDisplay()

Atualiza o que aparece na tela do timer:

```js
function atualizarDisplay() {
    const chakra = chakras[chakraAtual];
    
    timerNumero.textContent = formatarTempo(tempoRestante);
    timerLabel.textContent = chakra.nome;
    timerInstrucao.innerHTML = `Repita: <em>"${chakra.som}"</em>`;
    
    const progresso = (tempoRestante / DURACAO_CHAKRA) * 100;
    timerProgress.style.width = `${progresso}%`;
}
```

**Passo a passo:**
1. Pega os dados do chakra atual
2. Atualiza o número do timer
3. Atualiza o nome do chakra
4. Atualiza a instrução (com o som e pergunta)
5. Calcula o progresso percentual da barra

### 6.2 setInterval() - O Coração do Timer

`setInterval` executa uma função repetidamente, em um intervalo definido:

```js
// Sintaxe:
setInterval(função, milissegundos)

// Exemplo: executar a cada 1 segundo
timerIntervalo = setInterval(() => {
    console.log("1 segundo passou!");
}, 1000);
```

### 6.3 clearInterval() - Parando o Timer

Para parar um intervalo, guardamos a referência e usamos `clearInterval`:

```js
// Guardamos o retorno do setInterval
let timerIntervalo = setInterval(minhaFuncao, 1000);

// Quando quiser parar:
clearInterval(timerIntervalo);
timerIntervalo = null;
```

### 6.4 toggleTimer() - Iniciar/Pausar

```js
function toggleTimer() {
    if (timerAtivo) {
        // Se já está rodando → PAUSAR
        clearInterval(timerIntervalo);
        timerAtivo = false;
        btnTimer.textContent = "Continuar";
    } else {
        // Se está parado → INICIAR
        timerAtivo = true;
        btnTimer.textContent = "Pausar";
        
        timerIntervalo = setInterval(() => {
            tempoRestante--;
            atualizarDisplay();
            
            if (tempoRestante <= 0) {
                proximoChakra();
            }
        }, 1000);
    }
}
```

**Fluxo:**
```
Usuário clica "Iniciar"
    ↓
timerAtivo = true
    ↓
Cria setInterval (executa a cada 1 segundo)
    ↓
A cada segundo:
    - diminui tempoRestante em 1
    - atualiza a tela
    - se chegou a 0 → chama proximoChakra()
```

### 6.5 proximoChakra() - Avançar

```js
function proximoChakra() {
    chakraAtual++;  // Avança para próximo índice
    
    if (chakraAtual >= chakras.length) {
        // Se passou do último chakra
        iniciarSilencio();
    } else {
        // Próximo chakra normal
        tempoRestante = DURACAO_CHAKRA;  // Volta para 60s
        atualizarDisplay();
    }
}
```

### 6.6 .length - Quantidade de Itens

```js
chakras.length  // → 8 (quantos chakras existem)
frutas.length   // → 3

// Verificar se chegou ao fim:
if (chakraAtual >= chakras.length) {
    // Chegou no fim!
}
```

### 6.7 iniciarSilencio() - Modo Final

```js
function iniciarSilencio() {
    clearInterval(timerIntervalo);  // Para o timer atual
    
    timerNumero.textContent = formatarTempo(DURACAO_SILENCIO);
    timerLabel.textContent = "🧘 Silêncio";
    timerInstrucao.innerHTML = `Respire naturalmente...`;
    
    let silencioRestante = DURACAO_SILENCIO;  // 120 segundos
    
    timerIntervalo = setInterval(() => {
        silencioRestante--;
        timerNumero.textContent = formatarTempo(silencioRestante);
        
        if (silencioRestante <= 0) {
            clearInterval(timerIntervalo);
            timerNumero.textContent = "FIM";
            // Mostra mensagem de conclusão
        }
    }, 1000);
}
```

---

## 7. Accordion (Abrir/Fechar) <a name="accordion"></a>

Accordion é um componente que expande/colapsa conteúdo ao clicar.

### Estrutura HTML

```html
<button id="btnMudar">Mostrar</button>
<div id="painel" class="content">
    <!-- Conteúdo que aparece/oculta -->
</div>
```

### CSS do Accordion

```css
.content {
    max-height: 0;       /* Começa fechado (altura 0) */
    overflow: hidden;    /* Esconde conteúdo que ultrapassa */
    opacity: 0;          /* Começa invisível */
    transition: ...;    /* Animação suave */
}

.content.open {
    max-height: 2000px;  /* Quando aberto, altura suficiente */
    opacity: 1;          /* Visível */
}
```

### Lógica JavaScript

```js
let painelAberto = false;

function alternarPainel() {
    if (!painelAberto) {
        // ABRIR
        divPainel.innerHTML = gerarConteudo();
        divPainel.classList.add('open');
        botaoPainel.textContent = 'Ocultar';
        painelAberto = true;
    } else {
        // FECHAR
        divPainel.classList.remove('open');
        botaoPainel.textContent = 'Mostrar';
        painelAberto = false;
    }
}
```

### classList.add() / classList.remove()

Adiciona ou remove classes CSS:

```js
elemento.classList.add('open');      // Adiciona classe "open"
elemento.classList.remove('open');    // Remove classe "open"
elemento.classList.contains('open'); // true se tem a classe
elemento.classList.toggle('open');    // Adiciona se não tem, remove se tem
```

### innerHTML - Conteúdo do Elemento

Muda o conteúdo HTML interno:

```js
divPainel.innerHTML = "<p>Novo conteúdo!</p>";
divPainel.innerHTML = "";  // Limpa o conteúdo
```

---

## 8. Event Listeners <a name="event-listeners"></a>

Event listeners "ouvem" ações do usuário e executam funções.

### addEventListener()

```js
elemento.addEventListener('evento', funcao);
```

Exemplos de eventos:
- `'click'` - quando clica
- `'mouseover'` - quando o mouse passa por cima
- `'keydown'` - quando pressiona uma tecla
- `'change'` - quando muda um input
- `'transitionend'` - quando termina uma transição CSS

### No nosso código:

```js
// Quando clicar no botão do painel
botaoPainel.addEventListener('click', alternarPainel);

// Quando clicar no botão do timer
btnTimer.addEventListener('click', toggleTimer);

// Quando clicar em reset
btnReset.addEventListener('click', resetarTimer);

// Quando a transição CSS terminar
divPainel.addEventListener('transitionend', function handler() {
    // Só executa aqui quando animação terminar
    divPainel.classList.remove('animando');
    botaoPainel.disabled = false;
});
```

### transitionend

Este evento dispara quando uma transição CSS termina. Útil para:
- Reativar botões após animações
- Limpar conteúdo após fechar
- Encadear animações

---

## 9. Fluxo Completo da Aplicação <a name="fluxo-completo"></a>

### Inicialização (quando a página carrega)

```js
// Definir estado inicial
botaoPainel.setAttribute('aria-expanded', 'false');
atualizarDisplay();  // Mostra chakra 0, tempo 60s
```

### Fluxo 1: Abrir o Painel

```
Usuário clica "Mostrar"
    ↓
alternarPainel() executa
    ↓
Gera HTML dos 8 chakras
    ↓
Adiciona classe 'open' ao painel (CSS anima a abertura)
    ↓
Mostra o container do timer
    ↓
painelAberto = true
```

### Fluxo 2: Iniciar o Timer

```
Usuário clica "Iniciar"
    ↓
toggleTimer() executa
    ↓
timerAtivo = true
    ↓
Cria setInterval (a cada 1 segundo):
    - tempoRestante--
    - atualizarDisplay()
    - se tempoRestante == 0 → proximoChakra()
```

### Fluxo 3: Avançar Chakras

```
Timer chega a 0
    ↓
proximoChakra() executa
    ↓
chakraAtual++ (incrementa índice)
    ↓
Se ainda há chakras:
    - tempoRestante = 60
    - atualizarDisplay()
    ↓
Se passou do último:
    - iniciarSilencio()
```

### Fluxo 4: Conclusão

```
2 minutos de silêncio terminam
    ↓
timerNumero = "FIM"
    ↓
Mostra mensagem de parabéns
    ↓
timerAtivo = false
    ↓
Botão volta para "Iniciar"
```

### Fluxo 5: Resetar

```
Usuário clica "Resetar"
    ↓
resetarTimer() executa
    ↓
clearInterval(timerIntervalo)
    ↓
chakraAtual = 0
    ↓
tempoRestante = 60
    ↓
timerAtivo = false
    ↓
atualizarDisplay()
```

---

## Resumo dos Conceitos Aprendidos

| Conceito | Para que serve | Exemplo |
|----------|---------------|---------|
| `Array` | Lista de itens | `chakras[0]` |
| `Objeto` | Dados estruturados | `{ nome: "Ana" }` |
| `const vs let` | Imutável vs mutável | `const PI`, `let count` |
| `getElementById` | Selecionar HTML | `document.getElementById('id')` |
| `classList.add/remove` | Mudar classes CSS | `el.classList.add('open')` |
| `innerHTML` | Mudar conteúdo HTML | `el.innerHTML = '<p>oi</p>'` |
| `setInterval` | Repetir código | `setInterval(fn, 1000)` |
| `clearInterval` | Parar repetição | `clearInterval(id)` |
| `addEventListener` | Reagir a cliques | `btn.addEventListener('click', fn)` |
| `Template literals` | Interpolação de strings | `` `Olá ${nome}` `` |
| `Math.floor` | Arredondar para baixo | `Math.floor(3.7) → 3` |
| `%` (módulo) | Resto da divisão | `65 % 60 → 5` |
| `padStart` | Preencher com zeros | `"5".padStart(2, '0')` |
| `transitionend` | Após animação CSS | Event listener |

---

## Exercícios para Praticar

1. **Mude a duração**: Altere `DURACAO_CHAKRA` para 30 segundos e teste.

2. **Adicione cor**: Quando avançar o chakra, mude a cor de fundo da página conforme a cor do chakra atual.

3. **Som**: Adicione um som (beep) quando o timer chega a 0 usando a API de áudio do navegador.

4. **Pause automática**: Quando o painel for fechado, pause o timer automaticamente.

5. **localStorage**: Salve o progresso do exercício para que o usuário possa continuar de onde parou (veja `localStorage.setItem` e `localStorage.getItem`).

---

## Referências

- [MDN: Arrays](https://developer.mozilla.org/pt-BR/docs/Web/JavaScript/Reference/Global_Objects/Array)
- [MDN: setInterval](https://developer.mozilla.org/pt-BR/docs/Web/API/setInterval)
- [MDN: DOM](https://developer.mozilla.org/pt-BR/docs/Web/API/Document_Object_Model)
- [MDN: Event Listeners](https://developer.mozilla.org/pt-BR/docs/Web/API/EventTarget/addEventListener)
