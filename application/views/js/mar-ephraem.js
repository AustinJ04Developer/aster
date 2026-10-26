$(document).ready(function(){
	//Data Table
	$('#department-details').DataTable();
	$('#section-details').DataTable();
  });
//Email Validation
$("#emailid").change(function() 
{ 
	var emailid = $("#emailid").val();
	var msgbox = $("#status-email");
	$.ajax({  
		type: "POST",  
		url: "../ajax/emailavailability.php",  
		data: "emailid="+emailid,  
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
	});

// Id NO Avaialbility Check
	$("#id-no").change(function() 
		{ 
		var idno = $("#id-no").val();
		var msgbox = $("#status-idno");
		$.ajax({  
			type: "POST",  
			url: "../ajax/idnoavailability.php",  
			data: "idno="+ idno,  
			success: function(msg){  
		   $("#status-idno").ajaxComplete(function(event, request){ 
			if(msg == 'OK')
			{ 
			$('#id-no').val(idno);
			msgbox.html('');
			}  
			else  
			{  
				$("#id-no").removeClass("green");
				$("#id-no").addClass("red");
				$('#id-no').val("");
				$('#id-no').focus();
				msgbox.html(msg);
			}  
		   });
		   } 
		  }); 
		return false;
		});
$(document).on('click', "a#deleteAlert", function(ev) {
var href = $(this).attr('href');
		if (!$('#dataConfirmModal').length) {
			$('#page-wrapper').append('<div id="dataConfirmModal" class="modal" role="dialog" aria-labelledby="dataConfirmLabel" aria-hidden="true"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button><h3 id="dataConfirmLabel">Please Confirm</h3></div><div class="modal-body">Are you sure you want to delete?</div><div class="modal-footer"><a class="btn btn-primary" id="dataConfirmOK">OK</a><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button></div></div>');
		} 
		$('#dataConfirmModal').find('.modal-body').text($(this).attr('data-confirm'));
		$('#dataConfirmOK').attr('href', href);
		$('#dataConfirmModal').modal({show:true});
  	    return false;      
});
$('.gender').combobox(); // Gender Combo
$(".gender").attr("placeholder", "Select Gender");
$('.nationality').combobox(); // Gender Combo
$(".nationality").attr("placeholder", "Select Nationality");
$('.role').combobox(); // Role Combo
$(".role").attr("placeholder", "Select Role");
$('.designation').combobox(); // Designation Combo
$(".designation").attr("placeholder", "Select Designation");
$('.section').combobox(); // Section Combo
$(".section").attr("placeholder", "Select Section");
$('.department').combobox(); // Department Combo
$(".department").attr("placeholder", "Select School");
$('.degree').combobox(); // Degree Combo
$(".degree").attr("placeholder", "Select Degree");
$('.years').combobox(); // Year Combo
$(".years").attr("placeholder", "Select Year");
$('.semesters').combobox(); // Semester Combo
$(".semesters").attr("placeholder", "Select Semester");
$('.subject-type').combobox(); // Semester Combo
$(".subject-type").attr("placeholder", "Select Subject Type");
$('.academic-year').combobox(); // Semester Combo
$(".academic-year").attr("placeholder", "Select Academic Year");
$('.assign-to').combobox(); // Semester Combo
$(".assign-to").attr("placeholder", "Select Instructor");
$('.unit').combobox(); // Semester Combo
$(".unit").attr("placeholder", "Select Unit Number");
$('.course-outcome').combobox(); // Semester Combo
$(".course-outcome").attr("placeholder", "Select Course Outcome");
$('.programme-outcome').combobox(); // Semester Combo
$(".programme-outcome").attr("placeholder", "Select Programme Number");
$('.mapping-level').combobox(); // Semester Combo
$(".mapping-level").attr("placeholder", "Select Mapping Level");
$('.lesson').combobox(); // Semester Combo
$(".lesson").attr("placeholder", "Select Lesson");
$('.regulation').combobox(); // Regulation Combo
$(".regulation").attr("placeholder", "Select Regulation");
$('.ListYear').combobox(); // Exam YEAR Combo
$(".ListYear").attr("placeholder", "Select Exam Year");
$('.exams').combobox(); // Exam YEAR Combo
$(".exams").attr("placeholder", "Select Exam ");
$(".messagetype").combobox(); // Exam YEAR Combo
$(".messagetype").attr("placeholder", "Select Message Type");
$('.course-type').combobox(); // Semester Combo
$(".course-type").attr("placeholder", "Select Course Type");
$('.programme').combobox(); // Semester Combo
$(".programme").attr("placeholder", "Select Programme");
$('.course-code').combobox(); // Semester Combo
$(".course-code").attr("placeholder", "Select Course Code");
$('.visionmission').combobox(); // Semester Combo
$(".visionmission").attr("placeholder", "Select Vision or Mission");
$('.peopo').combobox(); // Semester Combo
$(".peopo").attr("placeholder", "Select PEO or PO");
$('.dayorder').combobox(); // Semester Combo
$(".dayorder").attr("placeholder", "Select Day Order");
$('.coursetype').combobox(); // Semester Combo
$(".coursetype").attr("placeholder", "Course Type");
$('.classtype').combobox(); // Semester Combo
$(".classtype").attr("placeholder", "Class Type");
$('.course-codes').combobox(); // Semester Combo
$(".course-codes").attr("placeholder", "Course Code");
$('.course-lt').combobox(); // Semester Combo
$(".course-lt").attr("placeholder", "L/T");
$('.category').combobox(); // Gender Combo
$(".category").attr("placeholder", "Select Category");
$('.admissioncategory').combobox(); // Gender Combo
$(".admissioncategory").attr("placeholder", "Select Admission Catgegory");
$('.course').combobox(); // Gender Combo
$(".course").attr("placeholder", "Select Course");
$('.exam').combobox(); // Gender Combo
$(".exam").attr("placeholder", "Select Exam");
$('.part').combobox(); // Gender Combo
$(".part").attr("placeholder", "Select Part");
$('.cycle').combobox(); // Gender Combo
$(".cycle").attr("placeholder", "Select Cycle");
$('.feedback-category').combobox(); // Gender Combo
$(".feedback-category").attr("placeholder", "Select Feedback Category");
$('.state').combobox(); // Gender Combo
$(".state").attr("placeholder", "Select State");
$('.nationality').combobox(); // Gender Combo
$(".nationality").attr("placeholder", "Select Nationality");
$('.bloodgroup').combobox();
$(".bloodgroup").attr("placeholder", "Select BloodGroup");
$('.disability').combobox(); // Gender Combo
$(".disability").attr("placeholder", "Select Disability");
$('.category').combobox(); // Gender Combo
$(".category").attr("placeholder", "Select Category");
$('.status').combobox(); // Gender Combo
$(".status").attr("placeholder", "Select Status");
$('.feedback').combobox(); // Gender Combo
$(".feedback").attr("placeholder", "Select Feedback");

$(".regulation").focus();
$(".visionmission").focus();
$(".peopo").focus();
$(".unit").focus();
$(".academic-year").focus();
/* Attendance table present and absent color changing function */
$('#tbl').find('input:checkbox[id$="checkbox-size-absent"]').click(function() {
    var isChecked = $(this).prop("checked");
    var $selectedRow = $(this).parent("td").parent("tr");
	if (isChecked){ 
	$selectedRow.removeClass("absent-students");
	$selectedRow.addClass("present-students");
	}
    else 
	{
	$selectedRow.removeClass("present-students");
	$selectedRow.addClass("absent-students");
	}
});
$('#tbl').find('input:checkbox[id$="checkbox-size-present"]').click(function() {
    var isChecked = $(this).prop("checked");
    var $selectedRow = $(this).parent("td").parent("tr");
	if (isChecked){ 
	$selectedRow.removeClass("absent-students");
	$selectedRow.addClass("present-students");
	}
    else 
	{
	$selectedRow.removeClass("present-students");
	$selectedRow.addClass("absent-students");
	}
});
$('#view-attendance').DataTable(); // View Attendance Data Table
<!--MESSAGE DATA TABLE
-->
$('#message-details').DataTable();
$('#studentsmarklist').DataTable();
<!--COMBOBOX-->

<!--FUNCTION TO ACCEPT NUMBERS-->
function validateNumber(evt)  {
		var charCode = (evt.which) ? evt.which : event.keyCode
		if (charCode == 17 )   {
			return false;
		}
		if ( charCode > 31 && (charCode < 48 || charCode > 57)){
		 return false;
		}
	}
<!--FUNCTION TO ACCEPT CHARACTERS-->
function validateCharacter(evt) {
       evt = (evt) ? evt : event;
       var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode :
          ((evt.which) ? evt.which : 0));
       if (charCode > 31 && (charCode < 65 || charCode > 90) &&
          (charCode < 97 || charCode > 122)) {
          return false;
       }
       return true;
     }
function validateAlphaNumeric(evt) {
   evt = (evt) ? evt : event;
    var charCode = (evt.charCode) ? evt.charCode : ((evt.keyCode) ? evt.keyCode :
  ((evt.which) ? evt.which : 0));
	if((charCode>47 && charCode<58) || (charCode>64 && charCode<91) || (charCode>96 && charCode<123))
	 {
       // alert("Enter letters only.");
        return true;
    }
    return false;
}
function GetSemester(YearId,SemesterId)
{
		var strURL="fill-semester.php?yearid="+YearId+"&selectedsemester="+SemesterId;
		if(!YearId)
		{
		$('#semester-id').data('combobox').clearTarget();
		$('#semester-id').data('combobox').clearElement();	
		$('#semester-id').combobox('refresh');
		}
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				
				if (req.readyState == 4) {
					//alert(req.responseText);
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('semester-id').innerHTML=req.responseText;
						
						$('#semester-id').combobox('refresh');
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
}





function GetSemesters(YearId,SemesterId)
{
		
		var strURL="fill-semester.php?yearid="+YearId+"&selectedsemester="+SemesterId;
		if(!YearId)
		{
		$('#semesters-id').data('combobox').clearTarget();
		$('#semesters-id').data('combobox').clearElement();	
		$('#semesters-id').combobox('refresh');
		}
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('semesters-id').innerHTML=req.responseText;
						
						$('#semesters-id').combobox('refresh');
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
}
function GetMessage(TID,MessageType)
{
		var strURL="fill-message.php?tid="+TID+"&messagetype="+MessageType;
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('message').innerHTML=req.responseText;
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

// Fill Programme, Component type and Title
function GetProgramTypeTitle(cid)
{
		var strURL="fill-programme-type-title.php?cid="+cid;
		// alert(strURL);
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						var resText = req.responseText;
						if(resText != '')
						{
							var res = resText.split("|"); 
							document.getElementById('course-type').value=res[1];
							document.getElementById('course-title').value=res[2];
						}
						else
						{
							document.getElementById('course-type').value="";
							document.getElementById('course-title').value="";
							}
					} else {
						//alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}	
}

function showCourse(str,caidvalue) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","ajax/fill-values-courses.php?q="+str+"&c="+caidvalue,true);
        xmlhttp.send();
    }
}

//Student Roll Number Validation
$("#roll-number").change(function() 
	{ 
	
	var rollnumber = $("#roll-number").val();
	var msgbox = $("#status-rollnumber");
	$.ajax({  
		type: "POST",  
		url: "../ajax/studentrollnoavailability.php",  
		data: "rollnumber="+ rollnumber,  
		success: function(msg){ 
	  /* $("#status-rollnumber").ajaxComplete(function(event, request){ */
		if(msg == 'OK')
		{ 
		$('#roll-number').val(rollnumber);
		msgbox.html('');
		}  
		else  
		{  
			$("#roll-number").removeClass("green");
			$("#roll-number").addClass("red");
			$('#roll-number').val("");
			$('#roll-number').focus();
			msgbox.html(msg);
		}  
	   /*});*/
	   } 
	  }); 
	return false;
	});

//Student Register Number Validation
$("#register-number").change(function() 
	{ 
	var registernumber = $("#register-number").val();
	var msgbox = $("#status-registernumber");
	$.ajax({  
		type: "POST",  
		url: "../ajax/studentregisternoavailability.php",  
		data: "registernumber="+ registernumber,  
		success: function(msg){ 
	  /* $("#status-rollnumber").ajaxComplete(function(event, request){ */
		if(msg == 'OK')
		{ 
		$('#register-number').val(registernumber);
		msgbox.html('');
		}  
		else  
		{  
			$("#register-number").removeClass("green");
			$("#register-number").addClass("red");
			$('#register-number').val("");
			$('#register-number').focus();
			msgbox.html(msg);
		}  
	   /*});*/
	   } 
	  }); 
	return false;
	});
//Unit Number Validation
$("#unit-number").change(function()
{
	var unitid = $("#unit-number").val();
	var caid = $("#caid").val();
	var msgbox = $("#status-unitnumber");
	$.ajax({  
		type: "POST",  
		url: "../ajax/unitnumberavailability.php",  
		data: "unitid="+unitid+"&caid="+caid,
		success: function(msg){  
	  /* $("#status-unitnumber").ajaxComplete(function(event, request){ */
		if(msg == 'OK')
		{ 
		$('#unit-number').val(unitid);
		msgbox.html('');
		}  
		else  
		{  
			$('#unit-number').data('combobox').clearTarget();
		    $('#unit-number').data('combobox').clearElement();	
		    $('#unit-number').combobox('refresh');
			$('#unit-number').focus();
			msgbox.html(msg);
		}  
	  /* });*/
	   } 
	  }); 
});
// Lesson No Avaialbility Check
	$("#lesson-number-course").change(function() 
		{ 
		
		var lessonno = $("#lesson-number-course").val();
		var caid = $("#caid").val();
		var unitid = $("#unit-id-course").val();
		var msgbox = $("#status-lessonno");
		var eid = $("#eid").val();
		
		$.ajax({  
			type: "POST",  
			url: "../ajax/lessonnoavailability.php",  
			data: "lessonno="+ lessonno+"&caid="+caid+"&unitid="+unitid+"&eid="+eid,  
			success: function(msg){  
		 /*  $("#status-lessonno").ajaxComplete(function(event, request){ */
			if(msg == 'OK')
			{ 
			$('#status-lessonno').val(lessonno);
			msgbox.html('');
			}  
			else  
			{  
				$("#lesson-number-course").removeClass("green");
				$("#lesson-number-course").addClass("red");
				$('#lesson-number-course').val("");
				$('#lesson-number-course').focus();
				msgbox.html(msg);
			}  
		  /* });*/
		   } 
		  }); 
		return false;
		});
	
function GetCourseUnitLesson(unitid,courseallocationid,selectedlesson){
	var strURL="fill-courselessons.php?unitid="+unitid+"&caid="+courseallocationid+"&lessonid="+selectedlesson;
		if(!unitid)
		{
		$('#lesson').data('combobox').clearTarget();
		$('#lesson').data('combobox').clearElement();	
		$('#lesson').combobox('refresh');
		}
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						document.getElementById('lesson').innerHTML=req.responseText;
						$('#lesson').combobox('refresh');
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
function FillCourseCode(semesterid, programmeid, regulationid, yearid,cid){
	/*alert(cid);
	alert(programmeid);
	alert(regulationid);
	alert(yearid);
	alert(semesterid);*/
	var strURL="fill-course-code.php?p="+programmeid+"&y="+yearid+"&r="+regulationid+"&s="+semesterid+"&c="+cid;
	//alert(strURL);
		if(!semesterid)
		{
		$('#course-code').data('combobox').clearTarget();
		$('#course-code').data('combobox').clearElement();	
		$('#course-code').combobox('refresh');
		}
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						document.getElementById('course-code').innerHTML=req.responseText;
						$('#course-code').combobox('refresh');

					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	
	}

function CheckIfExistSameLessson(selectedlesson,courseallocationid,unitid){
	var strURL="is_lesson_exist.php?unitid="+unitid+"&caid="+courseallocationid+"&lessonid="+selectedlesson;
	//alert(strURL);
		if(!unitid)
		{
		$('#lesson').data('combobox').clearTarget();
		$('#lesson').data('combobox').clearElement();	
		$('#lesson').combobox('refresh');
		}
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						document.getElementById('lesson').innerHTML=req.responseText;
						$('#unit-id').combobox('refresh');
						$('#lesson').combobox('refresh');
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	
	}

//Email Validation
$("#lesson").change(function() 
{ 
	var selectedlesson = $("#lesson").val();
	var courseallocationid = $("#caid").val();
	var unitid = $("#unit-id").val();
	var msgbox = $("#status-email");
	$.ajax({  
		type: "POST",  
		url: "../ajax/is_lesson_exist.php", 
		data:{
       			selectedlesson: selectedlesson,
			    courseallocationid: courseallocationid,
		        unitid: unitid
    	},
		success: function(msg){  
	    $("#status-email").ajaxComplete(function(event, request){ 
		if(msg == 'OK')
		{ 
		//alert();
		 $('#lesson').val(selectedlesson);
		 
		}  
		else  
		{  
			$('#lesson').data('combobox').clearTarget();
			$('#lesson').data('combobox').clearElement();	
			$('#lesson').combobox('refresh');
			$('#lesson').focus();
			$('related-principles').val(unitid);
			msgbox.html(msg);
		}  
	   });
	   } 
	  }); 
	});
$("#pre-mark").change(function() 
{
	alert();
	var premark = $("#pre-mark").val();
 if(premark > 100){
    $('#pre-mark').val("0");
  }
		});

function modalViewReport(clpid)
{
//	alert(clpid);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('load-modal-report.php',{'id1': clpid},
function(){
$modal.modal('show');
});
/*document.getElementById('loader').style.display = 'none';*/
}

function modalViewStudentEditPage(crid)
{
//	alert(clpid);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('edit-student-registration.php',{'id1': crid},
function(){
$modal.modal('show');
});
}

