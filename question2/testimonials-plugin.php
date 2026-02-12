<?php
/*
Plugin Name: Testimonials Manager
Description: Custom testimonials system
Version: 1.0
*/

function create_testimonial_post() {
register_post_type('testimonial',
array(
'labels'=>array('name'=>'Testimonials'),
'public'=>true,
'supports'=>array('title','editor','thumbnail'),
'menu_icon'=>'dashicons-format-quote'
));
}
add_action('init','create_testimonial_post');


function testimonial_shortcode($atts){
$args=shortcode_atts(array(
'count'=>-1,
'orderby'=>'date',
'order'=>'DESC'
),$atts);

$query=new WP_Query(array(
'post_type'=>'testimonial',
'posts_per_page'=>$args['count'],
'orderby'=>$args['orderby'],
'order'=>$args['order']
));

$output="<div class='testimonials'>";
while($query->have_posts()){ $query->the_post();
$output.="<div><h3>".get_the_title()."</h3><p>".get_the_content()."</p></div>";
}
$output.="</div>";
return $output;
}
add_shortcode('testimonials','testimonial_shortcode');
