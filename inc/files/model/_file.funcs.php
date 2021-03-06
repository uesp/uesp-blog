<?php
/**
 * This file implements various File handling functions.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2013 by Francois Planque - {@link http://fplanque.com/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
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
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER.
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id: _file.funcs.php 4373 2013-07-27 08:39:12Z attila $
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


if( ! function_exists('fnmatch') )
{
	/**
	 * A replacement for fnmatch() which needs PHP 4.3 and a POSIX compliant system (Windows is not).
	 *
	 * @author jk at ricochetsolutions dot com {@link http://php.net/manual/function.fnmatch.php#71725}
	 */
	function fnmatch($pattern, $string)
	{
	   return preg_match( '#^'.strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.')).'$#i', $string);
	}
}


/**
 * Converts bytes to readable bytes/kb/mb/gb, like "12.45mb"
 *
 * @param integer bytes
 * @param boolean use HTML <abbr> tags
 * @return string bytes made readable
 */
function bytesreadable( $bytes, $htmlabbr = true )
{
	static $types = NULL;

	if( empty($bytes) )
	{
		return T_('Empty');
	}

	if( !isset($types) )
	{ // generate once:
		$types = array(
			0 => array( 'abbr' => /* TRANS: Abbr. for "Bytes" */ T_('B.'), 'text' => T_('Bytes') ),
			1 => array( 'abbr' => /* TRANS: Abbr. for "Kilobytes" */ T_('KB'), 'text' => T_('Kilobytes') ),
			2 => array( 'abbr' => /* TRANS: Abbr. for Megabytes */ T_('MB'), 'text' => T_('Megabytes') ),
			3 => array( 'abbr' => /* TRANS: Abbr. for Gigabytes */ T_('GB'), 'text' => T_('Gigabytes') ),
			4 => array( 'abbr' => /* TRANS: Abbr. for Terabytes */ T_('TB'), 'text' => T_('Terabytes') )
		);
	}

	for( $i = 0; $bytes > 1024; $i++ )
	{
		$bytes /= 1024;
	}

	// Format to maximum of 1 digit after .
	$precision = max( 0, ( 1 -floor(log($bytes)/log(10))) );
	$r = sprintf( '%.'.$precision.'f', $bytes );

	$r .= $htmlabbr ? ( '&nbsp;<abbr title="'.$types[$i]['text'].'">' ) : ' ';
	$r .= $types[$i]['abbr'];
	$r .= $htmlabbr ? '</abbr>' : ( ' ('.$types[$i]['text'].')' );

	// $r .= ' '.$precision;

	return $r;
}


/**
 * Get an array of all directories (and optionally files) of a given
 * directory, either flat (one-dimensional array) or multi-dimensional (then
 * dirs are the keys and hold subdirs/files).
 *
 * Note: there is no ending slash on dir names returned.
 *
 * @param string the path to start
 * @param array of params
 * @return false|array false if the first directory could not be accessed,
 *                     array of entries otherwise
 */
function get_filenames( $path, $params = array() )
{
	global $Settings;

	$params = array_merge( array(
			'inc_files'		=> true,	// include files (not only directories)
			'inc_dirs'		=> true,	// include directories (not the directory itself!)
			'flat'			=> true,	// return a one-dimension-array
			'recurse'		=> true,	// recurse into subdirectories
			'basename'		=> false,	// get the basename only
			'trailing_slash'=> false,	// add trailing slash
			'inc_hidden'    => true,	// inlcude hidden files, directories and content
			'inc_evocache'	=> false,	// exclude evocache directories and content
		), $params );

	$r = array();

	$path = trailing_slash( $path );

	if( $dir = @opendir($path) )
	{
		while( ( $file = readdir($dir) ) !== false )
		{
			if( $file == '.' || $file == '..' )
				continue;

			// asimo> Also check if $Settings is not empty because this function is called from the install srcipt too, where $Settings is not initialized yet
			if( ! $params['inc_evocache'] && ! empty( $Settings ) && $file == $Settings->get('evocache_foldername') )
				continue;

			// Check for hidden status...
			if( ( ! $params['inc_hidden'] ) && ( substr( $file, 0, 1 ) == '.' ) )
			{ // Do not load & show hidden files and folders (prefixed with .)
				continue;
			}

			if( is_dir($path.$file) )
			{
				if( $params['flat'] )
				{
					if( $params['inc_dirs'] )
					{
						$directory_name = $params['basename'] ? $file : $path.$file;
						if( $params['trailing_slash'] )
						{
							$directory_name = trailing_slash( $directory_name );
						}

						$r[] = $directory_name;
					}
					if( $params['recurse'] )
					{
						$rSub = get_filenames( $path.$file, $params );
						if( $rSub )
						{
							$r = array_merge( $r, $rSub );
						}
					}
				}
				else
				{
					$r[$file] = get_filenames( $path.$file, $params );
				}
			}
			elseif( $params['inc_files'] )
			{
				$r[] = $params['basename'] ? $file : $path.$file;
			}
		}
		closedir($dir);
	}
	else
	{
		return false;
	}

	return $r;
}


/**
 * Get a list of available admin skins.
 *
 * This checks if there's a _adminUI.class.php in there.
 *
 * @return array  List of directory names that hold admin skins or false, if the admin skins driectory does not exist.
 */
function get_admin_skins()
{
	global $adminskins_path, $admin_subdir, $adminskins_subdir;

	$filename_params = array(
			'inc_files'		=> false,
			'recurse'		=> false,
			'basename'      => true,
		);
	$dirs_in_adminskins_dir = get_filenames( $adminskins_path, $filename_params );

	if( $dirs_in_adminskins_dir === false )
	{
		return false;
	}

	$r = array();
	if( $dirs_in_adminskins_dir )
	{
		foreach( $dirs_in_adminskins_dir as $l_dir )
		{
			if( !file_exists($adminskins_path.$l_dir.'/_adminUI.class.php') )
			{
				continue;
			}
			$r[] = $l_dir;
		}
	}
	return $r;
}


/**
 * Get size of a directory, including anything (especially subdirs) in there.
 *
 * @param string the dir's full path
 */
function get_dirsize_recursive( $path )
{
	$files = get_filenames( $path );
	$total = 0;

	if( !empty( $files ) )
	{
		foreach( $files as $lFile )
		{
			$total += filesize($lFile);
		}
	}

	return $total;
}


/**
 * Deletes a dir recursively, wiping out all subdirectories!!
 *
 * @param string The dir
 * @return boolean False on failure
 */
function rmdir_r( $path )
{
	$path = trailing_slash( $path );

	$r = true;

	if( ! cleardir_r( $path ) )
	{
		$r = false;
	}

	if( ! @rmdir( $path ) )
	{
		$r = false;
	}

	return $r;
}


/**
 * Clear contents of dorectory, but do not delete directory itself
 * @return boolean False on failure (may be only partial), true on success.
 */
function cleardir_r( $path )
{
	$path = trailing_slash( $path );
	// echo "<br>rmdir_r($path)";

	$r = true; // assume success

	if( $dir = @opendir($path) )
	{
		while( ( $file = readdir($dir) ) !== false )
		{
			if( $file == '.' || $file == '..' )
				continue;

			$adfp_filepath = $path.$file;

			// echo "<br> - $os_filepath ";

			if( is_dir( $adfp_filepath ) && ! is_link($adfp_filepath) )
			{ // Note: we do NOT follow symlinks
				// echo 'D';
				if( ! rmdir_r( $adfp_filepath ) )
				{
					$r = false;
				}
			}
			else
			{ // File or symbolic link
				//echo 'F/S';
				if( ! @unlink( $adfp_filepath ) )
				{
					$r = false;
				}
			}
		}
		closedir($dir);
	}
	else
	{
		$r = false;
	}

	return $r;
}

/**
 * Get the size of an image file
 *
 * @param string absolute file path
 * @param string what property/format to get: 'width', 'height', 'widthxheight',
 *               'type', 'string' (as for img tags), 'widthheight_assoc' (array
 *               with keys "width" and "height", else 'widthheight' (numeric array)
 * @return mixed false if no image, otherwise what was requested through $param
 */
