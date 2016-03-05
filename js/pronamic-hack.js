jQuery(document).ready(function($) {
  getAddress();
  function getAddress(){
    var $streetNumber = $('.acf-field[data-name="street_and_number"]').find('input[type="text"]'),
        $zipCode = $('.acf-field[data-name="cp"]').find('input[type="text"]'),
        $city = $('.acf-field[data-name="town"]').find('input[type="text"]'),

        $pronamicDescField = $('#pgm-description-field'),
        $pronamicAddressField = $('#pgm-address-field'),

        address = $streetNumber.val();
        address += ' ' + $zipCode.val();
        address += ' ' + $city.val();

        $pronamicAddressField.val(address);


    $streetNumber.on('change', function(){
      address = $streetNumber.val();
      address += '<br>' + $zipCode.val();
      address += ' ' + $city.val();
      $pronamicDescField.val(address);
      $pronamicAddressField.val(address);
    });
    $zipCode.on('change', function(){
      address = $streetNumber.val();
      address += '<br>' + $zipCode.val();
      address += ' ' + $city.val();
      $pronamicDescField.val(address);
      $pronamicAddressField.val(address);
    });
    $city.on('change', function(){
      address = $streetNumber.val();
      address += '<br>' + $zipCode.val();
      address += ' ' + $city.val();
      $pronamicDescField.val(address);
      $pronamicAddressField.val(address);
    });


    var $cont = $('.acf-field[data-name="town"]').parent();
    $('<div style="margin: 0 12px 15px; clear:both "><h2 style="text-align:center;font-weight: bold; font-size: 20px;"><a class="acf-required" href="#pgm-geocode-button">Don\'t forget to GEOCODE!</a></h2></div>').appendTo($cont);
  }

  $('#pgm-active-field').attr("checked", "checked");

});