function modalViewStudentsTc(srid)
{
//	alert(srid	);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('transfer-certificate.php',{'srid': srid},
function(){
$modal.modal('show');
});
}
function modalViewGrievanceStatusPage(grid)
{
//	alert(clpid);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('grievance-details-pop-up.php',{'id1': grid},
function(){
$modal.modal('show');
});
}
function modalViewStaffProfile(uid)
{
//	alert(uid);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('view-profile.php',{'id1': uid},
function(){
$modal.modal('show');
});
}
function modalViewStudentOD(crid)
{
//	alert(crid);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('show-student-od-details.php',{'id1': crid},
function(){
$modal.modal('show');
});
}

function showDayOrderUser(str,dayorder,mid) {
    if (str == "") {
		if(mid == 1)
			document.getElementById("txtHint").innerHTML = "";
		else if(mid == 2)
			document.getElementById("txtHint2").innerHTML = "";
		else if(mid == 3)
			document.getElementById("txtHint3").innerHTML = "";
		else if(mid == 4)
			document.getElementById("txtHint4").innerHTML = "";
		else if(mid == 5)
			document.getElementById("txtHint5").innerHTML = "";
		else if(mid == 6)
			document.getElementById("txtHint6").innerHTML = "";
		else if(mid == 7)
			document.getElementById("txtHint7").innerHTML = "";
		else if(mid == 8)
			document.getElementById("txtHint8").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
				if(mid == 1)
                	document.getElementById("txtHint").innerHTML = this.responseText;
				else if(mid == 2)
					document.getElementById("txtHint2").innerHTML = this.responseText;
				else if(mid == 3)
					document.getElementById("txtHint3").innerHTML = this.responseText;
				else if(mid == 4)
					document.getElementById("txtHint4").innerHTML = this.responseText;
				else if(mid == 5)
					document.getElementById("txtHint5").innerHTML = this.responseText;
				else if(mid == 6)
					document.getElementById("txtHint6").innerHTML = this.responseText;
				else if(mid == 7)
					document.getElementById("txtHint7").innerHTML = this.responseText;
				else if(mid == 8)
					document.getElementById("txtHint8").innerHTML = this.responseText;
				$('.combobox').data('combobox').clear();
            }
        };
        xmlhttp.open("GET","../ajax/getuserdayorder.php?q="+str+"&p="+mid+"&d="+dayorder,true);
        xmlhttp.send();
    }
}
$('.tutorial').combobox(); // Tutorial Combo
$(".tutorial").attr("placeholder", "Select Tutorial"); 
function GetCourseUnitTutorials(unitid,courseallocationid,selectedtutorial){
	var strURL="fill-coursetutorials.php?unitid="+unitid+"&caid="+courseallocationid+"&tutorialid="+selectedtutorial;
		if(!unitid)
		{
		$('#tutorial-title').data('combobox').clearTarget();
		$('#tutorial-title').data('combobox').clearElement();	
		$('#tutorial-title').combobox('refresh');
		}
		var req = getXMLHTTP();
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						document.getElementById('tutorial-title').innerHTML=req.responseText;
						$('#tutorial-title').combobox('refresh');
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

// Username Validation
$("#username").change(function() 
{ 
	var username = $("#username").val();
	var msgbox = $("#status-username");
	$.ajax({  
		type: "POST",  
		url: "../ajax/studentusernameavailability.php",  
		data: "username="+username,  
		success: function(msg){  
		
		if(msg == 'OK')
		{ 
		$('#username').val(username);
		msgbox.html('');
		}  
		else  
		{  
			$("#username").removeClass("green");
			$("#username").addClass("red");
			$('#username').val("");
			$('#username').focus();
			msgbox.html(msg);
		}  
	   } 
	  }); // Ajax Complete
	return false;
	});

function GetMessage(TID,MessageType)
{
		var strURL="fill-message.php?tid="+TID+"&messagetype="+MessageType;
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('message').innerHTML=req.responseText;
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
}
// View Student Details
function modalViewStudentViewPage(crid)
{
//	alert(clpid);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('view-student-registration.php',{'id1': crid},
function(){
$modal.modal('show');
});
}


