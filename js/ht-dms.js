jQuery(document).ready(function($) {
    ajaxURL = htDMS.ajaxURL;

    var data;
    //main function for getting views
    function viewGet( view, args, returnType ) {
        $.get(
            ajaxURL, {
                'action': 'holotree_dms_ui_ajax_view',
                'nonce' : htDMS.nonce,
                'view' : view,
                'args' : args,
                'returnType' : returnType
            },
            function( response ) {
                if ( response != undefined ) {
                    data = response;
                }
            }
        )
    }



    //defaults for our view getters
    var limit = 5;
    var returnType = 'template';
    var uID = null;
    var oID = null;

    /**
     * Get the users_groups view - All groups a user is a member of.
     *
     * AJAX wrapper for ht_dms\ui\build\views::users_groups()
     *
     * @param int|null  uID         Optional. ID of user to get. If null, the default, current user's groups are shown.
     * @param int|null  oID         Optional. ID of organization to limit groups to. If null, the default, groups in all organizations are returned.
     * @param int       limit       Optional. Number of groups to return. Default is 5.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function usersGroups( uID, oID, limit, returnType ) {

        return viewGet( 'users_groups', ['null', uID, limit ], 'template' );

    }

    /**
     * Get the public_groups view - All public groups
     *
     * AJAX wrapper for ht_dms\ui\build\views::public_groups()
     *
     * @param int|null  oID         Optional. ID of organization to limit groups to. If null, the default, groups in all organizations are returned.
     * @param int       limit       Optional. Number of groups to return. Default is 5.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function publicGroups( oID, limit, returnType ) {

        return viewGet( 'public_groups', [ 'null', oID, limit ], returnType );

    }

    /**
     * Get the assigned_tasks view - All tasks a user is assigned to.
     *
     * AJAX wrapper for ht_dms\ui\build\views::assigned_tasks()
     *
     * @param int|null  uID         Optional. ID of user to get assigned tasks for. If null, the default, current user's tasks are shown.
     * @param int|null  oID         Optional. ID of organization to limit tasks to. If null, the default, tasks in all organizations are returned.
     * @param int       limit       Optional. Number of tasks to return. Default is 5.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function assignedTasks( uID, oID, limit, returnType ) {

        return viewGet( 'assigned_tasks', [ 'null', uID, oID, limit ], returnType );
    }

    /**
     * Get the users_organizations view - All organizations a user is a member of.
     *
     * AJAX wrapper for ht_dms\ui\build\views::users_organizations()
     *
     * @param int|null  uID         Optional. ID of user to get organizations for. If null, the default, current user's organizations are shown.
     * @param int       limit       Optional. Number of organizations to return. Default is 5.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function usersOrganizations( uID, limit, returnType ) {

        return viewGet( 'users_organizations', [ 'null', uID, limit ], returnType );

    }

    /**
     * Get the decisions_tasks view - All (or some) tasks for a decision.
     *
     * AJAX wrapper for ht_dms\ui\build\views::decisions_tasks()
     *
     * @param int|null  ID          ID of decision to get tasks from.
     * @param int       limit       Optional. Number of tasks to return. Default is 5. Use -1 for all.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function decisionsTasks( ID, limit, returnType ) {

        return viewGet( 'decisions_tasks', [ 'null', ID, limit ], returnType );

    }

    /**
     * Get a single organization view.
     *
     * AJAX wrapper for ht_dms\ui\build\views::organization()
     *
     * @param int       ID          ID of organization to get
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function organization( ID, returnType ) {

        return viewGet( 'organization', [ 'null', ID ], returnType );

    }

    /**
     * Get a single group view.
     *
     * AJAX wrapper for ht_dms\ui\build\views::group()
     *
     * @param int       ID          ID of group to get.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function group( ID, returnType ) {

        return viewGet( 'group', [ 'null', ID ], returnType );

    }

    /**
     * Get a single decision view.
     *
     * AJAX wrapper for ht_dms\ui\build\views::decision()
     *
     * @param int       ID          ID of decision to get
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function decision( ID, returnType ) {

        return viewGet( 'decision', [ 'null', ID ], returnType );

    }

    /**
     * Get a single task view.
     *
     * AJAX wrapper for ht_dms\ui\build\views::task()
     *
     * @param int       ID          ID of task to get
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function task( ID ) {

        return viewGet( 'task', [ 'null', ID ], returnType );

    }

});
