<?php
require_once __DIR__ . '/../TestCase.php';

class SyntaxLintTest extends TestCase {
    private $phpBinary;

    public function __construct() {
        // Detect PHP binary
        $this->phpBinary = 'E:\\XAMPP\\php\\php.exe';
        if (!file_exists($this->phpBinary)) {
            $this->phpBinary = 'php';
        }
    }

    public function run() {
        $this->setTestName("Syntax & Lint Integrity Check of Entire Application");

        $directoriesToScan = [
            __DIR__ . '/../../application/settings',
            __DIR__ . '/../../application/views/controller/model',
            __DIR__ . '/../../application/views/controller',
        ];

        // Specific critical view files to check
        $criticalFiles = [
            __DIR__ . '/../../application/views/aster-jewel-loan.php',
            __DIR__ . '/../../application/views/aster-jewel-loan-view.php',
            __DIR__ . '/../../application/views/aster-jewel-loan-search.php',
            __DIR__ . '/../../application/views/aster-jewel-loan-receipt.php',
            __DIR__ . '/../../application/views/aster-jewel-loan-receipt-pdf.php',
            __DIR__ . '/../../application/views/aster-jewel-loan-pdf.php'
        ];

        $filesToCheck = [];

        foreach ($directoriesToScan as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/*.php');
                if ($files) {
                    $filesToCheck = array_merge($filesToCheck, $files);
                }
            }
        }

        foreach ($criticalFiles as $cf) {
            if (file_exists($cf)) {
                $filesToCheck[] = $cf;
            }
        }

        $filesToCheck = array_unique($filesToCheck);
        $this->assertGreaterThan(0, count($filesToCheck), "Discovered PHP files to lint");

        $lintFailures = [];
        foreach ($filesToCheck as $filePath) {
            $relPath = str_replace(realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR, '', realpath($filePath));
            $escapedPath = escapeshellarg($filePath);
            $cmd = "\"{$this->phpBinary}\" -l {$escapedPath} 2>&1";
            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            if ($returnVar === 0) {
                // Passed lint
            } else {
                $lintFailures[] = "{$relPath}: " . implode(' ', $output);
            }
        }

        if (empty($lintFailures)) {
            $this->assertTrue(true, "All " . count($filesToCheck) . " critical PHP files passed linting with zero syntax errors");
        } else {
            $this->fail("Syntax errors detected in " . count($lintFailures) . " files:\n" . implode("\n", $lintFailures));
        }
    }
}
