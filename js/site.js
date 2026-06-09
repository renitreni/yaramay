/**
 * YARAMAY site.js
 *
 * AOS (Animate On Scroll) — global config per design system:
 *   duration: 600
 *   easing: ease-out-cubic
 *   once: true
 *   offset: 80
 * Respects prefers-reduced-motion via AOS built-in detection + CSS overrides.
 */
if (typeof AOS !== 'undefined') {
  AOS.init({
    duration: 600,
    easing: 'ease-out-cubic',
    once: true,
    offset: 80,
  });
}

(function () {
  var rotateEl = document.querySelector('.hero-rotate-word');
  if (rotateEl) {
    var words = ['Digital Presence', 'IT Solutions', 'Web Design'];
    var index = 0;
    var prefersReduced =
      window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReduced) {
      setInterval(function () {
        rotateEl.classList.add('is-fading');
        setTimeout(function () {
          index = (index + 1) % words.length;
          rotateEl.textContent = words[index];
          rotateEl.classList.remove('is-fading');
        }, 300);
      }, 3500);
    }
  }
})();

(function () {
  var counters = document.querySelectorAll('[data-count-to]');
  if (!counters.length) return;

  var prefersReduced =
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function animateCounter(el) {
    if (el.dataset.counted === 'true') return;
    el.dataset.counted = 'true';

    var target = parseInt(el.getAttribute('data-count-to'), 10);
    var suffix = el.getAttribute('data-count-suffix') || '';
    var duration = 1800;
    var start = 0;
    var startTime = null;

    if (prefersReduced) {
      el.textContent = target + suffix;
      return;
    }

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      var current = Math.floor(start + (target - start) * eased);
      el.textContent = current + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        el.textContent = target + suffix;
      }
    }

    requestAnimationFrame(step);
  }

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.4 }
  );

  counters.forEach(function (counter) {
    observer.observe(counter);
  });
})();

(function () {
  var navbar = document.getElementById('siteNavbar');
  if (!navbar) return;

  var navLinks = document.querySelectorAll('[data-nav]');
  var sections = document.querySelectorAll('section[id]');
  var collapseEl = document.getElementById('navbarNav');

  function getNavOffset() {
    return navbar.offsetHeight;
  }

  function updateNavbarScroll() {
    navbar.classList.toggle('navbar-scrolled', window.scrollY > 40);
  }

  function setActiveNav(sectionId) {
    navLinks.forEach(function (link) {
      var isActive = link.getAttribute('href') === '#' + sectionId;
      link.classList.toggle('active', isActive);
    });
  }

  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (event) {
      var href = anchor.getAttribute('href');
      if (!href || href === '#') return;

      var target = document.querySelector(href);
      if (!target) return;

      event.preventDefault();

      var top =
        target.getBoundingClientRect().top + window.scrollY - getNavOffset();

      window.scrollTo({ top: top, behavior: 'smooth' });

      if (collapseEl && collapseEl.classList.contains('show')) {
        var instance = bootstrap.Collapse.getInstance(collapseEl);
        if (instance) instance.hide();
      }
    });
  });

  if (sections.length && navLinks.length) {
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            setActiveNav(entry.target.id);
          }
        });
      },
      {
        rootMargin: '-' + getNavOffset() + 'px 0px -55% 0px',
        threshold: 0,
      }
    );

    sections.forEach(function (section) {
      observer.observe(section);
    });
  }

  window.addEventListener('scroll', updateNavbarScroll, { passive: true });
  updateNavbarScroll();
})();

(function () {
  var params = new URLSearchParams(window.location.search);
  var status = params.get('contact');
  if (!status) return;

  var alertEl = document.getElementById('contact-alert');
  if (!alertEl) return;

  var reason = params.get('reason');
  var messages = {
    success: 'Thank you! Your message has been sent. We will get back to you soon.',
    error: {
      missing: 'Please fill in all fields before sending.',
      email: 'Please enter a valid email address.',
      send: 'We could not send your message. Please try again later or email us directly.',
    },
  };

  alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
  if (status === 'success') {
    alertEl.classList.add('alert-success');
    alertEl.textContent = messages.success;
    var form = document.getElementById('contact-form');
    if (form) form.reset();
  } else {
    alertEl.classList.add('alert-danger');
    alertEl.textContent =
      (messages.error && messages.error[reason]) || messages.error.send;
  }

  params.delete('contact');
  params.delete('reason');
  var query = params.toString();
  var cleanUrl =
    window.location.pathname +
    (query ? '?' + query : '') +
    window.location.hash;
  window.history.replaceState({}, '', cleanUrl);
})();

(function () {
  var positionSelect = document.getElementById('position');
  var positionOtherWrap = document.getElementById('position-other-wrap');
  if (positionSelect && positionOtherWrap) {
    function togglePositionOther() {
      var show = positionSelect.value === 'other';
      positionOtherWrap.hidden = !show;
      if (!show) {
        var otherInput = document.getElementById('position_other');
        if (otherInput) otherInput.value = '';
      }
    }
    positionSelect.addEventListener('change', togglePositionOther);
    togglePositionOther();
  }
})();

