<?php defined('ABSPATH') || exit; ?>

<div class="wrap">
    <h1><?php echo esc_html__('Location reports', 'open-user-map'); ?></h1>

    <?php if (!$reports->posts) : ?>
        <p><?php echo esc_html__('No reports found.', 'open-user-map'); ?></p>
    <?php else : ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th scope="col"><?php echo esc_html__('Location', 'open-user-map'); ?></th>
                    <th scope="col"><?php echo esc_html__('Reason', 'open-user-map'); ?></th>
                    <th scope="col"><?php echo esc_html__('Date', 'open-user-map'); ?></th>
                    <th scope="col"><?php echo esc_html__('Email status', 'open-user-map'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports->posts as $report) :
                    $location_id = (int) $report->post_parent;
                    $location = $location_id ? get_post($location_id) : null;
                    /* translators: %d: ID of the reported location. */
                    $location_title = sprintf(__('Location #%d', 'open-user-map'), $location_id);
                    $edit_url = '';
                    if ($location && $location->post_type === 'oum-location') {
                        $location_title = get_the_title($location) ?: $location_title;
                        $edit_url = get_edit_post_link($location, 'raw');
                    }
                    $mail_status = get_post_meta($report->ID, self::MAIL_STATUS, true);
                    ?>
                    <tr>
                        <th scope="row">
                            <?php if ($edit_url) : ?>
                                <a href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($location_title); ?></a>
                            <?php else : ?>
                                <?php echo esc_html($location_title); ?>
                            <?php endif; ?>
                        </th>
                        <td style="white-space: pre-wrap; overflow-wrap: anywhere;"><?php echo esc_html($report->post_content); ?></td>
                        <td><?php echo esc_html(get_the_date('', $report) . ' ' . get_the_time('', $report)); ?></td>
                        <td><?php echo esc_html($mail_status_labels[$mail_status] ?? $mail_status_labels['pending']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($reports->max_num_pages > 1) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php echo wp_kses_post(paginate_links(array(
                    'base' => str_replace('999999999', '%#%', add_query_arg('paged', '999999999', $page_url)),
                    'format' => '',
                    'current' => $page,
                    'total' => $reports->max_num_pages,
                ))); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
