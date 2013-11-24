/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$('a[data-op="sign-out"]').click(function(e) {
	e.preventDefault() ;
	$("#" + $(this).data('form-id')).submit() ;
}) ;