<?php
/**
 * This file implements the Messages class for displaying messages about performed actions.
 *
 * It additionally provides the class Log_noop that implements the same (used) methods, but as
 * no-operation functions. This is useful to create a more resource friendly object when
 * you don't need it (think Debuglog).
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
 * @author blueyed: Daniel HAHLER
 * @author fplanque: Francois PLANQUE
 *
 * @version $Id: _messages.class.php 3328 2013-03-26 11:44:11Z yura $ }}}
 *
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * Messages class. For displaying notes, successful actions & errors.
 *
 * @todo CLEAN UP A LOT because of previous over factorization with Log class.
 * 
 * Messages can be logged into different categories (aka levels)
 * Examples: 'note', 'error'. Note: 'all' is reserved to display all categories together.
 * Messages can later be displayed grouped by category/level.
 *
 * @package evocore
 */
class Messages
{
	/**
	 * The stored messages text.
	 * array of Strings
	 *
	 * @var array
	 */
	var $messages_text = array();

	/**
	 * The stored messages type.
	 * array of Strings
	 *
	 * @var array
	 */
	var $messages_type = array();

	/**
	 * The number of messages
	 * 
	 * @var integer
	 */
	var $count = 0;

	/**
	 * Error message was added or not.
	 * 
	 * @var boolean
	 */
	var $has_errors = false;

	/**
	 * Clears messages content
	 */
	function clear()
	{
		$this->messages_text = array();
		$this->messages_type = array();
		$this->count = 0;
		$this->has_errors = false;
	}


	/**
	 * Add a message.
	 *
	 * @param string the message
	 * @param string the message type, it can have this values: 'success', 'warning', 'error', 'note'
	 */
	function add( $text, $type = 'error' )
	{
		$this->messages_text[$this->count] = $text;
		$this->messages_type[$this->count] = $type;
		$this->count++;
		if( !$this->has_errors )
		{
			$this->has_errors = ( $type == 'error' );
		}
	}


	/**
	 * Add a Messages object to this.
	 *
	 * @param Messages object
	 */
	function add_messages( $p_Messages )
	{
		$this->count = $this->count + $p_Messages->count;
		for( $i = 0; $i < $p_Messages->count; $i++ )
		{
			$this->messages_text[] = $p_Messages->messages_text[$i];
			$this->messages_type[] = $p_Messages->messages_type[$i];
			if( !$this->has_errors )
			{
				$this->has_errors = ( $p_Messages->messages_type[$i] == 'error' );
			}
		}
	}


	/**
	 * TEMPLATE TAG
	 *
	 * The purpose here is to have a tag which is simple yet flexible.
	 * the display function is WAAAY too bloated.
	 *
	 * @todo optimize
	 *
	 * @param string HTML to display before the log when there is something to display
	 * @param string HTML to display after the log when there is something to display
	 */
	function disp( $before = '<div class="action_messages">', $after = '</div>' )
	{
		if( $this->count )
		{
			global $preview;
			if( $preview )
			{
				return;
			}

			$disp = $this->display( NULL, NULL, false, NULL );

			if( !empty( $disp ) )
			{
				echo $before.$disp.$after;
			}
		}
	}


	/**
	 * Display messages of the object.
	 *
	 * - You can output/get the messages
	 * - Head/Foot will be displayed on top/bottom of the messages.
	 * - You can suppress the outer div or set a css class for it (defaults to
	 *   'log_container').
	 *
	 * @todo Make this simple!
	 * start by getting rid of the $category selection and the special cases for 'all'. If you don't want to display ALL messages,
	 * then you should not log them in the same Log object and you should instantiate separate logs instead.
	 *
	 * @param string|NULL Header/title
	 * @param string|NULL Footer
	 * @param boolean to display or return (default: display)
	 * @param mixed the outer div, may be false
	 * @return boolean false, if no messages; else true (and outputs if $display)
	 */
	function display( $head = NULL, $foot = NULL, $display = true, $outerdivclass = 'log_container' )
	{
		if( $this->count == 0 ) {
			return false;
		}

		$disp = '';

		if( $outerdivclass )
		{
			$disp .= "\n<div class=\"$outerdivclass\">";
		}

		if( !empty( $head ) )
		{
			$disp .= '<h3>'.$head.'</h3>';
		}

		$disp .= '<ul>';
		for( $i = 0; $i < $this->count; $i++ )
		{
			$disp .= "<li>\t<div class=\"log_{$this->messages_type[$i]}\"".'>'
					.$this->messages_text[$i]."</div></li>\n";
		}
		$disp .= '</ul>';

		if( !empty( $foot ) )
		{
			$disp .= "\n<p>".$foot."</p>";
		}

		if( $outerdivclass )
		{
			$disp .= "</div>\n";
		}

		if( $display )
		{
			echo $disp;
			return true;
		}
		return $disp;
	}


	/**
	 * Concatenates messages of a given category to a string
	 *
	 * @param string prefix of the string
	 * @param string suffic of the string
	 * @param string the glue
	 * @param string result format
	 * @return string the messages, imploded. Tags stripped.
	 */
	function get_string( $head = '', $foot = '', $implodeBy = ', ', $format = 'striptags' )
	{
		if( !$this->count )
		{
			return false;
		}

		$r = '';
		if( '' != $head )
		{
			$r .= $head.' ';
		}
		$r .= implode( $implodeBy, $this->messages_text );
		if( '' != $foot )
		{
			$r .= ' '.$foot;
		}

		switch( $format )
		{
			case 'xmlrpc':
				$r = strip_tags( $r );	// get rid of <code>
				$r = str_replace( '&lt;', '<', $r );
				$r = str_replace( '&gt;', '>', $r );
				$r = str_replace( '&quot;', '"', $r );
				break;

			case 'striptags':
				$r = strip_tags( $r );
				break;
		}

		return $r;
	}


	/**
	 * Get the number of messages
	 *
	 * @return number of messages
	 */
	function count()
	{
		return $this->count;
	}


	/**
	 * Has error message in current object
	 *
	 * @return boolean true if error message was added, false otherwise
	 */
	function has_errors()
	{
		return $this->has_errors;
	}
}

?>