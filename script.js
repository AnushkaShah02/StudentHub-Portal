document.addEventListener("DOMContentLoaded", () => {

  // Dynamic animations setup
  const animationStyles = document.createElement("style");
  animationStyles.textContent = `
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .spin-animation {
      display: inline-block;
      animation: spin 1s linear infinite;
    }
    
    .bubble-question, [class*="faq-card"], [class*="faq-item"] {
      border: 1px solid rgba(109, 94, 247, 0.05) !important;
      transition: transform 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease !important;
    }
    .bubble-question:hover, [class*="faq-card"]:hover, [class*="faq-item"]:hover {
      border-color: var(--primary) !important;
      transform: translateX(4px);
    }
    .bubble-question.expanded, [class*="faq-card"].expanded, [class*="faq-item"].expanded {
      background: var(--bg-white) !important;
      border-color: var(--primary) !important;
      box-shadow: var(--shadow-hover) !important;
    }
  `;
  document.head.appendChild(animationStyles);

  /* ==========================================================
     REUSABLE NAVIGATION HEADER INJECTION
     ========================================================== */
  const globalNav = document.querySelector(".global-nav");
  if (globalNav) {
    const currentPath = window.location.pathname;
    const isCurrentPage = (pageName) => currentPath.includes(pageName);

    globalNav.innerHTML = `
      <div class="nav-container">
        <div class="logo-group">
          <div class="logo-icon"><i data-lucide="graduation-cap"></i></div>
          <span class="logo-text">StudentHub</span>
        </div>
        
        <nav class="nav-links">
          <a href="dashboard.html" class="nav-item ${isCurrentPage('dashboard.html') ? 'active' : ''}"><i data-lucide="layout-dashboard" style="width: 16px;"></i> Dashboard</a>
          <a href="profile.html" class="nav-item ${isCurrentPage('profile.html') ? 'active' : ''}"><i data-lucide="user" style="width: 16px;"></i> Profile</a>
          <a href="attendance.html" class="nav-item ${isCurrentPage('attendance.html') ? 'active' : ''}"><i data-lucide="activity" style="width: 16px;"></i> Attendance</a>
          <a href="about.html" class="nav-item ${isCurrentPage('about.html') ? 'active' : ''}"><i data-lucide="info" style="width: 16px;"></i> About</a>
          <a href="contact.html" class="nav-item ${isCurrentPage('contact.html') ? 'active' : ''}"><i data-lucide="phone" style="width: 16px;"></i> Contact</a>
          <a href="index.html" class="nav-item" style="color: #FF5A5A;"><i data-lucide="log-out" style="width: 16px;"></i> Logout</a>
        </nav>

        <div class="header-controls">
          <button class="control-btn" id="theme-toggle" aria-label="Toggle visual theme" title="Toggle Theme">
            <i data-lucide="moon" id="theme-icon"></i>
          </button>
          <button class="control-btn hamburger-btn" id="menu-toggle" aria-expanded="false" aria-label="Open navigation menu" title="Toggle Navigation">
            <i data-lucide="menu" id="menu-icon"></i>
          </button>
        </div>
      </div>
    `;
  }

  /* ==========================================================
     OFFLINE DATABASES CACHING UTILITY (Advanced Extension)
     ========================================================== */
  async function fetchWithCache(url, cacheKey) {
    try {
      const response = await fetch(url);
      if (!response.ok) throw new Error(`HTTP status response error: ${response.status}`);
      const data = await response.json();
      
      // Store dynamic data to system storage
      localStorage.setItem(cacheKey, JSON.stringify(data));
      localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());
      return data;
    } catch (error) {
      console.warn(`Dynamic dataset retrieve failed for ${url}. Fetching offline fallback registers...`);
      const offlineRegister = localStorage.getItem(cacheKey);
      if (offlineRegister) {
        return JSON.parse(offlineRegister);
      }
      throw error;
    }
  }

  /* ==========================================================
     DYNAMIC FAQS MODULE (dashboard.html)
     ========================================================== */
  const faqContainer = document.getElementById("dashboard-faq-container");
  if (faqContainer) {
    async function initDynamicFAQs() {
      try {
        const faqs = await fetchWithCache("faqs.json", "studentHub_faqs");
        faqContainer.innerHTML = "";
        
        faqs.forEach(item => {
          const faqNode = document.createElement("article");
          faqNode.className = "bubble-question";
          faqNode.innerHTML = `
            <h4>
              ${item.question}
              <i data-lucide="chevron-down" style="transition: transform 0.3s ease;"></i>
            </h4>
            <p style="max-height: 0px; overflow: hidden; transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease; opacity: 0;">
              ${item.answer}
            </p>
          `;
          faqContainer.appendChild(faqNode);
        });

        setupAccordionClickDelegator();
        if (typeof lucide !== "undefined") lucide.createIcons();
      } catch (err) {
        faqContainer.innerHTML = `
          <p style="color: #FF5A5A; font-style: italic; font-size: 0.85rem; padding: 10px;">
            Failed to process live questions array. Check local server connection logs.
          </p>
        `;
      }
    }

    function setupAccordionClickDelegator() {
      faqContainer.addEventListener("click", (e) => {
        const clickedHeader = e.target.closest("h4");
        if (!clickedHeader) return;

        const parentArticle = clickedHeader.closest(".bubble-question");
        if (!parentArticle) return;

        const answerPane = parentArticle.querySelector("p");
        const rotateIcon = clickedHeader.querySelector("i");
        const isOpen = parentArticle.classList.contains("expanded");

        // Close other panels cleanly
        const allPanels = faqContainer.querySelectorAll(".bubble-question");
        allPanels.forEach(p => {
          p.classList.remove("expanded");
          const ans = p.querySelector("p");
          const icon = p.querySelector("h4 i");
          if (ans) {
            ans.style.maxHeight = "0px";
            ans.style.opacity = "0";
            ans.style.marginTop = "0";
          }
          if (icon) icon.style.transform = "rotate(0deg)";
        });

        if (!isOpen) {
          parentArticle.classList.add("expanded");
          if (answerPane) {
            answerPane.style.maxHeight = answerPane.scrollHeight + "px";
            answerPane.style.opacity = "1";
            answerPane.style.marginTop = "10px";
          }
          if (rotateIcon) rotateIcon.style.transform = "rotate(180deg)";
        }
      });
    }

    initDynamicFAQs();
  }

  /* ==========================================================
     DYNAMIC CAMPUS EVENTS MODULE (events.html)
     ========================================================== */
  const eventsGrid = document.getElementById("events-dynamic-grid");
  if (eventsGrid) {
    let allEvents = [];
    let filteredEvents = [];
    let currentEventPage = 1;
    const eventsPerPage = 4;

    const searchInput = document.getElementById("search-input");
    const typeFilter = document.getElementById("type-filter");
    const sortSelect = document.getElementById("sort-select");
    const countrySelect = document.getElementById("country-select");
    const stateSelect = document.getElementById("state-select");
    const citySelect = document.getElementById("city-select");

    const eventsLoading = document.getElementById("events-loading");
    const eventsError = document.getElementById("events-error");

    async function loadEventsLedger() {
      eventsLoading.style.display = "block";
      eventsError.style.display = "none";
      eventsGrid.style.display = "none";

      try {
        allEvents = await fetchWithCache("events.json", "studentHub_events");
        filteredEvents = [...allEvents];

        eventsLoading.style.display = "none";
        eventsGrid.style.display = "grid";

        setupDependentDropdownData();
        executeFiltersAndSort();
      } catch (err) {
        console.error("Critical events load fault", err);
        eventsLoading.style.display = "none";
        eventsError.style.display = "block";
      }
    }

    function setupDependentDropdownData() {
      // Aggregate dynamic countries
      const countries = [...new Set(allEvents.map(e => e.country))].sort();
      countrySelect.innerHTML = `<option value="ALL">All Countries</option>`;
      countries.forEach(c => {
        countrySelect.innerHTML += `<option value="${c}">${c}</option>`;
      });

      countrySelect.addEventListener("change", () => {
        const countryVal = countrySelect.value;
        if (countryVal === "ALL") {
          stateSelect.innerHTML = `<option value="ALL">All States</option>`;
          stateSelect.disabled = true;
          citySelect.innerHTML = `<option value="ALL">All Cities</option>`;
          citySelect.disabled = true;
        } else {
          const states = [...new Set(allEvents.filter(e => e.country === countryVal).map(e => e.state))].sort();
          stateSelect.innerHTML = `<option value="ALL">All States</option>`;
          states.forEach(s => {
            stateSelect.innerHTML += `<option value="${s}">${s}</option>`;
          });
          stateSelect.disabled = false;

          citySelect.innerHTML = `<option value="ALL">All Cities</option>`;
          citySelect.disabled = true;
        }
        executeFiltersAndSort();
      });

      stateSelect.addEventListener("change", () => {
        const countryVal = countrySelect.value;
        const stateVal = stateSelect.value;

        if (stateVal === "ALL") {
          citySelect.innerHTML = `<option value="ALL">All Cities</option>`;
          citySelect.disabled = true;
        } else {
          const cities = [...new Set(allEvents.filter(e => e.country === countryVal && e.state === stateVal).map(e => e.city))].sort();
          citySelect.innerHTML = `<option value="ALL">All Cities</option>`;
          cities.forEach(c => {
            citySelect.innerHTML += `<option value="${c}">${c}</option>`;
          });
          citySelect.disabled = false;
        }
        executeFiltersAndSort();
      });

      citySelect.addEventListener("change", executeFiltersAndSort);
    }

    function executeFiltersAndSort() {
      let result = [...allEvents];

      // 1. Context Match search
      const query = searchInput.value.toLowerCase().trim();
      if (query) {
        result = result.filter(e => 
          e.title.toLowerCase().includes(query) ||
          e.description.toLowerCase().includes(query) ||
          e.venue.toLowerCase().includes(query)
        );
      }

      // 2. Event category match
      const typeVal = typeFilter.value;
      if (typeVal !== "ALL") {
        result = result.filter(e => e.type === typeVal);
      }

      // 3. Country matches
      const countryVal = countrySelect.value;
      if (countryVal !== "ALL") {
        result = result.filter(e => e.country === countryVal);
      }

      // 4. State matches
      const stateVal = stateSelect.value;
      if (stateVal && stateVal !== "ALL") {
        result = result.filter(e => e.state === stateVal);
      }

      // 5. City matches
      const cityVal = citySelect.value;
      if (cityVal && cityVal !== "ALL") {
        result = result.filter(e => e.city === cityVal);
      }

      // 6. Sorting
      const sortVal = sortSelect.value;
      if (sortVal === "date-asc") {
        result.sort((a, b) => new Date(a.date) - new Date(b.date));
      } else if (sortVal === "date-desc") {
        result.sort((a, b) => new Date(b.date) - new Date(a.date));
      } else if (sortVal === "registrations-desc") {
        result.sort((a, b) => b.registrations - a.registrations);
      } else if (sortVal === "title-asc") {
        result.sort((a, b) => a.title.localeCompare(b.title));
      }

      filteredEvents = result;
      currentEventPage = 1;
      renderEventsPage();
    }

    function renderEventsPage() {
      const totalEvents = filteredEvents.length;
      const totalPages = Math.ceil(totalEvents / eventsPerPage) || 1;

      if (currentEventPage > totalPages) currentEventPage = totalPages;
      if (currentEventPage < 1) currentEventPage = 1;

      const startIndex = (currentEventPage - 1) * eventsPerPage;
      const paginatedSlice = filteredEvents.slice(startIndex, startIndex + eventsPerPage);

      eventsGrid.innerHTML = "";

      if (paginatedSlice.length === 0) {
        eventsGrid.innerHTML = `
          <p style="text-align: center; grid-column: 1 / -1; color: var(--text-muted); font-style: italic; padding: 40px;">
            No event entries match your filtering parameters.
          </p>
        `;
        document.getElementById("page-indicator").textContent = `Page 0 of 0`;
        return;
      }

      paginatedSlice.forEach(evt => {
        const article = document.createElement("article");
        article.className = "event-card-organic";

        let badgeStyles = "background: var(--primary-light); color: var(--primary);";
        if (evt.type === "WORKSHOP") badgeStyles = "background: #EAF7EA; color: #4CAF50;";
        else if (evt.type === "SEMINAR") badgeStyles = "background: #FFF3E0; color: #FF9800;";

        article.innerHTML = `
          <div style="display: flex; flex-direction: column; height: 100%;">
            <div class="event-badge-row">
              <span class="event-badge" style="${badgeStyles}">${evt.type}</span>
              <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">${evt.date}</span>
            </div>
            
            <h4 class="font-poppins">${evt.title}</h4>
            <p>${evt.description}</p>
            
            <div class="event-meta-info">
              <span><i data-lucide="map-pin"></i> ${evt.venue} (${evt.city}, ${evt.state})</span>
              <span><i data-lucide="users"></i> ${evt.registrations} Registered / ${evt.seats} Seats</span>
            </div>
            
            <div class="event-footer">
              <span class="event-fee">${evt.fee}</span>
              <button class="btn-primary" style="padding: 8px 16px; font-size: 0.8rem; border-radius: var(--radius-capsule);">
                Register <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i>
              </button>
            </div>
          </div>
        `;
        eventsGrid.appendChild(article);
      });

      document.getElementById("page-indicator").textContent = `Page ${currentEventPage} of ${totalPages}`;
      document.getElementById("prev-page").disabled = currentEventPage === 1;
      document.getElementById("next-page").disabled = currentEventPage === totalPages;

      if (typeof lucide !== "undefined") lucide.createIcons();
    }

    // Connect controller hooks
    searchInput.addEventListener("input", executeFiltersAndSort);
    typeFilter.addEventListener("change", executeFiltersAndSort);
    sortSelect.addEventListener("change", executeFiltersAndSort);

    document.getElementById("prev-page").addEventListener("click", () => {
      if (currentEventPage > 1) {
        currentEventPage--;
        renderEventsPage();
      }
    });

    document.getElementById("next-page").addEventListener("click", () => {
      const maxPages = Math.ceil(filteredEvents.length / eventsPerPage) || 1;
      if (currentEventPage < maxPages) {
        currentEventPage++;
        renderEventsPage();
      }
    });

    const retryBtn = document.getElementById("retry-events-btn");
    if (retryBtn) retryBtn.addEventListener("click", loadEventsLedger);

    loadEventsLedger();
  }

  /* ==========================================================
     DYNAMIC STUDENT LEADERBOARD (events.html sidebar)
     ========================================================== */
  const boardList = document.getElementById("dynamic-leaderboard");
  if (boardList) {
    async function initLeaderboard() {
      try {
        const studentList = await fetchWithCache("students.json", "studentHub_students");
        studentList.sort((a, b) => b.points - a.points);
        const leadingTier = studentList.slice(0, 3);

        boardList.innerHTML = "";
        leadingTier.forEach((st, i) => {
          const listNode = document.createElement("li");
          listNode.innerHTML = `<strong>${st.name}</strong> (${st.points} pts) - ${st.department}`;
          boardList.appendChild(listNode);
        });
      } catch (err) {
        boardList.innerHTML = `
          <li>Anushka Shah (980 pts) [Cached]</li>
          <li>Shrusti Chavada (920 pts) [Cached]</li>
          <li>Bhavyaa Patel (850 pts) [Cached]</li>
        `;
      }
    }
    initLeaderboard();
  }

  /* ==========================================================
     THEME CONTROLLER & SYSTEM CLOCK CONFIGURATION
     ========================================================== */
  const themeToggle = document.getElementById("theme-toggle");
  const themeIcon = document.getElementById("theme-icon");

  const applyTheme = (theme) => {
    if (theme === "dark") {
      document.body.classList.add("dark-theme");
      if (themeIcon) {
        themeIcon.setAttribute("data-lucide", "sun");
        themeIcon.style.transform = "rotate(180deg)";
      }
    } else {
      document.body.classList.remove("dark-theme");
      if (themeIcon) {
        themeIcon.setAttribute("data-lucide", "moon");
        themeIcon.style.transform = "rotate(0deg)";
      }
    }
    if (typeof lucide !== "undefined") lucide.createIcons();
  };

  const savedTheme = localStorage.getItem("theme") || "light";
  applyTheme(savedTheme);

  if (themeIcon) themeIcon.style.transition = "transform 0.4s cubic-bezier(0.16, 1, 0.3, 1)";
  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      const activeState = document.body.classList.contains("dark-theme") ? "light" : "dark";
      localStorage.setItem("theme", activeState);
      applyTheme(activeState);
    });
  }

  // Hamburger Drawer Controls
  const menuToggle = document.getElementById("menu-toggle");
  const navLinks = document.querySelector(".nav-links");
  const menuIcon = document.getElementById("menu-icon");

  if (menuToggle && navLinks) {
    menuToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      const isOpen = navLinks.classList.toggle("nav-links-active");
      menuToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");

      if (menuIcon) {
        menuIcon.setAttribute("data-lucide", isOpen ? "x" : "menu");
        if (typeof lucide !== "undefined") lucide.createIcons();
      }
    });

    document.addEventListener("click", (e) => {
      if (navLinks.classList.contains("nav-links-active") && !navLinks.contains(e.target) && e.target !== menuToggle) {
        navLinks.classList.remove("nav-links-active");
        menuToggle.setAttribute("aria-expanded", "false");
        if (menuIcon) {
          menuIcon.setAttribute("data-lucide", "menu");
          if (typeof lucide !== "undefined") lucide.createIcons();
        }
      }
    });
  }

  // Live clock display
  const liveTimeEl = document.getElementById("live-time");
  const liveDateEl = document.getElementById("live-date");

  if (liveTimeEl || liveDateEl) {
    const runClock = () => {
      const d = new Date();
      if (liveTimeEl) liveTimeEl.textContent = d.toLocaleTimeString([], { hour12: false });
      if (liveDateEl) liveDateEl.textContent = d.toLocaleDateString("en-US", { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    };
    runClock();
    setInterval(runClock, 1000);
  }

  // Global Footers Dynamic text
  document.querySelectorAll(".global-footer").forEach(ft => {
    ft.innerHTML = `<p>&copy; 2026 StudentHub. All academic registries are dynamic. Affiliated directly to CHARUSAT CSE.</p>`;
  });

  // Modal notice trigger interactions
  const overlay = document.getElementById("audit-modal-overlay");
  const modalTriggers = document.querySelectorAll(".modal-trigger");
  const closeModalBtn = document.getElementById("close-modal-btn");
  const runDiagnosticBtn = document.getElementById("run-diagnostic-btn");

  if (overlay) {
    const triggerModalView = () => overlay.classList.add("modal-visible");
    const dismissModalView = () => overlay.classList.remove("modal-visible");

    modalTriggers.forEach(btn => btn.addEventListener("click", triggerModalView));
    if (closeModalBtn) closeModalBtn.addEventListener("click", dismissModalView);
    overlay.addEventListener("click", (e) => { if (e.target === overlay) dismissModalView(); });
  }

  if (runDiagnosticBtn && overlay) {
    runDiagnosticBtn.addEventListener("click", () => {
      overlay.classList.remove("modal-visible");
    });
  }

  // Dashboard Sub-navigation Tab Toggling
  const dashTabs = document.querySelectorAll(".dash-tab");
  const tabPanes = document.querySelectorAll(".tab-pane");

  if (dashTabs.length > 0 && tabPanes.length > 0) {
    dashTabs.forEach(tab => {
      tab.addEventListener("click", () => {
        const targetPaneId = `${tab.getAttribute("data-tab")}-pane`;

        dashTabs.forEach(t => t.classList.remove("active"));
        tab.classList.add("active");

        tabPanes.forEach(pane => {
          pane.classList.remove("active");
          if (pane.id === targetPaneId) {
            pane.classList.add("active");
          }
        });
      });
    });
  }

  /* ==========================================================
     GLOBAL BANNER NOTICE COMPONENT
     ========================================================== */
  const isAuthPage = window.location.pathname.includes("index.html") || window.location.pathname.includes("register.html");
  if (!isAuthPage) {
    const banner = document.getElementById("global-alert-banner");
    const closeBannerBtn = document.getElementById("close-banner");

    const isBannerDismissed = localStorage.getItem("studentHubBannerDismissed");
    if (isBannerDismissed === "true" && banner) {
      banner.remove();
    } else if (banner && closeBannerBtn) {
      closeBannerBtn.addEventListener("click", () => {
        banner.style.transition = "transform 0.4s ease, opacity 0.4s ease, margin-top 0.4s ease";
        banner.style.transform = "translateY(-100%)";
        banner.style.opacity = "0";
        localStorage.setItem("studentHubBannerDismissed", "true");
        setTimeout(() => banner.remove(), 400);
      });
    }
  }

  /* ==========================================================
     ASK FAQ QUESTION SUBMISSION FORM HANDLER
     ========================================================== */
  const faqForm = document.getElementById('faq-question-form');
  const faqInput = document.getElementById('faq-question-input');
  const submittedQuestions = document.getElementById('faq-submitted-questions');

  const renderSubmittedQuestions = () => {
    if (!submittedQuestions) return;
    submittedQuestions.innerHTML = '';
    const stored = JSON.parse(localStorage.getItem('studentHubQuestionsArray') || '[]');
    stored.forEach(q => {
      const questionItem = document.createElement('article');
      questionItem.className = 'faq-user-question';
      questionItem.innerHTML = `
          <div class="faq-user-question-header">
              <span class="faq-user-label">Your Question</span>
              <span class="faq-user-time">${q.time}</span>
          </div>
          <p class="faq-user-text"></p>
          <div class="faq-response">
              <strong>Support Desk:</strong> Your question is logged on cache. A response will appear upon sync.
          </div>
      `;
      questionItem.querySelector('.faq-user-text').textContent = q.text;
      submittedQuestions.appendChild(questionItem);
    });
  };

  renderSubmittedQuestions();

  if (faqForm && faqInput && submittedQuestions) {
    faqForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const questionText = faqInput.value.trim();
      if (!questionText) return;

      const stored = JSON.parse(localStorage.getItem('studentHubQuestionsArray') || '[]');
      stored.unshift({
        text: questionText,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      });
      localStorage.setItem('studentHubQuestionsArray', JSON.stringify(stored));

      renderSubmittedQuestions();
      faqInput.value = '';
    });
  }

  // Dashboard Academic Notice Announcement Slider
  const sliderTrack = document.getElementById("slider-track");
  const prevBtn = document.getElementById("slider-prev");
  const nextBtn = document.getElementById("slider-next");

  if (sliderTrack) {
    const slidesCount = sliderTrack.querySelectorAll(".slide").length;
    let activeSlideIndex = 0;

    const renderSliderPosition = () => {
      sliderTrack.style.transform = `translateX(-${activeSlideIndex * 100}%)`;
    };

    if (nextBtn) {
      nextBtn.addEventListener("click", (e) => {
        e.preventDefault();
        activeSlideIndex = (activeSlideIndex + 1) % slidesCount;
        renderSliderPosition();
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener("click", (e) => {
        e.preventDefault();
        activeSlideIndex = (activeSlideIndex - 1 + slidesCount) % slidesCount;
        renderSliderPosition();
      });
    }
  }

  // Run Lucide initialization
  if (typeof lucide !== "undefined") {
    lucide.createIcons();
  }
});
// Practical 13: AJAX live integration with api.php
async function loadEventsFromAPI(category = 'ALL') {
  const container = document.getElementById('events-dynamic-grid');
  const loader = document.getElementById('events-loading');
  if (!container) return;

  if (loader) loader.style.display = 'block';

  try {
    const res = await fetch(`api.php?action=list_events&type=${category}`);
    const json = await res.json();
    if (loader) loader.style.display = 'none';

    if (json.status === 'success' && json.data.length > 0) {
      container.innerHTML = json.data.map(ev => `
        <article class="event-card-organic">
          <div class="event-badge-row">
            <span class="event-badge" style="background: var(--primary-light); color: var(--primary);">${ev.type}</span>
            <span style="font-size: 0.8rem; color: var(--text-muted);">${ev.event_date}</span>
          </div>
          <h4>${ev.title}</h4>
          <p>${ev.description}</p>
          <div class="event-meta-info">
            <span><i data-lucide="map-pin"></i> ${ev.venue} (${ev.city})</span>
            <span><i data-lucide="users"></i> ${ev.registrations} Registered</span>
          </div>
          <div class="event-footer">
            <span class="event-fee">${ev.fee}</span>
            <button class="btn-primary" onclick="ajaxRegisterEvent(${ev.id})" style="font-size: 0.8rem; padding: 6px 14px;">Quick Register</button>
          </div>
        </article>
      `).join('');
      if (window.lucide) lucide.createIcons();
    } else {
      container.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: var(--text-muted);">No matching events active in the database.</p>`;
    }
  } catch (err) {
    if (loader) loader.style.display = 'none';
    console.error("AJAX Error:", err);
  }
}

// Asynchronous Event Registration
window.ajaxRegisterEvent = async function(id) {
  try {
    const res = await fetch('api.php?action=register_event', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ event_id: id })
    });
    const result = await res.json();
    if (result.status === 'success') {
      alert("Registration Successful!");
      loadEventsFromAPI();
    } else {
      alert(result.message);
    }
  } catch(e) {
    alert("Connection error occurred.");
  }
};