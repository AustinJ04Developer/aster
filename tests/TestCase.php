<?php
/**
 * Lightweight, robust test base class for Aster PHP project
 */
abstract class TestCase {
    protected $passedAssertions = 0;
    protected $failedAssertions = 0;
    protected $currentTest = '';
    protected $errors = [];

    abstract public function run();

    protected function setTestName($name) {
        $this->currentTest = $name;
        echo "\n  \033[1;36mTesting: {$name}\033[0m\n";
    }

    public function assertTrue($condition, $message = '') {
        if ($condition === true) {
            $this->pass($message ?: "Assertion is true");
        } else {
            $this->fail($message ?: "Expected true, got false");
        }
    }

    public function assertFalse($condition, $message = '') {
        if ($condition === false) {
            $this->pass($message ?: "Assertion is false");
        } else {
            $this->fail($message ?: "Expected false, got true");
        }
    }

    public function assertEquals($expected, $actual, $message = '') {
        if ($expected == $actual) {
            $this->pass($message ?: "Values match: [{$expected}]");
        } else {
            $expStr = is_scalar($expected) ? $expected : json_encode($expected);
            $actStr = is_scalar($actual) ? $actual : json_encode($actual);
            $this->fail(($message ? $message . " - " : "") . "Expected [{$expStr}], got [{$actStr}]");
        }
    }

    public function assertNotEquals($expected, $actual, $message = '') {
        if ($expected != $actual) {
            $this->pass($message ?: "Values do not match as expected");
        } else {
            $this->fail(($message ? $message . " - " : "") . "Expected value to differ from [{$expected}]");
        }
    }

    public function assertGreaterThan($expected, $actual, $message = '') {
        if ($actual > $expected) {
            $this->pass($message ?: "Actual [{$actual}] > Expected [{$expected}]");
        } else {
            $this->fail(($message ? $message . " - " : "") . "Actual [{$actual}] not greater than [{$expected}]");
        }
    }

    public function assertNotNull($actual, $message = '') {
        if ($actual !== null) {
            $this->pass($message ?: "Value is not null");
        } else {
            $this->fail($message ?: "Expected non-null value, got null");
        }
    }

    public function assertContains($needle, $haystack, $message = '') {
        if (is_array($haystack)) {
            if (in_array($needle, $haystack)) {
                $this->pass($message ?: "Array contains expected item");
                return;
            }
        } elseif (is_string($haystack)) {
            if (strpos($haystack, (string)$needle) !== false) {
                $this->pass($message ?: "String contains '{$needle}'");
                return;
            }
        }
        $this->fail($message ?: "Item not found in haystack");
    }

    public function assertMatchesRegex($pattern, $value, $message = '') {
        if (preg_match($pattern, (string)$value)) {
            $this->pass($message ?: "'{$value}' matches pattern {$pattern}");
        } else {
            $this->fail($message ?: "'{$value}' does NOT match pattern {$pattern}");
        }
    }

    protected function pass($message) {
        $this->passedAssertions++;
        echo "    \033[32m[PASS]\033[0m {$message}\n";
    }

    protected function fail($message) {
        $this->failedAssertions++;
        $fullMsg = "{$this->currentTest}: {$message}";
        $this->errors[] = $fullMsg;
        echo "    \033[31m[FAIL]\033[0m {$message}\n";
    }

    public function getPassedCount() {
        return $this->passedAssertions;
    }

    public function getFailedCount() {
        return $this->failedAssertions;
    }

    public function getErrors() {
        return $this->errors;
    }
}
