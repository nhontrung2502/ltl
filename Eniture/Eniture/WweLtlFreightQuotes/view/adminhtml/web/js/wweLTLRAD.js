/**
 * Document load function
 * @type type
 */

require([
        'jquery',
        'domReady!'
    ],
    function ($) {
        if ($("#suspend-rad-use").length > 0 && $("#suspend-rad-use").is(":disabled") == false) {
            if (!isdisabled) {
                if (($('#suspend-rad-use:checkbox:checked').length) > 0) {
                    $("#WweLtQuoteSetting_third_residentialDlvry").prop({disabled: false});
                    $("#WweLtQuoteSetting_third_RADforLiftgate").val('0');
                    $("#WweLtQuoteSetting_third_RADforLiftgate").prop({disabled: true});
                } else {
                    $("#WweLtQuoteSetting_third_residentialDlvry").prop({disabled: true});
                    $("#WweLtQuoteSetting_third_RADforLiftgate").prop({disabled: false});
                }
            }
        } else if ($("#suspend-rad-use").length > 0 && $("#suspend-rad-use").is(":disabled") == true) {
            $("#WweLtQuoteSetting_third_residentialDlvry").prop({disabled: false});
            $("#WweLtQuoteSetting_third_RADforLiftgate").prop({disabled: true});
        }

        $("#suspend-rad-use").on('click', function () {
            if (this.checked) {
                $("#WweLtQuoteSetting_third_residentialDlvry").prop({disabled: false});
                $("#WweLtQuoteSetting_third_RADforLiftgate").prop({disabled: true});
            } else {
                $("#WweLtQuoteSetting_third_residentialDlvry").prop({disabled: true});
                $("#WweLtQuoteSetting_third_RADforLiftgate").prop({disabled: false});
            }
        });
    });
