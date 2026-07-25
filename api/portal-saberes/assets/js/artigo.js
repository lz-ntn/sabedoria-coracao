/**
 * Portal Saberes - Página de Artigo
 * v2.0 — Likes, Rating, Favoritos, Compartilhamento, Comentários
 */

'use strict';

const { showToast } = window.PortalApp || {};

function escapeHtml(text) {
    if (!text) return '';
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(text));
    return d.innerHTML;
}

// ═══════════════════════════════════════════
// LIKES
// ═══════════════════════════════════════════
function initLike() {
    const btn = document.querySelector('.like-btn');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        const artigoId = this.dataset.artigo;
        this.disabled = true;

        try {
            const res = await fetch((window.SABERES_URL || '') + '/api/like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ artigo_id: artigoId, acao: 'toggle' })
            });
            const data = await res.json();

            if (data.liked !== undefined) {
                this.classList.toggle('active', data.liked);
                this.querySelector('i').className = data.liked ? 'bi bi-heart-fill' : 'bi bi-heart';
                this.querySelector('.like-count').textContent = data.total;
                this.classList.add('pulse');
                setTimeout(() => this.classList.remove('pulse'), 500);
            }
        } catch (e) {
            console.error('[Like]', e);
        }

        this.disabled = false;
    });
}

// ═══════════════════════════════════════════
// RATING
// ═══════════════════════════════════════════
function initRating() {
    const widgets = document.querySelectorAll('.rating-widget');
    widgets.forEach(widget => {
        const starsContainer = widget.querySelector('.rating-stars');
        if (!starsContainer) return;

        const stars = starsContainer.querySelectorAll('.star');

        stars.forEach(star => {
            // Hover
            star.addEventListener('mouseenter', () => {
                const val = parseInt(star.dataset.value);
                stars.forEach((s, i) => s.classList.toggle('hover', i < val));
            });

            // Leave
            star.addEventListener('mouseleave', () => {
                const current = parseInt(starsContainer.dataset.current || 0);
                stars.forEach((s, i) => {
                    s.classList.remove('hover');
                    s.classList.toggle('active', i < current);
                });
            });

            // Click
            star.addEventListener('click', async () => {
                const artigoId = widget.dataset.artigo;
                const rating = parseInt(star.dataset.value);

                try {
                    const res = await fetch((window.SABERES_URL || '') + '/api/rating.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ artigo_id: artigoId, rating })
                    });
                    const data = await res.json();

                    if (data.media !== undefined) {
                        starsContainer.dataset.current = rating;
                        stars.forEach((s, i) => {
                            s.classList.toggle('active', i < rating);
                            s.querySelector('i').className = i < rating ? 'bi bi-star-fill' : 'bi bi-star';
                            if (i < rating) {
                                s.classList.add('just-rated');
                                setTimeout(() => s.classList.remove('just-rated'), 400);
                            }
                        });
                        widget.querySelector('.rating-info strong').textContent = data.media;
                        widget.querySelector('.rating-count').textContent = `(${data.total})`;
                        if (showToast) showToast('Avaliação registrada!', 'success');
                    }
                } catch (e) {
                    console.error('[Rating]', e);
                }
            });
        });
    });
}

// ═══════════════════════════════════════════
// FAVORITOS
// ═══════════════════════════════════════════
function initFavorite() {
    const btn = document.querySelector('.favorite-btn');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        const artigoId = this.dataset.artigo;
        this.disabled = true;

        try {
            const res = await fetch((window.SABERES_URL || '') + '/api/favorito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ artigo_id: artigoId, acao: 'toggle' })
            });

            if (res.status === 401) {
                if (showToast) showToast('Faça login para favoritar', 'error');
                setTimeout(() => location.href = '/auth/login.php', 1500);
                return;
            }

            const data = await res.json();
            if (data.favorito !== undefined) {
                this.classList.toggle('active', data.favorito);
                this.querySelector('i').className = data.favorito ? 'bi bi-bookmark-fill' : 'bi bi-bookmark';
                this.querySelector('.fav-count').textContent = data.total;
                this.classList.add('pulse');
                setTimeout(() => this.classList.remove('pulse'), 500);
            }
        } catch (e) {
            console.error('[Favorite]', e);
        }

        this.disabled = false;
    });
}

// ═══════════════════════════════════════════
// COMPARTILHAR
// ═══════════════════════════════════════════
function initShare() {
    const shareBtn = document.querySelector('.share-btn');
    const shareModal = document.getElementById('shareModal');
    if (!shareBtn || !shareModal) return;

    shareBtn.addEventListener('click', () => {
        const isVisible = shareModal.style.display === 'block';
        shareModal.style.display = isVisible ? 'none' : 'block';
        if (!isVisible) {
            shareModal.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    shareModal.querySelectorAll('[data-platform]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const platform = btn.dataset.platform;
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);

            const shareUrls = {
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
                twitter: `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
                whatsapp: `https://wa.me/?text=${title}%20${url}`,
                telegram: `https://t.me/share/url?url=${url}&text=${title}`,
                linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
                email: `mailto:?subject=${title}&body=${title}%20${url}`,
            };

            if (platform === 'copy') {
                try {
                    await navigator.clipboard.writeText(window.location.href);
                    if (showToast) showToast('Link copiado!', 'success');
                } catch (e) {
                    if (showToast) showToast('Não foi possível copiar', 'error');
                }
            } else {
                window.open(shareUrls[platform], '_blank', 'width=600,height=400');
            }

            // Registrar compartilhamento
            try {
                await fetch((window.SABERES_URL || '') + '/api/compartilhar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        artigo_id: shareBtn.dataset.artigo,
                        plataforma: platform
                    })
                });
            } catch (e) {}
        });
    });
}

