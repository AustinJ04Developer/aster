$(function() {
    $('#side-menu').metisMenu();
});
$(document).ready(function()  
{
/* Tool Tip */
 $('[data-toggle="tooltip"]').tooltip();
 
});			
function loadimage()
{ 
	if(document.getElementById("username").value == "" || document.getElementById("password").value == "")
	{
		return false;
	}
	else
	{
		document.getElementById("loading").style.display = "block";
	}
}
$(".allow_decimal").on("input", function(evt) {
	var self = $(this);
	self.val(self.val().replace(/[^0-9\.]/g, ''));
	if ((evt.which != 46 || self.val().indexOf('.') != -1) && (evt.which < 48 || evt.which > 57)) 
	{
	 evt.preventDefault();
	}

});
//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
    $(window).bind("load resize", function() {
        topOffset = 50;
        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    var element = $('ul.nav a').filter(function() {
        return this.href == url || url.href.indexOf(this.href) == 0;
    }).addClass('active').parent().parent().addClass('in').parent();
    if (element.is('li')) {
        element.addClass('active');
    }
});
var password = document.getElementById("new-password");
var confirm_password = document.getElementById("confirm-password");

function validatePassword(){
  if(password.value != confirm_password.value) {
    confirm_password.setCustomValidity("Passwords Don't Match");
  } else {
    confirm_password.setCustomValidity('');
  }
}

/*password.onchange = validatePassword;
confirm_password.onkeyup = validatePassword;*/

function getXMLHTTP() { //fuction to return the xml http object
var xmlhttp=false;	
		try{
			xmlhttp=new XMLHttpRequest();
		}
		catch(e)	{		
			try{			
				xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e){
				try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				}
				catch(e1){
					xmlhttp=false;
				}
			}
		}
		//alert(xmlhttp); 	
		return xmlhttp;
    }

  function validateNumber(evt)  {
		var charCode = (evt.which) ? evt.which : event.keyCode
		if (charCode == 17 )   {
			return false;
		}
		if ( charCode > 31 && (charCode < 48 || charCode > 57)){
		 return false;
		}
	}

$(window).load(function() { // can also try on document ready
    $('input[autocomplete]').removeAttr('autocomplete');
});

/* Modal Popup */
function modalClick(id)
{
var currentMCId = document.getElementById(id).value;
var $modal = $('#load_popup_modal_show_id');
$modal.load('load-modal.php',{'id1': currentMCId, 'id2': 1},
//$modal.load('load-modal-system-drawing.php',{'id1': currentMCId, 'id2': 1},
function(){
$modal.modal('show');
});
}
$(document).ready(function()  
{
// All Combo box codes here
$(".state").combobox();
$(".state").attr("placeholder", "Select State");
$(".branch").combobox();
$(".branch").attr("placeholder", "Select Branch");
$(".dbfield").combobox();
$(".dbfield").attr("placeholder", "Select Search Field Name");
$(".dbfield").focus();
$(".eqno").combobox();
$(".eqno").attr("placeholder", "Select EQNO");
$(".txnfrom").combobox();
$(".txnfrom").attr("placeholder", "Select Transfer From Account");
$(".sdaccount-number").combobox();
$(".sdaccount-number").attr("placeholder","Select SD Account Number");
$(".sdaccount-number").focus();
$(".sdaccount-number").focus();
$(".fdaccount-number").combobox();
$(".fdaccount-number").attr("placeholder","Select FD Account Number");
$(".fdaccount-number").focus();
$(".rdperiod").combobox();
$(".rdperiod").attr("placeholder","Select RD Period");
$(".rdperiod").focus();
$(".rdamount").combobox();
$(".rdamount").attr("placeholder","Select RD Amount");
$(".rdagent").combobox();
$(".rdagent").attr("placeholder","Select RD Agent Name");
$(".account-head").combobox();
$(".account-head").attr("placeholder","Select Account Head");
$(".account-head").focus();
$(".account-sub-head").combobox();
$(".account-sub-head").attr("placeholder","Select Account Sub Heading");
$(".rdaccount-number").combobox();
$(".rdaccount-number").attr("placeholder","Select RD Account No");
$("#selectscheme").focus();
	$("#eqno1").change(function() 
	{ 
		var eqno = $("#eqno1").val();
		$('#name1').val('');
		$('#father1').val('');
		$('#street1').val('');
		if(eqno.length > 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/search-share-details.php",  
		data: "eqno="+eqno,  
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
			$('#name1').val(array_share[0]);
			$('#father1').val(array_share[1]);
			$('#street1').val(array_share[2]);
		}  
		} 
		}); 
		}
		return false;
	}); // ajax function end
	$("#eqno2").change(function() 
	{ 
		var eqno = $("#eqno2").val();
		$('#name2').val('');
		$('#father2').val('');
		$('#street2').val('');
		if(eqno.length > 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/search-share-details.php",  
		data: "eqno="+eqno,  
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
			$('#name2').val(array_share[0]);
			$('#father2').val(array_share[1]);
			$('#street2').val(array_share[2]);
		}  
		} 
		}); 
		}
		return false;
	}); // ajax function end
	$("#eqno3").change(function() 
	{ 
		var eqno = $("#eqno3").val();
		$('#name3').val('');
		$('#father3').val('');
		$('#street3').val('');
		if(eqno.length > 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/search-share-details.php",  
		data: "eqno="+eqno,  
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
			$('#name3').val(array_share[0]);
			$('#father3').val(array_share[1]);
			$('#street3').val(array_share[2]);
		}  
		} 
		}); 
		}
		return false;
	}); // ajax function end
	
	/*$("#period").change(function() 
	{ 
		var period = $("#period").val();
		var amount = $("#fd-amount").val();
		var issenior = $("#is-senior").val();
		var tax = $("#tax-percentage").val();
		//alert(amount);
		var eqno = $("#eqno").val();
		var tax = 1;
		if(document.getElementById('form-g').checked) {
		  tax = 0;
		}else if(document.getElementById('form-h').checked) {
		  tax = 0;
		}
		if(period.length > 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/find-maturity-date.php",  
		data: { period : period, amount : amount, tax : tax, eqno : eqno, issenior : issenior },
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
			$('#maturity-date').val(array_share[0]);
			$('#monthly-interest').val(array_share[1]);
			$('#monthly-tax').val(array_share[2]);
			$('#inerest-percentage').val(array_share[3]);
			$('#tax-percentage').val(array_share[4]);
			if(array_share[2] > 0)
				document.getElementById("tax").checked = true;
			else
				document.getElementById("no-tax").checked = true;
		}  
		} 
		}); 
		}
		return false;
	}); // ajax function end*/
});	
	
