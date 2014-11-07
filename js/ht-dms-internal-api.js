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
        $( document ).ajaxComplete(function( event,request, settings ) {
            app.events.ajaxComplete();
        });

    };

    /**
     * Event Handlers
     *
     * @since 0.1.0
     *
     * @type {{ajaxComplete: Function, click: Function}}
     */
    app.events = {
        ajaxComplete: function() {
          $( "[notification]" ).click( function() {
              nID = $(this).attr( 'notification' );
              app.notificationView( nID );
          });
        },
        click : function() {
            //@todo
        }


    };


    /**
     * Notification UI
     *
     * @since 0.1.0
     *
     * @type {{request: Function, cb: Function}}
     */
    app.notificationView = {
        request : function( nID ) {
            params.nID = nID;
            params.nID = 'load_notification';
            app.request.make( params );
            app.httpRequest.onreadystatechange = this.cb;
        },
        cb: function() {
            if ( app.request.ready() ) {
                response = app.httpRequest.responseText;
                var container = '#notification-viewer';
                var previews = $( container ).html();
                $( container ).html('');
                $( container ).hide().append( response ).fadeIn( 400 );
                $( '#notification-single-close').show();

                $( "#notification-single-close").click( function () {
                    $( '#notification-viewer').fadeOut( 400 ).html( '' );
                    $( container ).hide().append( previews ).fadeIn( 400 );
                });
            }
        }
    };

    /**
     * Reload consensus views
     *
     * Called from the window scoped reloadConsensus() function
     *
     * @type {{container: string, request: Function, cb: Function}}
     */
    app.reloadConsensus = {
        container: '#consensus-view',
        request:  function(){
            params.action = 'reload_consensus';
            params.dID = htDMS.id;

            app.request.make( params );
            app.httpRequest.onreadystatechange = this.cb;
        },
        cb: function() {
            if ( app.request.ready() ) {
                response = app.httpRequest.responseText;
                $( this.container ).html( '' );
                app.consensusView( response );
                app.updateDecisionStatus.request();

            }
        }

    };

    /**
     * Render consensus view
     *
     * @since 0.1.0
     *
     * @param user
     */
    app.consensusView = function( users ) {
        if ( undefined == users ) {
            users =  JSON.parse( htDMS.consensusMemberDetails );
        }

        var data = {
            header0: htDMS.consensusHeaders.header0,
            header1: htDMS.consensusHeaders.header1,
            header2: htDMS.consensusHeaders.header2,
            users0: users[0],
            users1: users[1],
            users2: users[2]
        };


        var source   = $( '#consensus-view-template' ).html();
        if ( typeof source === 'string' ) {
            var template = Handlebars.compile(source);
            var html = template(data);
            $('#consensus-view').append(html);
        }

        $( '#consensus-views-chooser li a' ).click( function () {
            var cst = $( this).first().attr( 'cst' );
           app.consensusViewUpdate( cst );
        });
    };

    app.consensusViewUpdate = function( id ) {
        $( '#consensus-views-by-status' ).children().fadeOut();
        var container = '#' + id;
        $( container ).fadeIn();

    };

    app.contentView = {

    };


    app.updateDecisionStatus = {
        container: '#decision-status',
        request: function() {
            params.action = 'update_decision_status';
            params.dID = htDMS.id;
            app.request.make( params );
            app.httpRequest.onreadystatechange = this.cb;
        },
        cb: function() {
            if ( app.request.ready() ) {

                response = app.httpRequest.responseText;
                $( this.container ).fadeOut( 400 );
                $( this.container ).html('');
                $( this.container ).append( response ).fadeIn( 400 );
            }

        }

    };

    app.reloadMembership = {
        container: "#group-membership",
        gID : htDMS.id,
        request: function() {
            params.action = 'reload_membership';
            params.gID = htDMS.id;
            app.request.make( params );
            app.httpRequest.onreadystatechange = this.cb;
        },
        cb: function() {
            if ( app.request.ready() ) {
                response = app.httpRequest.responseText;
                $( this.container ).fadeOut( 400 );
                $( this.container ).html( '' );
                $( this.container ).append( response ).fadeIn( 400 );
            }
        }

    };

    /**
     * Mark a notification as read
     *
     * @since 0.0.1
     *
     * @type {{request: Function, cb: Function}}
     */
    app.markNotification = {
        request: function( nID, viewed  ) {
            var mark = 1;
            if ( viewed == 'Yes' ) {
                mark = 0;
            }
            app.params.nID = nID;
            app.params.viewed = viewed;
            app.params.action = 'mark_notification';
            app.request.make( params );
            app.httpRequest.onreadystatechange = this.cb;

        },
        cb: function() {
            if ( app.request.ready() ) {
                app.paginate.request( "#users_notifications", 1 );
            }
        }
    };


    /**
     * Paginated view loader
     *
     * @type {{container: boolean, request: Function, cb: Function}}
     */
    app.paginate = {
        container:false,
        request: function( container, page, extraArg ) {
            app.paginate.container = container;

            var oID = 0;
            if ( undefined !== $( container ).attr( "oid" ) ) {
                oID = $( container ).attr( "oid" );
            }

            if ( undefined == extraArg ) {
                extraArg = 0;
            }
            if ( undefined == page ) {
                page = 0;
            }


            params = {};
            params.view = view = container.replace( '#', '' );
            params.page = page;
            params.extraArg = extraArg;
            params.oID = oID;
            params.view = $( container ).attr( "view" );
            params.limit = $( container ).attr( "limit" );
            params.action = 'paginate';
            url = app.url( params );


            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function ( response ) {

                    var htmlID = app.idCheck( response.html_id );
                    var templateID = app.idCheck( response.template_id );
                    var html = response.html;
                    var outer_html_id = response.outer_html_id;
                    var spinner = outer_html_id + "-spinner";

                    $( outer_html_id ).fadeOut( 800 ).hide();
                    $( outer_html_id ).append( html ).append( response.template );
                    $( spinner ).show().delay( 400 );


                    var rendered = '';

                    $.each( JSON.parse( response.json ), function ( i, val ) {

                        data = JSON.parse( val );
                        var source = $( templateID ).html();
                        template = Handlebars.compile( source );
                        rendered += template( data );
                        delete template;
                        delete source;
                    } );


                    $( htmlID ).html( rendered );
                    $( spinner ).hide();
                    $( outer_html_id ).attr( 'page', page );
                    $( outer_html_id ).fadeIn( 800 );

                }
            });
        }


    };

    /**
     * Get members of a group or organization
     *
     * @since 0.1.0
     *
     * @type {{request: Function, cb: Function}}
     */
    app.getMembers = {
        request: function( id, type, container ) {
            this.container = container;
            app.params.id = id;
            app.params.tyoe = viewed;
            app.params.action = 'mark_notification';
            app.request.make( params );
            app.httpRequest.onreadystatechange = this.cb( container );
        },
        cb: function( container ) {
            if ( app.request.ready() ) {
                response = app.httpRequest.responseText;
                document.getElementById(  container ).innerHTML = '<span class="members-label">Members:</span>' + response;
            }
        }

    };

    /**
     * Check if an ID has the # in it, if not, add it.
     *
     * @param id
     * @returns {*}
     */
    app.idCheck = function( id ) {
        if ( id.indexOf( '#' ) < 0 ) {
            id = '#' + id;
        }

        return id;
    };
    /**
     * Construct a nonced URL for the request
     *
     * @since 0.1.0
     *
     * @param params
     *
     * @returns {string|*} The URL
     */
    app.url = function( params ) {
        rootURL = htDMSinternalAPIvars.url;


        nonce = htDMS.nonce;
        params[ 'nonce' ] = nonce;

        params = $.param( params );

        url = rootURL +  '?' + params;

        return url;
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
            return app.url( params );
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


