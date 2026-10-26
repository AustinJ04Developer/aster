<?php
session_start();
if(!isset($_SESSION['UserId']))
	header('Location: login.php');
include_once "../php/general-functions.php";
InsertLoginIdInLogged($_SESSION['UserId']);///php/general-functions.php
$row_count_receipt = 0;
$row_count_payment = 0;
//$new_shares = GetCountForDashboards("pdn_share","COUNT(*) AS TOT","DATE(Date) = '".date('Y-m-d')."' AND IsDeleted = 0");
//$new_rd = GetCountForDashboards("pdn_rd_master","COUNT(*) AS TOT","DATE(StartDate) = '".date('Y-m-d')."' AND IsDeleted = 0");
//$new_fd = GetCountForDashboards("pdn_fd_deposit","COUNT(*) AS TOT","DATE(FDStartDate) = '".date('Y-m-d')."' AND IsDeleted = 0");
//$new_sd = GetCountForDashboards("pdn_savings_deposit","COUNT(*) AS TOT","DATE(LastUpdatedDate) = '".date('Y-m-d')."' AND IsDeleted = 0");
//$new_jl = GetCountForDashboards("pdn_jl_new","COUNT(*) AS TOT","DATE(JLDate) = '".date('Y-m-d')."' AND IsDeleted = 0");
//$cash_total_credit = GetCountForDashboards("pdn_transaction","SUM(Amount) AS TOT","IsDeleted = 0 AND TransType = 1 AND (TransMethod = 1 or TransMethod = 4) AND 
	//										DATE(Date) < '".date('Y-m-d')."'");
//$cash_total_debit = GetCountForDashboards("pdn_transaction","SUM(Amount) AS TOT","IsDeleted = 0 AND TransType = 2 AND (TransMethod = 1 or TransMethod = 4) AND 
	//										DATE(Date) < '".date('Y-m-d')."'");
//$cash_total_credit_current = GetCountForDashboards("pdn_transaction","SUM(Amount) AS TOT","IsDeleted = 0 AND TransType = 1 AND (TransMethod = 1 or TransMethod = 4) AND 
	//										DATE(Date) <= '".date('Y-m-d')."'");
//$cash_total_debit_current = GetCountForDashboards("pdn_transaction","SUM(Amount) AS TOT","IsDeleted = 0 AND TransType = 2 AND (TransMethod = 1 or TransMethod = 4) AND 
	//										DATE(Date) <= '".date('Y-m-d')."'");
//$cash_opening_balance = number_format($cash_total_credit - $cash_total_debit,2);
//$cash_total_credit = GetCountForDashboards("pdn_transaction","SUM(Amount) AS TOT","IsDeleted = 0 AND TransType = 1 AND TransMethod = 1");
//$cash_total_debit = GetCountForDashboards("pdn_transaction","SUM(Amount) AS TOT","IsDeleted = 0 AND TransType = 2 AND TransMethod = 1");
//$cash_current_balance = number_format($cash_total_credit_current - $cash_total_debit_current,2);
//$no_of_cheques = GetCountForDashboards("pdn_cheque","COUNT(*) AS TOT","DATE(Date) = '".date('Y-m-d')."' AND IsDeleted = 0");
//$collection_of_cheques_out = GetCountForDashboards("pdn_cheque","SUM(Amount) AS TOT","ChequePaymentType = 2 AND DATE(Date) = '".date('Y-m-d')."' AND IsDeleted = 0");
//$collection_of_cheques_in = GetCountForDashboards("pdn_cheque","SUM(Amount) AS TOT","ChequePaymentType = 1 AND DATE(Date) = '".date('Y-m-d')."' AND IsDeleted = 0");
//list($row_count_receipt,$search_result_transaction_receipt)=GetManagerUnApprovedTranastion(1);// 1=>Receipt  //general-functgions.php
//list($row_count_payment,$search_result_transaction_payment)=GetManagerUnApprovedTranastion(2);// 2=>Payment
//list($row_count_jl_due_list,$jl_due_list)=GetJLDueList();//general-functions.php
//list($row_count_jl_exceed_due_list,$jl_exceed_due_list)=GetJLDueExceedList();//general-functions.php
//list($row_count_jl_exceed_grace_period,$jl_exceed_grace_period)=GetJLGracePeriodExceedList();//general-functions.php

