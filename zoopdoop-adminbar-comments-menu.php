<?php
/*
Plugin Name: Zoopdoop Admin Bar Comments Menu
Plugin URI: http://zoopdoop.com/plugins/adminbar-comments-menu
Description: Adds a menu under the Admin Bar comments icon directly linking to the various comment admins for the current post or for the site
Version: 1.0
Author: Zoopdoop, LLC
Author URI: http://zoopdoop.com/
*/

// already called or not showing admin bar?
if ( !function_exists('zd_abcm_replace_comments') ) {

	// check if the Admin Bar is supported
	if ( version_compare( $wp_version, '3.1', '<' ) ) {
		function zd_abcm_error() {
			echo '<div id="message" class="error fade"><p>The <strong>Zoopdoop Admin Bar Comments Menu</strong> plugin requires at least <strong>WordPress 3.1</strong> to be installed.</p></div>';
		}	
		add_action( 'admin_notices', 'zd_abcm_error' );
		return;
	}
	
	// hook right before the admin bar is rendered if we are showing comments
	if ( !is_network_admin() && !is_user_admin() ) {	
		add_action( 'wp_before_admin_bar_render', 'zd_abcm_replace_comments' );
	}
	
	function zd_abcm_replace_comments() {
		global $wp_admin_bar, $post;
		
		// get the comment counts
		$counts = wp_count_comments();
		
		// get the post comment counts
		if ( is_single() ) {
			$postCounts = wp_count_comments( $post->ID );
			$postQuery = '&p=' . $post->ID;
		} else {
			$postCounts = (object) array(
				"moderated" => false,
				"approved" => false,
				"spam" => false,
				"trash" => false,
				"total_comments" => false,
			);
			$postQuery = '';
		}

		// replace the menu (adding the same id causes it to be replaced in admin-bar.php)
		$wp_admin_bar->add_menu( array(
			'id'    => 'comments',
			'title' => '<span class="ab-icon"></span><span id="ab-awaiting-mod" class="ab-label awaiting-mod pending-count count-' . $counts->moderated . '">' . ($postCounts->moderated !== false ? number_format_i18n($postCounts->moderated) . '/' : '') . number_format_i18n( $counts->moderated ) . '</span>',
			'href'  => admin_url( 'edit-comments.php?comment_status=moderated' . $postQuery )
		) );
		
		$wp_admin_bar->add_menu( array(
			'parent' => 'comments',
			'id'     => 'zd_abcm_comments_pending',
			'title'  => __('Pending') .  zd_abcm_comment_counts($counts->moderated, $postCounts->moderated),
			'href'  => admin_url( 'edit-comments.php?comment_status=moderated' . $postQuery ),
		) );
		
		$wp_admin_bar->add_menu( array(
			'parent' => 'comments',
			'id'     => 'zd_abcm_comments_approved',
			'title'  => __('Approved') .  zd_abcm_comment_counts($counts->approved, $postCounts->approved),
			'href'  => admin_url( 'edit-comments.php?comment_status=approved' . $postQuery ),
		) );
		
		$wp_admin_bar->add_menu( array(
			'parent' => 'comments',
			'id'     => 'zd_abcm_comments_spam',
			'title'  => __('Spam') .  zd_abcm_comment_counts($counts->spam, $postCounts->spam),
			'href'  => admin_url( 'edit-comments.php?comment_status=spam' . $postQuery ),
		) );
		
		$wp_admin_bar->add_menu( array(
			'parent' => 'comments',
			'id'     => 'zd_abcm_comments_trash',
			'title'  => __('Trash') .  zd_abcm_comment_counts($counts->trash, $postCounts->trash),
			'href'  => admin_url( 'edit-comments.php?comment_status=trash' . $postQuery ),
		) );
		
		$wp_admin_bar->add_menu( array(
			'parent' => 'comments',
			'id'     => 'zd_abcm_comments_all',
			'title'  => __('All') .  zd_abcm_comment_counts($counts->total_comments, $postCounts->total_comments),
			'href'  => admin_url( 'edit-comments.php?comment_status=all' . $postQuery ),
		) );
	}
	
	function zd_abcm_comment_counts( $count, $postCount ) {
		// we can't use a span to push the left-padding as it renders poorly in WordPress versions 3.1 and 3.2
		return '&nbsp;&nbsp;(' . ( $postCount !== false ? number_format_i18n( $postCount ) . '/' : '' ) . number_format_i18n( $count ) . ')';
	}
}



