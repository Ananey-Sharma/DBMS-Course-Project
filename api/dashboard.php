<?php
require_once __DIR__ . '/../config/db.php';

try {
    // 1. Total statistics
    $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    $totalParticipants = (int)$pdo->query("SELECT COUNT(*) FROM participants")->fetchColumn();
    $totalVenues = (int)$pdo->query("SELECT COUNT(*) FROM venues")->fetchColumn();
    $totalOrganizers = (int)$pdo->query("SELECT COUNT(*) FROM organizers")->fetchColumn();

    // Total revenue from Paid registrations
    $revenueStmt = $pdo->query("
        SELECT COALESCE(SUM(e.registration_fee), 0) as total_revenue
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.payment_status = 'Paid'
    ");
    $totalRevenue = (float)$revenueStmt->fetchColumn();

    // 2. Events by Category (for Doughnut chart)
    $categoryData = $pdo->query("
        SELECT c.category_name, COUNT(e.event_id) as event_count
        FROM event_categories c
        LEFT JOIN events e ON c.category_id = e.category_id
        GROUP BY c.category_id, c.category_name
        ORDER BY event_count DESC
    ")->fetchAll();

    // 3. Registrations by Status (for Bar/Pie chart)
    $registrationStatusData = $pdo->query("
        SELECT registration_status, COUNT(*) as count
        FROM registrations
        GROUP BY registration_status
    ")->fetchAll();

    // 4. Payment Status Breakdown
    $paymentStatusData = $pdo->query("
        SELECT payment_status, COUNT(*) as count
        FROM registrations
        GROUP BY payment_status
    ")->fetchAll();

    // 5. Recent Registrations
    $recentRegistrations = $pdo->query("
        SELECT 
            r.registration_id,
            r.registration_date,
            r.registration_status,
            r.payment_status,
            p.participant_name,
            p.email as participant_email,
            p.college,
            e.event_name,
            e.registration_fee
        FROM registrations r
        JOIN participants p ON r.participant_id = p.participant_id
        JOIN events e ON r.event_id = e.event_id
        ORDER BY r.registration_id DESC
        LIMIT 6
    ")->fetchAll();

    // 6. Upcoming Schedules
    $upcomingSchedules = $pdo->query("
        SELECT 
            s.schedule_id,
            s.event_date,
            s.start_time,
            s.end_time,
            e.event_id,
            e.event_name,
            e.status as event_status,
            e.registration_fee,
            c.category_name,
            v.venue_name,
            v.location as venue_location,
            o.organizer_name
        FROM event_schedules s
        JOIN events e ON s.event_id = e.event_id
        JOIN venues v ON s.venue_id = v.venue_id
        JOIN event_categories c ON e.category_id = c.category_id
        JOIN organizers o ON e.organizer_id = o.organizer_id
        ORDER BY s.event_date ASC, s.start_time ASC
        LIMIT 5
    ")->fetchAll();

    sendJsonResponse([
        'success' => true,
        'data' => [
            'metrics' => [
                'total_events' => $totalEvents,
                'total_registrations' => $totalRegistrations,
                'total_participants' => $totalParticipants,
                'total_venues' => $totalVenues,
                'total_organizers' => $totalOrganizers,
                'total_revenue' => $totalRevenue
            ],
            'categories_chart' => $categoryData,
            'registration_status_chart' => $registrationStatusData,
            'payment_status_chart' => $paymentStatusData,
            'recent_registrations' => $recentRegistrations,
            'upcoming_schedules' => $upcomingSchedules
        ]
    ]);
} catch (Exception $e) {
    sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
