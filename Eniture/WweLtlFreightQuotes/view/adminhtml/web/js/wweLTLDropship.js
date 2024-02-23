const WweLtDsFormId = "#WweLt-ds-form";
let WweLtDsEditFormData = '';

require([
        'jquery',
        'Magento_Ui/js/modal/modal',
        'Magento_Ui/js/modal/confirm',
        'domReady!',
    ],
    function ($, modal, confirmation) {

        const addDsModal = $('#WweLt-ds-modal');
        const options = {
            type: 'popup',
            modalClass: 'WweLt-add-ds-modal',
            responsive: true,
            innerScroll: true,
            title: 'Drop ship',
            closeText: 'Close',
            focus: WweLtDsFormId + ' #WweLt-ds-nickname',
            buttons: [{
                text: $.mage.__('Save'),
                class: 'en-btn save-ds-ds',
                click: function (data) {
                    var $this = this;
                    var form_data = WweLtGetFormData($, WweLtDsFormId);
                    var ajaxUrl = WweLtDsAjaxUrl + 'SaveDropship/';

                    if ($(WweLtDsFormId).valid() && WweLtDsZipMilesValid()) {
                        //If form data is unchanged then close the modal and show updated message
                        if (WweLtDsEditFormData !== '' && WweLtDsEditFormData === form_data) {
                            WweLtResponseMessage('WweLt-ds-msg', 'success', 'Drop ship updated successfully.');
                            addDsModal.modal('closeModal');
                        } else {
                            $.ajax({
                                url: ajaxUrl,
                                type: 'POST',
                                data: form_data,
                                showLoader: true,
                                success: function (data) {
                                    if (WweLtDropshipSaveResSettings(data)) {
                                        addDsModal.modal('closeModal');
                                    }
                                },
                                error: function (result) {
                                    console.log('no response !');
                                }
                            });
                        }
                    }
                }
            }],
            keyEventHandlers: {
                tabKey: function () {
                    return;
                },
                /**
                 * Escape key press handler,
                 * close modal window
                 */
                escapeKey: function () {
                    if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                        this.options.isOpen && this.modal[0] === document.activeElement) {
                        this.closeModal();
                    }
                }
            },
            closed: function () {
                WweLtModalClose(WweLtDsFormId, '#ds-', $);
            }
        };


        $('body').on('click', '.WweLt-del-ds', function (event) {
            event.preventDefault();
            confirmation({
                title: 'WWE LTL Freight Quotes',
                content: 'Warning! If you delete this location, Drop ship location settings will be disabled against products.',
                actions: {
                    always: function () {
                    },
                    confirm: function () {
                        var dataset = event.currentTarget.dataset;
                        WweLtDeleteDropship(dataset.id, WweLtDsAjaxUrl);
                    },
                    cancel: function () {
                    }
                }
            });
            return false;
        });


        //Add DS
        $('#WweLt-add-ds-btn').on('click', function () {
            const popup = modal(options, addDsModal);
            addDsModal.modal('openModal');
        });

        //Edit WH
        $('body').on('click', '.WweLt-edit-ds', function () {
            var dsId = $(this).data("id");
            if (typeof dsId !== 'undefined') {
                WweLtEditDropship(dsId, WweLtDsAjaxUrl);
                setTimeout(function () {
                    const popup = modal(options, addDsModal);
                    addDsModal.modal('openModal');
                }, 500);
            }
        });

        //Add required to Local Delivery Fee if Local Delivery is enabled
        $(WweLtDsFormId + ' #ds-enable-local-delivery').on('change', function () {
            if ($(this).is(':checked')) {
                $(WweLtDsFormId + ' #ds-ld-fee').addClass('required');
            } else {
                $(WweLtDsFormId + ' #ds-ld-fee').removeClass('required');
            }
        });

        //Get data of Zip Code
        $(WweLtDsFormId + ' #WweLt-ds-zip').on('change', function () {
            var ajaxUrl = WweLtAjaxUrl + 'WweLtlOriginAddress/';
            $(WweLtDsFormId + ' #ds-city').val('');
            $(WweLtDsFormId + ' #ds-state').val('');
            $(WweLtDsFormId + ' #ds-country').val('');
            WweLtGetAddressFromZip(ajaxUrl, this, WweLtGetDsAddressResSettings);
            $(WweLtDsFormId).validation('clearError');
        });
    });

/**
 * Set Address from zipCode
 * @param {type} data
 * @returns {Boolean}
 */
function WweLtGetDsAddressResSettings(data) {
    let id = WweLtDsFormId;
    if (data.country === 'US' || data.country === 'CA') {
        var oldNick = jQuery('#WweLt-ds-nickname').val();
        var newNick = '';
        var zip = jQuery('#WweLt-ds-zip').val();
        if (data.postcode_localities === 1) {
            jQuery(id + ' .city-select').show();
            jQuery(id + ' #ds-actname').replaceWith(data.city_option);
            jQuery(id + ' .city-multiselect').replaceWith(data.city_option);
            jQuery(id).on('change', '.city-multiselect', function () {
                var city = jQuery(this).val();
                jQuery(id + ' #ds-city').val(city);
                jQuery(id + ' #WweLt-ds-nickname').val(WweLtSetDsNickname(oldNick, zip, city));
            });
            jQuery(id + " #ds-city").val(data.first_city);
            jQuery(id + ' #ds-state').val(data.state);
            jQuery(id + ' #ds-country').val(data.country);
            jQuery(id + ' .city-input').hide();
            newNick = WweLtSetDsNickname(oldNick, zip, data.first_city);
        } else {
            jQuery(id + ' .city-input').show();
            jQuery(id + ' #wh-multi-city').removeAttr('value');
            jQuery(id + ' .city-select').hide();
            jQuery(id + ' #ds-city').val(data.city);
            jQuery(id + ' #ds-state').val(data.state);
            jQuery(id + ' #ds-country').val(data.country);
            newNick = WweLtSetDsNickname(oldNick, zip, data.city);
        }
        jQuery(id + ' #WweLt-ds-nickname').val(newNick);
    } else if (data.msg) {
        WweLtResponseMessage('WweLt-ds-modal-msg', 'error', data.msg);
    }
    return true;
}


