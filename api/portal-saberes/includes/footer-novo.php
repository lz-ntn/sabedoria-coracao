  </main>

  <footer class="site-footer">
    <div class="container footer-grid">
      <div>
        <div class="footer-brand">
          <span class="brand-star"><i class="fa-solid fa-star"></i></span>
          <div>
            <div class="brand-text">Saberes Ancestrais</div>
            <div class="brand-sub">Portal do Conhecimento</div>
          </div>
        </div>
        <p style="font-size:.88rem;color:var(--text-2);line-height:1.7;margin-top:.5rem">Disseminar o conhecimento vero para todos que buscam, sem controle, evoluir em harmonia com a natureza e consigo mesmo.</p>
      </div>
      <div>
        <h5>Navegação</h5>
        <ul>
          <li><a href="<?= APP_URL ?>/index.php">Início</a></li>
          <li><a href="<?= APP_URL ?>/biblioteca.php">Biblioteca</a></li>
          <li><a href="<?= APP_URL ?>/busca.php">Buscar</a></li>
          <?php if (isset($paginas)): foreach ($paginas as $p): ?>
            <li><a href="<?= APP_URL ?>/pagina/<?= esc($p['slug']) ?>"><?= esc($p['titulo']) ?></a></li>
          <?php endforeach; endif; ?>
        </ul>
      </div>
      <div>
        <h5>Categorias</h5>
        <ul>
          <?php if (isset($categorias)): ?>
            <?php foreach (array_slice($categorias, 0, 6) as $cat): ?>
            <li><a href="<?= APP_URL ?>/categoria/<?= esc($cat['slug']) ?>"><?= esc($cat['nome']) ?></a></li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
      <div>
        <h5>Filosofia</h5>
        <p style="font-family:var(--font-serif);font-style:italic;font-size:.9rem;color:var(--text-2);line-height:1.6">
          &ldquo;Cada folha que cai na floresta<br>ensina algo à terra.&rdquo;
        </p>
      </div>
    </div>
    <hr>
    <p class="copy">&copy; <?= date('Y') ?> Portal Saberes Ancestrais. v<?= APP_VERSION ?></p>
  </footer>

  <button id="backToTop" aria-label="Voltar ao topo">
    <i class="fa-solid fa-chevron-up"></i>
  </button>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('backToTop');
    if (btn) {
      window.addEventListener('scroll', function() {
        btn.classList.toggle('visible', window.scrollY > 400);
      }, { passive: true });
      btn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }
    var reveals = document.querySelectorAll('.reveal');
    if (reveals.length) {
      var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
          if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
      }, { threshold: 0.15 });
      reveals.forEach(function(el) { obs.observe(el); });
    }
    var counters = document.querySelectorAll('.hero-stat .num');
    if (counters.length) {
      var cObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
          if (e.isIntersecting) {
            var el = e.target;
            var target = parseInt(el.getAttribute('data-target')) || 0;
            if (!target) return;
            var cur = 0, step = Math.ceil(target / 40);
            var timer = setInterval(function() {
              cur += step;
              if (cur >= target) { cur = target; clearInterval(timer); }
              el.textContent = cur.toLocaleString('pt-BR');
            }, 30);
            cObs.unobserve(el);
          }
        });
      }, { threshold: 0.5 });
      counters.forEach(function(el) { cObs.observe(el); });
    }
  });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= APP_URL ?>/assets/js/app-novo.js"></script>
</body>
</html>
