<?php
/**
 * This is the HTML header include template.
 *
 * For a quick explanation of b2evo 2.0 skins, please start here:
 * {@link http://manual.b2evolution.net/Skins_2.0}
 *
 * This is meant to be included in a page template.
 * Note: This is also included in the popup: do not include site navigation!
 *
 * Russian b2evolution skin	ru.b2evo.net
 *
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $xmlsrv_url;

require_js( 'functions.js' );
require_js( 'rollovers.js' );

skin_content_header();	// Sets charset!
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php locale_lang() ?>" lang="<?php locale_lang() ?>">
<head>
	<?php skin_content_meta(); /* Charset for static pages */ ?>
	<meta name="google-site-verification" content="K_CGe6EqESOb6WZq12HKH0FdGujGadrwaPAu6fQilQk" />
    <?php skin_base_tag(); /* Base URL for this skin. You need this to fix relative links! */ ?>
    <?php $Plugins->trigger_event( 'SkinBeginHtmlHead' ); ?>
    <title><?php
            // ------------------------- TITLE FOR THE CURRENT REQUEST -------------------------
            request_title( array(
                'auto_pilot'		  => 'seo_title',
                'title_single_before' => $Blog->get( 'shortname' ).' - ',
            ) );
            // ------------------------------ END OF REQUEST TITLE -----------------------------
        ?></title>
    <meta name="description" content="<?php $Blog->disp( 'shortdesc', 'htmlattr' ); ?>" />
    <meta name="keywords" content="<?php $Blog->disp( 'keywords', 'htmlattr' ); ?>" />
    <?php robots_tag(); ?>
    <meta name="generator" content="b2evolution <?php app_version(); ?>" /> <!-- Please leave this for stats -->
    <link rel="shortcut icon" href="http://blog.uesp.net/favicon.ico?v=2" />
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php $Blog->disp( 'rss2_url', 'raw' ) ?>" />
    <link rel="alternate" type="application/atom+xml" title="Atom" href="<?php $Blog->disp( 'atom_url', 'raw' ) ?>" />
    <link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php echo $xmlsrv_url; ?>rsd.php?blog=<?php echo $Blog->ID; ?>" />
    <link rel="stylesheet" href="style.css" type="text/css" />
    <script type="text/javascript" src="/rsc/js/ajax.js"></script>
    <?php include_headlines() /* Add javascript and css files included by plugins and skin */ ?>
    <?php
          $Blog->disp( 'blog_css', 'raw');
          $Blog->disp( 'user_css', 'raw');
    ?>
</head>
<body>
<?php
// ---------------------------- TOOLBAR INCLUDED HERE ----------------------------
require $skins_path.'_toolbar.inc.php';
// ------------------------------- END OF TOOLBAR --------------------------------

echo "\n";
if( is_logged_in() )
{
	echo '<div id="skin_wrapper" class="skin_wrapper_loggedin">';
}
else
{
	echo '<div id="skin_wrapper" class="skin_wrapper_anonymous">';
}
echo "\n";
?>
<!-- Start of skin_wrapper -->
