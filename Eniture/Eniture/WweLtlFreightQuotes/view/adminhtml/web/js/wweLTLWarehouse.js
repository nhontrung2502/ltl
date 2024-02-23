var WweLtWhFormId = "#WweLt-wh-form";
var WweLtWhEditFormData = '';
require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'domReady!'
    ],
    function ($, modal) {

        let addWhModal = $('#WweLt-wh-modal');
        let formId = WweLtWhFormId;
        let options = {
            type: 'popup',
            modalClass: 'WweLt-add-wh-modal',
            responsive: true,
            innerScroll: true,
            title: 'Warehouse',
            closeText: 'Close',
            focus: formId + ' #WweLt-wh-zip',
            buttons: [{
                text: $.mage.__('Save'),
                class: 'en-btn save-wh-ds',
                click: function (data) {
                    var $this = this;
                    var formData = WweLtGetFormData($, formId);
                    var ajaxUrl = WweLtAjaxUrl + 'SaveWarehouse/';

                    if ($(formId).valid() && WweLtZipMilesValid()) {
                        //If form data is unchanged then close the modal and show updated message
                        if (WweLtWhEditFormData !== '' && WweLtWhEditFormData === formData) {
                            WweLtResponseMessage('WweLt-wh-msg', 'success', 'Warehouse updated successfully.');
                            addWhModal.modal('closeModal');
                        } else {
                            $.ajax({
                                url: ajaxUrl,
                                type: 'POST',
                                data: formData,
                                showLoader: true,
                                success: function (data) {
                                    if (WweLtWarehouseSaveResSettings(data)) {
                                        addWhModal.modal('closeModal');
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
                WweLtModalClose(formId, '#', $);
            }
        };

        //Add WH
        $('#WweLt-add-wh-btn').on('click', function () {
            var popup = modal(options, addWhModal);
            addWhModal.modal('openModal');
        });

        //Edit WH
        $('body').on('click', '.WweLt-edit-wh', function () {
            var whId = $(this).data("id");
            if (typeof whId !== 'undefined') {
                WweLtEditWarehouse(whId, WweLtAjaxUrl);
                setTimeout(function () {
                    var popup = modal(options, addWhModal);
                    addWhModal.modal('openModal');
                }, 500);
            }
        });

        //Delete WH
        $('body').on('click', '.WweLt-del-wh', function () {
            var whId = $(this).data("id");
            if (typeof whId !== 'undefined') {
                WweLtDeleteWarehouse(whId, WweLtAjaxUrl);
            }
        });

        //Add required to Local Delivery Fee if Local Delivery is enabled
        $(formId + ' #enable-local-delivery').on('change', function () {
            if ($(this).is(':checked')) {
                $(formId + ' #ld-fee').addClass('required');
            } else {
                $(formId + ' #ld-fee').removeClass('required');
            }
        });

        //Get data of Zip Code
        $(formId + ' #WweLt-wh-zip').on('change', function () {
            var ajaxUrl = WweLtAjaxUrl + 'WweLtlOriginAddress/';
            $(formId + ' #wh-origin-city').val('');
            $(formId + ' #wh-origin-state').val('');
            $(formId + ' #wh-origin-country').val('');
            WweLtGetAddressFromZip(ajaxUrl, this, WweLtGetAddressResSettings);
            $(formId).validation('clearError');
        });
    }
);


function WweLtGetAddressResSettings(data) {
    let id = WweLtWhFormId;
    if (data.country === 'US' || data.country === 'CA') {
        if (data.postcode_localities === 1) {
            jQuery(id + ' .city-select').show();
            jQuery(id + ' #actname').replaceWith(data.city_option);
            jQuery(id + ' .city-multiselect').replaceWith(data.city_option);
            jQuery(id).on('change', '.city-multiselect', function () {
                var city = jQuery(this).val();
                jQuery(id + ' #wh-origin-city').val(city);
            });
            jQuery(id + " #wh-origin-city").val(data.first_city);
            jQuery(id + " #wh-origin-state").val(data.state);
            jQuery(id + " #wh-origin-country").val(data.country);
            jQuery(id + ' .city-input').hide();
        } else {
            jQuery(id + ' .city-input').show();
            jQuery(id + ' #wh-multi-city').removeAttr('value');
            jQuery(id + ' .city-select').hide();
            jQuery(id + " #wh-origin-city").val(data.city);
            jQuery(id + " #wh-origin-state").val(data.state);
            jQuery(id + " #wh-origin-country").val(data.country);
        }
    } else if (data.msg) {
        WweLtResponseMessage('WweLt-wh-modal-msg', 'error', data.msg);
    }
    return true;
}


function WweLtZipMilesValid() {
    let id = WweLtWhFormId;
    var enable_instore_pickup = jQuery(id + " #enable-instore-pickup").is(':checked');
    var enable_local_delivery = jQuery(id + " #enable-local-delivery").is(':checked');
    if (enable_instore_pickup || enable_local_delivery) {
        var instore_within_miles = jQuery(id + " #within-miles").val();
        var instore_postal_code = jQuery(id + " #postcode-match").val();
        var ld_within_miles = jQuery(id + " #ld-within-miles").val();
        var ld_postal_code = jQuery(id + " #ld-postcode-match").val();

        switch (true) {
            case (enable_instore_pickup && (instore_within_miles.length == 0 && instore_postal_code.length == 0)):
                jQuery(id + ' .wh-instore-miles-postal-err').show('slow');
                scrollHideMsg(2, '', id + ' #wh-is-heading-left', '.wh-instore-miles-postal-err');
                return false;

            case (enable_local_delivery && (ld_within_miles.length == 0 && ld_postal_code.length == 0)):
                jQuery(id + ' .wh-local-miles-postals-err').show('slow');
                scrollHideMsg(2, '', id + ' #wh-ld-heading-left', '.wh-local-miles-postals-err');
                return false;
        }
    }
    return true;
}

function WweLtWarehouseSaveResSettings(data) {

    WweLtAddWarehouseRestriction(data.canAddWh);
    if (data.insert_qry == 1) {
        jQuery('#append-warehouse tr:last').after(
            '<tr id="row_' + data.id + '" data-id="' + data.id + '">' + WweLtGetRowData(data, 'wh') + '</tr>');
    } else if (data.update_qry == 1) {
        jQuery('tr[id=row_' + data.id + ']').html(WweLtGetRowData(data, 'wh'));
    } else {
        //to be changed
        WweLtResponseMessage('WweLt-wh-modal-msg', 'error', data.msg);
        return false;
    }
    WweLtResponseMessage('WweLt-wh-msg', 'success', data.msg);
    return true;
}

/**
 * Edit warehouse
 * @param {type} dataId
 * @param {type} ajaxUrl
 * @returns {Boolean}
 */
function WweLtEditWarehouse(dataId, ajaxUrl) {
    ajaxUrl = ajaxUrl + 'EditWarehouse/';
    let parameters = {
        'action': 'edit_warehouse',
        'edit_id': dataId
    };
    WweLtAjaxRequest(parameters, ajaxUrl, WweLtWarehouseEditResSettings);
}

function WweLtWarehouseEditResSettings(data) {
    if (data.error == 1) {
        WweLtResponseMessage('WweLt-wh-msg', 'error', data.msg);
        jQuery('#WweLt-wh-modal').modal('closeModal');
        return false
    }
    let id = WweLtWhFormId;
    if (data[0]) {
        jQuery(id + ' #edit-form-id').val(data[0].warehouse_id);
        jQuery(id + ' #WweLt-wh-zip').val(data[0].zip);
        jQuery(id + ' .city-select').hide();
        jQuery(id + ' .city-input').show();
        jQuery(id + ' #wh-origin-city').val(data[0].city);
        jQuery(id + ' #wh-origin-state').val(data[0].state);
        jQuery(id + ' #wh-origin-country').val(data[0].country);

        if (WweLtAdvancePlan) {
            // Load instorepikup and local delivery data
            if ((data[0].in_store != null && data[0].in_store != 'null')
                || (data[0].local_delivery != null && data[0].local_delivery != 'null')) {
                WweLtSetInspAndLdData(data[0], '#');
            }
        }
        WweLtWhEditFormData = WweLtGetFormData(jQuery, WweLtWhFormId);
    }
    return true;
}

/**
 * Delete selected Warehouse
 * @param {int} dataId
 * @param {string} ajaxUrl
 * @returns {boolean}
 */
function WweLtDeleteWarehouse(dataId, ajaxUrl) {
    ajaxUrl = ajaxUrl + 'DeleteWarehouse/';
    let parameters = {
        'action': 'delete_warehouse',
        'delete_id': dataId
    };
    WweLtAjaxRequest(parameters, ajaxUrl, WweLtWarehouseDeleteResSettings);
    return false;
}

function WweLtWarehouseDeleteResSettings(data) {
    if (data.qryResp == 1) {
        jQuery('#row_' + data.deleteID).remove();
        WweLtAddWarehouseRestriction(data.canAddWh);
    }
    WweLtResponseMessage('WweLt-wh-msg', 'success', data.msg);
    //scrollHideMsg(1, 'html,body', '.wh-text', '.WweLt-wh-msg');
    return true;
}

