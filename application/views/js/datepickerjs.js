$(function() {

   var options = {
		changeMonth: true,
		changeYear: true,
		dateFormat: 'dd/mm/yy',
	};
var options11 = {

		changeMonth: true,

		changeYear: true,
		dateFormat: 'dd/mm/yy',
		
		
	};
	
	var options12 = {

		changeMonth: true,

		changeYear: true,
		dateFormat: 'dd/mm/yy',
		
		
	};

	var options1 = {

		changeMonth: true,

		changeYear: true,

		dateFormat: 'mm/yy'

	};

	var options2 = {

		changeMonth: true,

		changeYear: true,

		dateFormat: 'mm/dd/yy',
		/*year = (options && options.year) ? options.year : (new Date()).getFullYear(),
		startYear: year - 100,
        finalYear: year + 100,*/

	};	
	

$( "#todaysdate" ).datepicker(options);
$( "#todaysdate1" ).datepicker(options11);
$( "#chequedate" ).datepicker(options12);
$( "#dob" ).datepicker(options);
$( "#todaysdate" ).datepicker1(options);
$( "#todaysdate" ).datepicker2(options);
$( "#todaysdate" ).datepicker3(options);
$( "#todaysdate" ).datepicker4(options);
$( "#todaysdate" ).datepicker5(options);
$( "#todaysdate" ).datepicker6(options);
$( "#todaysdate" ).datepicker7(options);
$( "#todaysdate" ).datepicker8(options);
$( "#todaysdate" ).datepicker9(options);
$( "#todaysdate" ).datepicker10(options);
});