// JavaScript Document
//  $('#user-registration').validate();
  $(document).ready(function(){
	$("#role").change(function() 
		{ 
		var role = $("#role").val();
		var msgbox = $("#status");
		$("#status").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
		$.ajax({  
			type: "POST",  
			url: "../ajax/getpages.php?id="+$("#role").val(),  
			success: function(data){  
		   /*$("#status").ajaxComplete(function(event, request){ 
				msgbox.html(data);
		   });*/
		    $("#status").html(data);
		   } 
		  }); 
		return false;
		});
	$("#role").change();
  });
