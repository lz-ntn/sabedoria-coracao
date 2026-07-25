document.addEventListener('DOMContentLoaded', function() {
  const toggle = document.getElementById('menu-toggle');
  const nav = document.getElementById('nav-list');

  // Mobile menu toggle
  if (toggle && nav) {
    toggle.addEventListener('click', function() {
      nav.classList.toggle('open');
    });
  }

  // Active nav item
  const current = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-list a').forEach(function(a) {
    var href = a.getAttribute('href').split('#')[0] || 'index.html';
    if (href === current) a.classList.add('active');
  });

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(function(a) {
    a.addEventListener('click', function(e) {
      var t = document.querySelector(this.getAttribute('href'));
      if (t) { 
        e.preventDefault(); 
        t.scrollIntoView({ behavior: 'smooth' }); 
      }
      if (nav) nav.classList.remove('open');
    });
  });

  // Scroll animations for elements
  function handleScrollAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    const windowHeight = window.innerHeight;
    
    elements.forEach(element => {
      const elementTop = element.getBoundingClientRect().top;
      const elementVisible = 150;
      
      if (elementTop < windowHeight - elementVisible) {
        element.classList.add('visible');
      }
    });
  }

  // Run on load and scroll
  window.addEventListener('load', handleScrollAnimations);
  window.addEventListener('scroll', handleScrollAnimations);
   
  // Add animate-on-scroll class to relevant elements
  document.querySelectorAll('.lib-card, .card, .section-tag, .section-sub, .content-page h1, .content-page h2, .content-page p, .content-page .quote, .content-page ul, .contact-box, .contribute-box').forEach(el => {
    el.classList.add('animate-on-scroll');
  });
});

// Search functionality is now handled by search.js
