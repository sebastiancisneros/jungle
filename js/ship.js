// JavaScript Document
$(document).ready(function(){

  getQuote = function(shippingData, obj, btn){
    shippingJSON = JSON.parse(shippingData);
    var height = parseFloat(shippingJSON.height);
    var width = parseFloat(shippingJSON.width);
    var depth = parseFloat(shippingJSON.depth);
    var weight = parseFloat(shippingJSON.weight);
    btn.attr("disabled", true);
    if ( (height > 0) && (width > 0) && (depth > 0) && (weight > 0) ){
      $.ajax({
              type: "POST",
              url: "../shippingRates.php",
              data: { 'shippingData' : shippingData },
              success : function(msg){
                obj.html(msg);
                btn.attr("disabled", false);
              }
            });
    } else {
      vex.dialog.alert({
          message: 'Make sure the package dimmensions are correct!',
          className: 'vex-theme-default'
      });
    }
  }

  buyPostage = function(shippingData, obj, btn){
    shippingJSON = JSON.parse(shippingData);
    var service = shippingJSON.service;
    if (service == null || service == ""){
      vex.dialog.alert({
          message: 'Make sure to select a shipping service!',
          className: 'vex-theme-default'
      });
    } else {
      btn.attr("disabled", true);
      $.ajax({
              type: "POST",
              url: "../shippingRates.php",
              data: { 'shippingData' : shippingData },
              success : function(msg){
                obj.closest('.rates').html(msg);
              }
            });
    }
  }

  printLabel = function(labelData){
    $.ajax({
            type: "POST",
            url: "../shippingRates.php",
            data: { 'labelData' : labelData },
            success : function(msg){
              console.log(msg);
            }
          });
  }

  $(document).on("click", 'input[type=radio].carrier', function(event) {
    carrier = $( "input:checked" ).attr('carrier');
    service = $( "input:checked" ).val();
    $(this).closest('.rates').find('.carrier:first').val(carrier);
    $(this).closest('.rates').find('.service:first').val(service);
  });

  // GET SHIPPING ESTIMATE
  $('.getRates').click(function() {
    sD = getShippingData($(this).parent().parent().parent());
    var shippingData = '{ "orderId":"' + sD['orderId'] + '" , "name":"' + sD['name'] + '" , "company":"" , "address1":"' + sD['address1'] + '" , "address2":"' + sD['address2'] + '" , "city":"' + sD['city'] + '" , "state":"' + sD['state'] + '" , "postalCode":"' + sD['postalCode'] + '" , "height":"' + sD['height'] + '" , "depth":"' + sD['depth'] + '" , "width":"' + sD['width'] + '" , "weight":"' + sD['weight'] + '" , "carrier":"' + sD['carrier'] + '" , "service":"' + sD['service'] + '" , "request":"quote"}';

    if (/^[\],:{}\s]*$/.test(shippingData.replace(/\\["\\\/bfnrtu]/g, '@').
    replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
    replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
      //the json is ok
      getQuote(shippingData, $(this).parent().parent().children('.results'), $(this));
    }else{
      console.log('nope');
      //the json is not ok

    }
  });

  // BUY POSTAGE ACTION
  $(document).on("click", '.buyPostage', function(event) {
    target = $(this).parent();
    sD = getShippingData($(this).closest('.order'));
    var shippingData = '{ "orderId":"' + sD['orderId'] + '" , "name":"' + sD['name'] + '" , "company":"" , "address1":"' + sD['address1'] + '" , "address2":"' + sD['address2'] + '" , "city":"' + sD['city'] + '" , "state":"' + sD['state'] + '" , "postalCode":"' + sD['postalCode'] + '" , "height":"' + sD['height'] + '" , "depth":"' + sD['depth'] + '" , "width":"' + sD['width'] + '" , "weight":"' + sD['weight'] + '" , "carrier":"' + sD['carrier'] + '" , "service":"' + sD['service'] + '" , "request":"buy"}';

    if (/^[\],:{}\s]*$/.test(shippingData.replace(/\\["\\\/bfnrtu]/g, '@').
    replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
    replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
      //the json is ok
      buyPostage(shippingData, target, $(this));
    }else{
      console.log('nope');
      //the json is not ok

    }
  });

  // PRINT Label
  $(document).on("click", '.printLabel', function(event) {
    orderId = $(this).closest('.order').children('.number').html();
    labelUrl = $(this).siblings('.labelUrl').val();
    window.open("shippingRates.php?request=label&orderId="+orderId+"&labelUrl="+labelUrl , '_blank');
  });


  function getShippingData(result) {
    var shippingData = [];
    shippingData['orderId'] = $(result).children('.number').html();
    shippingData['name'] = $(result).children('.name').html();
    dimmensions = $(result).find('.dimmensions:first');
    shippingData['height'] = dimmensions.children('.height').val();
    shippingData['width'] = dimmensions.children('.width').val();
    shippingData['depth'] = dimmensions.children('.depth').val();
    shippingData['weight'] = dimmensions.children('.weight').val();
    shippingData['address1'] = dimmensions.children('.address1').val();
    shippingData['address2'] = dimmensions.children('.address2').val();
    shippingData['city'] = dimmensions.children('.city').val();
    shippingData['state'] = dimmensions.children('.state').val();
    shippingData['postalCode'] = dimmensions.children('.postalCode').val();
    shippingData['carrier'] = dimmensions.children('.carrier').val();
    shippingData['service'] = dimmensions.children('.service').val();

    return shippingData;
  }
});
