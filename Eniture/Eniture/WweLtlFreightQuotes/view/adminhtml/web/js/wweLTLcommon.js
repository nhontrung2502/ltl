require([
        'jquery',
        'jquery/validate',
        'mage/translate',
        'domReady!'
    ],
    function ($) {

        $('.bootstrap-tagsinput input').bind('keyup keydown', function (event) {
            validateAlphaNumOnly($, this);
        });

        $('#WweLtConnSettings_first span, #WweLtQuoteSetting_third span').attr('data-config-scope', '');

        $('#WweLtQuoteSetting_third_hndlngFee').attr('title', 'Handling Fee / Markup');


        $('.add-ds-btn, .add-wh-btn').click(function () {
            $('.WweLt-wh-overlay').show();
        });

        $.validator.addMethod(
            'validate-WweLt-decimal-limit-2', function (value) {
                return !!(validateDecimal($, value, 2));
            }, 'Maximum 2 digits allowed after decimal point.');
        $.validator.addMethod(
            'validate-WweLt-decimal-limit-3', function (value) {
                return !!(validateDecimal($, value, 3));
            }, $.mage.__('Maximum 3 digits allowed after decimal point.'));
        $.validator.addMethod(
            'validate-WweLt-positive-decimal-limit-2', function (value) {
                return !!(validatePositiveDecimal($, value, 2));
            }, 'Maximum 2 digits allowed after decimal point, and number should be positive.');

        $('#WweLtQuoteSetting_third_hndlngFee').attr('title', 'Handling Fee / Markup');

        $('.hide-val').click(function () {
            WweLtEmptyFieldsAndErr('#WweLt-wh-form');
        });

        $('.hide_drop_val').click(function () {
            WweLtEmptyFieldsAndErr('#WweLt-dropship-form');
        });

        WweLtConnSettingsNote($);
        WweLtRatingMethodComment(); //check windows load label
        $('#WweLtQuoteSetting_third_ratingMethod').on('change', function () {
            WweLtRatingMethodComment(); //Add label on change rating methodes
        });

        // Set focus on first input field
        $('.add-wh-btn').click(function () {
            setTimeout(function () {
                if ($('.add-wh-popup').is(':visible')) {
                    $('.add-wh-input > input').eq(0).focus();
                }
            }, 500);
        });
        $('.add-ds-btn').click(function () {
            setTimeout(function () {
                if ($('.ds-popup').is(':visible')) {
                    $('.ds-input > input').eq(0).focus();
                }
            }, 500);
        });

        $('.hide-val').click(function () {
            $('#edit-form-id').val('');
            $("#WweLt-wh-zip").val('');
            $('.city-select').hide();
            $('.city-input').show();
            $("#wh-origin-city").val('');
            $("#wh-origin-state").val('');
            $("#wh-origin-country").val('');
        });

        $('#WweLtQuoteSetting_third_liftGate').on('change', function () {
            changeLiftgateOption('#WweLtQuoteSetting_third_OfferLiftgateAsAnOption', this.value);
            $('#WweLtQuoteSetting_third_RADforLiftgate').val('0');
        });

        $('#WweLtQuoteSetting_third_OfferLiftgateAsAnOption').on('change', function () {
            changeLiftgateOption('#WweLtQuoteSetting_third_liftGate', this.value);
        });

        $('#WweLtQuoteSetting_third_RADforLiftgate').on('change', function () {
            changeLiftgateOption('#WweLtQuoteSetting_third_liftGate', (this.value == 'yes') ? '1' : '0');
            $('#WweLtQuoteSetting_third_liftGate').val('0');
        });

    });

/**
 * Get address against zipcode from smart street api
 * @param {string} ajaxUrl
 * @param $this
 * @param callfunction
 * @returns {Boolean}
 */
function WweLtGetAddressFromZip(ajaxUrl, $this, callfunction) {
    const zipCode = $this.value;
    if (zipCode === '') {
        return false;
    }
    const parameters = {'origin_zip': zipCode};
    WweLtAjaxRequest(parameters, ajaxUrl, callfunction);
}

/*function validateDecimal($, ele, validDecimal) {
    var input = $(ele);
    var oldVal = input.val();
    if (validDecimal === 4) {
        var pattern = /^\d*(\.\d{0,4})?$/;
    } else {
        var pattern = /^\d*(\.\d{0,2})?$/;
    }
    var regex = new RegExp(pattern, 'g');
    setTimeout(function () {
        var newVal = input.val();
        if (!regex.test(newVal)) {
            input.val(oldVal);
        }
    }, 4);
}*/

