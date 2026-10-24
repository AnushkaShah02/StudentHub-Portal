/* ========================================================
   STUDENTHUB MASTER ENGINE - script.js
   Covers Practicals 1 to 6 and Integrates Practical 13 AJAX API
   Includes Dynamic Poster Rendering & Zero-Reload Registration
   ======================================================== */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Dynamic Nav and Footer Injection (Practicals 2 & 3)
  injectGlobalHeader();
  injectGlobalFooter();

  // Initialize Lucide Icons
  if (window.lucide) {
    lucide.createIcons();
  }

  // 2. Interactive Components (Practical 4)
  initThemeSwitcher();
  initMobileMenu();
  initLiveClock();
  initNotificationBanner();
  initModal();
  initDashboardTabs();
  initAnnouncementSlider();

  // 3. Dynamic Datasets & APIs (Practicals 4, 6 & 13)
  initFAQSystem();
  initEventsEngine();
});

/* ========================================================
   1. GLOBAL COMPONENT INJECTIONS
   ======================================================== */
function injectGlobalHeader() {
  const header = document.querySelector('.global-nav');
  if (!header || header.children.length > 0) return;

  const currentPage = window.location.pathname.split('/').pop() || 'index.html';

  header.innerHTML = `
    <div class="nav-container">
      <div class="logo-group">
        <div class="logo-icon"><i data-lucide="graduation-cap"></i></div>
        <span class="logo-text">StudentHub</span>
      </div>
      <nav class="nav-links" id="main-nav-links">
        <a href="dashboard.html" class="nav-item ${currentPage === 'dashboard.html' ? 'active' : ''}"><i data-lucide="layout-dashboard" style="width: 16px;"></i> Dashboard</a>
        <a href="profile.html" class="nav-item ${currentPage === 'profile.html' ? 'active' : ''}"><i data-lucide="user" style="width: 16px;"></i> Profile</a>
        <a href="attendance.html" class="nav-item ${currentPage === 'attendance.html' ? 'active' : ''}"><i data-lucide="activity" style="width: 16px;"></i> Attendance</a>
        <a href="events.html" class="nav-item ${currentPage === 'events.html' ? 'active' : ''}"><i data-lucide="calendar" style="width: 16px;"></i> Events</a>
        <a href="about.html" class="nav-item ${currentPage === 'about.html' ? 'active' : ''}"><i data-lucide="info" style="width: 16px;"></i> About</a>
        <a href="contact.html" class="nav-item ${currentPage === 'contact.html' ? 'active' : ''}"><i data-lucide="phone" style="width: 16px;"></i> Contact</a>
        <a href="logout.php" class="nav-item" style="color: #FF5A5A;"><i data-lucide="log-out" style="width: 16px;"></i> Logout</a>
      </nav>
      <div class="header-controls">
        <button class="control-btn" id="theme-toggle" aria-label="Toggle visual theme" title="Toggle Theme">
          <i data-lucide="moon" id="theme-icon"></i>
        </button>
        <button class="control-btn hamburger-btn" id="menu-toggle" aria-expanded="false" aria-label="Open navigation menu">
          <i data-lucide="menu" id="menu-icon"></i>
        </button>
      </div>
    </div>
  `;
}

function injectGlobalFooter() {
  const footers = document.querySelectorAll('.global-footer');
  footers.forEach(footer => {
    footer.innerHTML = `
      <div style="border-top: 1px dashed var(--lavender); padding-top: 25px; margin-top: 40px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
        <p>&copy; 2026 StudentHub. Charotar University of Science and Technology (CHARUSAT).</p>
        <p style="font-size: 0.78rem; color: var(--text-muted);">Crafted by Anushka Shah (25CS097) — Computer Science & Engineering</p>
      </div>
    `;
  });
}

/* ========================================================
   2. THEME SWITCHER (Practical 4)
   ======================================================== */
