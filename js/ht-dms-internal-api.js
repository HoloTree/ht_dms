/**globals jQuery, htDMS, htDMSinternalAPIvars**/
jQuery( function () {
    htDMSinternalAPI.init( );
} );

(function ( $, app ) {

    /**
     * Bootstrap internal API client-side interactions
     *
     * @since 0.1.0
     */
    app.init = function() {
        app.test();

    };

    app.test = function() {
        params = {
            nog: 'f',
            action: 'foo'
        };
        app.request.make( params);
        app.httpRequest.onreadystatechange = app.cb;
    };

    app.cb = function() {
        if ( app.request.ready() ) {

            console.log( app.httpRequest.responseText );
        }
    };

    /**
     * Initialize XMLHttpRequest object
     *
     * @since 0.1.0
     */
    app.httpRequest = new XMLHttpRequest();

    /**
     * Handle Requests
     *
     * @since 0.1.0
     */
    app.request = {


        /**
         * Make a request to internal API
         *
         * @since 0.1.0
         *
         * @param params URL params for request
         */
        make: function( params ) {
            url = app.request.url( params );

            app.httpRequest.open( 'GET', url, true );
            app.httpRequest.send( null ) ;

        },
        /**
         * Construct a nonced URL for the request
         *
         * @since 0.1.0
         *
         * @param params
         *
         * @returns {string|*} The URL
         */
        url: function( params ) {
            rootURL = htDMSinternalAPIvars.url;
            rootURL = 'http://gus.dev/ht-dms-internal-api';

            nonce = htDMS.nonce;
            params[ 'nonce' ] = nonce;

            params = $.param( params );

            url = rootURL +  '?' + params;

            return url;
        },
        /**
         * Check if the response is ready & status code is 200.
         *
         * @since 0.1.0
         *
         * @returns {boolean}
         */
        ready: function() {
            if ( app.httpRequest.readyState === 4 ) {
                if ( app.httpRequest.status === 200 ) {
                    return true;

                }
                if ( app.httpRequest.status === 550 ) {
                    console.log( app.httpRequest.responseText );
                }

            }

        }


    };
})( jQuery, window.htDMSinternalAPI || ( window.htDMSinternalAPI = {} ) );


