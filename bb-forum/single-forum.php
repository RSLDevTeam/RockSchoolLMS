<?php
/**
 * Template Name: Single Forum Page
 */
get_header();
$forum_id = get_the_ID();

// Get last updated time
$last_active_time = get_post_modified_time('F j, Y', false, $forum_id, true);

// Get topics in this forum
$topics = get_posts(array(
    'post_type'      => 'topic',
    'post_parent'    => $forum_id,
    'orderby'        => 'post_date',
    'order'          => 'DESC'
));

// Forum details
$topic_count = bbp_get_forum_topic_count($forum_id);
$reply_count = bbp_get_forum_reply_count($forum_id);
$last_active_user = get_userdata(get_post_field('post_author', $forum_id));

?>

<main id="primary" class="site-main forum-page">
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <div class="forum-container">
        <!-- Forum Title & Last Update -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0"><?php echo esc_html(get_the_title($forum_id)); ?></h1>
            <span class="text-muted">Last updated on <?php echo esc_html($last_active_time); ?></span>
        </div>
        <!-- Breadcrumb -->
        <!-- <?php if (function_exists('bbp_breadcrumb')) : ?>
            <nav class="breadcrumb">
                <?php bbp_breadcrumb(array(
                    'sep' => ' » ', // Separator between links
                    'home_text' => 'Home', // Custom home text
                )); ?>
            </nav>
        <?php endif; ?> -->

        <!-- Subscribe Button -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted">This forum has <?php echo esc_html($topic_count); ?> topic(s) and was last updated by <?php echo esc_html($last_active_user->display_name); ?>.</p>
            <button><?php bbp_forum_subscription_link(); ?></button>
        </div>

        <div class="mt-5">
          <div class="row">
                
              <div class="col-md-4 mb-4">
                  <div class="dashboard-section stat-box">
                      <div class="stat-icon posts"><i class="fa fa-plus-circle"></i></div>
                      <div class="metric-number">18</div>
                      <h3>Posts</h3>
                  </div>
              </div>
              <div class="col-md-4 mb-4">
                  <div class="dashboard-section stat-box">
                      <div class="stat-icon topics"><i class="fa fa-comments"></i></div>
                      <div class="metric-number">23</div>
                      <h3>Topics</h3>
                  </div>
              </div>
              <div class="col-md-4 mb-4">
                  <div class="dashboard-section stat-box">
                      <div class="stat-icon replies"><i class="fa fa-reply"></i></div>
                      <div class="metric-number">43</div>
                      <h3>Replies</h3>
                  </div>
              </div>
          </div>
        </div>

        <!-- Topics List -->
        <div class="dashboard-section">
            <div class="card-header">
                <h3 >Topics</h3>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>Voices</th>
                            <th>Posts</th>
                            <th>Last Post</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topics)) : ?>
                            <?php foreach ($topics as $topic) :
                                $topic_link = get_permalink($topic->ID);
                                $topic_author_id = $topic->post_author;
                                $topic_author = get_the_author_meta('display_name', $topic_author_id);
                                $last_activity = get_post_modified_time('F j, Y, g:i a', false, $topic->ID, true);
                                $reply_count = bbp_get_topic_reply_count($topic->ID);
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url($topic_link); ?>" class="text-dark fw-bold">
                                            <?php echo esc_html($topic->post_title); ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">Started by: <?php echo esc_html($topic_author); ?></small>
                                    </td>
                                    <td>1</td>
                                    <td><?php echo esc_html($reply_count); ?></td>
                                    <td>
                                        <small class="text-muted"><?php echo esc_html($last_activity); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo esc_html($topic_author); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No topics found in this forum.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div style="height: 20px;"></div>
        <!-- New Topic Form -->
        <?php if (is_user_logged_in()) : ?>
        <div class="dashboard-section">
            <div class="card-header">
                <h3>Create New Topic</h3>
            </div>
            <div class="card-body">
                <form id="new-topic" name="new-topic" method="post" action="<?php the_permalink(); ?>" class="needs-validation" novalidate>
                    <!-- Topic Title -->
                    <div class="row">
                        <div class="col-md-6">
                            <label for="bbp_topic_title" class="form-label bb_form_labler">Topic Title</label>
                            <input type="text" name="bbp_topic_title" id="bbp_topic_title" class="form-control" maxlength="80" required>
                            <div class="invalid-feedback">Please enter a topic title.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="bbp_topic_tags" class="form-label bb_form_labler">Topic Tags (comma-separated)</label>
                            <input type="text" name="bbp_topic_tags" id="bbp_topic_tags" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="bbp_stick_topic" class="form-label bb_form_labler">Topic Type</label>
                            <select name="bbp_stick_topic" id="bbp_stick_topic" class="form-select">
                                <option value="0">Normal</option>
                                <option value="super">Super Sticky</option>
                                <option value="sticky">Sticky</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bbp_topic_status" class="form-label bb_form_labler">Topic Status</label>
                            <select name="bbp_topic_status" id="bbp_topic_status" class="form-select">
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Topic Content -->
                    <div class="mb-3">
                        <label for="bbp_topic_content" class="form-label bb_form_labler">Topic Content</label>
                        <?php bbp_the_content( array( 'context' => 'topic' ) ); ?>
                    </div>

                    <!-- Notify via Email -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="bbp_topic_subscribe" id="bbp_topic_subscribe" value="yes">
                        <label class="form-check-label" for="bbp_topic_subscribe">Notify me of follow-up replies via email</label>
                    </div>

                    <!-- Hidden Fields -->
                    <?php do_action('bbp_theme_before_topic_form_submit_wrapper'); ?>
                    <?php bbp_topic_form_fields(); ?>

                    <div class="row">
                      <div class="col text-center">
                          <button type="submit" id="bbp_topic_submit" name="bbp_topic_submit" class="btn-sm">
                              Submit Topic
                          </button>
                      </div>
                  </div>
                </form>
            </div>
        </div>

        <!-- Bootstrap Validation Script -->
        <script>
            (function () {
                'use strict';
                var forms = document.querySelectorAll('.needs-validation');
                Array.prototype.slice.call(forms).forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            })();
        </script>
        <?php else : ?>
        <div class="alert alert-warning" role="alert">
            You must be logged in to create a new topic.
        </div>
        <?php endif; ?>
      </div>
  </article>
</main>