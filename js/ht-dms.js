jQuery(document).ready(function($) {
    ajaxURL = htDMS.ajaxURL;



    /**
     * Pagination view loader
     *
     * @param string Container container ID, with #
     * @param int page Page of results to load.
     */
    function paginate( container, page, extraArg ) {
        //var page = $( container ).attr( "page" );
        var limit = $( container ).attr( "limit" );
        var view = $( container ).attr( "view" );



        $.get(
            ajaxURL, {
                action: 'ht_dms_paginate',
                nonce : htDMS.nonce,
                view : view,
                page : page,
                limit : limit,
                container :container,
                extraArg : extraArg,
                localCache : true,
                cacheTTL : 1
            },
            function( response ) {
                $( container ).fadeOut( 800 ).hide();
                $( container + "-spinner" ).show().delay( 400 );
                $( container ).html('').hide().append( response ).fadeIn( 800 );
                $( container + "-spinner") .hide();
                $( container ).attr('page', page );


            }
        );
    }

    window.paginate = paginate;


    /**
     * Notification UI
     *
     * @since 0.0.3
     */
    $( document ).ajaxComplete(function( event,request, settings ) {

        $("[notification]").click( function () {
            var nID = $(this).attr( 'notification' );

            $.get(
                ajaxURL, {
                    action: 'ht_dms_load_notification',
                    nonce : htDMS.nonce,
                    nID : nID,
                    localCache : true,
                    cacheTTL : 1
                },
                function( response ) {
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
            );

        });



    });



    /**
     * Consensus reload via ajax
     *
     * @param string container ID of container showing consensus view
     * @param int dID ID of decision.
     *
     * @since 0.0.3
     */
    function reloadConsensus(  ) {

        var container = '#consensus-view';
        var dID =   htDMS.id;

        $.post(
            ajaxURL, {
                action: 'ht_dms_reload_consensus',
                nonce : htDMS.nonce,
                dID : dID
            },
            function( response ) {
                $( container ).html( '' );
                consensusView( response );
                update_decision_status( dID );





            }
        );

    }

    window.reloadConsensus = reloadConsensus;

    function update_decision_status( dID ) {
        var container = '#decision-status';
        $.get(
            ajaxURL, {
                action: 'ht_dms_update_decision_status',
                nonce : htDMS.nonce,
                dID   : dID,
                localCache : true,
                cacheTTL : 1

            },function( response ) {
                $( container ).fadeOut( 400 );
                $( container ).html('');
                $( container ).append( response ).fadeIn( 400 );


            }
        );

    }

    function reloadMembership( ) {
        var container = "#group-membership";

        var gID = htDMS.id;
        $.get(
            ajaxURL, {
                action: 'ht_dms_reload_membership',
                nonce : htDMS.nonce,
                gID   : gID,
                container :container,
                localCache : true,
                cacheTTL : 1
            },
            function( response ) {
                $( container ).fadeOut( 400 );
                $( container ).html('');
                $( container ).append( response ).fadeIn( 400 );
            }
        );

    }

    window.reloadMembership = reloadMembership;

    function markNotification( nID, viewed ) {
        var mark = 1;
        if ( viewed == 'Yes' ) {
            mark = 0;
        }

        $.get(
            ajaxURL, {
                action: 'ht_dms_mark_notification',
                nonce : htDMS.nonce,
                nID   : nID,
                mark  : mark,
                localCache : true,
                cacheTTL : 1

            },
            function( response ) {
                paginate( "#users_notifications", 1 );
            }
        );
    }

    window.markNotification = markNotification;


    function getMembers( id, type, container ) {
        console.log( container );
        $.get(
            ajaxURL, {
                action: 'ht_dms_members',
                nonce : htDMS.nonce,
                id    : id,
                type  : type,
                localCache : true,
                cacheTTL : 1

            },
            function( response ) {
                document.getElementById( container ).innerHTML = '<span class="members-label">Members:</span>' + response;

            }
        );
        
    }

    function loadUsers ( users, container, templateID  ) {

        $.each(users, function( i, val ) {
            var user = new wp.api.models.User( { ID: val } );
            user.fetch().done(function () {
                loadUser( user, container, templateID );
            });

        });
    }

    window.loadUsers = loadUsers;

    function loadUser( user, container, templateID  ) {

        var name = user.attributes.name;
        var avatar = user.attributes.avatar;
        var ID = user.attributes.ID;

        var source   = $( templateID ).html();

        var data = {
            name: name,
            avatar: avatar,
            ID: ID
        };

        if ( container == 'return' ) {
            return data;
        }

        var template    = Handlebars.compile( source );
        var html        = template( data );


        $( container ).append( html );

    }

    window.loadUser = loadUser;

    function groupPreview( json, templateID, htmlID ) {
        htmlID = idCheck( htmlID );
        templateID = idCheck( templateID );

        $.each( json, function( i, val ) {
            var data = JSON.parse( val );

            var source   = $( templateID ).html();
            if ( typeof source === 'string' ) {
                var template    = Handlebars.compile( source );
                var html        = template( data );
                $( htmlID ).append(html);
            }
            else{
                console.log( 'groupPreview i=' + i );
                console.log( templateID + '=' + source );
            }

        });

    }

    window.groupPreview = groupPreview;

    function organizationPreview( json, templateID, htmlID ) {
        htmlID = idCheck( htmlID );
        templateID = idCheck( templateID );

        $.each( json, function( i, val ) {
            var data = JSON.parse( val );

            var source   = $( templateID ).html();
            if ( typeof source === 'string' ) {
                var template = Handlebars.compile(source);
                var html = template(data);
                $ (htmlID ).append(html);
            }
            else{
                console.log( 'organizationPreview i=' + i );
            }



        });
    }

    window.organizationPreview = organizationPreview;


    /**
     * Consensus Visualization
     *
     * @since 0.0.3
     */
    $( document).ready( function()  {
        if (typeof htDMS.consensusMembers != 0) {
            consensusView();
        }
    });

    function consensusView( users ) {
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
            consensusViewUpdate( cst );
        });

    }

    function consensusViewUpdate( id ) {
        $( '#consensus-views-by-status').children().fadeOut();
        var container = '#' + id;
        $( container ).fadeIn();

    }

    /**
     * Check if an ID has the # in it, if not, add it.
     *
     * @param id
     * @returns {*}
     */
    function idCheck( id ) {
        if ( id.indexOf( '#' ) < 0 ) {
            id = '#' + id;
        }

        return id;

    }

    window.idCheck = idCheck;

    /**
     * Open the discussion Modal
     *
     * @since 0.0.3
     */
    function openCommentModal() {
        $( '#discussion-modal' ).foundation('reveal', 'open');
    }

    /**
     * If respond is chosen for the decision action form, open the modal
     *
     * @since 0.0.3
     */
    $( ".CF5411fb087123d" ).submit(function( event ) {
        if ( $( '#fld_738259_1').val() == 'respond' ) {
            event.preventDefault();
            openCommentModal();
        }

    });

    $( ".CF5411fb087123d" ).submit(function( event ) {
        if ( $( '#fld_738259_1').val() == 'propose-modify' ) {
            event.preventDefault();
            document.location = htDMS.proposeModifyURL;
        }

    });



});
