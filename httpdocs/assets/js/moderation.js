/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$('.moderation-ops > a').click(function(e) {
	e.preventDefault() ;
	var link = $(this) ;
	var postData = link.closest(".moderation-ops").children("input").serialize() + "&command=" + link.data('op') ;
	$.post("/", postData, function(result) {
		if (result === true) {
        	link.closest(".moderation-ops").find('.status').text(link.data('success')) ;
		}
    });
}) ;
