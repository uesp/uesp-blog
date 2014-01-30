<?php
/**
 * This is the handler for asynchronous 'AJAX' calls.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * fp> TODO: it would be better to have the code for the actions below part of the controllers they belong to.
 * This would require some refectoring but would be better for maintenance and code clarity.
 *
 * @copyright (c)2003-2013 by Francois Planque - {@link http://fplanque.com/}
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * }}
 *
 * @package evocore
 *
 * @version $Id: async.php 4110 2013-07-02 07:53:56Z yura $
 */


/**
 * Do the MAIN initializations:
 */
require_once dirname(__FILE__).'/../conf/_config.php';

/**
 * HEAVY :(
 *
 * @todo dh> refactor _main.inc.php to be able to include small parts
 *           (e.g. $current_User, charset init, ...) only..
 *           It worked already for $DB (_connect_db.inc.php).
 * fp> I think I'll try _core_main.inc , _evo_main.inc , _blog_main.inc ; this file would only need _core_main.inc
 */
require_once $inc_path.'_main.inc.php';

// create global $blog variable
global $blog;
// Init $blog with NULL to avoid injections, it will get the correct value from param where it is required
$blog = NULL;

param( 'action', 'string', '' );

// Check global permission:
if( empty($current_User) || ! $current_User->check_perm( 'admin', 'restricted' ) )
{	// No permission to access admin...
	require $adminskins_path.'_access_denied.main.php';
}

// Make sure the async responses are never cached:
header_nocache();
header_content_type( 'text/html', $io_charset );

// Save current debug values
$current_debug = $debug;
$current_debug_jslog = $debug_jslog;

// Do not append Debuglog to response!
$debug = false;

// Do not append Debug JSlog to response!
$debug_jslog = false;

// Init AJAX log
$Ajaxlog = new Log();

$Ajaxlog->add( sprintf( T_('action: %s'), $action ), 'note' );

$incorrect_action = false;

$add_response_end_comment = true;