function validateDecimal($, value, limit) {
    let pattern;
    switch (limit) {
        case 4:
            pattern = /^[+-]?\d*(\.\d{0,4})?$/;
            break;
        case 3:
            pattern = /^[+-]?\d*(\.\d{0,3})?$/;
            break;
        default:
            pattern = /^[+-]?\d*(\.\d{0,2})?$/;
            break;
    }
    let regex = new RegExp(pattern, 'g');
    return regex.test(value);
}


function validatePositiveDecimal($, value, limit) {
    let pattern;
    switch (limit) {
        case 4:
            pattern = /^[+]?\d*(\.\d{0,4})?$/;
            break;
        case 3:
            pattern = /^[+]?\d*(\.\d{0,3})?$/;
            break;
        default:
            pattern = /^[+]?\d*(\.\d{0,2})?$/;
            break;
    }
    let regex = new RegExp(pattern, 'g');
    return regex.test(value);
}

/*
* Hide message
 */
function scrollHideMsg(scrollType, scrollEle, scrollTo, hideEle) {

    if (scrollType == 1) {
        jQuery(scrollEle).animate({scrollTop: jQuery(scrollTo).offset().top - 170});
    } else if (scrollType == 2) {
        jQuery(scrollTo)[0].scrollIntoView({behavior: "smooth"});
    }
    setTimeout(function () {
        jQuery(hideEle).hide('slow');
    }, 5000);
}

function validateAlphaNumOnly($, element) {
    var value = $(element);
    value.val(value.val().replace(/[^a-z0-9]/g, ''));
}

/**
 * Display connection setting fedex account note
 */
function WweLtConnSettingsNote($) {
    var divAfter = '<div class="message message-notice notice conn-setting-note"><div data-ui-id="messages-message-notice">You must have a Worldwide Express account to use this application. If you do not have one, click <a target="_blank" href="https://eniture.com/request-worldwide-express-account-number/">here</a> to access the new account request form.</div>';
    var carrierDiv = '#WweLtConnSettings_first-head';
    WweLtNotesToggleHandling($, divAfter, '.conn-setting-note', carrierDiv);
}

function WweLtCurrentPlanNote($, planMsg, carrierDiv) {
    let divAfter = '<div class="message message-notice notice WweLt-plan-note"><div data-ui-id="messages-message-notice">' + planMsg + '</div></div>';
    WweLtNotesToggleHandling($, divAfter, '.WweLt-plan-note', carrierDiv);
}

function WweLtNotesToggleHandling($, divAfter, className, carrierDiv) {

    if ($(carrierDiv).attr('class') === 'open') {
        $(carrierDiv).after(divAfter);
    }
    $(carrierDiv).click(function () {
        if ($(carrierDiv).attr('class') === 'open') {
            $(carrierDiv).after(divAfter);
        } else if ($(className).length) {
            $(className).remove();
        }
    });
}

function changeLiftgateOption(selectId, optionVal) {
    if (optionVal == 1) {
        jQuery(selectId).val(0);
    }
}

/**
 * Add label to rating method
 */

function WweLtRatingMethodComment() {
    var ratingMethod = jQuery('#WweLtQuoteSetting_third_ratingMethod').val();
    if (ratingMethod == 3) {
        jQuery('#WweLtQuoteSetting_third_ratingMethod').next().text('Displays a single rate based on an average of a specified number of least expensive options.');
        jQuery('#WweLtQuoteSetting_third_options').next().text('Number of options to include in the calculation of the average.');
        jQuery('#WweLtQuoteSetting_third_labelAs').next().text('What the user sees during checkout, e.g. "Freight". If left blank will default to "Freight".');
    } else if (ratingMethod == 1) {
        jQuery('#WweLtQuoteSetting_third_ratingMethod').next().text('Displays a least expensive option.');
        jQuery('#WweLtQuoteSetting_third_labelAs').next().text('What the user sees during checkout e.g. "Freight". Leave blank to display carrier name.');
    } else {
        jQuery('#WweLtQuoteSetting_third_options').next().text('Number of options to display in the shopping cart.');
        jQuery('#WweLtQuoteSetting_third_ratingMethod').next().text('Displays list of specified number of least expensive options.');
    }
}

