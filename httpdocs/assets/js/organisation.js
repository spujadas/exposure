/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$('a[data-op="themes-select-all"]').click(function(e) {
  e.preventDefault() ;
  $('#themes input[type="checkbox"]').prop('checked', true) ;
});

$('a[data-op="themes-deselect-all"]').click(function(e) {
  e.preventDefault() ;
  $('#themes input[type="checkbox"]').prop('checked', false) ;
});
