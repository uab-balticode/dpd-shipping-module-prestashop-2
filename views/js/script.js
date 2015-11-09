/**
 * 2015 UAB BaltiCode
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License available
 * through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@balticode.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to
 * newer versions in the future.
 *
 *  @author    UAB Balticode Kęstutis Kaleckas
 *  @package   Balticode_DPD
 *  @copyright Copyright (c) 2015 UAB Balticode (http://balticode.com/)
 *  @license   http://www.gnu.org/licenses/gpl-3.0.txt  GPLv3
 */
jQuery(document).ready(function() {
    /*
    *  This is for Parcel Store selection
    */
    jQuery(".delivery_options .city").change(function() {
        showParcels(this); //change city, change parcel store list by this city
    });
    showParcels(jQuery(".delivery_options .city")); //select Parcel store if we have ony one on this from where is buying
    selectTimeStrip("time-strip"); //Select TimeStrip if availible only one

    /*
    *  This is for Delivery time show or hide
    */
    jQuery("select").change(function() {
        validate_fields( jQuery( this ).val(), jQuery( this ).attr( "name" )  )
    })
    //test all items who hide or show on load
    jQuery("select").each(function(index, value) {
        validate_fields( jQuery( this ).val(), jQuery( this ).attr( "name" )  )
    });

    function validate_fields(value,name)
    {
        var class_name = ('request_'+name).replace(/\[.+/g,'');
        if (value == 1) {
            jQuery('.'+class_name).show();
        } else {
           jQuery('.'+class_name).hide();
        }
    };

    /*
    * jQuery on some form is submit
    */
    jQuery("form").submit(function(event) {
        jQuery( "form .dynamicparceldistribution_options select").each(function(index, value) {
            selector = ".required-entry[name=\'"+value.name+"\']";
            if (! jQuery( selector ).val() ) {
                jQuery( selector ).css('color','red');
                event.preventDefault();
            } else {
                jQuery( selector ).css('color','');
            }
        });
    });
});

/*
* Test of parcel stores availible
*/
function showParcels(object)
{
    if (jQuery( object ).val() === undefined) {
        return false;
    }
    jQuery(".points optgroup").hide();
    jQuery(".points option:selected").removeAttr("selected");
    jQuery(".points [data-comment=\""+jQuery( object ).val()+"\"]").show();

    var count = jQuery(".points [data-comment=\""+jQuery( object ).val()+"\"] option").length;
    if (count == 1) {
        // jQuery(".points [data-comment=\""+jQuery( object ).val()+"\"] option").attr('selected','selected'); //TODO
        //setDeliveryOptions(id_delivery_option, id_address, value);
    } else {
        jQuery(".points option:first").attr('selected','selected');
    }
}

/*
* Select delivery time if one available
*/
function selectTimeStrip(select_name)
{
    if (jQuery("."+select_name+" option").length == 2) {
        //jQuery("."+select_name+" option:last").attr('selected','selected'); //TODO
    } else {
        jQuery("."+select_name+" option:first").attr('selected','selected');
    }
}

/*
* function For admin setings config add block
*/
function addBlock(template, heading, incrementNumberSelector)
{
    var template = jQuery(template).html(); //Get template who nead set
    var incrementNumber = (parseInt(jQuery(incrementNumberSelector).val()))+1; //Get how much is blocks and plius one to got next number
    jQuery(incrementNumberSelector).val(incrementNumber); //Increment blocks count number
    template = template.replace(/-0/g,incrementNumber) //From html template replace number to set current block number
    jQuery( heading ).append( template ); //Append html to site
}

/*
* function For admin setings config remove block
*/
function removeBlock(dom)
{
    jQuery(dom).closest('tr').remove();
}

/*
* Chaeckout set Delivery Option
*/
// function setDeliveryOptionToValue(selector, id_carrier, value, separator = ',')
// {
//     if (value !== '') value = value+',';
//     jQuery('input[id^=\''+selector+'\']:checked').val(id_carrier+separator+value);
// }

/*
*
*/
function setDeliveryOptions(id_delivery_option, id_address, value, label)
{
    var url = '';
    if (value.length == 0) {
        return '';
    }
    if (typeof(orderOpcUrl) !== 'undefined') {
        url = orderOpcUrl;
    } else {
        url = orderUrl;
    }

    jQuery.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: '?fc=module&module=dynamicparceldistribution&controller=savesettings',
        async: true,
        cache: false,
        dataType : "json",
        data: 'id_delivery_option='+id_delivery_option
            + '&value='+value
            + '&label='+label
            + '&id_address='+id_address
            //+ '&token='+static_token
            + '&allow_refresh=1',
        success: function(data)
        {
            console.log(data);
        }
    });
}

/*
* Order list add js to show call dpd carrier
*/
function showCarrierWindow(chk_arr)
{
    document.getElementById("call_dpd_carrier_popup").style.display = "block";
}

function hideCarrierWindow()
{
    document.getElementById("call_dpd_carrier_popup").style.display = "none";
}

function callCarrier()
{
    hideCarrierWindow();
}
