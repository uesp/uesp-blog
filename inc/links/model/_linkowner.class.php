<?php
/**
 * This file implements the abstract Link Owner class, which is a wrapper class for objects which can have linked files.
 * Important: This class is abstract must never be instantiated.
 * 
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2013 by Francois Planque - {@link http://fplanque.com/}
 * 
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author efy-asimo: Attila Simo.
 *
 * @version $Id: $
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * LinkOwner Abstract Class
 *
 * @package evocore
 */
class LinkOwner
{
	/**
	 * Type of the Link Owner object
	 * 
	 * @var string
	 */
	var $type;

	/**
	 * The link owner object
	 * 
	 * @var DataObject
	 */
	var $link_Object;

	/**
	 * Array of Links attached to this object.
	 *
	 * NULL when not initialized.
	 *
	 * @var array
	 * @access public
	 */
	var $Links = NULL;

	/**
	 * The link owner Blog
	 * 
	 * @var Blog
	 */
	var $Blog = NULL;

	/**
	 * The translation map, and it must be initialized in every subclass constructor
	 * 
	 * @var array
	 */
	var $_trans = NULL;

	/**
	 * Abstract methods that needs to be overriden in every subclass
	 * 
	 * function check_perm( $perm_name, $assert = false ); // check link owner object ( item, comment, ... ) edit/view permission
	 * function get_where_condition(); // get where condition for select query to get link owner links
	 * function get_positions(); // get all positions where link can be displayed ( 'teaser', 'aftermore' )
	 * function get_edit_url(); // get link owner edit url
	 * function get_view_url(); // get link owner view url
	 * function load_Links(); // load link owner all links
	 * function add_link( $file_ID, $position, $order ); // add a new link to link owner
	 * function load_Blog(); // set Link Owner Blog
	 */

	/**
	 * Constructor
	 * 
	 * @protected It is allowed to be called only from subclasses
	 *  
	 * @param object the link owner object
	 * @param string the link type ( item, comment, ... )
	 */
	function LinkOwner( $link_Object, $type )
	{
		$this->link_Object = $link_Object;
		$this->type = $type;
	}

	/**
	 * Get all links
	 */
	function & get_Links()
	{
		$this->load_Links();

		return $this->Links;
	}

	/**
	 * Remove link from the owner
	 *
	 * @param object Link
	 * @return boolean true on success
	 */
	function remove_link( & $Link )
	{
		$this->load_Links();

		$index = array_search( $Link, $this->Links );
		if( $index !== false )
		{
			unset( $this->Links[ $index ] );
		}
		$LinkCacche = & get_LinkCache();
		$LinkCacche->remove( $Link );
		return $Link->dbdelete();
	}

	/**
	 * Get Link by File ID
	 */
	function & get_link_by_file_ID( $file_ID )
	{
		$this->load_Links();

		$r = NULL;
		foreach( $this->Links as $Link )
		{
			if( $Link->file_ID == $file_ID )
			{
				$r = $Link;
				break;
			}
		}

		return $r;
	}

	/**
	 * Count how many files are attached to this link owner owner object
	 * 
	 * @return int the number of attachments
	 */
	function count_links()
	{
		$this->load_Links();

		return count( $this->Links );
	}

	/**
	 * Get Blog
	 */
	function & get_Blog()
	{
		$this->load_Blog();

		return $this->Blog;
	}

	/**
	 * Get SQL query to select all links attached to this owner
	 * 
	 * @param string links order by
	 * @return object SQL
	 */
	function get_SQL( $order_by = NULL )
	{
		$SQL = new SQL();

		/**
		 * asimo> Replace in select query the link_cmt_ID, link_itm_ID with the following ( it can be used only after MySQL 5.0)
		 * (CASE
			WHEN (link_cmt_ID IS NOT NULL) THEN 'comment'
			WHEN (link_itm_ID IS NOT NULL) THEN 'item'
			END) as owner_type
		 */
		if( $order_by == NULL )
		{
			$order_by = 'link_order, link_ID';
		}

		$SQL->SELECT( 'link_ID, link_ltype_ID, link_position, link_cmt_ID, link_itm_ID, file_ID, file_title, file_root_type, file_root_ID, file_path, file_alt, file_desc' );
		$SQL->FROM( 'T_links LEFT JOIN T_files ON link_file_ID = file_ID' );
		$SQL->WHERE( $this->get_where_condition() );
		$SQL->ORDER_BY( $order_by );

		return $SQL;
	}

