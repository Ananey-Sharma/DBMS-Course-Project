/**
 * EventSphere DBMS PBL Frontend Engine
 */

const API_BASE = './api';

// Global App State
const state = {
  currentTab: 'dashboard',
  events: [],
  registrations: [],
  participants: [],
  venues: [],
  organizers: [],
  categories: [],
  schedules: [],
  presets: [],
  charts: {
    categoryChart: null,
    registrationChart: null
  }
};

// Document Ready
document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initModals();
  loadAllData();
});

// Toast System
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  
  let icon = 'bi-info-circle';
  if (type === 'success') icon = 'bi-check-circle-fill text-emerald-400';
  if (type === 'error') icon = 'bi-exclamation-triangle-fill text-rose-400';

  toast.innerHTML = `
    <i class="bi ${icon}" style="font-size: 1.2rem;"></i>
    <div style="flex:1;">${escapeHtml(message)}</div>
    <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#94a3b8;cursor:pointer;">
      <i class="bi bi-x-lg"></i>
    </button>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// Navigation Tab Switcher
function initNavigation() {
  const navItems = document.querySelectorAll('.nav-item[data-tab]');
  navItems.forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      const tabName = item.getAttribute('data-tab');
      switchTab(tabName);
    });
  });
}

function switchTab(tabName) {
  state.currentTab = tabName;

  // Update nav links
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  const activeLink = document.querySelector(`.nav-item[data-tab="${tabName}"]`);
  if (activeLink) activeLink.classList.add('active');

  // Update tab panes
  document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
  const targetPane = document.getElementById(`tab-${tabName}`);
  if (targetPane) targetPane.classList.add('active');

  // Update topbar title
  const titles = {
    'dashboard': 'Analytics & Executive Dashboard',
    'events': 'Events Directory & Management',
    'registrations': 'Participant Registrations Hub',
    'participants': 'Registered Students & Attendees',
    'venues-schedules': 'Venues & Time Schedules',
    'organizers-categories': 'Clubs, Organizers & Categories',
    'student-portal': 'Public Student Registration Portal',
    'dbms-pbl': 'DBMS PBL Lab: ER Diagram & SQL Query Playground'
  };
  const titleEl = document.getElementById('currentViewTitle');
  if (titleEl) titleEl.textContent = titles[tabName] || 'Dashboard';

  // Refresh tab data
  if (tabName === 'dashboard') loadDashboard();
  if (tabName === 'events') loadEvents();
  if (tabName === 'registrations') loadRegistrations();
  if (tabName === 'participants') loadParticipants();
  if (tabName === 'venues-schedules') { loadVenues(); loadSchedules(); }
  if (tabName === 'organizers-categories') { loadOrganizers(); loadCategories(); }
  if (tabName === 'student-portal') loadStudentPortal();
  if (tabName === 'dbms-pbl') loadPblPresets();
}

// Global Data Loader
async function loadAllData() {
  await Promise.all([
    loadCategories(false),
    loadOrganizers(false),
    loadVenues(false),
    loadDashboard()
  ]);
}

// ----------------------------------------------------
// 1. DASHBOARD
// ----------------------------------------------------
async function loadDashboard() {
  try {
    const res = await fetch(`${API_BASE}/dashboard.php`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    const { metrics, categories_chart, registration_status_chart, payment_status_chart, recent_registrations, upcoming_schedules } = result.data;

    // Render Metrics
    document.getElementById('metricTotalEvents').textContent = metrics.total_events;
    document.getElementById('metricTotalRegistrations').textContent = metrics.total_registrations;
    document.getElementById('metricTotalParticipants').textContent = metrics.total_participants;
    document.getElementById('metricTotalVenues').textContent = metrics.total_venues;
    document.getElementById('metricTotalRevenue').textContent = '₹' + Number(metrics.total_revenue).toLocaleString();

    // Render Charts
    renderCategoryChart(categories_chart);
    renderRegistrationChart(registration_status_chart, payment_status_chart);

    // Render Recent Registrations
    const regTbody = document.getElementById('dashboardRecentRegistrations');
    if (regTbody) {
      if (!recent_registrations || recent_registrations.length === 0) {
        regTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted" style="padding: 20px;">No registrations recorded yet.</td></tr>`;
      } else {
        regTbody.innerHTML = recent_registrations.map(r => `
          <tr>
            <td>
              <div style="font-weight:600; color:#fff;">${escapeHtml(r.participant_name)}</div>
              <div style="font-size:12px; color:var(--text-dim);">${escapeHtml(r.participant_email)}</div>
            </td>
            <td>
              <div style="font-weight:500;">${escapeHtml(r.event_name)}</div>
              <div style="font-size:11.5px; color:var(--text-dim);">${escapeHtml(r.college)}</div>
            </td>
            <td>${formatDate(r.registration_date)}</td>
            <td>${getStatusBadge(r.registration_status)}</td>
            <td>${getPaymentBadge(r.payment_status)}</td>
          </tr>
        `).join('');
      }
    }

    // Render Upcoming Schedules
    const schedContainer = document.getElementById('dashboardUpcomingSchedules');
    if (schedContainer) {
      if (!upcoming_schedules || upcoming_schedules.length === 0) {
        schedContainer.innerHTML = `<div class="text-muted text-center" style="padding:20px;">No upcoming events scheduled.</div>`;
      } else {
        schedContainer.innerHTML = upcoming_schedules.map(s => `
          <div style="padding:14px; background:rgba(255,255,255,0.03); border:1px solid var(--border-color); border-radius:var(--radius-md); margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
              <span class="badge badge-purple">${escapeHtml(s.category_name)}</span>
              <span style="font-size:12px; color:var(--accent-cyan); font-weight:600;">
                <i class="bi bi-calendar-event me-1"></i>${formatDate(s.event_date)}
              </span>
            </div>
            <div style="font-weight:700; color:#fff; font-size:14.5px; margin-bottom:4px;">${escapeHtml(s.event_name)}</div>
            <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--text-muted);">
              <span><i class="bi bi-geo-alt me-1 text-emerald-400"></i>${escapeHtml(s.venue_name)}</span>
              <span><i class="bi bi-clock me-1 text-amber-400"></i>${formatTime(s.start_time)} - ${formatTime(s.end_time)}</span>
            </div>
          </div>
        `).join('');
      }
    }

  } catch (err) {
    console.error('Error loading dashboard:', err);
    showToast('Failed to load dashboard data: ' + err.message, 'error');
  }
}

function renderCategoryChart(data) {
  const ctx = document.getElementById('categoryDistributionChart');
  if (!ctx) return;

  const labels = data.map(d => d.category_name);
  const counts = data.map(d => d.event_count);

  if (state.charts.categoryChart) {
    state.charts.categoryChart.destroy();
  }

  state.charts.categoryChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: counts,
        backgroundColor: [
          '#6366f1',
          '#06b6d4',
          '#10b981',
          '#f59e0b',
          '#a855f7',
          '#ec4899'
        ],
        borderWidth: 2,
        borderColor: '#111827'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { color: '#94a3b8', font: { family: 'Inter', size: 12 }, boxWidth: 14 }
        }
      },
      cutout: '70%'
    }
  });
}

