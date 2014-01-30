<?php
/**
 * This is the BODY footer include template.
 *
 * For a quick explanation of b2evo 2.0 skins, please start here:
 * {@link http://manual.b2evolution.net/Skins_2.0}
 *
 * This is meant to be included in a page template.
 *
 * Russian b2evolution skin	ru.b2evo.net
 *
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );
?>

<!-- =================================== START OF FOOTER =================================== -->
<div id="adsense-bottom">
<center>
<script type="text/javascript"><!--
google_ad_client = "pub-3886949899853833";
/* 336x280, Blog Bottom */
google_ad_slot = "1283633694";
google_ad_width = 336;
google_ad_height = 280;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</div>
</center>
<div class="copyright">
<?php

// Display a link to contact the owner of this blog (if owner accepts messages):
$Blog->contact_link( array(
		'before'      => '',
		'after'       => ' &bull; ',
		'text'   => T_('Contact'),
		'title'  => T_('Send a message to the owner of this blog...'),
	) );
	
// Display footer text (text can be edited in Blog Settings):
$Blog->footer_text( array(
		'before'      => '',
		'after'       => ' &bull; ',
	) );

?>

Powered by <a href="http://b2evolution.net/" title="b2evolution home" target="_blank">b2evolution</a>

<?php
// Display additional credits (see /conf/):
// If you can add your own credits without removing the defaults, you'll be very cool :))
// Please leave this at the bottom of the page to make sure your blog gets listed on b2evolution.net
credits( array(
		'list_start'  => ' &bull; ',
		'list_end'    => '',
		'separator'   => ' &bull; ',
		'item_start'  => ' ',
		'item_end'    => ' ',
	) );
?>

<p>Theme designed by Alex (sam2kb) <a href="http://ru.b2evo.net/">Russian b2evolution</a></p>

</div>
