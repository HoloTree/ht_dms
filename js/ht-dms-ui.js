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

    /**
     * Reload views on Caldera submit
     *
     * Acts on decision consensus & group membership
     *
     * @since 0.0.3
     */
    $( document ).ajaxSuccess(function( event, xhr, settings ) {
        console.log( settings );
        console.log( htDMS.id );
       if ( settings.url == './' ) {
           var consensus = '#consensus-view';
           if ( $( consensus ).length ) {
               reloadConsensus( consensus, htDMS.id );
           }

           var membership = "#group-membership";
           if ( $( membership).length ) {
               reloadMembership( membership, htDMS.id );
           }

       }

    });


});
