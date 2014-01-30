<?php
/**
 * This is the main/default page template for the "custom" skin.
 *
 * This skin only uses one single template which includes most of its features.
 * It will also rely on default includes for specific dispays (like the comment form).
 *
 * For a quick explanation of b2evo 2.0 skins, please start here:
 * {@link http://manual.b2evolution.net/Skins_2.0}
 *
 * The main page template is used to display the blog when no specific page template is available
 * to handle the request (based on $disp).
 *
 * @version $Id: index.main.php,v 1.9 2007/11/03 23:54:39 fplanque Exp $
 *
 * Russian b2evolution skin	ru.b2evo.net
 */

if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

// Do inits depending on current $disp:
skin_init( $disp );

// -------------------------- HTML HEADER INCLUDED HERE --------------------------
	skin_include( '_html_header.inc.php' );
	// Note: You can customize the default HTML header by copying the generic
	// /skins/_html_header.inc.php file into the current skin folder.
// -------------------------------- END OF HEADER --------------------------------

$Plugins->call_by_code( 'starrating', array('display' => 'notice' ) );
?>

<div id="wrapper">
  <div id="page">

    <?php
	// ------------------------- BODY HEADER INCLUDED HERE --------------------------
		skin_include( '_body_header.inc.php' );
		// Note: You can customize the default BODY heder by copying the generic
		// /skins/_body_footer.inc.php file into the current skin folder.
	// ------------------------------- END OF FOOTER --------------------------------
	?>

    <!-- =================================== START OF MAIN AREA =================================== -->
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td valign="top" width="100%"><div class="bPosts">

            <?php
            // ------------------------- MESSAGES GENERATED FROM ACTIONS -------------------------
            	messages( array(
                    'block_start'	=> '<div class="action_messages">',
                    'block_end'		=> '</div>',
				) );
            // --------------------------------- END OF MESSAGES ---------------------------------


            // ------------------- PREV/NEXT POST LINKS (SINGLE POST MODE) -------------------
				item_prevnext_links( array(
                    'block_start'	=> '<div class="prevnext_post"><table><tr>',
                    'prev_start'	=> '<td>',
                    'prev_end'		=> '</td>',
                    'next_start'	=> '<td class="right">',
                    'next_end'		=> '</td>',
                    'block_end'		=> '</tr></table></div>',
				) );
			// ------------------------- END OF PREV/NEXT POST LINKS -------------------------


            // ------------------------ TITLE FOR THE CURRENT REQUEST ------------------------
				request_title( array(
					'title_before'		=> '<h2>',
					'title_after'		=> '</h2>',
					'glue'				=> ' - ',
					'title_single_disp' => false,
					'format'			=> 'htmlbody',
				) );
            // ----------------------------- END OF REQUEST TITLE ----------------------------


            // --------------------- PREV/NEXT PAGE LINKS (POST LIST MODE) -------------------
				mainlist_page_links( array(
					'block_start'	=> '<div class="PageLinks" style="padding-top:10px">'.T_('Pages:').' <strong>',
					'block_end'		=> '</strong></div>',
				) );
			// ------------------------- END OF PREV/NEXT PAGE LINKS -------------------------
			?>

            <!-- =================================== START OF POSTS =================================== -->

            <?php
            // --------------------------------- START OF POSTS -------------------------------------
				// Display message if no post:
				display_if_empty(); 
				while( $Item = & mainlist_get_item() )
				{	// For each blog post, do everything below up to the closing curly brace "}"

			// ------------------------------ DATE SEPARATOR ------------------------------
				$MainList->date_if_changed( array(
                    'before'		=> '<div class="PostDate">',
					'after'			=> '</div>',
					'date_format'	=> '#',
				) );
			// --------------------------- END OF DATE SEPARATOR --------------------------
			?>

            <div class="block_item_wrap">
              <div id="<?php $Item->anchor_id() ?>" class="bPost bPost<?php $Item->status_raw() ?>" lang="<?php $Item->lang() ?>">
                <?php $Item->locale_temp_switch(); // Temporarily switch to post locale (useful for multilingual blogs) ?>
                <div class="fieldset_title">
                  <div class="fieldset_title_right">
                    <div class="fieldset_title_bg">

                      <?php
						// ----------------------------- POST TITLE -------------------------------
							$Item->title( array(
									'before'	=> '<h3 class="bTitle">',
									'after'		=> '</h3>',
								));
						// -------------------------- END OF POST TITLE ---------------------------
						?>

                    </div>
                  </div>
                </div>
                <div class="block_item">
                  <div class="bSmallHead">
                    <div class="stars">

                      <?php
						// ---------------------------- STAR RATING -------------------------------
							$Plugins->call_by_code( 'starrating', array(
									'id' => $Item->ID
								));
						// ------------------------- END OF STAR RATING ----------------------------
						?>

                    </div>

                    <?php
                    $Item->issue_time( array(
                            'before'    => '<img src="img/time.gif" alt="'.T_('Issue time').'" />',
                            'after'     => '',
                        ));

                    $Item->author( array(
                            'before'    => ', '.T_('by').' <strong>',
                            'after'     => '</strong>',
                        ) );
    
                    $Item->msgform_link( array(
							'after'     => ' ',
						) );
					
					$Item->views();

/*					$Item->locale_flag( array(
                            'before'    => ' &nbsp; ',
                            'after'     => '',
                        ) );
*/					?>

                    <br />

                    <?php
                    $Item->categories( array(
							'before'			=> T_('Categories').': ',
							'after'				=> ' ',
							'include_main'		=> true,
							'include_other'		=> true,
							'include_external'	=> true,
							'link_categories'	=> true,
						) );
					?>

                  </div>
                  <div class="PostContent">

                    <?php
                    // ---------------------- POST CONTENT INCLUDED HERE ----------------------
						skin_include( '_item_content.inc.php', array(
								'image_size'	=>	'fit-400x320',
							) );
						// Note: You can customize the default item feedback by copying the generic
						// /skins/_item_content.inc.php file into the current skin folder.
                    // -------------------------- END OF POST CONTENT -------------------------

                    // List all tags attached to this post:
					$Item->tags( array(
                            'before'	=>	'<div class="bSmallPrint">'.T_('Tags').': ',
                            'after'		=>	'</div>',
                            'separator'	=>	', ',
                        ) );

// ------------------------- "Post bottom" CONTAINER EMBEDDED HERE --------------------------						
					skin_container( NT_('Post bottom'), array(
							'block_start'	=>	'<div class="PostBottom">',
							'block_end'		=>	'</div>',
						) );
// ----------------------------------- END OF "Post bottom" ---------------------------------
					?>

                    <div class="bSmallPrint">

                      <?php
                        // Permalink:
                        $Item->permanent_link( array(
								'before'	=>	'<div class="permalink"><img src="img/permalink.png" align="top" height="16" width="16" alt="" /> ',
								'after'		=>	'</div>',
								'text'		=>	T_('Permalink'),
								'title'		=>	T_('Permalink'),
                            ) );
    
                        // Link to comments:
                        $Item->feedback_link( array(
                                'type'				=>	'comments',
                                'link_before'		=>	'<span><img src="img/comment.png" align="top" height="16" width="16" alt="" /> ',
								'link_after'		=>	'</span>',
                                'link_text_zero'	=>	T_('Leave a comment'),
                                'link_text_one'		=>	T_('1 comment'),
                                'link_text_more'	=>	T_('%d comments'),
                                'link_title'		=>	T_('Leave a comment'),
                                'use_popup'			=>	false,
                            ) ); 

                        // Link to trackbacks:
                        $Item->feedback_link( array(
                                'type'				=>	'trackbacks',
                                'link_before'		=>	' <span><img src="img/spacer.gif" height="1" width="10" alt="" /><img src="img/trackback.png" align="top" height="16" width="16" alt="" /> ',
                                'link_after'		=>	'</span>',
                                'link_text_zero'	=>	T_('Send a trackback'),
                                'link_text_one'		=>	'#',
                                'link_text_more'	=>	'#',
                                'link_title'		=>	T_('Send a trackback'),
                                'use_popup'			=>	false,
                            ) );
    					
						// Link to backoffice for editing
                        $Item->edit_link( array( 
                                'before'	=>	'<span><img src="img/spacer.gif" height="1" width="10" alt="" /><img src="img/edit.png" align="top" height="16" width="16" alt="" /> ',
								'after'		=>	'</span>',
								'text'		=>	T_('Edit'),
								'title'		=>	T_('Edit'),
                            ) );
						?>

                    </div>

                    <?php
                    // ------------------ FEEDBACK (COMMENTS/TRACKBACKS) INCLUDED HERE ------------------
						skin_include( '_item_feedback.inc.php', array(
								'before_section_title'	=>	'<h4>',
								'after_section_title'	=>	'</h4>',
							) );
						// Note: You can customize the default item feedback by copying the generic
						// /skins/_item_feedback.inc.php file into the current skin folder.
                    // ---------------------- END OF FEEDBACK (COMMENTS/TRACKBACKS) ---------------------

                    locale_restore_previous();	// Restore previous locale (Blog locale)
					?>
                  </div>
                </div>
              </div>
            </div>
            <?php
            } // ---------------------------------- END OF POSTS ----------------------------------------
			
            // -------------------- PREV/NEXT PAGE BOTTOM LINKS (POST LIST MODE) -----------------------
			mainlist_page_links( array(
					'block_start'	=>	'<div class="PageLinks" style="padding-bottom: 10px">'.T_('Pages:').' <strong>',
					'block_end'		=>	'</strong></div>',
					'prev_text'		=>	'&lt;&lt;',
					'next_text'		=>	'&gt;&gt;',
				) );
			// ------------------------- END OF PREV/NEXT PAGE BOTTOM LINKS ----------------------------
			
            // ------------------- MAIN CONTENT TEMPLATE INCLUDED HERE (Based on $disp) ----------------
            skin_include( '$disp$', array(
                    'disp_posts'	=> '',		// We already handled this case above
                    'disp_single'	=> '',		// We already handled this case above
                    'disp_page'		=> '',		// We already handled this case above
                ) );
            // Note: you can customize any of the sub templates included here by
            // copying the matching php file into your skin directory.
            // ----------------------------- END OF MAIN CONTENT TEMPLATE ------------------------------