function imgsize( $path, $param = 'widthheight' )
{
	/**
	 * Cache image sizes
	 */
	global $cache_imgsize;

	if( isset($cache_imgsize[$path]) )
	{
		$size = $cache_imgsize[$path];
	}
	elseif( !($size = @getimagesize( $path )) )
	{
		return false;
	}
	else
	{
		$cache_imgsize[$path] = $size;
	}

	if( $param == 'width' )
	{
		return $size[0];
	}
	elseif( $param == 'height' )
	{
		return $size[1];
	}
	elseif( $param == 'widthxheight' )
	{
		return $size[0].'x'.$size[1];
	}
	elseif( $param == 'type' )
	{
		switch( $size[1] )
		{
			case 1: return 'gif';
			case 2: return 'jpg';
			case 3: return 'png';
			case 4: return 'swf';
			default: return 'unknown';
		}
	}
	elseif( $param == 'string' )
	{
		return $size[3];
	}
	elseif( $param == 'widthheight_assoc' )
	{
		return array( 'width' => $size[0], 'height' => $size[1] );
	}
	else
	{ // default: 'widthheight'
		return array( $size[0], $size[1] );
	}
}


/**
 * Remove leading slash, if any.
 *
 * @param string
 * @return string
 */
function no_leading_slash( $path )
{
	if( isset($path[0]) && $path[0] == '/' )
	{
		return substr( $path, 1 );
	}
	else
	{
		return $path;
	}
}


/**
 * Returns canonicalized pathname of a directory + ending slash
 *
 * @param string absolute path to be reduced ending with slash
 * @return string absolute reduced path, slash terminated or NULL if the path could not get canonicalized.
 */
function get_canonical_path( $ads_path )
{
	// Remove windows backslashes:
	$ads_path = str_replace( '\\', '/', $ads_path );

	$is_absolute = is_absolute_pathname($ads_path);

	// Make sure there's a trailing slash
	$ads_path = trailing_slash($ads_path);

	while( strpos($ads_path, '//') !== false )
	{
		$ads_path = str_replace( '//', '/', $ads_path );
	}
	while( strpos($ads_path, '/./') !== false )
	{
		$ads_path = str_replace( '/./', '/', $ads_path );
	}
	$parts = explode('/', $ads_path);
	for( $i = 0; $i < count($parts); $i++ )
	{
		if( $parts[$i] != '..' )
		{
			continue;
		}
		if( $i <= 0 || $parts[$i-1] == '' || substr($parts[$i-1], -1) == ':' /* windows drive letter */ )
		{
			return NULL;
		}
		// Remove ".." and the part before it
		unset($parts[$i-1], $parts[$i]);
		// Respin array
		$parts = array_values($parts);
		$i = $i-2;
	}
	$ads_realpath = implode('/', $parts);

	// pre_dump( 'get_canonical_path()', $ads_path, $ads_realpath );

	if( strpos( $ads_realpath, '..' ) !== false )
	{	// Path malformed:
		return NULL;
	}

	if( $is_absolute && ! strlen($ads_realpath) )
	{
		return NULL;
	}

	return $ads_realpath;
}


/**
 * Fix the length of a given file name based on the global $filename_max_length setting.
 *
 * @param string the file name
 * @param string the index before we should remove the over characters
 * @return string the modified filename if the length was above the max length and the $remove_before_index param was correct. The original filename otherwie.
 */
function fix_filename_length( $filename, $remove_before_index )
{
	global $filename_max_length;

	$filename_length = strlen( $filename );
	if( $filename_length > $filename_max_length )
	{
		$difference = $filename_length - $filename_max_length;
		if( $remove_before_index > $difference )
		{ // Fix file name length only if the filename part before the 'remove index' contains more characters then what we have to remove
			$filename = substr_replace( $filename, '', $remove_before_index - $difference, $difference );
		}
	}
	return $filename;
}


/**
 * Process filename:
 *  - convert to lower case
 *  - replace consecutive dots with one dot
 *  - if force_validation is true, then replace every not valid character to '_'
 *  - check if file name is valid
 *
 * @param string file name (by reference) - this file name will be processed
 * @param boolean force validation ( replace not valid characters to '_' without warning )
 * @return error message if the file name is not valid, false otherwise
 */
function process_filename( & $filename, $force_validation = false )
{
	global $filename_max_length;

	if( empty( $filename ) )
	{
		return T_( 'Empty file name is not valid.' );
	}

	if( $force_validation )
	{ // replace every not valid characters
		$filename = preg_replace( '/[^a-z0-9\-_.]+/i', '_', $filename );
		// Make sure the filename length doesn't exceed the maximum allowed. Remove characters from the end of the filename ( before the extension ) if required.
		$extension_pos = strrpos( $filename, '.' );
		$filename = fix_filename_length( $filename, strrpos( $filename, '.', ( $extension_pos ? $extension_pos : strlen( $filename ) ) ) );
	}

	// check if the file name contains consecutive dots, and replace them with one dot without warning ( keep only one dot '.' instead of '...' )
	$filename = preg_replace( '/\.(\.)+/', '.', evo_strtolower( $filename ) );

	if( $error_filename = validate_filename( $filename ) )
	{ // invalid file name
		return $error_filename;
	}

	// on success
	return false;
}


/**
 * Check for valid filename and extension of the filename (no path allowed). (MB)
 *
 * @uses $FiletypeCache, $settings or $force_regexp_filename form _advanced.php
 *
 * @param string filename to test
 * @param mixed true/false to allow locked filetypes. NULL means that FileType will decide
 * @return nothing if the filename is valid according to the regular expression and the extension too, error message if not
 */
function validate_filename( $filename, $allow_locked_filetypes = NULL )
{
	global $Settings, $force_regexp_filename, $filename_max_length;

	if( strpos( $filename, '..' ) !== false )
	{ // consecutive dots are not allowed in file name
		return sprintf( T_('&laquo;%s&raquo; is not a valid filename.').' '.T_( 'Consecutive dots are not allowed.' ), $filename );
	}

	if( strlen( $filename ) > $filename_max_length )
	{ // filename is longer then the maximum allowed
		return sprintf( T_('&laquo;%s&raquo; is not a valid filename.').' '.sprintf( T_( 'Max %d characters are allowed on filenames.' ), $filename_max_length ), $filename );
	}

	// Check filename
	if( $force_regexp_filename )
	{ // Use the regexp from _advanced.php
		if( !preg_match( ':'.str_replace( ':', '\:', $force_regexp_filename ).':', $filename ) )
		{ // Invalid filename
			return sprintf( T_('&laquo;%s&raquo; is not a valid filename.'), $filename );
		}
	}
	else
	{	// Use the regexp from SETTINGS
		if( !preg_match( ':'.str_replace( ':', '\:', $Settings->get( 'regexp_filename' ) ).':', $filename ) )
		{ // Invalid filename
			return sprintf( T_('&laquo;%s&raquo; is not a valid filename.'), $filename );
		}
	}

	// Check extension filename
	if( preg_match( '#\.([a-zA-Z0-9\-_]+)$#', $filename, $match ) )
	{ // Filename has a valid extension
		$FiletypeCache = & get_FiletypeCache();
		if( $Filetype = & $FiletypeCache->get_by_extension( strtolower( $match[1] ) , false ) )
		{
			if( $Filetype->is_allowed( $allow_locked_filetypes ) )
			{ // Filename has an unlocked extension or we allow locked extensions
				return;
			}
			else
			{	// Filename hasn't an allowed extension
				return sprintf( T_('&laquo;%s&raquo; is a locked extension.'), htmlentities($match[1]) );
			}
		}
		else
		{ // Filename hasn't an allowed extension
			return sprintf( T_('&laquo;%s&raquo; has an unrecognized extension.'), $filename );
		}
	}
	else
	{ // Filename hasn't a valid extension
		return sprintf( T_('&laquo;%s&raquo; has not a valid extension.'), $filename );
	}
}


/**
 * Check for valid dirname (no path allowed). ( MB )
 *
 * @uses $Settings or $force_regexp_dirname form _advanced.php
 * @param string dirname to test
 * @return nothing if the dirname is valid according to the regular expression, error message if not
 */
