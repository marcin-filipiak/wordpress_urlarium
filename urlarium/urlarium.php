<?php
/**
 * Plugin Name: URLarium
 * Description: Website directory plugin – categorize and list websites with name, description, and link.
 * Version: 1.0
 * Author: Marcin Filipiak
 * License: GPLv2 or later
 * Text Domain: urlarium
 * Domain Path: /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

// Load translations
function urlarium_load_textdomain() {
    load_plugin_textdomain( 'urlarium', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
}
add_action( 'plugins_loaded', 'urlarium_load_textdomain' );

/**
 * Register custom post type with Editor disabled
 */
function urlarium_register_post_type() {
    register_post_type(
        'urlarium_link',
        array(
            'labels'       => array(
                'name'               => __( 'URLarium', 'urlarium' ),
                'singular_name'      => __( 'Website', 'urlarium' ),
                'add_new_item'       => __( 'Add New Website', 'urlarium' ),
                'edit_item'          => __( 'Edit Website', 'urlarium' ),
                'new_item'           => __( 'New Website', 'urlarium' ),
                'view_item'          => __( 'View Website', 'urlarium' ),
                'search_items'       => __( 'Search Websites', 'urlarium' ),
                'not_found'          => __( 'No websites found.', 'urlarium' ),
            ),
            'public'       => true,
            'has_archive'  => false,
            'menu_icon'    => 'dashicons-admin-site-alt3',
            'menu_position'=> 5,
            'show_in_menu' => true,
            'supports'     => array( 'title' ),
            'rewrite'      => false,
            'show_ui'      => true,
            'show_in_rest' => false,
            'taxonomies'   => array( 'urlarium_category' ),
        )
    );
}
add_action( 'init', 'urlarium_register_post_type' );

/**
 * Register taxonomy
 */
function urlarium_register_taxonomy() {
    register_taxonomy(
        'urlarium_category',
        'urlarium_link',
        array(
            'labels'       => array(
                'name'              => __( 'URLarium Categories', 'urlarium' ),
                'singular_name'     => __( 'Category', 'urlarium' ),
                'search_items'      => __( 'Search Categories', 'urlarium' ),
                'all_items'         => __( 'All Categories', 'urlarium' ),
                'edit_item'         => __( 'Edit Category', 'urlarium' ),
                'update_item'       => __( 'Update Category', 'urlarium' ),
                'add_new_item'      => __( 'Add New Category', 'urlarium' ),
                'new_item_name'     => __( 'New Category Name', 'urlarium' ),
                'menu_name'         => __( 'Categories', 'urlarium' ),
            ),
            'show_ui'      => true,
            'show_in_menu' => 'edit.php?post_type=urlarium_link',
            'hierarchical' => true,
            'show_in_rest' => true,
        )
    );
}
add_action( 'init', 'urlarium_register_taxonomy' );

/**
 * Add Website Details metabox
 */
function urlarium_add_custom_meta_boxes() {
    add_meta_box(
        'urlarium_meta',
        __( 'Website Details', 'urlarium' ),
        'urlarium_meta_callback',
        'urlarium_link',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'urlarium_add_custom_meta_boxes' );

/**
 * Metabox callback: output fields + nonce
 */
function urlarium_meta_callback( $post ) {
    wp_nonce_field( 'urlarium_save_meta', 'urlarium_nonce' );
    $url  = get_post_meta( $post->ID, '_urlarium_url', true );
    $desc = get_post_meta( $post->ID, '_urlarium_desc', true );
    ?>
    <p>
        <label for="urlarium_url"><?php esc_html_e( 'Website URL:', 'urlarium' ); ?></label><br>
        <input type="url" id="urlarium_url" name="urlarium_url" value="<?php echo esc_attr( $url ); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="urlarium_desc"><?php esc_html_e( 'Website Description:', 'urlarium' ); ?></label><br>
        <textarea id="urlarium_desc" name="urlarium_desc" rows="4" style="width:100%;"><?php echo esc_textarea( $desc ); ?></textarea>
    </p>
    <?php
}

/**
 * Save custom meta; with nonce & unslash/sanitize
 */
function urlarium_save_meta( $post_id ) {
    if (
        ! isset( $_POST['urlarium_nonce'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['urlarium_nonce'] ) ),
            'urlarium_save_meta'
        )
    ) {
        return;
    }


    if ( isset( $_POST['urlarium_url'] ) ) {
        update_post_meta( $post_id, '_urlarium_url', sanitize_text_field( wp_unslash( $_POST['urlarium_url'] ) ) );
    }
    if ( isset( $_POST['urlarium_desc'] ) ) {
        update_post_meta( $post_id, '_urlarium_desc', sanitize_textarea_field( wp_unslash( $_POST['urlarium_desc'] ) ) );
    }
}
add_action( 'save_post', 'urlarium_save_meta' );

/**
 * Shortcode to display categories and links
 */
