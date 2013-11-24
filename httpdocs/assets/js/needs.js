/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$("a[data-op='financial-need-by-amount-delete']").click(function(e) {
	e.preventDefault() ;
	var need = $(this).closest(".financial-need-by-amount") ;
	var postData = "token=" + $(this).data("token") 
		+ "&financial_need_by_amount_id=" + $(this).data("need-id") 
		+ "&action=financial_need_by_amount_delete" ;
	$.post("/", postData, function(result) {
		if (result === true) {
			need.fadeOut() ;
		}
    });
}) ;

$("a[data-op='non-financial-need-delete']").click(function(e) {
	e.preventDefault() ;
	var need = $(this).closest(".non-financial-need") ;
	var postData = need.children("input").serialize() + "&action=non_financial_need_delete" ;
	$.post("/", postData, function(result) {
		if (result === true) {
			need.fadeOut() ;
		}
    });
}) ;
