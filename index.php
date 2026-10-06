<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventSphere | DBMS PBL - Complete Event Management System</title>
  
  <!-- Modern Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  
  <!-- Custom Styles -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Toast Container -->
  <div class="toast-container" id="toastContainer"></div>

  <!-- Sidebar Navigation -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">
        <i class="bi bi-calendar-event"></i>
      </div>
      <div class="brand-info">
        <h2>EventSphere</h2>
        <span>DBMS PBL System</span>
      </div>
    </div>

    <div class="sidebar-nav">
      <div class="nav-section-title">Overview</div>
      <a class="nav-item active" data-tab="dashboard">
        <i class="bi bi-grid-1x2"></i>
        <span>Dashboard</span>
      </a>
      <a class="nav-item" data-tab="events">
        <i class="bi bi-calendar3"></i>
        <span>Events</span>
      </a>
      <a class="nav-item" data-tab="registrations">
        <i class="bi bi-card-checklist"></i>
        <span>Registrations</span>
      </a>
      <a class="nav-item" data-tab="participants">
        <i class="bi bi-people"></i>
        <span>Participants</span>
      </a>

      <div class="nav-section-title">Logistics & Infrastructure</div>
      <a class="nav-item" data-tab="venues-schedules">
        <i class="bi bi-building"></i>
        <span>Venues & Schedules</span>
      </a>
      <a class="nav-item" data-tab="organizers-categories">
        <i class="bi bi-person-badge"></i>
        <span>Clubs & Categories</span>
      </a>

      <div class="nav-section-title">Portals & DBMS Lab</div>
      <a class="nav-item" data-tab="student-portal">
        <i class="bi bi-globe"></i>
        <span>Student Portal</span>
        <span class="nav-badge">Live</span>
      </a>
      <a class="nav-item" data-tab="dbms-pbl">
        <i class="bi bi-diagram-3"></i>
        <span>ER Diagram & SQL Lab</span>
        <span class="nav-badge" style="background:var(--accent-purple-light); color:var(--accent-purple);">PBL</span>
      </a>
    </div>

    <div class="sidebar-footer">
      <div class="db-status-badge">
        <span class="status-dot"></span>
        <div style="flex:1;">
          <strong style="color:#fff;">MySQL 10.4-MariaDB</strong>
          <div style="font-size:11px; color:var(--text-dim);">Database: event_management</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content Wrapper -->
  <main class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title" id="currentViewTitle">Analytics & Executive Dashboard</h1>
      </div>
      <div class="topbar-right">
        <button class="btn btn-secondary btn-sm" onclick="openCreateRegistrationModal()">
          <i class="bi bi-person-plus"></i> Register Attendee
        </button>
        <button class="btn btn-primary btn-sm" onclick="openCreateEventModal()">
          <i class="bi bi-plus-lg"></i> Create Event
        </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <div class="content-area">

      <!-- ============================================== -->
      <!-- TAB 1: DASHBOARD -->
      <!-- ============================================== -->
      <section class="tab-pane active" id="tab-dashboard">
        <!-- Top KPI Metrics -->
        <div class="metrics-grid">
          <div class="metric-card" style="--card-accent: var(--primary);">
            <div class="metric-top">
              <span class="metric-title">Total Events</span>
              <div class="metric-icon-wrap" style="background:var(--primary-light); color:var(--primary);">
                <i class="bi bi-calendar3"></i>
              </div>
            </div>
            <div class="metric-value" id="metricTotalEvents">0</div>
            <div class="metric-footer"><i class="bi bi-check2 text-emerald-400"></i> Active in database</div>
          </div>

          <div class="metric-card" style="--card-accent: var(--accent-cyan);">
            <div class="metric-top">
              <span class="metric-title">Total Registrations</span>
              <div class="metric-icon-wrap" style="background:var(--accent-cyan-light); color:var(--accent-cyan);">
                <i class="bi bi-card-checklist"></i>
              </div>
            </div>
            <div class="metric-value" id="metricTotalRegistrations">0</div>
            <div class="metric-footer"><i class="bi bi-arrow-up text-cyan-400"></i> Across all events</div>
          </div>

          <div class="metric-card" style="--card-accent: var(--accent-emerald);">
            <div class="metric-top">
              <span class="metric-title">Total Participants</span>
              <div class="metric-icon-wrap" style="background:var(--accent-emerald-light); color:var(--accent-emerald);">
                <i class="bi bi-people"></i>
              </div>
            </div>
            <div class="metric-value" id="metricTotalParticipants">0</div>
            <div class="metric-footer"><i class="bi bi-mortarboard text-emerald-400"></i> University students</div>
          </div>

          <div class="metric-card" style="--card-accent: var(--accent-amber);">
            <div class="metric-top">
              <span class="metric-title">Venues Configured</span>
              <div class="metric-icon-wrap" style="background:var(--accent-amber-light); color:var(--accent-amber);">
                <i class="bi bi-building"></i>
              </div>
            </div>
            <div class="metric-value" id="metricTotalVenues">0</div>
            <div class="metric-footer"><i class="bi bi-geo-alt text-amber-400"></i> Campus facilities</div>
          </div>

          <div class="metric-card" style="--card-accent: var(--accent-purple);">
            <div class="metric-top">
              <span class="metric-title">Total Revenue Collected</span>
              <div class="metric-icon-wrap" style="background:var(--accent-purple-light); color:var(--accent-purple);">
                <i class="bi bi-currency-rupee"></i>
              </div>
            </div>
            <div class="metric-value" id="metricTotalRevenue">₹0</div>
            <div class="metric-footer"><i class="bi bi-cash-stack text-purple-400"></i> From paid tickets</div>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
          <div class="card">
            <div class="card-header">
              <div>
                <h3 class="card-title"><i class="bi bi-pie-chart"></i> Events by Category</h3>
                <p class="card-desc">Distribution of campus events across categories</p>
              </div>
            </div>
            <div class="chart-container">
              <canvas id="categoryDistributionChart"></canvas>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div>
                <h3 class="card-title"><i class="bi bi-bar-chart"></i> Registrations & Payment Status</h3>
                <p class="card-desc">Overview of attendance confirmations and fee collection</p>
              </div>
            </div>
            <div class="chart-container">
              <canvas id="registrationStatusChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Dashboard Bottom Feed -->
        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
          <div class="card">
            <div class="card-header">
              <div>
                <h3 class="card-title"><i class="bi bi-clock-history"></i> Recent Registrations</h3>
                <p class="card-desc">Latest student enrolments in events</p>
              </div>
              <button class="btn btn-secondary btn-sm" onclick="switchTab('registrations')">View All</button>
            </div>
            <div class="table-responsive">
              <table class="custom-table">
                <thead>
                  <tr>
                    <th>Participant</th>
                    <th>Event Enrolled</th>
                    <th>Date</th>
                    <th>Registration</th>
                    <th>Payment</th>
                  </tr>
                </thead>
                <tbody id="dashboardRecentRegistrations">
                  <!-- Injected by JS -->
                </tbody>
              </table>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div>
                <h3 class="card-title"><i class="bi bi-calendar2-week"></i> Upcoming Timeline</h3>
                <p class="card-desc">Next scheduled event sessions</p>
              </div>
              <button class="btn btn-secondary btn-sm" onclick="switchTab('venues-schedules')">Schedule</button>
            </div>
            <div id="dashboardUpcomingSchedules">
              <!-- Injected by JS -->
            </div>
          </div>
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAB 2: EVENTS -->
      <!-- ============================================== -->
      <section class="tab-pane" id="tab-events">
        <div class="filter-bar">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="eventSearchInput" placeholder="Search events by title, description or organizer..." oninput="loadEvents()">
          </div>
          <div class="filter-group">
            <select class="select-custom" id="eventCategoryFilter" onchange="loadEvents()">
              <option value="">All Categories</option>
            </select>
            <select class="select-custom" id="eventStatusFilter" onchange="loadEvents()">
              <option value="">All Statuses</option>
              <option value="Upcoming">Upcoming</option>
              <option value="Ongoing">Ongoing</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
            <div style="display:flex; gap:4px; background:rgba(255,255,255,0.04); padding:3px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
              <button class="btn btn-secondary btn-sm active" id="btnEventsGrid" onclick="toggleEventView('grid')">
                <i class="bi bi-grid"></i>
              </button>
              <button class="btn btn-secondary btn-sm" id="btnEventsTable" onclick="toggleEventView('table')">
                <i class="bi bi-list-ul"></i>
              </button>
            </div>
            <button class="btn btn-primary btn-sm" onclick="openCreateEventModal()">
              <i class="bi bi-plus-lg"></i> Add Event
            </button>
          </div>
        </div>

        <div id="eventsContentArea">
          <!-- Injected by JS -->
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAB 3: REGISTRATIONS -->
      <!-- ============================================== -->
      <section class="tab-pane" id="tab-registrations">
        <div class="filter-bar">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="regSearchInput" placeholder="Search registrations by student, email, college or event..." oninput="loadRegistrations()">
          </div>
          <div class="filter-group">
            <select class="select-custom" id="regStatusFilter" onchange="loadRegistrations()">
              <option value="">All Reg Statuses</option>
              <option value="Confirmed">Confirmed</option>
              <option value="Pending">Pending</option>
              <option value="Cancelled">Cancelled</option>
            </select>
            <select class="select-custom" id="regPayFilter" onchange="loadRegistrations()">
              <option value="">All Payment Statuses</option>
              <option value="Paid">Paid</option>
              <option value="Pending">Pending</option>
              <option value="Not Required">Not Required</option>
              <option value="Refunded">Refunded</option>
            </select>
            <button class="btn btn-primary btn-sm" onclick="openCreateRegistrationModal()">
              <i class="bi bi-person-plus"></i> New Registration
            </button>
          </div>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>Reg ID</th>
                  <th>Student Info</th>
                  <th>Event</th>
                  <th>Fee</th>
                  <th>Registration Status</th>
                  <th>Payment Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="registrationsTableBody">
                <!-- Injected by JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAB 4: PARTICIPANTS -->
      <!-- ============================================== -->
      <section class="tab-pane" id="tab-participants">
        <div class="filter-bar">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="participantSearchInput" placeholder="Search students by name, email, phone or college..." oninput="loadParticipants()">
          </div>
          <button class="btn btn-primary btn-sm" onclick="openCreateParticipantModal()">
            <i class="bi bi-person-plus"></i> Add Student
          </button>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Student Name</th>
                  <th>Phone</th>
                  <th>College / Institution</th>
                  <th>Enrolments</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="participantsTableBody">
                <!-- Injected by JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAB 5: VENUES & SCHEDULES -->
      <!-- ============================================== -->
      <section class="tab-pane" id="tab-venues-schedules">
        <!-- Venues Section -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <div>
            <h2 style="font-size:18px; font-weight:700; color:#fff;">Campus Venues & Facilities</h2>
            <p style="font-size:13px; color:var(--text-dim);">Auditoriums, seminar halls, and computer labs</p>
          </div>
          <button class="btn btn-primary btn-sm" onclick="openCreateVenueModal()">
            <i class="bi bi-plus-lg"></i> Add Venue
          </button>
        </div>

        <div id="venuesCardsContainer" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px; margin-bottom:36px;">
          <!-- Injected by JS -->
        </div>

        <!-- Schedules Section -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <div>
            <h2 style="font-size:18px; font-weight:700; color:#fff;">Event Schedules Timeline</h2>
            <p style="font-size:13px; color:var(--text-dim);">Time allocations with automated collision prevention</p>
          </div>
          <button class="btn btn-secondary btn-sm" onclick="openCreateScheduleModal()">
            <i class="bi bi-calendar-plus"></i> Add Event Schedule
          </button>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>Schedule ID</th>
                  <th>Event Name</th>
                  <th>Venue & Location</th>
                  <th>Event Date</th>
                  <th>Time Slot</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="schedulesTableBody">
                <!-- Injected by JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAB 6: ORGANIZERS & CATEGORIES -->
      <!-- ============================================== -->
      <section class="tab-pane" id="tab-organizers-categories">
        <div style="display:grid; grid-template-columns: 3fr 2fr; gap:28px;">
          <!-- Organizers / Clubs -->
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <div>
                <h2 style="font-size:18px; font-weight:700; color:#fff;">Organizers & Student Clubs</h2>
                <p style="font-size:13px; color:var(--text-dim);">Host entities managing campus activities</p>
              </div>
              <button class="btn btn-primary btn-sm" onclick="openCreateOrganizerModal()">
                <i class="bi bi-plus-lg"></i> Add Club
              </button>
            </div>

            <div class="card" style="padding:0; overflow:hidden;">
              <div class="table-responsive">
                <table class="custom-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Club / Organizer</th>
                      <th>Department</th>
                      <th>Phone</th>
                      <th>Events Hosted</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="organizersTableBody">
                    <!-- Injected by JS -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Categories -->
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <div>
                <h2 style="font-size:18px; font-weight:700; color:#fff;">Event Categories</h2>
                <p style="font-size:13px; color:var(--text-dim);">Classification taxonomy</p>
              </div>
              <button class="btn btn-secondary btn-sm" onclick="openCreateCategoryModal()">
                <i class="bi bi-plus-lg"></i> Add Category
              </button>
            </div>

            <div id="categoriesGridContainer" style="display:flex; flex-direction:column; gap:16px;">
              <!-- Injected by JS -->
            </div>
          </div>
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAB 7: PUBLIC STUDENT PORTAL -->
      <!-- ============================================== -->
      <section class="tab-pane" id="tab-student-portal">
        <div class="public-banner">
          <div>
            <span class="badge badge-purple mb-2" style="background:rgba(255,255,255,0.2); color:#fff;">Woxsen University Campus</span>
            <h1>Discover & Enrol in Exciting Events</h1>
            <p>Explore hackathons, academic seminars, sports championships, and cultural celebrations. Enrol seamlessly and secure your ticket today.</p>
          </div>
          <div>
            <i class="bi bi-stars" style="font-size:70px; opacity:0.3;"></i>
          </div>
        </div>

        <div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
          <h2 style="font-size:20px; font-weight:700; color:#fff;">Upcoming Campus Events Open for Registration</h2>
          <span style="font-size:13px; color:var(--text-muted);">Real-time capacity tracking</span>
        </div>

        <div id="studentPortalEvents" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:24px;">
          <!-- Injected by JS -->
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAB 8: DBMS PBL LAB & ER DIAGRAM -->
      <!-- ============================================== -->
      <section class="tab-pane" id="tab-dbms-pbl">
        <!-- ER Diagram Showcase -->
        <div class="card" style="margin-bottom:28px;">
          <div class="card-header">
            <div>
              <h3 class="card-title"><i class="bi bi-diagram-3-fill text-indigo-400"></i> Relational Database Schema & ER Entity Model</h3>
              <p class="card-desc">Interactive representation of the 7 relational tables, primary keys (PK), foreign keys (FK), and 1:N cardinality relationships</p>
            </div>
            <a href="database/event-management.sql" download class="btn btn-secondary btn-sm">
              <i class="bi bi-download"></i> Download SQL Dump
            </a>
          </div>

          <div class="er-diagram-container">
            <div class="er-diagram-grid">
              
              <!-- 1. EVENT_CATEGORIES -->
              <div class="er-entity-card">
                <div class="er-entity-header" style="background:#dc2626; color:#fff;">
                  <span>EVENT_CATEGORIES</span>
                  <span style="font-size:11px; opacity:0.85;">1 : N (Events)</span>
                </div>
                <div class="er-entity-body">
                  <div class="er-attr-row">
                    <span class="er-pk">category_id (PK)</span>
                    <span class="er-type">INT AUTO</span>
                  </div>
                  <div class="er-attr-row">
                    <span>category_name</span>
                    <span class="er-type">VARCHAR(100) UNIQUE</span>
                  </div>
                  <div class="er-attr-row">
                    <span>description</span>
                    <span class="er-type">VARCHAR(255)</span>
                  </div>
                </div>
              </div>

              <!-- 2. ORGANIZERS -->
              <div class="er-entity-card">
                <div class="er-entity-header" style="background:#059669; color:#fff;">
                  <span>ORGANIZERS</span>
                  <span style="font-size:11px; opacity:0.85;">1 : N (Events)</span>
                </div>
                <div class="er-entity-body">
                  <div class="er-attr-row">
                    <span class="er-pk">organizer_id (PK)</span>
                    <span class="er-type">INT AUTO</span>
                  </div>
                  <div class="er-attr-row">
                    <span>organizer_name</span>
                    <span class="er-type">VARCHAR(100)</span>
                  </div>
                  <div class="er-attr-row">
                    <span>email</span>
                    <span class="er-type">VARCHAR(100) UNIQUE</span>
                  </div>
                  <div class="er-attr-row">
                    <span>phone</span>
                    <span class="er-type">VARCHAR(15)</span>
                  </div>
                  <div class="er-attr-row">
                    <span>department</span>
                    <span class="er-type">VARCHAR(100)</span>
                  </div>
                </div>
              </div>

              <!-- 3. VENUES -->
              <div class="er-entity-card">
                <div class="er-entity-header" style="background:#16a34a; color:#fff;">
                  <span>VENUES</span>
                  <span style="font-size:11px; opacity:0.85;">1 : N (Schedules)</span>
                </div>
                <div class="er-entity-body">
                  <div class="er-attr-row">
                    <span class="er-pk">venue_id (PK)</span>
                    <span class="er-type">INT AUTO</span>
                  </div>
                  <div class="er-attr-row">
                    <span>venue_name</span>
                    <span class="er-type">VARCHAR(100) UNIQUE</span>
                  </div>
                  <div class="er-attr-row">
                    <span>location</span>
                    <span class="er-type">VARCHAR(150)</span>
                  </div>
                  <div class="er-attr-row">
                    <span>capacity</span>
                    <span class="er-type">INT > 0</span>
                  </div>
                  <div class="er-attr-row">
                    <span>venue_type</span>
                    <span class="er-type">VARCHAR(50)</span>
                  </div>
                </div>
              </div>

              <!-- 4. EVENTS (Central Hub) -->
              <div class="er-entity-card" style="grid-column: span 1; border-color:var(--primary);">
                <div class="er-entity-header" style="background:#2563eb; color:#fff;">
                  <span>EVENTS (Central)</span>
                  <span style="font-size:11px; opacity:0.85;">Hub Entity</span>
                </div>
                <div class="er-entity-body">
                  <div class="er-attr-row">
                    <span class="er-pk">event_id (PK)</span>
                    <span class="er-type">INT AUTO</span>
                  </div>
                  <div class="er-attr-row">
                    <span>event_name</span>
                    <span class="er-type">VARCHAR(150)</span>
                  </div>
                  <div class="er-attr-row">
                    <span>description</span>
                    <span class="er-type">TEXT</span>
                  </div>
                  <div class="er-attr-row">
                    <span class="er-fk">category_id (FK)</span>
                    <span class="er-type">REF categories</span>
                  </div>
                  <div class="er-attr-row">
                    <span class="er-fk">organizer_id (FK)</span>
                    <span class="er-type">REF organizers</span>
                  </div>
                  <div class="er-attr-row">
                    <span>registration_fee</span>
                    <span class="er-type">DECIMAL(10,2)</span>
                  </div>
                  <div class="er-attr-row">
                    <span>max_participants</span>
                    <span class="er-type">INT > 0</span>
                  </div>
                  <div class="er-attr-row">
                    <span>status</span>
                    <span class="er-type">CHECK constraint</span>
                  </div>
                </div>
              </div>

              <!-- 5. EVENT_SCHEDULES -->
              <div class="er-entity-card">
                <div class="er-entity-header" style="background:#d97706; color:#fff;">
                  <span>EVENT_SCHEDULES</span>
                  <span style="font-size:11px; opacity:0.85;">N : 1 (Events & Venues)</span>
                </div>
                <div class="er-entity-body">
                  <div class="er-attr-row">
                    <span class="er-pk">schedule_id (PK)</span>
                    <span class="er-type">INT AUTO</span>
                  </div>
                  <div class="er-attr-row">
                    <span class="er-fk">event_id (FK)</span>
                    <span class="er-type">ON DELETE CASCADE</span>
                  </div>
                  <div class="er-attr-row">
                    <span class="er-fk">venue_id (FK)</span>
                    <span class="er-type">REF venues</span>
                  </div>
                  <div class="er-attr-row">
                    <span>event_date</span>
                    <span class="er-type">DATE</span>
                  </div>
                  <div class="er-attr-row">
                    <span>start_time</span>
                    <span class="er-type">TIME</span>
                  </div>
                  <div class="er-attr-row">
                    <span>end_time</span>
                    <span class="er-type">CHECK end > start</span>
                  </div>
                </div>
              </div>

              <!-- 6. PARTICIPANTS -->
              <div class="er-entity-card">
                <div class="er-entity-header" style="background:#059669; color:#fff;">
                  <span>PARTICIPANTS</span>
                  <span style="font-size:11px; opacity:0.85;">1 : N (Registrations)</span>
                </div>
                <div class="er-entity-body">
                  <div class="er-attr-row">
                    <span class="er-pk">participant_id (PK)</span>
                    <span class="er-type">INT AUTO</span>
                  </div>
                  <div class="er-attr-row">
                    <span>participant_name</span>
                    <span class="er-type">VARCHAR(100)</span>
                  </div>
                  <div class="er-attr-row">
                    <span>email</span>
                    <span class="er-type">VARCHAR(100) UNIQUE</span>
                  </div>
                  <div class="er-attr-row">
                    <span>phone</span>
                    <span class="er-type">VARCHAR(15)</span>
                  </div>
                  <div class="er-attr-row">
                    <span>college</span>
                    <span class="er-type">VARCHAR(150)</span>
                  </div>
                </div>
              </div>

              <!-- 7. REGISTRATIONS -->
              <div class="er-entity-card" style="grid-column: 2 / span 1;">
                <div class="er-entity-header" style="background:#c026d3; color:#fff;">
                  <span>REGISTRATIONS</span>
                  <span style="font-size:11px; opacity:0.85;">Associative Bridge</span>
                </div>
                <div class="er-entity-body">
                  <div class="er-attr-row">
                    <span class="er-pk">registration_id (PK)</span>
                    <span class="er-type">INT AUTO</span>
                  </div>
                  <div class="er-attr-row">
                    <span class="er-fk">event_id (FK)</span>
                    <span class="er-type">CASCADE</span>
                  </div>
                  <div class="er-attr-row">
                    <span class="er-fk">participant_id (FK)</span>
                    <span class="er-type">CASCADE</span>
                  </div>
                  <div class="er-attr-row">
                    <span>registration_date</span>
                    <span class="er-type">DATE</span>
                  </div>
                  <div class="er-attr-row">
                    <span>registration_status</span>
                    <span class="er-type">CHECK constraint</span>
                  </div>
                  <div class="er-attr-row">
                    <span>payment_status</span>
                    <span class="er-type">CHECK constraint</span>
                  </div>
                  <div class="er-attr-row" style="background:rgba(255,255,255,0.03);">
                    <span style="color:#f59e0b; font-size:11px;">UNIQUE(event_id, participant_id)</span>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- SQL Query Playground Section -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:28px;">
          <!-- Left: Preset PBL Queries -->
          <div>
            <div style="margin-bottom:16px;">
              <h3 style="font-size:18px; font-weight:700; color:#fff;">Essential DBMS PBL Academic Queries</h3>
              <p style="font-size:13px; color:var(--text-dim);">Click any query preset to load and execute it directly on the MariaDB engine.</p>
            </div>
            <div id="pblPresetQueriesList">
              <!-- Injected by JS -->
            </div>
          </div>

          <!-- Right: Interactive Query Runner -->
          <div>
            <div style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
              <h3 style="font-size:18px; font-weight:700; color:#fff;">Live MariaDB Query Console</h3>
              <span class="badge badge-emerald"><i class="bi bi-shield-check"></i> Read-Safe Playground</span>
            </div>

            <div class="sql-editor-wrap">
              <div class="sql-editor-toolbar">
                <span><i class="bi bi-terminal me-1 text-cyan-400"></i> SQL Input Console</span>
                <button class="btn btn-primary btn-sm" onclick="executeSql()">
                  <i class="bi bi-play-fill"></i> Execute SQL
                </button>
              </div>
              <textarea class="sql-textarea" id="sqlQueryInput" rows="6" placeholder="Write or select a SELECT SQL query here..."></textarea>
            </div>

            <div class="card" style="padding:16px;">
              <h4 style="font-size:14px; font-weight:600; color:#fff; margin-bottom:12px;">Query Execution Output</h4>
              <div id="sqlQueryResultArea">
                <div style="text-align:center; padding:30px; color:var(--text-dim); font-size:13px;">
                  Select a preset query on the left or type your custom SQL query above, then press <strong>Execute SQL</strong>.
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

    </div>
  </main>

  <!-- ============================================== -->
  <!-- MODALS -->
  <!-- ============================================== -->

  <!-- 1. Event Modal (Create / Edit) -->
  <div class="modal-overlay" id="eventModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="eventModalTitle">Create New Event</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="eventForm">
        <input type="hidden" id="eventFormId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Event Name *</label>
            <input type="text" class="form-control" id="eventNameInput" required placeholder="e.g. AI Innovation Summit 2026">
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="eventDescInput" rows="2" placeholder="Brief event overview, objectives, eligibility..."></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Category *</label>
              <select class="form-control" id="eventCategorySelect" required>
                <!-- Injected -->
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Organizing Club / Dept *</label>
              <select class="form-control" id="eventOrganizerSelect" required>
                <!-- Injected -->
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Registration Fee (₹) *</label>
              <input type="number" step="0.01" min="0" class="form-control" id="eventFeeInput" value="0.00" required>
            </div>
            <div class="form-group">
              <label class="form-label">Max Participants Capacity *</label>
              <input type="number" min="1" class="form-control" id="eventMaxInput" value="100" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Event Status *</label>
            <select class="form-control" id="eventStatusSelect">
              <option value="Upcoming">Upcoming</option>
              <option value="Ongoing">Ongoing</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>

          <div style="margin-top:10px; padding:14px; background:rgba(0,0,0,0.25); border-radius:var(--radius-md); border:1px solid var(--border-color);">
            <div style="font-weight:600; font-size:13px; color:var(--accent-cyan); margin-bottom:10px;">
              <i class="bi bi-clock me-1"></i> Venue & Schedule Configuration (Optional)
            </div>
            <div class="form-group mb-2">
              <label class="form-label">Assigned Venue</label>
              <select class="form-control" id="eventVenueSelect">
                <!-- Injected -->
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" id="eventDateInput">
              </div>
              <div class="form-group">
                <label class="form-label">Start Time</label>
                <input type="time" class="form-control" id="eventStartTimeInput">
              </div>
              <div class="form-group">
                <label class="form-label">End Time</label>
                <input type="time" class="form-control" id="eventEndTimeInput">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Event</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 2. Event Details Modal -->
  <div class="modal-overlay" id="eventDetailsModal">
    <div class="modal-content" style="max-width:760px;">
      <div class="modal-header">
        <h3 class="modal-title" id="eventDetailsTitle">Event Details</h3>
        <button class="modal-close">&times;</button>
      </div>
      <div class="modal-body" id="eventDetailsBody">
        <!-- Injected by JS -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>

  <!-- 3. Registration Modal (Admin) -->
  <div class="modal-overlay" id="registrationModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Register Participant to Event</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="registrationForm">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Target Event *</label>
            <select class="form-control" id="regEventSelect" required>
              <!-- Injected -->
            </select>
          </div>

          <div style="display:flex; gap:16px; margin:8px 0; font-size:13px;">
            <label style="cursor:pointer; display:flex; align-items:center; gap:6px;">
              <input type="radio" name="participantMode" value="existing" checked onchange="toggleParticipantInputMode('existing')"> Select Existing Student
            </label>
            <label style="cursor:pointer; display:flex; align-items:center; gap:6px;">
              <input type="radio" name="participantMode" value="new" onchange="toggleParticipantInputMode('new')"> Register New Student
            </label>
          </div>

          <!-- Existing Participant Option -->
          <div id="existingParticipantSection">
            <div class="form-group">
              <label class="form-label">Select Registered Student *</label>
              <select class="form-control" id="regParticipantSelect">
                <!-- Injected -->
              </select>
            </div>
          </div>

          <!-- New Participant Option -->
          <div id="newParticipantSection" style="display:none; background:rgba(0,0,0,0.2); padding:14px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
            <div class="form-group mb-2">
              <label class="form-label">Full Name *</label>
              <input type="text" class="form-control" id="regNewName" placeholder="e.g. Aditi Rao">
            </div>
            <div class="form-row mb-2">
              <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control" id="regNewEmail" placeholder="aditi.rao@gmail.com">
              </div>
              <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" id="regNewPhone" placeholder="9876543210">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">College / University *</label>
              <input type="text" class="form-control" id="regNewCollege" value="Woxsen University">
            </div>
          </div>

          <div class="form-row" style="margin-top:10px;">
            <div class="form-group">
              <label class="form-label">Registration Status *</label>
              <select class="form-control" id="regStatusSelect">
                <option value="Confirmed">Confirmed</option>
                <option value="Pending">Pending</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Payment Status *</label>
              <select class="form-control" id="regPaymentSelect">
                <option value="Paid">Paid</option>
                <option value="Pending">Pending</option>
                <option value="Not Required">Not Required</option>
                <option value="Refunded">Refunded</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Complete Registration</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 4. Participant Modal -->
  <div class="modal-overlay" id="participantModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="participantModalTitle">Add Student / Participant</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="participantForm">
        <input type="hidden" id="participantFormId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" class="form-control" id="partNameInput" required placeholder="e.g. Siddharth Joshi">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Email Address *</label>
              <input type="email" class="form-control" id="partEmailInput" required placeholder="sid.j@gmail.com">
            </div>
            <div class="form-group">
              <label class="form-label">Phone Number</label>
              <input type="text" class="form-control" id="partPhoneInput" placeholder="9876543210">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">College / Institution *</label>
            <input type="text" class="form-control" id="partCollegeInput" required value="Woxsen University">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Participant</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 5. Venue Modal -->
  <div class="modal-overlay" id="venueModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="venueModalTitle">Add Campus Venue</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="venueForm">
        <input type="hidden" id="venueFormId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Venue Name *</label>
            <input type="text" class="form-control" id="venueNameInput" required placeholder="e.g. Apple Inc. Lab 3">
          </div>
          <div class="form-group">
            <label class="form-label">Location / Building *</label>
            <input type="text" class="form-control" id="venueLocationInput" required placeholder="e.g. Technology Block, 2nd Floor">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Max Seating Capacity *</label>
              <input type="number" min="1" class="form-control" id="venueCapacityInput" required placeholder="150">
            </div>
            <div class="form-group">
              <label class="form-label">Venue Type *</label>
              <select class="form-control" id="venueTypeInput" required>
                <option value="Auditorium">Auditorium</option>
                <option value="Seminar Hall">Seminar Hall</option>
                <option value="Computer Lab">Computer Lab</option>
                <option value="Open Ground">Open Ground</option>
                <option value="Classroom">Classroom</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Venue</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 6. Schedule Modal -->
  <div class="modal-overlay" id="scheduleModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Schedule Event Session</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="scheduleForm">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Event *</label>
            <select class="form-control" id="schEventSelect" required></select>
          </div>
          <div class="form-group">
            <label class="form-label">Venue *</label>
            <select class="form-control" id="schVenueSelect" required></select>
          </div>
          <div class="form-group">
            <label class="form-label">Date *</label>
            <input type="date" class="form-control" id="schDateInput" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Start Time *</label>
              <input type="time" class="form-control" id="schStartTimeInput" required>
            </div>
            <div class="form-group">
              <label class="form-label">End Time *</label>
              <input type="time" class="form-control" id="schEndTimeInput" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Book Schedule</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 7. Organizer Modal -->
  <div class="modal-overlay" id="organizerModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="organizerModalTitle">Add Organizing Club</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="organizerForm">
        <input type="hidden" id="organizerFormId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Club / Organizer Name *</label>
            <input type="text" class="form-control" id="orgNameInput" required placeholder="e.g. Robotics Club">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Official Email *</label>
              <input type="email" class="form-control" id="orgEmailInput" required placeholder="robotics@woxsen.edu.in">
            </div>
            <div class="form-group">
              <label class="form-label">Contact Phone</label>
              <input type="text" class="form-control" id="orgPhoneInput" placeholder="9876500000">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Associated Department</label>
            <input type="text" class="form-control" id="orgDeptInput" placeholder="e.g. Mechatronics Department">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Organizer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 8. Category Modal -->
  <div class="modal-overlay" id="categoryModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="categoryModalTitle">Event Category</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="categoryForm">
        <input type="hidden" id="categoryFormId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Category Name *</label>
            <input type="text" class="form-control" id="catNameInput" required placeholder="e.g. Symposium">
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="catDescInput" rows="3" placeholder="Category definition..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Category</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 9. Student Direct Self-Registration Modal -->
  <div class="modal-overlay" id="studentRegisterModal">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h3 class="modal-title"><i class="bi bi-ticket-detailed text-indigo-400"></i> Event Registration</h3>
          <div style="font-size:13px; color:var(--accent-cyan); font-weight:600; margin-top:3px;" id="studentRegEventTitle">Event Title</div>
          <div style="font-size:12px; color:var(--text-dim);" id="studentRegEventFee">Registration Fee</div>
        </div>
        <button class="modal-close">&times;</button>
      </div>
      <form id="studentRegisterForm">
        <input type="hidden" id="studentRegEventId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Your Full Name *</label>
            <input type="text" class="form-control" id="studentNameInput" required placeholder="e.g. Ananey Sharma">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">College Email *</label>
              <input type="email" class="form-control" id="studentEmailInput" required placeholder="your.name@woxsen.edu.in">
            </div>
            <div class="form-group">
              <label class="form-label">Contact Number *</label>
              <input type="tel" class="form-control" id="studentPhoneInput" required placeholder="9876543210">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">College / University Name *</label>
            <input type="text" class="form-control" id="studentCollegeInput" required value="Woxsen University">
          </div>
          <div style="font-size:12px; color:var(--text-muted); background:rgba(255,255,255,0.03); padding:10px; border-radius:var(--radius-sm);">
            <i class="bi bi-info-circle me-1 text-cyan-400"></i> By registering, your seat will be reserved and an automated record will be created in the database.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm My Registration</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Main JavaScript App Engine -->
  <script src="assets/js/app.js"></script>
</body>
</html>