function validate_dirname( $dirname )
{
	global $Settings, $force_regexp_dirname, $filename_max_length;

	if( $dirname != '..' )
	{
		if( strlen( $dirname ) > $filename_max_length )
		{ // Don't allow longer directory names then the max file name length
			return sprintf( T_('&laquo;%s&raquo; is not a valid directory name.'), $dirname ).' '.sprintf( T_( 'Max %d characters are allowed.' ), $filename_max_length );
		}

		if( !empty( $force_regexp_dirname ) )
		{ // Use the regexp from _advanced.php
			if( preg_match( ':'.str_replace( ':', '\:', $force_regexp_dirname ).':', $dirname ) )
			{ // Valid dirname
				return;
			}
		}
		else
		{ // Use the regexp from SETTINGS
			if( preg_match( ':'.str_replace( ':', '\:', $Settings->get( 'regexp_dirname' ) ).':', $dirname ) )
			{ // Valid dirname
				return;
			}
		}
	}

	return sprintf( T_('&laquo;%s&raquo; is not a valid directory name.'), $dirname );
}


/**
 * Check if file rename is acceptable
 *
 * used when renaming a file, File settings
 *
 * @param string the new name
 * @param boolean true if it is a directory, false if not
 * @param string the absolute path of the parent directory
 * @param boolean true if user has permission to all kind of fill types, false otherwise
 * @return mixed NULL if the rename is acceptable, error message if not
 */
function check_rename( & $newname, $is_dir, $dir_path, $allow_locked_filetypes )
{
	global $dirpath_max_length;

	// Check if provided name is okay:
	$newname = trim( strip_tags($newname) );

	if( $is_dir )
	{
		if( $error_dirname = validate_dirname( $newname ) )
		{ // invalid directory name
			return $error_dirname;
		}
		if( $dirpath_max_length < ( strlen( $dir_path ) + strlen( $newname ) ) )
		{ // The new path length would be too long
			return T_('The new name is too long for this folder.');
		}
	}
	elseif( $error_filename = validate_filename( $newname, $allow_locked_filetypes ) )
	{ // Not a file name or not an allowed extension
		return $error_filename;
	}

	return NULL;
}


/**
 * Return a string with upload restrictions ( allowed extensions, max file size )
 */
function get_upload_restriction()
{
	global $DB, $Settings, $current_User;
	$restrictNotes = array();

	if( is_logged_in( false ) )
	{
		$condition = ( $current_User->check_perm( 'files', 'all' ) ) ? '' : 'ftyp_allowed <> "admin"';
	}
	else
	{
		$condition = 'ftyp_allowed = "any"';
	}

	if( !empty( $condition ) )
	{
		$condition = ' WHERE '.$condition;
	}

	// Get list of recognized file types (others are not allowed to get uploaded)
	// dh> because FiletypeCache/DataObjectCache has no interface for getting a list, this dirty query seems less dirty to me.
	$allowed_extensions = $DB->get_col( 'SELECT ftyp_extensions FROM T_filetypes'.$condition );
	$allowed_extensions = implode( ' ', $allowed_extensions ); // implode with space, ftyp_extensions can hold many, separated by space
	// into array:
	$allowed_extensions = preg_split( '~\s+~', $allowed_extensions, -1, PREG_SPLIT_NO_EMPTY );
	// readable:
	$allowed_extensions = implode_with_and($allowed_extensions);

	$restrictNotes[] = '<strong>'.T_('Allowed file extensions').'</strong>: '.$allowed_extensions;

	if( $Settings->get( 'upload_maxkb' ) )
	{ // We want to restrict on file size:
		$restrictNotes[] = '<strong>'.T_('Maximum allowed file size').'</strong>: '.bytesreadable( $Settings->get( 'upload_maxkb' )*1024 );
	}

	return implode( '<br />', $restrictNotes ).'<br />';
}


/**
 * Return the path without the leading {@link $basepath}, or if not
 * below {@link $basepath}, just the basename of it.
 *
 * Do not use this for file handling.  JUST for displaying! (DEBUG MESSAGE added)
 *
 * @param string Path
 * @return string Relative path or even base name.
 *   NOTE: when $debug, the real path gets appended.
 */
function rel_path_to_base( $path )
{
	global $basepath, $debug;

	// Remove basepath prefix:
	if( preg_match( '~^('.preg_quote($basepath, '~').')(.+)$~', $path, $match ) )
	{
		$r = $match[2];
	}
	else
	{
		$r = basename($path).( is_dir($path) ? '/' : '' );
	}

	if( $debug )
	{
		$r .= ' [DEBUG: '.$path.']';
	}

	return $r;
}


/**
 * Get the directories of the supplied path as a radio button tree.
 *
 * @todo fp> Make a DirTree class (those static hacks suck)
 *
 * @param FileRoot A single root or NULL for all available.
 * @param string the root path to use
 * @param boolean add radio buttons ?
 * @param string used by recursion
 * @param string what kind of action do the user ( we need this to check permission )
 * 			fp>asimo : in what case can this be something else than "view" ?
 * 			asimo>fp : on the _file_upload.view, we must show only those roots where current user has permission to add files
 * @return string
 */
function get_directory_tree( $Root = NULL, $ads_full_path = NULL, $ads_selected_full_path = NULL, $radios = false, $rds_rel_path = NULL, $is_recursing = false, $action = 'view' )
{
	static $js_closeClickIDs; // clickopen IDs that should get closed
	static $instance_ID = 0;
	static $fm_highlight;
	global $current_User;

	// A folder might be highlighted (via "Locate this directory!")
	if( ! isset($fm_highlight) )
	{
		$fm_highlight = param('fm_highlight', 'string', '');
	}


	if( ! $is_recursing )
	{	// This is not a recursive call (yet):
		// Init:
		$instance_ID++;
		$js_closeClickIDs = array();
		$ret = '<ul class="clicktree">';
	}
	else
	{
		$ret = '';
	}

	// ________________________ Handle Roots ______________________
	if( $Root === NULL )
	{ // We want to list all roots:
		$FileRootCache = & get_FileRootCache();
		$_roots = $FileRootCache->get_available_FileRoots();

		foreach( $_roots as $l_Root )
		{
			if( ! $current_User->check_perm( 'files', $action, false, $l_Root ) )
			{	// current user does not have permission to "view" (or other $action) this root
				continue;
			}
			$subR = get_directory_tree( $l_Root, $l_Root->ads_path, $ads_selected_full_path, $radios, '', true );
			if( !empty( $subR['string'] ) )
			{
				$ret .= '<li>'.$subR['string'].'</li>';
			}
		}
	}
	else
	{
		// We'll go through files in current dir:
		$Nodelist = new Filelist( $Root, trailing_slash($ads_full_path) );
		check_showparams( $Nodelist );
		$Nodelist->load();
		$Nodelist->sort( 'name' );
		$has_sub_dirs = $Nodelist->count_dirs();

		$id_path = 'id_path_'.$instance_ID.md5( $ads_full_path );

		$r['string'] = '<span class="folder_in_tree"';

		if( $ads_full_path == $ads_selected_full_path )
		{	// This is the current open path
	 		$r['opened'] = true;

	 		if( $fm_highlight && $fm_highlight == substr($rds_rel_path, 0, -1) )
	 		{
	 			$r['string'] .= ' id="fm_highlighted"';
	 			unset($fm_highlight);
	 		}
		}
		else
		{
	 		$r['opened'] = NULL;
		}

		$r['string'] .= '>';

		if( $radios )
		{ // Optional radio input to select this path:
			$root_and_path = format_to_output( implode( '::', array($Root->ID, $rds_rel_path) ), 'formvalue' );

			$r['string'] .= '<input type="radio" name="root_and_path" value="'.$root_and_path.'" id="radio_'.$id_path.'"';

			if( $r['opened'] )
			{	// This is the current open path
				$r['string'] .= ' checked="checked"';
			}

			//.( ! $has_sub_dirs ? ' style="margin-right:'.get_icon( 'collapse', 'size', array( 'size' => 'width' ) ).'px"' : '' )
			$r['string'] .= ' /> &nbsp; &nbsp;';
		}

		// Folder Icon + Name:
		$url = regenerate_url( 'root,path', 'root='.$Root->ID.'&amp;path='.$rds_rel_path );
		$label = action_icon( T_('Open this directory in the file manager'), 'folder', $url )
			.'<a href="'.$url.'"
			title="'.T_('Open this directory in the file manager').'">'
			.( empty($rds_rel_path) ? $Root->name : basename( $ads_full_path ) )
			.'</a>';

		// Handle potential subdir:
		if( ! $has_sub_dirs )
		{	// No subdirs
			$r['string'] .= get_icon( 'expand', 'noimg', array( 'class'=>'' ) ).'&nbsp;'.$label.'</span>';
		}
		else
		{ // Process subdirs
			$r['string'] .= get_icon( 'collapse', 'imgtag', array( 'onclick' => 'toggle_clickopen(\''.$id_path.'\');',
						'id' => 'clickimg_'.$id_path,
						'style'=>'margin:0 2px'
					) )
				.'&nbsp;'.$label.'</span>'
				.'<ul class="clicktree" id="clickdiv_'.$id_path.'">'."\n";

			while( $l_File = & $Nodelist->get_next( 'dir' ) )
			{
				$rSub = get_directory_tree( $Root, $l_File->get_full_path(), $ads_selected_full_path, $radios, $l_File->get_rdfs_rel_path(), true );

				if( $rSub['opened'] )
				{ // pass opened status on, if given
					$r['opened'] = $rSub['opened'];
				}

				$r['string'] .= '<li>'.$rSub['string'].'</li>';
			}

			if( !$r['opened'] )
			{
				$js_closeClickIDs[] = $id_path;
			}
			$r['string'] .= '</ul>';
		}

   	if( $is_recursing )
		{
			return $r;
		}
		else
		{
			$ret .= '<li>'.$r['string'].'</li>';
		}
	}

	if( ! $is_recursing )
	{
 		$ret .= '</ul>';

		if( ! empty($js_closeClickIDs) )
		{ // there are IDs of checkboxes that we want to close
			$ret .= "\n".'<script type="text/javascript">toggle_clickopen( \''
						.implode( "' );\ntoggle_clickopen( '", $js_closeClickIDs )
						."' );\n</script>";
		}
	}

	return $ret;
}


