<?php
/**
 * This is a demo template displaying multiple blogs on the same page
 *
 * If you're new to b2evolution templates or skins, you should not start with this file
 * It will be easier to start examining blog_a.php or noskin_a.php for instance...
 *
 * @package evoskins
 * @subpackage noskin
 */

# First blog will be displayed the regular way (why bother?)
$blog = 1;

# Tell b2evolution you don't want to use evoSkins
# (evoSkins are designed to display only one blog at once + optionnaly a linkblog)
$skin = '';

# This setting retricts posts to those published, thus hiding drafts.
# You should not have to change this.
$show_statuses = array();

# Additionnaly, you can set other values (see URL params in the manual)...
# $order = 'ASC'; // This for example would display the blog in chronological order...

# Tell b2evolution not to redirect. This is necessary only if "301" for homepage is checked and the blog URL is set
# to something else than this page -- which is the case on demo installs.
# For production systems, properly set the blog URL , then remove the line below:
$redir = 'no';

/**
 * Let b2evolution handle the query string and load the blog data:
 */
require_once dirname(__FILE__).'/conf/_config.php';

require_once $inc_path.'_blog_main.inc.php';

// Make sure includes will check in the current folder!
$ads_current_skin_path = dirname(__FILE__).'/';


# Now, below you'll find the magic template...


