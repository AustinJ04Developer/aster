<?php
require_once __DIR__ . '/../TestCase.php';

class PaymentAllocationTest extends TestCase {
    
    /**
     * Helper to allocate payment following Jewel Loan rules:
     * 1. Default Interest
     * 2. Accrued Interest
     * 3. Principal Reduction
     */
    private function allocate($totalPaid, $interestDue, $defaultIntDue, $currentBalance, $txnType = 'INTEREST_PAYMENT') {
        if ($txnType === 'JEWEL_RELEASE') {
            return [
                'principalPaid' => $currentBalance,
                'interestPaid' => $interestDue,
                'defaultInterestPaid' => $defaultIntDue,
                'newBalance' => 0.00,
                'totalPaid' => round($currentBalance + $interestDue + $defaultIntDue, 2)
            ];
        }

        $defaultPaid = min($defaultIntDue, $totalPaid);
        $rem = max(0.00, $totalPaid - $defaultPaid);

        $interestPaid = min($interestDue, $rem);
        $rem = max(0.00, $rem - $interestPaid);

        $principalPaid = min($currentBalance, $rem);
        $newBalance = max(0.00, round($currentBalance - $principalPaid, 2));

        return [
            'principalPaid' => round($principalPaid, 2),
            'interestPaid' => round($interestPaid, 2),
            'defaultInterestPaid' => round($defaultPaid, 2),
            'newBalance' => $newBalance,
            'totalPaid' => round($principalPaid + $interestPaid + $defaultPaid, 2)
        ];
    }

    public function run() {
        $this->setTestName("Allocation Case 1: Exact Interest Payment");
        // Loan balance 61,000, interest due 150.41, default interest 0.00, pays 150.41
        $res = $this->allocate(150.41, 150.41, 0.00, 61000.00);
        $this->assertEquals(150.41, $res['interestPaid'], "Interest paid is exactly 150.41");
        $this->assertEquals(0.00, $res['defaultInterestPaid'], "Default interest is 0.00");
        $this->assertEquals(0.00, $res['principalPaid'], "Principal paid is 0.00");
        $this->assertEquals(61000.00, $res['newBalance'], "Balance remains unchanged at 61000.00");

        $this->setTestName("Allocation Case 2: Partial Payment Covering Interest & Principal");
        // User pays ₹7,520.00 on ₹61,000 balance with ₹150.41 interest due
        $res2 = $this->allocate(7520.00, 150.41, 0.00, 61000.00);
        $this->assertEquals(150.41, $res2['interestPaid'], "Interest paid is 150.41");
        $this->assertEquals(7369.59, $res2['principalPaid'], "Principal reduced by 7,369.59");
        $this->assertEquals(53630.41, $res2['newBalance'], "New remaining balance is 53,630.41");
        $this->assertEquals(7520.00, $res2['totalPaid'], "Total paid matches 7,520.00");

        $this->setTestName("Allocation Case 3: Payment with Default Interest");
        // Overdue loan: default interest 50.00, regular interest 200.00, balance 10,000, pays 500.00
        $res3 = $this->allocate(500.00, 200.00, 50.00, 10000.00);
        $this->assertEquals(50.00, $res3['defaultInterestPaid'], "Default interest priority 1: 50.00 covered");
        $this->assertEquals(200.00, $res3['interestPaid'], "Accrued interest priority 2: 200.00 covered");
        $this->assertEquals(250.00, $res3['principalPaid'], "Principal priority 3: excess 250.00 reduces principal");
        $this->assertEquals(9750.00, $res3['newBalance'], "New balance is 9,750.00");

        $this->setTestName("Allocation Case 4: Partial Interest Only Payment");
        // Interest due is 300.00, customer pays only 100.00
        $res4 = $this->allocate(100.00, 300.00, 0.00, 50000.00);
        $this->assertEquals(100.00, $res4['interestPaid'], "Partial interest of 100.00 covered");
        $this->assertEquals(0.00, $res4['principalPaid'], "0 principal reduction on partial interest");
        $this->assertEquals(50000.00, $res4['newBalance'], "Balance remains at 50,000.00");

        $this->setTestName("Allocation Case 5: Full Settlement & Jewel Release");
        // Jewel release pays full remaining balance + all due interests
        $res5 = $this->allocate(0, 150.41, 0.00, 61000.00, 'JEWEL_RELEASE');
        $this->assertEquals(61000.00, $res5['principalPaid'], "Principal paid is full 61,000.00");
        $this->assertEquals(150.41, $res5['interestPaid'], "Interest paid is 150.41");
        $this->assertEquals(0.00, $res5['newBalance'], "Remaining balance is 0.00");
        $this->assertEquals(61150.41, $res5['totalPaid'], "Total paid is 61,150.41");
    }
}
