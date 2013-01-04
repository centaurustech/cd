<?php



$GLOBALS['content_width'] = 500; 

function mytheme_comment($comment, $args, $depth) {
$GLOBALS['comment'] = $comment; ?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
<div id="comment-<?php comment_ID(); ?>">
<div class="comment-author vcard">
<?php echo get_avatar($comment,$size='36',$default='<path_to_url>' ); ?>
 
<?php printf(__('<cite class="fn">%s</cite>  <span class="says">says:</span>'), get_comment_author_link()) ?>
</div>
<?php if ($comment->comment_approved == '0') : ?>
<em><?php _e('Your comment is awaiting moderation.') ?></em>
<br />
<?php endif; ?>
 
<div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars(get_comment_link( $comment->comment_ID )) ?>">
<?php printf(__('%1$s at %2$s'), get_comment_date(),get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'  ','') ?></div>
 
<?php comment_text() ?>
<?php if($args['max_depth']!=$depth) { ?>
<div class="reply">
<?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
</div>
<?php } ?>
</div>
<?php
}

require_once(TEMPLATEPATH . '/dashboard.php'); 

add_theme_support( 'automatic-feed-links' );

if ( function_exists('register_sidebar') )
	register_sidebar(array(
	'name'=>'sidebar1',
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h3>  ',
	'after_title' => '</h3>',
));

register_sidebar(array('name'=>'sidebar2',
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h3> ',
	'after_title' => '</h3>',
));

register_sidebar(array('name'=>'sidebar3',
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h3>  ',
	'after_title' => '</h3>',
));

register_sidebar(array('name'=>'sidebar4',
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h3>  ',
	'after_title' => '</h3>',
));

register_sidebar(array('name'=>'sidebar5',
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '<h3> ',
	'after_title' => '</h3>',
));


function get_average_readers($feed_id,$interval = 7){
	$today = date('Y-m-d', strtotime("now"));
	$ago = date('Y-m-d', strtotime("-".$interval." days"));
	$feed_url="https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=".$feed_id."&dates=".$ago.",".$today;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $feed_url);
	$data = curl_exec($ch);
	curl_close($ch);
	$xml = new SimpleXMLElement($data);
	$fb = $xml->feed->entry['circulation'];

	$nb = 0;
	foreach($xml->feed->children() as $circ){
		$nb += $circ['circulation'];
	}

	return round($nb/$interval);
}


function new_excerpt_length($length) {
	return 20;
}
add_filter('excerpt_length', 'new_excerpt_length');


function get_author_bio ($content=''){
    global $post;

    $post_author_name=get_the_author_meta("display_name");
    $post_author_description=get_the_author_meta("description");
    $html="<div class='clearfix' id='about_author'>\n";
	 $html.="<h1>Author</h1>";
    $html.="<img width='80' height='80' class='avatar' src='http://www.gravatar.com/avatar.php?gravatar_id=".md5(get_the_author_email()). "&default=".urlencode($GLOBALS['defaultgravatar'])."&size=80&r=PG' alt='PG'/>\n";
    $html.="<div class='author_text'>\n";
    $html.="<h3>$post_author_name</h3>\n";
    $html.= "<p>$post_author_description.</p>\n";
    $html.="</div>\n";
    $html.="</div>\n";
    $html.="<div class='clear'></div>\n";
    $content .= $html;

    return $content;
}

if (is_single()) {
add_filter(�the_content�, �get_author_bio�);
}





add_action( 'init', 'register_my_menu' );

function register_my_menu() {
	register_nav_menu( 'primary-menu', __( 'Primary Menu' ) );
}






function my_new_contactmethods( $contactmethods ) {
	// Add Twitter
	$contactmethods['twitter'] = 'Twitter';
	//add Facebook
	$contactmethods['facebook'] = 'Facebook';

return $contactmethods;
}
	add_filter('user_contactmethods','my_new_contactmethods',10,1);

function display_pages() {
echo '<div class="navigation">';
wp_list_pages('title_li=&depth=1&number=5');
echo 
'</div>';
}

function display_categories() {
echo '<div class="navigation">';
wp_list_categories('title_li=&depth=1&number=5');
echo '
</div>';
}

if (!is_admin()) add_action( 'wp_print_scripts', 'wordfinder_javascript' );
function wordfinder_javascript( ) {
wp_enqueue_script('wordfinder_js', get_bloginfo('template_directory').'/javascript/wordfinder_js.js', array( 'jquery' ) );
}

add_theme_support( 'post-thumbnails' );
add_image_size('first', 150, 150, true);
add_image_size('second', 90, 90, true);
?>
