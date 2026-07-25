const botaoOqueEra = document.getElementById('oQueEra');
const botaoPrincCarct = document.getElementById('principaisCaracteristicas');
const botaoDesfExpan = document.getElementById('desfiosExpansao');
const botaoPapMulher = document.getElementById('oPapelDaMulher');

const divPrincipal = document.getElementById('mostraConteudos');
const nagHammadiSection = document.getElementById('nagHammadi');

const botoesMap = {
    'oQueEra': botaoOqueEra,
    'principaisCaracteristicas': botaoPrincCarct,
    'desfiosExpansao': botaoDesfExpan,
    'oPapelDaMulher': botaoPapMulher,
    'nagHammadi': nagHammadiSection
};

function resetarTodos() {
    Object.values(botoesMap).forEach(b => {
        if (b.id === 'oQueEra') b.textContent = 'O que era o Cristianismo Primitivo?';
        else if (b.id === 'principaisCaracteristicas') b.textContent = 'Principais Características';
        else if (b.id === 'desfiosExpansao') b.textContent = 'Desafios e Expansão';
        else if (b.id === 'oPapelDaMulher') b.textContent = 'O Papel das Mulheres';
    });
    divPrincipal.classList.remove('ativo');
    divPrincipal.innerHTML = '';
}

function toggleSecao(elemento, conteudo, nomeOriginal) {
    const isNag = elemento.id === 'nagHammadi';
    const container = isNag ? elemento.querySelector('.container') : divPrincipal;
    
    if (container.classList.contains('ativo') && container.dataset.ativa === elemento.id) {
        container.classList.remove('ativo');
        delete container.dataset.ativa;
        
        if (isNag) {
            container.innerHTML = `<h2>Manuscritos de Nag Hammadi</h2><p>Relação com os livros usados pelo cristianismo primitivo</p>`;
        } else {
            resetarTodos();
        }
    } else {
        resetarTodos();
        
        container.dataset.ativa = elemento.id;
        container.classList.add('ativo');
        
        if (isNag) {
            container.innerHTML = `<h2>Manuscritos de Nag Hammadi</h2>${conteudo}<button class="btn-fechar">Fechar</button>`;
            container.querySelector('.btn-fechar').addEventListener('click', (e) => {
                e.stopPropagation();
                container.classList.remove('ativo');
                delete container.dataset.ativa;
                container.innerHTML = `<h2>Manuscritos de Nag Hammadi</h2><p>Relação com os livros usados pelo cristianismo primitivo</p>`;
            });
        } else {
            elemento.textContent = 'Fechar';
            divPrincipal.classList.add('ativo');
            divPrincipal.innerHTML = conteudo;
        }
    }
}

botaoOqueEra.addEventListener('click', () => {
    const conteudo = `
        <h2>O que Era o Cristianismo Primitivo?</h2>
        <p>Foi o movimento inicial da fé cristã (século I), nascido dentro do judaísmo no Império Romano. Diferente das estruturas religiosas organizadas de hoje, era um grupo vibrante, marcado por uma fé intensa na ressurreição de Jesus e pela expectativa iminente do seu retorno.</p>
        <p>O cristianismo primitivo foi o conjunto das primeiras comunidades cristãs dos séculos I a IV, nascidas em torno de Jesus e da pregação dos apóstolos, com forte ligação inicial ao judaísmo e depois expansão pelo mundo greco-romano.</p>
        <hr>
        <h3>Verdade Verdadeira</h3>
        <p>Há duas leituras principais:</p>
        <ul>
            <li><strong>Histórica:</strong> que tenta reconstruir o que essas comunidades realmente criam e faziam</li>
            <li><strong>Teológica:</strong> que pergunta o que é verdadeiro na fé cristã em si</li>
        </ul>
        <p>Como esse período teve correntes diversas — como ebionitas, docetas e gnósticos — falar em "uma única forma original" de cristianismo é simplificar demais.</p>
    `;
    toggleSecao(botaoOqueEra, conteudo, 'O que era o Cristianismo Primitivo?');
});

botaoPrincCarct.addEventListener('click', () => {
    const conteudo = `
        <h2>Principais Características</h2>
        <h3>Comunidade e Unidade</h3>
        <p>Os seguidores viviam com forte senso de pertencimento, compartilhando bens e apoiando uns aos outros. As reuniões aconteciam em <strong>casas particulares</strong> (casas-igrejas), criando um ambiente íntimo e familiar.</p>
        <h3>Práticas Essenciais</h3>
        <ul>
            <li><strong>Batismo:</strong> Rito de iniciação simbolizando morte para a vida antiga e renascimento.</li>
            <li><strong>Ceia do Senhor (Eucaristia):</strong> Refeição memorial do sacrifício de Jesus, ponto central das reuniões.</li>
            <li><strong>Oração e Ensino:</strong> Leitura das Escrituras (Antigo Testamento e primeiros escritos apostólicos), orações coletivas e cânticos.</li>
            <li><strong>Caridade:</strong> Cuidado intenso com pobres, viúvas e órfãos.</li>
        </ul>
        <h3>Estrutura Organizacional</h3>
        <p>Era fluida e descentralizada. A liderança inicial vinha dos <strong>apóstolos</strong>, evoluindo depois para <strong>presbíteros</strong> (anciãos) e <strong>diáconos</strong> conforme as comunidades cresciam. Não existia uma hierarquia rígida como a atual.</p>
    `;
    toggleSecao(botaoPrincCarct, conteudo, 'Principais Características');
});

