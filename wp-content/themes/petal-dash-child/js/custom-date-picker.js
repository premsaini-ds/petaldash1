	jQuery(document).ready(function($) {
    jQuery('#product_delivery_date').multiDatesPicker({
        minDate: 0, // Disable previous dates
        dateFormat: 'yy-mm-dd' // Format the date as YYYY-MM-DD
    });
});