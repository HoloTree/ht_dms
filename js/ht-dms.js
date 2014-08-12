jQuery(document).ready(function($) {
    ajaxURL = htDMS.ajaxURL;

    var data;
    var portOut = '';

    /**
     * Allows for getting views of the ht_dms\ui\build\views class via AJAX
     *
     * @param view Which view to get. Options: users_groups|public_groups|assigned_tasks|users_organizations|decision_tasks|organization|group|decision|task
     * @param args Array of arguments, varies by view.
     * @param returnType  Optional. What to return. Options: template|JSON|urlstring
     * @param put The ID of the container to put the view in.
     */
     function portIt(port) {
        // console.log("ran");
        portOut = port;
        // console.log(portOut);
        return toWindow(portOut);
     }
    function viewGet( view, args, returnType, put ) {
        $.get(
            ajaxURL, {
                'action': 'holotree_dms_ui_ajax_view',
                'nonce' : htDMS.nonce,
                'view' : view,
                'args' : args,
                'returnType' : returnType
            },
            function( response ) {
                var string = response.toString();

                // console.log( string );
                // alert(string.charAt(0));
                var port = string.slice(1,3119);
                portOut += port;

                return portIt(portOut);

                // console.log(portOut);

              

            }
        );
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
     * @param string    put         The ID of the container to put data in.
     * @param int|null  uID         Optional. ID of user to get. If null, the default, current user's groups are shown.
     * @param int|null  oID         Optional. ID of organization to limit groups to. If null, the default, groups in all organizations are returned.
     * @param int       limit       Optional. Number of groups to return. Default is 5.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function usersGroups( put, uID, oID, limit, returnType ) {

        return viewGet( 'users_groups', [ returnType, uID, oID, limit ], returnType, put );

    }

    /**
     * Get the public_groups view - All public groups
     *
     * AJAX wrapper for ht_dms\ui\build\views::public_groups()
     *
     * @param string    put         The ID of the container to put data in.
     * @param int|null  oID         Optional. ID of organization to limit groups to. If null, the default, groups in all organizations are returned.
     * @param int       limit       Optional. Number of groups to return. Default is 5.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function publicGroups( put, oID, limit, returnType ) {

        return viewGet( 'public_groups', [ returnType, oID, limit ], returnType );

    }

    /**
     * Get the assigned_tasks view - All tasks a user is assigned to.
     *
     * AJAX wrapper for ht_dms\ui\build\views::assigned_tasks()
     *
     * @param string    put         The ID of the container to put data in.
     * @param int|null  uID         Optional. ID of user to get assigned tasks for. If null, the default, current user's tasks are shown.
     * @param int|null  oID         Optional. ID of organization to limit tasks to. If null, the default, tasks in all organizations are returned.
     * @param int       limit       Optional. Number of tasks to return. Default is 5.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function assignedTasks( put, uID, oID, limit, returnType ) {

        return viewGet( 'assigned_tasks', [ returnType, uID, oID, limit ], returnType );
    }

    /**
     * Get the users_organizations view - All organizations a user is a member of.
     *
     * AJAX wrapper for ht_dms\ui\build\views::users_organizations()
     *
     * @param string    put         The ID of the container to put data in.
     * @param int|null  uID         Optional. ID of user to get organizations for. If null, the default, current user's organizations are shown.
     * @param int       limit       Optional. Number of organizations to return. Default is 5.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function usersOrganizations( put, uID, limit, returnType ) {

        return viewGet( 'users_organizations', [ returnType, uID, limit ], returnType );

    }

    /**
     * Get the decisions_tasks view - All (or some) tasks for a decision.
     *
     * AJAX wrapper for ht_dms\ui\build\views::decisions_tasks()
     *
     * @param string    put         The ID of the container to put data in.
     * @param int|null  ID          ID of decision to get tasks from.
     * @param int       limit       Optional. Number of tasks to return. Default is 5. Use -1 for all.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function decisionsTasks( put, ID, limit, returnType ) {

        return viewGet( 'decisions_tasks', [ returnType, ID, limit ], returnType );

    }

    /**
     * Get a single organization view.
     *
     * AJAX wrapper for ht_dms\ui\build\views::organization()
     *
     * @param string    put         The ID of the container to put data in.
     * @param int       ID          ID of organization to get
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function organization( put, ID, returnType ) {

        return viewGet( 'organization', [ returnType, ID ], returnType );

    }

    /**
     * Get a single group view.
     *
     * AJAX wrapper for ht_dms\ui\build\views::group()
     *
     * @param string    put         The ID of the container to put data in.
     * @param int       ID          ID of group to get.
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function group( put, ID, returnType ) {

        return viewGet( 'group', [ returnType, ID ], returnType );

    }

    /**
     * Get a single decision view.
     *
     * AJAX wrapper for ht_dms\ui\build\views::decision()
     *
     * @param string    put         The ID of the container to put data in.
     * @param int       ID          ID of decision to get
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function decision( put, ID, returnType ) {

        return viewGet( 'decision', [ returnType, ID ], returnType );

    }

    /**
     * Get a single task view.
     *
     * AJAX wrapper for ht_dms\ui\build\views::task()
     *
     * @param string    put         The ID of the container to put data in.
     * @param int       ID          ID of task to get
     * @param string    returnType  Optional. What to return. Options: template|JSON|urlstring
     *
     * @return string|JSON          Either HTML for the view, or a JSON object of the posts, or a URL string to get those posts via REST API.
     *
     * @since   0.0.2
     */
    function task( put, ID ) {

        return viewGet( 'task', [ returnType, ID ], returnType );

    }

        window.usersGroups = usersGroups;
        window.publicGroups = publicGroups;
        window.assignedTasks = assignedTasks;
        window.usersOrganizations = usersOrganizations;
        window.decisionsTasks = decisionsTasks;
        window.organization = organization;
        window.group = group;
        window.decision = decision;
        window.task = task;
    function toWindow(port) {
        window.ported = port;
        // console.log(window.ported);
    }


});
