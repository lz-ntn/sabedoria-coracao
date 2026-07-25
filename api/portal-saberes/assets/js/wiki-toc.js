/**
 * Wiki TOC (Table of Contents) Generator
 * Gera automaticamente tabela de conteúdo para artigos
 */

document.addEventListener('DOMContentLoaded', function() {
    const article = document.querySelector('.artigo-conteudo');
    if (!article) return;

    // Buscar todos os headings h2, h3, h4
    const headings = article.querySelectorAll('h2, h3, h4');
    if (headings.length < 2) return; // Só cria TOC se tiver pelo menos 2 headings

    // Criar container do TOC
    const tocContainer = document.createElement('div');
    tocContainer.className = 'wiki-toc';
    tocContainer.innerHTML = `
        <div class="toc-header">
            <i class="fa-solid fa-list"></i> Índice
        </div>
        <ul class="toc-list"></ul>
    `;

    const tocList = tocContainer.querySelector('.toc-list');

    // Gerar TOC
    headings.forEach((heading, index) => {
        // Criar ID para o heading se não existir
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }

        const level = parseInt(heading.tagName.charAt(1));
        const li = document.createElement('li');
        li.className = 'toc-item toc-level-' + level;

        const link = document.createElement('a');
        link.href = '#' + heading.id;
        link.textContent = heading.textContent;
        link.className = 'toc-link';

        li.appendChild(link);
        tocList.appendChild(li);

        // Scroll suave ao clicar
        link.addEventListener('click', function(e) {
            e.preventDefault();
            heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Inserir TOC antes do artigo
    article.insertBefore(tocContainer, article.firstChild);

    // Destacar seção atual no scroll
    const observerOptions = {
        rootMargin: '-100px 0px -66%',
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                document.querySelectorAll('.toc-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + id) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }, observerOptions);

    headings.forEach(heading => observer.observe(heading));
});

/**
 * Auto-link para termos wiki
 * Converte termos específicos em links para artigos relacionados
 */
function wikiAutoLinks() {
    const article = document.querySelector('.artigo-conteudo');
    if (!article) return;

    // Lista de termos para auto-link (pode ser expandida)
    const wikiTerms = {
        'Gnose': '/artigo/o-que-e-gnose',
        'Epigenética': '/artigo/o-que-e-epigenetica',
        'Hermetismo': '/categoria/hermetismo',
        'Kundalini': '/artigo/o-que-e-kundalini',
        'Teosofia': '/artigo/o-que-e-teosofia',
        'Tao': '/categoria/tao',
        'Coração': '/categoria/coracao'
    };

    let content = article.innerHTML;
    
    for (const [term, link] of Object.entries(wikiTerms)) {
        // Regex para encontrar o termo não-linkado
        const regex = new RegExp(`(?<!href="[^"]*)\\b${term}\\b(?!([^<]*<\\/a>))`, 'gi');
        content = content.replace(regex, `<a href="${link}" class="wiki-link" title="Ver artigo sobre ${term}">${term}</a>`);
    }

    article.innerHTML = content;
}

document.addEventListener('DOMContentLoaded', wikiAutoLinks);