// --------------------- PAGE LEVEL CACHING SUPPORT ---------------------
// Note: This is totally optional. General caching must be enabled in Global settings, otherwise this will do nothing.
// Delete this block if you don't care about page level caching. Don't forget to delete the matching section at the end of the page.
load_class( '_core/model/_pagecache.class.php', 'PageCache' );
$PageCache = new PageCache( NULL );
// Check for cached content & Start caching if needed:
if( ! $PageCache->check() )
{	// Cache miss, we have to generate:
	// --------------------- PAGE LEVEL CACHING SUPPORT ---------------------


// This is the main template; it may be used to display very different things.
// Do inits depending on current $disp:
skin_init( $disp );

// Add CSS:
require_css( 'basic_styles.css', 'rsc_url' ); // the REAL basic styles
require_css( 'basic.css', 'rsc_url' ); // Basic styles
require_css( 'blog_base.css', 'rsc_url' ); // Default styles for the blog navigation
require_css( 'item_base.css', 'rsc_url' ); // Default styles for the post CONTENT
require_css( 'fp02.css', 'rsc_url' );

add_js_for_toolbar();		// Registers all the javascripts needed by the toolbar menu

// Functions to work with AJAX response data
require_js( '#jquery#', 'rsc_url' );
require_js( '#jqueryUI#', 'rsc_url' );
require_js( 'ajax.js', 'rsc_url' );

headers_content_mightcache( 'text/html' );		// In most situations, you do NOT want to cache dynamic content!
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php locale_lang() ?>" lang="<?php locale_lang() ?>"><!-- InstanceBegin template="/Templates/Standard.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
	<title><?php
		// ------------------------- TITLE FOR THE CURRENT REQUEST -------------------------
		request_title( array(
			'title_before'=> '',
			'title_after' => ' - ',
			'title_none'  => '',
			'glue'        => ' - ',
			'format'      => 'htmlhead',
		) );
		// ------------------------------ END OF REQUEST TITLE -----------------------------
	?>Multiblog Demo</title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
	<?php skin_base_tag(); /* You're not using any skin here but this won't hurt. However it will be very helpful to have this here when you make the switch to a skin! */ ?>
	<?php skin_description_tag(); ?>
	<?php skin_keywords_tag(); ?>
	<meta name="generator" content="b2evolution <?php echo $app_version ?>" /> <!-- Please leave this for stats -->
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php $Blog->disp( 'rss2_url', 'raw' ) ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom" href="<?php $Blog->disp( 'atom_url', 'raw' ) ?>" />
	<?php include_headlines() /* Add javascript and css files included by plugins and skin */ ?>
	<!-- InstanceEndEditable -->
</head>
<body>
<!-- InstanceBeginEditable name="ToolBar" -->
<?php
	// ---------------------------- TOOLBAR INCLUDED HERE ----------------------------
	require $skins_path.'_toolbar.inc.php';
	// ------------------------------- END OF TOOLBAR --------------------------------
	echo "\n";
	if( show_toolbar() )
	{
		echo '<div id="skin_wrapper" class="skin_wrapper_loggedin">';
	}
	else
	{
		echo '<div id="skin_wrapper" class="skin_wrapper_anonymous">';
	}
	echo "\n";
?>
<!-- InstanceEndEditable -->
<div class="pageHeader">
<!-- InstanceBeginEditable name="NavBar2" -->
<?php
	// --------------------------------- START OF BLOG LIST --------------------------------
	skin_widget( array(
						// CODE for the widget:
						'widget' => 'colls_list_public',
						// Optional display params
						'block_start' => '<div class="NavBar">',
						'block_end' => '</div>',
						'block_display_title' => false,
						'list_start' => '',
						'list_end' => '',
						'item_start' => '',
						'item_end' => '',
						'item_selected_start' => '',
						'item_selected_end' => '',
						'link_selected_class' => 'NavButton2',
						'link_default_class' => 'NavButton2',
				) );
	// ---------------------------------- END OF BLOG LIST ---------------------------------
?>
<!-- InstanceEndEditable -->
<div class="pageTitle">
<h1 id="pageTitle"><!-- InstanceBeginEditable name="PageTitle" --><?php echo T_('Multiblog demo') ?><!-- InstanceEndEditable --></h1>
</div>
</div>


<div class="pageSubTitle"><!-- InstanceBeginEditable name="SubTitle" --><?php echo T_('This demo template displays 3 blogs at once (1 on the left, 2 on the right)') ?><!-- InstanceEndEditable --></div>


<div class="main"><!-- InstanceBeginEditable name="Main" -->

<!-- =================================== START OF MAIN AREA =================================== -->

<div class="bPosts">
	<h2>#1: <a href="<?php $Blog->disp( 'blogurl', 'raw' ) ?>"><?php echo $Blog->disp( 'name', 'htmlbody' ) ?></a></h2>

	<?php
		// ------------------------- TITLE FOR THE CURRENT REQUEST -------------------------
		request_title( array(
				'title_before'=> '<h2>',
				'title_after' => '</h2>',
				'title_none'  => '',
				'glue'        => ' - ',
				'title_single_disp' => true,
				'format'      => 'htmlbody',
			) );
		// ------------------------------ END OF REQUEST TITLE -----------------------------
	?>

	<?php // ------------------------------------ START OF POSTS ----------------------------------------
		// Display message if no post:
		display_if_empty();

		while( $Item = & mainlist_get_item() )
		{	// For each blog post, do everything below up to the closing curly brace "}"
			?>

			<?php
			// ------------------------------ DATE SEPARATOR ------------------------------
			$MainList->date_if_changed( array(
					'before'      => '<h2>',
					'after'       => '</h2>',
					'date_format' => '#',
				) );
			?>

		<div id="<?php $Item->anchor_id() ?>" class="bPost bPost<?php $Item->status_raw() ?>" lang="<?php $Item->lang() ?>">

			<div class="bSmallHead">
			<?php
				$Item->permanent_link( array(
						'text' => '#icon#',
					) );
			?>
			<?php
				$Item->issue_time(); // Post issue time
			?>
			<?php
				$Item->categories( array(
					'before'          => ', '.T_('Categories').': ',
					'after'           => ' ',
					'include_main'    => true,
					'include_other'   => true,
					'include_external'=> true,
					'link_categories' => true,
				) );
			?>
			</div>
			<h3 class="bTitle"><?php $Item->title(); ?></h3>

			<?php
				// ---------------------- POST CONTENT INCLUDED HERE ----------------------
				skin_include( '_item_content.inc.php', array(
						'image_size'	=>	'fit-400x320',
					) );
				// Note: You can customize the default item feedback by copying the generic
				// /skins/_item_feedback.inc.php file into the current skin folder.
				// -------------------------- END OF POST CONTENT -------------------------
			?>

			<div class="bSmallPrint">
				<?php
					// Link to comments, trackbacks, etc.:
					$Item->feedback_link( array(
									'type' => 'comments',
									'link_before' => '',
									'link_after' => ' &bull; ',
									'link_text_zero' => '#',
									'link_text_one' => '#',
									'link_text_more' => '#',
									'link_title' => '#',
									'use_popup' => false,
								) );
				 ?>
				<?php
					// Link to comments, trackbacks, etc.:
					$Item->feedback_link( array(
									'type' => 'trackbacks',
									'link_before' => '',
									'link_after' => ' &bull; ',
									'link_text_zero' => '#',
									'link_text_one' => '#',
									'link_text_more' => '#',
									'link_title' => '#',
									'use_popup' => false,
								) );
				 ?>

				<?php	$Item->permanent_link(); ?>
			</div>

			<?php
				// ------------------ FEEDBACK (COMMENTS/TRACKBACKS) INCLUDED HERE ------------------
				skin_include( '_item_feedback.inc.php', array(
						'before_section_title' => '<h4>',
						'after_section_title'  => '</h4>',
					) );
				// Note: You can customize the default item feedback by copying the generic
				// /skins/_item_feedback.inc.php file into the current skin folder.
				// ---------------------- END OF FEEDBACK (COMMENTS/TRACKBACKS) ---------------------
			?>
		</div>
	<?php
		} // ---------------------------------- END OF POSTS ------------------------------------
	?>

	<?php
		// -------------------- PREV/NEXT PAGE LINKS (POST LIST MODE) --------------------
		mainlist_page_links( array(
				'block_start' => '<p class="center"><strong>',
				'block_end' => '</strong></p>',
				'links_format' => '$prev$ :: $next$',
   			'prev_text' => '&lt;&lt; '.T_('Previous'),
   			'next_text' => T_('Next').' &gt;&gt;',
			) );
		// ------------------------- END OF PREV/NEXT PAGE LINKS -------------------------
	?>

	<?php
		// -------------- MAIN CONTENT TEMPLATE INCLUDED HERE (Based on $disp) --------------
		skin_include( '$disp$', array(
				'disp_posts'  => '',		// We already handled this case above
				'disp_single' => '',		// We already handled this case above
				'disp_page'   => '',		// We already handled this case above
			) );
		// Note: you can customize any of the sub templates included here by
		// copying the matching php file into your skin directory.
		// ------------------------- END OF MAIN CONTENT TEMPLATE ---------------------------
	?>

</div>

<!-- =================================== START OF SIDEBAR =================================== -->

<div class="bSideBar">

	<!-- =================================== START OF BLOG B =================================== -->

	<div class="bSideItem">
		<?php
		$BlogCache = & get_BlogCache();
		$Blog_B = & $BlogCache->get_by_ID( 2, false );
		if( empty($Blog_B) )
		{
			echo sprintf( T_('Blog #%d doesn\'t seem to exist.'), 2 );
		}
		else
		{
			?>

			<h3>#2: <a href="<?php $Blog_B->disp( 'blogurl', 'raw' ) ?>"><?php echo $Blog_B->disp( 'name', 'htmlbody' ) ?></a></h3>
			<?php
			$BlogBList = new ItemList2( $Blog_B, $Blog_B->get_timestamp_min(), $Blog_B->get_timestamp_max(), $posts );

			$BlogBList->set_filters( array(
					'authors' => $author,
					'ymdhms' => $m,
					'week' => $w,
					'order' => $order,
					'orderby' => $orderby,
					'unit' => $unit,
				) );

			// Run the query:
			$BlogBList->query();

			while( $Item = & $BlogBList->get_item() )
			{
				?>
				<div id="<?php $Item->anchor_id() ?>" class="bPostSide bPostSide<?php $Item->status_raw() ?>" lang="<?php $Item->lang() ?>">

					<h4 class="bTitle">
						<?php
							$Item->permanent_link( array(
									'text' => '#icon#',
								) );
						?>
						<?php $Item->title(); ?>
					</h4>
					<div class="bText">
						<?php
							// Display CONTENT (teaser only):
							$Item->content_teaser( array(
									'before'      => '',
									'after'       => '',
									'disppage'    => 1,
									'stripteaser' => false,
									'image_size'  => 'fit-400x320',
								) );
						?>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>

	<!-- =================================== START OF BLOG C =================================== -->

	<div class="bSideItem">
		<?php
		$Blog_roll = & $BlogCache->get_by_ID( 3, false );
		if( empty($Blog_roll) )
		{
			echo sprintf( T_('Blog #%d doesn\'t seem to exist.'), 3 );
		}
		else
		{
		?>
		<h3>#3: <a href="<?php $Blog_roll->disp( 'blogurl', 'raw' ) ?>"><?php echo $Blog_roll->disp( 'name', 'htmlbody' ) ?></a></h3>
		<?php
		$LinkblogList = new ItemList2( $Blog_roll, $Blog_roll->get_timestamp_min(), $Blog_roll->get_timestamp_max(), $posts );

		$LinkblogList->set_filters( array(
				'authors' => $author,
				'ymdhms' => $m,
				'week' => $w,
				'order' => $order,
				'orderby' => $orderby,
				'unit' => $unit,
			) );

		// Run the query:
		$LinkblogList->query();

		while( $Item = & $LinkblogList->get_item() )
		{
			?>
			<div id="<?php $Item->anchor_id() ?>" class="bPostSide bPostSide<?php $Item->status_raw() ?>" lang="<?php $Item->lang() ?>">
				<h4 class="bTitle">
					<?php
						$Item->permanent_link( array(
								'text' => '#icon#',
							) );
					?>
					<?php $Item->title(); ?>
				</h4>
				<div class="bText">
					<?php
						// Display CONTENT (teaser only):
						$Item->content_teaser( array(
								'before'      => '',
								'after'       => '',
								'disppage'    => 1,
								'stripteaser' => false,
								'image_size'  => 'fit-400x320',
							) );
					?>
				</div>
			</div>
			<?php
		}
		}
		?>
	</div>

	<!-- =================================== END OF BLOG C =================================== -->

	<div class="bSideItem">
		<h3><?php echo T_('Misc') ?></h3>
		<ul>
			<?php
				// Administrative links:
				user_login_link( '<li>', '</li>' );
				user_register_link( '<li>', '</li>' );
				user_admin_link( '<li>', '</li>' );
				user_profile_link( '<li>', '</li>' );
				user_subs_link( '<li>', '</li>' );
				user_logout_link( '<li>', '</li>' );
			?>
		</ul>
	</div>

</div>
<!-- InstanceEndEditable --></div>
<div class="footer">
This is a demo page for <a href="http://b2evolution.net/">b2evolution</a>.
<!-- InstanceBeginEditable name="Baseline" -->
<?php echo '</div>' ?>
<!-- Note: don't mess with the template here :/ --><!-- InstanceEndEditable --></div>
</body>
<!-- InstanceEnd --></html>
<?php
	// --------------------- PAGE LEVEL CACHING SUPPORT ---------------------
	// Save collected cached data if needed:
	$PageCache->end_collect();
}
// --------------------- PAGE LEVEL CACHING SUPPORT ---------------------
?>