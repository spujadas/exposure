/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

$("#location select").change(function() {
  if ($(this).val()=='') {
    var selectIds = new Array();
    $('#location select').each(function(){
      array.push($(this).attr('id')); 
    });
    
    var i = selectIds.indexOf(this.id) ;
    
    if (i < 1) {
      $.get("/place/country/" + $('#country').val(), null, function(result){$("#location").html(result);});
    }
    else {
      $.get("/place/location/" + $('#'+selectIds[i-1]).val(), null, function(result){$("#location").html(result);});
    }
  }
  else {
    $.get("/place/location/" + $(this).val(), null, function(result){$("#location").html(result);});
  } ;
});