botaoDesfExpan.addEventListener('click', () => {
    const conteudo = `
        <h2>Desafios e Expansão</h2>
        <h3>Perseguição</h3>
        <p>Enfrentaram hostilidade tanto de autoridades judaicas (por considerá-los hereges) quanto do Império Romano (acusados de "ateísmo" por não adorarem o imperador e de "canibalismo" simbólico pela Ceia). O martírio, no entanto, fortaleceu a fé e atraiu novos seguidores.</p>
        <h3>Relação com o Judaísmo</h3>
        <p>Começou como um movimento judaico messiânico. Com a inclusão de <strong>gentios</strong> (não judeus) sem a exigência de seguir todas as leis mosaicas (como a circuncisão), o movimento separou-se gradualmente do judaísmo tradicional.</p>
        <h3>Crescimento Rápido</h3>
        <p>Expansão facilitada pelas estradas romanas, pelo uso do grego (<em>koiné</em>) como língua universal, pela mensagem de salvação acessível e pela forte testemunho de vida dos cristianos.</p>
    `;
    toggleSecao(botaoDesfExpan, conteudo, 'Desafios e Expansão');
});

botaoPapMulher.addEventListener('click', () => {
    const conteudo = `
        <h2>O Papel das Mulheres</h2>
        <p>As mulheres tiveram um papel <strong>surpreendentemente ativo e significativo</strong> para a época, incluindo:</p>
        <ul>
            <li>Seguidoras fiéis de Jesus</li>
            <li>Testemunhas da ressurreição</li>
            <li>Líderes de igrejas em casa (como <strong>Lídia</strong> e <strong>Priscila</strong>)</li>
            <li>Colaboradoras na missão evangelizadora</li>
        </ul>
        <p>Em um contexto social onde as mulheres tinham direitos limitados, o cristianismo primitivo ofereceu um espaço de liderança e participação incomum para a época.</p>
    `;
    toggleSecao(botaoPapMulher, conteudo, 'O Papel das Mulheres');
});

nagHammadiSection.addEventListener('click', () => {
    const conteudo = `
        <p>Em 1945, perto da cidade egípcia de <strong>Nag Hammadi</strong>, foi descoberta uma biblioteca de manuscritos antigos contendo 13 dígices (livros encadernados) em couro, datados do século IV, mas contendo textos escritos entre os séculos II e III.</p>
        <h3>Textos Principais</h3>
        <ul>
            <li><strong>Evangelho de Tomé:</strong> Uma coleção de ditos de Jesus, sem narrativa</li>
            <li><strong>Evangelho de Filipe:</strong> Foca na relação entre Jesus e Maria Madalena</li>
            <li><strong>Evangelho de Veridade:</strong> Um sermão poético sobre a salvação</li>
            <li><strong>Apócrifo de João:</strong> Revelações de Jesus a João</li>
            <li><strong>Evangelho de Maria:</strong> Destaca o papel de Maria Madalena</li>
        </ul>
        <h3>O Que Eram Esses Textos?</h3>
        <p>A maioria eram <strong>textos gnósticos</strong>. O gnosticismo enfatizava o <em>gnosis</em> (conhecimento espiritual secreto) como caminho para a salvação.</p>
        <h3>Qual a Relação com os Livros Usados Pelos Primeiros Cristãos?</h3>
        <p>A descoberta confirmou algo fundamental: o cristianismo primitivo era <strong>incrivelmente diverso</strong>. Não havia um "cânon" fixo no século II ou III. Diferentes comunidades usavam diferentes evangelhos.</p>
        <p>Os textos de Nag Hammadi <strong>não</strong> faziam parte do cânon bíblico que a Igreja acabou estabelecendo. A Igreja primitiva selecionou gradualmente os textos apostólicos e ortodoxos, rejeitando outros como heréticos.</p>
        <h3>Diferenças Teológicas</h3>
        <div class="comparison">
            <div class="ortodoxia">
                <h4>Ortodoxia</h4>
                <p>Jesus é o Filho de Deus encarnado, que morreu e ressuscitou fisicamente. A salvação vem pela fé e graça.</p>
            </div>
            <div class="gnosticismo">
                <h4>Gnosticismo</h4>
                <p>O material é mau, Jesus veio trazer conhecimento secreto. A salvação é pelo conhecimento, não pela fé.</p>
            </div>
        </div>
    `;
    toggleSecao(nagHammadiSection, conteudo, 'Manuscritos de Nag Hammadi');
});

document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const target = link.dataset.target;
        if (botoesMap[target]) {
            botoesMap[target].click();
        }
    });
});