/*$sql_new_shares = "SELECT COUNT(*) AS TOT FROM pdn_share WHERE ";
$sql_new_rd = "SELECT COUNT(*) AS TOT FROM pdn_rd_master WHERE DATE(StartDate) = date('Y-m-d') AND IsDeleted = 0";
$sql_new_fd = "SELECT COUNT(*) AS TOT FROM pdn_fd_deposit WHERE DATE(FDStartDate) = date('Y-m-d') AND IsDeleted = 0";
$sql_new_sd = "SELECT COUNT(*) AS TOT FROM pdn_savings_deposit WHERE DATE(LastUpdatedDate) = date('Y-m-d') AND IsDeleted = 0";
$sql_new_jl = "SELECT COUNT(*) AS TOT FROM pdn_jl_new WHERE DATE(JLDate) = date('Y-m-d') AND IsDeleted = 0";
$sql_sd_credit = "SELECT SUM(Amount) as Credit FROM pdn_transaction WHERE IsDeleted = 0 AND ATMID = 4 AND TransType = 1 AND TransMethod = 1";
$sql_sd_debit = "SELECT SUM(Amount) as Debit FROM pdn_transaction WHERE IsDeleted = 0 AND ATMID = 4 AND TransType = 2 AND TransMethod = 1";*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<title>Inventory</title>
<!-- Bootstrap Core CSS -->
<link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom CSS -->
<link href="css/mpdn.css" rel="stylesheet">
<!-- Custom Fonts -->
<link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/datepicker.css" rel="stylesheet" type="text/css">
<link href="css/data-table.css" rel="stylesheet" type="text/css">
<link href="css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<link href="css/alertify.core.css" rel="stylesheet" type="text/css">
<link href="css/alertify.default.css" rel="stylessheet" type="text/css">
<link href="css/alertify.bootstrap.zoom.css" rel="stylesheet" type="text/css">
<link href="css/alertify.bootstrap-rtl.css" rel="stylesheet" type="text/css">
<link href="css/alertify.min.css" rel="stylesheet" type="text/css">
</head>
<body onbeforeunload="logout()">
<div id="wrapper">
  <?php include_once "header.php"; ?>
  <!-- Page Code Start From Here -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Managing Director Dashboard</h2>
                    <hr class="hr-background">
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <!-- SHARE APPROVAL START -->
          <div class="panel-group" id="accordion">
            <!-- TRANSACTION RECEIPT APPROVAL STARTS -->
            <?php if($row_count_receipt > 0)
			{?>
			<div class="panel panel-custom-danger">
			<?php } else {?>
            <div class="panel panel-custom-success">
            <?php } ?>
				<div class="panel-heading panel-heading-custom-grey">
			
			     <a data-toggle="collapse" data-parent="#accordion" href="#collapse4" style="text-decoration:none; outline:none;">
			    	 <h4 class="panel-title" style="text-align:center;">
        		 		<span style="color:#FFFFFF;">Transaction Receipt Approval</span>
				     </h4>
			     </a>
			   </div>
		     	<div id="collapse4" class="panel-collapse collapse ">
                   <div class="rm-dashboard-transaction-approval">
                    		
                            <div class="row">
                              <!-- Second Row -->
                              <div class="col-md-12">
                                    <table id="designation-details" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                      <thead>
                                        <tr class="header-row">
                                          <th>SlNo</th>
										  <th>R.Type</th>
                                          <th>A/C Type</th>
                                          <th>A/C Num</th>
                                          <th>Share</th>
                                          <th>Name</th>
                                          <th>Remarks</th>
                                          <th>Mode</th>
                                          <th>Challan No.</th>
                                          <th>Amount</th>
										  <th>Done By</th>
                                          <th>Approve</th>
                                        </tr>
                                      </thead>
                                      <tbody class="table-coloum-style">
                                      <?php echo $search_result_transaction_receipt;?>
                                      </tbody>
                                    </table>
                              </div>
                            <!-- Page Code End -->
                          </div>
                     </div>
                 </div>
               
            </div>
            <!-- TRANSACTION RECEIPT APPROVAL END -->
            
            
            <!-- TRASACTION PAYMENT APPROVAL STARTS -->
            
			<?php if($row_count_payment > 0)
			{?>
			<div class="panel panel-custom-danger">
			<?php } else {?>
            <div class="panel panel-custom-success">
            <?php } ?>
				<div class="panel-heading panel-heading-custom-grey">
			
			     <a data-toggle="collapse" data-parent="#accordion" href="#collapse5" style="text-decoration:none; outline:none;">
			    	 <h4 class="panel-title" style="text-align:center;">
        		 		<span style="color:#FFFFFF;">Transaction Payment Approval</span>
				     </h4>
			     </a>
			   </div>
		     	<div id="collapse5" class="panel-collapse collapse ">
                   <div class="rm-dashboard-transaction-approval">
                    		
                            <div class="row">
                              <!-- Second Row -->
                              <div class="col-md-12">
                                    <table id="designation-details" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                      <thead>
                                        <tr class="header-row">
                                          <th>SlNo</th>
										  <th>R.Type</th>
                                          <th>A/C Type</th>
                                          <th>A/C Num</th>
                                          <th>Share</th>
                                          <th>Name</th>
                                          <th>Remarks</th>
                                          <th>Mode</th>
                                          <th>Challan No.</th>
                                          <th>Amount</th>
										  <th>Done By</th>
                                          <th>Approve</th>
                                        </tr>
                                      </thead>
                                      <tbody class="table-coloum-style">
                                      <?php echo $search_result_transaction_payment;?>
                                      </tbody>
                                    </table>
                              </div>
                            <!-- Page Code End -->
                          </div>
                     </div>
                 </div>
               
            </div>
            <!-- TRANSACTION PAYMENT APPROVAL END -->
			
			<!-- JL DUE LIST STARTS -->
            
			<?php if($row_count_jl_due_list > 0)
			{?>
			<div class="panel panel-custom-danger">
			<?php } else {?>
            <div class="panel panel-custom-success">
            <?php } ?>
				<div class="panel-heading panel-heading-custom-grey">
			
			     <a data-toggle="collapse" data-parent="#accordion" href="#collapse6" style="text-decoration:none; outline:none;">
			    	 <h4 class="panel-title" style="text-align:center;">
        		 		<span style="color:#FFFFFF;">Jewel Loan Recent Due List</span>
				     </h4>
			     </a>
			   </div>
		     	<div id="collapse6" class="panel-collapse collapse ">
                   <div class="rm-dashboard-transaction-approval">
                    		
                            <div class="row">
                              <!-- Second Row -->
                              <div class="col-md-12">
                                    <table id="designation-details" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                      <thead>
                                        <tr class="header-row">
                                          <th width="5%">SlNo</th>
										  <th width="10%">JL A/C No</th>
                                          <th width="10%">EQNO</th>
                                          <th width="14%">Name</th>
                                          <th width="10%">Phone</th>
                                          <th width="15%">Address</th>
                                          <th width="9%">Due Date</th>
                                          <th width="10%">Amount (Dues)</th>
                                          <th width="7%">Interest (Dues)</th>
										  <th width="10%">Total (Dues)</th>
                                        </tr>
                                      </thead>
                                      <tbody class="table-coloum-style">
                                      <?php echo $jl_due_list;?>
                                      </tbody>
                                    </table>
                              </div>
                            <!-- Page Code End -->
                          </div>
                     </div>
                 </div>
               
            </div>
            <!-- JL DUE LIST END -->
			<!-- JL DUE  EXCEED LIST STARTS -->
            
			<?php if($row_count_jl_exceed_due_list > 0)
			{?>
			<div class="panel panel-custom-danger">
			<?php } else {?>
            <div class="panel panel-custom-success">
            <?php } ?>
				<div class="panel-heading panel-heading-custom-grey">
			
			     <a data-toggle="collapse" data-parent="#accordion" href="#collapse7" style="text-decoration:none; outline:none;">
			    	 <h4 class="panel-title" style="text-align:center;">
        		 		<span style="color:#FFFFFF;">Jewel Loan Accounts Exceed Due Date</span>
				     </h4>
			     </a>
			   </div>
		     	<div id="collapse7" class="panel-collapse collapse ">
                   <div class="rm-dashboard-transaction-approval">
                    		
                            <div class="row">
                              <!-- Second Row -->
                              <div class="col-md-12">
                                    <table id="designation-details" class="table-striped table-bordered" cellspacing="0" width="100%">
                                      <thead>
                                        <tr class="header-row">
                                          <th width="3%">SlNo</th>
										  <th width="10%">JL A/C No</th>
                                          <th width="10%">EQNO</th>
                                          <th width="12%">Name</th>
                                          <th width="10%">Phone</th>
                                          <th width="10%">Address</th>
                                          <th width="9%">Due Date</th>
                                          <th width="10%">Amount (Dues)</th>
                                          <th width="7%">Interest (Dues)</th>
										   <th width="4%">Def. Int.</th>
										  <th width="10%">Total (Dues)</th>
										  <th width="5%">Days Exceed Due Date</th>
                                        </tr>
                                      </thead>
                                      <tbody class="table-coloum-style">
                                      <?php echo $jl_exceed_due_list;?>
                                      </tbody>
                                    </table>
                              </div>
                            <!-- Page Code End -->
                          </div>
                     </div>
                 </div>
               
            </div>
            <!-- JL DUE EXCEED LIST END -->
			<!-- JL GRACE PERIOD  EXCEED LIST STARTS -->
			<?php if($row_count_jl_exceed_grace_period > 0)
			{?>
			<div class="panel panel-custom-danger">
			<?php } else {?>
            <div class="panel panel-custom-success">
            <?php } ?>
				<div class="panel-heading panel-heading-custom-grey">
			
			     <a data-toggle="collapse" data-parent="#accordion" href="#collapse8" style="text-decoration:none; outline:none;">
			    	 <h4 class="panel-title" style="text-align:center;">
        		 		<span style="color:#FFFFFF;">Jewel Loan Accounts Exceed Grace Period</span>
				     </h4>
			     </a>
			   </div>
		     	<div id="collapse8" class="panel-collapse collapse ">
                   <div class="rm-dashboard-transaction-approval">
                    		
                            <div class="row">
                              <!-- Second Row -->
                              <div class="col-md-12">
                                    <table id="designation-details" class="table-striped table-bordered" cellspacing="0" width="100%">
                                      <thead>
                                        <tr class="header-row">
                                          <th width="3%">SlNo</th>
										  <th width="10%">JL A/C No</th>
                                          <th width="10%">EQNO</th>
                                          <th width="12%">Name</th>
                                          <th width="10%">Phone</th>
                                          <th width="10%">Address</th>
                                          <th width="9%">Due Date</th>
                                          <th width="10%">Amount (Dues)</th>
                                          <th width="7%">Interest (Dues)</th>
										   <th width="4%">Def. Int.</th>
										  <th width="10%">Total (Dues)</th>
										  <th width="5%">Days Exceed Grace Period</th>
                                        </tr>
                                      </thead>
                                      <tbody class="table-coloum-style">
                                      <?php echo $jl_exceed_grace_period;?>
                                      </tbody>
                                    </table>
                              </div>
                            <!-- Page Code End -->
                          </div>
                     </div>
                 </div>
               
            </div>
            <!-- JL GRACE PERIOD EXCEED LIST END -->
            <br /><br />
             <div class="row">
                <div class="col-md-4">
                	<a href="share-list.php">
                    <div class="panel panel-info ">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-ok dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $new_shares; ?></div>
                                    <div class="dashboard-content-title">New Share Holders</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="rd-account-list.php">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-ok dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $new_rd; ?></div>
                                    <div class="dashboard-content-title">New RD</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                    </a>
                </div>
                
                
                <div class="col-md-4">
                	<a href="fd-accounts-list.php">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-ok dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $new_fd; ?></div>
                                    <div class="dashboard-content-title">New FD</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="sb-accounts-list.php">
                    <div class="panel panel-warning ">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                   <i class="glyphicon glyphicon-ok dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $new_sd; ?></div>
                                    <div class="dashboard-content-title">New SD</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                    </a>
                </div>
                 <div class="col-md-4">
                    <a href="jl-accounts-list.php">
                    <div class="panel panel-success shadow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-ok dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $new_jl; ?></div>
                                    <div class="dashboard-content-title">New Jewel Loan</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                    </a>
                </div>
                
                
                 <div class="col-md-4">
                    <a href="#">
                    <div class="panel panel-danger shadow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-inr" style="font-size:64px"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $cash_opening_balance; ?></div>
                                    <div class="dashboard-content-title">Cash - Opening Balance</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                    </a>
                </div>
              </div>
              <div class="row">
                  <div class="col-md-4">
                    <a href="#">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                  <i class="fa fa-inr" style="font-size:64px"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo number_format($collection_of_cheques_in,2); ?></div>
                                    <div class="dashboard-content-title">Cheque Collection - In</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                    </a>
                </div>
                
                 <div class="col-md-4">
                    <a href="#">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                   <i class="fa fa-inr" style="font-size:64px"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo number_format($collection_of_cheques_out,2); ?></div>
                                    <div class="dashboard-content-title">Cheque Collection - Out</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                    </a>
                </div>
                 <div class="col-md-4">
                   <a href="#">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-inr" style="font-size:64px"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo $cash_current_balance; ?></div>
                                    <div class="dashboard-content-title">Cash In Hand</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                   </a>
                </div>
             </div>
             <br />
             <div class="row">
                
                <div class="col-md-12">
                    <a href="#">
                    <!--<div class="panel panel-danger">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-files-o" style="font-size:64px"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">5</div>
                                    <div class="dashboard-content-title">Last FIVE Transactions</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                        	  </div>

                    </div>-->
                </div>
                </a>
            </div>
            <!-- /.row -->
            <!--<div class="row">
			 	<div class="col-md-12">
                	<div class="email-form">
                    <label class="send-mail"><i class="glyphicon glyphicon-envelope"></i>   Send Mail </label>
					<form name="send-mail" action="" method="post" enctype="multipart/form-data">
                    	 <input type="email" name="email" class="form-control" placeholder="Enter To E-Mail Id" required />
                        <input type="text" name="subject" class="form-control" id="subject" placeholder="Enter Subject" required />
                        <textarea class="ckeditor" name="editor"  id="ckeditor" required >
                       </textarea>
                       <div style="text-align:right;">
                        <button type="submit" class="btn btn-primary send" value="Send" >Send <i class="glyphicon glyphicon-circle-arrow-right"></i></button></div>
                    </form>
                    </div>
				</div>
			</div>-->
<!-- /#Page wrapper -->
<?php 
//print $_SESSION['dashboard'];
?>
</div><?php include_once "footer.php"; ?>
</div>
<!-- /#wrapper -->
<!-- jQuery -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/alertify.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="js/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="js/mpdn.js"></script>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<!--<script src="js/jquery.dataTables.js"></script>
<script src="js/designation.js"></script>-->
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/bootstrap-combobox.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.combobox').combobox();
	idleMax = <?php echo $_SESSION['TimeIdleMax']; ?>;// Logout after 10 minutes of IDLE
		idleTime = 1;
		var idleInterval = setInterval("timerIncrement()", 40000); 
		$(this).mousemove(function (e) {idleTime = 0;});
		$(this).keypress(function (e) {idleTime = 0;});
  }); 
  
  function timerIncrement() {
		idleTime = idleTime + 1;
		if (idleTime > idleMax) {
			 document.location.href = "logout.php";
		}
	}

/* Browser Close Event START */
var unloaded = false;
$(window).on('beforeunload', unload);
$(window).on('unload', unload);  
function unload(){
    if(!unloaded){
        $('body').css('cursor','wait');
        $.ajax({
            type: 'get',
            async: false,
            url: '../php/clear_current_user.php?uid=<?php echo $_SESSION['UserId'];?>&amodule=1',
            success:function(){ 
                unloaded = true; 
                $('body').css('cursor','default');
            },
            timeout: 5000
        });
    }
}
/* Browser Close Event END */
function manager_approve(id)
{
		alertify.confirm('Confirm Message','Are you sure to Approve this Transaction',
		function()
		{
			var transid = id; 
			$.ajax({
				   type:"POST",
				   url:"transaction-approveby-manager.php",
				   data:"transid="+transid,
				   
				   success : function(data)
							 {
									$('#'+id).closest("tr").remove();
									alertify.success('Transaction Approved')
							 }
					});
		},
		function()
		{
			alertify.error('Cancelled')
		
		}
		
		);
				
}
</script>

</body>
</html>
