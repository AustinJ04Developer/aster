<?php
require_once '../../settings/db.php';
require_once '../controller/model/RegulationModel.php';
header('Content-Type: application/json');
if (isset($_GET['brid'])) {
    $brid = (int)$_GET['brid'];
    $model = new RegulationModel($conn);
    $degreeBranches = $model->getDegreeBranchesByBranch($brid);
    echo json_encode($degreeBranches);
} else {
    echo json_encode([]);
}
$conn->close();