function initThemeSwitcher() {
  const themeToggle = document.getElementById('theme-toggle');
  const savedTheme = localStorage.getItem('studenthub_theme');

  if (savedTheme === 'dark') {
    document.body.classList.add('dark-theme');
    updateThemeIcon(true);
  }

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const isDark = document.body.classList.toggle('dark-theme');
      localStorage.setItem('studenthub_theme', isDark ? 'dark' : 'light');
      updateThemeIcon(isDark);
    });
  }
}

function updateThemeIcon(isDark) {
  const themeIcon = document.getElementById('theme-icon');
  if (!themeIcon) return;
  themeIcon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
  if (window.lucide) lucide.createIcons();
}

/* ========================================================
   3. MOBILE MENU & LIVE CLOCK (Practicals 3 & 4)
   ======================================================== */
function initMobileMenu() {
  const menuToggle = document.getElementById('menu-toggle');
  const navLinks = document.getElementById('main-nav-links');

  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
      const isOpen = navLinks.classList.toggle('nav-links-active');
      menuToggle.setAttribute('aria-expanded', isOpen);
    });
  }
}

function initLiveClock() {
  const timeEl = document.getElementById('live-time');
  const dateEl = document.getElementById('live-date');
  if (!timeEl || !dateEl) return;

  function updateClock() {
    const now = new Date();
    timeEl.textContent = now.toLocaleTimeString('en-US', { hour12: true });
    dateEl.textContent = now.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }
  updateClock();
  setInterval(updateClock, 1000);
}

function initNotificationBanner() {
  const banner = document.getElementById('global-alert-banner');
  const closeBtn = document.getElementById('close-banner');
  if (banner && closeBtn) {
    closeBtn.addEventListener('click', () => {
      banner.style.display = 'none';
    });
  }
}

/* ========================================================
   4. MODALS & DASHBOARD TABS (Practical 4)
   ======================================================== */
function initModal() {
  const modal = document.getElementById('audit-modal-overlay');
  const triggers = document.querySelectorAll('.modal-trigger');
  const closeBtn = document.getElementById('close-modal-btn');
  const ackBtn = document.getElementById('run-diagnostic-btn');

  if (!modal) return;

  triggers.forEach(t => {
    t.addEventListener('click', (e) => {
      e.preventDefault();
      modal.classList.add('modal-visible');
    });
  });

  const closeModal = () => modal.classList.remove('modal-visible');

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (ackBtn) ackBtn.addEventListener('click', closeModal);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });
}

function initDashboardTabs() {
  const tabs = document.querySelectorAll('.dash-tab');
  const panes = document.querySelectorAll('.tab-pane');

  if (tabs.length === 0) return;

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.getAttribute('data-tab');
      tabs.forEach(t => t.classList.remove('active'));
      panes.forEach(p => p.classList.remove('active'));

      tab.classList.add('active');
      const activePane = document.getElementById(`${target}-pane`);
      if (activePane) activePane.classList.add('active');
    });
  });
}

function initAnnouncementSlider() {
  const track = document.getElementById('slider-track');
  const prevBtn = document.getElementById('slider-prev');
  const nextBtn = document.getElementById('slider-next');

  if (!track || !prevBtn || !nextBtn) return;

  let currentSlide = 0;
  const slides = track.querySelectorAll('.slide');
  const totalSlides = slides.length;

  function updateSlider() {
    track.style.transform = `translateX(-${currentSlide * 100}%)`;
  }

  nextBtn.addEventListener('click', () => {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateSlider();
  });

  prevBtn.addEventListener('click', () => {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateSlider();
  });
}

/* ========================================================
   5. ASYNC FAQ SYSTEM (Practicals 4 & 6)
   ======================================================== */