// ------------------------------------- "After Posts" CONTAINER EMBEDDED HERE -------------------------
			skin_container( NT_('After Posts'), array(
						'block_start'	=>	'<div class="AfterPosts">',
						'block_end'		=>	'</div>',
                ) );			
// ----------------------------------------- END OF "After Posts" CONTAINER ----------------------------
			?>
          </div></td>
        <td valign="top"><!-- ================================== START OF SIDEBAR =================================== -->
          <div class="bSideBar">

            <?php
			// ------------------------- SIDEBAR INCLUDED HERE --------------------------
				skin_include( '_sidebar.inc.php' );
			// ----------------------------- END OF SIDEBAR -----------------------------
			?>

          </div></td>
      </tr>
    </table>
    <!-- ===================================== START OF FOOTER ==================================== -->
    <div id="pageFooter">
      <hr />

      <?php
// ------------------------------------ "Footer" CONTAINER EMBEDDED HERE --------------------------
            skin_container( NT_('Footer'), array() );			
// ---------------------------------------- END OF "Footer" CONTAINER -----------------------------


		// -------------------------------- PAGE FOOTER INCLUDED HERE -----------------------------
			skin_include( '_body_footer.inc.php' );
		// ------------------------------------ END OF PAGE FOOTER --------------------------------
		?>

    </div>
  </div>
</div>

<?php
// ------------------------- HTML FOOTER INCLUDED HERE --------------------------
	skin_include( '_html_footer.inc.php' );
	// Note: You can customize the default HTML footer by copying the
	// _html_footer.inc.php file into the current skin folder.
// ------------------------------- END OF FOOTER --------------------------------
?>