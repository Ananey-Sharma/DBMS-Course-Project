<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Engine | Relational DBMS Management Portal</title>
  
  <!-- Architectural & Technical Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
  
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
      <div class="brand-icon">DB</div>
      <div class="brand-info">
        <h2>EVENT ENGINE</h2>
        <span>DBMS RELATIONAL SYSTEM</span>
      </div>
    </div>

    <div class="sidebar-nav">
      <div class="nav-section-title">Core Modules</div>
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

      <div class="nav-section-title">Portals & Query Console</div>
      <a class="nav-item" data-tab="student-portal">
        <i class="bi bi-globe"></i>
        <span>Student Portal</span>
        <span class="nav-badge">Public</span>
      </a>
      <a class="nav-item" data-tab="dbms-pbl">
        <i class="bi bi-diagram-3"></i>
        <span>Schema & SQL Lab</span>
        <span class="nav-badge">PBL</span>
      </a>
    </div>

    <div class="sidebar-footer">
      <div class="db-status-badge">
        <span class="status-dot"></span>
        <div style="flex:1;">
          <strong style="color:var(--text-main);">MariaDB 10.4</strong>
          <div style="font-size:10.5px; color:var(--text-dim);">Database: event_management</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content Wrapper -->
  <main class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title" id="currentViewTitle">System Dashboard & Analytics</h1>
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
        <!-- Top Metrics -->
        <div class="metrics-grid">
          <div class="metric-card">
            <div class="metric-top">
              <span class="metric-title">Total Events</span>
              <div class="metric-icon-wrap"><i class="bi bi-calendar3"></i></div>
            </div>
            <div class="metric-value" id="metricTotalEvents">0</div>
            <div class="metric-footer"><i class="bi bi-check2"></i> Active database records</div>
          </div>

          <div class="metric-card">
            <div class="metric-top">
              <span class="metric-title">Total Registrations</span>
              <div class="metric-icon-wrap"><i class="bi bi-card-checklist"></i></div>
            </div>
            <div class="metric-value" id="metricTotalRegistrations">0</div>
            <div class="metric-footer">Across all events</div>
          </div>

          <div class="metric-card">
            <div class="metric-top">
              <span class="metric-title">Enrolled Participants</span>
              <div class="metric-icon-wrap"><i class="bi bi-people"></i></div>
            </div>
            <div class="metric-value" id="metricTotalParticipants">0</div>
            <div class="metric-footer">Registered students</div>
          </div>

          <div class="metric-card">
            <div class="metric-top">
              <span class="metric-title">Venues Configured</span>
              <div class="metric-icon-wrap"><i class="bi bi-building"></i></div>
            </div>
            <div class="metric-value" id="metricTotalVenues">0</div>
            <div class="metric-footer">Campus facilities</div>
          </div>

          <div class="metric-card">
            <div class="metric-top">
              <span class="metric-title">Revenue Collected</span>
              <div class="metric-icon-wrap"><i class="bi bi-currency-rupee"></i></div>
            </div>
            <div class="metric-value" id="metricTotalRevenue">₹0</div>
            <div class="metric-footer">Paid registrations</div>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
          <div class="card">
            <div class="card-header">
              <div>
                <h3 class="card-title"><i class="bi bi-pie-chart"></i> Category Distribution</h3>
                <p class="card-desc">Event breakdown across category entities</p>
              </div>
            </div>
            <div class="chart-container">
              <canvas id="categoryDistributionChart"></canvas>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div>
                <h3 class="card-title"><i class="bi bi-bar-chart"></i> Registration & Payment Metrics</h3>
                <p class="card-desc">Attendance confirmations and fee status breakdown</p>
              </div>
            </div>
            <div class="chart-container">
              <canvas id="registrationStatusChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Dashboard Bottom Feed -->
        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
          <div class="card" style="padding:0; overflow:hidden;">
            <div class="card-header" style="padding:16px 20px; border-bottom:1px solid var(--border-subtle); margin:0;">
              <div>
                <h3 class="card-title"><i class="bi bi-clock-history"></i> Recent Enrolment Log</h3>
                <p class="card-desc">Latest student event registrations</p>
              </div>
              <button class="btn btn-secondary btn-sm" onclick="switchTab('registrations')">View Log</button>
            </div>
            <div class="table-responsive" style="border:none;">
              <table class="custom-table">
                <thead>
                  <tr>
                    <th>Participant</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Status</th>
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
                <h3 class="card-title"><i class="bi bi-calendar2-week"></i> Schedule Timeline</h3>
                <p class="card-desc">Upcoming event allocations</p>
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
            <input type="text" class="search-input" id="eventSearchInput" placeholder="Filter events by title, description or organizer..." oninput="loadEvents()">
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
            <div style="display:flex; gap:2px; background:var(--bg-surface); padding:2px; border:1px solid var(--border-subtle); border-radius:var(--radius-sm);">
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
            <input type="text" class="search-input" id="regSearchInput" placeholder="Search by participant name, email, college or event..." oninput="loadRegistrations()">
          </div>
          <div class="filter-group">
            <select class="select-custom" id="regStatusFilter" onchange="loadRegistrations()">
              <option value="">All Registration Statuses</option>
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
          <div class="table-responsive" style="border:none;">
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
            <input type="text" class="search-input" id="participantSearchInput" placeholder="Search by student name, email, phone or college..." oninput="loadParticipants()">
          </div>
          <button class="btn btn-primary btn-sm" onclick="openCreateParticipantModal()">
            <i class="bi bi-person-plus"></i> Add Student
          </button>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
          <div class="table-responsive" style="border:none;">
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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h2 style="font-family:var(--font-display); font-size:16px; font-weight:700; color:var(--text-main); text-transform:uppercase;">Campus Venues & Facilities</h2>
            <p style="font-size:12px; color:var(--text-dim);">Auditoriums, seminar halls, and computer labs</p>
          </div>
          <button class="btn btn-primary btn-sm" onclick="openCreateVenueModal()">
            <i class="bi bi-plus-lg"></i> Add Venue
          </button>
        </div>

        <div id="venuesCardsContainer" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:16px; margin-bottom:32px;">
          <!-- Injected by JS -->
        </div>

        <!-- Schedules Section -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div>
            <h2 style="font-family:var(--font-display); font-size:16px; font-weight:700; color:var(--text-main); text-transform:uppercase;">Event Schedule Allocations</h2>
            <p style="font-size:12px; color:var(--text-dim);">Time slots with automated venue overlap conflict checks</p>
          </div>
          <button class="btn btn-secondary btn-sm" onclick="openCreateScheduleModal()">
            <i class="bi bi-calendar-plus"></i> Add Schedule
          </button>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
          <div class="table-responsive" style="border:none;">
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
        <div style="display:grid; grid-template-columns: 3fr 2fr; gap:24px;">
          <!-- Organizers / Clubs -->
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
              <div>
                <h2 style="font-family:var(--font-display); font-size:16px; font-weight:700; color:var(--text-main); text-transform:uppercase;">Organizing Entities & Clubs</h2>
                <p style="font-size:12px; color:var(--text-dim);">Host entities managing campus activities</p>
              </div>
              <button class="btn btn-primary btn-sm" onclick="openCreateOrganizerModal()">
                <i class="bi bi-plus-lg"></i> Add Club
              </button>
            </div>

            <div class="card" style="padding:0; overflow:hidden;">
              <div class="table-responsive" style="border:none;">
                <table class="custom-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Organizer Name</th>
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
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
              <div>
                <h2 style="font-family:var(--font-display); font-size:16px; font-weight:700; color:var(--text-main); text-transform:uppercase;">Event Categories</h2>
                <p style="font-size:12px; color:var(--text-dim);">Classification taxonomy</p>
              </div>
              <button class="btn btn-secondary btn-sm" onclick="openCreateCategoryModal()">
                <i class="bi bi-plus-lg"></i> Add Category
              </button>
            </div>

            <div id="categoriesGridContainer" style="display:flex; flex-direction:column; gap:12px;">
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
            <span class="badge badge-gray mb-2">Public Portal</span>
            <h1>Campus Events Catalogue</h1>
            <p>Select an upcoming event to view availability and submit your registration directly to the database.</p>
          </div>
        </div>

        <div style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
          <h2 style="font-family:var(--font-display); font-size:16px; font-weight:700; color:var(--text-main); text-transform:uppercase;">Upcoming Campus Events</h2>
          <span style="font-family:var(--font-mono); font-size:11px; color:var(--text-dim);">Real-time capacity tracking</span>
        </div>

        <div id="studentPortalEvents" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:18px;">
          <!-- Injected by JS -->
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAB 8: DBMS PBL LAB & ER DIAGRAM -->
      <!-- ============================================== -->
      <section class="tab-pane" id="tab-dbms-pbl">
        <!-- ER Diagram Showcase -->
        <div class="card" style="margin-bottom:24px;">
          <div class="card-header">
            <div>
              <h3 class="card-title"><i class="bi bi-diagram-3"></i> Relational Database Schema & Entity Model</h3>
              <p class="card-desc">Mapping of 7 relational tables, primary keys (PK), foreign keys (FK), and cardinality rules</p>
            </div>
            <a href="database/event-management.sql" download class="btn btn-secondary btn-sm">
              <i class="bi bi-download"></i> Export SQL Schema
            </a>
          </div>

          <div class="er-diagram-container">
            <div class="er-diagram-grid">
              
              <!-- 1. EVENT_CATEGORIES -->
              <div class="er-entity-card">
                <div class="er-entity-header">
                  <span>EVENT_CATEGORIES</span>
                  <span style="font-size:10px; color:var(--text-dim);">1 : N (Events)</span>
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
                <div class="er-entity-header">
                  <span>ORGANIZERS</span>
                  <span style="font-size:10px; color:var(--text-dim);">1 : N (Events)</span>
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
                <div class="er-entity-header">
                  <span>VENUES</span>
                  <span style="font-size:10px; color:var(--text-dim);">1 : N (Schedules)</span>
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

              <!-- 4. EVENTS (Central) -->
              <div class="er-entity-card" style="border-color:var(--border-strong);">
                <div class="er-entity-header" style="background:var(--bg-surface-3);">
                  <span>EVENTS (Central Entity)</span>
                  <span style="font-size:10px; color:var(--text-dim);">Hub</span>
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
                    <span class="er-type">CHECK status</span>
                  </div>
                </div>
              </div>

              <!-- 5. EVENT_SCHEDULES -->
              <div class="er-entity-card">
                <div class="er-entity-header">
                  <span>EVENT_SCHEDULES</span>
                  <span style="font-size:10px; color:var(--text-dim);">N : 1 (Events & Venues)</span>
                </div>
                <div class="er-entity-body">
                  <div class="er-attr-row">
                    <span class="er-pk">schedule_id (PK)</span>
                    <span class="er-type">INT AUTO</span>
                  </div>
                  <div class="er-attr-row">
                    <span class="er-fk">event_id (FK)</span>
                    <span class="er-type">CASCADE</span>
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
                <div class="er-entity-header">
                  <span>PARTICIPANTS</span>
                  <span style="font-size:10px; color:var(--text-dim);">1 : N (Registrations)</span>
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
                <div class="er-entity-header">
                  <span>REGISTRATIONS</span>
                  <span style="font-size:10px; color:var(--text-dim);">Associative Bridge</span>
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
                    <span class="er-type">CHECK status</span>
                  </div>
                  <div class="er-attr-row">
                    <span>payment_status</span>
                    <span class="er-type">CHECK payment</span>
                  </div>
                  <div class="er-attr-row" style="background:var(--bg-surface-2);">
                    <span style="color:var(--text-muted); font-size:10px;">UNIQUE(event_id, participant_id)</span>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- SQL Query Playground Section -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
          <!-- Left: Preset PBL Queries -->
          <div>
            <div style="margin-bottom:14px;">
              <h3 style="font-family:var(--font-display); font-size:16px; font-weight:700; color:var(--text-main); text-transform:uppercase;">Academic SQL Query Presets</h3>
              <p style="font-size:12px; color:var(--text-dim);">Select a query preset to populate and execute on MariaDB.</p>
            </div>
            <div id="pblPresetQueriesList">
              <!-- Injected by JS -->
            </div>
          </div>

          <!-- Right: Interactive Query Runner -->
          <div>
            <div style="margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
              <h3 style="font-family:var(--font-display); font-size:16px; font-weight:700; color:var(--text-main); text-transform:uppercase;">MariaDB Execution Console</h3>
              <span class="badge badge-gray">Read-Only Safety</span>
            </div>

            <div class="sql-editor-wrap">
              <div class="sql-editor-toolbar">
                <span><i class="bi bi-terminal me-1"></i> Query Buffer</span>
                <button class="btn btn-primary btn-sm" onclick="executeSql()">
                  <i class="bi bi-play-fill"></i> Run Query
                </button>
              </div>
              <textarea class="sql-textarea" id="sqlQueryInput" rows="6" placeholder="Write or select a SELECT SQL query here..."></textarea>
            </div>

            <div class="card" style="padding:14px;">
              <h4 style="font-family:var(--font-mono); font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:10px;">Execution Result</h4>
              <div id="sqlQueryResultArea">
                <div style="text-align:center; padding:24px; color:var(--text-dim); font-size:12.5px;">
                  Select a preset query on the left or enter a custom SELECT query above, then click <strong>Run Query</strong>.
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
        <h3 class="modal-title" id="eventModalTitle">Create Event</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="eventForm">
        <input type="hidden" id="eventFormId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Event Title *</label>
            <input type="text" class="form-control" id="eventNameInput" required placeholder="AI Innovation Workshop 2026">
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="eventDescInput" rows="2" placeholder="Brief event overview..."></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Category *</label>
              <select class="form-control" id="eventCategorySelect" required></select>
            </div>
            <div class="form-group">
              <label class="form-label">Organizing Entity *</label>
              <select class="form-control" id="eventOrganizerSelect" required></select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Registration Fee (₹) *</label>
              <input type="number" step="0.01" min="0" class="form-control" id="eventFeeInput" value="0.00" required>
            </div>
            <div class="form-group">
              <label class="form-label">Max Capacity *</label>
              <input type="number" min="1" class="form-control" id="eventMaxInput" value="100" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Status *</label>
            <select class="form-control" id="eventStatusSelect">
              <option value="Upcoming">Upcoming</option>
              <option value="Ongoing">Ongoing</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>

          <div style="margin-top:8px; padding:12px; background:var(--bg-surface-2); border:1px solid var(--border-subtle); border-radius:var(--radius-sm);">
            <div style="font-family:var(--font-mono); font-weight:700; font-size:11px; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">
              Venue Allocation & Timing (Optional)
            </div>
            <div class="form-group mb-2">
              <label class="form-label">Assigned Venue</label>
              <select class="form-control" id="eventVenueSelect"></select>
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
    <div class="modal-content" style="max-width:720px;">
      <div class="modal-header">
        <h3 class="modal-title" id="eventDetailsTitle">Event Specification</h3>
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
        <h3 class="modal-title">Register Participant</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="registrationForm">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Target Event *</label>
            <select class="form-control" id="regEventSelect" required></select>
          </div>

          <div style="display:flex; gap:14px; margin:6px 0; font-size:12.5px; font-family:var(--font-mono);">
            <label style="cursor:pointer; display:flex; align-items:center; gap:6px;">
              <input type="radio" name="participantMode" value="existing" checked onchange="toggleParticipantInputMode('existing')"> Existing Student
            </label>
            <label style="cursor:pointer; display:flex; align-items:center; gap:6px;">
              <input type="radio" name="participantMode" value="new" onchange="toggleParticipantInputMode('new')"> New Student
            </label>
          </div>

          <!-- Existing Participant Option -->
          <div id="existingParticipantSection">
            <div class="form-group">
              <label class="form-label">Student Record *</label>
              <select class="form-control" id="regParticipantSelect"></select>
            </div>
          </div>

          <!-- New Participant Option -->
          <div id="newParticipantSection" style="display:none; background:var(--bg-surface-2); padding:12px; border-radius:var(--radius-sm); border:1px solid var(--border-subtle);">
            <div class="form-group mb-2">
              <label class="form-label">Full Name *</label>
              <input type="text" class="form-control" id="regNewName" placeholder="Aditi Rao">
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
              <label class="form-label">College / Institution *</label>
              <input type="text" class="form-control" id="regNewCollege" value="Woxsen University">
            </div>
          </div>

          <div class="form-row" style="margin-top:8px;">
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
        <h3 class="modal-title" id="participantModalTitle">Add Student Record</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="participantForm">
        <input type="hidden" id="participantFormId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" class="form-control" id="partNameInput" required placeholder="Siddharth Joshi">
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
        <h3 class="modal-title" id="venueModalTitle">Add Venue</h3>
        <button class="modal-close">&times;</button>
      </div>
      <form id="venueForm">
        <input type="hidden" id="venueFormId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Venue Name *</label>
            <input type="text" class="form-control" id="venueNameInput" required placeholder="Main Auditorium">
          </div>
          <div class="form-group">
            <label class="form-label">Location / Block *</label>
            <input type="text" class="form-control" id="venueLocationInput" required placeholder="Academic Block A">
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
        <h3 class="modal-title">Schedule Event</h3>
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
            <input type="text" class="form-control" id="orgNameInput" required placeholder="Tech Club">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Official Email *</label>
              <input type="email" class="form-control" id="orgEmailInput" required placeholder="techclub@woxsen.edu.in">
            </div>
            <div class="form-group">
              <label class="form-label">Contact Phone</label>
              <input type="text" class="form-control" id="orgPhoneInput" placeholder="9876500000">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Associated Department</label>
            <input type="text" class="form-control" id="orgDeptInput" placeholder="Computer Science">
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
            <input type="text" class="form-control" id="catNameInput" required placeholder="Workshop">
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
          <h3 class="modal-title">Event Registration</h3>
          <div style="font-size:12.5px; color:var(--text-main); font-weight:600; margin-top:2px;" id="studentRegEventTitle">Event Title</div>
          <div style="font-family:var(--font-mono); font-size:11px; color:var(--text-dim);" id="studentRegEventFee">Registration Fee</div>
        </div>
        <button class="modal-close">&times;</button>
      </div>
      <form id="studentRegisterForm">
        <input type="hidden" id="studentRegEventId">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" class="form-control" id="studentNameInput" required placeholder="Ananey Sharma">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">College Email *</label>
              <input type="email" class="form-control" id="studentEmailInput" required placeholder="ananey.sharma@woxsen.edu.in">
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm Registration</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Main JavaScript App Engine -->
  <script src="assets/js/app.js"></script>
</body>
</html>
