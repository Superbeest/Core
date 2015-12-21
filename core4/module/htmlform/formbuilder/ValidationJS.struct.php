<?php
/**
* ValidationJS.struct.php
*
* Copyright c 2015, SUPERHOLDER. All rights reserved.
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or at your option any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301  USA
*/


namespace Module\HTMLForm\FormBuilder;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* Structure that contains validation functions. These constants do not need to be addresses.
* @package \Module\HTMLForm\FormBuilder
*/
class ValidationJS extends \System\Base\BaseStruct
{
    const JS_EMAIL_CALL = 'emailcheck_{ID}';
    const JS_EMAIL = '
        function emailcheck_{ID}(elem) {
            var pattern = new RegExp(/^([+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})?$/i);
            return (pattern.test($(elem).val()));
        };';

    const JS_NOTEMPTY_CALL = 'notempty_{ID}';
    const JS_NOTEMPTY = '
        function notempty_{ID}(elem) {
            return ($(elem).val().length > 0);
        };';

    const JS_PHONE_CALL = 'phone_{ID}';
    const JS_PHONE = '
        function phone_{ID}(elem) {
            var pattern = new RegExp(/^([\d\-\ \+\(\)]{10,20})?$/);
            return (pattern.test($(elem).val()));
        };';

    const JS_ZIP_NL_CALL = 'zip_nl_{ID}';
    const JS_ZIP_NL = '
        function zip_nl_{ID}(elem) {
            var pattern = new RegExp(/^([\d]{4} ?[a-z]{2})?$/i);
            return (pattern.test($(elem).val()));
        };';

    const JS_NUMBERS_CALL = 'numbers_{ID}';
    const JS_NUMBERS = '
        function numbers_{ID}(elem) {
            var pattern = new RegExp(/^([\d]*)$/i);
            return (pattern.test($(elem).val()));
        };';

    const JS_URL_CALL = 'url_{ID}';
    const JS_URL = '
        function url_{ID}(elem) {
        	var val = $(elem).val();
        	var patternPrefix = new RegExp(/^http(s)?:\/\//i);
        	if ((val.length > 0) &&
        		(!patternPrefix.test(val))) {
        		$(elem).val(\'http://\' + val);
        	}
            var pattern = new RegExp(/[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi);
            return (pattern.test($(elem).val()));
        };';
}
