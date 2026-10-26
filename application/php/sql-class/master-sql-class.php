<?php
session_start();
//include ("../../settings/config.php");
class COEMasterSQL
{
	//Insert Degree Details
	function InsertUpdateDegreeMaster($degreeName,$noOfYear,$editId)
	{
		global $dbh;
		// Query Generation
		try {
			$mode = 0;
            if ($editId > 0) {
                // Update record
                $stmt = $dbh->prepare('UPDATE coe_master_degree SET DegreeName = ?, NoOfYear = ? WHERE DID = ?');
                $stmt->execute([$degreeName, $noOfYear, $editId]);
                $success = 'Degree updated successfully.';
				$mode = 2;
            } else {
                // Insert new record
                $stmt = $dbh->prepare('INSERT INTO coe_master_degree (DegreeName, NoOfYear) VALUES (?, ?)');
                $stmt->execute([$degreeName, $noOfYear]);
                $success = 'Degree added successfully.';
				$mode = 1;
            }
            // Reset form
            $date = $degreeName = $noOfYear = '';
            $editId = 0;
			return array($success,$mode);
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        } // End Catch
	}// End Function
	// Insert Batch Details
	function InsertUpdateBatchMaster($did,$batchName,$fromYear,$toYear,$editId)
	{
		global $dbh;
		// Query Generation
		try {
			$mode = 0;
            if ($editId > 0) {
				$cls_master = new COEMasterSQL; // sql-class/sl-class.php
                // Update record
                $stmt = $dbh->prepare('UPDATE coe_master_batch SET DID = ?, BatchName = ?, FromYear = ?, ToYear = ? WHERE BID = ?');
                $stmt->execute([$did, $batchName, $fromYear, $toYear, $editId]);
                $success = 'Batch updated successfully.';
				$mode = 1;
            } else {
                // Insert new record
                $stmt = $dbh->prepare('INSERT INTO coe_master_batch (DID, BatchName, FromYear, ToYear) VALUES (?, ?, ?, ?)');
                $stmt->execute([$did, $batchName, $fromYear, $toYear]);
                $success = 'Batch added successfully.';
				$mode = 1;
            }
            // Reset form
            $batchName = $fromYear = $toYear = $did = '';
            $editId = 0;
			return array($success,$mode);
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        } // End Catch
	}// End Function
} // End Class