function modalViewStudentApprovalPage(crid)
{
//	alert(clpid);
//var currentMCId = document.getElementById(id).value;
document.getElementById('loader').style.display = 'block';
var $modal = $('#load_popup_modal_show_id');
$modal.load('approve-student-registration.php',{'id1': crid},
function(){
$modal.modal('show');
});
}

function showRegisteredStudentsList(programme)
{
	if (programme == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","ajax/fill-students-approval.php?q="+programme,true);
        xmlhttp.send();
    }
}

function marksUpdate(caid,examid,question,srid,maxmarks,totalquestions){
	var marksid = 'm'+caid.toString()+examid.toString()+question.toString()+srid.toString();
	var isabsent = 0;
	var marks = document.getElementById(marksid).value;
	if(marks > maxmarks)
	{
		alert("Enter a valid mark.  Maximum mark for this question is "+maxmarks);	
		document.getElementById(marksid).value = 0;
		document.getElementById("marksid").focus();
		return false;	
	}
	summarksofastudent = 0;
//	alert(totalquestions);
	for(iRow = 1;iRow<=totalquestions;iRow++)
	{
		marksidtot = 'm'+caid.toString()+examid.toString()+iRow.toString()+srid.toString();
		summarksofastudent += Number(document.getElementById(marksidtot).value);
	}
//	alert(summarksofastudent);
	var totmarksid = 'm'+srid.toString();
	var absentupdate = 0;
	document.getElementById(totmarksid).value = summarksofastudent;
	var strURL="update-students-marks.php?caid="+caid+"&examid="+examid+"&question="+question+"&srid="+srid+"&marks="+marks+"&isabsent="+isabsent+"&absentupdate="+absentupdate;
	var summarks = 'm'+srid.toString();
	// summarks += 
	//alert(strURL);
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						// alert(this.responseText);
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	
	}


