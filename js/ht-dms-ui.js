jQuery(document).ready(function( $ ) {
    ajaxURL = htDMS.ajaxURL;

    /**
     * Containers to use paginated views for
     *
     * Should be all views that are not single item views.
     *
     * @since 0.0.2
     */
    var paginatedViews = [ '#users_groups', '#public_groups', '#users_organizations', '#assigned_tasks', '#decisions_tasks' ];
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
    if ( undefined != consensusPossibilities && undefined != consensusPossibilities.possible_results[0] ) {
        var p0 = consensusPossibilities.possible_results[0];
        var p1 = consensusPossibilities.possible_results[1];
        var p2 = consensusPossibilities.possible_results[2];
    }

    //@todo (issue #13)
    // - get selection from action form
    // - use it to select one of the variables defined above
    // - output it in container
    //https://github.com/HoloTree/ht_dms/issues/13


});
