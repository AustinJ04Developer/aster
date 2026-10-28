<?php
require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/../../application/views/controller/model/SearchModel.php';

class SearchModelTest extends TestCase {
    private $conn;
    private $searchModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->searchModel = new SearchModel($conn);
    }

    public function run() {
        $this->setTestName("SearchModel Input Validation");
        // Empty inputs
        $resEmpty = $this->searchModel->getAllSearch('', '');
        $this->assertEquals([], $resEmpty, "Empty search returns empty array");

        $resInvalid = $this->searchModel->getAllSearch(99, 'test');
        $this->assertEquals([], $resInvalid, "Invalid field index returns empty array");

        $this->setTestName("Search by Member Code / D-Number (Field 1)");
        $resDNum = $this->searchModel->getAllSearch(1, 'D-0016');
        $this->assertTrue(is_array($resDNum), "Search returns array");
        $this->assertGreaterThan(0, count($resDNum), "Found at least 1 record for D-0016");
        if (!empty($resDNum)) {
            $first = $resDNum[0];
            $this->assertEquals('D-0016', $first['DNumber'], "Returned correct DNumber");
            $this->assertEquals('jewel', $first['RecordSource'], "RecordSource is 'jewel'");
        }

        $this->setTestName("Search by Name (Field 2)");
        $resName = $this->searchModel->getAllSearch(2, 'a');
        $this->assertTrue(is_array($resName), "Search by partial name returns array");

        $this->setTestName("Search Non-Existent Record");
        $resNone = $this->searchModel->getAllSearch(1, 'NONEXISTENT_999999');
        $this->assertEquals([], $resNone, "Non-existent search returns empty array");
    }
}
