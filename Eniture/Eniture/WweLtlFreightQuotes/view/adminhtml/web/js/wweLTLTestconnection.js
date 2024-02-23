require(["jquery", "domReady!"], function ($) {
    /* Test Connection Validation */
    addWweLtTestConnTitle($);
    $('#WweLtTestConnBtn').click(function (event) {
        event.preventDefault();
        if ($('#config-edit-form').valid()) {
            let ajaxURL = $(this).attr('connAjaxUrl');
            WweLtTestConnectionAjaxCall($, ajaxURL);
        }
        return false;
    });
});

/**
 * Assign Title to inputs
 */
function addWweLtTestConnTitle($) {
    $('#WweLtConnSettings_first_WweLtAccountNumber').attr('title', 'Account Number');
    $('#WweLtConnSettings_first_WweLtUsername').attr('title', 'Username');
    $('#WweLtConnSettings_first_WweLtPassword').attr('title', 'Password');
    $('#WweLtConnSettings_first_WweLtAuthenticationKey').attr('title', 'Authentication Key');
    $('#WweLtConnSettings_first_WweLtLicenseKey').attr('title', 'Plugin License Key');
}

/**
 * Test connection ajax call
 * @param {type} ajaxURL
 * @returns {Success or Error}
 */
function WweLtTestConnectionAjaxCall($, ajaxURL) {
    let wweltlApiEndpoint = $('#WweLtConnSettings_first_wweltlApiEndpoint').val();

    let credentials = {
        accountNumber: $('#WweLtConnSettings_first_WweLtAccountNumber').val(),
        pluginLicenceKey: $('#WweLtConnSettings_first_WweLtLicenseKey').val(),
        apiEndpoint         : wweltlApiEndpoint
    };

    if(wweltlApiEndpoint == 'new'){
        credentials.clientId            = $('#WweLtConnSettings_first_wweltlClientId').val();
        credentials.clientSecret        = $('#WweLtConnSettings_first_wweltlClientSecret').val();
        credentials.username            = $('#WweLtConnSettings_first_wweLtUsernameNewAPI').val();
        credentials.password            = $('#WweLtConnSettings_first_wweLtPasswordNewAPI').val();
    }else{
        credentials.username            = $('#WweLtConnSettings_first_WweLtUsername').val();
        credentials.password            = $('#WweLtConnSettings_first_WweLtPassword').val();
        credentials.authenticationKey   = $('#WweLtConnSettings_first_WweLtAuthenticationKey').val();
    }

    WweLtAjaxRequest(credentials, ajaxURL, WweLtConnectSuccessFunction);
}

/**
 *
 * @param {type} data
 * @returns {undefined}
 */
function WweLtConnectSuccessFunction(data) {
    let styleClass = data.error ? 'error': 'success';
    WweLtResponseMessage('WweLt-con-msg',styleClass, data.msg);
}

/**
 * Test connection ajax call
 * @param {object} $
 * @param {string} ajaxURL
 * @returns {function}
 */
 function wweLTLPlanRefresh(e){
    let ajaxURL = e.getAttribute('planRefAjaxUrl');
    let parameters = {};
    WweLtAjaxRequest(parameters, ajaxURL, wweLtlPlanRefreshResponse);
}

/**
 * Handle response
 * @param {object} data
 * @returns {void}
 */
function wweLtlPlanRefreshResponse(data){
    document.location.reload(true);
}
