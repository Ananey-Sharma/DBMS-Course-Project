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
    $sql = "
        SELECT 
            c.*,
            (SELECT COUNT(*) FROM events e WHERE e.category_id = c.category_id) as total_events
        FROM event_categories c
        ORDER BY c.category_name ASC
    ";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll();

    sendJsonResponse(['success' => true, 'data' => $categories]);
}

function handlePost($pdo) {
    $data = getJsonInput();

    if (empty($data['category_name'])) {
        sendJsonResponse(['success' => false, 'error' => 'Category name is required.'], 400);
    }

    $name = trim($data['category_name']);
    $desc = trim($data['description'] ?? '');

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM event_categories WHERE category_name = ?");
        $chk->execute([$name]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'Category name already exists.'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO event_categories (category_name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
        $id = (int)$pdo->lastInsertId();

        sendJsonResponse(['success' => true, 'message' => 'Category created successfully!', 'category_id' => $id]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handlePut($pdo) {
    $data = getJsonInput();

    if (empty($data['category_id']) || empty($data['category_name'])) {
        sendJsonResponse(['success' => false, 'error' => 'Category ID and name are required.'], 400);
    }

    $id = (int)$data['category_id'];
    $name = trim($data['category_name']);
    $desc = trim($data['description'] ?? '');

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM event_categories WHERE category_name = ? AND category_id != ?");
        $chk->execute([$name, $id]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'Category name already in use.'], 400);
        }

        $stmt = $pdo->prepare("UPDATE event_categories SET category_name = ?, description = ? WHERE category_id = ?");
        $stmt->execute([$name, $desc, $id]);

        sendJsonResponse(['success' => true, 'message' => 'Category updated successfully!']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

function handleDelete($pdo) {
    $data = getJsonInput();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($data['category_id'] ?? 0);

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'Category ID is required.'], 400);
    }

    try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM events WHERE category_id = ?");
        $chk->execute([$id]);
        if ($chk->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'error' => 'Cannot delete category: events exist within this category.'], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM event_categories WHERE category_id = ?");
        $stmt->execute([$id]);

        sendJsonResponse(['success' => true, 'message' => 'Category deleted successfully.']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
