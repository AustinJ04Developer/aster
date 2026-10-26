// JavaScript Document
$(document).ready(function() {
				var dataTable = $('#designation-grid').DataTable( {
					"processing": true,
					"serverSide": true,
					"ajax":{
						url :"designation-grid-data.php", // json datasource
						type: "post",  // method  , by default get
						error: function(){  // error handling
							$(".designation-grid-error").html("");
							$("#designation-grid").append('<tbody class="designation-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
							$("#designation-grid_processing").css("display","none");
							
						}
					}
				} );
				
				
				 
			} );