function urlarium_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'category' => '' ), $atts, 'urlarium' );
    ob_start();

    $categories = get_terms( array(
        'taxonomy'   => 'urlarium_category',
        'hide_empty' => false,
    ) );

    if ( ! $atts['category'] ) {
        echo '<ul class="urlarium-category-list">';
        foreach ( $categories as $cat ) {
            // Tworzymy nonce dla linku GET
            $nonce = wp_create_nonce( 'urlarium_category_nonce' );
            $link = add_query_arg( array(
                'urlarium_category' => sanitize_title( $cat->slug ),
                'urlarium_nonce'    => $nonce,
            ) );
            echo '<li><a href="'. esc_url( $link ) .'">'. esc_html( $cat->name ) .'</a></li>';
        }
        echo '</ul>';
    }

    $selected = '';

    // Weryfikacja nonce dla GET przed użyciem parametru
    if ( isset( $_GET['urlarium_nonce'] ) && isset( $_GET['urlarium_category'] ) ) {
        $nonce = sanitize_text_field( wp_unslash( $_GET['urlarium_nonce'] ) );
        $category = sanitize_text_field( wp_unslash( $_GET['urlarium_category'] ) );

        if ( wp_verify_nonce( $nonce, 'urlarium_category_nonce' ) ) {
            $selected = $category;
        } else {
            // Brak poprawnego nonce — nie ustawiamy kategorii z GET
            $selected = '';
        }
    }

    // Jeśli shortcode ma atrybut category, to nadpisujemy
    if ( $atts['category'] ) {
        $selected = $atts['category'];
    }

    if ( $selected ) {
        $query = new WP_Query( array(
            'post_type'      => 'urlarium_link',
            'tax_query'      => array( array( 'taxonomy'=>'urlarium_category','field'=>'slug','terms'=>$selected ) ),
            'posts_per_page' => -1,
        ) );

        if ( $query->have_posts() ) {
            echo '<div class="urlarium-website-list">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $url  = get_post_meta( get_the_ID(), '_urlarium_url', true );
                $desc = get_post_meta( get_the_ID(), '_urlarium_desc', true );
                echo '<div class="urlarium-entry">';
                echo '<h4><a href="'. esc_url( $url ) .'" target="_blank">'. esc_html( get_the_title() ) .'</a></h4>';
                echo '<p>'. esc_html( $desc ) .'</p>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>'. esc_html__( 'No websites found in this category.', 'urlarium' ) .'</p>';
        }
        wp_reset_postdata();
    }

    return ob_get_clean();
}

add_shortcode( 'urlarium', 'urlarium_shortcode' );

/**
 * Enqueue external CSS
 */
function urlarium_enqueue_styles() {
    wp_enqueue_style( 'urlarium-style', plugins_url( 'style.css', __FILE__ ), array(), '1.0' );
}
add_action( 'wp_enqueue_scripts', 'urlarium_enqueue_styles' );

/**
 * Admin Help submenu
 */
add_action( 'admin_menu', function(){
    add_submenu_page(
        'edit.php?post_type=urlarium_link',
        __( 'URLarium Help', 'urlarium' ),
        __( 'Help', 'urlarium' ),
        'manage_options',
        'urlarium_help',
        'urlarium_render_help_page'
    );
});

/**
 * Render Help page
 */
function urlarium_render_help_page(){
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'How to Use URLarium', 'urlarium' ); ?></h1>
        <p><?php esc_html_e( 'To display the list of website categories, insert this shortcode:', 'urlarium' ); ?></p>
        <code>[urlarium]</code>
        <p><?php esc_html_e( 'To display a specific category:', 'urlarium' ); ?></p>
        <code>[urlarium category="category-name"]</code>
        <p><?php esc_html_e( 'Add websites in the URLarium admin menu, each can have title, URL, description and category.', 'urlarium' ); ?></p>
    </div>
    <?php
}


// UNINSTALL

register_uninstall_hook(__FILE__, 'urlarium_uninstall_cleanup');

function urlarium_uninstall_cleanup() {
    file_put_contents( WP_CONTENT_DIR . '/urlarium_uninstall.log', "Uninstall started\n", FILE_APPEND );

    $links = get_posts(array(
        'post_type' => 'urlarium_link',
        'numberposts' => -1,
        'post_status' => 'any',
        'fields' => 'ids',
    ));
    file_put_contents( WP_CONTENT_DIR . '/urlarium_uninstall.log', "Found " . count($links) . " posts\n", FILE_APPEND );
    foreach ($links as $link_id) {
        wp_delete_post($link_id, true);
        file_put_contents( WP_CONTENT_DIR . '/urlarium_uninstall.log', "Deleted post $link_id\n", FILE_APPEND );
    }

    $terms = get_terms(array(
        'taxonomy' => 'urlarium_category',
        'hide_empty' => false,
        'fields' => 'ids',
    ));
    file_put_contents( WP_CONTENT_DIR . '/urlarium_uninstall.log', "Found " . count($terms) . " terms\n", FILE_APPEND );
    foreach ($terms as $term_id) {
        wp_delete_term($term_id, 'urlarium_category');
        file_put_contents( WP_CONTENT_DIR . '/urlarium_uninstall.log', "Deleted term $term_id\n", FILE_APPEND );
    }

    delete_option('urlarium_delete_data_on_uninstall');
    file_put_contents( WP_CONTENT_DIR . '/urlarium_uninstall.log', "Option deleted\n", FILE_APPEND );
}

