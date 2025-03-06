<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

if ( ! class_exists( 'DB' ) ) {

    class DB {

        /**
         * Sets up the database schema by creating required tables with specified columns and indexes.
         *
         * @return void
         */
        public static function schema() {

            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $charset_collate = "CHARSET=latin1";

            if ( ! empty( $wpdb->charset ) ) {
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty( $wpdb->collate ) ) {
                $charset_collate .= " COLLATE $wpdb->collate";
            }

            $sql    = "CREATE TABLE `{$wpdb->prefix}gabelli_tv_log` (
					`id` bigint(20) NOT NULL AUTO_INCREMENT,
					`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					`msg` longtext NULL,
					`success` ENUM('1','0') NOT NULL DEFAULT '1',
					PRIMARY KEY (`id`),
					KEY timestamp (`timestamp`)
			) ENGINE=InnoDB $charset_collate";
            $result = dbDelta( $sql );
        }

        public static function log_error( $msg ) {

            $data = [
                'timestamp' => current_time( 'mysql' ),
                'msg'       => maybe_serialize( $msg ),
                'success'   => 0,
            ];

            global $wpdb;

            $table = "{$wpdb->prefix}gabelli_tv_log";

            $wpdb->insert( $table, $data );
        }

        public static function log_success( $msg ) {

            $data = [
                'timestamp' => current_time( 'mysql' ),
                'msg'       => maybe_serialize( $msg ),
                'success'   => 1,
            ];

            global $wpdb;

            $table = "{$wpdb->prefix}gabelli_tv_log";

            $wpdb->insert( $table, $data );
        }

        /**
         * Removes outdated entries from specific database tables by deleting records older than two years.
         *
         * @return void
         */
        public static function prune() {

            global $wpdb;
            $wpdb->query( "DELETE FROM {$wpdb->prefix}gabelli_tv_log WHERE `timestamp` < DATE_SUB(NOW(), INTERVAL 2 YEAR)" );
        }

        public static function get_video_by_id( $video_id ) {

            global $wpdb;

            $sql = $wpdb->prepare( "
                SELECT p.ID 
                 FROM $wpdb->posts p
                    LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
                            WHERE pm.meta_key = 'video_id' AND pm.meta_value = %s
                              AND p.post_type = 'gabellitv'
                LIMIT 1
            ", $video_id );

            return $wpdb->get_var( $sql );
        }
    }
}