$("#transfer-share-amount").change(function() 
{ 
		var amount = $("#transfer-share-amount").val();
		var acctype = $("#transfer-account-type").val();
		var accno = $("#transfer-from-account").val();
		
	if(accno != 0 && acctype>0)
	{
		if(!accno)
		{
			accno = $("#account-no-temp").val();
		}
		var msgbox = $("#status");
		$("#status").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking Fund availability...');
		$.ajax({  
			type: "POST",  
			url: "../ajax/transfer-amount-availability.php",  
			data: {acctype : acctype, accno : accno, amount : amount},
			success: function(msg){ 
			if(msg == 'OK')
			{ 
				$("#transfer-share-amount").removeClass("red");
				$("#transfer-share-amount").addClass("green");
				msgbox.html('<img src="images/available.png" align="absmiddle">');
				$('#transfer-share-amount').val(amount);
				$("#status").html('<span></span>');
			}  
			else  
			{  
				$("#transfer-share-amount").removeClass("green");
				$("#transfer-share-amount").addClass("red");
				$('#transfer-share-amount').val("");
				$('#transfer-share-amount').focus();
				$("#status").html('<span style="color:red">Insuffient Fund</span>');
			}  
		   } 
		  }); 
		return false;
		}
		});
function getAccountNos(val){
		var strURL="../ajax/get-account-no.php?accid="+val;
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('transfer-from-account').innerHTML=req.responseText;
						$('#transfer-from-account').combobox('refresh');
						/*document.getElementById('sub-system').options.add(new Option("1", "SubSystem1"))*/
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}
function getSDBalance(val){
		document.getElementById("transfer-share-amount").value = 0;
		var strURL="../ajax/get-sd-amount.php?accid="+val;
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('sd-amount').innerHTML="Current Balance : "+req.responseText;
						/*$('#transfer-from-account').combobox('refresh');*/
						/*document.getElementById('sub-system').options.add(new Option("1", "SubSystem1"))*/
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}
function getSDDetails(val){
		var strURL="../ajax/get-sd-account-details.php?accid="+val;
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						str = req.responseText;
						var sd_array = str.split("|");
						document.getElementById('transfer-to-eqno').value=sd_array[0];
						document.getElementById('transfer-to-name').value=sd_array[1];
						/*$('#transfer-from-account').combobox('refresh');*/
						/*document.getElementById('sub-system').options.add(new Option("1", "SubSystem1"))*/
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}
function getFDDetails(val){
		var strURL="../ajax/get-fd-account-details.php?accid="+val;
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						str = req.responseText;
						var fd_array = str.split("|");
						document.getElementById('fd-eqno').value=fd_array[0];
						document.getElementById('fd-name').value=fd_array[1];
						/*$('#transfer-from-account').combobox('refresh');*/
						/*document.getElementById('sub-system').options.add(new Option("1", "SubSystem1"))*/
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}
function getXMLHTTP() { //fuction to return the xml http object
var xmlhttp=false;	
		try{
			xmlhttp=new XMLHttpRequest();
		}
		catch(e)	{		
			try{			
				xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e){
				try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				}
				catch(e1){
					xmlhttp=false;
				}
			}
		}
		//alert(xmlhttp); 	
		return xmlhttp;
    }
/*transfer-account-type*/