/**
 * Create a directory recursively.
 *
 * @todo dh> simpletests for this (especially for open_basedir)
 *
 * @param string directory name
 * @param integer permissions
 * @return boolean
 */
function mkdir_r( $dirName, $chmod = NULL )
{
	return evo_mkdir( $dirName, $chmod, true );
}


/**
 * Create a directory
 *
 * @param string Directory path
 * @param integer Permissions
 * @param boolean Create a dir recursively
 * @return boolean TRUE on success
 */
function evo_mkdir( $dir_path, $chmod = NULL, $recursive = false )
{
	if( is_dir( $dir_path ) )
	{ // already exists:
		return true;
	}

	if( mkdir( $dir_path, 0777, $recursive ) )
	{ // Directory is created succesfully
		if( $chmod === NULL )
		{ // Get default permissions
			global $Settings;
			$chmod = $Settings->get( 'fm_default_chmod_dir' );
		}

		if( ! empty( $chmod ) )
		{ // Set the dir rights by chmod() function because mkdir() doesn't provide this operation correctly
			chmod( $dir_path, is_string( $chmod ) ? octdec( $chmod ) : $chmod );
		}

		return true;
	}

	return false;
}


/**
 * Is the given path absolute (non-relative)?
 *
 * @return boolean
 */
function is_absolute_pathname($path)
{
	$pathlen = strlen($path);
	if( ! $pathlen )
	{
		return false;
	}

	if( is_windows() )
	{ // windows e-g: (note: "XY:" can actually happen as a drive ID in windows; I have seen it once in 2009 on MY XP sp3 after plugin in & plugin out an USB stick like 26 times over 26 days! (with sleep/hibernate in between)
		return ( $pathlen > 1 && $path[1] == ':' );
	}
	else
	{ // unix
		return ( $path[0] == '/' );
	}
}


/**
 * Define sys_get_temp_dir, if not available (PHP 5 >= 5.2.1)
 * @link http://us2.php.net/manual/en/function.sys-get-temp-dir.php#93390
 * @return string NULL on failure
 */
if ( !function_exists('sys_get_temp_dir'))
{
  function sys_get_temp_dir()
	{
    if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); }
    if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); }
    if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); }
    $tempfile=tempnam(__FILE__,'');
    if (file_exists($tempfile))
		{
      unlink($tempfile);
      return realpath(dirname($tempfile));
    }
    return null;
  }
}


/**
 * Controller helper
 */
function file_controller_build_tabs()
{
	global $AdminUI, $current_User, $blog;

	$AdminUI->add_menu_entries(
			'files',
			array(
					'browse' => array(
						'text' => T_('Browse'),
						'href' => regenerate_url( 'ctrl', 'ctrl=files' ) ),
					)
				);

	if( $current_User->check_perm( 'files', 'add', false, $blog ? $blog : NULL ) )
	{ // Permission to upload: (no subtabs needed otherwise)
		$AdminUI->add_menu_entries(
				'files',
				array(
						'upload' => array(
							'text' => /* TRANS: verb */ T_('Upload '),
							'href' => regenerate_url( 'ctrl', 'ctrl=upload' ) ),
					)
			);

		$AdminUI->add_menu_entries(
			array('files', 'upload'),
			array(
					'quick' => array(
						'text' => /* TRANS: Quick upload method */ T_('Quick '),
						'href' => '?ctrl=upload&amp;tab3=quick',
						'onclick' => 'return b2edit_reload( document.getElementById( \'fm_upload_checkchanges\' ), \'?ctrl=upload&amp;tab3=quick\' );' ),
					'standard' => array(
						'text' => /* TRANS: Standard upload method */ T_('Standard '),
						'href' => '?ctrl=upload&amp;tab3=standard',
						'onclick' => 'return b2edit_reload( document.getElementById( \'fm_upload_checkchanges\' ), \'?ctrl=upload&amp;tab3=standard\' );' ),
					'advanced' => array(
						'text' => /* TRANS: Advanced upload method */ T_('Advanced '),
						'href' => '?ctrl=upload&amp;tab3=advanced',
						'onclick' => 'return b2edit_reload( document.getElementById( \'fm_upload_checkchanges\' ), \'?ctrl=upload&amp;tab3=advanced\' );' ),
				)
			);
	}

	if( $current_User->check_perm( 'options', 'view' ) )
	{	// Permission to view settings:
		$AdminUI->add_menu_entries(
			'files',
			array(
				'settings' => array(
					'text' => T_('Settings'),
					'href' => '?ctrl=fileset',
					)
				)
			);

		$AdminUI->add_menu_entries(
			array('files', 'settings'),
			array(
					'settings' => array(
						'text' => T_('Settings'),
						'href' => '?ctrl=fileset' ),
					'filetypes' => array(
						'text' => T_('File types'),
						'href' => '?ctrl=filetypes' ),
				)
			);
	}

	if( $current_User->check_perm( 'options', 'edit' ) )
	{	// Permission to edit settings:
		$AdminUI->add_menu_entries(
			'files',
			array(
				'moderation' => array(
					'text' => T_('Moderation'),
					'href' => '?ctrl=filemod',
					'entries' => array(
						'suspicious' => array(
							'text' => T_('Suspicious'),
							'href' => '?ctrl=filemod&amp;tab=suspicious' ),
						'duplicates' => array(
							'text' => T_('Duplicates'),
							'href' => '?ctrl=filemod&amp;tab=duplicates' ),
						)
					)
				)
			);
	}

}


/**
 * Rename evocache folders after File settings update, whe evocahe folder name was chaned
 *
 * @param string old evocache folder name
 * @param string new evocache folder name
 * @return bool true on success
 */
function rename_cachefolders( $oldname, $newname )
{
	$FileRootCache = & get_FileRootCache();

	$available_Roots = $FileRootCache->get_available_FileRoots();

	$slash_oldname = '/'.$oldname;

	$result = true;
	foreach( $available_Roots as $fileRoot )
	{
		$filename_params = array(
				'inc_files'		=> false,
				'inc_evocache'	=> true,
			);
		$dirpaths = get_filenames( $fileRoot->ads_path, $filename_params );

		foreach( $dirpaths as $dirpath )
		{ // search ?evocache folders
			$dirpath_length = strlen( $dirpath );
			if( $dirpath_length < 10 )
			{ // The path is to short, can not contains ?evocache folder name
				continue;
			}
			// searching at the end of the path -> '/' character + ?evocache, length = 1 + 9
			$path_end = substr( $dirpath, $dirpath_length - 10 );
			if( $path_end == $slash_oldname )
			{ // this is a ?evocache folder
				$new_dirpath = substr_replace( $dirpath, $newname, $dirpath_length - 9 );
				// result is true only if all rename call return true (success)
				$result = $result && @rename( $dirpath, $new_dirpath );
			}
		}
	}
	return $result;
}


