// Funcinalidade de busca para Saberes de Coração site
class SiteSearch {
  constructor() {
    this.index = {};
    this.pages = [
      { url: "index.html", title: "Início", content: "" },
      { url: "biblioteca.html", title: "Biblioteca", content: "" },
      { url: "sobre.html", title: "Sobre", content: "" },
      { url: "referencias.html", title: "Referências", content: "" },
      { url: "contato.html", title: "Contato", content: "" },
      { url: "conteudos/gnose.html", title: "Gnose", content: "" },
      { url: "conteudos/teosofia.html", title: "Teosofia", content: "" },
      { url: "conteudos/hermetismo.html", title: "Hermetismo", content: "" },
      {
        url: "conteudos/cristianismo.html",
        title: "Cristianismo Primitivo",
        content: "",
      },
      { url: "conteudos/kundalini.html", title: "Kundalini", content: "" },
      { url: "conteudos/pneuma.html", title: "Pneuma", content: "" },
      { url: "conteudos/meditacao.html", title: "Meditação", content: "" },
      { url: "conteudos/coracao.html", title: "Coração", content: "" },
      { url: "conteudos/tao.html", title: "Tao", content: "" },
      { url: "conteudos/epigenetica.html", title: "Epigenética", content: "" },
      {
        url: "conteudos/fisica-espiritual.html",
        title: "Física Espiritual",
        content: "",
      },
      {
        url: "conteudos/ignorancia.html",
        title: "Ignorância Humana",
        content: "",
      },
    ];

    this.initialize();
  }

  async initialize() {
    const isFileProtocol = window.location.protocol === "file:";
    if (isFileProtocol) {
      console.warn(
        "Search: running under file:// protocol. Some browsers may block local fetches.",
      );
    }

    // Fetch and index all pages
    for (const page of this.pages) {
      try {
        const response = await fetch(page.url);
        if (response.ok) {
          const html = await response.text();
          // Extract text content from HTML
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, "text/html");

          // Remove script and style elements
          doc.querySelectorAll("script, style").forEach((el) => el.remove());

          // Get text content
          const textContent = doc.body ? doc.body.textContent || "" : "";
          page.content = this.cleanText(textContent);

          // Index the content
          this.indexPage(page);
        }
      } catch (error) {
        console.warn(`Failed to load page ${page.url}:`, error);
      }
    }

    console.log(
      "Search index initialized with",
      Object.keys(this.index).length,
      "terms",
    );
  }

  cleanText(text) {
    return text
      .toLowerCase()
      .replace(/[^\w\sáàâãéèêíìîóòôõúùûüç]/g, " ") // Keep Portuguese characters
      .replace(/\s+/g, " ") // Normalize whitespace
      .trim();
  }

  indexPage(page) {
    const words = page.content.split(/\s+/);
    const wordFrequency = {};

    // Count word frequency in this page
    words.forEach((word) => {
      if (word.length > 2) {
        // Ignore very short words
        wordFrequency[word] = (wordFrequency[word] || 0) + 1;
      }
    });

    // Add to global index
    Object.entries(wordFrequency).forEach(([word, count]) => {
      if (!this.index[word]) {
        this.index[word] = [];
      }
      this.index[word].push({ page: page, count });
    });
  }

  search(query) {
    const cleanQuery = this.cleanText(query);
    const queryWords = cleanQuery.split(/\s+/).filter((w) => w.length > 1);

    if (queryWords.length === 0) {
      return [];
    }

    // Score pages based on query term frequency
    const pageScores = {};

    queryWords.forEach((queryWord) => {
      if (this.index[queryWord]) {
        this.index[queryWord].forEach(({ page, count }) => {
          const pageUrl = page.url;
          if (!pageScores[pageUrl]) {
            pageScores[pageUrl] = { page: page, score: 0 };
          }
          // Simple TF-IDF like scoring (term frequency only for simplicity)
          pageScores[pageUrl].score += count;
        });
      }
    });

    // Sort by score descending
    const results = Object.values(pageScores)
      .filter((result) => result.score > 0)
      .sort((a, b) => b.score - a.score)
      .map((result) => result.page);

    return results;
  }

  renderResults(results, containerElement) {
    containerElement.innerHTML = "";

    if (results.length === 0) {
      containerElement.innerHTML = `
        <div class="search-results-empty">
          <p>Nenhum resultado encontrado para sua busca.</p>
          <p>Tente termos diferentes ou mais específicos.</p>
        </div>
      `;
      return;
    }

    const resultsHTML = `
      <div class="search-results">
        <h3>Resultados da busca (${results.length} encontrados)</h3>
        <div class="search-results-list">
          ${results
            .map(
              (page) => `
            <article class="search-result-item">
              <a href="${page.url}" class="search-result-link">
                <h4>${page.title}</h4>
                <p>${this.generateSnippet(page.content)}</p>
              </a>
            </article>
          `,
            )
            .join("")}
        </div>
      </div>
    `;

    containerElement.innerHTML = resultsHTML;
  }

  generateSnippet(text, maxLength = 200) {
    if (text.length <= maxLength) {
      return text;
    }

    // Try to break at a sentence boundary
    const snippet = text.substring(0, maxLength);
    const lastPeriod = snippet.lastIndexOf(".");
    const lastComma = snippet.lastIndexOf(",");
    const breakPoint = Math.max(lastPeriod, lastComma);

    if (breakPoint > maxLength * 0.7) {
      // If we can break reasonably close to the end
      return text.substring(0, breakPoint + 1) + "...";
    }

    return text.substring(0, maxLength) + "...";
  }
}

// Initialize search when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  const search = new SiteSearch();

  // Find search elements
  const searchInput = document.getElementById("search-input");
  const searchButton = document.getElementById("search-button");

  if (searchInput && searchButton) {
    // Create results container
    const resultsContainer = document.createElement("div");
    resultsContainer.id = "search-results-container";
    resultsContainer.style.display = "none";
    resultsContainer.style.position = "absolute";
    resultsContainer.style.top = "100%";
    resultsContainer.style.left = "0";
    resultsContainer.style.right = "0";
    resultsContainer.style.background = "var(--bg-card)";
    resultsContainer.style.border = "1px solid var(--borda)";
    resultsContainer.style.borderRadius = "12px";
    resultsContainer.style.boxShadow = "var(--sombra)";
    resultsContainer.style.zIndex = "1000";
    resultsContainer.style.maxHeight = "400px";
    resultsContainer.style.overflowY = "auto";
    resultsContainer.style.marginTop = "0.5rem";

    // Add to search container
    const searchContainer = searchInput.parentElement;
    searchContainer.style.position = "relative";
    searchContainer.appendChild(resultsContainer);

    // Handle search
    const performSearch = () => {
      const query = searchInput.value.trim();

      if (query.length < 2) {
        resultsContainer.style.display = "none";
        return;
      }

      // Show loading state
      resultsContainer.innerHTML =
        '<div class="search-loading">Buscando...</div>';
      resultsContainer.style.display = "block";

      // Perform search
      setTimeout(() => {
        const results = search.search(query);
        search.renderResults(results, resultsContainer);
      }, 100); // Small delay for better UX
    };

    // Event listeners
    searchButton.addEventListener("click", performSearch);

    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        performSearch();
      }
    });

    // Hide results when clicking outside
    document.addEventListener("click", (e) => {
      if (
        !searchContainer.contains(e.target) &&
        e.target !== searchInput &&
        e.target !== searchButton
      ) {
        resultsContainer.style.display = "none";
      }
    });

    // Hide results when Escape key is pressed
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        resultsContainer.style.display = "none";
      }
    });
  }
});
