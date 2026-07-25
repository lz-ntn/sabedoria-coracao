const tituloP = document.getElementById('titulo');

const h1 = document.createElement('h1');

h1.textContent = 'Viver';
//h1.style.display = 'none';
tituloP.appendChild(h1);


// início
const inicioV = document.getElementById('inicio');

const p = document.createElement('p');

p.textContent = 'Viver é o ato de existir, possuir vida biológica e, num sentido mais profundo, aproveitar a existência, cultivando saúde física, mental e relações afetivas. Vai além da sobrevivência, implicando em propósito, bem-estar e equilíbrio, muitas vezes diferenciado de apenas "morar" ou habitar um local';

inicioV.appendChild(p);

// Evento para mostrar conteudo
const botaoSemFiltros = document.getElementById('btnSemFiltros');
const divSemFiltros = document.getElementById('mostraSemFiltros');

botaoSemFiltros.addEventListener('click', () => {
    divSemFiltros.classList.toggle('ativo');
    if (divSemFiltros.classList.contains('ativo')) {
        divSemFiltros.classList.add('ativo');
        botaoSemFiltros.textContent = 'Esconder';
        botaoSemFiltros.className = 'btn btn-danger'
        const conteudo = `
			<p>Viver é uma porra caótica, linda e brutal ao mesmo tempo. É acordar todo dia dentro de um corpo que vai envelhecer e morrer, com uma mente que questiona tudo, sentindo fome, tesão, medo, amor, raiva, tédio e êxtase — tudo misturado</p>
			<h5>Sem filtros significa:</h5>
			<ul>
				<li>
					<strong>Aceitar a impermanência</strong>
					<p>Nada dura. Relacionamentos acabam, corpo falha, ideias mudam. A morte não é "se" vai acontecer, é "quando". Isso não é depressivo; é libertador. Faz você parar de adiar o que importa</p>
				</li>
				<li>
					<strong>Sentir tudo cru</strong>
					<p>Dor emocional, solidão, alegria boba, o vazio existencial às 3 da manhã. A maioria das pessoas foge disso com distrações (redes sociais, trabalho sem fim, religião como muleta, drogas, sexo sem conexão). Viver sem filtro é ficar presente nisso, sem anestesia</p>
				</li>
				<li>
					<strong>Buscar significado na bagunça</strong>
					<p>O universo não veio com manual. Não tem um "propósito" pronto te esperando. Você constrói o seu através de escolhas: amar alguém de verdade (com defeitos e tudo), criar algo (arte, família, ideia, jardim), entender como as coisas funcionam (ciência, filosofia), ajudar os outros sem esperar aplauso</p>
				</li>
				<li>
					<strong>Responsabilidade radical</strong>
					<p>Sua vida é sua. Culpar "o sistema", "Deus", "os pais", "a sociedade" é confortável, mas infantil. Adulto é olhar no espelho e decidir quem você vai ser hoje, mesmo sabendo que vai errar</p>
				</li>
				<li>
					<strong>Curiosidade sem medo</strong>
					<p>Questionar tudo, inclusive suas próprias crenças. O bom viver vem de entender o Universo (como xAI curte), o corpo, a mente, as relações humanas — sem dogmas que te protejam da realidade</p>
				</li>
			</ul>
			<h3><strong>Resumindo</strong>:</h3>
			<p>Viver sem filtros é estar vivo de verdade, não sobreviver no piloto automático. É desconfortável pra caralho no começo (porque a maioria vive dopada por ilusões), mas traz uma liberdade absurda. Tipo: "Eu sei que sou poeira cósmica consciente por um breve momento, então vou queimar isso bem."</p>
			<h4>O que é "Viver sem Filtros"?</h4>
			<p>Viver náo é apenas existrir biologicamente ou sobreviver no "piloto automático". É:</p>
			<ul class="list-group">
        		<li>
        			<strong>Aceitar a realidade crua</strong>
        			<p>Reconhecer que a vida é caótica, linda e brutal. Envolve sentir tudo (medo, amor, tédio, êxtase) sem anestesia emocional</p>
        		</li>
        		<li>
        			<strong>Entender a finitude</strong>
        			<p>Saber que nada dura para sempre (relacionamentos, o corpo, ideias) e que a morte é certa. Essa não é uma visão depressiva, mas libertadora, pois faz você parar de adiar o que importa</p>
        		</li>
        		<li>
        			<strong>Ter responsabilidade total</strong>
        			<p> Parar de culpar "o sistema", Deus ou os pais. Ser adulto é olhar no espelho e decidir quem você será hoje, sabendo que vai errar</p>
        		</li>
        		<li>
        			<strong>Construir seu próprio propósito</strong>
        			<p> O universo não tem um manual. Você cria sentido através de amar alguém (com defeitos), criar algo (arte, família), entender o mundo ou ajudar os outros sem esperar aplauso</p>
        		</li>	
			</ul>
		`;
        divSemFiltros.innerHTML = conteudo;
    } else {
        divSemFiltros.classList.remove('ativo');
        botaoSemFiltros.textContent = 'Abrir Novamente';
        botaoSemFiltros.className = 'btn btn-warning';
        divSemFiltros.innerHTML = '';
    }
});
// Segundo botao com interação
const botaoPorqueAbalou = document.getElementById('btnAbalou');
const divPorqueAbalou = document.getElementById('mostraAbalou');

