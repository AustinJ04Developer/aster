<?php
session_start();
require_once '../settings/db.php';

$type = $_GET['type'] ?? '';
$value = trim($_GET['value'] ?? '');
$editId = intval($_GET['editId'] ?? 0); // optional, skip check for current row

$response = ['exists' => false];

if ($type && $value) {
    $column = '';
    switch ($type) {
        case 'aadhaar':
            $column = 'Aadhaar';
            break;
        case 'pan':
            $column = 'PanNumber';
            break;
        case 'mobile':
            $column = 'MobileNo';
            break;
        default:
            echo json_encode($response);
            exit;
    }

    $sql = "SELECT MemberId FROM ast_members WHERE $column=?";

    if ($editId > 0) {
        $sql .= " AND MemberId<>?";
    }

    $stmt = $conn->prepare($sql);
    if ($editId > 0) {
        $stmt->bind_param("si", $value, $editId);
    } else {
        $stmt->bind_param("s", $value);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['exists'] = true;
    }
}

echo json_encode($response);
$conn->close();
?>
