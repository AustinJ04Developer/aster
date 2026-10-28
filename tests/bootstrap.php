<?php
/**
 * Test Bootstrap File
 */
define('DB_NAME', 'aster');
define('TEST_MODE', true);

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load application configs and database
require_once __DIR__ . '/../application/settings/config.php';
require_once __DIR__ . '/../application/settings/db.php';
require_once __DIR__ . '/../application/views/controller/model/JewelLoanModel.php';
require_once __DIR__ . '/../application/views/controller/model/SearchModel.php';
require_once __DIR__ . '/TestCase.php';