function fdtaxcalculation()
{
var period = $("#period").val();
		var amount_get = $("#fd-amount").val();
		var tax = $("#tax-percentage").val();
		var issenior = $("#is-senior").val();
		var currentdate = $("#current-dates").val();
		var tax = 1;
		if(document.getElementById('form-g').checked) {
		  tax = 0;
		}else if(document.getElementById('form-h').checked) {
		  tax = 0;
		}
		if(period.length > 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/find-maturity-date.php",  
		data: { period : period, totamount : amount_get, tax : tax, issenior : issenior,currentdate : currentdate  },
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
			/*alert(msg);*/
			var totint = (((array_share[6] * (array_share[3]/100))) / 12);
			
			totint = totint * array_share[5];
			$('#maturity-date').val(array_share[0]);
			$('#monthly-interest').val(Math.round(totint));
			$('#monthly-tax').val(array_share[2]);
			$('#inerest-percentage').val(array_share[3]);
			if(array_share[4])
			 $('#tax-percentage').val(array_share[4]);
			else
			 $('#tax-percentage').val(0);	
			if(array_share[2] > 0)
				document.getElementById("tax").checked = true;
			else
				document.getElementById("no-tax").checked = true;
		}  
		} 
		}); 
		}
		return false;
}
function fdtaxcalculationforedit()
{
var period = $("#period").val();
		var amount_get = $("#fd-amount").val();
		var tax = $("#tax-percentage").val();
		var issenior = $("#is-senior").val();
		var currentdate = $("#current-dates").val();
		var tax = 1;
		if(document.getElementById('form-g').checked) {
		  tax = 0;
		}else if(document.getElementById('form-h').checked) {
		  tax = 0;
		}
		if(period.length > 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/find-maturity-date.php",  
		data: { period : period, totamount : amount_get, tax : tax, issenior : issenior,currentdate : currentdate  },
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
		/*	alert(msg);*/
			var totint = (((array_share[6] * (array_share[3]/100))) / 12);
		/*	alert(array_share[3]);*/
			totint = totint * array_share[5];
			$('#maturity-date').val(array_share[0]);
			$('#monthly-interest').val(Math.round(totint));
			$('#monthly-tax').val(array_share[2]);
			$('#inerest-percentage').val(array_share[3]);
			$('#tax-percentage').val(array_share[4]);
			if(array_share[2] > 0)
				document.getElementById("tax").checked = true;
			else
				document.getElementById("no-tax").checked = true;
		}  
		} 
		}); 
		}
		return false;
}
/* Delete Alert  */
$(document).on('click', "a#deleteAlert", function(ev) {

var href = $(this).attr('href');
		if (!$('#dataConfirmModal').length) {
			$('#page-wrapper').append('<div id="dataConfirmModal" class="modal" role="dialog" aria-labelledby="dataConfirmLabel" aria-hidden="true"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h3 id="dataConfirmLabel">Please Confirm</h3></div><div class="modal-body">Are you sure you want to delete?</div><div class="modal-footer"><a class="btn btn-primary" id="dataConfirmOK">OK</a><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button></div></div>');
		} 

		$('#dataConfirmModal').find('.modal-body').text($(this).attr('data-confirm'));
		$('#dataConfirmOK').attr('href', href);
		$('#dataConfirmModal').modal({show:true});
  	    return false;      
	    
});
/******/
function showHideSecondaryDetails(id)
{
 var x = document.getElementById("secondary-account");
   if (x.style.display === "none" || x.style.display === "") {
    x.style.display = "block";
/*	document.getElementById("secondary-name").required = true;
	document.getElementById("address-s1").required = true;
	document.getElementById("address-s2").required = true;
	document.getElementById("address-s3").required = true;
	document.getElementById("third-name").required = true;
	document.getElementById("address-t1").required = true;
	document.getElementById("address-t2").required = true;
	document.getElementById("address-t3").required = true;
*/	
  } else {
    x.style.display = "none";
	/*document.getElementById("secondary-name").removeAttribute("required");
	document.getElementById("address-s1").removeAttribute("required");
	document.getElementById("address-s2").removeAttribute("required");
	document.getElementById("address-s3").removeAttribute("required");
	document.getElementById("third-name").removeAttribute("required");
	document.getElementById("address-t1").removeAttribute("required");
	document.getElementById("address-t2").removeAttribute("required");
	document.getElementById("address-t3").removeAttribute("required");*/
  }
}

