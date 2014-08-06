jQuery(document).ready(function($) {
    ajaxURL = htDMS.ajaxURL;

    //main function for getting views
    //@todo figure out how to handle callbacks
    function viewGet( view, args, returnType, callback ) {
        $.get(
            ajaxURL, {
                'action': 'holotree_dms_ui_ajax_view',
                'nonce' : htDMS.nonce,
                'view' : view,
                'args' : args,
                'returnType' : returnType
            },
            function( response ) {

                $("#here").append(response);

            }

        )
    }

    //experimental usage
    $( "#target" ).click(function() {
        var request = viewGet( 'users_groups', ['null','1'], 'template' );

    });




});
