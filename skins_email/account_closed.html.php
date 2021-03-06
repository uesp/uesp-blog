<?php
/**
 * This is sent to ((SystemAdmins)) to notify them that a ((User)) account has been closed (either by the User themselves or by another Admin).
 *
 * For more info about email skins, see: http://b2evolution.net/man/themes-templates-skins/email-skins/
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2013 by Francois Planque - {@link http://fplanque.com/}
 *
 * @version $Id: account_closed.html.php 3183 2013-03-10 21:59:41Z fplanque $
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

// ---------------------------- EMAIL HEADER INCLUDED HERE ----------------------------
emailskin_include( '_email_header.inc.html.php', $params );
// ------------------------------- END OF EMAIL HEADER --------------------------------

global $admin_url, $htsrv_url;

// Default params:
$params = array_merge( array(
		'login'   => '',
		'email'   => '',
		'reason'  => '',
		'user_ID' => '',
		'closed_by_admin' => '',// Login of admin which closed current user account
	), $params );

echo '<p>';
if( empty( $params['closed_by_admin'] ) )
{ // Current user closed own account
	echo T_('A user account was closed!');
}
else
{ // Admin closed current user account
	printf( T_('A user account was closed by %s'), get_user_colored_login( $params['closed_by_admin'] ) );
}
echo "</p>\n";

echo '<p>'.T_('Login').": ".get_user_colored_login( $params['login'] )."</p>\n";
echo '<p>'.T_('Email').": ".$params['email']."</p>\n";
echo '<p>'.T_('Account close reason').": ".nl2br( $params['reason'] )."</p>\n";

// Buttons:
echo '<div class="buttons">'."\n";
echo get_link_tag( $admin_url.'?ctrl=user&user_tab=profile&user_ID='.$params['user_ID'], T_( 'Edit User account' ), 'button_yellow' )."\n";
echo "</div>\n";

// Footer vars:
$params['unsubscribe_text'] = T_( 'If you don\'t want to receive any more notification when an account was closed, click here:' )
			.' <a href="'.$htsrv_url.'quick_unsubscribe.php?type=account_closed&user_ID=$user_ID$&key=$unsubscribe_key$">'
			.T_('instant unsubscribe').'</a>.';

// ---------------------------- EMAIL FOOTER INCLUDED HERE ----------------------------
emailskin_include( '_email_footer.inc.html.php', $params );
// ------------------------------- END OF EMAIL FOOTER --------------------------------
?>