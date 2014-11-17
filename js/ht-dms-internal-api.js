/**globals jQuery, htDMS, htDMSinternalAPIvars**/
jQuery( function ( ) {
    htDMSinternalAPI.init( htDMS );
} );

(function ( $, app ) {
    /**
     * URL for internal API
     */
    app.APIurl = htDMSinternalAPIvars.url;

    /**
     * Messages object
     *
     * @since 0.1.0
     */
    app.messages = htDMSinternalAPIvars.messages;

    /**
     * Bootstrap internal API client-side interactions
     *
     * @since 0.1.0
     */
    app.init = function( dms ) {

        app.htDMS = dms;
        app.discussion();
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
          $( "[notification]" ).click( function( event ) {
              event.preventDefault();

              nID = $(this).attr( 'notification' );

              app.notificationView.request( nID );
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
            params = {};
            params.nID = nID;
            params.action = 'load_notification';
            var url = app.constructURL( params );
            $.ajax( {
                method: 'GET',
                url: url,
                success: function( response ) {

                    app.notificationView.cb( response );
                }
                   
            });
        },
        cb: function( response ) {

            data = response.json;
            if ( 'object' != typeof data ) {
                data = JSON.parse( data );
            }
alert( response.outer_html_id );
            var container = response.outer_html_id;
            var source = $( '#notification-single' ).html();
            var template = Handlebars.compile( source );
            rendered = template( data );

            $( container ).html('');
            $( container ).hide().append( rendered ).fadeIn( 400 );
            $( '#notification-single-close').show();

            $( "#notification-single-close").click( function () {
                $( container ).fadeOut( 400 ).html( '' ).show();
                app.paginate.request( '#users_notifications', 1 );
            });

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
        request:  function(){
            params = {};
            params.action = 'reload_consensus';
            params.dID = app.htDMS.id;
            var url = app.constructURL( params );
            $.ajax( {
                method: 'GET',
                url: url,
                success: function( response ) {
                    app.reloadConsensus.cb( response );
                }
            });
        },
        cb: function( response ) {
            $( '#consensus-view' ).html( '' );
            app.consensusView( response );
            app.updateDecisionStatus.request();
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
            users =  app.htDMS.consensusMemberDetails;
        }

        if ( 'object' !== typeof users ) {
            users = JSON.parse( users );
        }

        var data = {
            header0: app.htDMS.consensusHeaders.header0,
            header1: app.htDMS.consensusHeaders.header1,
            header2: app.htDMS.consensusHeaders.header2,
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

        //@todo move this to the click handler object
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
            params = {};
            params.action = 'update_decision_status';
            params.dID = app.htDMS.id;
            var url = app.constructURL( params );
            $.ajax( {
                method: 'GET',
                url: url,
                success: function( response ) {
                    app.updateDecisionStatus.cb( response );
                }
            });
        },
        cb: function( response ) {
            $( this.container ).fadeOut( 400 );
            $( this.container ).html('');
            $( this.container ).append( response ).fadeIn( 400 );
        }

    };

    app.reloadMembership = {
        container: "#group-membership",
        request: function() {
            params = {};
            params.action = 'reload_membership';
            params.gID = app.htDMS.id;
            var url = app.constructURL( params );
            $.ajax( {
                method: 'GET',
                url: url,
                success: function( response ) {
                    app.reloadMembership.cb( response );
                }
            });
        },
        cb: function( response ) {
            $( this.container ).fadeOut( 400 );
            $( this.container ).html( '' );
            $( this.container ).append( response ).fadeIn( 400 );
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

            params = {};
            params.nID = nID;
            params.viewed = viewed;
            params.action = 'mark_notification';
            this.mark( nID );

            var url = app.constructURL( params );

            $.ajax( {
                method: 'GET',
                url: url,
                success: function( response ) {
                    app.markNotification.cb( response );
                }
            });

        },
        cb: function( response ) {

            app.paginate.request( "#users_notifications", 1 );

        },
        mark: function mark( nID ) {
            select = "[nID="+nID+"]";
            var els = $( select );

        }
    };

    app.discussion = function() {
        id = app.htDMS.id;
        container = '#discussion';
        if ( 'group' === htDMSinternalAPIvars.type || 'decision' === htDMSinternalAPIvars.type ) {

            if ( undefined == id ) {
                id = $( container ).attr( 'data-id' );
            }

            params = {};
            params.action = 'comments';
            params.id = id;
            var url = app.constructURL( params );

            $.ajax( {
                method: 'GET',
                url: url,
                success: function ( response, code  ) {

                    if ( 'success' !== code || undefined == response.json || '' == response.json ) {
                        return;
                    }

                    templateID = '#comments-view-template';

                    $( container ).append( response.template );
                    var source = $( templateID ).html();
                    var comments = JSON.parse( response.json );
                    template = Handlebars.compile( source );
                    rendered = '';

                    $.each(comments , function ( i, val ) {

                        rendered += template( val );

                    } );

                    $( container ).append( rendered );
                }
            } );
        }
    };


    /**
     * Paginated view loader
     *
     * @type {{container: boolean, request: Function, cb: Function}}
     */
    app.paginate = {
        container:false,
        request: function( container, page, extraArg, unViewedOnly ) {
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
            params.limit = view = $( container ).attr( "limit" );
            params.action = 'paginate';
            if ( undefined != unViewedOnly && true == unViewedOnly ) {
                params.unviewedonly = unViewedOnly
            }

            var url = app.constructURL( params );

            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                view: params.view,
                nonce: htDMSinternalAPI.nonce,
                success: function ( response ) {
                    var outer_html_id = response.outer_html_id;

                    var spinner = outer_html_id + "-spinner";

                    var obj = JSON.parse( response.json );

                    if ( undefined === obj || 0 == obj ) {
                        $( spinner ).fadeOut();
                        $( outer_html_id ).empty().append( '<p>No items found.</p>' );
                        return;
                    }

                    var htmlID = app.idCheck( response.html_id );
                    var templateID = app.idCheck( response.template_id );
                    var html = response.html;

                    var paginationID = outer_html_id;
                    paginationID += '-pagination';
                    $( paginationID ).remove();

                    $( outer_html_id ).fadeOut( 800 ).hide();
                    $( outer_html_id ).append( html ).append( response.template );
                    $( spinner ).show();

                    var rendered = '';

                    $.each( obj , function ( i, val ) {

                        if ( 'object' != typeof val ) {
                            data = JSON.parse( val );
                        }
                        else {
                            data = val;
                        }

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
                    $( outer_html_id ).parent().show();


                    if ( document.contains( document.getElementById('users-notifications-container' )  ) ) {

                        $( '#users-notifications-container' ).show();
                    }


                },
                complete : function( xhr ) {

                    if ( 200 == xhr.status ) {

                        tabHeight();
                    }
                    else {
                        spinnerID = '#' + this.view + '-spinner';
                        containerID = '#' + this.view;

                        app.noItems( containerID, spinnerID );

                    }
                }

            });
        }


    };

    /**
     * Get members of a group or organization
     *
     *
     * @since 0.1.0
     *
     * @type {{request: Function, cb: Function}}
     */
    app.getMembers = {
        request: function( id, type, container ) {
            this.container = container;
            params = {};
            params.id = id;
            params.type = viewed;
            params.action = 'mark_notification';
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
    app.constructURL = function( params ) {

        nonce = htDMSinternalAPIvars.nonce;
        params[ 'nonce' ] = nonce;

        params = $.param( params );

        url = app.APIurl  +  '?' + params;

        return url;
    };

    /**
     * Handle no items returned (or 404) by hiding spinner and outputing a message
     *
     * @param containerID The ID of the container to add the message to.
     * @param spinnerID Optional. The ID of the spinner to hide.
     */
    app.noItems = function( containerID, spinnerID ) {
        if ( undefined != spinnerID ) {
            $( spinnerID ).hide();
        }

        $( containerID ).html( '<p>' + app.messages.noItems + '</p>' );
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
            return app.constructURL( params );
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