function renderRegistrationChart(regStatusData, paymentData) {
  const ctx = document.getElementById('registrationStatusChart');
  if (!ctx) return;

  const labels = ['Registrations', 'Payments'];
  
  const confirmed = (regStatusData.find(d => d.registration_status === 'Confirmed') || {}).count || 0;
  const pending = (regStatusData.find(d => d.registration_status === 'Pending') || {}).count || 0;
  const paid = (paymentData.find(d => d.payment_status === 'Paid') || {}).count || 0;
  const notReq = (paymentData.find(d => d.payment_status === 'Not Required') || {}).count || 0;

  if (state.charts.registrationChart) {
    state.charts.registrationChart.destroy();
  }

  state.charts.registrationChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Confirmed', 'Pending Reg', 'Paid Fees', 'Free / No Fee'],
      datasets: [{
        label: 'Count',
        data: [confirmed, pending, paid, notReq],
        backgroundColor: [
          'rgba(16, 185, 129, 0.75)',
          'rgba(245, 158, 11, 0.75)',
          'rgba(99, 102, 241, 0.75)',
          'rgba(6, 182, 212, 0.75)'
        ],
        borderColor: [
          '#10b981',
          '#f59e0b',
          '#6366f1',
          '#06b6d4'
        ],
        borderWidth: 1,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(255,255,255,0.05)' },
          ticks: { color: '#94a3b8', stepSize: 1 }
        },
        x: {
          grid: { display: false },
          ticks: { color: '#94a3b8' }
        }
      },
      plugins: {
        legend: { display: false }
      }
    }
  });
}

// ----------------------------------------------------
// 2. EVENTS
// ----------------------------------------------------
let eventViewMode = 'grid'; // 'grid' or 'table'

function toggleEventView(mode) {
  eventViewMode = mode;
  document.getElementById('btnEventsGrid').className = mode === 'grid' ? 'btn btn-secondary btn-sm active' : 'btn btn-secondary btn-sm';
  document.getElementById('btnEventsTable').className = mode === 'table' ? 'btn btn-secondary btn-sm active' : 'btn btn-secondary btn-sm';
  renderEvents();
}

async function loadEvents() {
  try {
    const search = document.getElementById('eventSearchInput')?.value || '';
    const categoryId = document.getElementById('eventCategoryFilter')?.value || '';
    const status = document.getElementById('eventStatusFilter')?.value || '';

    let url = `${API_BASE}/events.php?`;
    if (search) url += `search=${encodeURIComponent(search)}&`;
    if (categoryId) url += `category_id=${categoryId}&`;
    if (status) url += `status=${encodeURIComponent(status)}&`;

    const res = await fetch(url);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    state.events = result.data;
    renderEvents();
  } catch (err) {
    showToast('Failed to load events: ' + err.message, 'error');
  }
}