// ═══════════════════════════════════════════
// COMENTÁRIOS
// ═══════════════════════════════════════════
function initComments() {
    const lista = document.getElementById('comentarios-lista');
    const form = document.getElementById('form-comentario');
    const countEl = document.getElementById('comentarios-count');
    if (!lista) return;

    async function carregar() {
        try {
            const artigoId = form?.querySelector('[name="artigo_id"]')?.value;
            const res = await fetch((window.SABERES_URL || '') + '/api/comentario-listar.php?artigo_id=' + artigoId);
            const data = await res.json();

            if (countEl) countEl.textContent = data.total;

            if (data.total === 0) {
                lista.innerHTML = '<p class="comentarios-empty"><i class="bi bi-chat"></i> Nenhum comentário ainda. Seja o primeiro!</p>';
                return;
            }
            lista.innerHTML = renderComentarios(data.comentarios);
            attachCommentEvents();
        } catch (e) {
            lista.innerHTML = '<p class="comentarios-empty">Erro ao carregar comentários.</p>';
        }
    }

    function renderComentarios(comentarios) {
        return comentarios.map(c => `
            <div class="comentario" data-id="${c.id}">
                <div class="comentario-avatar">
                    <i class="bi bi-person-circle"></i>
                    ${c.usuario_nivel === 'admin' ? '<span class="badge-admin">Admin</span>' : ''}
                </div>
                <div class="comentario-body">
                    <div class="comentario-meta">
                        <strong>${escapeHtml(c.autor_nome || 'Anônimo')}</strong>
                        <span class="comentario-tempo">${c.tempo_relativo}</span>
                    </div>
                    <div class="comentario-conteudo">${escapeHtml(c.conteudo).replace(/\n/g, '<br>')}</div>
                    <div class="comentario-acoes">
                        <button class="comentario-like" data-id="${c.id}">
                            <i class="bi bi-heart"></i> <span>${c.likes || 0}</span>
                        </button>
                        <button class="comentario-reply" data-id="${c.id}" data-nome="${escapeHtml(c.autor_nome || 'Anônimo')}">
                            <i class="bi bi-reply"></i> Responder
                        </button>
                    </div>
                    ${c.children?.length > 0 ? `<div class="comentarios-filhos">${renderComentarios(c.children)}</div>` : ''}
                </div>
            </div>
        `).join('');
    }

    function attachCommentEvents() {
        // Reply buttons
        lista.querySelectorAll('.comentario-reply').forEach(btn => {
            btn.addEventListener('click', function () {
                const parentId = this.dataset.id;
                const nome = this.dataset.nome;
                document.getElementById('parent_id').value = parentId;
                document.getElementById('form-info').innerHTML = `<i class="bi bi-reply"></i> Respondendo a <strong>${nome}</strong>`;
                document.getElementById('btn-texto').textContent = 'Enviar Resposta';
                document.getElementById('btn-cancel-reply').style.display = 'inline-flex';
                document.getElementById('comentario-texto').focus();
                document.getElementById('form-comentario').scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });

        // Like buttons
        lista.querySelectorAll('.comentario-like').forEach(btn => {
            btn.addEventListener('click', async function () {
                const id = this.dataset.id;
                try {
                    const res = await fetch((window.SABERES_URL || '') + '/api/comentario-like.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ comentario_id: id, acao: 'toggle' })
                    });
                    const data = await res.json();
                    if (data.liked !== undefined) {
                        this.classList.toggle('active', data.liked);
                        this.querySelector('i').className = data.liked ? 'bi bi-heart-fill' : 'bi bi-heart';
                        this.querySelector('span').textContent = data.total;
                    }
                } catch (e) {}
            });
        });
    }

    // Cancel reply
    document.getElementById('btn-cancel-reply')?.addEventListener('click', function () {
        document.getElementById('parent_id').value = '';
        document.getElementById('form-info').innerHTML = '';
        document.getElementById('btn-texto').textContent = 'Enviar Comentário';
        this.style.display = 'none';
    });

    // Submit form
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            try {
                const res = await fetch((window.SABERES_URL || '') + '/api/comentario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (res.ok) {
                    if (showToast) showToast('Comentário publicado!', 'success');
                    form.reset();
                    document.getElementById('parent_id').value = '';
                    document.getElementById('form-info').innerHTML = '';
                    document.getElementById('btn-texto').textContent = 'Enviar Comentário';
                    document.getElementById('btn-cancel-reply').style.display = 'none';
                    await carregar();
                } else {
                    if (showToast) showToast(result.error || 'Erro ao enviar', 'error');
                }
            } catch (e) {
                if (showToast) showToast('Erro de conexão', 'error');
            }

            submitBtn.disabled = false;
        });
    }

    // Carregar comentários
    carregar();
}

// ═══════════════════════════════════════════
// INICIALIZAÇÃO
// ═══════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    initLike();
    initRating();
    initFavorite();
    initShare();
    initComments();
});
