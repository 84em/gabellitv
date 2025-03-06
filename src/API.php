<?php

namespace Vested\GabelliTV;

use Google_Client;
use Google_Service_YouTube;

defined( 'ABSPATH' ) or die;

class API {

    public string $api_key = '';
    public string $channel_id = '';
    protected ?Google_Client $client;
    protected ?Google_Service_YouTube $youtube;
    protected ?string $next_page_token = null;

    /**
     * @throws \Exception
     */
    public function __construct() {

        $disabled = Settings\API::get_instance()->get_setting( 'disabled' );
        if ( 'yes' === $disabled ) {
            throw new \Exception( __( 'API is disabled!', 'gabelli' ) );
        }

        $api_key = Settings\API::get_instance()->get_setting( 'api_key' );
        if ( empty( $api_key ) ) {
            throw new \Exception( __( 'API Key is required!', 'gabelli' ) );
        }

        $channel_id = Settings\API::get_instance()->get_setting( 'channel_id' );
        if ( empty( $channel_id ) ) {
            throw new \Exception( __( 'Channel ID is required!', 'gabelli' ) );
        }

        $this->channel_id = $channel_id;
        $this->api_key    = $api_key;

        $this->client = new Google_Client();
        $this->client->setDeveloperKey( $this->api_key );
        $this->youtube = new Google_Service_YouTube( $this->client );
    }

    public function status() {

        try {
            $videoResponse = $this->fetch_response( 1 );
            return true;
        } catch ( \Exception $e ) {
            DB::log_error( $e->getMessage() );
            return false;
        }
    }

    public function update( $videoResponse = null ) {

        echo print_r( $videoResponse );

        foreach ( $videoResponse['items'] as $video ) {

            $video_duration = new \DateInterval( $video['contentDetails']['duration'] );

            $duration = ( $video_duration->h * 3600 ) + ( $video_duration->i * 60 ) + $video_duration->s;

            $existing_video_post_id = DB::get_video_by_id( $video['id'] );

            if ( ! $existing_video_post_id ) {

                $new_video_post_id = wp_insert_post( [
                    'post_title'     => $video['snippet']['title'],
                    'post_content'   => $video['snippet']['description'],
                    'post_type'      => 'gabellitv',
                    'post_status'    => 'publish',
                    'post_author'    => 1,
                    'post_date'      => date( 'Y-m-d H:i:s', strtotime( $video['snippet']['publishedAt'] ) ),
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                ] );

                if ( $new_video_post_id ) {

                    update_field( 'video_id', $video['id'], $new_video_post_id );
                    update_field( 'thumbnail', $video['snippet']['thumbnails']['medium']['url'], $new_video_post_id );
                    update_field( 'image', $video['snippet']['thumbnails']['maxres']['url'], $new_video_post_id );
                    update_field( 'duration', $duration, $new_video_post_id );

                    if ( $duration > 60 ) {
                        wp_set_object_terms( $new_video_post_id, 'video', 'gabellitv-type' );
                    }
                    else {
                        wp_set_object_terms( $new_video_post_id, 'short', 'gabellitv-type' );
                    }
                }
            }

//            else {
//
//                wp_update_post( [
//                    'ID'           => $existing_video_post_id,
//                    'post_title'   => $video['snippet']['title'],
//                    'post_content' => $video['snippet']['description'],
//                    'post_date'    => date( 'Y-m-d H:i:s', strtotime( $video['snippet']['publishedAt'] ) ),
//                ] );
//
//                update_field( 'thumbnail', $video['snippet']['thumbnails']['medium']['url'], $existing_video_post_id );
//                update_field( 'image', $video['snippet']['thumbnails']['maxres']['url'], $existing_video_post_id );
//                update_field( 'duration', $duration, $existing_video_post_id );
//            }
        }

        DB::log_success( sprintf( 'Updated %s videos.  Next Page Token: %s', count( $videoResponse['items'] ), $this->next_page_token ) );
    }

    public function poll() {

        $videoResponse = $this->fetch_response( 10 );
        $this->update( $videoResponse );
    }

    public function backfill() {

        do {
            $videoResponse = $this->fetch_response( 50 );
            $this->update( $videoResponse );
        } while ( ! empty( $this->next_page_token ) );
    }

    protected function fetch_response( $maxResults = 50 ) {

        $response = $this->youtube->search->listSearch( 'snippet', [
            'channelId'  => $this->channel_id,
            'maxResults' => $maxResults,
            'order'      => 'date',
            'type'       => 'video',
            'pageToken'  => $this->next_page_token,
        ] );

        $this->next_page_token = $response->getNextPageToken();

        $videoIds = array_map( function ( $item ) {

            return $item['id']['videoId'];
        }, $response['items'] );

        return $this->youtube->videos->listVideos( 'contentDetails,id,snippet', [
            'id' => implode( ',', $videoIds ),
        ] );

    }
}

