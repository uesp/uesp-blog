<?php
/**
 * This is the sidebar include template.
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

  <?php
// ---------------------------------------- "Sidebar" CONTAINER EMBEDDED HERE --------------------------
            skin_container( NT_('Sidebar'), array(
                    // This will enclose each widget in a block:
                    'block_start' => '<div class="bSideItem $wi_class$">',
                    'block_end'   => '</div>',
                    // This will enclose the title of each widget:
                    'block_title_start' => '<h3>',
                    'block_title_end'   => '</h3>',
                    // If a widget displays a list, this will enclose that list:
                    'list_start'  => '<ul>',
                    'list_end'    => '</ul>',
                    // This will enclose each item in a list:
                    'item_start'  => '<li>',
                    'item_end'    => '</li>',
                    // This will enclose sub-lists in a list:
                    'group_start' => '<ul>',
                    'group_end'   => '</ul>',
                    // This will enclose (foot)notes:
                    'notes_start' => '<div class="notes">',
                    'notes_end'   => '</div>',
                ) );
// --------------------------------------- END OF "Sidebar" CONTAINER -----------------------------

    	// ------------------------------ PAGE AD/CREDITS INCLUDED HERE ---------------------------

			skin_include( '_sidebar_credits.inc.php' );

		// ---------------------------------- END OF PAGE AD/CREDITS ------------------------------
		?>
  <div class="bSideItem">
    <div class="center"><a href="http://b2evolution.net" title="b2evolution.net" target="_blank"><img src="../../rsc/img/powered-by-b2evolution-150t.gif" alt="Powered by b2evolution" /></a></div>
  </div>