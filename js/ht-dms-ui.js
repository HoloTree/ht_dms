jQuery(document).ready(function( $ ) {
    ajaxURL = htDMS.ajaxURL;

    /**
     * Containers to use paginated views for
     *
     * Should be all views that are not single item views.
     *
     * @since 0.0.2
     */
    var paginatedViews = [ '#users_groups', '#public_groups', '#users_organizations', '#assigned_tasks', '#decisions_tasks', "#users_notifications" ];
    window.paginatedViews = paginatedViews;

    //loop through paginatedViews running each one, if we have that div already.
    $.each( paginatedViews, function( index, value ){
        if ( $( value ).length ) {
            var spinner = value + "-spinner.spinner";
            $( spinner ).show();
            paginate( value , 1 );
        };
    });

    /**
     * Put possible result of actions into variables
     */
    var select_field = '#fld_738259_1';

    function result( select_field, consensusPossibilities ) {
        if ( undefined != consensusPossibilities && undefined != consensusPossibilities.possible_results[0]) {
            var p0 = consensusPossibilities.possible_results[0];
            var p1 = consensusPossibilities.possible_results[1];
            var p2 = consensusPossibilities.possible_results[2];

            var selected_action = $( select_field ) .val();
            console.log( selected_action );
            var result = false;
            if ( selected_action === 'accept' ) {
                var result = 'Decision will be ' + p1 + '.';
            }

            if (selected_action === 'object') {
                var result = 'Decision will be ' + p2 + '.';
            }

            //@todo translation-friendliness!
            if (selected_action === 'propose-modify') {
                var result = 'You will be able to propose a new version of this decision to consider.';
            }

            if ( selected_action === 'respond') {
                var result = 'You will be able to respond to this decision';
            }

            if ( false != result ) {
                $( '#dms-action-result').hide();
                result = 'If you make this choice: ' + result;
                $( '#dms-action-result').empty();
                $( '#dms-action-result' ).append( result).show();
            }


            console.log(selected_action);
        }
    }

    result( select_field );
    $( select_field ).change( function() {
        result( select_field, consensusPossibilities );
    });

    $( document ).ajaxComplete(function( event, xhr, settings ) {
        $('.notification-mark' ).each(function(i, el) {


            nRead = $( el ).attr( 'viewed' );
            if ( nRead == 'Yes' ) {
                $( el ).html( 'Mark Not Viewed' );

            }

            if ( nRead == 'No' ) {
                $( el ).html( 'Mark Viewed' );
            }


        });

        var mark = '.notification-mark';
        $( mark ).click(function () {

            markNotification( $( this ).attr('nid' ), $( this ).attr( 'viewed' ) );
        });

        $( '#notification-single-close' ).click(function () {

            markNotification( $( this ).attr('nid' ) );

        });

        $( '#unviewed-only' ).click( function() {
            container = '#users_notifications';
            paginate( container, $( container ).attr( 'page' ), 1 );

        });

    });

    /**
     * Breadcrumbs JS
     */

    var breadNames = breadNamesJSON;
    console.log( breadNames );
    var oName = breadNames.organization;
    var gName = breadNames.group;
    var dName = breadNames.decision;

    $( document).ready( function()  {
        bakeTheBread();
    });

    function bakeTheBread() {

        //store the breadcrumb string names into vars
        var org = document.getElementById("breadNames");
        var group = document.getElementById("breadGroup");
        var decid = document.getElementById("breadDecid");


        //store the screen-size into var
        console.log(window.innerWidth);
        var screen = window.innerWidth;
        if (screen < 658) {
            if ( oName != '' ) {
                org.innerText = abbreviate(oName, 5);
            }
            if ( gName != '' ) {
                group.innerText = abbreviate(gName, 5);
            }
            if ( dName != '') {
                decid.innerText = abbreviate(dName, 5);
            }
        } else if (screen > 658) {
            if ( oName != '' ) {
                org.innerText = oName;
            }
            if ( gName != '' ) {
                group.innerText = gName;
            }
            if ( dName != '' ) {
                decid.innerText = dName;
            }
        }

        window.addEventListener('resize', sizeBread );

        return screen;

    }

    function sizeBread() {
        var org = document.getElementById("breadNames");
        var group = document.getElementById("breadGroup");
        var decid = document.getElementById("breadDecid");

        var nowScreen = bakeTheBread();

        if ( nowScreen < 658) {
            if ( oName != '' ) {
                org.innerText = abbreviate(oName, 5);
            }
            if ( gName != '' ) {
                group.innerText = abbreviate(gName, 5);
            }
            if ( dName != '' ) {
                decid.innerText = abbreviate(dName, 5);
            }
        }

    }

    function abbreviate(str, max, suffix) {
        if((str = str.replace(/^s+|s+$/g, '').replace(/[rn]*s*[rn]+/g, ' ').replace(/[ t]+/g, ' ')).length <= max)
        {
            return str;
        }

        var
            abbr = '',
            str = str.split(' '),
            suffix = (typeof suffix !== 'undefined' ? suffix : ' ...'),
            max = (max - suffix.length);

        for(var len = str.length, i = 0; i < len; i ++)
        {
            if((abbr + str[i]).length > max)
            {
                abbr += str[i] + ' ';
            }
            else { break; }
        }

        return abbr.replace(/[ ]$/g, '') + suffix;
    }





});
