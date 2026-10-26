	$('a[data-confirm]').click(function(ev) {
		var href = $(this).attr('href');
		if (!$('#dataConfirmModal').length) {
			$('#page-wrapper').append('<div id="dataConfirmModal" class="modal" role="dialog" aria-labelledby="dataConfirmLabel" aria-hidden="true"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h5 id="dataConfirmLabel">This will delete all drawings related to this stage. Please Confirm</h5></div><div class="modal-body"></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button><a class="btn btn-primary" id="dataConfirmOK">OK</a></div></div>');
		} 
		$('#dataConfirmModal').find('.modal-body').text($(this).attr('data-confirm'));
		$('#dataConfirmOK').attr('href', href);
		$('#dataConfirmModal').modal({show:true});
		return false;
	});
	/* BY RB on 21/03/16 */
	 $(document).ready(function()
	 {
								
								$("#idno").change(function() 
								{ 
							
								var stageid = $("#idno").val();
								var msgbox = $("#status-Stageidno");
							
								$("#status-Stageidno").html('<img src="images/loader.gif" align="absmiddle">&nbsp;Checking availability...');
								$.ajax({  
									type: "POST",  
									url: "../ajax/stageidentificationavailability.php",  
									data: "stageidno="+ stageid,  
									success: function(msg){  
								/*   $("#status-role").ajaxComplete(function(event, request){ */
									if(msg == 'OK')
									{ 
										$("#idno").removeClass("red");
										$("#idno").addClass("green");
										msgbox.html('');
										$('#idno').val(stageid);
									}  
									else  
									{ 
										$("#idno").removeClass("green");
										$("#idno").addClass("red");
										$('#idno').val("");
										$('#idno').focus();
										msgbox.html(msg);
									} 
								   /*});*/
								   } 
								  }); 
								
								return false;
								});
	});
		