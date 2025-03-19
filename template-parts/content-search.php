<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package rslfranchise
 */
?>

<?php if (!empty($courses_by_category)) : ?>
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs text-center" id="myTab" role="tablist">
        <?php 
        $first = true; // To mark the first tab as active
        foreach ($courses_by_category as $category_name => $post_ids) : 
        ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $first ? 'active' : ''; ?>" id="<?php echo sanitize_title($category_name); ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo sanitize_title($category_name); ?>" type="button" role="tab" aria-controls="<?php echo sanitize_title($category_name); ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                    <?php echo esc_html($category_name); ?>
                </button>
            </li>
        <?php 
        $first = false; // Only the first tab should be active
        endforeach; 
        ?>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="myTabContent">
        <?php 
        $first = true; // Reset for tab content
        foreach ($courses_by_category as $category_name => $post_ids) : 
        ?>
            <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" id="<?php echo sanitize_title($category_name); ?>" role="tabpanel" aria-labelledby="<?php echo sanitize_title($category_name); ?>-tab">
							<div class="row">  
								<?php 
                foreach ($post_ids as $post_id) : 
                    $post = get_post($post_id);
                    if ($post) :
                        setup_postdata($post);
												?>
												<div class="col-md-6 col-lg-4">
													<?php
														get_template_part('template-parts/loop', 'sfwd-courses'); 
													?>
												</div>
												<?php
                    endif;
                endforeach; 
                wp_reset_postdata();
                ?>
							</div>
            </div>
        <?php 
        $first = false; 
        endforeach; 
        ?>
    </div>
<?php endif; ?>
