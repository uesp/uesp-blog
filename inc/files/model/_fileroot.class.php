<?php
/**
 * This file implements the FileRoot class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
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
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id: _fileroot.class.php 4494 2013-08-14 06:18:43Z yura $
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * This class provides info about a File Root.
 *
 * A FileRoot describes a directory available for media file storage, under access permission.
 *
 * @package evocore
 */
class FileRoot
{
	/**
	 * Type: 'user', 'group' or 'collection'.
	 *
	 * Note: group is not implemented yet. Absolute will probably be deprecated.
	 */
	var $type;

	/**
	 * ID of user, group or collection.
	 */
	var $in_type_ID;

	/**
	 * Unique Root ID constructed from type and in_type_ID
	 * @var string
	 */
	var $ID;

	/**
	 * Name of the root
	 */
	var $name;

	/**
	 * Absolute path, ending with slash
	 */
	var $ads_path;

	/**
	 * Absolute URL, ending with slash
	 */
	var $ads_url;


	/**
	 * Constructor
	 *
	 * Will fail if non existent User or Blog is requested.
	 * But specific access permissions on (threfore existence of) this User or Blog should have been tested before anyway.
	 *
	 * @param string Root type: 'user', 'group' or 'collection'
	 * @param integer ID of the user, the group or the collection the file belongs to...
	 * @param boolean Create the directory, if it does not exist yet?
	 */
	function FileRoot( $root_type, $root_in_type_ID, $create = true )
	{
		/**
		 * @var User
		 */
		global $current_User;
		global $Messages;
 		global $Settings, $Debuglog;
 		global $Blog;

		// Store type:
		$this->type = $root_type;
		// Store ID in type:
		$this->in_type_ID = $root_in_type_ID;
		// Generate unique ID:
		$this->ID = FileRoot::gen_ID( $root_type, $root_in_type_ID );

		switch( $root_type )
		{
			case 'user':
				$UserCache = & get_UserCache();
				if( ! $User = & $UserCache->get_by_ID( $root_in_type_ID, false, false ) )
				{	// User not found
					return false;
				}
				$this->name = $User->get( 'login' ); //.' ('. /* TRANS: short for "user" */ T_('u').')';
				$this->ads_path = $User->get_media_dir( $create );
				$this->ads_url = $User->get_media_url();
				return;

			case 'collection':
				$BlogCache = & get_BlogCache();
				if( ! $Blog = & $BlogCache->get_by_ID( $root_in_type_ID, false, false ) )
				{	// Blog not found
					return false;
				}
				$this->name = $Blog->get( 'shortname' ); //.' ('. /* TRANS: short for "blog" */ T_('b').')';
				$this->ads_path = $Blog->get_media_dir( $create );
				$this->ads_url = $Blog->get_media_url();
				return;

			case 'shared':
				// fp> TODO: handle multiple shared directories
				global $media_path, $media_url;
				$rds_shared_subdir = 'shared/global/';
				$ads_shared_dir = $media_path.$rds_shared_subdir;

				if( ! $Settings->get( 'fm_enable_roots_shared' ) )
				{ // Shared dir is disabled:
					$Debuglog->add( 'Attempt to access shared dir, but this feature is globally disabled', 'files' );
				}
				/* Try to create shared directory if it doesn't exist.
				 * Note: mkdir_r() already checks if the dir to create exists.
				 */
				elseif( ! mkdir_r( $ads_shared_dir ) )
				{
					// Only display error on an admin page:
					if( is_admin_page() )
					{
						$Messages->add( sprintf( T_('The directory &laquo;%s&raquo; could not be created.'), $rds_shared_subdir ).get_manual_link('directory_creation_error'), 'error' );
					}
				}
				else
				{
					$this->name = T_('Shared');
					$this->ads_path = $ads_shared_dir;
					if( isset($Blog) )
					{	// (for now) Let's make shared files appear as being part of the currently displayed blog:
						$this->ads_url = $Blog->get_local_media_url().'shared/global/';
					}
					else
					{
						$this->ads_url = $media_url.'shared/global/';
					}
				}
				return;

    	case 'skins':
    		// fp> some stuff here should go out of here... but I don't know where to put it yet. I'll see after the Skin refactoring.
     		if( ! $Settings->get( 'fm_enable_roots_skins' ) )
				{ // Skins root is disabled:
					$Debuglog->add( 'Attempt to access skins dir, but this feature is globally disabled', 'files' );
				}
				elseif( empty( $current_User ) || ( ! $current_User->check_perm( 'templates' ) ) )
				{	// No perm to access templates:
					$Debuglog->add( 'Attempt to access skins dir, but no permission', 'files' );
				}
				else
				{
					global $skins_path, $skins_url;
					$this->name = T_('Skins');
					$this->ads_path = $skins_path;
					if( isset($Blog) )
					{	// (for now) Let's make skin files appear as being part of the currently displayed blog:
						$this->ads_url = $Blog->get_local_skins_url();
					}
					else
					{
						$this->ads_url = $skins_url;
					}
				}
				return;
		}

		debug_die( "Invalid root type" );
	}


	function get_typegroupname()
	{
		switch( $this->type )
		{
			case 'user':
				return NT_('User roots');

			case 'collection':
				return NT_('Blog roots');

			default:
				return NT_('Special roots');
		}
	}

	/**
	 * @static
	 */
	function gen_ID( $root_type, $root_in_type_ID )
	{
		if( !is_number( $root_in_type_ID ) )
		{
			debug_die( "Invalid root type ID" );
		}

		switch( $root_type )
		{
			case 'user':
			case 'shared':
			case 'collection':
			case 'skins':
				return $root_type.'_'.$root_in_type_ID;
		}

		debug_die( "Invalid root type" );
	}

}

?>