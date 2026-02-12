<?php
/*
Plugin Name: DVC Testimonials Manager
Description: Custom Testimonials Management System with shortcode and slider.
Version: 1.0
Author: Sarthak Keche
*/

if (!defined('ABSPATH')) exit;

/* -------------------------
   CUSTOM POST TYPE
--------------------------*/
function dvc_register_testimonials() {

    $labels = array(
        'name' => 'Testimonials',
        'singular_name' => 'Testimonial',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Testimonial',
        'edit_item' => 'Edit Testimonial',
        'all_items' => 'All Testimonials'
    );

    register_post_type('testimonial', array(
        'labels' => $labels,
        'public' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-format-quote',
        'show_in_rest' => true
    ));
}
add_action('init', 'dvc_register_testimonials');


/* -------------------------
   META BOX
--------------------------*/
function dvc_add_meta_box() {
    add_meta_box(
        'dvc_testimonial_meta',
        'Client Details',
        'dvc_meta_box_callback',
        'testimonial',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'dvc_add_meta_box');

function dvc_meta_box_callback($post) {

    wp_nonce_field('dvc_save_meta', 'dvc_nonce');

    $client_name = get_post_meta($post->ID, '_client_name', true);
    $position = get_post_meta($post->ID, '_client_position', true);
    $company = get_post_meta($post->ID, '_client_company', true);
    $rating = get_post_meta($post->ID, '_client_rating', true);

    ?>

    <p>
        <label>Client Name (Required)</label><br>
        <input type="text" name="client_name" value="<?php echo esc_attr($client_name); ?>" required>
    </p>

    <p>
        <label>Position</label><br>
        <input type="text" name="client_position" value="<?php echo esc_attr($position); ?>">
    </p>

    <p>
        <label>Company</label><br>
        <input type="text" name="client_company" value="<?php echo esc_attr($company); ?>">
    </p>

    <p>
        <label>Rating</label><br>
        <select name="client_rating">
            <?php for($i=1;$i<=5;$i++): ?>
                <option value="<?php echo $i; ?>" <?php selected($rating,$i); ?>>
                    <?php echo $i; ?> Star
                </option>
            <?php endfor; ?>
        </select>
    </p>

<?php
}

function dvc_save_meta($post_id) {

    if (!isset($_POST['dvc_nonce']) || !wp_verify_nonce($_POST['dvc_nonce'], 'dvc_save_meta'))
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    update_post_meta($post_id, '_client_name', sanitize_text_field($_POST['client_name']));
    update_post_meta($post_id, '_client_position', sanitize_text_field($_POST['client_position']));
    update_post_meta($post_id, '_client_company', sanitize_text_field($_POST['client_company']));
    update_post_meta($post_id, '_client_rating', intval($_POST['client_rating']));
}
add_action('save_post', 'dvc_save_meta');


/* -------------------------
   SHORTCODE
--------------------------*/
function dvc_testimonials_shortcode($atts) {

    $atts = shortcode_atts(array(
        'count' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ), $atts);

    $query = new WP_Query(array(
        'post_type' => 'testimonial',
        'posts_per_page' => intval($atts['count']),
        'orderby' => sanitize_text_field($atts['orderby']),
        'order' => sanitize_text_field($atts['order'])
    ));

    ob_start();

    if ($query->have_posts()) :
        ?>

        <div class="dvc-testimonials">

            <?php while($query->have_posts()) : $query->the_post(); 

                $name = get_post_meta(get_the_ID(), '_client_name', true);
                $position = get_post_meta(get_the_ID(), '_client_position', true);
                $company = get_post_meta(get_the_ID(), '_client_company', true);
                $rating = get_post_meta(get_the_ID(), '_client_rating', true);
            ?>

            <div class="dvc-item">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('thumbnail'); ?>
                <?php endif; ?>

                <div class="stars">
                    <?php for($i=1;$i<=5;$i++): ?>
                        <?php echo ($i <= $rating) ? '★' : '☆'; ?>
                    <?php endfor; ?>
                </div>

                <p><?php echo wp_kses_post(get_the_content()); ?></p>

                <h4><?php echo esc_html($name); ?></h4>
                <small><?php echo esc_html($position . ' - ' . $company); ?></small>

            </div>

            <?php endwhile; ?>

        </div>

        <?php
    endif;

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('testimonials', 'dvc_testimonials_shortcode');


/* -------------------------
   STYLING
--------------------------*/
function dvc_testimonial_styles() {
    ?>
    <style>
    .dvc-testimonials {
        display:grid;
        grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
        gap:20px;
        margin-top:30px;
    }
    .dvc-item {
        background:#ffffff;
        padding:20px;
        border-radius:15px;
        box-shadow:0 10px 25px rgba(0,0,0,0.1);
        text-align:center;
    }
    .dvc-item img {
        border-radius:50%;
        margin-bottom:10px;
    }
    .stars {
        color:#f4b400;
        font-size:18px;
        margin:10px 0;
    }
    .dvc-item h4 {
        margin-top:15px;
        font-weight:bold;
    }
    </style>
    <?php
}
add_action('wp_head', 'dvc_testimonial_styles');
