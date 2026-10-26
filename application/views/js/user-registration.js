// JavaScript Document
//  $('#user-registration').validate();
  $(document).ready(function(){
	$("#username").change(function() 
		{ 
		var username = $("#username").val();
		var msgbox = $("#status");
		if(username.length > 4)
		{
		$("#status").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
		$.ajax({
			type: "POST",  
			url: "../ajax/usernameavailability.php",  
			data: "username="+ username,  
			success: function(msg){  
		   $("#status").ajaxComplete(function(event, request){ 
			if(msg == 'OK')
			{ 
				$("#username").removeClass("red");
				$("#username").addClass("green");
				msgbox.html('<img src="images/available.png" align="absmiddle">');
				$('#username').val(username);
			}  
			else  
			{  
				$("#username").removeClass("green");
				$("#username").addClass("red");
				$('#username').val("");
				$('#username').focus();
				msgbox.html(msg);
			}  
		   });
		   } 
		  }); 
		}
		else
		{
		/*$("#username").addClass("red");
		$("#status").html('<font color="#cc0000">Please enter atleast FIVE characters</font>');*/
		}
		return false;
		});
	//Email Validation
	$("#emailid").change(function() 
		{ 
		var emailid = $("#emailid").val();
		var msgbox = $("#status-email");
		$.ajax({  
			type: "POST",  
			url: "../ajax/emailavailability.php",  
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
				$('#emailid').focus();
				msgbox.html(msg);
			}  
		   });
		   } 
		  }); 
		return false;
		});
	//StaffCode Validation BY RB on 24/2/16
	$("#old-sd-no").change(function() 
		{ 
		var sdno = $("#old-sd-no").val();
		var msgbox = $("#status-sdno");
		$("#status-sdno").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
		$.ajax({  
			type: "POST",  
			url: "../ajax/sdnoavailability.php",  
			data: "sdno="+ sdno,  
			success: function(msg){  
/*		   $("#status-designation").ajaxComplete(function(event, request){ */
			if(msg == 'OK')
			{ 
				$("#old-sd-no").removeClass("red");
				$("#old-sd-no").addClass("green");
		        msgbox.html('');
				$('#old-sd-no').val(sdno);
				
			}  
			else  
			{ 
				$("#old-sd-no").removeClass("green");
				$("#old-sd-no").addClass("red");
				$('#old-sd-no').val("");
				$('#old-sd-no').focus();
				msgbox.html(msg);
			} 
		  /* });*/
		   } 
		  }); 
		
		return false;
		});
	$("#staffcode-number").change(function() 
		{ 
		var staffcode = $("#staffcode-number").val();
		var msgbox = $("#status-staffcode");
	
		$("#status-staffcode").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
		$.ajax({  
			type: "POST",  
			url: "../ajax/staffcodeavailability.php",  
			data: "staffcode="+ staffcode,  
			success: function(msg){  
		   $("#status-staffcode").ajaxComplete(function(event, request){ 
			if(msg == 'OK')
			{ 
				$("#staffcode-number").removeClass("red");
				$("#staffcode-number").addClass("green");
		        msgbox.html('');
				$('#staffcode-number').val(staffcode);
			}  
			else  
			{ 
				$("#staffcode-number").removeClass("green");
				$("#staffcode-number").addClass("red");
				$('#staffcode-number').val("");
				$('#staffcode-number').focus();
				msgbox.html(msg);
			} 
		   });
		   } 
		  }); 
		
		return false;
		});
	
	$("#main-heading").change(function() 
		{ 
		
		var heading=$("#main-heading").val();
		var msgbox= $("#status-heading");
		
		$("#status-heading").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
	
		$.ajax({  
			type: "POST",  
			url: "../ajax/headingavailability.php",  
			data: "heading="+ heading,  
			success: function(msg)
			{ 
		
			   /*$("#status-heading").ajaxComplete(function(event, request)
				{ */
					if(msg =='OK')
					{ 
					
					
					$("#main-heading").removeClass("red");
					$("#main-heading").addClass("green");
					$('#main-heading').val(heading);
					 msgbox.html('');
					
					
					}  
					else  
					{  
						$("#main-heading").removeClass("green");
						$("#main-heading").addClass("red");
						$('#main-heading').val("");
						$('#main-heading').focus();
						msgbox.html(msg);
					}  
			 /*  });*/
		    } 
		  }); //ajax complete
		return false;
		});
	
		$("#designation").change(function() 
		{ 
		var designation = $("#designation").val();
		var msgbox = $("#status-designation");
		
		
		var eid=$("#eid").val();
	
		
			 if(eid>0)
			{
				var str1 = $.trim($("#designation").val().toUpperCase());
				var str2 = $.trim($("#olddesg").val().toUpperCase());
			
				if(str1==str2)
				{
				  return true;
				}
			}
	
	
		$("#status-designation").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
		$.ajax({  
			type: "POST",  
			url: "../ajax/designationavailability.php",  
			data: "designation="+ designation,  
			success: function(msg){  
/*		   $("#status-designation").ajaxComplete(function(event, request){ */
			if(msg == 'OK')
			{ 
				$("#designation").removeClass("red");
				$("#designation").addClass("green");
		        msgbox.html('');
				$('#designation').val(designation);
				
			}  
			else  
			{ 
				$("#designation").removeClass("green");
				$("#designation").addClass("red");
				$('#designation').val("");
				$('#designation').focus();
				msgbox.html(msg);
			} 
		  /* });*/
		   } 
		  }); 
		
		return false;
		});
	
	$("#division").change(function() 
		{ 
		var division = $("#division").val();
		var msgbox = $("#status-division");
		
		    var eid=$("#eid").val();
	
		
			 if(eid>0)
			{
				var str1 = $.trim($("#division").val().toUpperCase());
				var str2 = $.trim($("#olddiv").val().toUpperCase());
			
				if(str1==str2)
				{
				  return true;
				}
			}
	
		$("#status-division").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
		$.ajax({  
			type: "POST",  
			url: "../ajax/divisionavailability.php",  
			data: "division="+ division,  
			success: function(msg){  
		  /* $("#status-division").ajaxComplete(function(event, request){ */
			if(msg == 'OK')
			{ 
				$("#division").removeClass("red");
				$("#division").addClass("green");
		        msgbox.html('');
				$('#division').val(division);
			}  
			else  
			{ 
				$("#division").removeClass("green");
				$("#division").addClass("red");
				$('#division').val("");
				$('#division').focus();
				msgbox.html(msg);
				
			} 
		 /*  });*/
		   } 
		  }); 
		
		return false;
		});
	
	$("#entity").change(function() 
		{ 
		var entity = $("#entity").val();
		var msgbox = $("#status-entity");
		
			var eid=$("#eid").val();
	
		
			 if(eid>0)
			{
				var str1 = $.trim($("#entity").val().toUpperCase());
				var str2 = $.trim($("#oldentity").val().toUpperCase());
			
				if(str1==str2)
				{
				  return true;
				}
			}
	
		$("#status-entity").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
		$.ajax({  
			type: "POST",  
			url: "../ajax/entityavailability.php",  
			data: "entity="+ entity,  
			success: function(msg){  
		  /* $("#status-entity").ajaxComplete(function(event, request){ */
			if(msg == 'OK')
			{ 
				$("#entity").removeClass("red");
				$("#entity").addClass("green");
		        msgbox.html('');
				$('#entity').val(entity);
			}  
			else  
			{ 
				$("#entity").removeClass("green");
				$("#entity").addClass("red");
				$('#entity').val("");
				$('#entity').focus();
				msgbox.html(msg);
			} 
		   /*});*/
		   } 
		  }); 
		
		return false;
		});
	
	$("#role").change(function() 
		{ 
		var role = $("#role").val();
		var msgbox = $("#status-role");
		
			var eid=$("#eid").val();
	
		
			 if(eid>0)
			{
				var str1 = $.trim($("#role").val().toUpperCase());
				var str2 = $.trim($("#oldrole").val().toUpperCase());
			
				if(str1==str2)
				{
				  return true;
				}
			}
	
		$("#status-role").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
		$.ajax({  
			type: "POST",  
			url: "../ajax/roleavailability.php",  
			data: "role="+ role,  
			success: function(msg){  
		/*   $("#status-role").ajaxComplete(function(event, request){ */
			if(msg == 'OK')
			{ 
				$("#role").removeClass("red");
				$("#role").addClass("green");
		        msgbox.html('');
				$('#role').val(role);
			}  
			else  
			{ 
				$("#role").removeClass("green");
				$("#role").addClass("red");
				$('#role').val("");
				$('#role').focus();
				msgbox.html(msg);
			} 
		   /*});*/
		   } 
		  }); 
		
		return false;
		});
  });
