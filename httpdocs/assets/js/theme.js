/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$("#theme select").change(function() {
  if ($(this).val()=='') {
    var selectIds = new Array();
    $('#theme select').each(function(){
      array.push($(this).attr('id')); 
    });

    var i = selectIds.indexOf(this.id) ;
    
    if (i > 0) {
      $.get("/theme/" + $('#'+selectIds[i-1]).val(), null, function(result){$("#theme").html(result);});
    }
    else {
      $.get("/theme/", null, function(result){$("#theme").html(result);});
    }
  }
  else {
    $.get("/theme/" + $(this).val(), null, function(result){$("#theme").html(result);});
  } ;
});
