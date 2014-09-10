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
    var select_field = '#dms_action';

    function result( select_field, consensusPossibilities ) {
        if ( undefined != consensusPossibilities && undefined != consensusPossibilities.possible_results[0]) {
            var p0 = consensusPossibilities.possible_results[0];
            var p1 = consensusPossibilities.possible_results[1];
            var p2 = consensusPossibilities.possible_results[2];

            var selected_action = $( select_field ) .val();
            var result = false;
            if ( selected_action === 'accept-change') {
                var result = 'Decision will be ' + p1 + '.';
            }

            if (selected_action === 'block') {
                var result = 'Decision will be ' + p2 + '.';
            }

            //@todo translation-friendliness!
            if (selected_action === 'propose-change') {
                var result = 'You will be able to propose a new version of this decision to consider.';
            }

            if ( selected_action === 'respond') {
                var result = 'You will be able to respond to this decision';
            }

            if ( false != result ) {
                result = 'If you make this choice: ' + result;
                $( '#dms-action-result').empty();
                $( '#dms-action-result' ).append( result );
            }


            console.log(selected_action);
        }
    }

    result( select_field );
    $( select_field ).change( function() {
        result( select_field, consensusPossibilities );
    });

});
