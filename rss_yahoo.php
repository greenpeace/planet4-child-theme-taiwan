<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */

header( 'Content-Type: application/rss+xml; charset=' . get_option( 'blog_charset' ), true );

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';

/**
 * Fires between the xml and rss tags in a feed.
 *
 * @since 4.0.0
 *
 * @param string $context Type of feed. Possible values include 'rss2', 'rss2-comments',
 *                        'rdf', 'atom', and 'atom-comments'.
 */
do_action( 'rss_tag_pre', 'rss2' );
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:media="http://search.yahoo.com/mrss/"

	<?php
	/**
	 * Fires at the end of the RSS root to add namespaces.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_ns' );
	?>
>

<channel>
	<title><?php wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss( 'url' ); ?></link>
	<description><?php bloginfo_rss( 'description' ); ?></description>
	<lastBuildDate><?php echo get_feed_build_date('D, d M Y H:i:s +0800'); ?></lastBuildDate>
	<sy:updatePeriod>
	<?php
		$duration = 'hourly';

		/**
		 * Filters how often to update the RSS feed.
		 *
		 * @since 2.1.0
		 *
		 * @param string $duration The update period. Accepts 'hourly', 'daily', 'weekly', 'monthly',
		 *                         'yearly'. Default 'hourly'.
		 */
		echo apply_filters( 'rss_update_period', $duration );
	?>
	</sy:updatePeriod>
	<sy:updateFrequency>
	<?php
		$frequency = '1';

		/**
		 * Filters the RSS update frequency.
		 *
		 * @since 2.1.0
		 *
		 * @param string $frequency An integer passed as a string representing the frequency
		 *                          of RSS updates within the update period. Default '1'.
		 */
		echo apply_filters( 'rss_update_frequency', $frequency );
	?>
	</sy:updateFrequency>
	<?php
	/**
	 * Fires at the end of the RSS2 Feed Header.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_head' );

	while ( have_posts() ) :
		the_post();
		?>
	<item>
		<title><?php the_title_rss(); ?></title>
		<link><?php the_permalink_rss(); ?></link>
		<?php if ( get_comments_number() || comments_open() ) : ?>
			<comments><?php comments_link_feed(); ?></comments>
		<?php endif; ?>

		<dc:creator><![CDATA[<?php echo get_post_meta( $post->ID, 'p4_author_override', true ); ?>]]></dc:creator>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0800', get_post_time( 'Y-m-d H:i:s', false ), false ); ?></pubDate>
		<?php the_category_rss( 'rss2' ); ?>
		<guid isPermaLink="false"><?php the_guid(); ?></guid>

		<?php if ( has_post_thumbnail($post->ID) ) : ?>
			<media:thumbnail url="<?php echo get_the_post_thumbnail_url($post->ID, "small")?>" />
			<media:content url="<?php echo get_the_post_thumbnail_url($post->ID, "small")?>" medium="image" />
		<?php endif; ?>

		<?php if ( get_option( 'rss_use_excerpt' ) ) : ?>
			<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
		<?php else : ?>
			<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
			<?php $content = get_the_content_feed( 'rss2' ); ?>
			<?php if ( strlen( $content ) > 0 ) : ?>
				<?php
					$yahoo_content = $content;

					// remove blockquote
					$yahoo_content = preg_replace('/<blockquote>\s*(.*?)\s*<\/blockquote>/', "<p>$1</p>", $yahoo_content);
					$yahoo_content = preg_replace('/<p><p>(.*?)<\/p><\/p>/', "<p>$1</p>", $yahoo_content); // to fix blockquote=>p

					// yahoo said that they don't want h3
					$yahoo_content = preg_replace('/<h2>\s*(.*?)\s*<\/h2>/', "<h1>$1</h1>", $yahoo_content); // upgrade h2 to h1
					$yahoo_content = preg_replace('/<h3>\s*(.*?)\s*<\/h3>/', "<h2>$1</h2>", $yahoo_content); // upgrade h3 to h2.

					// yahoo don't want the img links
					$yahoo_content = preg_replace('/<a.*?(<img.*?>)<\/a>/', "$1", $yahoo_content);

					// remove format html tags
					$yahoo_content = preg_replace('/class="[^\"]+"\s{0,1}/', "", $yahoo_content);
					$yahoo_content = preg_replace('/style="[^\"]+"\s{0,1}/', "", $yahoo_content);
					$yahoo_content = preg_replace('/<span\s*>([^<]+)<\/span>/', "$1", $yahoo_content);
					$yahoo_content = preg_replace('/<figcaption\s*>([^<]+)<\/figcaption>/', "", $yahoo_content);
					$yahoo_content = preg_replace('/<figure[^<]*>/', "<p>", $yahoo_content);
					$yahoo_content = preg_replace('/<\/figure>/', "</p>", $yahoo_content);
					$yahoo_content = preg_replace('/width="(\d+)"/', 'width="1200"', $yahoo_content);
					$yahoo_content = preg_replace('/height="(\d+)"/', 'height="auto"', $yahoo_content);

					// yahoo accepts only two extra links
					$yahoo_content = preg_replace(
						'/<h2>延伸閱讀：<\/h2>\s*<ul>\s*<li>(.*?)<\/li>\s*<li>(.*?)<\/li>([\s\S\n\r]*)<\/ul>/u',
						'<p class="read-more-vendor"><span>更多綠色和平文章</span><br/>$1<br/>$2</p>',
						$yahoo_content);
				?>
				<content:encoded><![CDATA[<?php echo $yahoo_content; ?>]]></content:encoded>
			<?php else : ?>
				<content:encoded><![CDATA[<?php the_excerpt_rss(); ?>]]></content:encoded>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( get_comments_number() || comments_open() ) : ?>
			<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link( null, 'rss2' ) ); ?></wfw:commentRss>
			<slash:comments><?php echo get_comments_number(); ?></slash:comments>
		<?php endif; ?>

		<?php rss_enclosure(); ?>

		<?php
		/**
		 * Fires at the end of each RSS2 feed item.
		 *
		 * @since 2.0.0
		 */
		do_action( 'rss2_item' );
		?>
	</item>
	<?php endwhile; ?>
</channel>
</rss>
