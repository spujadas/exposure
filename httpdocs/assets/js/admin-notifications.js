/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$('a[data-op="notification-read"]').click(function(e) {
	e.preventDefault() ;
	var url = $(this).attr('href') ;
	var notification = $(this).closest(".notification") ;
	var postData = 
		"token=" + $('#notifications').data("token") 
		+ "&notification_type=" + notification.data("notification-type") 
		+ "&notification_id=" + notification.data("notification-id") 
		+ "&action=admin_notification_mark_read" ;
	$.post("/", postData, function(result) {
		if (result === true) {
        	window.location.href = url;
		}
    });
}) ;

$("a[data-op='notification-delete']").click(function(e) {
	e.preventDefault() ;
	var notification = $(this).closest(".notification") ;
	var postData = 
		"token=" + $('#notifications').data("token") 
		+ "&notification_type=" + notification.data("notification-type") 
		+ "&notification_id=" + notification.data("notification-id") 
		+ "&action=admin_notification_delete" ;
	$.post("/", postData, function(result) {
		if (result === true) {
			notification.fadeOut() ;
        	updateNotificationDisplay() ;
		}
    });
}) ;

$("a[data-op='notification-archive']").click(function(e) {
	e.preventDefault() ;
	var notification = $(this).closest(".notification") ;
	var postData = 
		"token=" + $('#notifications').data("token") 
		+ "&notification_type=" + notification.data("notification-type") 
		+ "&notification_id=" + notification.data("notification-id") 
		+ "&action=admin_notification_archive" ;
	$.post("/", postData, function(result) {
		if (result === true) {
			if ($('#notifications').data('hide-archived-notifications') === true) {
				notification.fadeOut() ;
			}
			else {
	        	var span = notification.find('.notification-unread, .notification-read') ;
	        	span.addClass('notification-archived') ;
	        	span.removeClass('notification-unread notification-read') ;
	        	notification.find('.label').remove() ;
	        	notification.find('a[data-op="notification-archive"]').remove() ;
			}
	    	updateNotificationDisplay() ;
	    }
    });
}) ;

$(document).ready(function() {
	if ($('#notifications li.notification').length > 0) {
		$('#no-notifications').hide() ;
	}	
}) ;

function updateNotificationDisplay() {
	if ($('#notifications li.notification').length === 0) {
		$('#notifications').hide() ;
		$('#no-notifications').show() ;
	}
}