/**
 * Set empty values to warehouse and dropship fields and remove error class
 * @param {string} form_id
 */
function WweLtEmptyFieldsAndErr(form_id) {
    jQuery(form_id + " input[type='text']").each(function () {
        jQuery(this).val('');
        jQuery('.err').text('');
    });
    jQuery(jQuery(".bootstrap-tagsinput").find("span[data-role=remove]")).trigger("click");
    jQuery(form_id + " input[type='checkbox']").each(function () {
        jQuery(this).prop('checked', false);
    });
    jQuery('.city-select').hide();
    jQuery('.city-input').show();
    jQuery('#edit-form-id').val('');
    jQuery('#edit-ds-form-id').val('');
}

/**
 * @param canAddWh
 */
function WweLtAddWarehouseRestriction(canAddWh) {
    switch (canAddWh) {
        case 0:
            jQuery("#append-warehouse").find("tr").removeClass('inactiveLink');
            jQuery('.add-wh-btn').addClass('inactiveLink');
            if (jQuery(".required-plan-msg").length == 0) {
                jQuery('.add-wh-btn').after('<a href="https://eniture.com/magento2-worldwide-express-ltl-freight/" target="_blank" class="required-plan-msg">Standard Plan required</a>');
            }
            jQuery("#append-warehouse").find("tr:gt(1)").addClass('inactiveLink');
            break;
        case 1:
            jQuery('#WweLt-add-wh-btn').removeClass('inactiveLink');
            jQuery('.required-plan-msg').remove();
            jQuery("#append-warehouse").find("tr").removeClass('inactiveLink');
            break;
        default:
            break;
    }

}

/**
 * call for warehouse ajax requests
 * @param {array} parameters
 * @param {string} ajaxUrl
 * @param {string} responseFunction
 * @returns {function}
 */
function WweLtAjaxRequest(parameters, ajaxUrl, responseFunction) {

    new Ajax.Request(ajaxUrl, {
        method: 'POST',
        parameters: parameters,
        onSuccess: function (response) {
            var json = response.responseText;
            var data = JSON.parse(json);
            var callbackRes = responseFunction(data);
            return callbackRes;

        }
    });
}


/**
 * Restrict Quote Settings Fields
 * @param {array} qRestriction
 */
function planQuoteRestriction(qRestriction) {
    var quoteSecRowID = "#row_fedexLtlQuoteSetting_third_";
    var quoteSecID = "#fedexLtlQuoteSetting_third_";
    var parsedData = JSON.parse(qRestriction)

    if (parsedData['advance']) {
        jQuery('' + quoteSecRowID + 'HoldAtTerminal').before('<tr><td><label><span data-config-scope=""></span></label></td><td class="value"><a href="https://eniture.com/magento2-worldwide-express-ltl-freight/" target="_blank" class="required-plan-msg adv-plan-err">Advance Plan required</a></td><td class=""></td></tr>');
        disabledFieldsLoop(parsedData['advance'], quoteSecID);
    }

}

function disabledFieldsLoop(dataArr, quoteSecID) {
    jQuery.each(dataArr, function (index, value) {
        jQuery(quoteSecID + value).attr('disabled', 'disabled');
    });
}

function loadInsidePikupAndLocalDeliveryData(data, formid) {
    var instore = JSON.parse(data.in_store);
    var localdel = JSON.parse(data.local_delivery);
    //Filling form data
    if (instore != null && instore != 'null') {
        instore.enable_store_pickup == 1 ? jQuery(formid + 'enable-instore-pickup').prop('checked', true) : '';
        jQuery(formid + 'within-miles').val(instore.miles_store_pickup);
        jQuery(formid + 'postcode-match').tagsinput('add', instore.match_postal_store_pickup);
        jQuery(formid + 'checkout_descp').val(instore.checkout_desc_store_pickup);
        instore.suppress_other == 1 ? jQuery(formid + 'ld-sup-rates').prop('checked', true) : '';
    }

    if (localdel != null && localdel != 'null') {
        localdel.enable_local_delivery == 1 ? jQuery(formid + 'enable-local-delivery').prop('checked', true) : '';
        jQuery(formid + 'ld-within-miles').val(localdel.miles_local_delivery);
        jQuery(formid + 'ld-postcode-match').tagsinput('add', localdel.match_postal_local_delivery);
        jQuery(formid + 'ld-checkout-descp').val(localdel.checkout_desc_local_delivery);
        jQuery(formid + 'ld-fee').val(localdel.fee_local_delivery);
        localdel.suppress_other == 1 ? jQuery(formid + 'ld-sup-rates').prop('checked', true) : '';
    }

}

