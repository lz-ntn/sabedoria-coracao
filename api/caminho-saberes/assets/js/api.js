/* ==========================================
   API Client - Comunicação com Backend PHP
   ========================================== */

const API = {
    baseURL: '/caminho-saberes/api',

    // ══════════════════════════════════════
    // Requisição base
    // ══════════════════════════════════════
    async request(endpoint, options = {}) {
        const url = this.baseURL + '/' + endpoint;
        const token = (window.APP_DATA && window.APP_DATA.csrf_token) || '';
        const headers = { 'Content-Type': 'application/json' };
        if (token) {
            headers['X-CSRF-Token'] = token;
        }
        const config = { headers, ...options };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || `Erro ${response.status}`);
            }

            return data;
        } catch (error) {
            if (error.name === 'TypeError') {
                console.warn('⚠️ Servidor offline. Usando fallback local.');
                return null;
            }
            throw error;
        }
    },

    // ══════════════════════════════════════
    // Progresso
    // ══════════════════════════════════════

    async buscarProgresso() {
        return this.request('progresso.php');
    },

    async salvarProgresso(licaoId, categoria) {
        return this.request('progresso.php', {
            method: 'POST',
            body: JSON.stringify({ licao_id: licaoId, categoria })
        });
    },

    async resetarProgresso() {
        return this.request('progresso.php?reset=1', {
            method: 'DELETE'
        });
    },

    // ══════════════════════════════════════
    // Newsletter
    // ══════════════════════════════════════

    async inscreverNewsletter(email) {
        return this.request('newsletter.php', {
            method: 'POST',
            body: JSON.stringify({ email })
        });
    },

    // ══════════════════════════════════════
    // Favoritos
    // ══════════════════════════════════════

    async buscarFavoritos() {
        return this.request('favoritos.php');
    },

    async adicionarFavorito(licaoId) {
        return this.request('favoritos.php', {
            method: 'POST',
            body: JSON.stringify({ licao_id: licaoId })
        });
    },

    async removerFavorito(licaoId) {
        return this.request(`favoritos.php?licao_id=${licaoId}`, {
            method: 'DELETE'
        });
    },

    // ══════════════════════════════════════
    // Quiz
    // ══════════════════════════════════════

    async buscarPerguntas(categoria = null) {
        const params = categoria ? `?categoria=${categoria}` : '';
        return this.request('quiz.php' + params);
    },

    async salvarResultadoQuiz(acertos, total, pontuacao, respostas = null) {
        return this.request('quiz.php', {
            method: 'POST',
            body: JSON.stringify({ acertos, total, pontuacao, respostas })
        });
    },

    // ══════════════════════════════════════
    // Estatísticas
    // ══════════════════════════════════════

    async buscarStats() {
        return this.request('stats.php');
    }
};

window.API = API;
