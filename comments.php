<?php
/**
 * Comments.
 *
 * WHY THIS FILE HAD TO EXIST
 *
 * single.php calls comments_template(). If the theme has no comments.php,
 * WordPress falls back to a compatibility file it has marked deprecated since
 * version 3.0 and logs:
 *
 *   PHP Deprecated: File Theme without comments.php is deprecated since 3.0.0
 *
 * on every post view. Any customer running WP_DEBUG sees it, and any reviewer
 * looking at the error log fails the theme for it.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

/*
 * Never render comments for a post still being password-checked — the comments
 * would leak content the password is protecting.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="acreage-comments">

	<?php if ( have_comments() ) : ?>

		<h2 class="acreage-comments__title">
			<?php
			$acreage_count = get_comments_number();

			printf(
				esc_html(
					/* translators: %s: comment count. */
					_n( '%s comment', '%s comments', $acreage_count, 'acreage' )
				),
				esc_html( number_format_i18n( $acreage_count ) )
			);
			?>
		</h2>

		<ol class="acreage-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 56,
				)
			);
			?>
		</ol>

		<?php
		/*
		 * Only print pagination when there is more than one page. The nav
		 * landmark is labelled because a page can contain several.
		 */
		the_comments_navigation(
			array(
				'prev_text' => esc_html__( 'Older comments', 'acreage' ),
				'next_text' => esc_html__( 'Newer comments', 'acreage' ),
				'screen_reader_text' => esc_html__( 'Comments navigation', 'acreage' ),
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="acreage-comments__closed"><?php esc_html_e( 'Comments are closed.', 'acreage' ); ?></p>
		<?php endif; ?>

	<?php endif; ?>

	<?php
	/*
	 * The form. Every field gets a real <label> rather than a placeholder:
	 * a placeholder vanishes the moment someone types, which leaves screen
	 * reader and low-vision users with an unlabelled box.
	 */
	comment_form(
		array(
			'class_form'         => 'acreage-comments__form',
			'title_reply'        => esc_html__( 'Leave a comment', 'acreage' ),
			'title_reply_to'     => esc_html__( 'Reply to %s', 'acreage' ),
			'cancel_reply_link'  => esc_html__( 'Cancel reply', 'acreage' ),
			'label_submit'       => esc_html__( 'Post comment', 'acreage' ),
			'comment_field'      => sprintf(
				'<p class="comment-form-comment"><label for="comment">%1$s</label><textarea id="comment" name="comment" cols="45" rows="6" required></textarea></p>',
				esc_html__( 'Comment', 'acreage' )
			),
		)
	);
	?>

</div>
