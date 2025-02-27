<?php
/**
 * Course filters
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get the filtered course IDs passed from the main template
$filtered_course_ids = get_query_var('filtered_course_ids', []);

// Function to get only terms assigned to the filtered courses
function get_used_terms($taxonomy, $course_ids) {
    if (empty($course_ids)) {
        return [];
    }
    
    return get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'object_ids' => $course_ids, // Restricts terms to those found in queried posts
    ]);
}

?>

<section class="dashboard-section filter-section">
    <h3><?php _e('Filters', 'rslfranchise'); ?></h3>

    <form method="GET" action="" id="course-filter-form">
        <label for="category"><?php _e('Category', 'rslfranchise'); ?>:</label>
        <select name="category" id="category">
            <option value="">All</option>
            <?php
            $categories = get_used_terms('ld_course_category', $filtered_course_ids);
            foreach ($categories as $category) :
            ?>
                <option value="<?php echo $category->slug; ?>" <?php selected($_GET['category'] ?? '', $category->slug); ?>>
                    <?php echo $category->name; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="level"><?php _e('Level', 'rslfranchise'); ?>:</label>
        <select name="level" id="level">
            <option value="">All</option>
            <?php
            $levels = get_used_terms('ld_course_level', $filtered_course_ids);
            foreach ($levels as $level) :
            ?>
                <option value="<?php echo $level->slug; ?>" <?php selected($_GET['level'] ?? '', $level->slug); ?>>
                    <?php echo $level->name; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="term"><?php _e('Term', 'rslfranchise'); ?>:</label>
        <select name="term" id="term">
            <option value="">All</option>
            <?php
            $terms = get_used_terms('ld_course_term', $filtered_course_ids);
            foreach ($terms as $term) :
            ?>
                <option value="<?php echo $term->slug; ?>" <?php selected($_GET['term'] ?? '', $term->slug); ?>>
                    <?php echo $term->name; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="tag"><?php _e('Tag', 'rslfranchise'); ?>:</label>
        <select name="tag" id="tag">
            <option value="">All</option>
            <?php
            $tags = get_used_terms('ld_course_tag', $filtered_course_ids);
            foreach ($tags as $tag) :
            ?>
                <option value="<?php echo $tag->slug; ?>" <?php selected($_GET['tag'] ?? '', $tag->slug); ?>>
                    <?php echo $tag->name; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="filter-actions">
            <button type="submit"><?php _e('Filter', 'rslfranchise'); ?></button>
            <a href="<?php echo esc_url(remove_query_arg(['category', 'level', 'term', 'tag'])); ?>" class="reset-filters">
                <?php _e('Reset', 'rslfranchise'); ?>
            </a>
        </div>

    </form>
</section>