function WweLtDsZipMilesValid() {
    let id = WweLtDsFormId;
    var enable_instore_pickup = jQuery(id + " #ds-enable-instore-pickup").is(':checked');
    var enable_local_delivery = jQuery(id + " #ds-enable-local-delivery").is(':checked');
    if (enable_instore_pickup || enable_local_delivery) {
        var instore_within_miles = jQuery(id + " #ds-within-miles").val();
        var instore_postal_code = jQuery(id + " #ds-postcode-match").val();
        var ld_within_miles = jQuery(id + " #ds-ld-within-miles").val();
        var ld_postal_code = jQuery(id + " #ds-ld-postcode-match").val();

        switch (true) {
            case (enable_instore_pickup && (instore_within_miles.length == 0 && instore_postal_code.length == 0)):
                jQuery(id + ' .ds-instore-miles-postal-err').show('slow');
                scrollHideMsg(2, '', id + ' #ds-is-heading-left', '.ds-instore-miles-postal-err');
                return false;

            case (enable_local_delivery && (ld_within_miles.length == 0 && ld_postal_code.length == 0)):
                jQuery(id + ' .ds-local-miles-postals-err').show('slow');
                scrollHideMsg(2, '', id + ' #ds-ld-heading-left', '.ds-local-miles-postals-err');
                return false;

        }
    }
    return true;
}


function WweLtDropshipSaveResSettings(data) {
    let styleClass = '';
    if (data.insert_qry == 1) {
        jQuery('#append-dropship tr:last').after(
            '<tr id="row_' + data.id + '" data-id="' + data.id + '">' +
            '<td>' + data.nickname + '</td>' +
            WweLtGetRowData(data, 'ds') + '</tr>');
    } else if (data.update_qry == 1) {
        jQuery('tr[id=row_' + data.id + ']').html('<td>' + data.nickname + '</td>' + WweLtGetRowData(data, 'ds'));
    } else {
        WweLtResponseMessage('WweLt-ds-modal-msg', 'error', data.msg);
        return false;
    }
    WweLtResponseMessage('WweLt-ds-msg', 'success', data.msg);
    return true;
}

function WweLtEditDropship(dataId, ajaxUrl) {
    ajaxUrl = ajaxUrl + 'EditDropship/';
    const parameters = {
        'action': 'edit_dropship',
        'edit_id': dataId
    };

    WweLtAjaxRequest(parameters, ajaxUrl, WweLtDropshipEditResSettings);
    return false;
}

function WweLtDropshipEditResSettings(data) {
    let id = WweLtDsFormId;
    if (data[0]) {
        jQuery(id + ' #ds-edit-form-id').val(data[0].warehouse_id);
        jQuery(id + ' #WweLt-ds-zip').val(data[0].zip);
        jQuery(id + ' #WweLt-ds-nickname').val(data[0].nickname);
        jQuery(id + ' .city-select').hide();
        jQuery(id + ' .city-input').show();
        jQuery(id + ' #ds-city').val(data[0].city);
        jQuery(id + ' #ds-state').val(data[0].state);
        jQuery(id + ' #ds-country').val(data[0].country);

        if (WweLtAdvancePlan) {
            // Load instore pickup and local delivery data
            if ((data[0].in_store != null && data[0].in_store != 'null')
                || (data[0].local_delivery != null && data[0].local_delivery != 'null')) {
                WweLtSetInspAndLdData(data[0], '#ds-');
                //WweLtSetInspAndLdData(data[0], '#ds-');
            }
        }

        WweLtDsEditFormData = WweLtGetFormData(jQuery, WweLtDsFormId);
    }
    return true;
}

function WweLtDeleteDropship(deleteid, ajaxUrl) {
    ajaxUrl = ajaxUrl + 'DeleteDropship/';
    let parameters = {
        'action': 'delete_dropship',
        'delete_id': deleteid
    };
    WweLtAjaxRequest(parameters, ajaxUrl, WweLtDropshipDeleteResSettings);
    return false;
}

function WweLtDropshipDeleteResSettings(data) {
    if (data.qryResp == 1) {
        jQuery('#row_' + data.deleteID).remove();
    }
    WweLtResponseMessage('WweLt-ds-msg', 'success', data.msg);
    return true;
}

function WweLtSetDsNickname(oldNick, zip, city) {
    let nickName = '';
    let curNick = 'DS_' + zip + '_' + city;
    let pattern = /DS_[0-9 a-z A-Z]+_[a-z A-Z]*/;
    let regex = new RegExp(pattern, 'g');
    if (oldNick !== '') {
        nickName = regex.test(oldNick) ? curNick : oldNick;
    }
    return nickName;
}
