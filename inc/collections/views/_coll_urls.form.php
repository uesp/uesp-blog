<?php
/**
 * This file implements the UI view for the collection URL properties.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2013 by Francois Planque - {@link http://fplanque.com/}
 * Parts of this file are copyright (c)2004-2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package admin
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id: _coll_urls.form.php 3328 2013-03-26 11:44:11Z yura $
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var Blog
 */
global $edited_Blog;
/**
 * @var GeneralSettings
 */
global $Settings;
/**
 * @var Log
 */
global $Debuglog;

?>
<script type="text/javascript">
	// Script to update the Blog URL preview:
	var blog_baseurl = '<?php echo str_replace( "'", "\'", $edited_Blog->gen_baseurl() ); ?>';

	function update_urlpreview( baseurl )
	{
		if( typeof baseurl == 'string' )
		{
			blog_baseurl = baseurl;
		}

		if( document.getElementById( 'urlpreview' ).hasChildNodes() )
		{
			document.getElementById( 'urlpreview' ).firstChild.data = blog_baseurl;
		}
		else
		{
			document.getElementById( 'urlpreview' ).appendChild( document.createTextNode( blog_baseurl ) );
		}
	}

	function show_hide_chapter_prefix(ob)
	{
		var fldset = document.getElementById( 'category_prefix_container' );
		if( ob.value == 'param_num' )
		{
			fldset.style.display = 'none';
		}
		else
		{
			fldset.style.display = '';
		}
	}
</script>


<?php

global $blog, $tab;

global $preset;

$Form = new Form();

$Form->begin_form( 'fform' );

$Form->add_crumb( 'collection' );
$Form->hidden_ctrl();
$Form->hidden( 'action', 'update' );
$Form->hidden( 'tab', $tab );
$Form->hidden( 'blog', $blog );


global $baseurl, $basedomain;

// determine siteurl type (if not set from update-action)
if( preg_match('#https?://#', $edited_Blog->get( 'siteurl' ) ) )
{ // absolute
	$blog_siteurl_relative = '';
	$blog_siteurl_absolute = $edited_Blog->get( 'siteurl' );
}
else
{ // relative
	$blog_siteurl_relative = $edited_Blog->get( 'siteurl' );
	$blog_siteurl_absolute = 'http://';
}

