<?php

namespace OpenUserMapPlugin\Base;

class LocationReport {
    const ACTION = 'oum_report_location';

    const POST_TYPE = 'oum-location-report';

    const ADMIN_PAGE = 'open-user-map-location-reports';

    const SUBMISSION_KEY = '_oum_report_submission_key';

    const MAIL_STATUS = '_oum_report_mail_status';

    const MAX_REASON_LENGTH = 1000;

    const GUEST_COOKIE = 'oum_report_guest';

    public function register() {
    }

    public function register_post_type() {
        register_post_type( self::POST_TYPE, array(
            'public'              => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_ui'             => false,
            'show_in_rest'        => false,
            'rewrite'             => false,
            'query_var'           => false,
            'can_export'          => false,
            'capability_type'     => array(self::POST_TYPE, 'oum-location-reports'),
            'map_meta_cap'        => true,
            'supports'            => false,
        ) );
    }

    public function add_admin_page() {
        add_submenu_page(
            'edit.php?post_type=oum-location',
            __( 'Location reports', 'open-user-map' ),
            __( 'Reports', 'open-user-map' ),
            'manage_options',
            self::ADMIN_PAGE,
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to access this page.' ), '', array(
                'response' => 403,
            ) );
        }
        $page = ( filter_var( $_GET['paged'] ?? null, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1,
            ),
        ) ) ?: 1 );
        $reports = new \WP_Query(array(
            'post_type'              => self::POST_TYPE,
            'post_status'            => 'private',
            'posts_per_page'         => 25,
            'paged'                  => $page,
            'orderby'                => array(
                'date' => 'DESC',
                'ID'   => 'DESC',
            ),
            'update_post_term_cache' => false,
        ));
        $mail_status_labels = array(
            'pending' => __( 'Pending', 'open-user-map' ),
            'sent'    => __( 'Sent', 'open-user-map' ),
            'failed'  => __( 'Failed', 'open-user-map' ),
        );
        $page_url = admin_url( 'edit.php?post_type=oum-location&page=' . self::ADMIN_PAGE );
        require dirname( __DIR__, 2 ) . '/templates/page-backend-location-reports.php';
    }

    public static function is_enabled() {
        return function_exists( 'oum_fs' ) && oum_fs()->is__premium_only() && oum_fs()->can_use_premium_code() && get_option( 'oum_enable_location_reporting' ) === 'on';
    }

    public static function count_by_location( array $location_ids ) {
        global $wpdb;
        $location_ids = wp_parse_id_list( $location_ids );
        if ( !$location_ids ) {
            return array();
        }
        $placeholders = implode( ', ', array_fill( 0, count( $location_ids ), '%d' ) );
        $counts = $wpdb->get_results( $wpdb->prepare( "SELECT post_parent, COUNT(*) AS report_count\n             FROM {$wpdb->posts}\n             WHERE post_type = %s AND post_status = %s AND post_parent IN ({$placeholders})\n             GROUP BY post_parent", array_merge( array(self::POST_TYPE, 'private'), $location_ids ) ), OBJECT_K );
        $totals = array_fill_keys( $location_ids, 0 );
        foreach ( $counts as $location_id => $row ) {
            $totals[$location_id] = (int) $row->report_count;
        }
        return $totals;
    }

    public static function requires_login() {
        return get_option( 'oum_location_reporting_require_login' ) === 'on';
    }

    public static function render_popup_action( $post_id ) {
        if ( !self::is_enabled() ) {
            return '';
        }
        $logged_in = is_user_logged_in();
        $login_required = !$logged_in && self::requires_login();
        if ( !$logged_in && !$login_required && self::get_guest_id() === '' ) {
            $id = wp_generate_password( 32, false );
            $cookie = $id . '.' . hash_hmac( 'sha256', $id, wp_salt( 'nonce' ) );
            setcookie(
                self::GUEST_COOKIE,
                $cookie,
                0,
                ADMIN_COOKIE_PATH,
                (string) COOKIE_DOMAIN,
                is_ssl(),
                true
            );
        }
        $label = self::trim_text( (string) get_option( 'oum_location_report_label', '' ) );
        if ( $label === '' ) {
            $label = __( 'Report a problem', 'open-user-map' );
        }
        $ui_color = sanitize_hex_color( get_option( 'oum_ui_color', '' ) );
        ob_start();
        ?>
        <div class="oum-location-report">
            <button type="button" class="oum-location-report-open" aria-haspopup="dialog">
                <span class="oum-location-report-label"><?php 
        echo esc_html( $label );
        ?></span>
            </button>
            <template>
                <dialog class="oum-location-report-dialog<?php 
        echo ( $login_required ? ' oum-location-report-dialog-login' : '' );
        ?>" aria-label="<?php 
        echo esc_attr( $label );
        ?>"<?php 
        if ( $ui_color ) {
            ?> style="--oum-report-accent: <?php 
            echo esc_attr( $ui_color );
            ?>"<?php 
        }
        ?>>
                    <button type="button" class="oum-location-report-close" aria-label="<?php 
        echo esc_attr__( 'Close', 'open-user-map' );
        ?>">
                        <span aria-hidden="true">&#x2715;</span>
                    </button>
                    <h2><?php 
        echo esc_html( $label );
        ?></h2>
                    <?php 
        if ( $login_required ) {
            ?>
                        <div class="oum-location-report-login">
                            <p><?php 
            echo esc_html__( 'Please log in to report this location.', 'open-user-map' );
            ?></p>
                            <a class="oum-location-report-login-link" href="<?php 
            echo esc_url( wp_login_url( ( wp_get_referer() ?: '' ) ) );
            ?>"><?php 
            echo esc_html__( 'Log in', 'open-user-map' );
            ?></a>
                        </div>
                    <?php 
        } else {
            ?>
                        <form class="oum-location-report-form" action="<?php 
            echo esc_url( admin_url( 'admin-ajax.php' ) );
            ?>" method="post"
                            data-invalid-reason="<?php 
            echo esc_attr( sprintf( __( 'Enter a reason of 1 to %d characters.', 'open-user-map' ), self::MAX_REASON_LENGTH ) );
            ?>"
                            data-request-failed="<?php 
            echo esc_attr__( 'The report could not be sent. Please try again.', 'open-user-map' );
            ?>">
                            <input type="hidden" name="action" value="<?php 
            echo esc_attr( self::ACTION );
            ?>">
                            <input type="hidden" name="post_id" value="<?php 
            echo esc_attr( $post_id );
            ?>">
                            <input type="hidden" name="nonce" value="<?php 
            echo esc_attr( wp_create_nonce( self::ACTION . '_' . $post_id ) );
            ?>">
                            <label for="oum-location-report-reason">
                                <?php 
            echo esc_html( sprintf( __( 'Reason (maximum %d characters)', 'open-user-map' ), self::MAX_REASON_LENGTH ) );
            ?>
                            </label>
                            <textarea id="oum-location-report-reason" name="reason" rows="4" required autofocus data-max-length="<?php 
            echo esc_attr( self::MAX_REASON_LENGTH );
            ?>"></textarea>
                            <button type="submit" class="oum-location-report-submit"><?php 
            echo esc_html__( 'Send report', 'open-user-map' );
            ?></button>
                        </form>
                        <p class="oum-location-report-status" role="status"></p>
                    <?php 
        }
        ?>
                </dialog>
            </template>
        </div>
        <?php 
        return trim( ob_get_clean() );
    }

    public static function validate_reason( $input ) {
        if ( !is_string( $input ) ) {
            return '';
        }
        $reason = str_replace( array("\r\n", "\r"), "\n", wp_unslash( $input ) );
        $reason = self::trim_text( $reason );
        // Count Unicode code points consistently with the form, including emoji.
        if ( $reason === '' || preg_match( '/\\A.{1,' . self::MAX_REASON_LENGTH . '}\\z/us', $reason ) !== 1 ) {
            return '';
        }
        return self::trim_text( wp_strip_all_tags( $reason ) );
    }

    private static function trim_text( $text ) {
        return preg_replace( '/\\A[\\p{Z}\\x09-\\x0d\\x{FEFF}]+|[\\p{Z}\\x09-\\x0d\\x{FEFF}]+\\z/u', '', $text ) ?? '';
    }

    private static function get_guest_id() {
        $cookie = $_COOKIE[self::GUEST_COOKIE] ?? '';
        if ( !is_string( $cookie ) || !preg_match( '/\\A([a-zA-Z0-9]{32})\\.([a-f0-9]{64})\\z/', $cookie, $matches ) ) {
            return '';
        }
        return ( hash_equals( hash_hmac( 'sha256', $matches[1], wp_salt( 'nonce' ) ), $matches[2] ) ? $matches[1] : '' );
    }

    /**
     * Keep retries attached to the saved report even after a cache reset.
     * Called only while holding the sender/location database lock.
     */
    private static function find_or_save( $location_id, $reason, $submission_key ) {
        $matches = get_posts( array(
            'post_type'   => self::POST_TYPE,
            'post_status' => 'private',
            'post_parent' => $location_id,
            'meta_key'    => self::SUBMISSION_KEY,
            'meta_value'  => $submission_key,
            'fields'      => 'ids',
            'numberposts' => 1,
            'orderby'     => 'ID',
            'order'       => 'DESC',
        ) );
        if ( $matches && get_post_meta( $matches[0], self::MAIL_STATUS, true ) !== 'sent' ) {
            return $matches[0];
        }
        $id = wp_insert_post( array(
            'post_type'    => self::POST_TYPE,
            'post_status'  => 'private',
            'post_parent'  => $location_id,
            'post_author'  => 0,
            'post_content' => wp_slash( $reason ),
            'meta_input'   => array(
                self::SUBMISSION_KEY => $submission_key,
                self::MAIL_STATUS    => 'pending',
            ),
        ), true );
        if ( is_wp_error( $id ) || !$id ) {
            return 0;
        }
        // WordPress can return a post ID even if an individual meta write fails.
        if ( get_post_meta( $id, self::SUBMISSION_KEY, true ) !== $submission_key || get_post_meta( $id, self::MAIL_STATUS, true ) !== 'pending' ) {
            wp_delete_post( $id, true );
            return 0;
        }
        return $id;
    }

    /**
     * Serialize persistence and mail attempts for a sender and location.
     */
    public static function save_and_notify( $post, $reason, $edit_url ) {
        global $wpdb;
        $user_id = get_current_user_id();
        if ( $user_id ) {
            $sender = 'user:' . $user_id;
        } else {
            $guest_id = self::get_guest_id();
            // Guests with cookies disabled fall back to the server's client address.
            $sender = ( $guest_id !== '' ? 'guest:' . $guest_id : 'address:' . ($_SERVER['REMOTE_ADDR'] ?? '') );
        }
        $key = hash_hmac( 'sha256', $wpdb->options . ':' . $post->ID . ':' . $sender, wp_salt( 'nonce' ) );
        $transient = 'oum_location_report_' . $key;
        $lock = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $key ) );
        if ( (string) $lock === '0' ) {
            return 'repeated';
        }
        if ( (string) $lock !== '1' ) {
            return 'failed';
        }
        try {
            if ( get_transient( $transient ) !== false ) {
                return 'repeated';
            }
            $report_id = self::find_or_save( $post->ID, $reason, hash_hmac( 'sha256', $reason, $key ) );
            if ( !$report_id ) {
                return 'failed';
            }
            if ( !set_transient( $transient, 1, 15 * MINUTE_IN_SECONDS ) ) {
                return 'mail_failed';
            }
            $sent = false;
            try {
                $recipient = get_option( 'oum_admin_notification_email' );
                if ( !is_email( $recipient ) ) {
                    $recipient = get_option( 'admin_email' );
                }
                if ( !is_email( $recipient ) ) {
                    return 'mail_failed';
                }
                $subject = __( 'Location report', 'open-user-map' );
                $message = sprintf(
                    /* translators: 1: location title, 2: report reason, 3: admin edit link. */
                    __( "Location: %1\$s\n\nReason:\n%2\$s\n\nEdit location: %3\$s", 'open-user-map' ),
                    wp_strip_all_tags( $post->post_title ),
                    $reason,
                    $edit_url
                );
                $sent = wp_mail( $recipient, $subject, $message );
                return ( $sent ? 'sent' : 'mail_failed' );
            } finally {
                update_post_meta( $report_id, self::MAIL_STATUS, ( $sent ? 'sent' : 'failed' ) );
                if ( !$sent ) {
                    delete_transient( $transient );
                }
            }
        } finally {
            $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $key ) );
        }
    }

}
