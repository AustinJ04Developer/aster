<?php
require_once '../../settings/db.php';
require_once '../controller/model/RegulationModel.php';
header('Content-Type: application/json');
if (isset($_GET['did'])) {
    $did = (int)$_GET['did'];
    $model = new RegulationModel($conn);
    $branches = $model->getBranchesByDegree($did);
    echo json_encode($branches);
} else {
    echo json_encode([]);
}
$conn->close();
?>