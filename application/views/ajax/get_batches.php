<?php
require_once '../../settings/db.php';
require_once '../controller/model/RegulationModel.php';
header('Content-Type: application/json');
if (isset($_GET['did'])) {
    $did = (int)$_GET['did'];
    $model = new RegulationModel($conn);
    $batches = $model->getBatchesByDegree($did);
    echo json_encode($batches);
} else {
    echo json_encode([]);
}
$conn->close();
?>