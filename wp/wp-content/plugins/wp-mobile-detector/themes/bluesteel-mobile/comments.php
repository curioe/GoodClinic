<?php if (comments_open()) : ?>

	<h2>Leave a Comment</h2>
	
  <?php if ( get_option('comment_registration') && !$user_ID ) : ?>
  
  	<p class="websitez-comments-p">You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
  	
  <?php else : ?>
  
  	<?php comment_form(); ?>
	
	<?php endif; ?>

<?php endif; ?>

<?php if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');
	if (!empty($post->post_password)) { 
    if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {
?>

			<h2><?php _e('Password Protected'); ?></h2>
			<p><?php _e('Enter the password to view comments.'); ?></p>

<?php
			return;
		}
	}
?>

<?php if (have_comments()) : ?>

  <h3><?php comments_number('No Comments', '1 Comment', '% Comments' );?> to &#8220;<?php the_title(); ?>&#8221;</h3>
	<div class="websitez-comments-navigation">
		<p><?php previous_comments_link() ?></p>
		<p><?php next_comments_link() ?></p>
	</div>
		
	<?php wp_list_comments( array( 'callback' => 'websitez_comment' ) ); ?>

<?php else : ?>
	
	<?php if (!comments_open()) : ?>

  	<p class="nocomments">Comments are closed.</p>

  <?php endif; ?>

<?php endif; ?>