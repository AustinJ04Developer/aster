<?php
//============================================================+
// File name   : example_001.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 001 for TCPDF class
//               Default Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Default Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 * @group header
 * @group footer
 * @group page
 * @group pdf
 */

// Include the main TCPDF library (search for installation path).
$id = $_REQUEST['id'];
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Bright');
$pdf->setTitle('Dunar Invoice');
$pdf->setSubject($id);
$pdf->setKeywords('TCPDF, PDF, example, test, guide');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(5, 10, 5, 5, true);

// set default header data
/*$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));*/

// set header and footer fonts
/*$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));*/

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
/*$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);*/

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetAutoPageBreak(TRUE, 0);

// set image scale factor
//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->setFont('dejavusans', '', 8, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage('P','A4');

// set text shadow effect
//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

// Set some content to print
//$pdf->writeHTMLCell(100, 50, 10, 10, '<img src="nslogo.png" />');
$html = <<<EOD
<table width="100%" border="1" cellpadding="2">
	<tr>
		<td width="65%" colspan="2" align="center" valign="middle" style="vertical-align:middle;padding-top:10px;">
			<div style="font-size:1pt">&nbsp;</div>
			<div style="font-size:18px;font-weight:bold;margin-top:20px;">Tax Invoice</div>
		</td>
		<td width="35%" style="text-align:center;" align="center;">
			<img src="nslogo.png" style="margin:0 auto;" />
		</td>
	</tr>
	<tr>
		<td width="35%">
			<b>NS Mart</b><br />
			5/43B, Mangalakuntu, killiyoor Thaluk<br />
			Kanyakumari District<br />
			Tamil Nadu 629178<br />
			GSTIN NO:33HYWPS6245C2ZU<br />
			EMAIL ADDRESS:nsmart7755@gmail.com<br />
			PHONE NO:9042859345
		</td>
		<td colspan="2" width="65%">
			<table width="100%" >
				<tr>
					<td>Invoice No:01130
					</td>
					<td>Date:24/7/2024
					</td>
				</tr>
				<tr>
					<td>Due Date:
					</td>
					<td>Destination:Pullimoodu Aatoor
					</td>
				</tr>
				<tr>
					<td>Suppliers Ref:
					</td>
					<td>Order No: 
					</td>
				</tr>
				<tr>
					<td>Reference No:
					</td>
					<td>Vehicle No: 
					</td>
				</tr>
				<tr>
					<td>E-way Bill No: 
					</td>
					<td>Driver M.No:
					</td>
				</tr>
				<tr>
					<td>Transporter Name:
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>Mode of payment: 
					</td>
					<td>CREDIT
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="50%">
			<br />
			BUYER DETAILS<br />
			<b>NAME:ATHITHAN STORE</b><br />
			CITY, DIST.: <br />
			STATE, PINCODE: <br />
			GSTIN NO: <br />
			EMAIL ADDRESS:<br />
		</td>
		<td width="50%">
			<b>TERMS AND CONDITIONS:</b><br /><br /><br /><br /><br />
			PHONE NO:9442948762
		</td>
	</tr>
	<tr>
		<td colspan="2" width="100%">
			<table width="100%" cellpadding="2">
				<tr style="background-color:#cccccc;color:#000;text-align:center;">
					<th width="5%" style="border-right:#000 2px solid;font-weight:bold;">SlNo
					</th>
					<th width="35%" style="border-right:#000 2px solid;font-weight:bold;">Item Description
					</th>
					<th width="10%" style="border-right:#000 2px solid;font-weight:bold;">HSN
					</th>
					<th width="5%" style="border-right:#000 2px solid;font-weight:bold;">Qty
					</th>
					<th width="15%" style="border-right:#000 2px solid;font-weight:bold;">Unit
					</th>
					<th width="15%" align="right" style="border-right:#000 2px solid;font-weight:bold;">Price
					</th>
					<th width="15%" align="right" style="border-right:#000 2px solid;font-weight:bold;">Unit
					</th>
				</tr>
				<tr style="text-align:center;">
					<td width="5%" style="border-right:#000 2px solid">1
					</td>
					<td width="35%" style="border-right:#000 2px solid">No Brandad Ebadat Sella XXXL
					</td>
					<td width="10%" style="border-right:#000 2px solid">100630
					</td>
					<td width="5%" style="border-right:#000 2px solid">1
					</td>
					<td width="15%" style="border-right:#000 2px solid">Bag
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid"><span style="font-family:dejavusans;">&#8377;</span>3000
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid"><span style="font-family:dejavusans;">&#8377;</span>3000
					</td>
				</tr>
				<tr style="text-align:center;">
					<td width="5%" style="border-right:#000 2px solid">1
					</td>
					<td width="35%" style="border-right:#000 2px solid">No Brandad Ebadat Sella XXXL
					</td>
					<td width="10%" style="border-right:#000 2px solid">100630
					</td>
					<td width="5%" style="border-right:#000 2px solid">1
					</td>
					<td width="15%" style="border-right:#000 2px solid">Bag
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid"><span style="font-family:dejavusans;">&#8377;</span>3000
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid"><span style="font-family:dejavusans;">&#8377;</span>3000
					</td>
				</tr>
				<tr style="text-align:center;">
					<td style="border-right:#000 2px solid;border-top:#000 2px solid;text-align:right;font-weight:bold;"
					 colspan="3">Total Quantity
					</td>
					<td width="5%" style="border-right:#000 2px solid;border-top:#000 2px solid;text-align:center;font-weight:bold;">2
					</td>
					<td style="border-right:#000 2px solid;border-top:#000 2px solid;text-align:right;font-weight:bold;" colspan="2">Sub Total
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid;border-top:#000 2px solid;font-weight:bold;">
					<span style="font-family:dejavusans;">&#8377;</span>6000
					</td>
				</tr>
				<tr style="text-align:center;">
					<td style="border-right:#000 2px solid;border-top:#000 2px solid;"
					 colspan="3"><b><u><CENTER>Tax Amount In Words</CENTER></u></b>
					 <br><CENTER>NILL</CENTER>
					 <br><br><b><u><CENTER>Total Amount In Words</CENTER></u></b>
					 <br><CENTER>Six Thousand Only</CENTER>
					</td>
					<td style="border-right:#000 2px solid;border-top:#000 2px solid;text-align:right;" colspan="4">
						<table width="100%" border = "0">
						
							<tr>
								<td width="60%">TAX RATE(%)
								</td>
								<td width="40%" style="text-align:right;">
								</td>
							</tr>
							<tr>
								<td width="60%">OUTPUT CGST :
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span>0
								</td>
							</tr>
							<tr>
								<td width="60%">OUTPUT SGST :
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span>0
								</td>
							</tr>
							<tr>
								<td width="60%">TOTAL AMOUNT :
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span>6000
								</td>
							</tr>
							<tr>
								<td width="60%">ROUND OFF :
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span>0
								</td>
							</tr>
							<tr>
								<td width="60%"><b>TOTAL :</b>
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span><b>6000</b>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center;border-top:#000 2px solid;">
					<center><b><u>DECLARATION</u></b></center><br>
					We declare that this invoice shows the actual price of the goods described and that all particulers are true and correct
					</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center;border-top:#000 2px solid;">
					This is a Computer Generated Invoice
					</td>
				</tr>
				
			</table>
		</td>
	</tr>
