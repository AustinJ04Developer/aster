<?php
require_once '../settings/db.php';
require_once 'model/SearchModel.php';

class SearchController {
    private $model;
    private $errors = [];
    private $success = '';

    public function __construct($conn) {
        $this->model = new SearchModel($conn);
    }

    public function handleRequest() {
      $searchmember = [];
    $search_field = '';
    $search_value = '';
	$search_type = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $search_field = $_POST['search-field'] ?? '';
        $search_value = $_POST['search-value'] ?? '';
		$search_type = $_POST['search-type'] ?? '';

        if (empty($this->errors)) {
            $searchmember = $this->model->getAllSearch($search_field, $search_value);
        }
    }

    return [
        'searchmember' => $searchmember,
        'search_field' => $search_field,
        'search_value' => $search_value,
		'search_type' => $search_type,
        'success' => $this->success,
        'errors' => $this->errors
    ];
    }
}
?>
