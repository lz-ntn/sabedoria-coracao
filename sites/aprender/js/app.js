(function() {
  'use strict';

  function qs(s, ctx) { return (ctx || document).querySelector(s); }
  function qsa(s, ctx) { return (ctx || document).querySelectorAll(s); }

  /* === TABS === */
  var Tabs = {
    init: function() {
      qsa('.tab-trigger').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var target = this.getAttribute('data-tab');
          var wrap = this.closest('.tabs-wrapper') || this.closest('[class*="tabs"]');
          if (!wrap) wrap = document;
          qsa('.tab-trigger', wrap).forEach(function(b) { b.classList.remove('active'); });
          this.classList.add('active');
          qsa('.tab-pane', wrap).forEach(function(p) { p.classList.remove('active'); });
          var pane = qs('.tab-pane[data-pane="' + target + '"]', wrap);
          if (pane) pane.classList.add('active');
        });
      });
      qsa('.tabs-wrapper').forEach(function(w) {
        var first = qs('.tab-trigger', w);
        var firstPane = qs('.tab-pane', w);
        if (first && !first.classList.contains('active')) first.classList.add('active');
        if (firstPane && !firstPane.classList.contains('active')) firstPane.classList.add('active');
      });
    }
  };

  /* === ACCORDION === */
  var Accordion = {
    init: function() {
      qsa('.accordion-trigger').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var item = this.closest('.accordion-item');
          var isOpen = item.classList.contains('open');
          var group = this.closest('.accordion');
          qsa('.accordion-item', group).forEach(function(i) { i.classList.remove('open'); });
          if (!isOpen) item.classList.add('open');
        });
      });
    }
  };

  /* === PROGRESSO === */
  var Progresso = {
    chave: 'progressoSaberes',

    get: function() {
      try { return JSON.parse(localStorage.getItem(this.chave)) || {}; }
      catch(e) { return {}; }
    },

    salvar: function(d) { localStorage.setItem(this.chave, JSON.stringify(d)); },

    marcar: function(el, cat) {
      var id = el.getAttribute('data-lesson');
      var p = this.get();
      p[id] = p[id] ? { concluido: !p[id].concluido, data: new Date().toISOString() }
                     : { concluido: true, data: new Date().toISOString() };
      this.salvar(p);
      this.atualizar(cat);
      el.innerHTML = p[id].concluido
        ? '<i class="bi bi-check2-square"></i> Concluído'
        : '<i class="bi bi-check2-square"></i> Marcar como estudado';
      var dot = el.closest('.accordion-item').querySelector('.lesson-dot i');
      if (dot) dot.className = p[id].concluido ? 'bi bi-check-circle-fill' : 'bi bi-circle';
    },

    atualizar: function(cat) {
      var p = this.get();
      var prefix = cat + '-';
      var feitos = 0, total = 0;
      Object.keys(p).forEach(function(k) { if (k.startsWith(prefix) && p[k].concluido) feitos++; });
      qsa('[data-cat="' + cat + '"]').forEach(function(el) {
        var fill = el.querySelector('.progress-track-fill');
        if (!fill) return;
        var totalEls = qsa('.accordion-item[data-lesson^="' + cat + '-"]').length;
        total = totalEls || 1;
        var pct = Math.min(Math.round((feitos / total) * 100), 100);
        fill.style.width = pct + '%';
      });
      var status = document.getElementById('s-' + cat);
      if (status) {
        total = qsa('.accordion-item[data-lesson^="' + cat + '-"]').length || 1;
        status.textContent = Math.min(Math.round((feitos / total) * 100), 100) + '%';
      }
    }
  };

  /* === BUSCA === */
  var Busca = {
    init: function() {
      var toggle = document.getElementById('search-toggle');
      var modal = document.getElementById('search-modal');
      if (!toggle || !modal) return;
      toggle.addEventListener('click', function() { modal.classList.add('open'); });
      qsa('.modal-close', modal).forEach(function(b) {
        b.addEventListener('click', function() { modal.classList.remove('open'); });
      });
      modal.addEventListener('click', function(e) { if (e.target === modal) modal.classList.remove('open'); });
      var input = document.getElementById('search-input');
      var results = document.getElementById('search-results');
      if (!input || !results) return;
      input.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        if (!q) { results.innerHTML = ''; return; }
        var found = [];
        qsa('.accordion-item').forEach(function(item) {
          if (item.textContent.toLowerCase().includes(q)) {
            var title = qs('.accordion-trigger h3', item);
            found.push({ title: title ? title.textContent.trim() : 'Item', el: item });
          }
        });
        if (!found.length) { results.innerHTML = '<p style="opacity:0.5;padding:0.5rem 0;">Nenhum resultado.</p>'; return; }
        results.innerHTML = found.map(function(f) {
          return '<div class="search-hit" style="padding:0.4rem 0;border-bottom:1px solid var(--cor-borda);cursor:pointer;font-size:0.9rem;">' + f.title + '</div>';
        }).join('');
        qsa('.search-hit', results).forEach(function(item, i) {
          item.addEventListener('click', function() {
            modal.classList.remove('open');
            found[i].el.scrollIntoView({ behavior: 'smooth' });
            setTimeout(function() { found[i].el.classList.add('open'); }, 500);
          });
        });
      });
    }
  };

  /* === TEMA === */
  var Tema = {
    init: function() {
      var toggle = document.getElementById('theme-toggle');
      var icon = document.getElementById('theme-icon');
      if (!toggle) return;
      var salvo = localStorage.getItem('temaSaberes') || 'escuro';
      if (salvo === 'claro') { this.claro(); if (icon) icon.className = 'bi bi-sun'; }
      toggle.addEventListener('click', function() {
        if (document.documentElement.getAttribute('data-tema') === 'claro') {
          Tema.escuro(); if (icon) icon.className = 'bi bi-moon-stars';
        } else {
          Tema.claro(); if (icon) icon.className = 'bi bi-sun';
        }
      });
    },
    claro: function() {
      document.documentElement.setAttribute('data-tema', 'claro');
      localStorage.setItem('temaSaberes', 'claro');
    },
    escuro: function() {
      document.documentElement.removeAttribute('data-tema');
      localStorage.setItem('temaSaberes', 'escuro');
    }
  };

  /* === NAV === */
  var Nav = {
    init: function() {
      qsa('.nav-link').forEach(function(link) {
        link.addEventListener('click', function() {
          qsa('.nav-link').forEach(function(l) { l.classList.remove('active'); });
          this.classList.add('active');
        });
      });
    }
  };

  /* === INIT === */
  document.addEventListener('DOMContentLoaded', function() {
    Tabs.init();
    Accordion.init();
    Busca.init();
    Tema.init();
    Nav.init();
  });

  window.Progresso = Progresso;
  window.marcarAprendizado = function(el, cat) { Progresso.marcar(el, cat); };
  window.comprarTemplate = function(plano, nome, valor) {
    var msg = plano >= 3
      ? 'Ola! Tenho interesse no sistema "' + nome + '" (R$ ' + valor + '). Poderia me passar mais detalhes?'
      : 'Ola! Tenho interesse no plano "' + nome + '" (R$ ' + valor + ').';
    var assunto = encodeURIComponent('Quero o ' + nome + ' - Saberes de Coracao');
    var corpo = encodeURIComponent(msg);
    if (confirm(nome + ' - R$ ' + valor + '\n\nOK = WhatsApp\nCancelar = E-mail')) {
      window.open('https://wa.me/?text=' + corpo, '_blank');
    } else {
      window.location.href = 'mailto:?subject=' + assunto + '&body=' + corpo;
    }
  };
})();
