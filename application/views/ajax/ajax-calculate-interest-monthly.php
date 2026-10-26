<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();

require_once '../../../settings/db.php';
require_once '../model/SavingsMonthlyModel.php';
$model = new SavingsMonthlyModel($conn);
if (
    isset($_POST['saving_type']) &&
    isset($_POST['interest_percentage']) &&
    isset($_POST['installment_amount'])
)
{
    $savingType =(int)$_POST['saving_type'];
    $interestPercentage =(float)$_POST['interest_percentage'];
    $installmentAmount =(float)$_POST['installment_amount'];
    $result = $model->Interestcalculate($savingType,$installmentAmount,$interestPercentage);
    header('Content-Type: application/json');
    echo json_encode([
    'total_interest' => number_format($result['total_interest'], 2, '.', ''),
    'matured_amount' => number_format($result['matured_amount'], 2, '.', '')
	]);
    exit;
}
?>