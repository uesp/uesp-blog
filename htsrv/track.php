<?php
/**
 * This is the goal tracker + redirect handler.
 *
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2013 by Francois Planque - {@link http://fplanque.com/}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 *
 * @package sessions
 *
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id: track.php 3508 2013-04-19 06:58:02Z yura $
 */

/**
 * @global Hit
 */
global $Hit;

/**
 * Do the MAIN initializations:
 */
require_once dirname(__FILE__).'/../conf/_config.php';

/**
 * HEAVY :(
 */
require_once $inc_path.'_main.inc.php';

param( 'key', 'string', '' );

$sql = 'SELECT *
					FROM T_track__goal
				 WHERE goal_key = '.$DB->quote($key);

$Goal = $DB->get_row( $sql );

if( empty($Goal) )
{
	require $skins_path.'_404_not_found.main.php'; // error & exit
	exit(0);
}

if( !empty($Goal->goal_redir_url) )
{	// TODO adapt and use header_redirect()

	$redir_url = $Goal->goal_redir_url;

	if( preg_match( '/\$([a-z_]+)\$/i', $redir_url, $matches ) )
	{	// We want to replace a special code like $hit_ID$ in the redir URL:
		// Tblue> What about using preg_replace_callback() to do this?
		// echo $matches[1];
		switch( $matches[1] )
		{
			case 'hit_ID':
				// We need to log the HIT now! Because we need the hit ID!
				$Hit->log();
				$redir_url = str_replace( '$hit_ID$', $Hit->ID, $redir_url );
				break;
		}
	}

	header_http_response( '302 Found' );
	header( 'Location: '.$redir_url, true, 302 ); // explictly setting the status is required for (fast)cgi
	// TODO: dh> str_repeat won't be enough (when gzipped), see http://core.trac.wordpress.org/ticket/8942
	//           should be probably a more general function and get used in e.g. bad_request_die(), too (if necessary)
	echo str_repeat( ' ', 1024 );
	evo_flush();
	// At this point Firefox 2 will redirect without waiting for the end of the page, but IE7 will not :/
}
else
{	// No redirection specified, we send a blank pixel instead:
	load_funcs( '_core/_template.funcs.php' );
	$blank_gif = $rsc_path.'img/blank.gif';

 	header('Content-type: image/gif' );
	header('Content-Length: '.filesize( $blank_gif ) );
	header_nocache();
	readfile( $blank_gif );
	evo_flush();
}

// We need to log the HIT now! Because we need the hit ID!
$Hit->log();

// pre_dump($Hit);

$extra_params = '';
if( isset( $_SERVER['QUERY_STRING'] ) )
{
	$extra_params = '&'.$_SERVER['QUERY_STRING'].'&';
	$extra_params = str_replace( '&key='.$key.'&', '&', $extra_params );
	$extra_params = trim( $extra_params, '&' );
}


// Record a goal hit:
$sql = 'INSERT INTO T_track__goalhit( ghit_goal_ID, ghit_hit_ID, ghit_params )
				VALUES( '.$Goal->goal_ID.', '.$Hit->ID.', '.$DB->quote($extra_params).' )';
$DB->query( $sql );

?>