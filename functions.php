<?php
function my_theme_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );



function enqueue_ajax_script() {
    wp_enqueue_script('ajax-script', get_stylesheet_directory_uri() . '/js/ajax-script.js', array('jquery'), '1.6', true);
    // Pass the AJAX URL to the script
    wp_localize_script('ajax-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_script',200);


function load_more_posts() {
    // Check the requested page number and category
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    // Set up query arguments
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 6,
        'paged' => $page, // Use the requested page
    );
    if (!empty($category)) {
        // If a category is selected, filter by that category
        $args['category_name'] = $category;
    }
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // Output the posts
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="post">
               <?php if (has_post_thumbnail()) : ?>
                  <div class="post-thumbnail">
                    <?php the_post_thumbnail('medium'); // Display the featured image ?>
                 </div>
              <?php else : ?>
                <div class="post-thumbnail">
                    <img src="https://olabbd.com/wp-content/uploads/2024/02/sublime.png" alt="Demo Image"> 
                </div>
              <?php endif; ?>
              <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
               <!-- <p><?php the_excerpt(); ?></p> -->
            </div>
            <?php
        }
        // Output pagination links
        $big = 999999999; // need an unlikely integer
        $pagination = paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?page=%#%',
            'current' => $page,
            'total' => $query->max_num_pages,
            'type' => 'array', // We need pagination links as an array
        ));

        if ($pagination) {
            echo '<div class="pagination">';
            foreach ($pagination as $page_link) {
                echo '<span class="page-link">' . $page_link . '</span>';
            }
            echo '</div>';
        }
    } else {
        echo ''; // Return an empty string if no posts found
    }

    wp_die(); // Properly terminate AJAX request
}

add_action('wp_ajax_load_more_posts', 'load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'load_more_posts');


function category_filter_load_more_shortcode() {
    // Enqueue scripts and pass AJAX URL
    wp_enqueue_script('category-load-more', get_template_directory_uri() . '/js/category-load-more.js', array('jquery'), null, true);
    wp_localize_script('category-load-more', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

    // Get all categories
    $categories = get_categories();

    // HTML structure for category filters, post container, and load more button
    ob_start();
    ?>
    <div id="category-filters">
        <!-- 'All' Category Link -->
        <a href="#" class="category-filter nav-active" data-category="">All</a>

        <?php foreach ($categories as $category) : ?>
            <a href="#" class="category-filter" data-category="<?php echo esc_attr($category->slug); ?>">
                <?php echo esc_html($category->name); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div id="post-container">
        <!-- Posts will be loaded here -->
    </div>

    <button id="load-more" style="display: none;">Load More</button>
    <?php
    return ob_get_clean();
}
add_shortcode('category_filter_load_more', 'category_filter_load_more_shortcode');