function showHideSecondaryDetailsJoint(id)
{
 var x = document.getElementById("secondary-account");
      x.style.display = "block";
}	
function showHideSecondaryDetailsSingle(id)
{
 var x = document.getElementById("secondary-account");
    x.style.display = "none";
}	
function showHideMinor(age)
{
	//var age = parseInt(document.getElementById("age").value);
	if(age <= 17)
	{
		document.getElementById("minor").style.display="block";
		document.getElementById("represented-by").required = true;
		document.getElementById("represented-by-relation").required = true;
	}
	else
	{
		document.getElementById("minor").style.display="none";
		document.getElementById("represented-by").removeAttribute("required");
		document.getElementById("represented-by-relation").removeAttribute("required");
	}
}
function showHideChequeTransfer(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque/transfer */
{
var cheq = document.getElementById("cheque");
 var trans = document.getElementById("transfer");
 var sd = document.getElementById("sdcash");
 
 if(id == 2) // Cheque
 {
//	 alert("aaa");
	// cheq.checked = true;
	 $('#selected-transfer').attr('checked',false);
	 $('#selected-cheque').attr('checked',true);
	 $('#cash').attr('checked',false);
	 cheq.style.display = "block";
	 trans.style.display = "none";
	 document.getElementById("current-date4").value = '';
	 document.getElementById("transfer-account-type").value = '';
	 document.getElementById("transfer-from-account").value = '';
/*	 document.getElementById("transfer-to-account").value = '';*/
	 document.getElementById("transfer-share-amount").value = '';
	/* document.getElementById("current-date1").required = true;*/
	 document.getElementById("current-date2").required = true;
	 var today = new Date();
	 var dd = String(today.getDate()).padStart(2, '0');
	 var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	 var yyyy = today.getFullYear();
	 today = dd+ '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 document.getElementById("current-date1").value = today;
	 document.getElementById("bank").required = true;

	 document.getElementById("cheque-number").required = true;
	/* document.getElementById("current-date3").required = true;*/
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required = true;
	 
	 document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	/* document.getElementById("transfer-to-account").removeAttribute("required");*/
	 document.getElementById("transfer-share-amount").removeAttribute("required");
	/* alert(document.getElementById("transfer-share-amount").value);*/
	
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	 $('#selected-transfer').attr('checked',true);
	 $('#selected-cheque').attr('checked',false);
	 $('#cash').attr('checked',false);
//	document.getElementById("selected-transfer").checked = true;
	trans.style.display = "block";
	cheq.style.display = "none";
	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	document.getElementById("transfer-account-type").required = true;
	document.getElementById("transfer-from-account").required = true;
	/*document.getElementById("transfer-to-account").required = true;*/
	document.getElementById("transfer-share-amount").required = true;
 }
 else // cash
 {
	 $('#selected-transfer').attr('checked',false);
	 $('#selected-cheque').attr('checked',false);
	 $('#cash').attr('checked',true);
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 /*sd.style.display = "block";*/
	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	  document.getElementById("current-date4").value = '';
	 document.getElementById("transfer-account-type").value = '';
	 document.getElementById("transfer-from-account").value = '';
	/* document.getElementById("transfer-to-account").value = '';*/
	 document.getElementById("transfer-share-amount").value = '';
	 
	 
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
/*	 document.getElementById("transfer-to-account").removeAttribute("required");*/
	 document.getElementById("transfer-share-amount").removeAttribute("required");
	/* alert(document.getElementById("transfer-share-amount").value);
	 document.getElementById("sd-amount").required = true;*/
	  
 }
}	

function showHideChequeTransferJLEdit(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque/transfer */
{
var cheq = document.getElementById("cheque");
 var trans = document.getElementById("transfer");
 var sd = document.getElementById("sdcash");
 
 if(id == 2) // Cheque
 {
//	 alert("aaa");
	 cheq.style.display = "block";
	 trans.style.display = "none";
	 document.getElementById("current-date4").value = '';
	 document.getElementById("transfer-account-type").value = '';
	 document.getElementById("transfer-from-account").value = '';
/*	 document.getElementById("transfer-to-account").value = '';*/
	 document.getElementById("transfer-share-amount").value = '';
	/* document.getElementById("current-date1").required = true;*/
	 document.getElementById("current-date2").required = true;
	 var today = new Date();
	 var dd = String(today.getDate()).padStart(2, '0');
	 var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	 var yyyy = today.getFullYear();
	 today = dd+ '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 document.getElementById("current-date1").value = today;
	 document.getElementById("bank").required = true;

	 document.getElementById("cheque-number").required = true;
	/* document.getElementById("current-date3").required = true;*/
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required = true;
	 
	 document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amounts").removeAttribute("required");
	/* alert(document.getElementById("transfer-share-amount").value);*/
	
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	trans.style.display = "block";
	cheq.style.display = "none";
	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	document.getElementById("transfer-account-type").required = true;
	document.getElementById("transfer-from-account").required = true;
	/*document.getElementById("transfer-to-account").required = true;*/
	document.getElementById("transfer-share-amount").required = true;
	document.getElementById("transfer-from-account").value = document.getElementById("jl-acc-no").value;
 }
 else // cash
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 /*sd.style.display = "block";*/
	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	  document.getElementById("current-date4").value = '';
	 document.getElementById("transfer-account-type").value = '';
	 document.getElementById("transfer-from-account").value = '';
	/* document.getElementById("transfer-to-account").value = '';*/
	 document.getElementById("transfer-share-amount").value = '';
	 
	 
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amounts").removeAttribute("required");
	/* alert(document.getElementById("transfer-share-amount").value);
	 document.getElementById("sd-amount").required = true;*/
	  
 }
}
function showHideCheque(id) /*This function is used to show or hide cheque related fields */
{
	var age = document.getElementById("age").value;
	if(age > 17)
	{
		document.getElementById("minor").style.display="block";
		document.getElementById("represented-by").attr("required");

		document.getElementById("represented-by-relation").attr("required");
	}
	else
	{
		document.getElementById("minor").style.display="none";
		document.getElementById("represented-by").removeAttribute("required");
		document.getElementById("represented-by-relation").removeAttribute("required");
	}
}
function getAge(dateString) {
    var today = new Date();
    var birthDate = new Date(dateString);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}
function findShareValue(shares)	/* This function is used to find the total share amount and used in share master page */
{
	var share_value = document.getElementById("default-share-value").value;
	var total_share_value = share_value * shares;
	document.getElementById("share-amount").value = total_share_value;
}
/* Show / Hide Joint Account for Fixed, Rd, Sd */
function showHideJoinedAccount(id)
{
 var block_value = document.getElementById(id).value;
// alert(block_value);
 var x = document.getElementById("joined-account");
 if(block_value == 1)
 	x.style.display = "none";
 else
    x.style.display = "block";
/*	document.getElementById("secondary-name").removeAttribute("required");
	document.getElementById("address-s1").removeAttribute("required");
	document.getElementById("address-s2").removeAttribute("required");
	document.getElementById("address-s3").removeAttribute("required");
*/
}
function showHideJoinedAccountSingle(id)
{
 var x = document.getElementById("joined-account");
  x.style.display = "none";
  document.getElementById("eqno1").removeAttribute("required");
/*	document.getElementById("secondary-name").removeAttribute("required");
	document.getElementById("address-s1").removeAttribute("required");
	document.getElementById("address-s2").removeAttribute("required");
	document.getElementById("address-s3").removeAttribute("required");
*/
}
function validateNewFDForm()
{

 var totalamount = Number(document.getElementById("fd-amount").value);
 if(document.getElementById("selected-transfer").checked)
 {
	 var transferamount = Number(document.getElementById("transfer-share-amount").value);
	 if(parseFloat(transferamount) != parseFloat(totalamount))
	 {
		alert("Please check fixed amount and transfer amount");	 
		return false;
	 }
 }
 else if(document.getElementById("selected-cheque").checked)
 {
	var chequeamount = Number(document.getElementById("transfer-cheque-amount").value);
	if(parseFloat(chequeamount) != parseFloat(totalamount))
	 {
		alert("Please check fixed amount and cheque amount");	 
		return false;
	 }
 }
 else if(document.getElementById('cash').checked) 
 {
	if(parseFloat(fdmaturedamount) > parseFloat(cashinhand))
	 {
		alert("Unable to process... Because insufficient cash");	 
		return false;
	 }
 }
 return true;
}
function validateNewShareForm()
{
 var chequeamount = Number(document.getElementById("transfer-cheque-amount").value);
 var transferamount = Number(document.getElementById("transfer-share-amount").value);
 var totalamount = Number(document.getElementById("share-amount").value);
 var totamt = Number(chequeamount) + Number(transferamount);
 if(document.getElementById("selected-transfer").checked)
 {
	 if(parseFloat(transferamount) != parseFloat(totalamount))
	 {
		alert("Please check share amount and transfer amount");	 
		return false;
	 }
 }
 else if(document.getElementById("selected-cheque").checked)
 {
	 if(parseFloat(chequeamount) != parseFloat(totalamount))
	 {
		alert("Please check share amount and cheque amount");	 
		return false;
	 }
 }
 return true;
}

function validateJLReceipt()
{
 var chequeamount = Number(document.getElementById("transfer-cheque-amount").value);
 var transferamount = Number(document.getElementById("transfer-share-amount").value);
 var totalamount = Number(document.getElementById("current-amount").value);
 var totamt = Number(chequeamount) + Number(transferamount);
 if(document.getElementById("selected-transfer").checked)
 {
	 if(parseFloat(transferamount) != parseFloat(totalamount))
	 {
		alert("Please check loan amount and transfer amount");	 
		return false;
	 }
 }
 else if(document.getElementById("selected-cheque").checked)
 {
	 if(parseFloat(chequeamount) != parseFloat(totalamount))
	 {
		alert("Please check loan amount and cheque amount");	 
		return false;
	 }
 }
 return true;
}

/* Show / Hide Joint Account for Fixed, Rd, Sd */
function showHideTax(id)
{
    if(document.getElementById('no-tax').checked)
	{
	  document.getElementById('tax-percentage').value=0;
	  document.getElementById('monthly-tax').value=0;
	}
	else if(document.getElementById('tax').checked)
	{
	  //Female radio button is checked
	}

}	

function taxPercentageCalculation(amount) // On click radio button the tax is calculated
{ 
		if(amount.length > 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/find-tax.php",  
		data: "amount="+amount,  
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
			$('#tax-percentage').val(array_share[0]);
		}  
		} 
		}); 
		}
		return false;
}
function modalRMViewModifications(id)
{
//	alert(srid	);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-same-date-modifications.php',{'id': id},
function(){
$modal.modal('show');
});
}
function modalRMViewModificationsAndApproval(id)
{
//	alert(srid	);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-previous-date-modifications.php',{'id': id},
function(){
$modal.modal('show');
});
}
function modalFDInterestTransfer(fdid,eqno,month,year,interest,tax,date,name)
{
//	alert(name);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-fd-interest-transfer.php',{'fdid': fdid,'eqno': eqno,'month': month,'year': year,'interest': interest,'tax': tax,'date': date,'name': name},
function(){
$modal.modal('show');
});
}
function modalFDInterestTransferAsCash(fdid,eqno,month,year,interest,tax,date,name)
{
//	alert(name);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-fd-interest-transfer-as-cash.php',{'fdid': fdid,'eqno': eqno,'month': month,'year': year,'interest': interest,'tax': tax,'date': date,'name': name},
function(){
$modal.modal('show');
});
}
function modalOthersShowAllAccounts(ttype)
{
//	alert(name);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-all-other-accounts.php',{'ttype': ttype},
function(){
$modal.modal('show');
});
}
/*function showHideCashChequeSD(id)
{
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");

 if(id == 2)
 {
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";
	
	 document.getElementById("sd-amount").removeAttribute("required"); // CASH FIELD
	  var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 document.getElementById("current-date1").required = true;   // CHEQUE FIELD
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	 document.getElementById("current-date3").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amount").removeAttribute("required");
 }
 else if(id == 3) // Transfer
 {
   
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	
	document.getElementById("transfer-account-type").required = true;
	
	document.getElementById("transfer-from-account").required = true;
	
	document.getElementById("transfer-share-amount").required = true;
	document.getElementById("sd-amount").removeAttribute("required");
	
	document.getElementById("transfer-to-bank-account").removeAttribute("required");
 
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 document.getElementById("sd-amount").required = true;
	  
 }
}	*/
function showHideCashChequeSDPayment(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque in SD*/
{
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");

 if(id == 2)
 {
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";
	
	 document.getElementById("sd-amount").removeAttribute("required"); // CASH FIELD
	  var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 document.getElementById("current-date1").required = true;   // CHEQUE FIELD
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	 document.getElementById("current-date3").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amount-payment").removeAttribute("required");
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	
	document.getElementById("transfer-account-type").required = true;
	
	document.getElementById("transfer-from-account").required = true;
	
	document.getElementById("transfer-share-amount-payment").required = true;
	document.getElementById("sd-amount").removeAttribute("required");
	
	document.getElementById("transfer-to-bank-account").removeAttribute("required");
 
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amount-payment").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 document.getElementById("sd-amount").required = true;
	  
 }
}	
/* Update by Babu*/
function modalRMViewSDcurrentModifications(id,sdid)
{
	
//	alert(srid	);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-same-date-sd-modifications.php?id='+id+'&sdid='+sdid,
function(){
$modal.modal('show');
});
}

