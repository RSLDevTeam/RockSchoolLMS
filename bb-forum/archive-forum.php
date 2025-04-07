<?php
/**
 * Template Name: Forum Page
 */
get_header();

// Initialize variables for stats
$totol_forums = 0;
$total_replies = 0;
$total_topics = 0;
$unanswered_forums = [];
$forums = get_posts(array(
  'post_type'      => 'forum',
  'posts_per_page' => -1,
  'orderby'        => 'menu_order',
  'order'          => 'ASC'
));
if (!empty($forums)) :
  foreach ($forums as $forum){
    $totol_forums++;
    $total_replies += bbp_get_forum_reply_count($forum->ID);
    $total_topics += bbp_get_forum_topic_count($forum->ID);

    // Get topics within this forum
    $topics = get_posts(array(
      'post_type'      => 'topic',
      'posts_per_page' => -1,
      'post_parent'    => $forum->ID,
      'meta_key'       => 'bbp_reply_count',
      'meta_value'     => '0' // Unanswered topics only
    ));

    // If all topics in a forum have zero replies, consider the forum unanswered
    if (empty($topics)) {
        $unanswered_forums[] = get_post($forum->ID);
    }
  }
endif;
?>
<main id="primary" class="site-main forum-page">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="forum-container">
            <h1><?php _e('Forum Discussions', 'rslfranchise'); ?></h1>

            <?php if (function_exists('bbp_breadcrumb')) : ?>
                <nav class="breadcrumb">
                    <?php bbp_breadcrumb(array(
                        'sep' => ' » ', // Separator between links
                        'home_text' => 'Home', // Custom home text
                    )); ?>
                </nav>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-3">
                    <section class="dashboard-section">
                        <h3><?php _e('Forum Statistics', 'rslfranchise'); ?></h3>
                        <ul class="list-group forum-stats">
                            <li class="list-group-item d-flex justify-content-between">
                                <div><strong>Forums</strong></div>
                                <div class="list-numbers"><?php echo $totol_forums; ?></div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <div><strong>Topics</strong></div>
                                <div class="list-numbers"><?php echo $total_topics; ?></div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <div><strong>Replies</strong></div>
                                <div class="list-numbers"><?php echo $total_replies; ?></div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <div><strong>Users</strong></div>
                                <div class="list-numbers"><?php echo function_exists('bbp_get_total_users') ? bbp_get_total_users() : 'N/A'; ?></div>
                            </li>
                        </ul>
                    </section>
                </div>

                <div class="col-lg-9">
                    <section>
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="forumTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="popular-tab" data-bs-toggle="tab" data-bs-target="#popular" role="tab" aria-controls="popular" aria-selected="true"><?php _e('Popular', 'rslfranchise'); ?></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="featured-tab" data-bs-toggle="tab" data-bs-target="#featured" role="tab" aria-controls="featured" aria-selected="false"><?php _e('Featured', 'rslfranchise'); ?></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent" role="tab" aria-controls="recent" aria-selected="false"><?php _e('Recent', 'rslfranchise'); ?></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="unanswered-tab" data-bs-toggle="tab" data-bs-target="#unanswered" role="tab" aria-controls="unanswered" aria-selected="false"><?php _e('Unanswered', 'rslfranchise'); ?></button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="forumTabsContent">
                            <!-- Popular Tab -->
                            <div class="tab-pane fade show active" id="popular" role="tabpanel" aria-labelledby="popular-tab">
                                <?php
                                    // Query for popular forums (most topics or replies)
                                    $popular_forums = get_posts(array(
                                        'post_type'      => 'forum',
                                        'posts_per_page' => -1,
                                        'orderby'        => 'comment_count', // Ordering by reply count (popular)
                                        'order'          => 'DESC'
                                    ));
                                    display_forums($popular_forums); 
                                ?>
                            </div>

                            <!-- Featured Tab -->
                            <div class="tab-pane fade" id="featured" role="tabpanel" aria-labelledby="featured-tab">
                                <?php
                                    // Query for featured forums (could be custom fields like 'featured' or a tag)
                                    $featured_forums = get_posts(array(
                                        'post_type'      => 'forum',
                                        'posts_per_page' => -1,
                                        'meta_key'       => 'is_featured',
                                        'meta_value'     => '1'
                                    ));
                                    display_forums($featured_forums);
                                ?>
                            </div>

                            <!-- Recent Tab -->
                            <div class="tab-pane fade" id="recent" role="tabpanel" aria-labelledby="recent-tab">
                                <?php
                                    // Query for recent forums
                                    $recent_forums = get_posts(array(
                                        'post_type'      => 'forum',
                                        'posts_per_page' => 5, // Limit to 5 forums for recent
                                        'orderby'        => 'date',
                                        'order'          => 'DESC'
                                    ));
                                    display_forums($recent_forums);
                                ?>
                            </div>

                            <!-- Unanswered Tab -->
                            <div class="tab-pane fade" id="unanswered" role="tabpanel" aria-labelledby="unanswered-tab">
                                <?php
                                    // Display forums with unanswered topics
                                    display_forums($unanswered_forums);
                                ?>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </article>
</main>
<?php get_footer(); ?>

<?php
// Function to display forum table
function display_forums($forums) {
  if (!empty($forums)) :
    foreach ($forums as $forum) :
        $forum_id = $forum->ID;
        $forum_link = get_permalink($forum_id);
        $topic_count = bbp_get_forum_topic_count($forum_id);
        $reply_count = bbp_get_forum_reply_count($forum_id);
        $last_active_id = bbp_get_forum_last_active_id($forum_id);
        $last_post_time = !empty($last_active_id) ? get_the_time('F j, Y', $last_active_id) : __('No posts yet', 'rslfranchise');

        // Get latest topic details
        $latest_topic_id = bbp_get_forum_last_topic_id($forum_id);
        $latest_topic_title = ($latest_topic_id) ? get_the_title($latest_topic_id) : __('No recent topics', 'rslfranchise');
        $latest_topic_author = ($latest_topic_id) ? get_the_author_meta('display_name', get_post_field('post_author', $latest_topic_id)) : __('Unknown', 'rslfranchise');
        ?>

        <div class="dashboard-section">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <img src="<?php echo esc_url(get_avatar_url(get_post_field('post_author', $latest_topic_id), ['size' => 40])); ?>" class="rounded-circle me-3" width="40" height="40" alt="User Avatar">
                    <div>
                        <h5 class="card-title mb-1">
                            <a href="<?php echo esc_url($forum_link); ?>" class="text-dark">
                                <?php echo esc_html($forum->post_title); ?>
                            </a>
                        </h5>
                        <p class="text-muted small mb-0">Posted By: <?php echo esc_html($latest_topic_author); ?> · <?php echo esc_html($last_post_time); ?></p>
                    </div>
                </div>
                <p class="card-text mt-2 text-muted"><?php echo esc_html(wp_trim_words($forum->post_content, 20, '...')); ?></p>
                <div class="d-flex justify-content-between text-muted small">
                    <span><i class="fa fa-comments-o" aria-hidden="true"></i> <?php echo esc_html($reply_count); ?> Replies</span>
                    <span><i class="fa fa-plus-circle" aria-hidden="true"></i> <?php echo esc_html($topic_count); ?> Topics</span>
                </div>
            </div>
        </div>

    <?php endforeach;
  else : ?>
    <p class="text-center"><?php _e('No forums available.', 'rslfranchise'); ?></p>
  <?php endif;
}
?>