function renderEvents() {
  const container = document.getElementById('eventsContentArea');
  if (!container) return;

  if (state.events.length === 0) {
    container.innerHTML = `
      <div style="text-align:center; padding:50px 20px; color:var(--text-muted); background:var(--bg-card); border-radius:var(--radius-lg); border:1px solid var(--border-color);">
        <i class="bi bi-calendar-x" style="font-size:40px; color:var(--text-dim); display:block; margin-bottom:12px;"></i>
        <h3>No events match your criteria</h3>
        <p style="font-size:13px; margin-top:6px;">Try adjusting filters or create a new event using the button above.</p>
      </div>
    `;
    return;
  }

  if (eventViewMode === 'grid') {
    container.innerHTML = `
      <div class="events-cards-grid">
        ${state.events.map(ev => {
          const confirmed = Number(ev.confirmed_count) || 0;
          const max = Number(ev.max_participants) || 1;
          const percent = Math.min(100, Math.round((confirmed / max) * 100));
          const fee = Number(ev.registration_fee);

          return `
            <div class="event-card">
              <div class="event-card-banner">
                <span class="badge badge-purple">${escapeHtml(ev.category_name)}</span>
                <span class="badge ${fee === 0 ? 'badge-emerald' : 'badge-blue'}">
                  ${fee === 0 ? 'FREE' : '₹' + fee}
                </span>
              </div>
              <div class="event-card-body">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                  <h3 class="event-title">${escapeHtml(ev.event_name)}</h3>
                  ${getStatusBadge(ev.status)}
                </div>
                <p class="event-desc">${escapeHtml(ev.description || 'No description provided.')}</p>

                <div class="event-meta-list">
                  <div class="event-meta-item">
                    <i class="bi bi-people"></i>
                    <span>Organized by: <strong style="color:#fff;">${escapeHtml(ev.organizer_name)}</strong></span>
                  </div>
                  <div class="event-meta-item">
                    <i class="bi bi-geo-alt"></i>
                    <span>Venue: <strong style="color:#fff;">${escapeHtml(ev.venue_name || 'TBA')}</strong></span>
                  </div>
                  <div class="event-meta-item">
                    <i class="bi bi-calendar-event"></i>
                    <span>Schedule: ${ev.event_date ? formatDate(ev.event_date) + ' (' + formatTime(ev.start_time) + ')' : 'Schedule Pending'}</span>
                  </div>
                </div>

                <div class="capacity-progress">
                  <div class="capacity-label">
                    <span>Enrolled: <strong>${confirmed} / ${max}</strong></span>
                    <span>${percent}% Full</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-fill" style="width: ${percent}%;"></div>
                  </div>
                </div>

                <div class="event-card-actions">
                  <button class="btn btn-secondary btn-sm" style="flex:1;" onclick="viewEventDetails(${ev.event_id})">
                    <i class="bi bi-eye"></i> Details
                  </button>
                  <button class="btn btn-secondary btn-sm btn-icon" onclick="openEditEventModal(${ev.event_id})" title="Edit Event">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-danger btn-sm btn-icon" onclick="deleteEvent(${ev.event_id})" title="Delete Event">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          `;
        }).join('')}
      </div>
    `;
  } else {
    // Table View
    container.innerHTML = `
      <div class="table-responsive">
        <table class="custom-table">
          <thead>
            <tr>
              <th>Event Title</th>
              <th>Category</th>
              <th>Organizer</th>
              <th>Venue & Date</th>
              <th>Fee</th>
              <th>Capacity</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            ${state.events.map(ev => `
              <tr>
                <td>
                  <div style="font-weight:700; color:#fff;">${escapeHtml(ev.event_name)}</div>
                  <div style="font-size:11.5px; color:var(--text-dim); max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(ev.description || '')}</div>
                </td>
                <td><span class="badge badge-purple">${escapeHtml(ev.category_name)}</span></td>
                <td>
                  <div style="font-weight:500;">${escapeHtml(ev.organizer_name)}</div>
                  <div style="font-size:11px; color:var(--text-dim);">${escapeHtml(ev.organizer_department || '')}</div>
                </td>
                <td>
                  <div>${escapeHtml(ev.venue_name || 'Not Assigned')}</div>
                  <div style="font-size:11.5px; color:var(--text-dim);">${ev.event_date ? formatDate(ev.event_date) : 'No date'}</div>
                </td>
                <td><strong>${Number(ev.registration_fee) === 0 ? '<span class="badge badge-emerald">Free</span>' : '₹' + ev.registration_fee}</strong></td>
                <td>${ev.confirmed_count} / ${ev.max_participants}</td>
                <td>${getStatusBadge(ev.status)}</td>
                <td style="text-align:right;">
                  <button class="btn btn-secondary btn-sm btn-icon" onclick="viewEventDetails(${ev.event_id})" title="View Details">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-secondary btn-sm btn-icon" onclick="openEditEventModal(${ev.event_id})" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-danger btn-sm btn-icon" onclick="deleteEvent(${ev.event_id})" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
  }
}

// Event Details Modal
async function viewEventDetails(eventId) {
  try {
    const res = await fetch(`${API_BASE}/events.php?id=${eventId}`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    const ev = result.data;
    const modalBody = document.getElementById('eventDetailsBody');
    const modalTitle = document.getElementById('eventDetailsTitle');

    modalTitle.innerHTML = `<i class="bi bi-ticket-perforated text-indigo-400"></i> ${escapeHtml(ev.event_name)}`;

    const fee = Number(ev.registration_fee);
    const confirmed = Number(ev.confirmed_count) || 0;
    const max = Number(ev.max_participants);
    const percent = Math.min(100, Math.round((confirmed / max) * 100));

    modalBody.innerHTML = `
      <div style="background:rgba(255,255,255,0.03); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
          <div>
            <span class="badge badge-purple me-2">${escapeHtml(ev.category_name)}</span>
            ${getStatusBadge(ev.status)}
          </div>
          <span class="badge ${fee === 0 ? 'badge-emerald' : 'badge-blue'}" style="font-size:13px;">
            ${fee === 0 ? 'Free Entry' : 'Fee: ₹' + fee}
          </span>
        </div>
        <p style="font-size:13.5px; color:var(--text-muted); line-height:1.6; margin-bottom:14px;">${escapeHtml(ev.description || 'No detailed description available.')}</p>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; font-size:13px;">
          <div>
            <div style="color:var(--text-dim); font-size:11px; text-transform:uppercase;">Organizer</div>
            <div style="font-weight:600; color:#fff;">${escapeHtml(ev.organizer_name)}</div>
            <div style="color:var(--text-dim);">${escapeHtml(ev.organizer_email)} | ${escapeHtml(ev.organizer_phone || '')}</div>
          </div>
          <div>
            <div style="color:var(--text-dim); font-size:11px; text-transform:uppercase;">Venue & Timings</div>
            <div style="font-weight:600; color:#fff;">${escapeHtml(ev.venue_name || 'TBA')} (${escapeHtml(ev.venue_location || '')})</div>
            <div style="color:var(--text-dim);">${ev.event_date ? formatDate(ev.event_date) + ' | ' + formatTime(ev.start_time) + ' - ' + formatTime(ev.end_time) : 'Schedule Pending'}</div>
          </div>
        </div>

        <div style="margin-top:16px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.05);">
          <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
            <span>Occupancy: <strong>${confirmed} / ${max} Registered</strong></span>
            <span>${max - confirmed} Slots Available (${percent}% Full)</span>
          </div>
          <div class="progress-track" style="height:8px;">
            <div class="progress-fill" style="width:${percent}%;"></div>
          </div>
        </div>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
        <h4 style="font-size:15px; font-weight:700; color:#fff;">Registered Participants (${ev.participants?.length || 0})</h4>
        <button class="btn btn-emerald btn-sm" onclick="openDirectRegisterModal(${ev.event_id}, '${escapeHtml(ev.event_name)}')">
          <i class="bi bi-person-plus"></i> Register Attendee
        </button>
      </div>

      <div class="table-responsive" style="max-height:260px;">
        <table class="custom-table">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Email</th>
              <th>College</th>
              <th>Reg Status</th>
              <th>Payment</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            ${(!ev.participants || ev.participants.length === 0) ? `
              <tr><td colspan="6" class="text-center text-muted" style="padding:16px;">No participants registered yet.</td></tr>
            ` : ev.participants.map(p => `
              <tr>
                <td style="font-weight:600; color:#fff;">${escapeHtml(p.participant_name)}</td>
                <td style="font-size:12px; color:var(--text-muted);">${escapeHtml(p.email)}</td>
                <td style="font-size:12px;">${escapeHtml(p.college)}</td>
                <td>${getStatusBadge(p.registration_status)}</td>
                <td>
                  <span onclick="quickTogglePayment(${p.registration_id}, '${p.payment_status}', ${ev.event_id})" style="cursor:pointer;" title="Click to toggle status">
                    ${getPaymentBadge(p.payment_status)}
                  </span>
                </td>
                <td>
                  <button class="btn btn-danger btn-sm btn-icon" onclick="deleteRegistrationFromDetail(${p.registration_id}, ${ev.event_id})" title="Remove">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;

    openModal('eventDetailsModal');
  } catch (err) {
    showToast('Failed to load event details: ' + err.message, 'error');
  }
}

// Open Create Event Modal
function openCreateEventModal() {
  document.getElementById('eventForm').reset();
  document.getElementById('eventFormId').value = '';
  document.getElementById('eventModalTitle').textContent = 'Create New Event';

  populateCategorySelect('eventCategorySelect');
  populateOrganizerSelect('eventOrganizerSelect');
  populateVenueSelect('eventVenueSelect');

  openModal('eventModal');
}

// Open Edit Event Modal
async function openEditEventModal(eventId) {
  try {
    const res = await fetch(`${API_BASE}/events.php?id=${eventId}`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    const ev = result.data;
    document.getElementById('eventModalTitle').textContent = 'Edit Event Details';
    document.getElementById('eventFormId').value = ev.event_id;
    document.getElementById('eventNameInput').value = ev.event_name;
    document.getElementById('eventDescInput').value = ev.description || '';
    document.getElementById('eventFeeInput').value = ev.registration_fee;
    document.getElementById('eventMaxInput').value = ev.max_participants;
    document.getElementById('eventStatusSelect').value = ev.status;

    populateCategorySelect('eventCategorySelect', ev.category_id);
    populateOrganizerSelect('eventOrganizerSelect', ev.organizer_id);
    populateVenueSelect('eventVenueSelect', ev.venue_id);

    document.getElementById('eventDateInput').value = ev.event_date || '';
    document.getElementById('eventStartTimeInput').value = ev.start_time || '';
    document.getElementById('eventEndTimeInput').value = ev.end_time || '';

    openModal('eventModal');
  } catch (err) {
    showToast('Failed to load event for editing: ' + err.message, 'error');
  }
}

// Save Event (Create or Update)
async function saveEvent(e) {
  e.preventDefault();
  const eventId = document.getElementById('eventFormId').value;
  const payload = {
    event_name: document.getElementById('eventNameInput').value.trim(),
    description: document.getElementById('eventDescInput').value.trim(),
    category_id: document.getElementById('eventCategorySelect').value,
    organizer_id: document.getElementById('eventOrganizerSelect').value,
    registration_fee: document.getElementById('eventFeeInput').value,
    max_participants: document.getElementById('eventMaxInput').value,
    status: document.getElementById('eventStatusSelect').value,
    venue_id: document.getElementById('eventVenueSelect').value || null,
    event_date: document.getElementById('eventDateInput').value || null,
    start_time: document.getElementById('eventStartTimeInput').value || null,
    end_time: document.getElementById('eventEndTimeInput').value || null
  };

  if (eventId) {
    payload.event_id = eventId;
  }

  try {
    const method = eventId ? 'PUT' : 'POST';
    const res = await fetch(`${API_BASE}/events.php`, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message || 'Event saved successfully!', 'success');
    closeModal('eventModal');
    loadEvents();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// Delete Event
async function deleteEvent(eventId) {
  if (!confirm('Are you sure you want to delete this event? This will cascade and delete its schedule and registrations.')) {
    return;
  }
  try {
    const res = await fetch(`${API_BASE}/events.php?id=${eventId}`, { method: 'DELETE' });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message || 'Event deleted successfully.', 'success');
    loadEvents();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// ----------------------------------------------------
// 3. REGISTRATIONS
// ----------------------------------------------------
async function loadRegistrations() {
  try {
    const search = document.getElementById('regSearchInput')?.value || '';
    const regStatus = document.getElementById('regStatusFilter')?.value || '';
    const payStatus = document.getElementById('regPayFilter')?.value || '';

    let url = `${API_BASE}/registrations.php?`;
    if (search) url += `search=${encodeURIComponent(search)}&`;
    if (regStatus) url += `registration_status=${encodeURIComponent(regStatus)}&`;
    if (payStatus) url += `payment_status=${encodeURIComponent(payStatus)}&`;

    const res = await fetch(url);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    state.registrations = result.data;
    renderRegistrations();
  } catch (err) {
    showToast('Failed to load registrations: ' + err.message, 'error');
  }
}

function renderRegistrations() {
  const tbody = document.getElementById('registrationsTableBody');
  if (!tbody) return;

  if (state.registrations.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted" style="padding:30px;">No registrations found.</td></tr>`;
    return;
  }

  tbody.innerHTML = state.registrations.map(r => `
    <tr>
      <td>#${r.registration_id}</td>
      <td>
        <div style="font-weight:700; color:#fff;">${escapeHtml(r.participant_name)}</div>
        <div style="font-size:12px; color:var(--text-muted);">${escapeHtml(r.participant_email)} | ${escapeHtml(r.participant_phone || 'No phone')}</div>
        <div style="font-size:11px; color:var(--accent-cyan);">${escapeHtml(r.college)}</div>
      </td>
      <td>
        <div style="font-weight:600;">${escapeHtml(r.event_name)}</div>
        <div style="font-size:11.5px; color:var(--text-dim);">${escapeHtml(r.category_name)}</div>
      </td>
      <td>
        <strong>${Number(r.registration_fee) === 0 ? 'Free' : '₹' + r.registration_fee}</strong>
      </td>
      <td>
        <span onclick="toggleRegStatusPrompt(${r.registration_id}, '${r.registration_status}')" style="cursor:pointer;" title="Click to change status">
          ${getStatusBadge(r.registration_status)}
        </span>
      </td>
      <td>
        <span onclick="togglePaymentStatusPrompt(${r.registration_id}, '${r.payment_status}')" style="cursor:pointer;" title="Click to change payment status">
          ${getPaymentBadge(r.payment_status)}
        </span>
      </td>
      <td>
        <button class="btn btn-danger btn-sm btn-icon" onclick="deleteRegistration(${r.registration_id})" title="Cancel/Delete">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

function openCreateRegistrationModal() {
  document.getElementById('registrationForm').reset();
  populateEventSelect('regEventSelect');
  populateParticipantSelect('regParticipantSelect');
  toggleParticipantInputMode('existing');
  openModal('registrationModal');
}

function toggleParticipantInputMode(mode) {
  const existingSection = document.getElementById('existingParticipantSection');
  const newSection = document.getElementById('newParticipantSection');
  if (mode === 'existing') {
    existingSection.style.display = 'block';
    newSection.style.display = 'none';
  } else {
    existingSection.style.display = 'none';
    newSection.style.display = 'block';
  }
}

async function saveRegistration(e) {
  e.preventDefault();
  const eventId = document.getElementById('regEventSelect').value;
  const isExisting = document.querySelector('input[name="participantMode"]:checked').value === 'existing';

  const payload = {
    event_id: eventId,
    registration_status: document.getElementById('regStatusSelect').value,
    payment_status: document.getElementById('regPaymentSelect').value
  };

  if (isExisting) {
    const partId = document.getElementById('regParticipantSelect').value;
    if (!partId) {
      showToast('Please select a registered participant.', 'error');
      return;
    }
    payload.participant_id = partId;
  } else {
    const name = document.getElementById('regNewName').value.trim();
    const email = document.getElementById('regNewEmail').value.trim();
    const phone = document.getElementById('regNewPhone').value.trim();
    const college = document.getElementById('regNewCollege').value.trim();

    if (!name || !email || !college) {
      showToast('Name, Email, and College are required for new participant.', 'error');
      return;
    }
    payload.participant_name = name;
    payload.email = email;
    payload.phone = phone;
    payload.college = college;
  }

  try {
    const res = await fetch(`${API_BASE}/registrations.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message || 'Registration created successfully!', 'success');
    closeModal('registrationModal');
    loadRegistrations();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteRegistration(regId) {
  if (!confirm('Are you sure you want to remove this registration record?')) return;
  try {
    const res = await fetch(`${API_BASE}/registrations.php?id=${regId}`, { method: 'DELETE' });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message, 'success');
    loadRegistrations();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function quickTogglePayment(regId, currentStatus, eventId) {
  const nextStatus = currentStatus === 'Paid' ? 'Pending' : 'Paid';
  try {
    const res = await fetch(`${API_BASE}/registrations.php`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ registration_id: regId, payment_status: nextStatus })
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(`Payment marked as ${nextStatus}`, 'success');
    viewEventDetails(eventId);
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function togglePaymentStatusPrompt(regId, currentStatus) {
  const statuses = ['Paid', 'Pending', 'Refunded', 'Not Required'];
  let next = statuses[(statuses.indexOf(currentStatus) + 1) % statuses.length];
  try {
    const res = await fetch(`${API_BASE}/registrations.php`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ registration_id: regId, payment_status: next })
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    showToast(`Payment updated to ${next}`, 'success');
    loadRegistrations();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function toggleRegStatusPrompt(regId, currentStatus) {
  const statuses = ['Confirmed', 'Pending', 'Cancelled'];
  let next = statuses[(statuses.indexOf(currentStatus) + 1) % statuses.length];
  try {
    const res = await fetch(`${API_BASE}/registrations.php`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ registration_id: regId, registration_status: next })
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    showToast(`Registration status updated to ${next}`, 'success');
    loadRegistrations();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteRegistrationFromDetail(regId, eventId) {
  if (!confirm('Remove this participant registration?')) return;
  try {
    const res = await fetch(`${API_BASE}/registrations.php?id=${regId}`, { method: 'DELETE' });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    showToast('Participant registration removed', 'success');
    viewEventDetails(eventId);
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// Direct Attendee Register Modal from Event Details
function openDirectRegisterModal(eventId, eventName) {
  document.getElementById('registrationForm').reset();
  populateEventSelect('regEventSelect', eventId);
  populateParticipantSelect('regParticipantSelect');
  toggleParticipantInputMode('existing');
  closeModal('eventDetailsModal');
  openModal('registrationModal');
}

// ----------------------------------------------------
// 4. PARTICIPANTS
// ----------------------------------------------------
async function loadParticipants() {
  try {
    const search = document.getElementById('participantSearchInput')?.value || '';
    let url = `${API_BASE}/participants.php`;
    if (search) url += `?search=${encodeURIComponent(search)}`;

    const res = await fetch(url);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    state.participants = result.data;
    renderParticipants();
  } catch (err) {
    showToast('Failed to load participants: ' + err.message, 'error');
  }
}

function renderParticipants() {
  const tbody = document.getElementById('participantsTableBody');
  if (!tbody) return;

  if (state.participants.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted" style="padding:30px;">No participants found.</td></tr>`;
    return;
  }

  tbody.innerHTML = state.participants.map(p => `
    <tr>
      <td>#${p.participant_id}</td>
      <td>
        <div style="font-weight:700; color:#fff;">${escapeHtml(p.participant_name)}</div>
        <div style="font-size:12px; color:var(--text-muted);">${escapeHtml(p.email)}</div>
      </td>
      <td>${escapeHtml(p.phone || 'N/A')}</td>
      <td><span class="badge badge-cyan">${escapeHtml(p.college)}</span></td>
      <td>
        <span class="badge badge-purple">${p.total_events_registered} Registered (${p.confirmed_events} Confirmed)</span>
      </td>
      <td>
        <button class="btn btn-secondary btn-sm btn-icon" onclick="viewParticipantHistory(${p.participant_id})" title="View Enrolled Events">
          <i class="bi bi-clock-history"></i>
        </button>
        <button class="btn btn-secondary btn-sm btn-icon" onclick="openEditParticipantModal(${p.participant_id})" title="Edit Student">
          <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-danger btn-sm btn-icon" onclick="deleteParticipant(${p.participant_id})" title="Delete">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

function openCreateParticipantModal() {
  document.getElementById('participantForm').reset();
  document.getElementById('participantFormId').value = '';
  document.getElementById('participantModalTitle').textContent = 'Add New Student / Participant';
  openModal('participantModal');
}

async function openEditParticipantModal(partId) {
  try {
    const res = await fetch(`${API_BASE}/participants.php?id=${partId}`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    const p = result.data;
    document.getElementById('participantModalTitle').textContent = 'Edit Participant Details';
    document.getElementById('participantFormId').value = p.participant_id;
    document.getElementById('partNameInput').value = p.participant_name;
    document.getElementById('partEmailInput').value = p.email;
    document.getElementById('partPhoneInput').value = p.phone || '';
    document.getElementById('partCollegeInput').value = p.college;

    openModal('participantModal');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function saveParticipant(e) {
  e.preventDefault();
  const partId = document.getElementById('participantFormId').value;
  const payload = {
    participant_name: document.getElementById('partNameInput').value.trim(),
    email: document.getElementById('partEmailInput').value.trim(),
    phone: document.getElementById('partPhoneInput').value.trim(),
    college: document.getElementById('partCollegeInput').value.trim()
  };

  if (partId) payload.participant_id = partId;

  try {
    const method = partId ? 'PUT' : 'POST';
    const res = await fetch(`${API_BASE}/participants.php`, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message || 'Participant saved!', 'success');
    closeModal('participantModal');
    loadParticipants();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function viewParticipantHistory(partId) {
  try {
    const res = await fetch(`${API_BASE}/participants.php?id=${partId}`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    const p = result.data;
    alert(`Participant: ${p.participant_name} (${p.college})\nRegistered Events: ${p.registrations?.length || 0}\n\n` + 
      (p.registrations?.map(r => `• ${r.event_name} [${r.registration_status} | Payment: ${r.payment_status}]`).join('\n') || 'None'));
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteParticipant(partId) {
  if (!confirm('Are you sure you want to delete this participant? All their registrations will also be removed.')) return;
  try {
    const res = await fetch(`${API_BASE}/participants.php?id=${partId}`, { method: 'DELETE' });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message, 'success');
    loadParticipants();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// ----------------------------------------------------
// 5. VENUES & SCHEDULES
// ----------------------------------------------------
async function loadVenues(render = true) {
  try {
    const res = await fetch(`${API_BASE}/venues.php`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    state.venues = result.data;
    if (render) renderVenues();
  } catch (err) {
    console.error(err);
  }
}

function renderVenues() {
  const container = document.getElementById('venuesCardsContainer');
  if (!container) return;

  container.innerHTML = state.venues.map(v => `
    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px;">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
        <span class="badge badge-emerald">${escapeHtml(v.venue_type)}</span>
        <span style="font-size:12px; color:var(--text-dim);">ID #${v.venue_id}</span>
      </div>
      <h3 style="font-size:16px; font-weight:700; color:#fff; margin-bottom:4px;">${escapeHtml(v.venue_name)}</h3>
      <div style="font-size:12.5px; color:var(--text-muted); margin-bottom:14px;">
        <i class="bi bi-geo-alt me-1 text-cyan-400"></i>${escapeHtml(v.location)}
      </div>
      <div style="display:flex; justify-content:space-between; font-size:12px; padding:10px; background:rgba(0,0,0,0.2); border-radius:var(--radius-md); margin-bottom:14px;">
        <span>Max Capacity: <strong style="color:#fff;">${v.capacity}</strong></span>
        <span>Scheduled: <strong style="color:var(--accent-purple);">${v.total_events_scheduled}</strong></span>
      </div>
      <div style="display:flex; gap:8px;">
        <button class="btn btn-secondary btn-sm" style="flex:1;" onclick="openEditVenueModal(${v.venue_id})">
          <i class="bi bi-pencil"></i> Edit
        </button>
        <button class="btn btn-danger btn-sm btn-icon" onclick="deleteVenue(${v.venue_id})">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
  `).join('');
}

function openCreateVenueModal() {
  document.getElementById('venueForm').reset();
  document.getElementById('venueFormId').value = '';
  document.getElementById('venueModalTitle').textContent = 'Add Campus Venue';
  openModal('venueModal');
}

async function openEditVenueModal(venueId) {
  try {
    const res = await fetch(`${API_BASE}/venues.php?id=${venueId}`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    const v = result.data;

    document.getElementById('venueModalTitle').textContent = 'Edit Venue Details';
    document.getElementById('venueFormId').value = v.venue_id;
    document.getElementById('venueNameInput').value = v.venue_name;
    document.getElementById('venueLocationInput').value = v.location;
    document.getElementById('venueCapacityInput').value = v.capacity;
    document.getElementById('venueTypeInput').value = v.venue_type;

    openModal('venueModal');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function saveVenue(e) {
  e.preventDefault();
  const venueId = document.getElementById('venueFormId').value;
  const payload = {
    venue_name: document.getElementById('venueNameInput').value.trim(),
    location: document.getElementById('venueLocationInput').value.trim(),
    capacity: document.getElementById('venueCapacityInput').value,
    venue_type: document.getElementById('venueTypeInput').value.trim()
  };

  if (venueId) payload.venue_id = venueId;

  try {
    const method = venueId ? 'PUT' : 'POST';
    const res = await fetch(`${API_BASE}/venues.php`, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message || 'Venue saved!', 'success');
    closeModal('venueModal');
    loadVenues();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteVenue(venueId) {
  if (!confirm('Are you sure you want to delete this venue?')) return;
  try {
    const res = await fetch(`${API_BASE}/venues.php?id=${venueId}`, { method: 'DELETE' });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message, 'success');
    loadVenues();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// Schedules
async function loadSchedules() {
  try {
    const res = await fetch(`${API_BASE}/schedules.php`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    state.schedules = result.data;
    renderSchedules();
  } catch (err) {
    console.error(err);
  }
}

function renderSchedules() {
  const tbody = document.getElementById('schedulesTableBody');
  if (!tbody) return;

  if (state.schedules.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted" style="padding:24px;">No event schedules recorded.</td></tr>`;
    return;
  }

  tbody.innerHTML = state.schedules.map(s => `
    <tr>
      <td>#${s.schedule_id}</td>
      <td>
        <div style="font-weight:700; color:#fff;">${escapeHtml(s.event_name)}</div>
        <div style="font-size:11.5px; color:var(--text-dim);">${escapeHtml(s.category_name)} • ${escapeHtml(s.organizer_name)}</div>
      </td>
      <td>
        <div style="font-weight:600;">${escapeHtml(s.venue_name)}</div>
        <div style="font-size:11.5px; color:var(--text-dim);">${escapeHtml(s.venue_location)}</div>
      </td>
      <td>
        <span class="badge badge-cyan"><i class="bi bi-calendar-event me-1"></i>${formatDate(s.event_date)}</span>
      </td>
      <td>
        <span class="badge badge-amber"><i class="bi bi-clock me-1"></i>${formatTime(s.start_time)} - ${formatTime(s.end_time)}</span>
      </td>
      <td>
        <button class="btn btn-danger btn-sm btn-icon" onclick="deleteSchedule(${s.schedule_id})" title="Delete Schedule">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

function openCreateScheduleModal() {
  document.getElementById('scheduleForm').reset();
  populateEventSelect('schEventSelect');
  populateVenueSelect('schVenueSelect');
  openModal('scheduleModal');
}

async function saveSchedule(e) {
  e.preventDefault();
  const payload = {
    event_id: document.getElementById('schEventSelect').value,
    venue_id: document.getElementById('schVenueSelect').value,
    event_date: document.getElementById('schDateInput').value,
    start_time: document.getElementById('schStartTimeInput').value,
    end_time: document.getElementById('schEndTimeInput').value
  };

  try {
    const res = await fetch(`${API_BASE}/schedules.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message || 'Schedule created successfully!', 'success');
    closeModal('scheduleModal');
    loadSchedules();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteSchedule(schId) {
  if (!confirm('Remove this event schedule?')) return;
  try {
    const res = await fetch(`${API_BASE}/schedules.php?id=${schId}`, { method: 'DELETE' });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    showToast(result.message, 'success');
    loadSchedules();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// ----------------------------------------------------
// 6. ORGANIZERS & CATEGORIES
// ----------------------------------------------------
async function loadOrganizers(render = true) {
  try {
    const res = await fetch(`${API_BASE}/organizers.php`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    state.organizers = result.data;
    if (render) renderOrganizers();
  } catch (err) {
    console.error(err);
  }
}

function renderOrganizers() {
  const tbody = document.getElementById('organizersTableBody');
  if (!tbody) return;

  tbody.innerHTML = state.organizers.map(o => `
    <tr>
      <td>#${o.organizer_id}</td>
      <td>
        <div style="font-weight:700; color:#fff;">${escapeHtml(o.organizer_name)}</div>
        <div style="font-size:12px; color:var(--text-muted);">${escapeHtml(o.email)}</div>
      </td>
      <td>${escapeHtml(o.department || 'N/A')}</td>
      <td>${escapeHtml(o.phone || 'N/A')}</td>
      <td><span class="badge badge-purple">${o.total_events_organized} Events</span></td>
      <td>
        <button class="btn btn-secondary btn-sm btn-icon" onclick="openEditOrganizerModal(${o.organizer_id})">
          <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-danger btn-sm btn-icon" onclick="deleteOrganizer(${o.organizer_id})">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

function openCreateOrganizerModal() {
  document.getElementById('organizerForm').reset();
  document.getElementById('organizerFormId').value = '';
  document.getElementById('organizerModalTitle').textContent = 'Add Organizing Entity / Club';
  openModal('organizerModal');
}

async function openEditOrganizerModal(orgId) {
  try {
    const res = await fetch(`${API_BASE}/organizers.php?id=${orgId}`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    const o = result.data;

    document.getElementById('organizerModalTitle').textContent = 'Edit Organizer Details';
    document.getElementById('organizerFormId').value = o.organizer_id;
    document.getElementById('orgNameInput').value = o.organizer_name;
    document.getElementById('orgEmailInput').value = o.email;
    document.getElementById('orgPhoneInput').value = o.phone || '';
    document.getElementById('orgDeptInput').value = o.department || '';

    openModal('organizerModal');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function saveOrganizer(e) {
  e.preventDefault();
  const orgId = document.getElementById('organizerFormId').value;
  const payload = {
    organizer_name: document.getElementById('orgNameInput').value.trim(),
    email: document.getElementById('orgEmailInput').value.trim(),
    phone: document.getElementById('orgPhoneInput').value.trim(),
    department: document.getElementById('orgDeptInput').value.trim()
  };

  if (orgId) payload.organizer_id = orgId;

  try {
    const method = orgId ? 'PUT' : 'POST';
    const res = await fetch(`${API_BASE}/organizers.php`, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message || 'Organizer saved!', 'success');
    closeModal('organizerModal');
    loadOrganizers();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteOrganizer(orgId) {
  if (!confirm('Are you sure you want to delete this organizer?')) return;
  try {
    const res = await fetch(`${API_BASE}/organizers.php?id=${orgId}`, { method: 'DELETE' });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message, 'success');
    loadOrganizers();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// Categories
async function loadCategories(render = true) {
  try {
    const res = await fetch(`${API_BASE}/categories.php`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    state.categories = result.data;
    if (render) renderCategories();
  } catch (err) {
    console.error(err);
  }
}

function renderCategories() {
  const container = document.getElementById('categoriesGridContainer');
  if (!container) return;

  container.innerHTML = state.categories.map(c => `
    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; display:flex; flex-direction:column; justify-content:space-between;">
      <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
          <h4 style="font-size:16px; font-weight:700; color:#fff;">${escapeHtml(c.category_name)}</h4>
          <span class="badge badge-purple">${c.total_events} Events</span>
        </div>
        <p style="font-size:13px; color:var(--text-muted); line-height:1.5; margin-bottom:14px;">${escapeHtml(c.description || 'No description.')}</p>
      </div>
      <div style="display:flex; gap:8px;">
        <button class="btn btn-secondary btn-sm" style="flex:1;" onclick="openEditCategoryModal(${c.category_id}, '${escapeHtml(c.category_name)}', '${escapeHtml(c.description || '')}')">
          <i class="bi bi-pencil"></i> Edit
        </button>
        <button class="btn btn-danger btn-sm btn-icon" onclick="deleteCategory(${c.category_id})">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
  `).join('');
}

function openCreateCategoryModal() {
  document.getElementById('categoryForm').reset();
  document.getElementById('categoryFormId').value = '';
  document.getElementById('categoryModalTitle').textContent = 'Create Event Category';
  openModal('categoryModal');
}

function openEditCategoryModal(catId, catName, desc) {
  document.getElementById('categoryModalTitle').textContent = 'Edit Event Category';
  document.getElementById('categoryFormId').value = catId;
  document.getElementById('catNameInput').value = catName;
  document.getElementById('catDescInput').value = desc;
  openModal('categoryModal');
}

async function saveCategory(e) {
  e.preventDefault();
  const catId = document.getElementById('categoryFormId').value;
  const payload = {
    category_name: document.getElementById('catNameInput').value.trim(),
    description: document.getElementById('catDescInput').value.trim()
  };

  if (catId) payload.category_id = catId;

  try {
    const method = catId ? 'PUT' : 'POST';
    const res = await fetch(`${API_BASE}/categories.php`, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message || 'Category saved!', 'success');
    closeModal('categoryModal');
    loadCategories();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteCategory(catId) {
  if (!confirm('Delete this category?')) return;
  try {
    const res = await fetch(`${API_BASE}/categories.php?id=${catId}`, { method: 'DELETE' });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    showToast(result.message, 'success');
    loadCategories();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// ----------------------------------------------------
// 7. PUBLIC STUDENT PORTAL
// ----------------------------------------------------
async function loadStudentPortal() {
  try {
    const res = await fetch(`${API_BASE}/events.php?status=Upcoming`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    const container = document.getElementById('studentPortalEvents');
    if (!container) return;

    if (result.data.length === 0) {
      container.innerHTML = `<div class="text-center text-muted" style="padding:40px;">No upcoming events available for registration at this moment.</div>`;
      return;
    }

    container.innerHTML = result.data.map(ev => {
      const fee = Number(ev.registration_fee);
      const seatsLeft = Number(ev.seats_left);
      const isFull = seatsLeft <= 0;

      return `
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden; display:flex; flex-direction:column; box-shadow:var(--shadow-md);">
          <div style="height:100px; background:linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(6, 182, 212, 0.3)); padding:16px; display:flex; justify-content:space-between; align-items:flex-start;">
            <span class="badge badge-purple">${escapeHtml(ev.category_name)}</span>
            <span class="badge ${fee === 0 ? 'badge-emerald' : 'badge-blue'}" style="font-size:12px;">
              ${fee === 0 ? 'FREE ENTRY' : '₹' + fee}
            </span>
          </div>
          <div style="padding:20px; flex:1; display:flex; flex-direction:column;">
            <h3 style="font-size:18px; font-weight:700; color:#fff; margin-bottom:8px;">${escapeHtml(ev.event_name)}</h3>
            <p style="font-size:13px; color:var(--text-muted); line-height:1.5; margin-bottom:14px;">${escapeHtml(ev.description || '')}</p>

            <div style="font-size:12.5px; color:var(--text-muted); display:flex; flex-direction:column; gap:6px; margin-bottom:16px; padding:12px; background:rgba(0,0,0,0.2); border-radius:var(--radius-md);">
              <div><i class="bi bi-calendar-event me-2 text-indigo-400"></i>${ev.event_date ? formatDate(ev.event_date) + ' at ' + formatTime(ev.start_time) : 'TBA'}</div>
              <div><i class="bi bi-geo-alt me-2 text-cyan-400"></i>${escapeHtml(ev.venue_name || 'Campus Venue')}</div>
              <div><i class="bi bi-people me-2 text-emerald-400"></i>Club: ${escapeHtml(ev.organizer_name)}</div>
            </div>

            <div style="margin-top:auto; display:flex; justify-content:space-between; align-items:center;">
              <span style="font-size:12px; font-weight:600; color:${isFull ? 'var(--accent-rose)' : 'var(--accent-emerald)'};">
                ${isFull ? '● Registration Full' : `● ${seatsLeft} Seats Remaining`}
              </span>
              <button class="btn btn-primary btn-sm" ${isFull ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''} onclick="openStudentRegisterModal(${ev.event_id}, '${escapeHtml(ev.event_name)}', ${fee})">
                <i class="bi bi-check2-circle"></i> Register Now
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

function openStudentRegisterModal(eventId, eventName, fee) {
  document.getElementById('studentRegisterForm').reset();
  document.getElementById('studentRegEventId').value = eventId;
  document.getElementById('studentRegEventTitle').textContent = eventName;
  document.getElementById('studentRegEventFee').textContent = fee === 0 ? 'Free Event' : `Registration Fee: ₹${fee}`;
  openModal('studentRegisterModal');
}

async function submitStudentRegistration(e) {
  e.preventDefault();
  const eventId = document.getElementById('studentRegEventId').value;
  const payload = {
    event_id: eventId,
    participant_name: document.getElementById('studentNameInput').value.trim(),
    email: document.getElementById('studentEmailInput').value.trim(),
    phone: document.getElementById('studentPhoneInput').value.trim(),
    college: document.getElementById('studentCollegeInput').value.trim(),
    registration_status: 'Confirmed'
  };

  try {
    const res = await fetch(`${API_BASE}/registrations.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!result.success) throw new Error(result.error);

    closeModal('studentRegisterModal');
    showToast('🎉 Registration Successful! Welcome to the event.', 'success');
    loadStudentPortal();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// ----------------------------------------------------
// 8. DBMS PBL LAB: PRESET QUERIES & LIVE PLAYGROUND
// ----------------------------------------------------
async function loadPblPresets() {
  try {
    const res = await fetch(`${API_BASE}/query.php`);
    const result = await res.json();
    if (!result.success) throw new Error(result.error);
    state.presets = result.data;
    renderPblPresets();
  } catch (err) {
    console.error(err);
  }
}

function renderPblPresets() {
  const container = document.getElementById('pblPresetQueriesList');
  if (!container) return;

  container.innerHTML = state.presets.map((p, idx) => `
    <div class="query-preset-item" onclick="loadPresetQuery(${idx})">
      <div class="query-preset-header">
        <span class="query-preset-title">${escapeHtml(p.title)}</span>
        <span class="query-preset-concept">${escapeHtml(p.concept)}</span>
      </div>
      <div style="font-size:12.5px; color:var(--text-muted);">${escapeHtml(p.description)}</div>
    </div>
  `).join('');
}

function loadPresetQuery(index) {
  const query = state.presets[index];
  if (!query) return;

  document.getElementById('sqlQueryInput').value = query.sql;
  executeSql();
}

async function executeSql() {
  const sql = document.getElementById('sqlQueryInput').value.trim();
  if (!sql) {
    showToast('Please enter an SQL query first.', 'error');
    return;
  }

  const resultArea = document.getElementById('sqlQueryResultArea');
  resultArea.innerHTML = `<div style="text-align:center; padding:30px; color:var(--text-muted);"><i class="bi bi-arrow-repeat spin me-2"></i> Executing query on MariaDB...</div>`;

  try {
    const res = await fetch(`${API_BASE}/query.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ sql })
    });
    const result = await res.json();

    if (!result.success) {
      resultArea.innerHTML = `
        <div style="padding:16px; background:rgba(244, 63, 94, 0.1); border:1px solid rgba(244, 63, 94, 0.3); border-radius:var(--radius-md); color:#fca5a5; font-size:13.5px;">
          <div style="font-weight:700; margin-bottom:4px;"><i class="bi bi-x-circle me-1"></i> Query Execution Failed:</div>
          <div>${escapeHtml(result.error)}</div>
        </div>
      `;
      return;
    }

    const { columns, rows, count, execution_time_ms } = result.data;

    let tableHtml = `
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:12.5px; color:var(--text-muted);">
        <span><strong style="color:#fff;">${count}</strong> record(s) returned</span>
        <span><i class="bi bi-stopwatch me-1 text-emerald-400"></i> Execution time: <strong>${execution_time_ms} ms</strong></span>
      </div>
      <div class="table-responsive" style="max-height:350px;">
        <table class="custom-table">
          <thead>
            <tr>
              ${columns.map(col => `<th>${escapeHtml(col)}</th>`).join('')}
            </tr>
          </thead>
          <tbody>
    `;

    if (rows.length === 0) {
      tableHtml += `<tr><td colspan="${columns.length || 1}" class="text-center text-muted" style="padding:20px;">Query returned 0 rows.</td></tr>`;
    } else {
      rows.forEach(row => {
        tableHtml += '<tr>';
        columns.forEach(col => {
          const val = row[col];
          tableHtml += `<td>${val === null ? '<em style="color:#64748b;">NULL</em>' : escapeHtml(val)}</td>`;
        });
        tableHtml += '</tr>';
      });
    }

    tableHtml += `
          </tbody>
        </table>
      </div>
    `;

    resultArea.innerHTML = tableHtml;
  } catch (err) {
    resultArea.innerHTML = `<div class="text-danger" style="padding:20px;">Execution Error: ${escapeHtml(err.message)}</div>`;
  }
}

// ----------------------------------------------------
// HELPERS & MODAL HANDLERS
// ----------------------------------------------------
function initModals() {
  document.querySelectorAll('.modal-close, [data-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-overlay');
      if (modal) modal.classList.remove('active');
    });
  });

  window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
      e.target.classList.remove('active');
    }
  });

  // Attach Form Submit Handlers
  document.getElementById('eventForm')?.addEventListener('submit', saveEvent);
  document.getElementById('registrationForm')?.addEventListener('submit', saveRegistration);
  document.getElementById('participantForm')?.addEventListener('submit', saveParticipant);
  document.getElementById('venueForm')?.addEventListener('submit', saveVenue);
  document.getElementById('scheduleForm')?.addEventListener('submit', saveSchedule);
  document.getElementById('organizerForm')?.addEventListener('submit', saveOrganizer);
  document.getElementById('categoryForm')?.addEventListener('submit', saveCategory);
  document.getElementById('studentRegisterForm')?.addEventListener('submit', submitStudentRegistration);
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('active');
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.remove('active');
}

function populateCategorySelect(selectId, selectedId = null) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = '<option value="">-- Choose Category --</option>' + 
    state.categories.map(c => `<option value="${c.category_id}" ${selectedId == c.category_id ? 'selected' : ''}>${escapeHtml(c.category_name)}</option>`).join('');
}

function populateOrganizerSelect(selectId, selectedId = null) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = '<option value="">-- Choose Organizer / Club --</option>' + 
    state.organizers.map(o => `<option value="${o.organizer_id}" ${selectedId == o.organizer_id ? 'selected' : ''}>${escapeHtml(o.organizer_name)} (${escapeHtml(o.department || 'General')})</option>`).join('');
}

function populateVenueSelect(selectId, selectedId = null) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = '<option value="">-- Select Venue (Optional) --</option>' + 
    state.venues.map(v => `<option value="${v.venue_id}" ${selectedId == v.venue_id ? 'selected' : ''}>${escapeHtml(v.venue_name)} (Cap: ${v.capacity})</option>`).join('');
}

function populateEventSelect(selectId, selectedId = null) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = '<option value="">-- Choose Event --</option>' + 
    state.events.map(ev => `<option value="${ev.event_id}" ${selectedId == ev.event_id ? 'selected' : ''}>${escapeHtml(ev.event_name)} (${ev.category_name})</option>`).join('');
}

function populateParticipantSelect(selectId, selectedId = null) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = '<option value="">-- Select Registered Participant --</option>' + 
    state.participants.map(p => `<option value="${p.participant_id}" ${selectedId == p.participant_id ? 'selected' : ''}>${escapeHtml(p.participant_name)} (${escapeHtml(p.email)})</option>`).join('');
}