$Form->begin_fieldset( T_('Blog URL').' ['.T_('Admin').']'.get_manual_link('blog_url_settings') );

	if( $current_User->check_perm( 'blog_admin', 'edit', false, $edited_Blog->ID ) )
	{	// Permission to edit advanced admin settings

		$Form->text( 'blog_urlname', $edited_Blog->get( 'urlname' ), 20, T_('Blog URL name'), T_('Used to uniquely identify this blog. Appears in URLs and gets used as default for the media location (see the advanced tab).'), 255 );

		if( $default_blog_ID = $Settings->get('default_blog_ID') )
		{
			$Debuglog->add('Default blog is set to: '.$default_blog_ID);
			$BlogCache = & get_BlogCache();
			if( $default_Blog = & $BlogCache->get_by_ID($default_blog_ID, false) )
			{ // Default blog exists
				$defblog = $default_Blog->dget('shortname');
			}
		}

		$siteurl_relative_warning = '';
		if( ! preg_match( '~(^|/|\.php.?)$~i', $blog_siteurl_relative ) )
		{
			$siteurl_relative_warning = ' <span class="note red">'.T_('WARNING: it is highly recommended that this ends in with a / or .php !').'</span>';
		}

		$siteurl_absolute_warning = '';
		if( ! preg_match( '~(^|/|\.php.?)$~i', $blog_siteurl_absolute ) )
		{
			$siteurl_absolute_warning = ' <span class="note red">'.T_('WARNING: it is highly recommended that this ends in with a / or .php !').'</span>';
		}


		$Form->radio( 'blog_access_type', $edited_Blog->get( 'access_type' ), array(
			/* TODO: Tblue> This option only should be available if the
			 *              current blog is set as the default blog, otherwise
			 *              this setting is confusing. Another possible
			 *              solution would be to change the default blog
			 *              setting if this blog-specific setting is changed,
			 *              but then we would be have the same setting in
			 *              two places... I would be in favor of the first
			 *              solution.
			 * fp> I think it should actually change the default blog setting because
			 * people have a hard time finding the settings. I personally couldn't care
			 * less that there are 2 ways to do the same thing.
			 */
			array( 'default', T_('Default blog in index.php'),
											$baseurl.'index.php ('.( !isset($defblog)
												?	/* TRANS: NO current default blog */ T_('No default blog is currently set')
												: /* TRANS: current default blog */ T_('Current default :').' '.$defblog ).
											')',
										'',
										'onclick="update_urlpreview( \''.$baseurl.'index.php\' );"'
			),
			array( 'index.php', T_('Explicit param on index.php'),
										$baseurl.'index.php?blog='.$edited_Blog->ID,
										'',
										'onclick="update_urlpreview( \''.$baseurl.'index.php?blog='.$edited_Blog->ID.'\' )"',
			),
			array( 'extrapath', T_('Extra path on index.php'),
										$baseurl.'index.php/'.$edited_Blog->get( 'urlname' ),
										'',
										'onclick="update_urlpreview( \''.$baseurl.'index.php/\'+document.getElementById( \'blog_urlname\' ).value )"'
			),
			array( 'relative', T_('Relative to baseurl').':',
										'',
										'<span class="nobr"><code>'.$baseurl.'</code>'
										.'<input type="text" id="blog_siteurl_relative" class="form_text_input" name="blog_siteurl_relative" size="35" maxlength="120" value="'
										.format_to_output( $blog_siteurl_relative, 'formvalue' )
										.'" onkeyup="update_urlpreview( \''.$baseurl.'\'+this.value );"
										onfocus="document.getElementsByName(\'blog_access_type\')[3].checked=true;
										update_urlpreview( \''.$baseurl.'\'+this.value );" /></span>'.$siteurl_relative_warning,
										'onclick="document.getElementById( \'blog_siteurl_relative\' ).focus();"'
			),
			array( 'subdom', T_('Subdomain of basedomain'),
										preg_replace( '#(https?://)#i', '$1'.$edited_Blog->urlname.'.', $baseurl ),
										'',
										'onclick="update_urlpreview( \'http://\'+document.getElementById( \'blog_urlname\' ).value+\'.'.preg_replace( '#(https?://)#i', '', $baseurl ).'\' )"'
			),
			array( 'absolute', T_('Absolute URL').':',
										'',
										'<input type="text" id="blog_siteurl_absolute" class="form_text_input" name="blog_siteurl_absolute" size="50" maxlength="120" value="'
											.format_to_output( $blog_siteurl_absolute, 'formvalue' )
											.'" onkeyup="update_urlpreview( this.value );"
											onfocus="document.getElementsByName(\'blog_access_type\')[5].checked=true;
											update_urlpreview( this.value );" />'.$siteurl_absolute_warning,
										'onclick="document.getElementById( \'blog_siteurl_absolute\' ).focus();"'
			),
		), T_('Blog base URL'), true );

	}

	// URL Preview (always displayed)
	$blogurl = $edited_Blog->gen_blogurl();
	$Form->info( T_('URL preview'), '<span id="urlpreview">'.$blogurl.'</span>' );

$Form->end_fieldset();


$Form->begin_fieldset( T_('Date archive URLs') );

	$Form->radio( 'archive_links', $edited_Blog->get_setting('archive_links'),
		array(
				array( 'param', T_('Use param'), T_('E-g: ')
								.url_add_param( $blogurl, '<strong>m=20071231</strong>' ) ),
				array( 'extrapath', T_('Use extra-path'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/2007/12/31/</strong>' ) ),
			), T_('Date archive URLs'), true );

$Form->end_fieldset();


$Form->begin_fieldset( T_('Category URLs') );

	$Form->radio( 'chapter_links', $edited_Blog->get_setting('chapter_links'),
		array(
				array( 'param_num', T_('Use param: cat ID'), T_('E-g: ')
								.url_add_param( $blogurl, '<strong>cat=123</strong>' ),'', 'onclick="show_hide_chapter_prefix(this);"'),
				array( 'subchap', T_('Use extra-path: sub-category'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/subcat/</strong>' ), '', 'onclick="show_hide_chapter_prefix(this);"' ),
				array( 'chapters', T_('Use extra-path: category path'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/cat/subcat/</strong>' ), '', 'onclick="show_hide_chapter_prefix(this);"' ),
			), T_('Category URLs'), true );


		echo '<div id="category_prefix_container">';
			$Form->text_input( 'category_prefix', $edited_Blog->get_setting( 'category_prefix' ), 30, T_('Prefix'),
														T_('An optional prefix to be added to the URLs of the categories'),
														array('maxlength' => 120) );
		echo '</div>';

		if( $edited_Blog->get_setting( 'chapter_links' ) == 'param_num' )
		{ ?>
		<script type="text/javascript">
			<!--
			var fldset = document.getElementById( 'category_prefix_container' );
			fldset.style.display = 'none';
			//-->
		</script>
		<?php
		}

$Form->end_fieldset();


$Form->begin_fieldset( T_('Tag page URLs'), array('id'=>'tag_links_fieldset') );

	$Form->radio( 'tag_links', $edited_Blog->get_setting('tag_links'),
		array(
			array( 'param', T_('Use param'), T_('E-g: ')
				.url_add_param( $blogurl, '<strong>tag=mytag</strong>' ) ),
			array( 'prefix-only', T_('Use extra-path').': '.'Use URL path prefix only (recommended)', T_('E-g: ')
				.url_add_tail( $blogurl, '<strong>/<span class="tag_links_tag_prefix"></span>mytag</strong>' ) ),
			array( 'dash', T_('Use extra-path').': '.'trailing dash', T_('E-g: ')
				.url_add_tail( $blogurl, '<strong>/<span class="tag_links_tag_prefix"></span>mytag-</strong>' ) ),
			array( 'colon', T_('Use extra-path').': '.'trailing colon', T_('E-g: ')
				.url_add_tail( $blogurl, '<strong>/<span class="tag_links_tag_prefix"></span>mytag:</strong>' ) ),
			array( 'semicolon', T_('Use extra-path').': '.'trailing semi-colon (NOT recommended)', T_('E-g: ')
				.url_add_tail( $blogurl, '<strong>/<span class="tag_links_tag_prefix"></span>mytag;</strong>' ) ),
		), T_('Tag page URLs'), true );


	$Form->text_input( 'tag_prefix', $edited_Blog->get_setting( 'tag_prefix' ), 30, T_('Prefix'),
		T_('An optional prefix to be added to the URLs of the tag pages'),
		array('maxlength' => 120) );

	$Form->checkbox( 'tag_rel_attrib', $edited_Blog->get_setting( 'tag_rel_attrib' ), T_('Rel attribute'),
		sprintf( T_('Add <a %s>rel="tag" attribute</a> to tag links.'), 'href="http://microformats.org/wiki/rel-tag"' ) );

$Form->end_fieldset();

// Javascript juice for the tag fields.
?>
<script type="text/javascript">
jQuery("#tag_links_fieldset input[type=radio]").click( function()
{
	// Disable tag_prefix, if "param" is used.
	jQuery('#tag_prefix').attr("disabled", this.value == 'param' ? "disabled" : "");
	// Disable tag_rel_attrib, if "prefix-only" is not used.
	jQuery('#tag_rel_attrib').attr("disabled", this.value == 'prefix-only' ? "" : "disabled");

	// NOTE: dh> ".closest('fieldset').andSelf()" is required for the add-field_required-class-to-fieldset-hack. Remove as appropriate.
	if( this.value == 'prefix-only' )
		jQuery('#tag_prefix').closest('fieldset').andSelf().addClass('field_required');
	else
		jQuery('#tag_prefix').closest('fieldset').andSelf().removeClass('field_required');
} ).filter(":checked").click();

// Set text of span.tag_links_tag_prefix according to this field, defaulting to "tag" for "prefix-only".
jQuery("#tag_prefix").keyup( function() {
	jQuery("span.tag_links_tag_prefix").each(
		function() {
			var newval = ((jQuery("#tag_prefix").val().length || jQuery(this).closest("div").find("input[type=radio]").attr("value") != "prefix-only") ? jQuery("#tag_prefix").val() : "tag");
			if( newval.length ) newval += "/";
			jQuery(this).text( newval );
		}
	) } ).keyup();
</script>


<?php
$Form->begin_fieldset( T_('Single post URLs') );

	$Form->radio( 'single_links', $edited_Blog->get_setting('single_links'),
		array(
			  array( 'param_num', T_('Use param: post ID'), T_('E-g: ')
			  				.url_add_param( $blogurl, '<strong>p=123&amp;more=1</strong>' ) ),
			  array( 'param_title', T_('Use param: post title'), T_('E-g: ')
			  				.url_add_param( $blogurl, '<strong>title=post-title&amp;more=1</strong>' ) ),
				array( 'short', T_('Use extra-path: post title'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/post-title</strong>' ) ),
				array( 'y', T_('Use extra-path: year'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/2006/post-title</strong>' ) ),
				array( 'ym', T_('Use extra-path: year & month'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/2006/12/post-title</strong>' ) ),
				array( 'ymd', T_('Use extra-path: year, month & day'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/2006/12/31/post-title</strong>' ) ),
				array( 'subchap', T_('Use extra-path: sub-category'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/subcat/post-title</strong>' ) ),
				array( 'chapters', T_('Use extra-path: category path'), T_('E-g: ')
								.url_add_tail( $blogurl, '<strong>/cat/subcat/post-title</strong>' ) ),
			), T_('Single post URLs'), true );

$Form->end_fieldset();


$Form->buttons( array( array( 'submit', 'submit', T_('Save !'), 'SaveButton' ),
													array( 'reset', '', T_('Reset'), 'ResetButton' ) ) );

$Form->end_form();

?>