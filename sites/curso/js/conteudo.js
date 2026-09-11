// ============================================================================
// CONTEÚDO DO CURSO — fonte única de verdade (sabedoria-curso)
// Estrutura: 6 Módulos + Teste de Realidade (3 perguntas) + Certificado
// Para editar o curso, altere EXCLUSIVAMENTE este arquivo (js/conteudo.js).
// ============================================================================

const CURSO = {
    meta: {
        titulo: "A Jornada da Consciência",
        subtitulo: "Curso Interativo Sem Filtros",
        chamada: "Uma exploração brutalmente honesta sobre consciência, percepção e realidade.",
        aviso: "Sem filtros, sem religião, sem new age. Apenas o que a realidade mostra.",
        botaoIniciar: "Começar Jornada",
        rodape: "Um confronto honesto com o maior mistério do universo"
    },

    // Cada modulo: numero, icone Bootstrap, cor do card-header,
    // lead (1 linha), botaoAberto/botaoFechado e "html" (conteúdo expandido).
    modulos: [
        {
            id: "modulo1",
            numero: 1,
            titulo: "O Que é Consciência?",
            icone: "bi-1-circle",
            cor: "bg-warning",
            lead: "A consciência humana e sua conexão com a realidade total é o enigma fundamental. Vamos encarar isso sem mentiras confortáveis.",
            botaoFechado: "Explorar a Realidade",
            botaoAberto: "Ocultar Realidade",
            html: `
                <div class="card bg-dark text-white border-danger">
                    <div class="card-body">
                        <h4 class="text-danger"><i class="bi bi-exclamation-triangle"></i> A VERDADE SOBRE CONSCIÊNCIA</h4>
                        <p class="fs-5">Consciência é a capacidade de ter <strong>experiência subjetiva</strong>. Ponto.</p>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5 class="text-warning">O que sabemos (pouco):</h5>
                                <ul class="list-unstyled">
                                    <li>✓ Correlaciona com atividade cerebral</li>
                                    <li>✓ Afetada por drogas, lesões, emoções</li>
                                    <li>✓ Existe em diferentes níveis (coma, sonho, vigília)</li>
                                    <li>✓ Parece ser um processo, não uma coisa</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-danger">O que NÃO sabemos (quase tudo):</h5>
                                <ul class="list-unstyled">
                                    <li>❌ Como matéria gera experiência</li>
                                    <li>❌ Por que existe "algo" ao invés de "nada"</li>
                                    <li>❌ Se é universal ou exclusivamente humana</li>
                                    <li>❌ Se continua após a morte</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-danger mt-4 border-0">
                            <h5 class="alert-heading"><i class="bi bi-lightbulb"></i> PROBLEMA DURO DA CONSCIÊNCIA</h5>
                            <p class="mb-0">Como neurônios disparando (matéria) criam a <strong>sensação de ver vermelho</strong> (experiência)?</p>
                            <p class="mb-0">Esta é a maior lacuna da ciência. Ninguém resolveu. Nunca.</p>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <strong>REALIDADE BRUTAL:</strong> Qualquer um que disser que "sabe" o que é consciência está mentindo ou enganado.
                        </div>
                    </div>
                </div>
            `
        },
        {
            id: "modulo2",
            numero: 2,
            titulo: "Percepção e Conhecimento Real",
            icone: "bi-2-circle",
            cor: "bg-info",
            lead: "O conhecimento não vem de livros ou gurus. Vem da percepção crua e da atenção implacável.",
            botaoFechado: "Mergulhar Fundo",
            botaoAberto: "Ocultar Percepção",
            html: `
                <div class="card bg-light border-info">
                    <div class="card-body">
                        <h4 class="text-info"><i class="bi bi-eye"></i> PERCEPÇÃO: A PORTA DA REALIDADE</h4>
                        <p class="fs-5">Sua percepção não é uma câmera. É um <strong>filtro ativo</strong> que distorce tudo.</p>
                        <div class="alert alert-danger mt-3">
                            <h5 class="alert-heading"><i class="bi bi-bug"></i> O PROBLEMA: SEU CÉREBRO MENTE PARA VOCÊ</h5>
                            <p>95% da sua "realidade" é construída, não percebida. Seu cérebro preenche as lacunas com mentiras convincentes.</p>
                        </div>
                        <h5 class="text-danger mt-4">OS 4 FILTROS QUE DESTROEM A VERDADE:</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ol class="small">
                                    <li><strong>Vieses emocionais:</strong> Você vê o que quer ver</li>
                                    <li><strong>Crenças cegas:</strong> Você distorce para confirmar</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <ol class="small" start="3">
                                    <li><strong>Pressão social:</strong> Você mente para se encaixar</li>
                                    <li><strong>Ego:</strong> Você defende sua identidade, não a verdade</li>
                                </ol>
                            </div>
                        </div>
                        <div class="alert alert-success mt-4">
                            <h5 class="alert-heading"><i class="bi bi-shield-check"></i> O CAMINHO DA PERCEPÇÃO REAL</h5>
                            <p class="mb-2"><strong>1. Observação pura:</strong> Observe sem interpretar</p>
                            <p class="mb-2"><strong>2. Questionamento brutal:</strong> Questione TUDO, especialmente suas certezas</p>
                            <p class="mb-2"><strong>3. Evidências reais:</strong> Apenas fatos verificáveis</p>
                            <p class="mb-0"><strong>4. Humildade intelectual:</strong> Admita: "eu posso estar errado"</p>
                        </div>
                        <div class="alert alert-dark mt-3">
                            <strong>EXERCÍCIO BRUTAL:</strong> Tente observar 1 minuto sem julgar. Impossível. Seu cérebro não consegue.
                        </div>
                    </div>
                </div>
            `
        },
        {
            id: "modulo3",
            numero: 3,
            titulo: "As Três Verdades sobre Consciência",
            icone: "bi-3-circle",
            cor: "bg-success",
            lead: "Três visões. Nenhuma tem todas as respostas. Cada uma explica parte do mistério.",
            botaoFechado: "Explorar as Contradições",
            botaoAberto: "Ocultar Contradições",
            html: `
                <div class="card bg-light border-success">
                    <div class="card-body">
                        <h4 class="text-success"><i class="bi bi-yin-yang"></i> AS TRÊS VERDADES INCOMPLETAS</h4>
                        <p class="fs-5">Cada visão explica parte. Nenhuma explica tudo. A verdade está nas contradições.</p>
                        <div class="accordion" id="visoesAccordion">
                            <div class="accordion-item border-warning">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#materialismo">
                                        <i class="bi bi-cpu text-warning me-2"></i> <strong>MATERIALISMO: A realidade é só matéria</strong>
                                    </button>
                                </h2>
                                <div id="materialismo" class="accordion-collapse collapse show" data-bs-parent="#visoesAccordion">
                                    <div class="accordion-body">
                                        <p><strong>TESSE:</strong> Consciência = processo cerebral complexo</p>
                                        <p class="text-muted">Como software emerge do hardware.</p>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h6 class="text-success">✓ EVIDÊNCIAS A FAVOR:</h6>
                                                <ul class="small">
                                                    <li>Lesões cerebrais alteram consciência</li>
                                                    <li>Drogas afetam percepção</li>
                                                    <li>EEG correlaciona com estados mentais</li>
                                                    <li>Evolução mostra complexidade crescente</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-danger">❌ PROBLEMAS FATAIS:</h6>
                                                <ul class="small">
                                                    <li>Não explica experiência subjetiva</li>
                                                    <li>Não explica por que existe "algo"</li>
                                                    <li>Reducionismo extremo</li>
                                                    <li>Ignora qualia (sensação de ver vermelho)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-info">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panpsiquismo">
                                        <i class="bi bi-infinity text-info me-2"></i> <strong>PANPSIQUISMO: Consciência está em tudo</strong>
                                    </button>
                                </h2>
                                <div id="panpsiquismo" class="accordion-collapse collapse" data-bs-parent="#visoesAccordion">
                                    <div class="accordion-body">
                                        <p><strong>TESSE:</strong> Consciência é propriedade fundamental do universo</p>
                                        <p class="text-muted">Como massa ou energia. Existe em diferentes níveis.</p>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h6 class="text-success">✓ EVIDÊNCIAS A FAVOR:</h6>
                                                <ul class="small">
                                                    <li>Evita problema duro da consciência</li>
                                                    <li>Explica por que experiência existe</li>
                                                    <li>Compatível com física quântica?</li>
                                                    <li>Unifica mental e físico</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-danger">❌ PROBLEMAS FATAIS:</h6>
                                                <ul class="small">
                                                    <li>Não é testável cientificamente</li>
                                                    <li>Como pedra tem "consciência"?</li>
                                                    <li>Explicação vazia (panpsiquismo patológico)</li>
                                                    <li>Não explica diferentes níveis</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-danger">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#experiencia">
                                        <i class="bi bi-eye text-danger me-2"></i> <strong>EXPERIÊNCIA DIRETA: Além do mental</strong>
                                    </button>
                                </h2>
                                <div id="experiencia" class="accordion-collapse collapse" data-bs-parent="#visoesAccordion">
                                    <div class="accordion-body">
                                        <p><strong>TESSE:</strong> Estados alterados revelam outra realidade</p>
                                        <p class="text-muted">Meditação, psicodélicos, experiências místicas.</p>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h6 class="text-success">✓ EVIDÊNCIAS A FAVOR:</h6>
                                                <ul class="small">
                                                    <li>Experiências místicas universais</li>
                                                    <li>Psicodélicos mudam percepção radicalmente</li>
                                                    <li>Relatos de unidade com o Todo</li>
                                                    <li>Experiência near-death consistentes</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-danger">❌ PROBLEMAS FATAIS:</h6>
                                                <ul class="small">
                                                    <li>Não é replicável</li>
                                                    <li>Não é verbalizável</li>
                                                    <li>Pode ser alucinação cerebral</li>
                                                    <li>Falta evidência objetiva</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-dark mt-4">
                            <h5 class="alert-heading"><i class="bi bi-lightbulb-fill"></i> A VERDADE INCONFORTÁVEL</h5>
                            <p class="mb-2">Nenhuma das três explicações funciona sozinha.</p>
                            <p class="mb-0">A resposta provavelmente é: <strong>"nós não temos capacidade de compreender ainda"</strong>.</p>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <strong>CONCLUSÃO BRUTAL:</strong> Quem tem certeza sobre consciência não entendeu o problema.
                        </div>
                    </div>
                </div>
            `
        },
        {
            id: "modulo4",
            numero: 4,
            titulo: "Neurociência Brutal",
            icone: "bi-4-circle",
            cor: "bg-warning text-dark",
            lead: "Vamos cortar o bullshit e ver o que a neurociência REALMENTE mostra sobre consciência.",
            botaoFechado: "Mergulhar na Neurociência",
            botaoAberto: "Ocultar Neurociência",
            html: `
                <div class="card bg-dark text-white border-warning">
                    <div class="card-body">
                        <h4 class="text-warning"><i class="bi bi-activity"></i> NEUROCIÊNCIA SEM FILTROS</h4>
                        <p class="fs-5">O que seu cérebro REALMENTE faz quando você "pensa" que está consciente.</p>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5 class="text-danger">OS NÚMEROS BRUTAIS:</h5>
                                <ul class="list-unstyled">
                                    <li>🧠 <strong>86 bilhões</strong> de neurônios</li>
                                    <li>🔌 <strong>100 trilhões</strong> de sinapses</li>
                                    <li>⚡ <strong>200 mph</strong> velocidade dos sinais</li>
                                    <li>🔋 <strong>20%</strong> da energia corporal</li>
                                    <li>💾 <strong>2.5 petabytes</strong> capacidade estimada</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-info">A VERDADE SOBRE PROCESSAMENTO:</h5>
                                <ul class="list-unstyled">
                                    <li>📊 <strong>11 milhões</strong> bits/segundo processados</li>
                                    <li>🎯 <strong>50 bits</strong> chegam à consciência</li>
                                    <li>🤖 <strong>95%</strong> das decisões são inconscientes</li>
                                    <li>⏰ <strong>0.5 segundos</strong> antes de você "decidir"</li>
                                    <li>🔄 <strong>60.000</strong> pensamentos/dia (95% repetidos)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-danger mt-4">
                            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> SEU EU CONSCIENTE É UMA ILUSÃO</h5>
                            <p class="mb-2"><strong>Readiness Potential:</strong> Seu cérebro decide 0.5 segundos ANTES de você tomar consciência.</p>
                            <p class="mb-0"><strong>Libet's Experiment:</strong> Você apenas racionaliza decisões já tomadas.</p>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">CORTEX PRÉ-FRONTAL</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="small"><strong>Função:</strong> Planejamento, decisões</p>
                                        <p class="small"><strong>Problema:</strong> Desliga no flow state</p>
                                        <p class="small"><strong>Verdade:</strong> Você pensa melhor sem ele</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">SISTEMA LÍMBICO</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="small"><strong>Função:</strong> Emoções, memória</p>
                                        <p class="small"><strong>Problema:</strong> Sequestra a razão</p>
                                        <p class="small"><strong>Verdade:</strong> 90% das suas decisões</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">CEREBELO</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="small"><strong>Função:</strong> Movimento, timing</p>
                                        <p class="small"><strong>Problema:</strong> Subestimado</p>
                                        <p class="small"><strong>Verdade:</strong> 50% dos neurônios</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-dark mt-4">
                            <h5 class="alert-heading"><i class="bi bi-lightbulb"></i> CONCLUSÃO NEUROLÓGICA</h5>
                            <p class="mb-0">Você não controla seu cérebro. Seu cérebro controla você e <strong>cria a ilusão</strong> que você controla.</p>
                        </div>
                    </div>
                </div>
            `
        },
        {
            id: "modulo5",
            numero: 5,
            titulo: "Estados Alterados Sem New Age",
            icone: "bi-5-circle",
            cor: "bg-info text-white",
            lead: "Sem misticismo barato. Apenas o que a evidência mostra sobre estados modificados de consciência.",
            botaoFechado: "Explorar Estados Alterados",
            botaoAberto: "Ocultar Estados",
            html: `
                <div class="card bg-light border-info">
                    <div class="card-body">
                        <h4 class="text-info"><i class="bi bi-droplet"></i> ESTADOS ALTERADOS: CIÊNCIA PURA</h4>
                        <p class="fs-5">O que REALMENTE acontece quando sua consciência muda de estado.</p>
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <div class="card h-100 border-warning text-center">
                                    <div class="card-body">
                                        <i class="bi bi-moon-stars display-4 text-warning"></i>
                                        <h6 class="mt-2">SONHO REM</h6>
                                        <p class="small">Paralisia muscular + atividade cerebral intensa</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card h-100 border-info text-center">
                                    <div class="card-body">
                                        <i class="bi bi-droplet display-4 text-info"></i>
                                        <h6 class="mt-2">PSICODÉLICOS</h6>
                                        <p class="small">Redes neurais default mode desligam</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card h-100 border-success text-center">
                                    <div class="card-body">
                                        <i class="bi bi-yin-yang display-4 text-success"></i>
                                        <h6 class="mt-2">MEDITAÇÃO</h6>
                                        <p class="small">Ondas gama aumentam, ego diminui</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card h-100 border-danger text-center">
                                    <div class="card-body">
                                        <i class="bi bi-lightning display-4 text-danger"></i>
                                        <h6 class="mt-2">FLOW STATE</h6>
                                        <p class="small">Pré-frontal desliga, performance máxima</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion mt-3" id="estadosAccordion">
                            <div class="accordion-item border-warning">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sono-rem">
                                        <i class="bi bi-moon-stars text-warning me-2"></i> <strong>SONHO REM: O CÉREBRO ACORDADO DORMINDO</strong>
                                    </button>
                                </h2>
                                <div id="sono-rem" class="accordion-collapse collapse show" data-bs-parent="#estadosAccordion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-warning">O QUE ACONTECE:</h6>
                                                <ul class="small">
                                                    <li>🛡️ <strong>Atonia muscular:</strong> Paralisia total</li>
                                                    <li>🧠 <strong>Cérebro ativo:</strong> Mesmo nível que vigília</li>
                                                    <li>👁️ <strong>Parietal desligado:</strong> Sem noção de corpo</li>
                                                    <li>🎭 <strong>Amígdala hiperativa:</strong> Emoções intensas</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-info">VERDADES CIENTÍFICAS:</h6>
                                                <ul class="small">
                                                    <li>✅ 25% do sono em REM</li>
                                                    <li>✅ 4-5 ciclos por noite</li>
                                                    <li>✅ Essencial para memória</li>
                                                    <li>✅ Privação causa psicose</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning mt-3">
                                            <strong>REALIDADE:</strong> Seu cérebro cria realidades completas sem input sensorial. Como isso é possível?
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-info">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#psicodelicos">
                                        <i class="bi bi-droplet text-info me-2"></i> <strong>PSICODÉLICOS: RESET NEURAL</strong>
                                    </button>
                                </h2>
                                <div id="psicodelicos" class="accordion-collapse collapse" data-bs-parent="#estadosAccordion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-info">O QUE LSD/PSILOCIBINA FAZ:</h6>
                                                <ul class="small">
                                                    <li>🔄 <strong>DMN desliga:</strong> Default Mode Network</li>
                                                    <li>🌐 <strong>Conexões novas:</strong> Cérebro se reconecta</li>
                                                    <li>👥 <strong>Ego dissolve:</strong> Frontal desliga</li>
                                                    <li>🎨 <strong>Sinestesia:</strong> Sentidos misturam</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-success">EVIDÊNCIAS CLÍNICAS:</h6>
                                                <ul class="small">
                                                    <li>✅ 80% remissão depressão</li>
                                                    <li>✅ Cura para dependências</li>
                                                    <li>✅ Experiências místicas universais</li>
                                                    <li>✅ Mudanças permanentes de personalidade</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="alert alert-info mt-3">
                                            <strong>PERGUNTA:</strong> Se uma molécula pode dissolver o ego, o ego era real pra começar?
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-success">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#meditacao">
                                        <i class="bi bi-yin-yang text-success me-2"></i> <strong>MEDITAÇÃO: NEUROPLASTICIDADE REAL</strong>
                                    </button>
                                </h2>
                                <div id="meditacao" class="accordion-collapse collapse" data-bs-parent="#estadosAccordion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-success">MUDANÇAS CEREBRAIS COMPROVADAS:</h6>
                                                <ul class="small">
                                                    <li>📈 <strong>Ondas gama:</strong> 40Hz aumentam</li>
                                                    <li>🧘 <strong>Córtex engrossa:</strong> +0.5mm</li>
                                                    <li>🧠 <strong>Amígdala encolhe:</strong> -15%</li>
                                                    <li>🔗 <strong>Conexões fortes:</strong> Mais mielinização</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-warning">ESTADOS DE CONSCIÊNCIA:</h6>
                                                <ul class="small">
                                                    <li>🎯 <strong>Foco:</strong> Atenção sustentada</li>
                                                    <li>🌊 <strong>Flow:</strong> Desligamento do eu</li>
                                                    <li>🕳️ <strong>Vazio:</strong> Sem pensamentos</li>
                                                    <li>☀️ <strong>Unidade:</strong> Fusão com ambiente</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="alert alert-success mt-3">
                                            <strong>VERDADE:</strong> 10.000 horas de meditação mudam fisicamente seu cérebro. Não é placebo.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-danger">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flow">
                                        <i class="bi bi-lightning text-danger me-2"></i> <strong>FLOW STATE: PERFORMANCE MÁXIMA</strong>
                                    </button>
                                </h2>
                                <div id="flow" class="accordion-collapse collapse" data-bs-parent="#estadosAccordion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-danger">O QUE ACONTECE NO FLOW:</h6>
                                                <ul class="small">
                                                    <li>🧠 <strong>Pré-frontal desliga:</strong> Sem crítica</li>
                                                    <li>⚡ <strong>Dopamina alta:</strong> 500% normal</li>
                                                    <li>🎯 <strong>Foco total:</strong> 100% atenção</li>
                                                    <li>⏰ <strong>Distorção temporal:</strong> Tempo para</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-info">CARACTERÍSTICAS:</h6>
                                                <ul class="small">
                                                    <li>✅ Desafio = habilidade</li>
                                                    <li>✅ Feedback imediato</li>
                                                    <li>✅ Objetivo claro</li>
                                                    <li>✅ Controle total</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="alert alert-danger mt-3">
                                            <strong>IRONIA:</strong> Você performa melhor quando seu "eu consciente" desliga.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-dark mt-4">
                            <h5 class="alert-heading"><i class="bi bi-lightbulb-fill"></i> PADRÃO UNIVERSAL</h5>
                            <p class="mb-0">Todos os estados alterados têm uma coisa em comum: <strong>redução da atividade do córtex pré-frontal</strong>.</p>
                            <p class="mb-0">Seu "eu" é o problema, não a solução.</p>
                        </div>
                    </div>
                </div>
            `
        },
        {
            id: "modulo6",
            numero: 6,
            titulo: "Laboratório da Realidade",
            icone: "bi-6-circle",
            cor: "bg-success text-white",
            lead: "Teoria sem prática é bullshit. Experimentos reais para testar os limites da sua consciência.",
            botaoFechado: "Fazer os Experimentos",
            botaoAberto: "Ocultar Experimentos",
            html: `
                <div class="card bg-light border-success">
                    <div class="card-body">
                        <h4 class="text-success"><i class="bi bi-eye"></i> LABORATÓRIO DA PERCEPÇÃO</h4>
                        <p class="fs-5">Experimentos práticos para PROVAR que sua percepção é falha e limitada.</p>
                        <div class="alert alert-warning mt-3">
                            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> AVISO IMPORTANTE</h5>
                            <p class="mb-0">Se você acha que vai "conseguir" perfeitamente, você já falhou. A verdade está no fracasso.</p>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">EXPERIMENTO 1: CEGUEIRA ATENCIONAL</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="text-primary">O PROCEDIMENTO:</h6>
                                        <ol class="small">
                                            <li>Assista ao vídeo <strong>"The Invisible Gorilla"</strong></li>
                                            <li>Conte quantos passes da equipe branca</li>
                                            <li>Não olhe para nada além dos passes</li>
                                            <li>Depois, responda: o que mais você viu?</li>
                                        </ol>
                                        <h6 class="text-danger mt-3">RESULTADO ESPERADO:</h6>
                                        <p class="small">Você <strong>não viu o gorila</strong>. Seu cérebro filtrou 50% da realidade.</p>
                                        <div class="alert alert-info small py-2">
                                            <strong>Conclusão:</strong> Você só vê o que procura. O resto não existe para você.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">EXPERIMENTO 2: PRESENTE SUBJETIVO</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="text-warning">O PROCEDIMENTO:</h6>
                                        <ol class="small">
                                            <li>Sente-se em silêncio absoluto</li>
                                            <li>Foque apenas no "agora" presente</li>
                                            <li>Tente manter atenção por 60 segundos</li>
                                            <li>Conte quantas vezes sua mente viaja</li>
                                        </ol>
                                        <h6 class="text-danger mt-3">RESULTADO ESPERADO:</h6>
                                        <p class="small">Sua mente viajou <strong>10-20 vezes</strong>. Você não consegue ficar no presente.</p>
                                        <div class="alert alert-warning small py-2">
                                            <strong>Conclusão:</strong> "Agora" é uma ilusão. Sua mente vive no passado/futuro.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">EXPERIMENTO 3: MEMÓRIA FALSA</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="text-info">O PROCEDIMENTO:</h6>
                                        <ol class="small">
                                            <li>Pense em uma memória da infância</li>
                                            <li>Descreva todos os detalhes</li>
                                            <li>Agora: quantos detalhes você adicionou?</li>
                                            <li>Peça para alguém que estava lá confirmar</li>
                                        </ol>
                                        <h6 class="text-danger mt-3">RESULTADO ESPERADO:</h6>
                                        <p class="small">Pelo menos <strong>30% é falso</strong>. Você inventou detalhes.</p>
                                        <div class="alert alert-info small py-2">
                                            <strong>Conclusão:</strong> Sua memória não é gravação. É reconstrução criativa.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0">EXPERIMENTO 4: VIÉS DE CONFIRMAÇÃO</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="text-danger">O PROCEDIMENTO:</h6>
                                        <ol class="small">
                                            <li>Escolha uma crença forte sua</li>
                                            <li>Procure 5 evidências CONTRA ela</li>
                                            <li>Sinceramente: isso foi difícil?</li>
                                            <li>Sua mente resistiu às evidências?</li>
                                        </ol>
                                        <h6 class="text-danger mt-3">RESULTADO ESPERADO:</h6>
                                        <p class="small">Foi <strong>extremamente difícil</strong>. Sua mente rejeitou evidências.</p>
                                        <div class="alert alert-danger small py-2">
                                            <strong>Conclusão:</strong> Você não busca verdade. Busca confirmação.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-dark mt-4">
                            <h5 class="alert-heading"><i class="bi bi-bug"></i> DIAGNÓSTICO FINAL</h5>
                            <p class="mb-2">Se você falhou em todos os experimentos: <strong>PARABÉNS!</strong> Você é humano normal.</p>
                            <p class="mb-2">Se você "conseguiu" em todos: <strong>CUIDADO!</strong> Você está mentindo para si mesmo.</p>
                            <p class="mb-0"><strong>A REALIDADE:</strong> Sua percepção é um filtro falho. Aceitar isso é o primeiro passo para a verdade.</p>
                        </div>
                        <div class="alert alert-success mt-3">
                            <h5 class="alert-heading"><i class="bi bi-trophy"></i> PRÓXIMO NÍVEL</h5>
                            <p class="mb-0">Agora que você sabe que sua percepção é falha, tente:</p>
                            <ul class="mb-0">
                                <li>Observar sem julgar (quase impossível)</li>
                                <li>Questionar suas certezas (doloroso)</li>
                                <li>Aceitar que você pode estar errado (humilhante)</li>
                                <li>Viver com incerteza (libertador)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `
        }
    ],

    quiz: {
        titulo: "Teste de Realidade",
        introducao: "Vamos ver se você realmente entendeu ou apenas está repetindo conceitos.",
        botao: "Testar Realidade",
        perguntas: [
            {
                id: "q1",
                pergunta: "1. Sobre consciência, qual é a VERDADE?",
                opcoes: [
                    { texto: "A ciência já explicou tudo sobre consciência", valor: "errado" },
                    { texto: "Ninguém sabe exatamente o que é ou como funciona", valor: "certo" },
                    { texto: "É apenas energia quântica no cérebro", valor: "errado" }
                ]
            },
            {
                id: "q2",
                pergunta: "2. Qual visão diz que consciência está em tudo?",
                opcoes: [
                    { texto: "Panpsiquismo", valor: "certo" },
                    { texto: "Materialismo", valor: "errado" },
                    { texto: "Dualismo", valor: "errado" }
                ]
            },
            {
                id: "q3",
                pergunta: "3. O que mais distorce a percepção real?",
                opcoes: [
                    { texto: "Falta de inteligência", valor: "errado" },
                    { texto: "Vieses emocionais e crenças pré-existentes", valor: "certo" },
                    { texto: "Falta de educação formal", valor: "errado" }
                ]
            }
        ],
        feedbackSucesso: "EXCELENTE!",
        feedbackQuase: "QUASE LÁ...",
        feedbackFalhou: "PRECISA ESTUDAR MAIS"
    },

    certificado: {
        titulo: "Certificado de Confronto com a Realidade",
        verificacoes: [
            "O mistério insolúvel da consciência",
            "Como os filtros destroem a realidade",
            "As contradições das visões filosóficas",
            "A coragem de admitir: \"não sei\""
        ],
        realizacao: "Aceitou que a verdade é mais complexa que qualquer explicação",
        botaoImprimir: "Imprimir Certificado"
    }
};
