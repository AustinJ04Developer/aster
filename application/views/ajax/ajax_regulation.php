<?php
require_once '../../config.php';
require_once '../controller/RegulationController.php';

$controller = new RegulationController($conn);

if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    if ($_POST['action'] == 'getBatches' && isset($_POST['did'])) {
        echo json_encode($controller->getBatches($_POST['did']));
    } elseif ($_POST['action'] == 'getDegreeBranches' && isset($_POST['brid'])) {
        echo json_encode($controller->getDegreeBranches($_POST['brid']));
    }
    exit;
}
?>