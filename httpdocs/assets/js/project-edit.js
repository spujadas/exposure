/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$(function() {
	$.datepicker.setDefaults({dateFormat: "dd/mm/yy", numberOfMonths: 2}) ;
	$( "input[name='sponsoring_deadline'], input[name='event_date']" ).datepicker();

	$('#no_sponsoring_deadline').click(function(e) {
		e.preventDefault() ;
		$("input[name='sponsoring_deadline']").val('') ;
	}) ;

	$('#no_event_date').click(function(e) {
		e.preventDefault() ;
		$("input[name='event_date']").val('') ;
	}) ;

	$("#country").change(function() {
    	$.get(
      		"/place/country/" + $(this).val(),
      		null,
      		function(result){
       			$("#location").html(result);
      		}
    	);
  	});
});