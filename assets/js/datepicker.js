(function ($) {
    acf.add_filter('date_picker_args', function (args, $field) {
        var fieldName = $field.data('name');

        if (fieldName === 'show_from') {
            args.minDate = 0;
            args.maxDate = '+10 years';
        }

        if (fieldName === 'show_to') {
            args.minDate = '+1d';
            args.maxDate = '+10 years';
        }

        return args;
    });

    acf.add_action('ready_field/type=date_picker', function ($field) {
        if ($field.data('name') === 'show_from') {
            $field.find('input[type="text"]').on('change', function () {
                var $toFields = acf.findFields({ name: 'show_to' });

                if ($toFields && $toFields.length > 0) {
                    var $fromInput = $(this);
                    var fromDate = $fromInput.datepicker('getDate');

                    if (fromDate) {
                        var minDate = new Date(fromDate);
                        minDate.setDate(minDate.getDate() + 1);

                        var $toInput = $toFields
                            .first()
                            .find('input[type="text"]');
                        $toInput.datepicker('option', 'minDate', minDate);

                        var toDate = $toInput.datepicker('getDate');
                        if (toDate && toDate <= fromDate) {
                            $toInput.val('').trigger('change');
                        }
                    }
                }
            });
        }
    });
})(jQuery);