function modalRMViewSDOldModifications(id,sdid)
{
//	alert(srid	);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-previous-date-sd-modifications.php?id='+id+'&sdid='+sdid,
function(){
$modal.modal('show');
});
}

function modalRMViewSDcurrentTransModifications(id,sdid,tid)
{
	
//	alert(srid	);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-same-date-sd-trans-modifications.php?id='+id+'&sdid='+sdid+'&tid='+tid,
function(){
$modal.modal('show');
});
}

function modalRMViewFDcurrentTransModifications(id,fdid,lfdid)
{
//alert(lfdid);	
//	alert(srid	);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');

$modal.load('popup-same-date-fd-modifications.php?id='+fdid+'&type='+id+'&lid='+lfdid,
function(){
$modal.modal('show');
});
}

function modalRMViewFDTransModifications(id,fdid,ltid)
{
	
//	alert(srid	);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-fd-transaction-modifications.php?id='+ltid,
function(){
$modal.modal('show');
});
}

function modalRMViewSDoldTransModifications(id,sdid,tid,newsdid,amount,transtype)
{
	
	
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('popup-previous-date-sd-trans-modifications.php?id='+id+'&sdid='+sdid+'&tid='+tid+'&newsdid='+newsdid+'&amount='+amount+'&transtype='+transtype,
function(){
$modal.modal('show');
});
}


