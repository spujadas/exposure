/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

function displayMessage() {
    var n = noty({
    text: $('#message').data('content'),
    type: $('#message').data('type'),
    timeout: 4000,
    dismissQueue: true,
    layout: 'top',
    theme: 'defaultTheme'
}) ;
}
$(window).load(displayMessage()) ;
