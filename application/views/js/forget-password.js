// JavaScript Document
//  $('#user-registration').validate();
  $(document).ready(function(){
	//Email Validation
	$("#emailid").change(function() 
		{ 
		var emailid = $("#emailid").val();
		var msgbox = $("#status-email");
		$.ajax({  
			type: "POST",  
			url: "../ajax/emailexist.php",  
			data: "emailid="+ emailid,  
			success: function(msg){  
		   $("#status-email").ajaxComplete(function(event, request){ 
			if(msg == 'OK')
			{ 
			$('#emailid').val(emailid);
			msgbox.html('');
			}  
			else  
			{  
				$("#emailid").removeClass("green");
				$("#emailid").addClass("red");
				$('#emailid').val("");
				msgbox.html(msg);
			}  
		   });
		   } 
		  }); 
		return false;
		});
  });
