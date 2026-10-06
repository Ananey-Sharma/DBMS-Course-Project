<?php
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    case 'PUT':
        handlePut($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

function handleGet($pdo) {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM organizers WHERE organizer_id = ?");
        $stmt->execute([$id]);
        $organizer = $stmt->fetch();

        if (!$organizer) {
            sendJsonResponse(['success' => false, 'error' => 'Organizer not found'], 404);
        }

        // Fetch events organized
        $eventsStmt = $pdo->prepare("
            SELECT e.*, c.category_name
            FROM events e
            JOIN event_categories c ON e.category_id = c.category_id
            WHERE e.organizer_id = ?
            ORDER BY e.event_id DESC
        ");
        $eventsStmt->execute([$id]);
        $organizer['events'] = $eventsStmt->fetchAll();

        sendJsonResponse(['success' => true, 'data' => $organizer]);
    } else {
        $sql = "
            SELECT 
                o.*,
                (SELECT COUNT(*) FROM events e WHERE e.organizer_id = o.organizer_id) as total_events_organized
            FROM organizers o
            ORDER BY o.organizer_name ASC
        ";
        $stmt = $pdo->query($sql);
        $organizers = $stmt->fetchAll();

        sendJsonResponse(['success' => true, 'data' => $organizers]);
    }
}

function handlePost($pdo) {
    $data = getJsonInput();

    if (empty($data['organizer_name']) || empty($data['email'])) {
        sendJsonResponse(['success' => false, 'error' => 'Organizer name and email are required.'], 400);
    }

    $name = trim($data['organizer_name']);
    $email = trim($data['email']);
    $phone = trim($data['phone'] ?? '');
    $department = trim($data['department'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(['success' => false, 'error' => 'Invalid email format.'], 400);
    }

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM organizers WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'An organizer with this email already exists.'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO organizers (organizer_name, email, phone, department) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $department]);
        $id = (int)$pdo->lastInsertId();

        sendJsonResponse(['success' => true, 'message' => 'Organizer created successfully!', 'organizer_id' => $id]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handlePut($pdo) {
    $data = getJsonInput();

    if (empty($data['organizer_id']) || empty($data['organizer_name']) || empty($data['email'])) {
        sendJsonResponse(['success' => false, 'error' => 'Organizer ID, name, and email are required.'], 400);
    }

    $id = (int)$data['organizer_id'];
    $name = trim($data['organizer_name']);
    $email = trim($data['email']);
    $phone = trim($data['phone'] ?? '');
    $department = trim($data['department'] ?? '');

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM organizers WHERE email = ? AND organizer_id != ?");
        $chk->execute([$email, $id]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'Email is already used by another organizer.'], 400);
        }

        $stmt = $pdo->prepare("UPDATE organizers SET organizer_name = ?, email = ?, phone = ?, department = ? WHERE organizer_id = ?");
        $stmt->execute([$name, $email, $phone, $department, $id]);

        sendJsonResponse(['success' => true, 'message' => 'Organizer updated successfully!']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handleDelete($pdo) {
    $data = getJsonInput();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($data['organizer_id'] ?? 0);

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'Organizer ID is required.'], 400);
    }

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM events WHERE organizer_id = ?");
        $chk->execute([$id]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'Cannot delete organizer: active events are linked to this organizer.'], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM organizers WHERE organizer_id = ?");
        $stmt->execute([$id]);

        sendJsonResponse(['success' => true, 'message' => 'Organizer deleted successfully.']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
