(function( $ ) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     */

    $(document).ready(function() {
        // Handle dismissible notices
        $(document).on('click', '.ssl-cert-notice .notice-dismiss', function() {
            var $notice = $(this).closest('.ssl-cert-notice');
            var domain = $notice.data('domain');
            
            // Send AJAX request to dismiss the notice
            $.ajax({
                url: ssl_cert_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ssl_cert_dismiss_notice',
                    domain: domain,
                    nonce: ssl_cert_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $notice.fadeOut();
                    }
                }
            });
        });
    });

})( jQuery ); 