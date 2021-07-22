window.LmsBlockMenu = (function ($) {

    'use strict';

    function clearTableCreditWrapper() {
        $('.js-credits-ajax-container').children('.table-credit-wrapper').remove();
    }

    function populateTableCreditWrapper(data) {
        $('.js-credits-ajax-container').append(data);
    }

    /**
     * function used to fetch the credit table
     * @param callback   {function}   -   callback used to build the table credit
     */
    var loadUserCreditData = function (callback, selectedPeriod) {

        var _data = {
            'credits_period': selectedPeriod
        };

        $.ajax({

            type: 'post',
            url: 'ajax.adm_server.php?r=lms/profile/credits',
            data: _data,
            success: function (data) {
                var parsedData = data;

                callback(parsedData);

                return parsedData;
            },
            error: function (e) {
                $('.loading').html('errore: ' + e.message);
                return false;
            }
        });
    };

    $(document).ready(function () {

        $('#credits_period').on('change', function () {

            loadUserCreditData(function (data) {
                clearTableCreditWrapper();
                populateTableCreditWrapper(data);

            }, this.value);
        });

    });

})(jQuery);