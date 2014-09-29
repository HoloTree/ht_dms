jQuery(document).ready(function($) {
    ajaxURL = htDMS.ajaxURL;


    /**
     * Allows for getting views of the ht_dms\ui\build\views class via AJAX
     *
     * @param view Which view to get. Options: users_groups|public_groups|assigned_tasks|users_organizations|decision_tasks|organization|group|decision|task
     * @param args Array of arguments, varies by view.
     * @param returnType  Optional. What to return. Options: template|JSON|urlstring
     * @param put The ID of the container to put the view in.
     */
    function viewGet( view, args, returnType, put ) {
        $.get(
            ajaxURL, {
                'action': 'ht_dms_ui_ajax_view',
                'nonce' : htDMS.nonce,
                'view' : view,
                'args' : args,
                'returnType' : returnType
            },
            function( response ) {
                var string = response.toString();

            }
        );
    }




    //defaults for our view getters
    var limit = 5;
    var returnType = 'template';
    var uID = null;
    var oID = null;


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
                'action': 'ht_dms_paginate',
                'nonce' : htDMS.nonce,
                'view' : view,
                'page' : page,
                'limit' : limit,
                'container' :container,
                'extraArg' : extraArg

            },
            function( response ) {
                $( container ).fadeOut( 800 ).hide();
                $( container + "-spinner" ).show().delay( 400 );
                $( container ).html('');
                $( container ).hide().append( response ).fadeIn( 800 );
                $( container + "-spinner") .hide();
                $( container ).attr('page', page );

                $( container ).find( '.dms-members-load' ).each( function() {

                    var type = $( this ).attr( 'dms_type' );
                    var id = $( this ).attr( 'dms_id' );
                    var containerID = $( this ).attr( 'id' );

                    getMembers( id, type, containerID );

                });


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
                    'action': 'ht_dms_load_notification',
                    'nonce' : htDMS.nonce,
                    'nID' : nID
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

        $.get(
            ajaxURL, {
                'action': 'ht_dms_reload_consensus',
                'nonce' : htDMS.nonce,
                'dID' : dID,
                'container' :container
            },
            function( response ) {
                $( container ).fadeOut( 800 ).hide();
                $( container ).empty();
                update_decision_status( dID );

                $( container).html( response ).fadeIn( 800 );



            }
        );

    }

    window.reloadConsensus = reloadConsensus;

    function update_decision_status( dID ) {
        var container = '#decision-status';
        $.get(
            ajaxURL, {
                'action': 'ht_dms_update_decision_status',
                'nonce' : htDMS.nonce,
                'dID'   : dID

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
                'action': 'ht_dms_reload_membership',
                'nonce' : htDMS.nonce,
                'gID'   : gID,
                'container' :container
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
                'action': 'ht_dms_mark_notification',
                'nonce' : htDMS.nonce,
                'nID'   : nID,
                'mark'  : mark

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
                'action': 'ht_dms_members',
                'nonce' : htDMS.nonce,
                'id'    : id,
                'type'  : type

            },
            function( response ) {
                document.getElementById( container ).innerHTML = '<span class="members-label">Members:</span>' + response;

            }
        );
        
    }


});