/*FindAccountNumber($field1)*/

function showHideCashChequeSD(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque in SD*/
{
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");

 if(id == 2)
 {
	
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";
	
	 document.getElementById("sd-amount").removeAttribute("required"); // CASH FIELD
	  var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 //document.getElementById("current-date1").required = true;   // CHEQUE FIELD
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	// document.getElementById("current-date3").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amount").removeAttribute("required");
 
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
//	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 //document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	// document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	// document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	
	document.getElementById("transfer-account-type").required = true;
	
	document.getElementById("transfer-from-account").required = true;
	
	//document.getElementById("transfer-share-amount").required = true;
	document.getElementById("sd-amount").removeAttribute("required");
	
	document.getElementById("transfer-to-bank-account").removeAttribute("required");
 
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 
	// document.getElementById("current-date1").removeAttribute("required");
	 //document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
//	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	/* document.getElementById("transfer-to-account").removeAttribute("required");*/
	 document.getElementById("transfer-share-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 
	 document.getElementById("sd-amount").required = true;
	  
 }
}
function getSDBalanceAmount(val){
		document.getElementById("transfer-share-amount").value = 0;
		var strURL="../ajax/get-sd-amount.php?accid="+val;
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {	
					
						document.getElementById('sd-balance').innerHTML="Current Balance : "+req.responseText;
						/*$('#transfer-from-account').combobox('refresh');*/
						/*document.getElementById('sub-system').options.add(new Option("1", "SubSystem1"))*/
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}
function getSDAccountNos(val,sdid){
	    var currentsdid= parseInt(sdid.val());
		var strURL="../ajax/get-sd-account-no.php?accid="+val+"&sdid="+currentsdid;
	
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('transfer-from-account').innerHTML=req.responseText;
						$('#transfer-from-account').combobox('refresh');
						/*document.getElementById('sub-system').options.add(new Option("1", "SubSystem1"))*/
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}
/*FindAccountNumber($field1)*/
function getSDAccountNosEdit(sdid,fromsdid){
	
	    var currentsdid= parseInt(sdid.val());
		var transferfromsdid= parseInt(fromsdid.val());
		var strURL="../ajax/get-sd-account-no-edit.php?sdid="+currentsdid+"&fromid="+transferfromsdid;
	
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('transfer-from-account').innerHTML=req.responseText;
						$('#transfer-from-account').combobox('refresh');
						/*document.getElementById('sub-system').options.add(new Option("1", "SubSystem1"))*/
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}

function getSDAccountNosPaymentEdit(sdid,fromsdid){
	    var currentsdid= parseInt(sdid.val());
		var transferfromsdid= parseInt(fromsdid.val());
	
		var strURL="../ajax/get-sd-account-no-payment-edit.php?sdid="+currentsdid+"&fromid="+transferfromsdid;
	
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					if (req.status == 200) {						
						document.getElementById('transfer-from-account').innerHTML=req.responseText;
						$('#transfer-from-account').combobox('refresh');
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}

/*FindAccountNumber($field1)*/
$("#sdaccount-number").change(function() 
	{ 
		var sdno = $("#sdaccount-number").val();
		$('#primary-name').val('');
		$('#eq-no').val('');
		if(sdno.length > 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/search-sd-details.php",  
		data: "sdno="+sdno,  
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
			$('#primary-name').val(array_share[0]);
			$('#eq-no').val(array_share[1]);
		}  
		} 
		}); 
		}
		return false;
	}); // ajax function end

function validateNewJLForm()
{
 var chequeamount = Number(document.getElementById("transfer-cheque-amount").value);
 var transferamount = Number(document.getElementById("transfer-share-amounts").value);
 var totalamount = Number(document.getElementById("loanamount").value);
 var totamt = Number(chequeamount) + Number(transferamount);
 var permissible_amount = Number(document.getElementById("permissibleamount").value);
 var valuation_amount = Number(document.getElementById("valuationamount").value);
 var loan_amount = Number(document.getElementById("loanamount").value);
 
if(parseFloat(permissible_amount) > parseFloat(valuation_amount))
{
	alert("Permissible amount should not greater than valuation amount");	 
	return false;
}
if(parseFloat(loan_amount) > parseFloat(permissible_amount))
{
	alert("Loan amount should not greater than Permissible amount");	 
	return false;
}
/* alert(parseFloat(transferamount));
 alert(parseFloat(totalamount));
*/
if(document.getElementById("selected-transfer").checked)
 {
	if(parseFloat(transferamount) != parseFloat(totalamount))
	{
		alert("Please check Loan amount and transfer amount");	 
		return false;
	}
 }
 else if(document.getElementById("selected-cheque").checked)
 {
	if(parseFloat(chequeamount) != parseFloat(totalamount))
	{
		alert("Please check Loan amount and cheque amount");	 
		return false;
	}
 }
 return true;
}

