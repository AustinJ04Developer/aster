<?php
/**
 * Application Database & Environment Configuration
 * 
 * Auto-detects port (3306/3307), creates database if missing,
 * and synchronizes connections across team collaborators.
 */
ini_set('max_execution_time', 600);

require_once __DIR__ . '/db_connection_provider.php';
?>
