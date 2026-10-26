$('button[id="editClick"]').on('click', function(e){
    var $form=$(this).closest('form'); 
    e.preventDefault();
    $('#confirm').modal('show');
});
$('#edit').on('click', function(e){
 $('#editAlert').click();
});

/*  Show Delete Popup */
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

var next = 1;
    $(".add-more").click(function(e){
        e.preventDefault();
        var addto = "#field" + next;
        var addRemove = "#field" + (next);
        next = next + 1;
		
        var newIn = '<input autocomplete="off" class="input form-control upload-textbox pull-left" id="field' + next + '" name="fileupload[]" type="file" style="width:70%;margin-bottom:10px;"  >';
        var newInput = $(newIn);
        var removeBtn = '<span class="input-group-btn"><button id="remove' + (next - 1) + '" class="btn btn-danger remove-me pull-right" style="margin-bottom:10px;">&nbsp;Remove&nbsp;</button></span></div><div id="field">';
        var removeButton = $(removeBtn);
        $(addto).after(newInput);
        $(addRemove).after(removeButton);
        $("#field" + next).attr('data-source',$(addto).attr('data-source'));
        $("#count").val(next);  
        
            $('.remove-me').click(function(e){
                e.preventDefault();
                var fieldNum = this.id.charAt(this.id.length-1);
				//alert(fieldNum);
                var fieldID = "#field" + fieldNum;
                $(this).remove();
                $(fieldID).remove();
            });
    });
	
	var maxField = 100; //Input fields increment limitation
	var addButton = $('.add_button'); //Add button selector
	var addActButton = $('.act_add_button'); //Add button selector
	var wrapper = $('.field_wrapper'); //Input field wrapper
	var wrapper1 = $('.field_wrapper1'); //Input field wrapper Prasad
	var actwrapper = $('.field_wrapper_act'); //Input field wrapper Prasad
	var fieldHTML = '<div> <input autocomplete="off" class="form-control input upload-textbox pull-left"  name="fileupload[]" type="file"  style="width:70%;margin-bottom:10px;"/><a href="javascript:void(0);" class="remove_button" title="Remove field"> <button id="b1" class="btn btn-danger pull-right pull-right" type="button" style="" >&nbsp;Remove&nbsp;</button></a></div>'; //New input field html 
	var x = 1; //Initial field counter is 1
	$(addButton).click(function(){ //Once add button is clicked
		if(x < maxField){ //Check maximum number of input fields
			x++; //Increment field counter
			$(wrapper).append(fieldHTML); // Add field html
		}
	});
	var newCheckListId = 1;
	$(addActButton).click(function(){ //Once add button is clicked
		newCheckListId++;
		var fieldActHTML = '<div class="panel-group"><div class="panel-body panel-info-bg"><textarea id="activity_check_list_'+newCheckListId+'" autocomplete="off" class="form-control"  name="activity_check_list[]"  style="float:left;"></textarea><input type="checkbox" name="activity_check_list_mandatory'+newCheckListId+'" id="activity_check_list_mandatory'+newCheckListId+'"  onClick="validateChecklist(this.id);" style="float:left;margin-top:5px;display:none;"><a href="javascript:void(0);" class="remove_button" title="Remove field"> <button id="b1" class="btn btn-danger pull-right pull-right" type="button" style="float:left;margin-top:5px;" >&nbsp;Remove&nbsp;</button></a></div></div>'; //New input field html 
		if(x < maxField){ //Check maximum number of input fields
			x++; //Increment field counter
			$(actwrapper).append(fieldActHTML); // Add field html
		}
	});
	$(wrapper).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
		x--; //Decrement field counter
	});
	$(wrapper1).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
		x--; //Decrement field counter
	});
	$(actwrapper).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
		x--; //Decrement field counter
	});
	var wrapper2 = $('.field_wrapper2'); //Input field wrapper Prasad
	var wrapper3 = $('.field_wrapper3'); //Input field wrapper Prasad
	$(wrapper2).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
		x--; //Decrement field counter
	});
	$(wrapper3).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
		x--; //Decrement field counter
	});
	
/*Multiple add button in the same page */
var addId = 0;
var addButtonComp = '';
  $(".btn-reference").click(function(){
    addId = this.id;
	addButtonComp = $('.add_button'+addId); //Add button selector
	var wrapper = $('.field_wrapper'+addId); //Input field wrapper
		var wrapper1 = $('.field_wrapper1'); //Input field wrapper Prasad
	var fieldHTML = '<div> <input autocomplete="off" class="form-control input upload-textbox pull-left"  name="fileupload'+addId+'[]" type="file"  style="width:70%;margin-bottom:10px;"/><a href="javascript:void(0);" class="remove_button" title="Remove field"> <button id="b1" class="btn btn-danger pull-right pull-right" type="button" style="" >&nbsp;Remove&nbsp;</button></a></div>'; //New input field html 
	$(wrapper).append(fieldHTML); // Add field html
	$(wrapper).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
	});
	/**/
 });

// Exist Reference
 $(".btn-ref-exist").click(function(){
    addId = this.id;
	var wrappers = $('.field_wrapperphotoexist'+addId); //Input field wrapper
	$(wrappers).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
	});
	/**/
 });
//Exist photo
 $(".btn-photo-exist").click(function(){
    addId = this.id;
	var wrappers = $('.field_wrapperphoto'+addId); //Input field wrapper
	$(wrappers).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
	});
	/**/
 });


// Add multiple Phot o
  $(".btn-photo").click(function(){
    addId = this.id;
	addButtonComp = $('.add_button1'+addId); //Add button selector
	var wrapper1 = $('.field_wrapper1'+addId); //Input field wrapper
	var fieldHTML = '<div> <input autocomplete="off" class="form-control input upload-textbox pull-left"  name="fileupload1'+addId+'[]" type="file"  style="width:70%;margin-bottom:10px;"/><a href="javascript:void(0);" class="remove_button" title="Remove field"> <button id="b1" class="btn btn-danger pull-right pull-right" type="button" style="" >&nbsp;Remove&nbsp;</button></a></div>'; //New input field html 
	$(wrapper1).append(fieldHTML); // Add field html
	$(wrapper1).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
	});
	/**/
 });

// Add multiple Phot o
  $(".btn-fastner-photo").click(function(){
	addButtonComp = $('.add_button1'); //Add button selector
	var wrapper1 = $('.field_wrapper1'); //Input field wrapper
	var fieldHTML = '<div> <input autocomplete="off" class="form-control input upload-textbox pull-left"  name="fileupload1[]" type="file"  style="width:70%;margin-bottom:10px;"/><a href="javascript:void(0);" class="remove_button" title="Remove field"> <button id="b1" class="btn btn-danger pull-right pull-right" type="button" style="" >&nbsp;Remove&nbsp;</button></a></div>'; //New input field html 
	$(wrapper1).append(fieldHTML); // Add field html
	$(wrapper1).on('click', '.remove_button', function(e){ //Once remove button is clicked
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
	});
	/**/
 });
  
  
/*   Edit Alert */
$('#editClick').on('click', function(e){
    var $form=$(this).closest('form'); 
    e.preventDefault();
    $('#confirm').modal('show');
});
$('#edit').on('click', function(e){
 $('#editAlert').click();
});

/* Edit Alert End*/