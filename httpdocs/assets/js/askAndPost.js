/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

function askAndPost(postData) {
	var link = $(this) ;
	noty({
		text: '<p><strong>' + link.data('question') + '</strong></p>',
		type: 'confirmation',
		buttons: [
			{
				addClass: 'btn btn-danger', 
				text: link.data('yes-label'), 
				onClick: function($noty) {
					$noty.close();
					$.post("/", postData, function(result) {
						if (result === true) {
							noty({
								text: link.data('success-notification-text'),
								type: 'success',
								timeout: 4000,
								dismissQueue: true,
								layout: 'top',
								theme: 'defaultTheme'
							}) ;
							$('#' + link.data('success-text-id')).text(link.data('success-text')) ;
						}
					});
				}
			},
			{
				addClass: 'btn btn-primary', 
				text: link.data('noLabel') , 
				onClick: function($noty) {
					$noty.close();
				}
			}
		],
		dismissQueue: true,
		layout: 'center',
		modal: 'true',
		theme: 'defaultTheme'
	}) ;
}

$('a[data-op="return-start"]').click(function(e) {
	e.preventDefault() ;
	askAndPost.call(
		$(this),
		"token=" + $(this).data('token') 
		+ "&return_id=" + $(this).data("return-id") 
		+ "&action=return_start"
	) ;
}) ;

$('a[data-op="return-complete"]').click(function(e) {
	e.preventDefault() ;
	askAndPost.call(
		$(this),
		"token=" + $(this).data('token') 
		+ "&return_id=" + $(this).data("return-id") 
		+ "&action=return_complete"
	) ;
}) ;

$('a[data-op="return-approve"]').click(function(e) {
	e.preventDefault() ;
	askAndPost.call(
		$(this),
		"token=" + $(this).data('token') 
		+ "&return_id=" + $(this).data("return-id") 
		+ "&action=return_approve"
	) ;
}) ;

$('a[data-op="contribution-proposal-approve"]').click(function(e) {
	e.preventDefault() ;
	askAndPost.call(
		$(this),
		"token=" + $(this).data('token') 
		+ "&contribution_id=" + $(this).data("contribution-id") 
		+ "&action=contribution_proposal_approve"
	) ;
}) ;

$('a[data-op="contribution-mark-sent"]').click(function(e) {
	e.preventDefault() ;
	askAndPost.call(
		$(this),
		"token=" + $(this).data('token') 
		+ "&contribution_id=" + $(this).data("contribution-id") 
		+ "&action=contribution_mark_sent"
	) ;
}) ;

$('a[data-op="contribution-mark-received"]').click(function(e) {
	e.preventDefault() ;
	askAndPost.call(
		$(this),
		"token=" + $(this).data('token') 
		+ "&contribution_id=" + $(this).data("contribution-id") 
		+ "&action=contribution_mark_received"
	) ;
}) ;

$('a[data-op="need-contribute"]').click(function(e) {
	e.preventDefault() ;
	askAndPost.call(
		$(this),
		"token=" + $(this).data('token') 
		+ "&need_type=" + $(this).data("need-type") 
		+ "&need_id=" + $(this).data("need-id") 
		+ "&organisation_id=" + $(this).data("organisation-id") 
		+ "&project_id=" + $(this).data("project-id") 
		+ "&action=need_contribute"
	) ;
}) ;

$('a[data-op="want"]').click(function(e) {
	e.preventDefault() ;
	askAndPost.call(
		$(this),
		"token=" + $(this).data('token') 
		+ "&organisation_id=" + $(this).data("organisation-id") 
		+ "&project_id=" + $(this).data("project-id") 
		+ "&action=project_want"
	) ;
}) ;

