/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$("a[data-op='bookmark-remove']").click(function(e) {
	e.preventDefault() ;
	var listItem = $(this).closest('li') ;
	var postData = "token=" + $(this).closest('ul').data('token')
					+ "&project_id=" + $(this).data("project-id")
					+ "&action=project_bookmark_remove" ;
	$.post("/", postData, function(result) {
		if (result === true) {
			listItem.fadeOut() ;
		}
    });
}) ;

$("a[data-op='bookmark-add']").click(function(e) {
	e.preventDefault() ;
	var link = $(this) ;
	var postData = "token=" + link.data('token')
		+ "&project_id=" + link.data('project-id')
		+ "&action=project_bookmark_add" ;
	$.post("/", postData, function(result) {
		if (result === true) {
			$('#sponsor-bookmark').text(link.data('success')) ;
		}
    });
}) ;
