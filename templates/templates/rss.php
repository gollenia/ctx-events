<?php
/*
 * RSS Page
 * This page handles the even RSS feed.
 * You can override this file by and copying it to yourthemefolder/plugins/events/templates/ and modifying as necessary.
 * 
 */

use Contexis\Events\Collections\EventCollection;

header ( "Content-type: application/rss+xml; charset=UTF-8" );
echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo esc_html(get_option ( 'dbem_rss_main_title' )); ?></title>
		<link><?php	echo EM_URI; ?></link>
		<description><?php echo esc_html(get_option('dbem_rss_main_description')); ?></description>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<pubDate><?php echo date('D, d M Y H:i:s +0000', get_option('em_last_modified')); ?></pubDate>
		<atom:link href="<?php echo esc_attr(EM_RSS_URI); ?>" rel="self" type="application/rss+xml" />
		<?php
		$description_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", get_option ( 'dbem_rss_description_format' ) ) );
        $rss_limit = get_option('dbem_rss_limit');
        $page_limit = $rss_limit > 50 || !$rss_limit ? 50 : $rss_limit; //set a limit of 50 to output at a time, unless overall limit is lower		
		$args = !empty($args) ? $args:array(); /* @var $args array */
		$args = array_merge(array('scope'=>get_option('dbem_rss_scope'), 'owner'=>false, 'limit'=>$page_limit, 'page'=>1, 'order'=>get_option('dbem_rss_order'), 'orderby'=>get_option('dbem_rss_orderby')), $args);
		$args = apply_filters('em_rss_template_args',$args);
		$events = EventCollection::find( $args );
		$count = 0;
		while( count($events) > 0 ){
			foreach ( $events as $event ) {
				/* @var $event Event */
				$description = EventView::render($event, get_option ( 'dbem_rss_description_format' ), "rss");
				$description = ent2ncr(convert_chars($description)); //Some RSS filtering
				$event_url = $event->get_permalink;
				?>
				<item>
					<title><?php echo $event->event_name; ?></title>
					<link><?php echo $event_url; ?></link>
					<guid><?php echo $event_url; ?></guid>
					<pubDate><?php echo $event->start(true)->format('D, d M Y H:i:s +0000'); ?></pubDate>
					<description><![CDATA[<?php echo $description; ?>]]></description>
				</item>
				<?php
				$count++;
			}
        	if( $rss_limit != 0 && $count >= $rss_limit ){ 
        	    //we've reached our limit, or showing one event only
        	    break;
        	}else{
        	    //get next page of results
        	    $args['page']++;
        		$events = EventCollection::find( $args );
        	}
		}
		?>
		
	</channel>
</rss>