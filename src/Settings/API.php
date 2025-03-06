<?php

namespace Vested\GabelliTV\Settings;

defined( 'ABSPATH' ) or die;

use Vested\GabelliTV\API as GAPI;

class API {

    public array $settings = [];

    public array $fields = [];

    private static API $instance;

    private function __construct() {

    }

    public static function get_instance() {

        return self::$instance ?? self::$instance = new self();
    }

    /**
     * Retrieves the specified setting from the stored settings.
     *
     * @param  string  $setting  The name of the setting to retrieve.
     *
     * @return mixed The value of the specified setting, or an empty string if the setting is not found.
     */
    public function get_setting( string $setting ) {

        if ( empty( $this->settings ) ) {

            $this->settings = (array) get_option( 'gabellitv-api' );
        }

        return $this->settings[ $setting ] ?? '';
    }

    /**
     * Adds an admin menu option for the Gabelli API to the WordPress settings menu.
     *
     * @return void
     */
    public function add_admin_menu(): void {

        add_options_page(
            __( 'GabelliTV API', 'gabellitv-api' ),
            __( 'GabelliTV API', 'gabellitv-api' ),
            'manage_options',
            'gabellitv-api',
            [ $this, 'render' ],
        );
    }

    /**
     * Renders the settings page for the Gabelli API, including the form with fields for
     * managing API settings, and displaying the current connection status and API token.
     *
     * @return void
     */
    public function render(): void {

        ?>
        <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo admin_url( 'options-general.php?page=gabellitv-api' ); ?>" class="nav-tab<?php echo ! isset( $_REQUEST['tab'] ) ? ' nav-tab-active' : '' ?>">API Settings</a>
            <a href="<?php echo admin_url( 'options-general.php?page=gabellitv-api&tab=audit' ); ?>" class="nav-tab<?php echo isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'audit' ? ' nav-tab-active' : '' ?>">API Data Updates</a>
        </h2>
        <?php
        if ( isset( $_REQUEST['tab'] ) ) {
            require_once GABELLITV_TEMPLATES_DIR . 'api/api-audit-logs.php';
        }
        else {
            ?>
            <form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
                <?php settings_fields( 'gabellitv-api' ); ?>
                <?php do_settings_sections( 'gabellitv-api' ); ?>
                <?php submit_button(); ?>
            </form>
            </div>
            <?php
        }
    }

    /**
     * Adds custom action links to the plugin's entry in the plugins list.
     *
     * @param  array  $links  An array of action links for the plugin.
     *
     * @return array The modified array of action links for the plugin.
     */
    public function plugin_action_link( $links ) {

        // adds settigns link
        $url = esc_url( add_query_arg(
            'page',
            'gabellitv-api',
            get_admin_url() . 'options-general.php'
        ) );
        array_unshift( $links, "<a href='$url'>" . __( 'Settings', 'gabelli' ) . '</a>' );

        return $links;
    }

    /**
     * Initializes the settings for the Gabelli API, including registering settings, adding settings
     * sections, and defining fields for various API credentials and options.
     *
     * @return void
     */
    public function init_settings(): void {

        register_setting(
            'gabellitv-api',
            'gabellitv-api',
            [ 'sanitize_callback' => [ $this, 'sanitize' ] ]
        );

        add_settings_section(
            'gabellitv-api',
            __( 'API Status', 'gabelli' ),
            [ $this, 'api_status' ],
            'gabellitv-api'
        );

        add_settings_section(
            'gabellitv-api-0',
            __( 'API Settings', 'gabelli' ),
            '__return_empty_string',
            'gabellitv-api'
        );

        $this->fields[] = [
            'id'          => 'api_key',
            'title'       => __( 'API Key', 'gabelli' ),
            'type'        => 'text',
            'section'     => 0,
            'description' => __( 'API key provided by YouTube', 'gabelli' ),
            'sanitize'    => 'wp_strip_all_tags',
        ];

        $this->fields[] = [
            'id'          => 'channel_id',
            'title'       => __( 'Channel ID', 'gabelli' ),
            'type'        => 'text',
            'section'     => 0,
            'description' => __( 'The YouTube Channel ID', 'gabelli' ),
            'sanitize'    => 'wp_strip_all_tags',
        ];

        $this->fields[] = [
            'id'          => 'disabled',
            'title'       => __( 'Disabled', 'gabelli' ),
            'type'        => 'select',
            'section'     => 0,
            'description' => __( 'If Yes, YouTube updates will not run', 'gabelli' ),
            'values'      => [ 'no' => 'No', 'yes' => 'Yes' ],
        ];

        $this->fields[] = [
            'id'          => 'poller',
            'title'       => __( 'Frequency', 'gabelli' ),
            'type'        => 'number',
            'section'     => 0,
            'description' => __( 'How often (in minutes) to poll YouTube for new videos?', 'gabelli' ),
            'sanitize'    => 'absint',
            'atts'        => [ 'min' => 1, 'max' => 59 ],
        ];

        foreach ( $this->fields as $field ) {
            add_settings_field(
                $field['id'],
                $field['title'],
                [ $this, 'render_setting_field' ],
                'gabellitv-api',
                'gabellitv-api-' . $field['section'],
                $field
            );
        }
    }