(function () {
  var params = new URLSearchParams(window.location.search);
  var status = params.get('registration');
  if (!status) return;

  var alertEl = document.getElementById('registration-alert');
  if (!alertEl) return;

  var reason = params.get('reason');
  var messages = {
    success: 'Thank you! Your registration has been submitted successfully. We will contact you soon.',
    error: {
      missing: 'Please fill in all required fields before submitting.',
      email: 'Please enter a valid email address.',
      send: 'We could not send your registration. Please try again later or contact us directly.',
    },
  };

  alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
  if (status === 'success') {
    alertEl.classList.add('alert-success');
    alertEl.textContent = messages.success;
    var form = document.getElementById('registration-form');
    if (form) form.reset();
    var positionOtherWrap = document.getElementById('position-other-wrap');
    if (positionOtherWrap) positionOtherWrap.hidden = true;
  } else {
    alertEl.classList.add('alert-danger');
    alertEl.textContent =
      (messages.error && messages.error[reason]) || messages.error.send;
  }

  params.delete('registration');
  params.delete('reason');
  var query = params.toString();
  var cleanUrl =
    window.location.pathname +
    (query ? '?' + query : '') +
    window.location.hash;
  window.history.replaceState({}, '', cleanUrl);
})();

// NEW UPDATE JUNE 8 2026
//PHILIPINE HANDLER //
// --- PHILIPPINE HANDLERS (SELYADO AT REFRESHED LOGIC) ---

// 1. LOGIC PARA SA PAG-ADD
document.getElementById('add-ph-handler').addEventListener('click', function() {
    const container = document.getElementById('ph-handler-container');
    const buttonControls = document.getElementById('ph-button-controls');
    if (!container || !buttonControls) return;

    // Binibilang ang LAHAT ng 'ph-row' sa screen ngayon (hardcoded man o dynamic)
    const currentRowsCount = container.getElementsByClassName('ph-row').length;
    
    // Automatic na magpapatuloy sa tamang kasunod na numero (hal. 3 + 1 = 4, o 1 + 1 = 2)
    const nextNumber = currentRowsCount + 1;

    // Haharangin kapag magiging 6 na ang idadagdag (Maximum of 5)
    if (nextNumber > 5) {
        alert('Maximum of 5 handlers reached.');
        return;
    }

    // Template ng bagong row - 'ph-row' ang ginamit nating class!
    const newRowHTML = `
        <div class="row g-2 align-items-start mb-3 ph-row col-12 border-bottom pb-3 mb-2">
            <div class="col-md-4">
                <label class="form-label">${nextNumber}. Handler Name</label>
                <input type="text" name="ph_handler_name[]" class="form-control contact-form__input" placeholder="Enter handler name">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Contact Person</label>
                <input type="text" name="ph_handler_person[]" class="form-control contact-form__input" placeholder="Full name">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Contact Number</label>
                <input type="tel" name="ph_handler_number[]" class="form-control contact-form__input" placeholder="Phone or mobile">
            </div>
        </div>
    `;
    
    // Isisingit ang bagong row sa itaas mismo ng mga buttons
    buttonControls.insertAdjacentHTML('beforebegin', newRowHTML);
});

// 2. LOGIC PARA SA PAG-REMOVE (Pinalakas gamit ang hiwalay na button)
const removePhBtn = document.getElementById('remove-ph-handler');
if (removePhBtn) {
    removePhBtn.addEventListener('click', function(e) {
        e.preventDefault(); // Iwasan ang hindi sinasadyang pag-submit ng form
        
        const container = document.getElementById('ph-handler-container');
        if (!container) return;

        // Kinukuha ang lahat ng rows na may class na 'ph-row'
        const allRows = container.getElementsByClassName('ph-row');
        
        // Tinitiyak na laging may matitirang isa (bawal burahin ang Number 1)
        if (allRows.length <= 3) {
            alert('Minimum of 3 handler is required.');
            return;
        }

        // Buburahin ang pinakahuling row sa screen
        const lastRow = allRows[allRows.length - 1];
        if (lastRow) {
            lastRow.remove();
        }
    });
}

// FOREIGN HANDLER //
document.getElementById('add-fr-handler').addEventListener('click', function() {
    const container = document.getElementById('fr-handler-container');
    const buttonControls = document.getElementById('fr-button-controls');
    if (!container || !buttonControls) return;

    const currentRowsCount = container.getElementsByClassName('fr-row').length;
    const nextNumber = currentRowsCount + 1;
    if (nextNumber > 5) {
        alert("Maximum of 5 foreign handlers reached.");
        return;
    }

  const newRowHTML = `
        <div class="row g-2 align-items-start mb-3 col-12 border-bottom pb-3 mb-2  fr-row">
            <div class="col-md-4">
                <label class="form-label">${nextNumber}. Agency Name</label>
                <input type="text" name="fr_handler_name[]" class="form-control contact-form__input" placeholder="Enter agency name">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Contact Person</label>
                <input type="text" name="fr_handler_person[]" class="form-control contact-form__input" placeholder="Full name">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Contact Number</label>
                <input type="tel" name="fr_handler_number[]" class="form-control contact-form__input" placeholder="Phone or mobile">
            </div>
        </div>
    `;
    
    buttonControls.insertAdjacentHTML('beforebegin', newRowHTML);
});

const removeFrBtn = document.getElementById('remove-fr-handler');
if (removeFrBtn) {
    removeFrBtn.addEventListener('click', function(e) {
        e.preventDefault(); 
        
        const container = document.getElementById('fr-handler-container');
        if (!container) return;
        const allRows = container.getElementsByClassName('fr-row');
        if (allRows.length <= 3) {
            alert('Minimum of 3 agency handler is required.');
            return;
        }
        const lastRow = allRows[allRows.length - 1];
        if (lastRow) {
            lastRow.remove();
        }
    });
}