/**
 * Delete any ?evocache folders.
 *
 * @param Log Pass a Log object here to have error messages added to it.
 * @return integer Number of deleted dirs.
 */
function delete_cachefolders( $Log = NULL )
{
	global $media_path, $Settings;

	// Get this, just in case someone comes up with a different naming:
	$evocache_foldername = $Settings->get( 'evocache_foldername' );

	$filename_params = array(
			'inc_files'		=> false,
			'inc_evocache'	=> true,
		);
	$dirs = get_filenames( $media_path, $filename_params );

	$deleted_dirs = 0;
	foreach( $dirs as $dir )
	{
		$basename = basename($dir);
		if( $basename == '.evocache' || $basename == '_evocache' || $basename == $evocache_foldername )
		{	// Delete .evocache directory recursively
			if( rmdir_r( $dir ) )
			{
				$deleted_dirs++;
			}
			elseif( $Log )
			{
				$Log->add( sprintf( T_('Could not delete directory: %s'), $dir ), 'error' );
			}
		}
	}
	return $deleted_dirs;
}


/**
 * Check and set the given FileList object fm_showhidden and fm_showevocache params
 */
function check_showparams( & $Filelist )
{
	global $UserSettings;

	if( $UserSettings->param_Request( 'fm_showhidden', 'fm_showhidden', 'integer', 0 ) )
	{
		$Filelist->_show_hidden_files = true;
	}

	if( $UserSettings->param_Request( 'fm_showevocache', 'fm_showevocache', 'integer', 0 ) )
	{
		$Filelist->_show_evocache = true;
	}
}


/**
 * Process file uploads (this can process multiple file uploads at once)
 *
 * @param string FileRoot id string
 * @param string the upload dir relative path in the FileRoot
 * @param boolean Shall we create path dirs if they do not exist?
 * @param boolean Shall we check files add permission for current_User?
 * @param boolean upload quick mode
 * @param boolean show warnings if filename is not valid
 * @param integer minimum size for pictures in pixels (width and height)
 * @return mixed NULL if upload was impossible to complete for some reason (wrong fileroot ID, insufficient user permission, etc.)
 * 				       array, which contains uploadedFiles, failedFiles, renamedFiles and renamedMessages
 */
function process_upload( $root_ID, $path, $create_path_dirs = false, $check_perms = true, $upload_quickmode = true, $warn_invalid_filenames = true, $min_size = 0 )
{
	global $Settings, $Plugins, $Messages, $current_User, $force_upload_forbiddenext;

	if( empty($_FILES) )
	{	// We have NO uploaded files to process...
		return NULL;
	}

	/**
	 * Remember failed files (and the error messages)
	 * @var array
	 */
	$failedFiles = array();
	/**
	 * Remember uploaded files
	 * @var array
	 */
	$uploadedFiles = array();
	/**
	 * Remember renamed files
	 * @var array
	 */
	$renamedFiles = array();
	/**
	 * Remember renamed Messages
	 * @var array
	 */
	$renamedMessages = array();

	$FileRootCache = & get_FileRootCache();
	$fm_FileRoot = & $FileRootCache->get_by_ID($root_ID, true);
	if( !$fm_FileRoot )
	{ // fileRoot not found:
		return NULL;
	}

	if( $check_perms && ( !isset( $current_User ) || $current_User->check_perm( 'files', 'add', false, $fm_FileRoot ) ) )
	{ // Permission check required but current User has no permission to upload:
		return NULL;
	}

	// Let's get into requested list dir...
	$non_canonical_list_path = $fm_FileRoot->ads_path.$path;
	// Dereference any /../ just to make sure, and CHECK if directory exists:
	$ads_list_path = get_canonical_path( $non_canonical_list_path );

	// check if the upload dir exists
	if( !is_dir( $ads_list_path ) )
	{
		if( $create_path_dirs )
		{ // Create path
			mkdir_r( $ads_list_path );
		}
		else
		{ // This case should not happen! If it happens then there is a bug in the code where this function was called!
			return NULL;
		}
	}

	// Get param arrays for all uploaded files:
	$uploadfile_title = param( 'uploadfile_title', 'array/string', array() );
	$uploadfile_alt = param( 'uploadfile_alt', 'array/string', array() );
	$uploadfile_desc = param( 'uploadfile_desc', 'array/string', array() );
	$uploadfile_name = param( 'uploadfile_name', 'array/string', array() );

	// LOOP THROUGH ALL UPLOADED FILES AND PROCCESS EACH ONE:
	foreach( $_FILES['uploadfile']['name'] as $lKey => $lName )
	{
		if( empty( $lName ) )
		{ // No file name:
			if( $upload_quickmode
				 || !empty( $uploadfile_title[$lKey] )
				 || !empty( $uploadfile_alt[$lKey] )
				 || !empty( $uploadfile_desc[$lKey] )
				 || !empty( $uploadfile_name[$lKey] ) )
			{ // User specified params but NO file! Warn the user:
				$failedFiles[$lKey] = T_( 'Please select a local file to upload.' );
			}
			// Abort upload for this file:
			continue;
		}

		if( $Settings->get( 'upload_maxkb' )
				&& $_FILES['uploadfile']['size'][$lKey] > $Settings->get( 'upload_maxkb' )*1024 )
		{ // File is larger than allowed in settings:
			$failedFiles[$lKey] = sprintf(
					T_('The file is too large: %s but the maximum allowed is %s.'),
					bytesreadable( $_FILES['uploadfile']['size'][$lKey] ),
					bytesreadable($Settings->get( 'upload_maxkb' )*1024) );
			// Abort upload for this file:
			continue;
		}

		if( !empty( $min_size ) )
		{	// Check pictures for small sizes
			$image_sizes = imgsize( $_FILES['uploadfile']['tmp_name'][$lKey], 'widthheight' );
			if( $image_sizes[0] < $min_size || $image_sizes[1] < $min_size )
			{	// Abort upload for this file:
				$failedFiles[$lKey] = sprintf(
					T_( 'Your profile picture must have a minimum size of %dx%d pixels.' ),
					$min_size,
					$min_size );
				continue;
			}
		}

		if( $_FILES['uploadfile']['error'][$lKey] )
		{ // PHP itself has detected an error!:
			switch( $_FILES['uploadfile']['error'][$lKey] )
			{
				case UPLOAD_ERR_FORM_SIZE:
					// The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.
					// This can easily be edited by the user/hacker, so we do not use it.. file size gets checked for real just above.
					break;

				case UPLOAD_ERR_INI_SIZE:
					// File is larger than allowed in php.ini:
					$failedFiles[$lKey] = 'The file exceeds the upload_max_filesize directive in php.ini.'; // Configuration error, no translation
					// Abort upload for this file:
					continue;

				case UPLOAD_ERR_PARTIAL:
					$failedFiles[$lKey] = T_('The file was only partially uploaded.');
					// Abort upload for this file:
					continue;

				case UPLOAD_ERR_NO_FILE:
					// Is probably the same as empty($lName) before.
					$failedFiles[$lKey] = T_('No file was uploaded.');
					// Abort upload for this file:
					continue;

				case 6: // numerical value of UPLOAD_ERR_NO_TMP_DIR
				# (min_php: 4.3.10, 5.0.3) case UPLOAD_ERR_NO_TMP_DIR:
					// Missing a temporary folder.
					$failedFiles[$lKey] = 'Temporary upload dir is missing! (upload_tmp_dir in php.ini)'; // Configuration error, no translation
					// Abort upload for this file:
					continue;

				default:
					$failedFiles[$lKey] = T_('An unknown error has occurred!').' Error code #'.$_FILES['uploadfile']['error'][$lKey];
					// Abort upload for this file:
					continue;
			}
		}

		if( ! isset($_FILES['uploadfile']['_evo_fetched_url'][$lKey]) // skip check for fetched URLs
			&& ! is_uploaded_file( $_FILES['uploadfile']['tmp_name'][$lKey] ) )
		{ // Ensure that a malicious user hasn't tried to trick the script into working on files upon which it should not be working.
			$failedFiles[$lKey] = T_('The file does not seem to be a valid upload! It may exceed the upload_max_filesize directive in php.ini.');
			// Abort upload for this file:
			continue;
		}

		// Use new name on server if specified:
		$newName = !empty( $uploadfile_name[ $lKey ] ) ? $uploadfile_name[ $lKey ] : $lName;
		// validate file name
		if( $error_filename = process_filename( $newName, !$warn_invalid_filenames ) )
		{ // Not a valid file name or not an allowed extension:
			$failedFiles[$lKey] = $error_filename;
			// Abort upload for this file:
			continue;
		}

		// Check if the uploaded file type is an image, and if is an image then try to fix the file extension based on mime type
		// If the mime type is a known mime type and user has right to upload files with this kind of file type,
		// this part of code will check if the file extension is the same as admin defined for this file type, and will fix it if it isn't the same
		// Note: it will also change the jpeg extensions to jpg.
		$uploadfile_path = $_FILES['uploadfile']['tmp_name'][$lKey];
		// this image_info variable will be used again to get file thumb
		$image_info = getimagesize($uploadfile_path);
		if( $image_info )
		{ // This is an image, validate mimetype vs. extension:
			$image_mimetype = $image_info['mime'];
			$FiletypeCache = & get_FiletypeCache();
			// Get correct file type based on mime type
			$correct_Filetype = $FiletypeCache->get_by_mimetype( $image_mimetype, false, false );

			// Check if file type is known by us, and if it is allowed for upload.
			// If we don't know this file type or if it isn't allowed we don't change the extension! The current extension is allowed for sure.
			if( $correct_Filetype && $correct_Filetype->is_allowed() )
			{ // A FileType with the given mime type exists in database and it is an allowed file type for current User
				// The "correct" extension is a plausible one, proceed...
				$correct_extension = array_shift($correct_Filetype->get_extensions());
				$path_info = pathinfo($newName);
				$current_extension = $path_info['extension'];

				// change file extension to the correct extension, but only if the correct extension is not restricted, this is an extra security check!
				if( strtolower($current_extension) != strtolower($correct_extension) && ( !in_array( $correct_extension, $force_upload_forbiddenext ) ) )
				{ // change the file extension to the correct extension
					$old_name = $newName;
					$newName = $path_info['filename'].'.'.$correct_extension;
					$Messages->add( sprintf(T_('The extension of the file &laquo;%s&raquo; has been corrected. The new filename is &laquo;%s&raquo;.'), $old_name, $newName), 'warning' );
				}
			}
		}

		// Get File object for requested target location:
		$oldName = strtolower( $newName );
		list( $newFile, $oldFile_thumb ) = check_file_exists( $fm_FileRoot, $path, $newName, $image_info );
		$newName = $newFile->get( 'name' );

		// Trigger plugin event
		if( $Plugins->trigger_event_first_false( 'AfterFileUpload', array(
					'File' => & $newFile,
					'name' => & $_FILES['uploadfile']['name'][$lKey],
					'type' => & $_FILES['uploadfile']['type'][$lKey],
					'tmp_name' => & $_FILES['uploadfile']['tmp_name'][$lKey],
					'size' => & $_FILES['uploadfile']['size'][$lKey],
				) ) )
		{
			// Plugin returned 'false'.
			// Abort upload for this file:
			continue;
		}

		// Attempt to move the uploaded file to the requested target location:
		if( isset($_FILES['uploadfile']['_evo_fetched_url'][$lKey]) )
		{ // fetched remotely
			if( ! rename( $_FILES['uploadfile']['tmp_name'][$lKey], $newFile->get_full_path() ) )
			{
				$failedFiles[$lKey] = T_('An unknown error occurred when moving the uploaded file on the server.');
				// Abort upload for this file:
				continue;
			}
		}
		elseif( ! move_uploaded_file( $_FILES['uploadfile']['tmp_name'][$lKey], $newFile->get_full_path() ) )
		{
			$failedFiles[$lKey] = T_('An unknown error occurred when moving the uploaded file on the server.');
			// Abort upload for this file:
			continue;
		}

		// change to default chmod settings
		if( $newFile->chmod( NULL ) === false )
		{ // add a note, this is no error!
			$Messages->add( sprintf( T_('Could not change permissions of &laquo;%s&raquo; to default chmod setting.'), $newFile->dget('name') ), 'note' );
		}

		// Refreshes file properties (type, size, perms...)
		$newFile->load_properties();

		if( ! empty( $oldFile_thumb ) )
		{ // The file name was changed!
			if( $image_info )
			{
				$newFile_thumb = $newFile->get_preview_thumb( 'fulltype' );
			}
			else
			{
				$newFile_thumb = $newFile->get_size_formatted();
			}
			//$newFile_size = bytesreadable ($_FILES['uploadfile']['size'][$lKey]);
			$renamedMessages[$lKey]['message'] = sprintf( T_('"%s was renamed to %s. Would you like to replace %s with the new version instead?'),
														 '&laquo;'.$oldName.'&raquo;', '&laquo;'.$newName.'&raquo;', '&laquo;'.$oldName.'&raquo;' );
			$renamedMessages[$lKey]['oldThumb'] = $oldFile_thumb;
			$renamedMessages[$lKey]['newThumb'] = $newFile_thumb;
			$renamedFiles[$lKey]['oldName'] = $oldName;
			$renamedFiles[$lKey]['newName'] = $newName;
		}

		// Store extra info about the file into File Object:
		if( isset( $uploadfile_title[$lKey] ) )
		{ // If a title text has been passed... (does not happen in quick upload mode)
			$newFile->set( 'title', trim( strip_tags($uploadfile_title[$lKey])) );
		}
		if( isset( $uploadfile_alt[$lKey] ) )
		{ // If an alt text has been passed... (does not happen in quick upload mode)
			$newFile->set( 'alt', trim( strip_tags($uploadfile_alt[$lKey])) );
		}
		if( isset( $uploadfile_desc[$lKey] ) )
		{ // If a desc text has been passed... (does not happen in quick upload mode)
			$newFile->set( 'desc', trim( strip_tags($uploadfile_desc[$lKey])) );
		}

		// Store File object into DB:
		$newFile->dbsave();
		$uploadedFiles[] = $newFile;
	}

	prepare_uploaded_files( $uploadedFiles );

	return array( 'uploadedFiles' => $uploadedFiles, 'failedFiles' => $failedFiles, 'renamedFiles' => $renamedFiles, 'renamedMessages' => $renamedMessages );
}