async function initFAQSystem() {
  const container = document.getElementById('dashboard-faq-container');
  if (!container) return;

  try {
    const res = await fetch('faqs.json');
    const faqs = await res.json();

    container.innerHTML = faqs.slice(0, 4).map(f => `
      <article class="bubble-question">
        <h4 class="font-poppins" style="font-size: 0.95rem; color: var(--primary); display: flex; justify-content: space-between; align-items: center;">
          ${f.question}
          <i data-lucide="chevron-down" style="width: 16px;"></i>
        </h4>
        <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 6px;">${f.answer}</p>
      </article>
    `).join('');

    if (window.lucide) lucide.createIcons();

    container.querySelectorAll('.bubble-question').forEach(card => {
      card.addEventListener('click', () => {
        card.classList.toggle('expanded');
        const p = card.querySelector('p');
        if (card.classList.contains('expanded')) {
          p.style.maxHeight = p.scrollHeight + 'px';
          p.style.opacity = '1';
        } else {
          p.style.maxHeight = '0';
          p.style.opacity = '0';
        }
      });
    });

  } catch (err) {
    container.innerHTML = `<p style="font-size: 0.8rem; color: var(--text-muted); text-align: center;">Active FAQs loading...</p>`;
  }

  const form = document.getElementById('faq-question-form');
  const input = document.getElementById('faq-question-input');
  const target = document.getElementById('faq-submitted-questions');

  if (form && input && target) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const val = input.value.trim();
      if (!val) return;

      const qBox = document.createElement('div');
      qBox.className = 'faq-user-question';
      qBox.innerHTML = `
        <div class="faq-user-question-header">
          <span class="faq-user-label">Submitted Query</span>
          <span class="faq-user-time">Just now</span>
        </div>
        <p class="faq-user-text">${val}</p>
        <div class="faq-response">Your query has been logged and assigned to academic support.</div>
      `;
      target.prepend(qBox);
      input.value = '';
    });
  }
}

/* ========================================================
   6. EVENTS ENGINE WITH DYNAMIC POSTERS & AJAX (Prac 6 & 13)
   ======================================================== */