function remarksUpdate(caid,examid,srid){
	var remarksid = 're'+caid.toString()+examid.toString()+srid.toString();
	var remarks = document.getElementById(remarksid).value;
	var remarksdoneid = 'rd'+caid.toString()+examid.toString()+srid.toString();
	var remarksdone = document.getElementById(remarksdoneid).value;
	/*alert(remarksdone);
	alert(remarks);*/
	var strURL="update-students-marks-remarks.php?caid="+caid+"&examid="+examid+"&srid="+srid+"&remarks="+remarks+"&remarksdone="+remarksdone;
	//alert(strURL);
	var req = getXMLHTTP();
		if (req) {
			//alert(req);
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						// alert(this.responseText);
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	
	}



function absentsUpdate(caid,examid,question,srid,totalquestions){
	var marksid = 'm'+caid.toString()+examid.toString()+question.toString()+srid.toString();
	var isabsent = document.getElementById(marksid).value;
	var marks = 0;
	summarksofastudent = 0;
//	alert(totalquestions);
	if(isabsent == -1)
	{
		for(iRow = 1;iRow<=totalquestions;iRow++)
		{
			marksidtot = 'm'+caid.toString()+examid.toString()+iRow.toString()+srid.toString();
			document.getElementById(marksidtot).value = 0; 
			document.getElementById(marksidtot).disabled = true; 
		}
	}
	else
	{
		for(iRow = 1;iRow<=totalquestions;iRow++)
		{
			marksidtot = 'm'+caid.toString()+examid.toString()+iRow.toString()+srid.toString();
			document.getElementById(marksidtot).disabled = false; 
		}
	}
//	alert(summarksofastudent);
	var totmarksid = 'm'+srid.toString();
	var absentupdate = 0;
	if(isabsent == -1)
	{
	 absentupdate = 1;
	}
	document.getElementById(totmarksid).value = summarksofastudent;
	var strURL="update-students-marks.php?caid="+caid+"&examid="+examid+"&question="+question+"&srid="+srid+"&marks="+marks+"&isabsent="+isabsent+"&absentupdate="+absentupdate;
	var summarks = 'm'+srid.toString();
	// summarks += 
	//alert(strURL);
		var req = getXMLHTTP();
		if (req) {
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						// alert(this.responseText);
					} else {
						alert("Problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
	
	}
/*
function marksUpdate(caid,examid,question,srid)
	{
		alert(caid);
		alert(examid);
		alert(question);
		alert(srid);
	}
*/