/**
 * Prepare the uploaded files
 *
 * @param array Uploaded files
 */
function prepare_uploaded_files( $uploadedFiles )
{
	if( count( $uploadedFiles ) == 0 )
	{	// No uploaded files
		return;
	}

	foreach( $uploadedFiles as $File )
	{
		$Filetype = & $File->get_Filetype();
		if( $Filetype )
		{
			if( in_array( $Filetype->mimetype, array( 'image/jpeg', 'image/gif', 'image/png' ) ) )
			{	// Image file
				prepare_uploaded_image( $File, $Filetype->mimetype );
			}
		}
	}
}


/**
 * Prepare image file (Resize, Rotate and etc.)
 *
 * @param object File
 * @param string mimetype
 */
function prepare_uploaded_image( $File, $mimetype )
{
	global $Settings;

	$thumb_width = $Settings->get( 'fm_resize_width' );
	$thumb_height = $Settings->get( 'fm_resize_height' );
	$thumb_quality = $Settings->get( 'fm_resize_quality' );

	$do_resize = false;
	if( $Settings->get( 'fm_resize_enable' ) &&
	    $thumb_width > 0 && $thumb_height > 0 )
	{	// Image resizing is enabled
		list( $image_width, $image_height ) = explode( 'x', $File->get_image_size() );
		if( $image_width > $thumb_width || $image_height > $thumb_height )
		{	// This image should be resized
			$do_resize = true;
		}
	}

	load_funcs( 'files/model/_image.funcs.php' );

	$resized_imh = null;
	if( $do_resize )
	{	// Resize image
		list( $err, $src_imh ) = load_image( $File->get_full_path(), $mimetype );
		if( empty( $err ) )
		{
			list( $err, $resized_imh ) = generate_thumb( $src_imh, 'fit', $thumb_width, $thumb_height );
		}
	}

	if( !empty( $err ) )
	{	// Error exists, Exit here
		return;
	}

	if( $mimetype == 'image/jpeg' )
	{	// JPEG, do autorotate if EXIF Orientation tag is defined
		$save_image = !$do_resize; // If image was be resized, we should save file only in the end of this function
		exif_orientation( $File->get_full_path(), $resized_imh, $save_image );
	}

	if( !$resized_imh )
	{	// Image resource is incorrect
		return;
	}

	if( $do_resize && empty( $err ) )
	{	// Save resized image ( and also rotated image if this operation was done )
		save_image( $resized_imh, $File->get_full_path(), $mimetype, $thumb_quality );
	}
}