function initEventsEngine() {
  const eventsGrid = document.getElementById('events-dynamic-grid');
  if (!eventsGrid) return;

  const searchInput = document.getElementById('search-input');
  const typeFilter = document.getElementById('type-filter');
  const sortSelect = document.getElementById('sort-select');
  const countrySelect = document.getElementById('country-select');
  const stateSelect = document.getElementById('state-select');
  const citySelect = document.getElementById('city-select');
  const loading = document.getElementById('events-loading');
  const errorBox = document.getElementById('events-error');
  const prevPageBtn = document.getElementById('prev-page');
  const nextPageBtn = document.getElementById('next-page');
  const pageIndicator = document.getElementById('page-indicator');

  let allEvents = [];
  let currentPage = 1;
  const itemsPerPage = 6;

  loadLeaderboard();

  // Expose fetchEvents globally so registration triggers real-time refresh
  window.fetchEvents = async function() {
    if (loading) loading.style.display = 'block';
    if (errorBox) errorBox.style.display = 'none';

    try {
      // Practical 13: Fetch from PHP REST API
      const res = await fetch('api.php?action=list_events');
      if (!res.ok) throw new Error('API offline, falling back');
      const json = await res.json();
      allEvents = json.data;
    } catch (e) {
      try {
        // Practical 6 Fallback
        const res2 = await fetch('events.json');
        allEvents = await res2.json();
      } catch (err) {
        if (loading) loading.style.display = 'none';
        if (errorBox) errorBox.style.display = 'block';
        return;
      }
    }

    if (loading) loading.style.display = 'none';
    populateDependentDropdowns();
    renderFilteredEvents();
  };

  fetchEvents();

  // Dependent Dropdowns (Country -> State -> City)
  function populateDependentDropdowns() {
    if (!countrySelect) return;
    const countries = [...new Set(allEvents.map(e => e.country).filter(Boolean))];
    countrySelect.innerHTML = '<option value="ALL">All Countries</option>';
    countries.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c;
      opt.textContent = c;
      countrySelect.appendChild(opt);
    });

    countrySelect.onchange = () => {
      const selCountry = countrySelect.value;
      stateSelect.innerHTML = '<option value="ALL">All States</option>';
      citySelect.innerHTML = '<option value="ALL">All Cities</option>';

      if (selCountry === 'ALL') {
        stateSelect.disabled = true;
        citySelect.disabled = true;
      } else {
        stateSelect.disabled = false;
        citySelect.disabled = true;
        const states = [...new Set(allEvents.filter(e => e.country === selCountry).map(e => e.state).filter(Boolean))];
        states.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s;
          opt.textContent = s;
          stateSelect.appendChild(opt);
        });
      }
      currentPage = 1;
      renderFilteredEvents();
    };

    stateSelect.onchange = () => {
      const selState = stateSelect.value;
      citySelect.innerHTML = '<option value="ALL">All Cities</option>';

      if (selState === 'ALL') {
        citySelect.disabled = true;
      } else {
        citySelect.disabled = false;
        const cities = [...new Set(allEvents.filter(e => e.state === selState).map(e => e.city).filter(Boolean))];
        cities.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c;
          opt.textContent = c;
          citySelect.appendChild(opt);
        });
      }
      currentPage = 1;
      renderFilteredEvents();
    };

    if (citySelect) {
      citySelect.onchange = () => {
        currentPage = 1;
        renderFilteredEvents();
      };
    }
  }

  // Filter, Sort, Paginate & Render Cards with Uploaded Posters
  function renderFilteredEvents() {
    let filtered = [...allEvents];

    const q = searchInput ? searchInput.value.toLowerCase().trim() : '';
    if (q) {
      filtered = filtered.filter(e =>
        e.title.toLowerCase().includes(q) ||
        e.description.toLowerCase().includes(q) ||
        (e.venue && e.venue.toLowerCase().includes(q))
      );
    }

    const cat = typeFilter ? typeFilter.value : 'ALL';
    if (cat !== 'ALL') {
      filtered = filtered.filter(e => e.type.toUpperCase() === cat);
    }

    if (countrySelect && countrySelect.value !== 'ALL') {
      filtered = filtered.filter(e => e.country === countrySelect.value);
    }
    if (stateSelect && !stateSelect.disabled && stateSelect.value !== 'ALL') {
      filtered = filtered.filter(e => e.state === stateSelect.value);
    }
    if (citySelect && !citySelect.disabled && citySelect.value !== 'ALL') {
      filtered = filtered.filter(e => e.city === citySelect.value);
    }

    const sort = sortSelect ? sortSelect.value : 'date-asc';
    filtered.sort((a, b) => {
      if (sort === 'date-asc') return new Date(a.date || a.event_date) - new Date(b.date || b.event_date);
      if (sort === 'date-desc') return new Date(b.date || b.event_date) - new Date(a.date || a.event_date);
      if (sort === 'registrations-desc') return (b.registrations || 0) - (a.registrations || 0);
      if (sort === 'title-asc') return a.title.localeCompare(b.title);
      return 0;
    });

    const totalPages = Math.ceil(filtered.length / itemsPerPage) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    const startIndex = (currentPage - 1) * itemsPerPage;
    const paginatedItems = filtered.slice(startIndex, startIndex + itemsPerPage);

    if (paginatedItems.length === 0) {
      eventsGrid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: var(--bg-white); border-radius: 16px;">
          <p style="color: var(--text-muted);">No events found matching your filter criteria.</p>
        </div>
      `;
    } else {
      eventsGrid.innerHTML = paginatedItems.map(ev => {
        // Practical 12 Poster Verification & Path Mapping
        const hasPoster = ev.poster_image && ev.poster_image !== 'default_event.png';
        const posterSrc = hasPoster ? `uploads/posters/${ev.poster_image}` : '';

        return `
          <article class="event-card-organic">
            <!-- Event Poster Banner -->
            <div style="width: 100%; height: 160px; border-radius: 14px; overflow: hidden; margin-bottom: 14px; background: var(--bg-light); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(109, 94, 247, 0.08);">
              ${hasPoster ? `
                <img 
                  src="${posterSrc}" 
                  alt="${ev.title}" 
                  style="width: 100%; height: 100%; object-fit: cover;"
                  onerror="this.parentElement.innerHTML='<div style=\\'color:var(--primary);display:flex;flex-direction:column;align-items:center;gap:6px;font-size:0.75rem;font-weight:600;\\'><i data-lucide=\\'image\\' style=\\'width:28px;\\'></i><span>CHARUSAT Event</span></div>'; if(window.lucide) lucide.createIcons();"
                >
              ` : `
                <div style="color: var(--primary); display: flex; flex-direction: column; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 600;">
                  <i data-lucide="image" style="width: 28px; height: 28px;"></i>
                  <span>CHARUSAT Event</span>
                </div>
              `}
            </div>

            <div class="event-badge-row">
              <span class="event-badge" style="background: var(--primary-light); color: var(--primary);">${ev.type}</span>
              <span style="font-size: 0.78rem; color: var(--text-muted);">${ev.date || ev.event_date}</span>
            </div>
            <h4>${ev.title}</h4>
            <p>${ev.description}</p>
            <div class="event-meta-info">
              <span><i data-lucide="map-pin"></i> ${ev.venue} (${ev.city || 'Campus'})</span>
              <span id="event-reg-count-${ev.id}"><i data-lucide="users"></i> ${ev.registrations || 0} Registered / ${ev.seats || '100'} Seats</span>
            </div>
            <div class="event-footer">
              <span class="event-fee">${ev.fee}</span>
              <button class="btn-primary" onclick="ajaxRegisterEvent(${ev.id})" style="font-size: 0.78rem; padding: 6px 14px;">Quick Register</button>
            </div>
          </article>
        `;
      }).join('');
    }

    if (window.lucide) lucide.createIcons();

    if (pageIndicator) pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;
    if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
    if (nextPageBtn) nextPageBtn.disabled = currentPage === totalPages;
  }

  // Hook input events
  if (searchInput) searchInput.addEventListener('input', () => { currentPage = 1; renderFilteredEvents(); });
  if (typeFilter) typeFilter.addEventListener('change', () => { currentPage = 1; renderFilteredEvents(); });
  if (sortSelect) sortSelect.addEventListener('change', () => { renderFilteredEvents(); });

  if (prevPageBtn) {
    prevPageBtn.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        renderFilteredEvents();
      }
    });
  }

  if (nextPageBtn) {
    nextPageBtn.addEventListener('click', () => {
      currentPage++;
      renderFilteredEvents();
    });
  }

  // Practical 13: Asynchronous AJAX Registration without full page reload
  window.ajaxRegisterEvent = async function (id) {
    try {
      const res = await fetch('api.php?action=register_event', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ event_id: id })
      });
      const data = await res.json();
      if (data.status === 'success') {
        alert(data.message);
        // Refresh dynamic list to update registration count and state
        if (typeof window.fetchEvents === 'function') {
          window.fetchEvents();
        }
      } else {
        alert(data.message || "Registration failed.");
      }
    } catch (e) {
      alert("Error establishing connection to events API.");
    }
  };
}

/* ========================================================
   7. DYNAMIC LEADERBOARD (Practical 6)
   ======================================================== */
async function loadLeaderboard() {
  const target = document.getElementById('dynamic-leaderboard');
  if (!target) return;

  try {
    const res = await fetch('students.json');
    const students = await res.json();
    students.sort((a, b) => b.points - a.points);

    target.innerHTML = students.slice(0, 5).map(s => `
      <li style="margin-bottom: 8px;">
        <strong>${s.name}</strong> 
        <span style="color: var(--primary); font-weight: 700; float: right;">${s.points} pts</span>
      </li>
    `).join('');
  } catch (err) {
    target.innerHTML = `<li style="color: var(--text-muted);">Leaderboard syncing...</li>`;
  }
}