require([
    'jquery',
    'jquery/validate',
    'domReady!'
],function ($) {
    var rules;
    /*$.validator.addMethod(
        'validate-WweLt-decimal-limit-3', function (value) {
            return (validateDecimal($,value,3)) ? true : false;
        }, $.mage.__('Maximum 3 digits allowed after decimal point.'));*/

    /**
     * @param {String} name
     * @param {*} method
     * @param {*} message
     * @param {*} dontSkip
     */
    $.validator.addMethod = function (name, method, message, dontSkip) {
        $.validator.methods[name] = method;
        $.validator.messages[name] = message !== undefined ? message : $.validator.messages[name];

        if (method.length < 3 || dontSkip) {
            $.validator.addClassRules(name, $.validator.normalizeRule(name));
        }
    };

    rules = {
        'max-words': [
            function (value, element, params) {
                return this.optional(element) || $.mage.stripHtml(value).match(/\b\w+\b/g).length < params;
            },
            $.mage.__('Please enter {0} words or less.')
        ],
        'min-words': [
            function (value, element, params) {
                return this.optional(element) || $.mage.stripHtml(value).match(/\b\w+\b/g).length >= params;
            },
            $.mage.__('Please enter at least {0} words.')
        ],
        'time': [
            function (value, element) {
                return this.optional(element) || /^([01]\d|2[0-3])(:[0-5]\d){0,2}$/.test(value);
            },
            $.mage.__('Please enter a valid time, between 00:00 and 23:59')
        ],
        'time12h': [
            function (value, element) {
                return this.optional(element) || /^((0?[1-9]|1[012])(:[0-5]\d){0,2}(\s[AP]M))$/i.test(value);
            },
            $.mage.__('Please enter a valid time, between 00:00 am and 12:00 pm')
        ],
        'phoneUS': [
            function (phoneNumber, element) {
                phoneNumber = phoneNumber.replace(/\s+/g, '');

                return this.optional(element) || phoneNumber.length > 9 &&
                    phoneNumber.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
            },
            $.mage.__('Please specify a valid phone number')
        ],
        'phoneUK': [
            function (phoneNumber, element) {
                return this.optional(element) || phoneNumber.length > 9 &&
                    phoneNumber.match(/^(\(?(0|\+44)[1-9]{1}\d{1,4}?\)?\s?\d{3,4}\s?\d{3,4})$/);
            },
            $.mage.__('Please specify a valid phone number')
        ],
        'mobileUK': [
            function (phoneNumber, element) {
                return this.optional(element) || phoneNumber.length > 9 &&
                    phoneNumber.match(/^((0|\+44)7\d{3}\s?\d{6})$/);
            },
            $.mage.__('Please specify a valid mobile number')
        ],
        'stripped-min-length': [
            function (value, element, param) {
                return value.length >= param;
            },
            $.mage.__('Please enter at least {0} characters')
        ],

        /* detect chars that would require more than 3 bytes */
        'validate-no-utf8mb4-characters': [
            function (value) {
                var validator = this,
                    message = $.mage.__('Please remove invalid characters: {0}.'),
                    matches = value.match(/(?:[\uD800-\uDBFF][\uDC00-\uDFFF])/g),
                    result = matches === null;

                if (!result) {
                    validator.charErrorMessage = message.replace('{0}', matches.join());
                }

                return result;
            }, function () {
                return this.charErrorMessage;
            }
        ],

        /* eslint-enable max-len */
        'pattern': [
            function (value, element, param) {
                return this.optional(element) || param.test(value);
            },
            $.mage.__('Invalid format.')
        ],
        'allow-container-className': [
            function (element) {
                if (element.type === 'radio' || element.type === 'checkbox') {
                    return $(element).hasClass('change-container-classname');
                }
            },
            ''
        ],
        'validate-no-html-tags': [
            function (value) {
                return !/<(\/)?\w+/.test(value);
            },
            $.mage.__('HTML tags are not allowed.')
        ],
        'validate-admin-password': [
            function (v) {
                var pass;

                if (v == null) {
                    return false;
                }
                pass = $.trim(v);
                // strip leading and trailing spaces
                if (pass.length === 0) {
                    return true;
                }

                if (!/[a-z]/i.test(v) || !/[0-9]/.test(v)) {
                    return false;
                }

                if (pass.length < 7) {
                    return false;
                }

                return true;
            },
            $.mage.__('Please enter 7 or more characters, using both numeric and alphabetic.')
        ],
        'validate-ssn': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^\d{3}-?\d{2}-?\d{4}$/.test(v);

            },
            $.mage.__('Please enter a valid social security number (Ex: 123-45-6789).')
        ],
        'validate-zip-us': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(v);

            },
            $.mage.__('Please enter a valid zip code (Ex: 90602 or 90602-1234).')
        ],
        'validate-currency-dollar': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v); //eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid $ amount. For example $100.00.')
        ],
        'validate-not-negative-number': [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);

                return !isNaN(v) && v >= 0;

            },
            $.mage.__('Please enter a number 0 or greater in this field.')
        ],
        // validate-not-negative-number should be replaced in all places with this one and then removed
        'validate-zero-or-greater': [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);

                return !isNaN(v) && v >= 0;

            },
            $.mage.__('Please enter a number 0 or greater in this field.')
        ],
        'validate-greater-than-zero': [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);

                return !isNaN(v) && v > 0;
            },
            $.mage.__('Please enter a number greater than 0 in this field.')
        ],
        'validate-css-length': [
            function (v) {
                if (v !== '') {
                    return (/^[0-9]*\.*[0-9]+(px|pc|pt|ex|em|mm|cm|in|%)?$/).test(v);
                }

                return true;
            },
            $.mage.__('Please input a valid CSS-length (Ex: 100px, 77pt, 20em, .5ex or 50%).')
        ],
        // Additional methods
        'validate-number': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || !isNaN($.mage.parseNumber(v)) && /^\s*-?\d*(\.\d*)?\s*$/.test(v);
            },
            $.mage.__('Please enter a valid number in this field.')
        ],
        'required-number': [
            function (v) {
                return !!v.length;
            },
            $.mage.__('Please enter a valid number in this field.')
        ],
        'validate-number-range': [
            function (v, elm, param) {
                var numValue, dataAttrRange, classNameRange, result, range, m, classes, ii;

                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                numValue = $.mage.parseNumber(v);

                if (isNaN(numValue)) {
                    return false;
                }

                dataAttrRange = /^(-?[\d.,]+)?-(-?[\d.,]+)?$/;
                classNameRange = /^number-range-(-?[\d.,]+)?-(-?[\d.,]+)?$/;
                result = true;
                range = param;

                if (typeof range === 'string') {
                    m = dataAttrRange.exec(range);

                    if (m) {
                        result = result && $.mage.isBetween(numValue, m[1], m[2]);
                    } else {
                        result = false;
                    }
                } else if (elm && elm.className) {
                    classes = elm.className.split(' ');
                    ii = classes.length;

                    while (ii--) {
                        range = classes[ii];
                        m = classNameRange.exec(range);

                        if (m) { //eslint-disable-line max-depth
                            result = result && $.mage.isBetween(numValue, m[1], m[2]);
                            break;
                        }
                    }
                }

                return result;
            },
            $.mage.__('The value is not within the specified range.'),
            true
        ],
        'validate-digits': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || !/[^\d]/.test(v);
            },
            $.mage.__('Please enter a valid number in this field.')
        ],
        'validate-forbidden-extensions': [
            function (v, elem) {
                var forbiddenExtensions = $(elem).attr('data-validation-params'),
                    forbiddenExtensionsArray = forbiddenExtensions.split(','),
                    extensionsArray = v.split(','),
                    result = true;

                this.validateExtensionsMessage = $.mage.__('Forbidden extensions has been used. Avoid usage of ') +
                    forbiddenExtensions;

                $.each(extensionsArray, function (key, extension) {
                    if (forbiddenExtensionsArray.indexOf(extension) !== -1) {
                        result = false;
                    }
                });

                return result;
            }, function () {
                return this.validateExtensionsMessage;
            }
        ],
        'validate-digits-range': [
            function (v, elm, param) {
                var numValue, dataAttrRange, classNameRange, result, range, m, classes, ii;

                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                numValue = $.mage.parseNumber(v);

                if (isNaN(numValue)) {
                    return false;
                }

                dataAttrRange = /^(-?\d+)?-(-?\d+)?$/;
                classNameRange = /^digits-range-(-?\d+)?-(-?\d+)?$/;
                result = true;
                range = param;

                if (typeof range === 'string') {
                    m = dataAttrRange.exec(range);

                    if (m) {
                        result = result && $.mage.isBetween(numValue, m[1], m[2]);
                    } else {
                        result = false;
                    }
                } else if (elm && elm.className) {
                    classes = elm.className.split(' ');
                    ii = classes.length;

                    while (ii--) {
                        range = classes[ii];
                        m = classNameRange.exec(range);

                        if (m) { //eslint-disable-line max-depth
                            result = result && $.mage.isBetween(numValue, m[1], m[2]);
                            break;
                        }
                    }
                }

                return result;
            },
            $.mage.__('The value is not within the specified range.'),
            true
        ],
        'validate-range': [
            function (v, elm) {
                var minValue, maxValue, ranges, reRange, result, values,
                    i, name, validRange, minValidRange, maxValidRange;

                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                } else if ($.validator.methods['validate-digits'] && $.validator.methods['validate-digits'](v)) {
                    minValue = maxValue = $.mage.parseNumber(v);
                } else {
                    ranges = /^(-?\d+)?-(-?\d+)?$/.exec(v);

                    if (ranges) {
                        minValue = $.mage.parseNumber(ranges[1]);
                        maxValue = $.mage.parseNumber(ranges[2]);

                        if (minValue > maxValue) { //eslint-disable-line max-depth
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
                reRange = /^range-(-?\d+)?-(-?\d+)?$/;
                result = true;
                values = $(elm).prop('class').split(' ');

                for (i = values.length - 1; i >= 0; i--) {
                    name = values[i];
                    validRange = reRange.exec(name);

                    if (validRange) {
                        minValidRange = $.mage.parseNumber(validRange[1]);
                        maxValidRange = $.mage.parseNumber(validRange[2]);
                        result = result &&
                            (isNaN(minValidRange) || minValue >= minValidRange) &&
                            (isNaN(maxValidRange) || maxValue <= maxValidRange);
                    }
                }

                return result;
            },
            $.mage.__('The value is not within the specified range.')
        ],
        'validate-alpha': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z]+$/.test(v);
            },
            $.mage.__('Please use letters only (a-z or A-Z) in this field.')
        ],
        'validate-code': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z]+[a-zA-Z0-9_]+$/.test(v);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in this field, and the first character should be a letter.') //eslint-disable-line max-len
        ],
        'validate-alphanum': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9]+$/.test(v);
            },
            $.mage.__('Please use only letters (a-z or A-Z) or numbers (0-9) in this field. No spaces or other characters are allowed.') //eslint-disable-line max-len
        ],
        'validate-not-number-first': [
            function (value) {
                return $.mage.isEmptyNoTrim(value) || /^[^0-9-\.].*$/.test(value.trim());
            },
            $.mage.__('First character must be letter.')
        ],
        'validate-date': [
            function (value, params, additionalParams) {
                var test = moment(value, additionalParams.dateFormat);

                return $.mage.isEmptyNoTrim(value) || test.isValid();
            },
            $.mage.__('Please enter a valid date.')

        ],
        'validate-date-range': [
            function (v, elm) {
                var m = /\bdate-range-(\w+)-(\w+)\b/.exec(elm.className),
                    currentYear, normalizedTime, dependentElements;

                if (!m || m[2] === 'to' || $.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                currentYear = new Date().getFullYear() + '';

                /**
                 * @param {String} vd
                 * @return {Number}
                 */
                normalizedTime = function (vd) {
                    vd = vd.split(/[.\/]/);

                    if (vd[2] && vd[2].length < 4) {
                        vd[2] = currentYear.substr(0, vd[2].length) + vd[2];
                    }

                    return new Date(vd.join('/')).getTime();
                };

                dependentElements = $(elm.form).find('.validate-date-range.date-range-' + m[1] + '-to');

                return !dependentElements.length || $.mage.isEmptyNoTrim(dependentElements[0].value) ||
                    normalizedTime(v) <= normalizedTime(dependentElements[0].value);
            },
            $.mage.__('Make sure the To Date is later than or the same as the From Date.')
        ],
        'validate-cpassword': [
            function () {
                var conf = $('#confirmation').length > 0 ? $('#confirmation') : $($('.validate-cpassword')[0]),
                    pass = false,
                    passwordElements, i, passwordElement;

                if ($('#password')) {
                    pass = $('#password');
                }
                passwordElements = $('.validate-password');

                for (i = 0; i < passwordElements.length; i++) {
                    passwordElement = $(passwordElements[i]);

                    if (passwordElement.closest('form').attr('id') === conf.closest('form').attr('id')) {
                        pass = passwordElement;
                    }
                }

                if ($('.validate-admin-password').length) {
                    pass = $($('.validate-admin-password')[0]);
                }

                return pass.val() === conf.val();
            },
            $.mage.__('Please make sure your passwords match.')
        ],
        'validate-identifier': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/.test(v);
            },
            $.mage.__('Please enter a valid URL Key (Ex: "example-page", "example-page.html" or "anotherlevel/example-page").') //eslint-disable-line max-len
        ],
        'validate-zip-international': [

            /*function(v) {
             // @TODO: Cleanup
             return Validation.get('IsEmpty').test(v) ||
             /(^[A-z0-9]{2,10}([\s]{0,1}|[\-]{0,1})[A-z0-9]{2,10}$)/.test(v);
             }*/
            function () {
                return true;
            },
            $.mage.__('Please enter a valid zip code.')
        ],
        'validate-one-required': [
            function (v, elm) {
                var p = $(elm).parent(),
                    options = p.find('input');

                return options.map(function (el) {
                    return $(el).val();
                }).length > 0;
            },
            $.mage.__('Please select one of the options above.')
        ],
        'validate-state': [
            function (v) {
                return v !== 0 || v === '';
            },
            $.mage.__('Please select State/Province.')
        ],
        'required-file': [
            function (v, elm) {
                var result = !$.mage.isEmptyNoTrim(v),
                    ovId;

                if (!result) {
                    ovId = $('#' + $(elm).attr('id') + '_value');

                    if (ovId.length > 0) {
                        result = !$.mage.isEmptyNoTrim(ovId.val());
                    }
                }

                return result;
            },
            $.mage.__('Please select a file.')
        ],
        'validate-ajax-error': [
            function (v, element) {
                element = $(element);
                element.on('change.ajaxError', function () {
                    element.removeClass('validate-ajax-error');
                    element.off('change.ajaxError');
                });

                return !element.hasClass('validate-ajax-error');
            },
            ''
        ],
        'validate-optional-datetime': [
            function (v, elm, param) {
                var dateTimeParts = $('.datetime-picker[id^="options_' + param + '"]'),
                    hasWithValue = false,
                    hasWithNoValue = false,
                    pattern = /day_part$/i,
                    i;

                for (i = 0; i < dateTimeParts.length; i++) {
                    if (!pattern.test($(dateTimeParts[i]).attr('id'))) {
                        if ($(dateTimeParts[i]).val() === 's') { //eslint-disable-line max-depth
                            hasWithValue = true;
                        } else {
                            hasWithNoValue = true;
                        }
                    }
                }

                return hasWithValue ^ hasWithNoValue;
            },
            $.mage.__('The field isn\'t complete.')
        ],
        'validate-required-datetime': [
            function (v, elm, param) {
                var dateTimeParts = $('.datetime-picker[id^="options_' + param + '"]'),
                    i;

                for (i = 0; i < dateTimeParts.length; i++) {
                    if (dateTimeParts[i].value === '') {
                        return false;
                    }
                }

                return true;
            },
            $.mage.__('This is a required field.')
        ],
        'validate-one-required-by-name': [
            function (v, elm, selector) {
                var name = elm.name.replace(/([\\"])/g, '\\$1'),
                    container = this.currentForm;

                selector = selector === true ? 'input[name="' + name + '"]:checked' : selector;

                return !!container.querySelectorAll(selector).length;
            },
            $.mage.__('Please select one of the options.')
        ],
        'less-than-equals-to': [
            function (value, element, params) {
                if ($.isNumeric($(params).val()) && $.isNumeric(value)) {
                    this.lteToVal = $(params).val();

                    return parseFloat(value) <= parseFloat($(params).val());
                }

                return true;
            },
            function () {
                var message = $.mage.__('Please enter a value less than or equal to %s.');

                return message.replace('%s', this.lteToVal);
            }
        ],
        'greater-than-equals-to': [
            function (value, element, params) {
                if ($.isNumeric($(params).val()) && $.isNumeric(value)) {
                    this.gteToVal = $(params).val();

                    return parseFloat(value) >= parseFloat($(params).val());
                }

                return true;
            },
            function () {
                var message = $.mage.__('Please enter a value greater than or equal to %s.');

                return message.replace('%s', this.gteToVal);
            }
        ],
        'validate-emails': [
            function (value) {
                var validRegexp, emails, i;

                if ($.mage.isEmpty(value)) {
                    return true;
                }
                validRegexp = /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i; //eslint-disable-line max-len
                emails = value.split(/[\s\n\,]+/g);

                for (i = 0; i < emails.length; i++) {
                    if (!validRegexp.test(emails[i].trim())) {
                        return false;
                    }
                }

                return true;
            },
            $.mage.__('Please enter valid email addresses, separated by commas. For example, johndoe@domain.com, johnsmith@domain.com.') //eslint-disable-line max-len
        ],

        'validate-cc-type-select': [

            /**
             * Validate credit card type matches credit card number
             * @param {*} value - select credit card type
             * @param {*} element - element contains the select box for credit card types
             * @param {*} params - selector for credit card number
             * @return {Boolean}
             */
            function (value, element, params) {
                if (value && params && creditCartTypes[value]) {
                    return creditCartTypes[value][0].test($(params).val().replace(/\s+/g, ''));
                }

                return false;
            },
            $.mage.__('Card type does not match credit card number.')
        ],
        'validate-cc-number': [

            /**
             * Validate credit card number based on mod 10.
             *
             * @param {*} value - credit card number
             * @return {Boolean}
             */
            function (value) {
                if (value) {
                    return validateCreditCard(value);
                }

                return false;
            },
            $.mage.__('Please enter a valid credit card number.')
        ],
        'validate-cc-type': [

            /**
             * Validate credit card number is for the correct credit card type.
             *
             * @param {String} value - credit card number
             * @param {*} element - element contains credit card number
             * @param {*} params - selector for credit card type
             * @return {Boolean}
             */
            function (value, element, params) {
                var ccType;

                if (value && params) {
                    ccType = $(params).val();
                    value = value.replace(/\s/g, '').replace(/\-/g, '');

                    if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                        return creditCartTypes[ccType][0].test(value);
                    } else if (creditCartTypes[ccType] && !creditCartTypes[ccType][0]) {
                        return true;
                    }
                }

                return false;
            },
            $.mage.__('Credit card number does not match credit card type.')
        ],
        'validate-cc-exp': [

            /**
             * Validate credit card expiration date, make sure it's within the year and not before current month.
             *
             * @param {*} value - month
             * @param {*} element - element contains month
             * @param {*} params - year selector
             * @return {Boolean}
             */
            function (value, element, params) {
                var isValid = false,
                    month, year, currentTime, currentMonth, currentYear;

                if (value && params) {
                    month = value;
                    year = $(params).val();
                    currentTime = new Date();
                    currentMonth = currentTime.getMonth() + 1;
                    currentYear = currentTime.getFullYear();

                    isValid = !year || year > currentYear || year == currentYear && month >= currentMonth; //eslint-disable-line
                }

                return isValid;
            },
            $.mage.__('Incorrect credit card expiration date.')
        ],
        'validate-cc-cvn': [

            /**
             * Validate credit card cvn based on credit card type.
             *
             * @param {*} value - credit card cvn
             * @param {*} element - element contains credit card cvn
             * @param {*} params - credit card type selector
             * @return {*}
             */
            function (value, element, params) {
                var ccType;

                if (value && params) {
                    ccType = $(params).val();

                    if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                        return creditCartTypes[ccType][1].test(value);
                    }
                }

                return false;
            },
            $.mage.__('Please enter a valid credit card verification number.')
        ],
        'validate-cc-ukss': [

            /**
             * Validate Switch/Solo/Maestro issue number and start date is filled.
             *
             * @param {*} value - input field value
             * @return {*}
             */
            function (value) {
                return value;
            },
            $.mage.__('Please enter issue number or start date for switch/solo card type.')
        ],
        'validate-WweLt-decimal-limit-2': [
            function (v, elm) {
            console.log(elm);
                var reMax = new RegExp(/^maximum-length-[0-9]+$/),
                    reMin = new RegExp(/^minimum-length-[0-9]+$/),
                    validator = this,
                    result = true,
                    length = 0;

                $.each(elm.className.split(' '), function (index, name) {
                    if (name.match(reMax) && result) {
                        length = name.split('-')[2];
                        result = v.length <= length;
                        validator.validateMessage =
                            $.mage.__('Please enter less or equal than %1 symbols.').replace('%1', length);
                    }

                    if (name.match(reMin) && result && !$.mage.isEmpty(v)) {
                        length = name.split('-')[2];
                        result = v.length >= length;
                        validator.validateMessage =
                            $.mage.__('Please enter more or equal than %1 symbols.').replace('%1', length);
                    }
                });

                return result;
            }, function () {
                return this.validateMessage;
            }
        ],
        'required-entry': [
            function (value) {
                return !$.mage.isEmpty(value);
            }, $.mage.__('This is a required field.')
        ],
        'not-negative-amount': [
            function (v) {
                if (v.length) {
                    return (/^\s*\d+([,.]\d+)*\s*%?\s*$/).test(v);
                }

                return true;
            },
            $.mage.__('Please enter positive number in this field.')
        ],
        'validate-per-page-value-list': [
            function (v) {
                var isValid = true,
                    values = v.split(','),
                    i;

                if ($.mage.isEmpty(v)) {
                    return isValid;
                }

                for (i = 0; i < values.length; i++) {
                    if (!/^[0-9]+$/.test(values[i])) {
                        isValid = false;
                    }
                }

                return isValid;
            },
            $.mage.__('Please enter a valid value, ex: 10,20,30')
        ],
        'validate-per-page-value': [
            function (v, elm) {
                var values;

                if ($.mage.isEmpty(v)) {
                    return false;
                }
                values = $('#' + elm.id + '_values').val().split(',');

                return values.indexOf(v) !== -1;
            },
            $.mage.__('Please enter a valid value from list')
        ],
        'validate-new-password': [
            function (v) {
                if ($.validator.methods['validate-password'] && !$.validator.methods['validate-password'](v)) {
                    return false;
                }

                if ($.mage.isEmpty(v) && v !== '') {
                    return false;
                }

                return true;
            },
            $.mage.__('Please enter 6 or more characters. Leading and trailing spaces will be ignored.')
        ],
        'required-if-not-specified': [
            function (value, element, params) {
                var valid = false,
                    alternate = $(params),
                    alternateValue;

                if (alternate.length > 0) {
                    valid = this.check(alternate);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        alternateValue = alternate.val();

                        if (typeof alternateValue == 'undefined' || alternateValue.length === 0) { //eslint-disable-line
                            valid = false;
                        }
                    }
                }

                if (!valid) {
                    valid = !this.optional(element);
                }

                return valid;
            },
            $.mage.__('This is a required field.')
        ],
        'required-if-all-sku-empty-and-file-not-loaded': [
            function (value, element, params) {
                var valid = false,
                    alternate = $(params.specifiedId),
                    alternateValue;

                if (alternate.length > 0) {
                    valid = this.check(alternate);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        alternateValue = alternate.val();

                        if (typeof alternateValue == 'undefined' || alternateValue.length === 0) { //eslint-disable-line
                            valid = false;
                        }
                    }
                }

                if (!valid) {
                    valid = !this.optional(element);
                }

                $('input[' + params.dataSku + '=true]').each(function () {
                    if ($(this).val() !== '') {
                        valid = true;
                    }
                });

                return valid;
            },
            $.mage.__('Please enter valid SKU key.')
        ],
        'required-if-specified': [
            function (value, element, params) {
                var valid = true,
                    dependent = $(params),
                    dependentValue;

                if (dependent.length > 0) {
                    valid = this.check(dependent);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        dependentValue = dependent.val();
                        valid = typeof dependentValue != 'undefined' && dependentValue.length > 0;
                    }
                }

                if (valid) {
                    valid = !this.optional(element);
                } else {
                    valid = true; // dependent was not valid, so don't even check
                }

                return valid;
            },
            $.mage.__('This is a required field.')
        ],
        'required-number-if-specified': [
            function (value, element, params) {
                var valid = true,
                    dependent = $(params),
                    depeValue;

                if (dependent.length) {
                    valid = this.check(dependent);

                    if (valid) {
                        depeValue = dependent[0].value;
                        valid = !!(depeValue && depeValue.length);
                    }
                }

                return valid ? !!value.length : true;
            },
            $.mage.__('Please enter a valid number.')
        ],
        'datetime-validation': [
            function (value, element) {
                var isValid = true;

                if ($(element).val().length === 0) {
                    isValid = false;
                    $(element).addClass('mage-error');
                }

                return isValid;
            },
            $.mage.__('This is required field')
        ],
        'validate-item-quantity': [
            function (value, element, params) {
                var validator = this,
                    result = false,
                    // obtain values for validation
                    qty = $.mage.parseNumber(value),
                    isMinAllowedValid = typeof params.minAllowed === 'undefined' ||
                        qty >= $.mage.parseNumber(params.minAllowed),
                    isMaxAllowedValid = typeof params.maxAllowed === 'undefined' ||
                        qty <= $.mage.parseNumber(params.maxAllowed),
                    isQtyIncrementsValid = typeof params.qtyIncrements === 'undefined' ||
                        qty % $.mage.parseNumber(params.qtyIncrements) === 0;

                result = qty > 0;

                if (result === false) {
                    validator.itemQtyErrorMessage = $.mage.__('Please enter a quantity greater than 0.');//eslint-disable-line max-len

                    return result;
                }

                result = isMinAllowedValid;

                if (result === false) {
                    validator.itemQtyErrorMessage = $.mage.__('The fewest you may purchase is %1.').replace('%1', params.minAllowed);//eslint-disable-line max-len

                    return result;
                }

                result = isMaxAllowedValid;

                if (result === false) {
                    validator.itemQtyErrorMessage = $.mage.__('The maximum you may purchase is %1.').replace('%1', params.maxAllowed);//eslint-disable-line max-len

                    return result;
                }

                result = isQtyIncrementsValid;

                if (result === false) {
                    validator.itemQtyErrorMessage = $.mage.__('You can buy this product only in quantities of %1 at a time.').replace('%1', params.qtyIncrements);//eslint-disable-line max-len

                    return result;
                }

                return result;
            }, function () {
                return this.itemQtyErrorMessage;
            }
        ],
        'password-not-equal-to-user-name': [
            function (value, element, params) {
                if (typeof params === 'string') {
                    return value.toLowerCase() !== params.toLowerCase();
                }

                return true;
            },
            $.mage.__('The password can\'t be the same as the email address. Create a new password and try again.')
        ]
    };

    $.each(rules, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });

});

/*
* This is method is used to validate decimal values.
* */
function validateDecimal($, value, limit) {
    let pattern = '';
    switch (limit) {
        case 3:
            pattern = /^\d*(\.\d{0,3})?$/;
            break;
        case 4:
            pattern = /^\d*(\.\d{0,4})?$/;
            break;
        case 5:
            pattern = /^\d*(\.\d{0,5})?$/;
            break;
        default:
            pattern = /^\d*(\.\d{0,2})?$/;
    }
    const regex = new RegExp(pattern, 'g');
    return regex.test(value);
}