/**
 * Rotate the JPEG image if EXIF Orientation tag is defined
 *
 * @param string File name (with full path)
 * @param resource Image resource ( result of the function imagecreatefromjpeg() ) (by reference)
 * @param boolean TRUE - to save the rotated image in the end of this function
 */
function exif_orientation( $file_name, & $imh/* = null*/, $save_image = false )
{
	global $Settings;

	if( !$Settings->get( 'exif_orientation' ) )
	{	// Autorotate is disabled
		return;
	}

	if( ! function_exists('exif_read_data') )
	{	// EXIF extension is not loaded
		return;
	}

	$EXIF = exif_read_data( $file_name );
	if( !( isset( $EXIF['Orientation'] ) && in_array( $EXIF['Orientation'], array( 3, 6, 8 ) ) ) )
	{	// EXIF Orientation tag is not defined OR we don't interested in current value
		return;
	}

	load_funcs( 'files/model/_image.funcs.php' );

	if( is_null( $imh ) )
	{	// Create image resource from file name
		$imh = imagecreatefromjpeg( $file_name );
	}

	if( !$imh )
	{	// Image resource is incorrect
		return;
	}

	switch( $EXIF['Orientation'] )
	{
		case 3:	// Rotate for 180 degrees
			$imh = @imagerotate( $imh, 180, 0 );
			break;

		case 6:	// Rotate for 90 degrees to the right
			$imh = @imagerotate( $imh, 270, 0 );
			break;

		case 8:	// Rotate for 90 degrees to the left
			$imh = @imagerotate( $imh, 90, 0 );
			break;
	}

	if( !$imh )
	{	// Image resource is incorrect
		return;
	}

	if( $save_image )
	{	// Save rotated image
		save_image( $imh, $file_name, 'image/jpeg' );
	}
}


/**
 * Check if file exists in the target location with the given name. Used during file upload.
 *
 * @param FileRoot target file Root
 * @param string target path
 * @param string file name
 * @param array the new file image_info
 * @return array contains two elements
 * 			first elements is a new File object
 * 			second element is the existing file thumb, or empty string, if the file doesn't exists
 */
function check_file_exists( $fm_FileRoot, $path, $newName, $image_info = NULL )
{
	global $filename_max_length;

	// Get File object for requested target location:
	$FileCache = & get_FileCache();
	$newFile = & $FileCache->get_by_root_and_path( $fm_FileRoot->type, $fm_FileRoot->in_type_ID, trailing_slash($path).$newName, true );

	$num_ext = 0;
	$oldName = $newName;

	$oldFile_thumb = "";
	while( $newFile->exists() )
	{ // The file already exists in the target location!
		$num_ext++;
		$ext_pos = strrpos( $newName, '.');
		if( $num_ext == 1 )
		{
			if( $image_info == NULL )
			{
				$image_info = getimagesize( $newFile->get_full_path() );
			}
			$newName = substr_replace( $newName, '-'.$num_ext.'.', $ext_pos, 1 );
			if( $image_info )
			{
				$oldFile_thumb = $newFile->get_preview_thumb( 'fulltype' );
			}
			else
			{
				$oldFile_thumb = $newFile->get_size_formatted();
			}
		}
		else
		{
			$replace_length = strlen( '-'.($num_ext-1) );
			$newName = substr_replace( $newName, '-'.$num_ext, $ext_pos-$replace_length, $replace_length );
		}
		if( strlen( $newName ) > $filename_max_length )
		{
			$newName = fix_filename_length( $newName, strrpos( $newName, '-' ) );
			if( $error_filename = process_filename( $newName, true ) )
			{ // The file name is still not valid
				debug_die( 'Invalid file name has found during file exists check: '.$error_filename );
			}
		}
		$newFile = & $FileCache->get_by_root_and_path( $fm_FileRoot->type, $fm_FileRoot->in_type_ID, trailing_slash($path).$newName, true );
	}

	return array( $newFile, $oldFile_thumb );
}


/**
 * Remove files with the given ids
 *
 * @param array file ids to remove, default to remove all orphan file IDs
 * @param integer remove files older than the given hour, default NULL will remove all
 * @return integer the number of removed files
 */
function remove_orphan_files( $file_ids = NULL, $older_then = NULL )
{
	global $DB, $localtimenow;
	// asimo> This SQL query should use file class delete_restrictions array (currently T_links and T_users is explicitly used)
	// select orphan comment attachment file ids
	$sql = 'SELECT file_ID FROM T_files
				WHERE ( file_path LIKE "comments/p%" OR file_path LIKE "anonymous_comments/p%" ) AND file_ID NOT IN (
					SELECT * FROM (
						( SELECT DISTINCT link_file_ID FROM T_links
							WHERE link_file_ID IS NOT NULL ) UNION
						( SELECT DISTINCT user_avatar_file_ID FROM T_users
							WHERE user_avatar_file_ID IS NOT NULL ) ) AS linked_files )';

	if( $file_ids != NULL )
	{ // remove only from the given files
		$sql .= ' AND file_ID IN ( '.implode( ',', $file_ids ).' )';
	}

	$result = $DB->get_col( $sql );
	$FileCache = & get_FileCache();
	$FileCache->load_list( $result );
	$count = 0;
	foreach( $result as $file_ID )
	{
		$File = $FileCache->get_by_ID( $file_ID, false, false );
		if( $older_then != NULL )
		{ // we have to check if the File is older then the given value
			$datediff = $localtimenow - filemtime( $File->_adfp_full_path );
			if( $datediff > $older_then * 3600 ) // convert hours to seconds
			{ // not older
				continue;
			}
		}
		// delete the file
		if( $File->unlink() )
		{
			$count++;
		}
	}
	// Clear FileCache to save memory
	$FileCache->clear();

	return $count;
}


/**
 * Get available icons for file types
 *
 * @return array 'key'=>'name'
 */
function get_available_filetype_icons()
{
	$icons = array(
		''               => T_('Unknown'),
		'file_empty'     => T_('Empty'),
		'file_image'     => T_('Image'),
		'file_document'  => T_('Document'),
		'file_www'       => T_('Web file'),
		'file_log'       => T_('Log file'),
		'file_sound'     => T_('Audio file'),
		'file_video'     => T_('Video file'),
		'file_message'   => T_('Message'),
		'file_pdf'       => T_('PDF'),
		'file_php'       => T_('PHP script'),
		'file_encrypted' => T_('Encrypted file'),
		'file_zip'       => T_('Zip archive'),
		'file_tar'       => T_('Tar archive'),
		'file_tgz'       => T_('Tgz archive'),
		'file_pk'        => T_('Archive'),
		'file_doc'       => T_('Microsoft Word'),
		'file_xls'       => T_('Microsoft Excel'),
		'file_ppt'       => T_('Microsoft PowerPoint'),
		'file_pps'       => T_('Microsoft PowerPoint Slideshow'),
	);

	return $icons;
}


/**
 * Save a vote for the file by user
 *
 * @param string File ID
 * @param integer User ID
 * @param string Action of the voting ( 'like', 'noopinion', 'dontlike', 'inappropriate', 'spam' )
 * @param integer 1 = checked, 0 = unchecked (for checkboxes: 'Inappropriate' & 'Spam' )
 */