</table>
<br>
<br>
<br>
<br>
<br>
<table width="100%" border="1" cellpadding="2">
	<tr>
		<td width="65%" colspan="2" align="center" valign="middle" style="vertical-align:middle;">
			<div style="font-size:1pt">&nbsp;</div>
			<div style="font-size:20px;font-weight:bold;margin-top:20px;">Tax Invoice</div>
		</td>
		<td width="35%" style="text-align:center;" align="center;">
			<img src="nslogo.png" style="margin:0 auto;" />
		</td>
	</tr>
	<tr>
		<td width="35%">
			<b>NS Mart</b><br />
			5/43B, Mangalakuntu, killiyoor Thaluk<br />
			Kanyakumari District<br />
			Tamil Nadu 629178<br />
			GSTIN NO:33HYWPS6245C2ZU<br />
			EMAIL ADDRESS:nsmart7755@gmail.com<br />
			PHONE NO:9042859345
		</td>
		<td colspan="2" width="65%">
			<table width="100%" >
				<tr>
					<td>Invoice No:01130
					</td>
					<td>Date:24/7/2024
					</td>
				</tr>
				<tr>
					<td>Due Date:
					</td>
					<td>Destination:Pullimoodu Aatoor
					</td>
				</tr>
				<tr>
					<td>Suppliers Ref:
					</td>
					<td>Order No: 
					</td>
				</tr>
				<tr>
					<td>Reference No:
					</td>
					<td>Vehicle No: 
					</td>
				</tr>
				<tr>
					<td>E-way Bill No: 
					</td>
					<td>Driver M.No:
					</td>
				</tr>
				<tr>
					<td>Transporter Name:
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>Mode of payment: 
					</td>
					<td>CREDIT
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="50%">
			<br />
			BUYER DETAILS<br />
			<b>NAME:ATHITHAN STORE</b><br />
			CITY, DIST.: <br />
			STATE, PINCODE: <br />
			GSTIN NO: <br />
			EMAIL ADDRESS:<br />
		</td>
		<td width="50%">
			<b>TERMS AND CONDITIONS:</b><br /><br /><br /><br /><br />
			PHONE NO:9442948762
		</td>
	</tr>
	<tr>
		<td colspan="2" width="100%">
			<table width="100%" cellpadding="2">
				<tr style="background-color:#cccccc;color:#000;text-align:center;">
					<th width="5%" style="border-right:#000 2px solid;font-weight:bold;">SlNo
					</th>
					<th width="35%" style="border-right:#000 2px solid;font-weight:bold;">Item Description
					</th>
					<th width="10%" style="border-right:#000 2px solid;font-weight:bold;">HSN
					</th>
					<th width="5%" style="border-right:#000 2px solid;font-weight:bold;">Qty
					</th>
					<th width="15%" style="border-right:#000 2px solid;font-weight:bold;">Unit
					</th>
					<th width="15%" align="right" style="border-right:#000 2px solid;font-weight:bold;">Price
					</th>
					<th width="15%" align="right" style="border-right:#000 2px solid;font-weight:bold;">Unit
					</th>
				</tr>
				<tr style="text-align:center;">
					<td width="5%" style="border-right:#000 2px solid">1
					</td>
					<td width="35%" style="border-right:#000 2px solid">No Brandad Ebadat Sella XXXL
					</td>
					<td width="10%" style="border-right:#000 2px solid">100630
					</td>
					<td width="5%" style="border-right:#000 2px solid">1
					</td>
					<td width="15%" style="border-right:#000 2px solid">Bag
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid"><span style="font-family:dejavusans;">&#8377;</span>3000
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid"><span style="font-family:dejavusans;">&#8377;</span>3000
					</td>
				</tr>
				<tr style="text-align:center;">
					<td width="5%" style="border-right:#000 2px solid">1
					</td>
					<td width="35%" style="border-right:#000 2px solid">No Brandad Ebadat Sella XXXL
					</td>
					<td width="10%" style="border-right:#000 2px solid">100630
					</td>
					<td width="5%" style="border-right:#000 2px solid">1
					</td>
					<td width="15%" style="border-right:#000 2px solid">Bag
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid"><span style="font-family:dejavusans;">&#8377;</span>3000
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid"><span style="font-family:dejavusans;">&#8377;</span>3000
					</td>
				</tr>
				<tr style="text-align:center;">
					<td style="border-right:#000 2px solid;border-top:#000 2px solid;text-align:right;font-weight:bold;"
					 colspan="3">Total Quantity
					</td>
					<td width="5%" style="border-right:#000 2px solid;border-top:#000 2px solid;text-align:center;font-weight:bold;">2
					</td>
					<td style="border-right:#000 2px solid;border-top:#000 2px solid;text-align:right;font-weight:bold;" colspan="2">Sub Total
					</td>
					<td width="15%" align="right" style="border-right:#000 2px solid;border-top:#000 2px solid;font-weight:bold;">
					<span style="font-family:dejavusans;">&#8377;</span>6000
					</td>
				</tr>
				<tr style="text-align:center;">
					<td style="border-right:#000 2px solid;border-top:#000 2px solid;"
					 colspan="3"><b><u><CENTER>Tax Amount In Words</CENTER></u></b>
					 <br><CENTER>NILL</CENTER>
					 <br><br><b><u><CENTER>Total Amount In Words</CENTER></u></b>
					 <br><CENTER>Six Thousand Only</CENTER>
					</td>
					<td style="border-right:#000 2px solid;border-top:#000 2px solid;text-align:right;" colspan="4">
						<table width="100%" border = "0">
						
							<tr>
								<td width="60%">TAX RATE(%)
								</td>
								<td width="40%" style="text-align:right;">
								</td>
							</tr>
							<tr>
								<td width="60%">OUTPUT CGST :
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span>0
								</td>
							</tr>
							<tr>
								<td width="60%">OUTPUT SGST :
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span>0
								</td>
							</tr>
							<tr>
								<td width="60%">TOTAL AMOUNT :
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span>6000
								</td>
							</tr>
							<tr>
								<td width="60%">ROUND OFF :
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span>0
								</td>
							</tr>
							<tr>
								<td width="60%"><b>TOTAL :</b>
								</td>
								<td width="40%" style="text-align:right;"><span style="font-family:dejavusans;">&#8377;</span><b>6000</b>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center;border-top:#000 2px solid;">
					<center><b><u>DECLARATION</u></b></center><br>
					We declare that this invoice shows the actual price of the goods described and that all particulers are true and correct
					</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center;border-top:#000 2px solid;">
					This is a Computer Generated Invoice
					</td>
				</tr>
				
			</table>
		</td>
	</tr>
</table>
EOD;
// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('dunar-invoice.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
