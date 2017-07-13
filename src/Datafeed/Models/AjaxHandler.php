<?php

namespace Datafeed\Models;

use Datafeed\Models\WidgetDisplayProcessor as Processor;
use Datafeed\Models\DBAdapter;

class AjaxHandler
{
    /** @var Processor */
    private $processor;

    /** @var DBAdapter */
    private $adapter;

    /**
     * @param \Datafeed\Processor $processor
     * @param \Datafeed\DBAdapter $adapter
     */
    public function __construct(Processor $processor, DBAdapter $adapter)
    {
        $this->processor = $processor;
        $this->adapter = $adapter;
    }

    public function run()
    {
        /** get_sw_product */
        add_action( 'wp_ajax_get_sw_product', array( $this, 'get_sw_product' ) );
        add_action( 'wp_ajax_nopriv_get_sw_product', array( $this, 'get_sw_product' ) );

        /** track_user_click */
        add_action( 'wp_ajax_track_user_click', array( $this, 'track_user_click') );
        add_action( 'wp_ajax_nopriv_track_user_click', array( $this, 'track_user_click') );
    }

    public function get_sw_product()
    {
        $title = $_REQUEST['title'];
        $count = $_REQUEST['displayCount'];
        $layout = $_REQUEST['layout'];
        $keywords = $_REQUEST['keywords'];

        if (! empty($title)) {
            $this->processor->setTitle($title);
        }
        if (! empty($count)) {
            $this->processor->setProductCount($count);
        }
        $this->processor->setLayout($layout);

        if (! empty($keywords)) {
            $this->processor->setKeywords($keywords);
        }

        echo $this->processor->displayWidget();
        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function track_user_click()
    {
        $row = array(
            'feedId'    => $_REQUEST['feedId'],
            'clickIp' => $this->getUserIp(),
            'clickDateTime' => current_time( 'mysql' )
        );

        $this->adapter->saveAnalytics($row);
        wp_die();
    }

    public function getUserIp()
    {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return apply_filters( 'wpb_get_ip', $ip );
    }

}
