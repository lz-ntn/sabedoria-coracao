/* ============================================
   Biblioteca - Portal Saberes Ancestrais
   Depende de window.BIB_DATA definido no HTML
   ============================================ */

(function() {
    'use strict';

    const ALL = window.BIB_DATA || [];

    // ── Stats counter ──
    document.querySelectorAll('[data-count]').forEach(function(el) {
        var target = parseInt(el.dataset.count);
        var duration = 800;
        var start = performance.now();
        function update(now) {
            var pct = Math.min((now - start) / duration, 1);
            el.textContent = Math.floor(pct * target).toLocaleString('pt-BR');
            if (pct < 1) requestAnimationFrame(update);
            else el.textContent = target.toLocaleString('pt-BR');
        }
        requestAnimationFrame(update);
    });

    // ── Search (debounce) ──
    var searchInput = document.getElementById('bibSearch');
    var clearBtn = document.getElementById('clearSearch');
    var searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 150);
            if (clearBtn) clearBtn.style.display = searchInput.value ? 'block' : 'none';
        });
    }
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            applyFilters();
        });
    }

    // Keyboard shortcut
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            e.preventDefault();
            if (searchInput) searchInput.focus();
        }
        if (e.key === 'Escape') { closePreview(); }
    });

    // ── Tag filter ──
    var activeTag = '';

    document.querySelectorAll('.tag-pill').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tag-pill').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            activeTag = btn.dataset.tag;
            var clear = document.getElementById('tagClear');
            if (clear) clear.style.display = activeTag ? 'inline' : 'none';
            applyFilters();
        });
    });

    var tagClear = document.getElementById('tagClear');
    if (tagClear) {
        tagClear.addEventListener('click', function() {
            var allBtn = document.querySelector('.tag-pill[data-tag=""]');
            if (allBtn) allBtn.click();
        });
    }

    window.filterByTag = function(tag) {
        var btn = document.querySelector('.tag-pill[data-tag="' + CSS.escape(tag) + '"]');
        if (btn) btn.click();
    };

    // ── Axis filter ──
    document.querySelectorAll('.axis-card').forEach(function(card) {
        card.addEventListener('click', function() {
            card.classList.toggle('active');
            applyFilters();
        });
    });

    // ── Sort ──
    var sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', applySort);
    }

    function applySort() {
        var sort = sortSelect ? sortSelect.value : 'recent';
        document.querySelectorAll('.biblioteca-grid').forEach(function(grid) {
            var items = Array.from(grid.querySelectorAll('.bib-item:not(.hidden)'));
            items.sort(function(a, b) {
                if (sort === 'title') return a.dataset.title.localeCompare(b.dataset.title);
                if (sort === 'views') return parseInt(b.dataset.views) - parseInt(a.dataset.views);
                return new Date(b.dataset.date) - new Date(a.dataset.date);
            });
            items.forEach(function(item) { grid.appendChild(item); });
        });
    }

    // ── View toggle ──
    var viewGrid = document.getElementById('viewGrid');
    var viewList = document.getElementById('viewList');
    if (viewGrid) viewGrid.addEventListener('click', function() { setView('grid'); });
    if (viewList) viewList.addEventListener('click', function() { setView('list'); });

    function setView(view) {
        document.querySelectorAll('.toolbar-btn[data-view]').forEach(function(b) { b.classList.remove('active'); });
        var btn = document.querySelector('.toolbar-btn[data-view="' + view + '"]');
        if (btn) btn.classList.add('active');
        document.querySelectorAll('.biblioteca-grid').forEach(function(g) {
            g.classList.toggle('list-view', view === 'list');
        });
    }

    // ── Random article ──
    var randomBtn = document.getElementById('randomBtn');
    if (randomBtn) {
        randomBtn.addEventListener('click', function() {
            var visible = ALL.filter(function(a) {
                var el = document.querySelector('.bib-item[data-slug="' + a.slug + '"]');
                return el && !el.classList.contains('hidden');
            });
            if (visible.length === 0) return;
            var pick = visible[Math.floor(Math.random() * visible.length)];
            window.location.href = BIB_APP_URL + '/artigo/' + pick.slug;
        });
    }

    // ── Main filter ──
    function applyFilters() {
        var q = searchInput ? searchInput.value.toLowerCase().trim() : '';
        var activeAxes = new Set();
        document.querySelectorAll('.axis-card.active').forEach(function(c) {
            activeAxes.add(c.dataset.cats);
        });

        var visibleCount = 0;

        document.querySelectorAll('.bib-item').forEach(function(item) {
            var title = item.dataset.title || '';
            var tags = (item.dataset.tags || '').toLowerCase();
            var resumo = (item.dataset.resumo || '').toLowerCase();
            var cat = item.dataset.category || '';
            var itemTags = tags.split(',').map(function(t) { return t.trim(); }).filter(Boolean);

            var matchSearch = !q || title.includes(q) || tags.includes(q) || resumo.includes(q);

            var matchTag = !activeTag || itemTags.indexOf(activeTag) !== -1;

            var matchAxis = true;
            if (activeAxes.size > 0) {
                matchAxis = false;
                activeAxes.forEach(function(cats) {
                    if (cats.split(',').indexOf(cat) !== -1) matchAxis = true;
                });
            }

            var visible = matchSearch && matchTag && matchAxis;
            item.classList.toggle('hidden', !visible);
            if (visible) visibleCount++;
        });

        // Category visibility
        document.querySelectorAll('.biblioteca-categoria').forEach(function(cat) {
            var visibleItems = cat.querySelectorAll('.bib-item:not(.hidden)').length;
            var countBadge = cat.querySelector('.cat-filter-count');
            if (countBadge) countBadge.remove();
            cat.style.display = visibleItems > 0 ? '' : 'none';
            if (visibleItems > 0 && visibleItems < cat.querySelectorAll('.bib-item').length) {
                var badge = document.createElement('span');
                badge.className = 'cat-filter-count';
                badge.textContent = visibleItems;
                cat.querySelector('.cat-header h2').after(badge);
            }
        });

        var resultCount = document.getElementById('resultCount');
        if (resultCount) {
            resultCount.textContent = 'Mostrando ' + visibleCount + ' artigo' + (visibleCount !== 1 ? 's' : '') +
                (visibleCount !== ALL.length ? ' de ' + ALL.length : '');
        }

        applySort();
    }

    // ── Preview modal ──
    var previewCache = {};

    window.openPreview = function(event, slug) {
        if (event.target.closest('.tag')) return;
        var modal = document.getElementById('previewModal');
        var title = document.getElementById('previewTitle');
        var content = document.getElementById('previewContent');
        var link = document.getElementById('previewLink');

        document.getElementById('previewBody').style.display = 'block';
        title.textContent = 'Carregando...';
        content.innerHTML = '<p style="opacity:.5">Carregando conteúdo...</p>';
        link.href = BIB_APP_URL + '/artigo/' + slug;
        modal.style.display = 'flex';

        if (previewCache[slug]) {
            title.textContent = previewCache[slug].titulo;
            content.innerHTML = previewCache[slug].html;
            return;
        }

        fetch(BIB_APP_URL + '/api/artigo-preview.php?slug=' + encodeURIComponent(slug))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.error) { content.innerHTML = '<p style="opacity:.5">' + data.error + '</p>'; return; }
                title.textContent = data.titulo;
                content.innerHTML = data.html;
                previewCache[slug] = { titulo: data.titulo, html: data.html };
            })
            .catch(function() {
                content.innerHTML = '<p style="opacity:.5">Erro ao carregar.</p>';
            });
    };

    window.closePreview = function() {
        var modal = document.getElementById('previewModal');
        if (modal) modal.style.display = 'none';
    };

    // ── Init ──
    applySort();
})();