// fp> Does the following have an HTTP fallback when Javascript/AJ is not available?
// dh> yes, but not through this file..
// dh> IMHO it does not make sense to let the "normal controller" handle the AJAX call
//     if there's something lightweight like calling "$UserSettings->param_Request()"!
//     Hmm.. bad example (but valid). Better example: something like the actions below, which
//     output only a small part of what the "real controller" does..
switch( $action )
{
	case 'add_plugin_sett_set':
		// Dislay a new Plugin(User)Settings set ( it's used only from plugins with "array" type settings):

		// This does not require CSRF because it doesn't update the db, it only displays a new block of empty plugin setting fields

		// Check permission to view plugin settings:
		$current_User->check_perm( 'options', 'view', true );

		param( 'plugin_ID', 'integer', true );

		$admin_Plugins = & get_Plugins_admin(); // use Plugins_admin, because a plugin might be disabled
		$Plugin = & $admin_Plugins->get_by_ID($plugin_ID);
		if( ! $Plugin )
		{
			bad_request_die('Invalid Plugin.');
		}
		param( 'set_type', 'string', '' ); // "Settings" or "UserSettings"
		if( $set_type != 'Settings' /* && $set_type != 'UserSettings' */ )
		{
			bad_request_die('Invalid set_type param!');
		}
		param( 'set_path', '/^\w+(?:\[\w+\])+$/', '' );

		load_funcs('plugins/_plugin.funcs.php');

		// Init the new setting set:
		_set_setting_by_path( $Plugin, $set_type, $set_path, array() );

		// Get the new plugin setting set and display it with a fake Form
		$r = get_plugin_settings_node_by_path( $Plugin, $set_type, $set_path, /* create: */ false );

		$Form = new Form(); // fake Form to display plugin setting
		autoform_display_field( $set_path, $r['set_meta'], $Form, $set_type, $Plugin, NULL, $r['set_node'] );
		break;

	case 'set_object_link_position':
		// Change a position of a link on the edit item screen (fieldset "Images & Attachments")

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'link' );

		// Check item/comment edit permission below after we have the $LinkOwner object ( we call LinkOwner->check_perm ... )

		param('link_ID', 'integer', true);
		param('link_position', 'string', true);

		$LinkCache = & get_LinkCache();
		if( ( $Link = & $LinkCache->get_by_ID( $link_ID ) ) === false )
		{	// Bad request with incorrect link ID
			echo '';
			exit(0);
		}
		$LinkOwner = & $Link->get_LinkOwner();

		// Check permission:
		$LinkOwner->check_perm( 'edit', true );

		if( $Link->set( 'position', $link_position ) && $Link->dbupdate() )
		{	// update was successful
			echo 'OK';

			// Update last touched date of Item
			$LinkOwner->item_update_last_touched_date();
		}
		else
		{	// return the current value on failure
			echo $Link->get( 'position' );
		}
		break;

	case 'get_login_list':
		// Get users login list for username form field hintbox.

		// current user must have at least view permission to see users login
		$current_User->check_perm( 'users', 'view', true );

		$text = trim( urldecode( param( 'q', 'string', '' ) ) );

		/**
		 * sam2kb> The code below decodes percent-encoded unicode string produced by Javascript "escape"
		 * function in format %uxxxx where xxxx is a Unicode value represented as four hexadecimal digits.
		 * Example string "MAMA" (cyrillic letters) encoded with "escape": %u041C%u0410%u041C%u0410
		 * Same word encoded with "encodeURI": %D0%9C%D0%90%D0%9C%D0%90
		 *
		 * jQuery hintbox plugin uses "escape" function to encode URIs
		 *
		 * More info here: http://en.wikipedia.org/wiki/Percent-encoding#Non-standard_implementations
		 */
		if( preg_match( '~%u[0-9a-f]{3,4}~i', $text ) && version_compare(PHP_VERSION, '5', '>=') )
		{	// Decode UTF-8 string (PHP 5 and up)
			$text = preg_replace( '~%u([0-9a-f]{3,4})~i', '&#x\\1;', $text );
			$text = html_entity_decode( $text, ENT_COMPAT, 'UTF-8' );
		}

		if( !empty( $text ) )
		{
			$SQL = new SQL();
			$SQL->SELECT( 'user_login' );
			$SQL->FROM( 'T_users' );
			$SQL->WHERE( 'user_login LIKE "'.$DB->escape($text).'%"' );
			$SQL->LIMIT( '10' );
			$SQL->ORDER_BY('user_login');

			echo implode( "\n", $DB->get_col($SQL->get()) );
		}

		// don't show ajax response end comment, because the result will be processed with jquery hintbox
		$add_response_end_comment = false;
		break;

	case 'get_opentrash_link':
		// Used to get a link 'Open recycle bin' in order to show it in the header of comments list

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'comment' );

		echo get_opentrash_link( true, true );
		break;

	case 'set_comment_status':
		// Used for quick moderation of comments in dashboard, item list full view and comment list screens

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'comment' );

		// Check comment moderate permission below after we have the $edited_Comment object

		$is_admin_page = true;
		$blog = param( 'blogid', 'integer' );
		$moderation = param( 'moderation', 'string', NULL );
		$status = param( 'status', 'string' );
		$expiry_status = param( 'expiry_status', 'string', 'active' );
		$limit = param( 'limit', 'integer', 0 );
		$edited_Comment = & Comment_get_by_ID( param( 'commentid', 'integer' ), false );
		if( $edited_Comment !== false )
		{	// The comment still exists
			// Check permission:
			$current_User->check_perm( 'comment!'.$status, 'moderate', true, $edited_Comment );

			$redirect_to = param( 'redirect_to', 'url', NULL );

			$edited_Comment->set( 'status', $status );
			// Comment moderation is done, handle moderation "secret"
			$edited_Comment->handle_qm_secret();
			$edited_Comment->dbupdate();

			if( $status == 'published' )
			{
				$edited_Comment->handle_notifications();
			}

			if( $moderation != NULL )
			{
				$statuses = param( 'statuses', 'string', NULL );
				$item_ID = param( 'itemid', 'integer' );
				$currentpage = param( 'currentpage', 'integer', 1 );

				if( strlen($statuses) > 2 )
				{
					$statuses = substr( $statuses, 1, strlen($statuses) - 2 );
				}
				$status_list = explode( ',', $statuses );
				if( $status_list == NULL )
				{
					$status_list = get_visibility_statuses( 'keys', array( 'redirected', 'trash' ) );
				}

				// In case of comments_fullview we must set a filterset name to be abble to restore filterset.
				// If $moderation is not NULL, then this requests came from the comments_fullview
				// TODO: asimo> This should be handled with a better solution
				$filterset_name = ( $item_ID > 0 ) ? '' : 'fullview';
				if( $limit == 0 )
				{
					$limit = $UserSettings->get( 'results_per_page' ); 
				}
				echo_item_comments( $blog, $item_ID, $status_list, $currentpage, $limit, array(), $filterset_name, $expiry_status );
				break;
			}
		}

		if( $moderation == NULL )
		{
			get_comments_awaiting_moderation( $blog );
		}

		break;

	case 'delete_comment':
		// Delete a comment from dashboard screen

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'comment' );

		// Check comment moderate permission below after we have the $edited_Comment objects

		$is_admin_page = true;
		$blog = param( 'blogid', 'integer' );
		$edited_Comment = & Comment_get_by_ID( param( 'commentid', 'integer' ), false );
		if( $edited_Comment !== false )
		{	// The comment still exists
			// Check permission:
			$current_User->check_perm( 'comment!CURSTATUS', 'delete', true, $edited_Comment );

			$edited_Comment->dbdelete();
		}

		get_comments_awaiting_moderation( $blog );
		break;

	case 'delete_comments':
		// Delete the comments from the list on dashboard, on comments full text view screen or on a view item screen

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'comment' );

		// Check comment moderate permission below after we have the $edited_Comment objects

		$is_admin_page = true;
		$blog = param( 'blogid', 'integer' );
		$commentIds = param( 'commentIds', 'array' );
		$statuses = param( 'statuses', 'string', NULL );
		$expiry_status = param( 'expiry_status', 'string', 'active' );
		$item_ID = param( 'itemid', 'integer' );
		$currentpage = param( 'currentpage', 'integer', 1 );
		$limit = param( 'limit', 'integer', 0 );

		foreach( $commentIds as $commentID )
		{
			$edited_Comment = & Comment_get_by_ID( $commentID, false );
			if( $edited_Comment !== false )
			{ // The comment still exists
				// Check permission:
				$current_User->check_perm( 'comment!CURSTATUS', 'delete', true, $edited_Comment );

				$edited_Comment->dbdelete();
			}
		}

		if( strlen($statuses) > 2 )
		{
			$statuses = substr( $statuses, 1, strlen($statuses) - 2 );
		}
		$status_list = explode( ',', $statuses );
		if( $status_list == NULL )
		{
			$status_list = get_visibility_statuses( 'keys', array( 'redirected', 'trash' ) );
		}

		// In case of comments_fullview we must set a filterset name to be abble to restore filterset.
		// If $item_ID is not valid, then this requests came from the comments_fullview
		// TODO: asimo> This should be handled with a better solution
		$filterset_name = /*'';*/( $item_ID > 0 ) ? '' : 'fullview';
		if( $limit == 0 )
		{
			$limit = $UserSettings->get( 'results_per_page' ); 
		}
		echo_item_comments( $blog, $item_ID, $status_list, $currentpage, $limit, array(), $filterset_name, $expiry_status );
		break;

	case 'delete_comment_url':
		// Delete spam URL from a comment directly in the dashboard - comment remains otherwise untouched

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'comment' );

		// Check comment edit permission below after we have the $edited_Comment object

		$blog = param( 'blogid', 'integer' );
		$edited_Comment = & Comment_get_by_ID( param( 'commentid', 'integer' ), false );
		if( $edited_Comment !== false && $edited_Comment->author_url != NULL )
		{	// The comment still exists
			// Check permission:
			$current_User->check_perm( 'comment!CURSTATUS', 'edit', true, $edited_Comment );

			$edited_Comment->set( 'author_url', NULL );
			$edited_Comment->dbupdate();
		}

		break;

	case 'refresh_comments':
		// Refresh the comments list on dashboard by clicking on the refresh icon or after ban url

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'comment' );

		$is_admin_page = true;
		$blog = param( 'blogid', 'integer' );
		// Check minimum permissions ( The comment specific permissions are checked when displaying the comments )
		$current_User->check_perm( 'blog_ismember', 'view', true, $blog );

		get_comments_awaiting_moderation( $blog );
		break;

	case 'refresh_item_comments':
		// Refresh item comments on the item view screen, or refresh all blog comments on comments view, if param itemid = -1
		// A refresh is used on the actions:
		// 1) click on the refresh icon.
		// 2) limit by selected status(radioboxes 'Draft', 'Published', 'All comments').
		// 3) ban by url of a comment

		load_funcs( 'items/model/_item.funcs.php' );

		$is_admin_page = true;
		$blog = param( 'blogid', 'integer' );
		$item_ID = param( 'itemid', 'integer', NULL );
		$statuses = param( 'statuses', 'string', NULL );
		$expiry_status = param( 'expiry_status', 'string', 'active' );
		$currentpage = param( 'currentpage', 'string', 1 );

		// Check minimum permissions ( The comment specific permissions are checked when displaying the comments )
		$current_User->check_perm( 'blog_ismember', 'view', true, $blog );

		if( strlen($statuses) > 2 )
		{
			$statuses = substr( $statuses, 1, strlen($statuses) - 2 );
		}
		$status_list = explode( ',', $statuses );
		if( $status_list == NULL )
		{ // init statuses
			$status_list = get_visibility_statuses( 'keys', array( 'redirected', 'trash' ) );
		}

		echo_item_comments( $blog, $item_ID, $status_list, $currentpage, 20, array(), '', $expiry_status );
		break;

	case 'get_tags':
		// Get list of item tags, where $term is part of the tag name (sorted)
		// To be used for Tag autocompletion

		// Crumb check and permission check are not required because this won't modify anything and it returns public info

		$term = param('term', 'string');

		$tags = $DB->get_results( '
			SELECT tag_name AS id, tag_name AS title
			  FROM T_items__tag
			 WHERE tag_name LIKE '.$DB->quote('%'.$term.'%').'
			 ORDER BY tag_name', ARRAY_A );

		// Check if current term is not an existing tag
		$term_is_new_tag = true;
		foreach( $tags as $tag )
		{
			if( $tag['title'] == $term )
			{ // Current term is an existing tag
				$term_is_new_tag = false;
			}
		}
		if( $term_is_new_tag )
		{	// Add current term in the beginning of the tags list
			array_unshift( $tags, array( 'id' => $term, 'title' => $term ) );
		}

		echo evo_json_encode( $tags );
		exit(0);

	case 'dom_type_edit':
		// Update type of a reffering domain from list screen by clicking on the type column

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'domtype' );

		// Check permission:
		$current_User->check_perm( 'stats', 'edit', true );

		load_funcs('sessions/model/_hitlog.funcs.php');

		$dom_type = param( 'new_dom_type', 'string' );
		$dom_name = param( 'dom_name', 'string' );

		$DB->query( 'UPDATE T_basedomains
						SET dom_type = '.$DB->quote($dom_type).'
						WHERE dom_name =' . $DB->quote($dom_name));
		echo '<a href="#" rel="'.$dom_type.'">'.stats_dom_type_title( $dom_type ).'</a>';
		break;

	case 'iprange_status_edit':
		// Update status of IP range from list screen by clicking on the status column

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'iprange' );

		// Check permission:
		$current_User->check_perm( 'spamblacklist', 'edit', true );

		$new_status = param( 'new_status', 'string' );
		$iprange_ID = param( 'iprange_ID', 'integer', true );

		$DB->query( 'UPDATE T_antispam__iprange
						SET aipr_status = '.( empty( $new_status ) ? 'NULL' : $DB->quote( $new_status ) ).'
						WHERE aipr_ID =' . $DB->quote( $iprange_ID ) );
		echo '<a href="#" rel="'.$new_status.'" color="'.aipr_status_color( $new_status ).'">'.aipr_status_title( $new_status ).'</a>';
		break;

	case 'emblk_status_edit':
		// Update blocked status of email address

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'emblkstatus' );

		// Check permission:
		$current_User->check_perm( 'emails', 'edit', true );

		$new_status = param( 'new_status', 'string' );
		$emblk_ID = param( 'emblk_ID', 'integer', true );

		load_funcs('tools/model/_email.funcs.php');

		$DB->query( 'UPDATE T_email__blocked
						SET emblk_status = '.( empty( $new_status ) ? 'NULL' : $DB->quote( $new_status ) ).'
						WHERE emblk_ID =' . $DB->quote( $emblk_ID ) );
		echo '<a href="#" rel="'.$new_status.'" color="'.emblk_get_status_color( $new_status ).'">'.emblk_get_status_title( $new_status ).'</a>';
		break;

	case 'user_level_edit':
		// Update level of an user from list screen by clicking on the level column

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'userlevel' );

		// Check permission:
		$current_User->check_perm( 'users', 'edit', true );

		$user_level = param( 'new_user_level', 'integer' );
		$user_ID = param( 'user_ID', 'string' );

		$UserCache = & get_UserCache();
		if( $User = & $UserCache->get_by_ID( $user_ID, false ) )
		{
			$User->set( 'level', $user_level );
			$User->dbupdate();
			echo '<a href="#" rel="'.$user_level.'">'.$user_level.'</a>';
		}
		break;

	default:
		$incorrect_action = true;
		break;
}

if( !$incorrect_action )
{
	if( $current_debug || $current_debug_jslog )
	{	// debug is ON
		$Ajaxlog->display( NULL, NULL, true, 'all',
						array(
								'error' => array( 'class' => 'jslog_error', 'divClass' => false ),
								'note'  => array( 'class' => 'jslog_note',  'divClass' => false ),
							), 'ul', 'jslog' );
	}

	if( $add_response_end_comment )
	{ // add ajax response end comment
		echo '<!-- Ajax response end -->';
	}

	exit(0);
}

/**
 * Get comments awaiting moderation
 *
 * @param integer blog_ID
 */
function get_comments_awaiting_moderation( $blog_ID )
{
	$limit = 5;

	load_funcs( 'dashboard/model/_dashboard.funcs.php' );
	show_comments_awaiting_moderation( $blog_ID, NULL, $limit, array(), false );
}

?>