function file_vote( $file_ID, $user_ID, $vote_action, $checked = 1 )
{
	global $DB;

	// Set modified field name and value
	switch( $vote_action )
	{
		case 'like':
			$field_name = 'fvot_like';
			$field_value = '1';
			break;

		case 'noopinion':
			$field_name = 'fvot_like';
			$field_value = '0';
			break;

		case 'dontlike':
			$field_name = 'fvot_like';
			$field_value = '-1';
			break;

		case 'inappropriate':
			$field_name = 'fvot_inappropriate';
			$field_value = $checked;
			break;

		case 'spam':
			$field_name = 'fvot_spam';
			$field_value = $checked;
			break;

		default:
			// invalid vote action
			return;
	}

	$DB->begin();

	$SQL = new SQL();
	$SQL->SELECT( 'fvot_file_ID' );
	$SQL->FROM( 'T_files__vote' );
	$SQL->WHERE( 'fvot_file_ID = '.$DB->quote( $file_ID ) );
	$SQL->WHERE_and( 'fvot_user_ID = '.$DB->quote( $user_ID ) );
	$vote = $DB->get_row( $SQL->get() );

	// Save a voting results in DB
	if( empty( $vote ) )
	{	// User replace into to avoid duplicate key conflict in case when user clicks two times fast one after the other
		$result = $DB->query( 'REPLACE INTO T_files__vote ( fvot_file_ID, fvot_user_ID, '.$field_name.' )
						VALUES ( '.$DB->quote( $file_ID ).', '.$DB->quote( $user_ID ).', '.$DB->quote( $field_value ).' )' );
	}
	else
	{	// Update existing record, because user already has a vote for this file
		$result = $DB->query( 'UPDATE T_files__vote
					SET '.$field_name.' = '.$DB->quote( $field_value ).'
					WHERE fvot_file_ID = '.$DB->quote( $file_ID ).'
						AND fvot_user_ID = '.$DB->quote( $user_ID ) );
	}

	if( $result )
	{
		$DB->commit();
	}
	else
	{
		$DB->rollback();
	}
}


/**
 * Copy file from source path to destination path (Used on import)
 *
 * @param string Path of source file
 * @param string FileRoot id string
 * @param string the upload dir relative path in the FileRoot
 * @param boolean Shall we check files add permission for current_User?
 * @return mixed NULL if import was impossible to complete for some reason (wrong fileroot ID, insufficient user permission, etc.)
 *               file ID of new inserted file in DB
 */
function copy_file( $file_path, $root_ID, $path, $check_perms = true )
{
	global $current_User;

	$FileRootCache = & get_FileRootCache();
	$fm_FileRoot = & $FileRootCache->get_by_ID($root_ID, true);
	if( !$fm_FileRoot )
	{	// fileRoot not found:
		return NULL;
	}

	if( $check_perms && ( !isset( $current_User ) || $current_User->check_perm( 'files', 'add', false, $fm_FileRoot ) ) )
	{	// Permission check required but current User has no permission to upload:
		return NULL;
	}

	// Let's get into requested list dir...
	$non_canonical_list_path = $fm_FileRoot->ads_path.$path;
	// Dereference any /../ just to make sure, and CHECK if directory exists:
	$ads_list_path = get_canonical_path( $non_canonical_list_path );

	// check if the upload dir exists
	if( !is_dir( $ads_list_path ) )
	{	// Create path
		mkdir_r( $ads_list_path );
	}

	// Get file name from full path:
	$newName = basename( $file_path );
	// validate file name
	if( $error_filename = process_filename( $newName, true ) )
	{	// Not a valid file name or not an allowed extension:
		// Abort import for this file:
		return NULL;
	}

	// Check if the imported file type is an image, and if is an image then try to fix the file extension based on mime type
	// If the mime type is a known mime type and user has right to import files with this kind of file type,
	// this part of code will check if the file extension is the same as admin defined for this file type, and will fix it if it isn't the same
	// Note: it will also change the jpeg extensions to jpg.
	// this image_info variable will be used again to get file thumb
	$image_info = getimagesize( $file_path );
	if( $image_info )
	{	// This is an image, validate mimetype vs. extension:
		$image_mimetype = $image_info['mime'];
		$FiletypeCache = & get_FiletypeCache();
		// Get correct file type based on mime type
		$correct_Filetype = $FiletypeCache->get_by_mimetype( $image_mimetype, false, false );

		// Check if file type is known by us, and if it is allowed for upload.
		// If we don't know this file type or if it isn't allowed we don't change the extension! The current extension is allowed for sure.
		if( $correct_Filetype && $correct_Filetype->is_allowed() )
		{	// A FileType with the given mime type exists in database and it is an allowed file type for current User
			// The "correct" extension is a plausible one, proceed...
			$correct_extension = array_shift($correct_Filetype->get_extensions());
			$path_info = pathinfo($newName);
			$current_extension = $path_info['extension'];

			// change file extension to the correct extension, but only if the correct extension is not restricted, this is an extra security check!
			if( strtolower($current_extension) != strtolower($correct_extension) && ( !in_array( $correct_extension, $force_upload_forbiddenext ) ) )
			{	// change the file extension to the correct extension
				$old_name = $newName;
				$newName = $path_info['filename'].'.'.$correct_extension;
			}
		}
	}

	// Get File object for requested target location:
	$oldName = strtolower( $newName );
	list( $newFile, $oldFile_thumb ) = check_file_exists( $fm_FileRoot, $path, $newName, $image_info );
	$newName = $newFile->get( 'name' );

	if( ! copy( $file_path, $newFile->get_full_path() ) )
	{	// Abort import for this file:
		return NULL;
	}

	// change to default chmod settings
	$newFile->chmod( NULL );

	// Refreshes file properties (type, size, perms...)
	$newFile->load_properties();

	// Store File object into DB:
	if( $newFile->dbsave() )
	{	// Success
		return $newFile->ID;
	}
	else
	{	// Failure
		return NULL;
	}
}


/**
 * Create links between users and image files from the users profile_pictures folder
 */
function create_profile_picture_links()
{
	global $DB;

	load_class( 'files/model/_filelist.class.php', 'Filelist' );
	load_class( 'files/model/_fileroot.class.php', 'FileRoot' );
	$path = 'profile_pictures';

	$FileRootCache = & get_FileRootCache();
	$UserCache = & get_UserCache();

	// SQL query to get all users and limit by page below
	$users_SQL = new SQL();
	$users_SQL->SELECT( '*' );
	$users_SQL->FROM( 'T_users' );
	$users_SQL->ORDER_BY( 'user_ID' );

	$page = 0;
	$page_size = 100;
	while( count( $UserCache->cache ) > 0 || $page == 0 )
	{ // Load users by 100 at one time to avoid errors about memory exhausting
		$users_SQL->LIMIT( ( $page * $page_size ).', '.$page_size );
		$UserCache->clear();
		$UserCache->load_by_sql( $users_SQL );

		while( ( $iterator_User = & $UserCache->get_next(/* $user_ID, false, false */) ) != NULL )
		{ // Iterate through UserCache)
			$FileRootCache->clear();
			$user_FileRoot = & $FileRootCache->get_by_type_and_ID( 'user', $iterator_User->ID );
			if( !$user_FileRoot )
			{ // User FileRoot doesn't exist
				continue;
			}

			$ads_list_path = get_canonical_path( $user_FileRoot->ads_path.$path );
			// Previously uploaded avatars
			if( !is_dir( $ads_list_path ) )
			{ // profile_picture folder doesn't exists in the user root dir
				continue;
			}

			$user_avatar_Filelist = new Filelist( $user_FileRoot, $ads_list_path );
			$user_avatar_Filelist->load();

			if( $user_avatar_Filelist->count() > 0 )
			{	// profile_pictures folder is not empty
				$info_content = '';
				$LinkOwner = new LinkUser( $iterator_User );
				while( $lFile = & $user_avatar_Filelist->get_next() )
				{ // Loop through all Files:
					$fileName = $lFile->get_name();
					if( process_filename( $fileName ) )
					{ // The file has invalid file name, don't create in the database
						// TODO: asimo> we should collect each invalid file name here, and send an email to the admin
						continue;
					}
					$lFile->load_meta( true );
					if( $lFile->is_image() )
					{
						$lFile->link_to_Object( $LinkOwner );
					}
				}
			}
		}

		// Increase page number to get next portion of users
		$page++;
	}

	// Clear cache data
	$UserCache->clear();
	$FileRootCache->clear();
}


/**
 * Create .htaccess and sample.htaccess files with deny rules in the folder
 *
 * @param string Directory path
 * @return boolean TRUE if files have been created successfully
 */
function create_htaccess_deny( $dir )
{
	if( ! mkdir_r( $dir, NULL ) )
	{
		return false;
	}

	$htaccess_files = array(
			$dir.'.htaccess',
			$dir.'sample.htaccess'
		);

	$htaccess_content = '# We don\'t want web users to access any file in this directory'."\r\n".
		'Order Deny,Allow'."\r\n".
		'Deny from All';

	foreach( $htaccess_files as $htaccess_file )
	{
		if( file_exists( $htaccess_file ) )
		{ // File already exists
			continue;
		}

		$handle = @fopen( $htaccess_file, 'w' );

		if( !$handle )
		{ // File cannot be created
			return false;
		}

		fwrite( $handle, $htaccess_content );
		fclose( $handle );
	}

	return true;
}
?>