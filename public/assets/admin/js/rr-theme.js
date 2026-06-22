/* Rei do Rodeio Admin Theme JS - pequenos aprimoramentos de UX */
(function(){
  'use strict';

  // Ripple leve para botões primários
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.btn, .page-link');
    if(!btn) return;
    btn.classList.add('rr-pressed');
    setTimeout(()=>btn.classList.remove('rr-pressed'), 180);
  });

  // Tooltip auto-enable se Bootstrap estiver disponível
  if(window.bootstrap && document.querySelector('[data-bs-toggle="tooltip"]')){
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // Focus style em inputs para acessibilidade
  document.addEventListener('focusin', (e)=>{
    const el = e.target;
    if(el.classList && (el.classList.contains('form-control') || el.classList.contains('form-select'))){
      el.style.boxShadow = '0 0 0 0.2rem rgba(255,107,53,0.25)';
      el.style.borderColor = '#FF6B35';
    }
  });
  document.addEventListener('focusout', (e)=>{
    const el = e.target;
    if(el.classList && (el.classList.contains('form-control') || el.classList.contains('form-select'))){
      el.style.boxShadow = '';
      el.style.borderColor = '';
    }
  });

  // Sidebar responsive toggle
  document.addEventListener('click', (e)=>{
    const open = e.target.closest('.res-sidebar-open-btn');
    const close = e.target.closest('.res-sidebar-close-btn');
    const sidebar = document.querySelector('.sidebar');
    if(!sidebar) return;
    if(open){ sidebar.classList.add('active'); }
    if(close){ sidebar.classList.remove('active'); }
  });

  // Marcar menu ativo baseado em URL
  try {
    const current = window.location.pathname;
    document.querySelectorAll('.sidebar__menu a.nav-link').forEach(a=>{
      if(a.getAttribute('href') && a.getAttribute('href') !== 'javascript:void(0)'){
        const url = new URL(a.href);
        if(url.pathname === current){
          a.classList.add('active');
          const li = a.closest('.sidebar-menu-item');
          if(li){ li.classList.add('active'); }
        }
      }
    });
  } catch(err) {}
})();