    /**
     * Renders a setting field based on the provided arguments. Supports various input types such
     * as text, number, password, textarea, checkbox, and select.
     *
     * @param  array  $args        {
     *                             An associative array of arguments used to render the setting field.
     *
     * @type string   $id          The ID of the setting field.
     * @type string   $description The description of the setting field.
     * @type string   $placeholder The placeholder text for the input field. Default is an empty string.
     * @type string   $type        The type of the input field. Accepts 'text', 'number', 'password', 'textarea', 'checkbox', 'select'.
     * @type array    $values      An array of options for select fields. Default is an empty array.
     * @type bool     $required    Whether the input field is required. Default is false.
     *                             }
     *
     * @return void
     */
    public function render_setting_field( array $args ): void {

        $id          = $args['id'];
        $description = $args['description'];
        $placeholder = ! empty( $args['placeholder'] ) ? $args['placeholder'] : '';
        $type        = $args['type'];
        $values      = ! empty( $args['values'] ) ? $args['values'] : [];
        $required    = isset( $args['required'] ) ? 'required' : '';
        $value       = $this->get_setting( $id );

        if ( 'textarea' === $type ) {
            echo '<textarea cols=40 rows=5 name="gabellitv-api[' . esc_attr( $id ) . ']" class="regular-textarea ' . esc_attr( $id ) . '_field">' . esc_textarea( $value ) . '</textarea>';
        }
        elseif ( 'checkbox' === $type ) {
            if ( ! empty( $value ) ) {
                $checked = 'checked';
            }
            else {
                $checked = '';
            }
            echo '<input type="checkbox" name="gabellitv-api[' . esc_attr( $id ) . ']" class="regular-text ' . esc_attr( $id ) . '_field" value="1" ' . esc_attr( $checked ) . '/>';
        }
        elseif ( 'select' === $type ) {

            echo '<select name="gabellitv-api[' . esc_attr( $id ) . ']" ' . esc_html( $required ) . '>';
            foreach ( $values as $array_key => $array_value ) {
                $selected = ( $value == $array_key ) ? 'selected' : '';
                echo '<option value="' . esc_attr( $array_key ) . '"' . esc_html( $selected ) . '>' . esc_html( $array_value ) . '</option>';
            }
            echo '</select>';
        }
        else {
            echo '<input style="width:auto;max-width:auto"
			type="' . esc_attr( $type ) . '"
			name="gabellitv-api[' . esc_attr( $id ) . ']"
			class="large-text ' . esc_attr( $id ) . '_field"
			placeholder="' . esc_attr( $placeholder ) . '"
			value="' . esc_attr( $value ) . '"
			' . esc_attr( $required );

            if ( ! empty( $args['atts'] ) ) {
                foreach ( $args['atts'] as $key => $value ) {
                    echo ' ' . $key . '="' . esc_attr( $value ) . '"';
                }
            }

            echo '>';

        }
        if ( ! empty( $description ) ) {
            echo '<p><small>' . wp_kses_post( $description ) . '</small></p>';
        }
    }

    /**
     * Sanitizes the provided options based on predefined sanitization functions for each field.
     *
     * Iterates through the fields and options, applying the appropriate sanitization function to each
     * option value if a sanitization function is defined and is callable.
     * If a sanitization function is not callable, an error is triggered.
     *
     * @param  array  $options  The array of options to be sanitized.
     *
     * @return array The sanitized array of options.
     */
    public function sanitize( array $options ) {

        foreach ( $this->fields as $field ) {
            foreach ( $options as $key => $value ) {
                if ( $key === $field['id'] ) {
                    if ( isset( $field['sanitize'] ) ) {
                        if ( is_callable( $field['sanitize'] ) ) {
                            $options[ $key ] = $field['sanitize']( $value );
                        }
                        else {
                            trigger_error( sprintf( "%s sanitize function is not callable", $field['sanitize'] ), E_USER_ERROR );
                        }
                    }
                }
            }
        }

        return $options;
    }

    public function api_status() {

        ?>
        <p><?php echo __( 'API Status', 'gabelli' ) ?>: <?php
            $connected = false;
            try {
                $api       = new GAPI();
                $connected = $api->status();
            } catch ( \Exception $e ) {
                echo '<strong style="color:red">' . __( $e->getMessage(), 'gabelli' ) . '</strong>';
                return;
            }

            if ( $connected ) {
                echo '<strong style="color:green">' . __( 'Connected', 'gabelli' ) . '</strong>';
            }
            else {
                echo '<strong style="color:red">' . __( 'Not Connected', 'gabelli' ) . '</strong>';
            }
            ?></p>
        <?php
    }

}
