<?php
/**
 * Template Name: Single Topic Page
 */
get_header();
$topic_id = get_the_ID();

// Get topic details
$topic_title = get_the_title($topic_id);
$topic_content = apply_filters('the_content', get_post_field('post_content', $topic_id));
$topic_author_id = get_post_field('post_author', $topic_id);
$topic_author = get_the_author_meta('display_name', $topic_author_id);
$topic_date = get_the_date('F j, Y, g:i a', $topic_id);
$reply_count = bbp_get_topic_reply_count($topic_id);

// Check if user is subscribed
$is_subscribed = bbp_is_user_subscribed($user_id, $topic_id);
$subscribe_text = $is_subscribed ? "Unsubscribe" : "Subscribe";
$subscribe_action = $is_subscribed ? "bbp_unsubscribe" : "bbp_subscribe";

// Check if user has favorited the topic
$is_favorite = bbp_is_user_favorite($user_id, $topic_id);
$favorite_text = $is_favorite ? "Unfavorite" : "Favorite";
$favorite_action = $is_favorite ? "bbp_favorite_remove" : "bbp_favorite_add";

// Generate nonce for security
$subscribe_nonce = wp_create_nonce($subscribe_action);
$favorite_nonce = wp_create_nonce($favorite_action);

// Get replies
$replies = get_posts(array(
    'post_type'   => 'reply',
    'post_parent' => $topic_id,
    'orderby'     => 'post_date',
    'order'       => 'ASC'
));
?>

<main id="primary" class="site-main topic-page">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="topic-container">
            <!-- Forum Title & Last Update -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="mb-0"><?php echo esc_html(get_the_title($topic_id)); ?></h1>
                <span class="text-muted">Last updated on <?php echo esc_html($topic_date); ?></span>
            </div>
            <div class="row">
              <div class="col-md-6">
                <!-- Breadcrumb -->
                <?php if (function_exists('bbp_breadcrumb')) : ?>
                    <nav class="breadcrumb">
                        <?php bbp_breadcrumb(array(
                            'sep' => ' » ', // Separator between links
                            'home_text' => 'Home', // Custom home text
                        )); ?>
                    </nav>
                <?php endif; ?>
              </div>
              <div class="col-md-6">
                <!-- Subscribe Button -->
                <div class="">
                  <span id="subscription-toggle">
                      <a href="?action=<?php echo $subscribe_action; ?>&amp;object_id=<?php echo $topic_id; ?>&amp;object_type=post&amp;_wpnonce=<?php echo $subscribe_nonce; ?>"
                          class="subscription-toggle"
                          data-bbp-object-id="<?php echo $topic_id; ?>"
                          data-bbp-object-type="post"
                          data-bbp-nonce="<?php echo $subscribe_nonce; ?>"
                          rel="nofollow">
                          <?php echo $subscribe_text; ?>
                      </a>
                  </span>

                  <!-- Favorite/Unfavorite -->
                  <span id="favorite-toggle">
                      | <a href="?action=<?php echo $favorite_action; ?>&amp;object_id=<?php echo $topic_id; ?>&amp;_wpnonce=<?php echo $favorite_nonce; ?>"
                          class="favorite-toggle"
                          data-bbp-object-id="<?php echo $topic_id; ?>"
                          data-bbp-object-type="post"
                          data-bbp-nonce="<?php echo $favorite_nonce; ?>"
                          rel="nofollow">
                          <?php echo $favorite_text; ?>
                      </a>
                  </span>
                </div>
              </div>
            </div>
        </div>
        <div class="container mt-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Topic: <?php echo esc_html($topic_title); ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Started by: <?php echo esc_html($topic_author); ?> on <?php echo esc_html($topic_date); ?></p>
                    <div class="border p-3 bg-light">
                        <?php echo $topic_content; ?>
                    </div>
                </div>
            </div>

            <!-- Replies Section -->
            <div class="card mt-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Replies (<?php echo esc_html($reply_count); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($replies)) : ?>
                        <?php foreach ($replies as $reply) : 
                            $reply_author_id = $reply->post_author;
                            $reply_author = get_the_author_meta('display_name', $reply_author_id);
                            $reply_date = get_the_date('F j, Y, g:i a', $reply->ID);
                            $reply_content = apply_filters('the_content', $reply->post_content);
                        ?>
                            <div class="border-bottom pb-3 mb-3">
                                <p class="fw-bold mb-1"> <?php echo esc_html($reply_author); ?> <span class="text-muted">(<?php echo esc_html($reply_date); ?>)</span></p>
                                <div class="p-2 bg-light"> <?php echo $reply_content; ?> </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="text-muted">No replies yet. Be the first to respond!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reply Form -->
            <?php if (is_user_logged_in()) : ?>
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Post a Reply</h5>
                    </div>
                    <div class="card-body">
                        <form id="new-reply" name="new-reply" method="post" action="<?php the_permalink(); ?>" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="bbp_reply_content" class="form-label">Your Reply</label>
                                <?php bbp_the_content(array('context' => 'reply')); ?>
                            </div>
                            
                            <?php bbp_reply_form_fields(); ?>
                            
                            <div class="text-center">
                                <button type="submit" id="bbp_reply_submit" name="bbp_reply_submit" class="btn-sm">Submit Reply</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <p class="text-center mt-4">You must be logged in to reply.</p>
            <?php endif; ?>
        </div>
    </article>
</main>

<?php get_footer(); ?>