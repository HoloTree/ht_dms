/**global loginData **/
jQuery( function () {

    HTDMSLogin.init();
} );

(function ( $, login ) {

    /**
     * Message container
     *
     * @since 0.0.3
     */
    login.messageContainer = '#invite-code-message';

    /**
     * Invite Code Field
     *
     * @since 0.0.3
     */
    login.inviteCodeField = '#invitation_code';

    /**
     * Email field name
     *
     * @since 0.0.3
     */
    login.emailField = '#user_email';

    /**
     * The user's email
     *
     * @since 0.0.3
     */
    login.email = '';

    /**
     * Get the invite code
     *
     * @since 0.0.3
     */
    login.urlInviteCode = '';

    /**
     * Submit button
     *
     * @since 0.0.3
     */
    login.submitButton = 'input#wp-submit';

    /**
     * Submit message container
     *
     * @since 0.0.3
     */
    login.submitMessageContianer = 'p.submit';

    /**
     * Bootstrap login script
     *
     * @since 0.0.3
     */
    login.init = function( ) {
        if ( 'register' === login.getParameterByName( 'action' ) ) {
            login.getCodeFromURL();
            login.enterInviteCode();

            if ( '' !== $( login.inviteCodeField ).val() ) {
                login.message( loginData.needEmail, true );
            }

            $( login.emailField ).change( function () {
                login.checkSequence();
            } );

            $( login.inviteCodeField ).change( function () {
                login.checkSequence();
            } );

            login.submission( false );
        }

    };

    /**
     * Run the check sequence if we can haz email and invite code
     *
     * @since 0.0.3
     */
    login.checkSequence = function() {

        if ( '' != $( login.emailField ).val() && '' != $( login.inviteCodeField).val() ) {
            login.message( loginData.processing, false );
            login.checkCode();
            login.enterInviteCode();
        }
        else if ( '' == $( login.emailField ).val() && '' != $( login.inviteCodeField).val() ) {
            login.message( loginData.needEmail, true );
        }
    };

    /**
     * Set a new message
     *
     * @param message
     */
    login.message = function( message, warning ) {

        $( login.messageContainer ).fadeOut().html( message ).fadeIn();
        if ( warning ) {
            $( login.messageContainer ).css( {
               'background-color' : '#5a180a',
                'color' : '#f4d99f'
            });
        }
        else {
            $( login.messageContainer ).removeAttr( 'style' );
        }
    };

    /**
     * Set invite code from URL in the right field, if we can.
     *
     * @since 0.0.3
     */
    login.enterInviteCode = function() {

        if ( '' != login.urlInviteCode ) {
            
            if ( '' == $( login.emailField ).val() ) {
                login.message( loginData.needEmail, true );
            }
        }


    };

    /**
     * Check the invite code via email
     *
     * @since 0.0.3
     */
    login.checkCode = function() {

        $.post(
            loginData.ajaxURL, {
                action: 'ht_dms_validate_invite_code',
                nonce: loginData.nonce,
                code: $(login.inviteCodeField).val(),
                email: $(login.emailField).val(),
            },
            function( response ) {

                    if ( 1 == response ) {
                        login.message( loginData.codeGood, false );
                        login.canHazRegister = true;
                        login.submission();

                    } else {
                        login.message( loginData.codeNotGood, true );
                    }

            }

        );

    };

    /**
     * Get inputted Email
     *
     * @since 0.0.3
     * @returns {*}
     */
    login.getEmail = function() {

        return $( login.emailField ).val();

    };

    /**
     * Get invite code from URL
     *
     * @since 0.0.3
     */
    login.getCodeFromURL = function() {

        if ( '' != login.getParameterByName( 'ht_dms_invite_code' ) ) {
            login.urlInviteCode = login.getParameterByName( 'ht_dms_invite_code' );
        }
    };

    login.submission = function( canHazRegister) {
        console.log( canHazRegister );
        if ( false === canHazRegister ) {
            $( login.submitButton ).hide();
            $( login.submitMessageContianer ).append( '<p id="no-submit">' + loginData.noSubmit + '</p>' );
        }
        else {
            $( login.submitMessageContianer = '#no-submit' ).hide();
            $( login.submitButton ).show();

        }
    };


    /**
     * Get URL Param
     *
     * Source: http://stackoverflow.com/a/901144/1469799
     *
     * @since 0.0.3
     */
    login.getParameterByName = function(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]" );
        var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, ''));
    };

})( jQuery, window.HTDMSLogin || ( window.HTDMSLogin = {} ) );
