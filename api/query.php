<?php
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Return list of preset PBL queries
    $presets = [
        [
            'id' => 'q1_multi_join',
            'title' => '1. Multi-Table Master Join (5 Tables)',
            'description' => 'Retrieves complete event profile joining Events, Categories, Organizers, Schedules, and Venues with aggregate confirmed registrations.',
            'concept' => 'INNER JOIN, LEFT JOIN, Aggregation (COUNT), Aliasing',
            'sql' => "SELECT 
    e.event_id,
    e.event_name,
    c.category_name,
    o.organizer_name,
    o.department,
    v.venue_name,
    v.location as venue_location,
    s.event_date,
    s.start_time,
    s.end_time,
    e.registration_fee,
    e.max_participants,
    COUNT(r.registration_id) AS total_confirmed_participants
FROM events e
JOIN event_categories c ON e.category_id = c.category_id
JOIN organizers o ON e.organizer_id = o.organizer_id
LEFT JOIN event_schedules s ON e.event_id = s.event_id
LEFT JOIN venues v ON s.venue_id = v.venue_id
LEFT JOIN registrations r ON e.event_id = r.event_id AND r.registration_status = 'Confirmed'
GROUP BY e.event_id, c.category_name, o.organizer_name, o.department, v.venue_name, v.location, s.event_date, s.start_time, s.end_time, e.registration_fee, e.max_participants
ORDER BY s.event_date ASC;"
        ],
        [
            'id' => 'q2_organizer_summary',
            'title' => '2. Organizer Analytics & Revenue Report',
            'description' => 'Aggregates each organizer\'s total hosted events, registered participants, and total paid revenue collected.',
            'concept' => 'GROUP BY, SUM, COUNT, COALESCE, Multi-table JOIN',
            'sql' => "SELECT 
    o.organizer_id,
    o.organizer_name,
    o.department,
    COUNT(DISTINCT e.event_id) AS total_events_hosted,
    COUNT(r.registration_id) AS total_registrations,
    COALESCE(SUM(CASE WHEN r.payment_status = 'Paid' THEN e.registration_fee ELSE 0 END), 0) AS total_revenue_collected
FROM organizers o
LEFT JOIN events e ON o.organizer_id = e.organizer_id
LEFT JOIN registrations r ON e.event_id = r.event_id
GROUP BY o.organizer_id, o.organizer_name, o.department
ORDER BY total_revenue_collected DESC, total_events_hosted DESC;"
        ],
        [
            'id' => 'q3_venue_utilization',
            'title' => '3. Venue Capacity vs Event Occupancy',
            'description' => 'Computes venue capacity utilization percentage for scheduled events to analyze space efficiency.',
            'concept' => 'Mathematical Expressions, ROUND, Nested Joins, Integrity Constraints',
            'sql' => "SELECT 
    v.venue_name,
    v.venue_type,
    v.capacity AS venue_capacity,
    e.event_name,
    e.max_participants AS event_target_capacity,
    COUNT(r.registration_id) AS current_attendees,
    ROUND((COUNT(r.registration_id) / e.max_participants) * 100, 2) AS fill_rate_percentage,
    ROUND((e.max_participants / v.capacity) * 100, 2) AS venue_occupancy_percentage
FROM event_schedules s
JOIN venues v ON s.venue_id = v.venue_id
JOIN events e ON s.event_id = e.event_id
LEFT JOIN registrations r ON e.event_id = r.event_id AND r.registration_status = 'Confirmed'
GROUP BY v.venue_name, v.venue_type, v.capacity, e.event_name, e.max_participants;"
        ],
        [
            'id' => 'q4_subquery_above_avg',
            'title' => '4. Above-Average Popular Events (Subquery)',
            'description' => 'Finds all events whose confirmed participant count is strictly higher than or equal to the average registrations across all events.',
            'concept' => 'Correlated / Scalar Subquery, HAVING, AVG aggregate',
            'sql' => "SELECT 
    e.event_id,
    e.event_name,
    c.category_name,
    COUNT(r.registration_id) AS confirmed_participants
FROM events e
JOIN event_categories c ON e.category_id = c.category_id
LEFT JOIN registrations r ON e.event_id = r.event_id AND r.registration_status = 'Confirmed'
GROUP BY e.event_id, e.event_name, c.category_name
HAVING COUNT(r.registration_id) >= (
    SELECT AVG(reg_count)
    FROM (
        SELECT COUNT(registration_id) AS reg_count
        FROM registrations
        WHERE registration_status = 'Confirmed'
        GROUP BY event_id
    ) AS avg_table
)
ORDER BY confirmed_participants DESC;"
        ],
        [
            'id' => 'q5_category_revenue',
            'title' => '5. Category Revenue & Average Ticket Price',
            'description' => 'Evaluates performance by event category: average ticket fee, max fee, and total actual paid revenue.',
            'concept' => 'GROUP BY, AVG, MAX, SUM with Conditional Logic',
            'sql' => "SELECT 
    c.category_name,
    COUNT(DISTINCT e.event_id) AS total_events,
    ROUND(AVG(e.registration_fee), 2) AS avg_ticket_fee,
    MAX(e.registration_fee) AS max_ticket_fee,
    COALESCE(SUM(CASE WHEN r.payment_status = 'Paid' THEN e.registration_fee ELSE 0 END), 0) AS total_collected_revenue
FROM event_categories c
LEFT JOIN events e ON c.category_id = e.category_id
LEFT JOIN registrations r ON e.event_id = r.event_id
GROUP BY c.category_id, c.category_name
ORDER BY total_collected_revenue DESC;"
        ],
        [
            'id' => 'q6_participant_colleges',
            'title' => '6. Participant Demographic & Institutional Reach',
            'description' => 'Aggregates participant distribution by university/college, including total enrolled events and paid fees.',
            'concept' => 'Demographic GROUP BY, Aggregates, Multi-table JOIN',
            'sql' => "SELECT 
    p.college,
    COUNT(DISTINCT p.participant_id) AS total_students,
    COUNT(r.registration_id) AS total_event_registrations,
    COALESCE(SUM(CASE WHEN r.payment_status = 'Paid' THEN e.registration_fee ELSE 0 END), 0) AS total_fees_paid
FROM participants p
LEFT JOIN registrations r ON p.participant_id = r.participant_id
LEFT JOIN events e ON r.event_id = e.event_id
GROUP BY p.college
ORDER BY total_students DESC, total_event_registrations DESC;"
        ]
    ];

    sendJsonResponse(['success' => true, 'data' => $presets]);
}