	/**
	 * Get link owner object ID
	 */
	function get_ID()
	{
		return $this->link_Object->ID;
	}

	/**
	 * Get link owner object parameter
	 * 
	 * @param string parameter name to get
	 */
	function get( $parname )
	{
		return $this->link_Object->dget( $parname );
	}

	/**
	 * Get a ready-to-display position name by key value
	 * 
	 * @param string link position
	 */
	function dget_position( $position )
	{
		$positions = $this->get_positions();
		if( isset( $positions[ $position ] ) )
		{
			return $positions[ $position ];
		}
		return NULL;
	}

	/**
	 * Get default position for a new link
	 * 
	 * @param boolean new link file is image or not
	 */
	function get_default_position( $is_image = false )
	{
		$this->load_Links();

		if( $this->Links )
		{
			$position = NULL;
			$last_Link = array_pop( $this->Links );
			$last_File = & $last_Link->get_File();

			if( $last_File && $is_image && $last_File->is_image() && ! count( $this->Links ) )
			{ // there's only one image attached yet, the second becomes "aftermore"
				$position = 'aftermore';
			}
			else
			{ // default: use position of previous link/attachment
				$position = $last_Link->get('position');
			}

			// Re-add popped link.
			$this->Links[] = $last_Link;
		}
		else
		{ // no attachment yet
			$position = $is_image ? 'teaser' : 'aftermore';
		}

		return $position;
	}


	/**
	 * Get list of attached files
	 * 
	 * INNER JOIN on files ensures we only get back file links
	 * 
	 * @param integer Limit max result
	 * @param string Restrict to files/images linked to a specific position.
	 *               Position can be 'teaser'|'aftermore'|'inline'
	 *               Use comma as separator
	 * @param string order by
	 * @return DataObjectList2 on success or NULL if no linked files found
	 */
	function get_attachment_FileList( $limit = 1000, $position = NULL, $order = 'link_ID' )
	{
		if( ! isset($GLOBALS['files_Module']) )
		{
			return NULL;
		}

		load_class( '_core/model/dataobjects/_dataobjectlist2.class.php', 'DataObjectList2' );

		$FileCache = & get_FileCache();

		$FileList = new DataObjectList2( $FileCache ); // IN FUNC

		$SQL = new SQL();
		$SQL->SELECT( 'file_ID, file_title, file_root_type, file_root_ID, file_path, file_alt, file_desc, link_ID' );
		$SQL->FROM( 'T_links INNER JOIN T_files ON link_file_ID = file_ID' );
		$SQL->WHERE( $this->get_where_condition() );
		if( !empty($position) )
		{
			global $DB;
			$position = explode( ',', $position );
			$SQL->WHERE_and( 'link_position IN ( '.$DB->quote( $position ).' )' );
		}
		//$SQL->ORDER_BY( $order );
		$SQL->ORDER_BY( 'link_order' );
		$SQL->LIMIT( $limit );

		$FileList->sql = $SQL->get();

		$FileList->query( false, false, false, 'get_attachment_FileList' );

		if( $FileList->result_num_rows == 0 )
		{	// Nothing found
			$FileList = NULL;
		}

		return $FileList;
	}


	/**
	 * Get translated text for the specific link owner class
	 * 
	 * @param string text key in the translation map
	 */
	function translate( $text_key, $text_params = NULL )
	{
		if( empty( $this->_trans ) || empty( $text_key ) || ( !array_key_exists( $text_key, $this->_trans ) ) )
		{ // This text was not listed in translation map
			return NULL;
		}

		return sprintf( T_( $this->_trans[ $text_key ] ), $text_params );
	}


	/**
	 * Update owner last_touched_ts if exists 
	 * This must be override in the subclasses if the owner object has last_touched_ts field
	 */
	function item_update_last_touched_date()
	{
		return;
	}
}

?>