botaoPorqueAbalou.addEventListener('click', () => {
    divPorqueAbalou.classList.toggle('ativo');
    if (divPorqueAbalou.classList.contains('ativo')) {
        divPorqueAbalou.classList.add('ativo');
        botaoPorqueAbalou.textContent = 'Esconder';
        botaoPorqueAbalou.className = 'btn btn-danger';

        const conteudo1 = `
			<p>Ênfase forte em ética prática (os "Dois Caminhos": da vida vs. da morte), jejum, oração, hospitalidade, rejeição ao aborto e infanticídio (comum na época romana)</p>
			<p>Estrutura comunitária mais simples, com profetas itinerantes, bispos e diáconos eleitos localmente — bem diferente da hierarquia pesada e centralizada que a Igreja Católica (e outras) desenvolveram depois</p>
			<p>Rituais mais "diretos" e menos ritualísticos/formais que séculos de tradição acumularam</p>
			<hr class="text-warning">
			<p>Muitos estudiosos e críticos viram nisso uma visão mais primitiva e "pura" do cristianismo, que contrastava com práticas, dogmas e poderes institucionais que surgiram depois (Constantino em diante, concílios, etc.). Parecia revelar que muita coisa era desenvolvimento humano posterior, não "entregue pronto pelos apóstolos". Isso incomodou quem defendia que a tradição da Igreja era intocável e divina em todos os detalhes</p>
			<h3>Sobre a perseguição e o que "tentavam esconder"</h3>
			<p>Não rolou uma grande "perseguição específica" à Didache em 1884 — o texto foi aceito como autêntico depois de um tempo (alguns chamaram de falsificação no início, como sempre acontece). Mas o padrão histórico é claro: instituições religiosas (não só cristãs) frequentemente resistem ou minimizam descobertas que ameaçam sua autoridade</p>
			<h4>Exemplos clássicos</h4>
			<p>Textos gnósticos ou apócrifos que mostram diversidade enorme nos primeiros séculos (muita variação teológica, papéis de mulheres diferentes, etc.). Críticas históricas à Bíblia, evolução, etc</p>
			<h4>A perseguição revela o medo</h4>
			<p>Quando uma instituição persegue ideias, livros ou pessoas em vez de debater abertamente com evidências, geralmente está protegendo poder, narrativa ou renda, não "a verdade pura". História mostra isso repetidamente — da Inquisição a censuras modernas. O que tentam "esconder" muitas vezes é que religiões são evoluções culturais humanas, cheias de acréscimos, política e adaptações, não um bloco monolítico caído do céu</p>
			<p>Isso não destrói fé necessariamente. Muita gente vê beleza na evolução da tradição. Mas sem filtro, a lição é: questionar fontes primárias é saudável. Não engula narrativas institucionais de boca aberta. Leia os textos antigos você mesmo, compare com o que se pratica hoje</p>
			<h4>Ligando os pontos</h4>
			<p>Viver sem filtros inclui olhar a religião (ou qualquer ideologia) sem véu. A Didache (e descobertas parecidas) nos lembra que humanos constroem sistemas de sentido desde sempre. Eles podem ajudar a viver melhor (comunidade, ética, consolo), mas também podem virar jaulas quando viram "a única verdade" intocável</p>
			<h3>Se você quer viver verdadeiramente</h3>
			<p>Busque evidências, não conforto</p>
			<p>Ame e seja bom porque faz sentido, não por medo de inferno ou promessa de céu</p>
			<p>Aceite o mistério. O universo é gigantesco e estranho. Ciência, filosofia e experiência diteta ajudam mais que dogmas rígidos</p>
			<p>Seja honesto consigo. O que você ralmente acredita, e por quê?</p>
		`;
        divPorqueAbalou.innerHTML = conteudo1;
    } else {
        divPorqueAbalou.classList.remove('ativo');
        botaoPorqueAbalou.textContent = 'Abrir Novamente';
        botaoPorqueAbalou.className = 'btn btn-warning';
        divPorqueAbalou.innerHTML = '';
    }
});
// Relatórios NL
const botaoRelatorios = document.getElementById("btnRelat");
const divRelatorios = document.getElementById("mostrarRelat");

botaoRelatorios.addEventListener('click', () => {
    divRelatorios.classList.toggle('ativo');
    if (divRelatorios.classList.contains('ativo')) {
        divRelatorios.classList.add('ativo');
        botaoRelatorios.textContent = 'Fechar';

        const conteudo3 = `
			<p>A resistência de uma organização frente a novas evidências não deve ser lida como mera teimosia ideológica, mas como uma tática de autopreservação de sistema. Instituições operam através de "filtros" que selecionam quais fatos podem ou não integrar a narrativa pública, visando manter a coesão e o controle sobre seus membros</p>
		`;

        divRelatorios.innerHTML = conteudo3;
    } else {
        divRelatorios.classList.remove('ativo');
        botaoRelatorios.textContent = 'Abrir Novamente';

        divRelatorios.innerHTML = '';
    }
});