if ($method === 'POST') {
    $data = getJsonInput();
    $sql = trim($data['sql'] ?? '');

    if (empty($sql)) {
        sendJsonResponse(['success' => false, 'error' => 'SQL query string cannot be empty.'], 400);
    }

    // Safety guard for live playground: Allow only SELECT, SHOW, EXPLAIN, DESCRIBE queries
    $cleaned = preg_replace('/\s+/', ' ', $sql);
    $firstWord = strtoupper(explode(' ', trim($cleaned))[0]);

    if (!in_array($firstWord, ['SELECT', 'SHOW', 'EXPLAIN', 'DESCRIBE', 'WITH'])) {
        sendJsonResponse([
            'success' => false,
            'error' => 'Security Notice: The DBMS PBL Query Playground permits read-only analytical queries (SELECT, EXPLAIN, SHOW, DESCRIBE, WITH) to prevent accidental data destruction.'
        ], 403);
    }

    // Disallow dangerous keywords within query
    $disallowed = ['DROP', 'TRUNCATE', 'ALTER', 'DELETE', 'UPDATE', 'INSERT', 'RENAME', 'GRANT', 'REVOKE'];
    foreach ($disallowed as $bad) {
        if (preg_match('/\b' . $bad . '\b/i', $sql)) {
            sendJsonResponse([
                'success' => false,
                'error' => "Dangerous operation detected: {$bad} is not permitted in read-only analysis mode."
            ], 403);
        }
    }

    try {
        $startTime = microtime(true);
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $columns = [];
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
        } elseif ($stmt->columnCount() > 0) {
            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                $meta = $stmt->getColumnMeta($i);
                $columns[] = $meta['name'] ?? "col_{$i}";
            }
        }

        sendJsonResponse([
            'success' => true,
            'data' => [
                'columns' => $columns,
                'rows' => $rows,
                'count' => count($rows),
                'execution_time_ms' => $durationMs,
                'executed_sql' => $sql
            ]
        ]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => 'SQL Error: ' . $e->getMessage()], 400);
    }
}
