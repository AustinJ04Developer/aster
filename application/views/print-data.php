<?php
session_start();
/**
 * This is a demo script for the functions of the PHP ESC/POS print driver,
 * Escpos.php.
 *
 * Most printers implement only a subset of the functionality of the driver, so
 * will not render this output correctly in all cases.
 *
 * @author Michael Billington <michael.billington@gmail.com>
 */
// print __DIR__;
if(!isset($_SESSION['UserId']))
		header('Location: login.php');
include_once "../settings/config.php";
require '../../vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
$smid = $_REQUEST['s'];
global $dbh;
try
{
	$sql = "SELECT sm.TotalSave,sd.ItemName,sd.qty,sd.Price,sd.Tax,sd.SalesPrice,sd.TotalSalesAmount,sm.InvoiceNo,c.PrimaryContact
			FROM  invt_sales_master sm
			LEFT JOIN invt_sales_details sd ON sd.SMID = sm.SMID 
			LEFT JOIN invt_customers c ON c.CustomerId = sm.CustomerId 
			WHERE sm.SMID = ".$smid;
	$query_result = $dbh->query($sql);
	$query_result->setFetchMode(PDO::FETCH_ASSOC);
	$result = $query_result->fetch();
	$slno = $result["InvoiceNo"];
	$customer_name = $result["PrimaryContact"];
}
catch (PDOException $e)
{
	ErrorTracking('purchase-functions','GetSlNo',$e->getMessage());
}
//$connector = new FilePrintConnector("\\\\192.168.1.7\\RP3200 plus");
$connector = new FilePrintConnector("php://stdout");
//print $connector;
$printer = new Printer($connector);
/* Initialize */
$printer -> initialize();
function addSpaces($string = '', $valid_string_length = 0) {
    if (strlen($string) < $valid_string_length) {
        $spaces = $valid_string_length - strlen($string);
        for ($index1 = 1; $index1 <= $spaces; $index1++) {
            $string = $string . ' ';
        }
    }

    return $string;
}

$printer->feed();
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer -> setTextSize(2,2);
$printer -> text("NS-MART \n");
$printer -> setTextSize(1,1);
$printer -> text("Mangalakuntu \n");
$printer -> text("Ph: 9042851345 \n");
$gstno = '33HYWPS6245C2ZU';
//$customer_name  = 'OTHERS';
//$slno = '12345';
$date = date('d-m-Y');
$printer->text(addSpaces($gstno, 35) . addSpaces($customer_name, 5). "\n");
$printer->text(addSpaces($slno, 35) . addSpaces($date, 5). "\n");
$printer->setPrintLeftMargin(0);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->setEmphasis(true);
$printer->text(addSpaces('Item', 23) . addSpaces('Qty', 5) . addSpaces('MRP', 5) . addSpaces('Rate', 7) . addSpaces('Tot', 7) . "\n");
$printer->setEmphasis(false);
$sql = "SELECT sm.TotalSave,sd.ItemName,sd.qty,sd.Price,sd.Tax,sd.SalesPrice,sd.TotalSalesAmount,sm.InvoiceNo,c.PrimaryContact,sm.NetAmount,sm.TotalSave
			FROM  invt_sales_master sm
			LEFT JOIN invt_sales_details sd ON sd.SMID = sm.SMID 
			LEFT JOIN invt_customers c ON c.CustomerId = sm.CustomerId 
			WHERE sm.SMID = ".$smid;
$query_result = $dbh->query($sql);
$query_result->setFetchMode(PDO::FETCH_ASSOC);
$total_items = 0;
$total_qty = 0;
$item = '';
$qty = 0;
$mrp = 0;
$rate = 0;
$total = 0;
$bill_total = 0;
$total_save = 0;
while($result = $query_result->fetch())
{
	$item = $result["ItemName"];
	$qty = $result["qty"];
	$mrp = $result["Price"];
	$rate = $result["SalesPrice"];
	$total = $result["TotalSalesAmount"];
	$bill_total = $result["NetAmount"];
	$total_save = $result["TotalSave"];
	$printer->text(addSpaces($item, 23) . addSpaces($qty, 5) . addSpaces($mrp, 5) . addSpaces($rate, 7) . addSpaces($total, 7) . "\n");
	$total_qty += $qty;
	$total_items += 1;
}
$printer -> setTextSize(1,2);
//$total_items = '6';
//$total_qty = '60';
//$bill_total = '333333.00';
$printer->text(addSpaces('Tot Items:',5) . addSpaces($total_items, 5) . addSpaces('Tot Qty:',5) . addSpaces($total_qty, 5) . addSpaces('Bill Amt:',5) .addSpaces($bill_total, 5). "\n");
$printer -> setTextSize(1,1);
$printer->text(addSpaces('You Saved Rs.',5) . addSpaces($total_save, 5) . "\n\n\n");
$printer -> setTextSize(1,1);
$printer->text(addSpaces('GOODS ONCE SOLD CANNOT BE TAKEN BACK OR EXCHANGE',5) . "\n");
$printer->cut();
$printer->pulse();
$printer->close();
header('Location:invt-sales-entry.php?s=1');