$("#rdperiod").change(function() 
	{ 
		var period = $("#rdperiod").val();
		$('#maturity-date').val('');
		$('#maturity-amount').val('');
		$('#rdperiod-interest').val('');
		$('#period').val('');
		$('#periodid').val('');
		$('#rdamount').data('combobox').clearTarget();
		$('#rdamount').data('combobox').clearElement();	
		$('#rdamount').combobox('refresh');
		$('#transfer-cheque-amount').val('');
		$('#deposit-amount').val('');
		$('#transfer-cheque-amount').val('');
		$('#rdimid').val('');
	
		if(period> 0)
		{
		$.ajax({  
		type: "POST",  
		url: "../ajax/search-rd-details.php",  
		data: "periodid="+period,  
		success: function(msg){ 
		if(msg != '')
		{ 
			var array_share = msg.split('|');
			$('#rdperiod-interest').val(array_share[0]);
			$('#maturity-date').val(array_share[1]);
			$('#period').val(array_share[2]);
			$('#periodid').val(array_share[3]);
			$('#rdimid').val(array_share[4]);
		}  
		} 
		}); 
		}
		return false;
	}); // ajax function end

function getSDBalanceAmountRD(val){
		var strURL="../ajax/get-sd-amount.php?accid="+val;
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {	
					
						document.getElementById('sd-balance').innerHTML="Current Balance : "+req.responseText;
						/*$('#transfer-from-account').combobox('refresh');*/
						/*document.getElementById('sub-system').options.add(new Option("1", "SubSystem1"))*/
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}
	
function showHideCashChequeRD(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque in SD*/
{
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");

 if(id == 2)
 {
	
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";

	 document.getElementById("deposit-amount").removeAttribute("required"); // CASH FIELD
	
	  var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 document.getElementById("current-date1").required = true;   // CHEQUE FIELD
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	 document.getElementById("current-date3").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amount").removeAttribute("required");

	
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	
	document.getElementById("transfer-account-type").required = true;
	
	document.getElementById("transfer-from-account").required = true;

	document.getElementById("transfer-share-amount").required = true;
	document.getElementById("deposit-amount").removeAttribute("required");
	
	document.getElementById("transfer-to-bank-account").removeAttribute("required");
 
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 document.getElementById("rdamount").required = true;
	  
 }
}
function showHideCashChequeRDPay(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque in SD*/
{
	
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");

 if(id == 2)
 {
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";
	
	 document.getElementById("sd-amount").removeAttribute("required"); // CASH FIELD
	  var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 document.getElementById("current-date1").required = true;   // CHEQUE FIELD
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	 document.getElementById("current-date3").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amounts").removeAttribute("required");
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	
	document.getElementById("transfer-account-type").required = true;
	
	document.getElementById("transfer-from-account").required = true;
	
	document.getElementById("transfer-share-amounts").required = true;
	document.getElementById("sd-amount").removeAttribute("required");
	
	document.getElementById("transfer-to-bank-account").removeAttribute("required");
 
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amounts").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 document.getElementById("sd-amount").required = true;
	  
 }
}	
function getAccountSubHead(val) {
		var strURL="get-accountsubhead.php?id="+val;
		if(!val)
		{
			
		$('#account-subhead').data('combobox').clearTarget();
		$('#account-subhead').data('combobox').clearElement();	
		$('#account-subhead').combobox('refresh');
	
		}
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				//alert(req.readyState);
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {	
					//alert(req.status);
						document.getElementById('account-subhead').innerHTML=req.responseText;
						//alert(req.responseText);
						$('#account-subhead').combobox('refresh');
						
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}
function showHideCashChequeOthers(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque in SD*/
{
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");
 if(id == 2)
 {
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";
	
	 document.getElementById("amount").removeAttribute("required"); // CASH FIELD
	  var today = new Date();
	  var dd = String(today.getDate()).padStart(2, '0');
	  var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	  var yyyy = today.getFullYear();
	  today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	/*Remove Cash Fields*/
	 document.getElementById("amount").removeAttribute("required");
	 /* Remove Tramsfer Fields*/
	 document.getElementById("transfer-amount").value = '';
	 document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-amount").removeAttribute("required");
 }
 else if(id == 3) // Transfer
 {
   
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
	 document.getElementById("current-date4").required = true;
	 document.getElementById("transfer-account-type").required = true;
	 document.getElementById("transfer-amount").required = true;
	 
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';

	/*Remove Cash Fields*/
	 document.getElementById("amount").removeAttribute("required");
	 /* Remove Cheque Fields*/
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	/* document.getElementById("current-date4").value =  document.getElementById("current-date").value;*/
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 document.getElementById("amount").required = true;
	/* Remove Cheque Fields*/
	
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 /* Remove Tramsfer Fields*/
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	 document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-amount").removeAttribute("required");
 }
}
function GetSubAccountHead(accheadid,accsubheadid) {
		
		var strURL="get-accountsubhead-edit.php?id="+accheadid+"&subid="+accsubheadid;
		if(!accheadid)
		{
			alert();
		$('#account-subhead').data('combobox').clearTarget();
		$('#account-subhead').data('combobox').clearElement();	
		$('#account-subhead').combobox('refresh');
	
		}
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				//alert(req.readyState);
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {	
					//alert(req.status);
						document.getElementById('account-subhead').innerHTML=req.responseText;
						//alert(req.responseText);
						$('#account-subhead').combobox('refresh');
						
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	}

function showHideCashChequeLoanPayment(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque in SD*/
{
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");

 if(id == 2)
 {
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";
	
	 document.getElementById("sd-amount").removeAttribute("required"); // CASH FIELD
	  var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 //document.getElementById("current-date1").required = true;   // CHEQUE FIELD
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	// document.getElementById("current-date3").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 /*document.getElementById("transfer-to-account").removeAttribute("required");*/
	 document.getElementById("transfer-share-amount-payment").removeAttribute("required");
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
	 //document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	// document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	// document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	// document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	
	document.getElementById("transfer-account-type").required = true;
	
	document.getElementById("transfer-from-account").required = true;
	
	document.getElementById("transfer-share-amount-payment").required = true;
	document.getElementById("sd-amount").removeAttribute("required");
	
	
 
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 
	// document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	// document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 /*document.getElementById("transfer-to-account").removeAttribute("required");*/
	 document.getElementById("transfer-share-amount-payment").removeAttribute("required");
	 
	 
	 document.getElementById("sd-amount").required = true;
	  
 }
}	
function showHideCashChequeLoanPayment(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque in SD*/
{
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");

 if(id == 2)
 {
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";
	
	 document.getElementById("sd-amount").removeAttribute("required"); // CASH FIELD
	  var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 //document.getElementById("current-date1").required = true;   // CHEQUE FIELD
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	// document.getElementById("current-date3").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 /*document.getElementById("transfer-to-account").removeAttribute("required");*/
	 document.getElementById("transfer-share-amount-payment").removeAttribute("required");
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
	 //document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	// document.getElementById("current-date3").value = '';
	 document.getElementById("transfer-cheque-amount").value = '';
	
	// document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	// document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	
	document.getElementById("transfer-account-type").required = true;
	
	document.getElementById("transfer-from-account").required = true;
	
	document.getElementById("transfer-share-amount-payment").required = true;
	document.getElementById("sd-amount").removeAttribute("required");
	
	
 
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 
	// document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	// document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 /*document.getElementById("transfer-to-account").removeAttribute("required");*/
	 document.getElementById("transfer-share-amount-payment").removeAttribute("required");
	 
	 
	 document.getElementById("sd-amount").required = true;
	  
 }
}	


function showHideCashChequeLoan(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque in SD*/
{
var cheq = document.getElementById("cheque");
var sd = document.getElementById("sdcash");
var trans = document.getElementById("transfer");

 if(id == 2)
 {
	
	 cheq.style.display = "block";
	 sd.style.display = "none";
	 trans.style.display = "none";
	
	 document.getElementById("sdamount").removeAttribute("required"); // CASH FIELD
	  var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	 document.getElementById("current-date2").value = today;
	 //document.getElementById("current-date1").required = true;   // CHEQUE FIELD
	 document.getElementById("current-date2").required = true;
	 document.getElementById("bank").required = true;
	 document.getElementById("cheque-number").required = true;
	// document.getElementById("current-date3").required = true;
	 document.getElementById("transfer-cheque-amount").required = true;
	 document.getElementById("transfer-to-bank-account").required=true;
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	 document.getElementById("transfer-to-account").removeAttribute("required");
	 document.getElementById("transfer-share-amount").removeAttribute("required");
 
 }
 else if(id == 3) // Transfer
 {
    /*alert(id);*/
	trans.style.display = "block";
	cheq.style.display = "none";
	sd.style.display = "none";
	
//	 document.getElementById("current-date1").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("bank").value = '';
	 document.getElementById("cheque-number").value = '';
	 //document.getElementById("current-date3").value = '';
	 var chequeinterest=$("#transfer-cheque-amount").val();
	
	 if(chequeinterest==0 && chequeinterest!='')
	 {
		 		  
		 document.getElementById("transfer-cheque-amount").value =0;
	 }
	 else
	 {
		 document.getElementById("transfer-cheque-amount").value = '';
	 }
	
	// document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	// document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	
	document.getElementById("current-date4").required = true;
	document.getElementById("current-date4").value = today;
	
	document.getElementById("transfer-account-type").required = true;
	
	document.getElementById("transfer-from-account").required = true;
	
	//document.getElementById("transfer-share-amount").required = true;
	document.getElementById("sdamount").removeAttribute("required");
	
	document.getElementById("transfer-to-bank-account").removeAttribute("required");
 
 }
 else
 {
	 cheq.style.display = "none";
	 trans.style.display = "none";
	 sd.style.display = "block";
	 
	// document.getElementById("current-date1").removeAttribute("required");
	 //document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("bank").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
//	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("transfer-cheque-amount").removeAttribute("required");
	 
	  document.getElementById("current-date4").removeAttribute("required");
	 document.getElementById("transfer-account-type").removeAttribute("required");
	 document.getElementById("transfer-from-account").removeAttribute("required");
	/* document.getElementById("transfer-to-account").removeAttribute("required");*/
	 document.getElementById("transfer-share-amount").removeAttribute("required");
	 document.getElementById("transfer-to-bank-account").removeAttribute("required");
	 
	 
	 document.getElementById("sdamount").required = true;
	  
 }
}
function isValidDate(dateString)
	{
   // alert();
	// First check for the pattern
    if(!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateString))
	{
        document.getElementById("current-date5").value = "";
		return false;
	}

    // Parse the date parts to integers
    var parts = dateString.split("/");
    var day = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10);
    var year = parseInt(parts[2], 10);

    // Check the ranges of month and year
    if(year < 1000 || year > 3000 || month == 0 || month > 12)
	{
		document.getElementById("current-date5").value = "";
	    return false;
	}

    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

    // Adjust for leap years
    if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
        monthLength[1] = 29;

    // Check the range of the day
    return day > 0 && day <= monthLength[month - 1];
};