function WweLtGetRowData(data, loc) {
    return '<td>' + data.origin_city + '</td>' +
        '<td>' + data.origin_state + '</td>' +
        '<td>' + data.origin_zip + '</td>' +
        '<td>' + data.origin_country + '</td>' +
        '<td><a href="javascript:;" data-id="' + data.id + '" title="Edit" class="WweLt-edit-' + loc + '">Edit</a>' +
        ' | ' +
        '<a href="javascript:;" data-id="' + data.id + '" title="Delete" class="WweLt-del-' + loc + '">Delete</a>' +
        '</td>';
}

//This function serialize complete form data
function WweLtGetFormData($, formId) {
    // To initialize the Disabled inputs
    var disabled = $(formId).find(':input:disabled').removeAttr('disabled');
    var formData = $(formId).serialize();
    disabled.attr('disabled', 'disabled');
    var addData = '';
    $(formId + ' input[type=checkbox]').each(function () {
        if (!$(this).is(":checked")) {
            addData += '&' + $(this).attr('name') + '=';
        }
    });
    return formData + addData;
}


/*
* @identifierElem (will be the id or class name)
* @elemType (will be the type of identifier whether it an id or an class ) id = 1, class = 0
* @msgClass (magento style class) [success, error, info, warning]
* @msg (this will be the message which you want to print)
* */
function WweLtResponseMessage(identifierId, msgClass, msg) {
    identifierId = '#' + identifierId;
    let finalClass = 'message message-';
    switch (msgClass) {
        case 'success':
            finalClass += 'success success';
            break;
        case 'info':
            finalClass += 'info info';
            break;
        case 'error':
            finalClass += 'error error';
            break;
        default:
            finalClass += 'warning warning';
            break;
    }
    jQuery(identifierId).addClass(finalClass);
    jQuery(identifierId).text(msg).show('slow');
    setTimeout(function () {
        jQuery(identifierId).hide('slow');
        jQuery(identifierId).removeClass(finalClass);
    }, 5000);
}


function WweLtModalClose(formId, ele, $) {
    $(formId).validation('clearError');
    $(formId).trigger("reset");
    $($(formId + " .bootstrap-tagsinput").find("span[data-role=remove]")).trigger("click");
    $(formId + ' ' + ele + 'ld-fee').removeClass('required');
    $(ele + 'edit-form-id').val('');
    $('.city-select').hide();
    $('.city-input').show();
}

function WweLtSetInspAndLdData(data, eleid) {
    var instore = JSON.parse(data.in_store);
    var localdel = JSON.parse(data.local_delivery);
    //Filling form data
    if (instore != null && instore != 'null') {
        instore.enable_store_pickup == 1 ? jQuery(eleid + 'enable-instore-pickup').prop('checked', true) : '';
        jQuery(eleid + 'within-miles').val(instore.miles_store_pickup);
        jQuery(eleid + 'postcode-match').tagsinput('add', instore.match_postal_store_pickup);
        jQuery(eleid + 'checkout-descp').val(instore.checkout_desc_store_pickup);
        instore.suppress_other == 1 ? jQuery(eleid + 'ld-sup-rates').prop('checked', true) : '';
    }

    if (localdel != null && localdel != 'null') {
        if (localdel.enable_local_delivery == 1) {
            jQuery(eleid + 'enable-local-delivery').prop('checked', true);
            jQuery(eleid + 'ld-fee').addClass('required');
        }
        jQuery(eleid + 'ld-within-miles').val(localdel.miles_local_delivery);
        jQuery(eleid + 'ld-postcode-match').tagsinput('add', localdel.match_postal_local_delivery);
        jQuery(eleid + 'ld-checkout-descp').val(localdel.checkout_desc_local_delivery);
        jQuery(eleid + 'ld-fee').val(localdel.fee_local_delivery);
        localdel.suppress_other == 1 ? jQuery(eleid + 'ld-sup-rates').prop('checked', true) : '';
    }
}