function getStatusBadge(status) {
  switch (status) {
    case 'Confirmed':
    case 'Upcoming':
      return `<span class="badge badge-emerald"><i class="bi bi-check-circle-fill"></i> ${status}</span>`;
    case 'Ongoing':
      return `<span class="badge badge-cyan"><i class="bi bi-play-circle-fill"></i> ${status}</span>`;
    case 'Completed':
      return `<span class="badge badge-blue"><i class="bi bi-flag-fill"></i> ${status}</span>`;
    case 'Pending':
      return `<span class="badge badge-amber"><i class="bi bi-clock-fill"></i> ${status}</span>`;
    case 'Cancelled':
      return `<span class="badge badge-rose"><i class="bi bi-x-circle-fill"></i> ${status}</span>`;
    default:
      return `<span class="badge badge-gray">${escapeHtml(status)}</span>`;
  }
}

function getPaymentBadge(status) {
  switch (status) {
    case 'Paid':
      return `<span class="badge badge-emerald"><i class="bi bi-currency-rupee"></i> Paid</span>`;
    case 'Pending':
      return `<span class="badge badge-amber"><i class="bi bi-hourglass-split"></i> Pending</span>`;
    case 'Not Required':
      return `<span class="badge badge-gray">Free / None</span>`;
    case 'Refunded':
      return `<span class="badge badge-rose"><i class="bi bi-arrow-counterclockwise"></i> Refunded</span>`;
    default:
      return `<span class="badge badge-gray">${escapeHtml(status)}</span>`;
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatTime(timeStr) {
  if (!timeStr) return '';
  const [h, m] = timeStr.split(':');
  const d = new Date();
  d.setHours(h, m, 0);
  return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
