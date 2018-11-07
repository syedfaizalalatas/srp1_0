/* show hide test in newdoc */
$(document).ready(function() {    
	// Makes sure the code contained doesn't run until all the DOM elements have loaded
	$('#pemilih').change(function(){
		alert("Ok");
	// $("#ujianpilihan").hide();
	// $("#ujianpilihan div").hide();
	// $("#ujianpilihan div:eq(" + $(this).attr("selectedIndex") + ")").show();
});

});

$(function() {    
	// Makes sure the code contained doesn't run until all the DOM elements have loaded

	$('#kod_status').change(function(){
  		// $('.statusdok').hide();
  		// $('#' + $(this).val()).show();
  		$("#pilihanstatusdok div").hide();
  		$("#pilihanstatusdok div:eq(" + $(this).attr("selectedIndex") + ")").show();
	});

});
