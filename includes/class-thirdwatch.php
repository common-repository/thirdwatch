<?php
/**
 * Thirdwatch setup
 *
 * @package Thirdwatch
 * @since   3.2.0
 */

defined( 'ABSPATH' ) || exit;

defined( 'DS' ) or define( 'DS', DIRECTORY_SEPARATOR );
define( 'THIRDWATCH_ROOT', dirname( __DIR__ ) . DS );

/**
 * Main Thirdwatch Class.
 *
 * @class Thirdwatch
 */
final class Thirdwatch {

    protected static $_instance = null;
    private $order;

    private $namespace;
    private $enabled;
    private $api_key;
    private $approve_status;
    private $review_status;
    private $reject_status;
    private $fraud_message;
    private $debug_log;
    public $version = '1.0.0';

    /**
     * Main Thirdwatch Instance.
     *
     * Ensures only one instance of Thirdwatch is loaded or can be loaded.
     *
     * @since 2.1
     * @static
     * @see WC()
     * @return Thirdwatch - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WooCommerce Constructor.
     */
    public function __construct() {

        add_action( 'rest_api_init', array($this, 'score_postback' ));
        add_action( 'rest_api_init', array($this, 'action_postback' ));
        add_action( 'rest_api_init', array($this, 'shipping_address_postback' ));
        add_action( 'rest_api_init', array($this, 'postback_handler' ));
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
    * Define Thirdwatch Constants.
    */
    private function define_constants() {
        $upload_dir = wp_upload_dir( null, false );
        $this->define( 'TW_ABSPATH', dirname( TW_PLUGIN_FILE ) . DS );
        $this->define( 'TW_PLUGIN_BASENAME', plugin_basename( TW_PLUGIN_FILE ) );
        $this->define( 'TW_VERSION', $this->version );
        $this->define( 'TW_DELIMITER', '|' );
        $this->define( 'TW_LOG_DIR', $upload_dir['basedir'] . '/wc-logs/' );
        $this->namespace			= 'woocommerce-thirdwatch';
        $this->enabled				= $this->get_setting( 'enabled' );
        $this->api_key				= $this->get_setting( 'api_key' );
        $this->approve_status		= 'wc-' === substr( $this->get_setting( 'approve_status' ), 0, 3 ) ? substr( $this->get_setting( 'approve_status' ), 3 ) : $this->get_setting( 'approve_status' );
        $this->review_status		= 'wc-' === substr( $this->get_setting( 'review_status' ), 0, 3 ) ? substr( $this->get_setting( 'review_status' ), 3 ) : $this->get_setting( 'review_status' );
        $this->reject_status		= 'wc-' === substr( $this->get_setting( 'reject_status' ), 0, 3 ) ? substr( $this->get_setting( 'reject_status' ), 3 ) : $this->get_setting( 'reject_status' );
        $this->fraud_message		= $this->get_setting( 'fraud_message' );
        $this->debug_log			= $this->get_setting( 'debug_log' );
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    public function includes() {
        /**
         * Class autoloader.
         */
        include_once TW_ABSPATH . 'includes/class-tw-autoloader.php';
        include_once TW_ABSPATH . 'includes/class-tw-install.php';
        include_once TW_ABSPATH . 'includes/libraries/thirdwatch-php/autoload.php';
    }

    /**
     * Hook into actions and filters.
     *
     * @since 2.3
     */
    private function init_hooks() {
        register_activation_hook( TW_PLUGIN_FILE, array( 'TW_Install', 'install' ));
        add_action( 'admin_init', array( 'TW_Install', 'update_db' ));
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'wp_ajax_thirdwatch_woocommerce_admin_notice', array( $this, 'plugin_dismiss_admin_notice' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        // Hooks for WooCommerce
        add_filter( 'manage_shop_order_posts_columns', array( $this, 'add_column' ), 11 );
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_column' ), 3 );
        add_action( 'woocommerce_created_customer', array( $this, 'register' ), 99, 3 );
        add_action( 'wp_login', array( $this, 'login'), 99, 2 );
//        add_action( 'woocommerce_thankyou', array($this, 'get_orders'), 99, 1); // HS-20200526
        add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 99, 3 );
    }

    function tw_upgrade_completed( $upgrader_object, $options ) {
        // If an update has taken place and the updated type is plugins and the plugins element exists
        if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            // Iterate through the plugins being updated and check if ours is there
            foreach( $options['plugins'] as $plugin ) {
                if( $plugin == TW_PLUGIN_BASENAME ) {
                    // Set a transient to record that our plugin has just been updated
                    set_transient( 'wp_upe_updated', 1 );
                }
            }
        }
    }

    function score_postback() {
        register_rest_route( 'thirdwatch/api', 'scorepostback', array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'score_postback_route'),
        ));
    }

    function action_postback() {
        register_rest_route( 'thirdwatch/api', 'actionpostback', array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'action_postback_route'),
        ));
    }

    function shipping_address_postback() {
        register_rest_route( 'thirdwatch/api', 'shippingaddresspostback', array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'shipping_address_postback_route'),
        ));
    }

    function postback_handler() {
        register_rest_route( 'thirdwatch/api', 'postbackhandler', array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'postback_handler_route'),
        ));
    }

    function postback_handler_route( WP_REST_Request $request ) {
        /* SAMPLE REQUEST FORMAT
        {
            "entity": "payment_link",
            "api_key": "xxxxxxxxxx",
            "payload": {
                "order_id": "1203",
                "status": "paid",
                "payment_link": "https://link.link.com/link"
            }
        }
        */
        $this->write_debug_log("Postback Handler incoming");
        $postback_rs['status'] = "failed";

        try {
            $request_params = $request->get_json_params();

            # Check 'entity' and 'api_key' parameter
            if($request_params['entity'] != '' && $request_params['api_key'] != '' && 
                !empty($request_params['entity']) && !empty($request_params['api_key']) ) {

                $this->write_debug_log("Processing postback_handler_route with entity: '".$request_params['entity']."'");

                # Verify API key and validate shop
                if ($request_params['api_key'] == $this->api_key) {

                    # Call respective method based on 'entity'
                    switch ($request_params['entity']) {

                        case "payment_link":
                            $postback_rs = $this->woocom_mark_order_paid($request_params['payload']);
                            break;

                        default;
                            $postback_rs['error'] = "invalid_entity";
                            $postback_rs['message'] = "Invalid entity value received. Processing requested 'entity' is unauthorized.";
                            break;
                    }

                } else {
                    $postback_rs['error'] = "invalid_api_key";
                    $postback_rs['message'] = "Invalid api_key received. API Key doesn't match with stored Thirdwatch configuration.";
                    $this->write_debug_log($postback_rs['message']);
                }

            } else {
                $postback_rs['error'] = "missing_parameter";
                $postback_rs['message'] = "Error while processing postback_handler_route: Request body is missing 'entity' or 'api_key' parameter.";
                $this->write_debug_log($postback_rs['message']);
            }

        } catch (\Throwable $e) {
            $postback_rs['message'] = "Error while processing postback_handler_route: ".$e->getMessage();
            $this->write_debug_log($postback_rs['message']);
        }

        $this->write_debug_log("Postback Handler completed with status: '".$postback_rs['status']."'");
        $postback_status_code = ($postback_rs['status'] == "success") ? 200 : 400;

        return new WP_REST_Response($postback_rs, $postback_status_code, array('content-type' => 'application/json'));
    }

    public function score_postback_route( WP_REST_Request $request ) {
        global $wpdb;
        $dt = new DateTime();
        $response_json = $request->get_json_params();
        $headers = $request->get_headers();
        $response_score = array();

        if ($headers){
            try {
                $api_key = $headers['x_thirdwatch_api_key'][0];
            }
            catch (\Throwable $e){
                $response_score['Error'] = "Authentication Failed";
                return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
            }

            if ($api_key == $this->api_key){
                try {
                    if ($response_json){
                        $order_id = $response_json['order_id'];
                        $flag = $response_json['flag'];
                        $score = $response_json['score'];
                        
                        $this->write_debug_log("Order ID: ".$order_id." - Score Postback initiated with flag '".$flag."'.");

                        $order_rs = $this->get_wp_tw_order_details($order_id);
                        $this->order = wc_get_order( $order_rs->order_id );

                        if ($flag ==  "red"){
                            $status = "FLAGGED";
                            $flag = "RED";
                        }
                        elseif ($flag == "green"){
                            $status = "APPROVED";
                            $flag = "GREEN";
                        }
                        else{
                            $status = "HOLD";
                            $flag = "";
                        }

                        $customers = $wpdb->update($wpdb->prefix."tw_orders", array("flag"=>$flag, "status" => $status,"score" => (string) $score , "date_modified"=>$dt->format('Y-m-d H:i:s')), array("order_number" => $this->order->get_order_number()));

                        if ( $flag ==  "RED") {
                            if ( $this->review_status && $this->review_status != $this->order->get_status() ) {
                                $this->order->update_status( $this->review_status, __( 'Updated by Thirdwatch: ', $this->namespace ) );
                            }
                        }
                        elseif ( $flag == "GREEN" ) {
                            if ( $this->approve_status && $this->approve_status != $this->order->get_status() ) {
                                $this->order->update_status( $this->approve_status, __( 'Updated by Thirdwatch: ', $this->namespace ) );
                            }
                        }

                        $this->write_debug_log("Order ID: ".$order_id." - Score Postback completed with flag '".$flag."'.");

                        $response_score['Success'] = "Success";
                        return new WP_REST_Response($response_score, 200, array('content-type'=>'application/json'));
                    }
                    $response_score['Error'] = "Response Incorrect";
                    return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
                }
                catch (\Throwable $e) {
                    $this->write_debug_log($e->getMessage());
                    $response_score['Error'] = "Authentication Failed";
                    return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
                }
            }
            else{
                $response_score['Error'] = "Authentication Failed";
                return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
            }
        }
        $response_score['Error'] = "Authentication Failed";
        return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
    }

    public function action_postback_route( WP_REST_Request $request ) {
        global $wpdb;
        $dt = new DateTime();

        $response_json = $request->get_json_params();
        $headers = $request->get_headers();
        $response_score = array();

        $this->write_debug_log("Order ID: ".$response_json['order_id']." - Action Postback initiated with action '".$response_json['action_type']."'.");

        if ($headers) {
            try {
                $api_key = $headers['x_thirdwatch_api_key'][0];
            } catch (\Throwable $e) {
                $response_score['Error'] = "Authentication Failed";
                return new WP_REST_Response($response_score, 400, array('content-type' => 'application/json'));
            }

            if ($api_key == $this->api_key) {

                try {
                    if ($response_json) {
                        $order_id = $response_json['order_id'];
                        $action_type = $response_json['action_type'];
                        $action_message = $response_json['action_message'];
                        
                        $order_rs = $this->get_wp_tw_order_details($order_id);
                        $this->order = wc_get_order( $order_rs->order_id );
                        
                        if ($action_type == "approved") {
                            $action = "APPROVED";
                            $comment = $action_message;
                        } elseif ($action_type == "declined") {
                            $action = "DECLINED";
                            $comment = $action_message;
                        } else {
                            $action = "";
                            $comment = "";
                        }
                        $customers = $wpdb->update($wpdb->prefix."tw_orders", array("action" => $action, "message" => $comment, "date_modified" => $dt->format('Y-m-d H:i:s')), array("order_number" => $this->order->get_order_number()));

                        if ($action_type == "declined") {
                            if ($this->reject_status && $this->reject_status != $this->order->get_status() && $this->review_status == $this->order->get_status()) {
                                $this->order->update_status($this->reject_status, __('Updated by Thirdwatch: ', $this->namespace));
                            }
                        } elseif ($action_type == "approved") {
                            if ($this->approve_status && $this->approve_status != $this->order->get_status() && $this->review_status == $this->order->get_status()) {
                                $this->order->update_status($this->approve_status, __('Updated by Thirdwatch: ', $this->namespace));
                            }
                        }

                        $this->write_debug_log("Order ID: ".$order_id." - Action Postback completed with action '".$response_json['action_type']."'.");

                        $response_score['Success'] = "Success";
                        return new WP_REST_Response($response_score, 200, array('content-type'=>'application/json'));
                    }
                    $response_score['Error'] = "Response Incorrect";
                    return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
                } catch (\Throwable $e) {
                    $this->write_debug_log($e->getMessage());
                    $response_score['Error'] = "Authentication Failed";
                    return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
                }
            }
            else{
                $response_score['Error'] = "Authentication Failed";
                return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
            }
        }
        $response_score['Error'] = "Authentication Failed";
        return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
    }

    public function shipping_address_postback_route( WP_REST_Request $request ) {
        /*
        SAMPLE REQUEST FORMAT:
        {
            "order_id": "56",
            "shipping_address": {
                "name": "Rahul Sharma",
                "address1": "Don Bosco institute of technology, boys hostel dbit",
                "address2": "Kumbalagodu, banglore",
                "city": "Bangalore",
                "region": "Karnataka",
                "zipcode": "560074",
                "country": "India"
            }
        }
        */
        $response_json = $request->get_json_params();
        $headers = $request->get_headers();
        $response_score = array();

        $this->write_debug_log("Order ID: ".$response_json['order_id']." - Shipping Address Postback initiated.");

        if ($headers) {
            try {
                $api_key = $headers['x_thirdwatch_api_key'][0];
            } catch (\Throwable $e) {
                $response_score['Error'] = "Authentication Failed";
                return new WP_REST_Response($response_score, 400, array('content-type' => 'application/json'));
            }

            if ($api_key == $this->api_key) {

                try {
                    if ($response_json && !empty($response_json['shipping_address']) ) {
                        $order_id = $response_json['order_id'];
                        $shipping_address = $response_json['shipping_address'];

                        $order_rs = $this->get_wp_tw_order_details($order_id);
                        $this->order = wc_get_order( $order_rs->order_id );

                        # Split Name field into First Name and Last Name
                        if( isset($shipping_address['name']) && !empty($shipping_address['name']) ) {
                            $name_parts     = explode(" ", trim($shipping_address['name']));
                            $ship_lastname  = array_pop($name_parts);
                            $ship_firstname = implode(" ", $name_parts);

                            $this->order->set_shipping_first_name($ship_firstname);
                            $this->order->set_shipping_last_name($ship_lastname);
                        }

                        if( isset($shipping_address['address1'])   && !empty($shipping_address['address1']) )   $this->order->set_shipping_address_1($shipping_address['address1']);
                        if( isset($shipping_address['address2'])   && !empty($shipping_address['address2']) )   $this->order->set_shipping_address_2($shipping_address['address2']);
                        if( isset($shipping_address['city'])       && !empty($shipping_address['city']) )       $this->order->set_shipping_city($shipping_address['city']);
                        if( isset($shipping_address['region'])     && !empty($shipping_address['region']) )     $this->order->set_shipping_state($shipping_address['region']);
                        if( isset($shipping_address['zipcode'])    && !empty($shipping_address['zipcode']) )    $this->order->set_shipping_postcode($shipping_address['zipcode']);
                        if( isset($shipping_address['country'])    && !empty($shipping_address['country']) )    $this->order->set_shipping_country($shipping_address['country']);

                        $this->order->save();
                        $this->order->add_order_note("Shipping Address updated by Thirdwatch.");

                        $this->write_debug_log("Order ID: ".$order_id." - Shipping Address Postback completed.");

                        $response_score['Success'] = "Success";
                        return new WP_REST_Response($response_score, 200, array('content-type'=>'application/json'));
                    }
                    $response_score['Error'] = "Response Incorrect";
                    return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
                } catch (\Throwable $e) {
                    $this->write_debug_log($e->getMessage());
                    $response_score['Error'] = "Authentication Failed";
                    return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
                }
            }
            else{
                $response_score['Error'] = "Authentication Failed";
                return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
            }
        }
        $response_score['Error'] = "Authentication Failed";
        return new WP_REST_Response($response_score, 400, array('content-type'=>'application/json'));
    }

    public function login( $user_login, $user ) {
        if ($this->enabled == 'yes'){
            $config = \ai\thirdwatch\Configuration::getDefaultConfiguration()->setApiKey('X-THIRDWATCH-API-KEY', $this->api_key);
            $customerInfo = array();
            $sessionInfo = array();

            try{
                $customerInfo['_user_id'] = (string) $user->get('ID');
                $customerInfo['_session_id'] = (string) WC()->session->get_customer_id();
                $customerInfo['_device_ip'] = (string) $_SERVER['REMOTE_ADDR'];
                $customerInfo['_origin_timestamp'] = (string) (time() * 1000);
                $customerInfo['_login_status'] = "_success";

                $api_instance = new \ai\thirdwatch\Api\LoginApi(new GuzzleHttp\Client(), $config);
                $json = new \ai\thirdwatch\Model\Login($customerInfo);
            }
            catch (\Throwable $e) {
                $this->write_debug_log($e->getMessage());
            }

            try {
                $result = $api_instance->login($json);
            }
            catch (\Throwable $e) {
                $this->write_debug_log($e->getMessage());
            }

            try{
                $sessionInfo['_user_id'] = (string) $user->get('ID');
                $sessionInfo['_session_id'] = (string) WC()->session->get_customer_id();
                $api_instance2 = new \ai\thirdwatch\Api\LinkSessionToUserApi(new GuzzleHttp\Client(), $config);
                $json2 = new \ai\thirdwatch\Model\LinkSessionToUser($sessionInfo);
                $result2 = $api_instance2->linkSessionToUser($json2);
            }
            catch (\Throwable $e) {
                $this->write_debug_log($e->getMessage());
            }
        }
    }

    public function register($customer_id, $new_customer_data, $password_generated){
        if ($this->enabled == 'yes'){
            $this->write_debug_log("Customer ID: ".$customer_id." - Creating customer on Thirdwatch.");
            $config = \ai\thirdwatch\Configuration::getDefaultConfiguration()->setApiKey('X-THIRDWATCH-API-KEY', $this->api_key);
            $customerInfo = array();
            $sessionInfo = array();

            try{
                $customerInfo['_user_id'] = (string) $customer_id;
                $customerInfo['_session_id'] = (string) WC()->session->get_customer_id();
                $customerInfo['_device_ip'] = (string) $_SERVER['REMOTE_ADDR'];
                $customerInfo['_origin_timestamp'] = (string) (time() * 1000);
                $customerInfo['_user_email'] = (string) $new_customer_data['user_email'];
                $customerInfo['_account_status'] = '_active';

                $api_instance = new \ai\thirdwatch\Api\CreateAccountApi(new GuzzleHttp\Client(), $config);
                $json = new \ai\thirdwatch\Model\CreateAccount($customerInfo);
                $result = $api_instance->createAccount($json);
            }
            catch (\Throwable $e) {
                $this->write_debug_log($e->getMessage());
            }

            try{
                $sessionInfo['_user_id'] = (string) $customer_id;
                $sessionInfo['_session_id'] = (string) WC()->session->get_customer_id();
                $api_instance2 = new \ai\thirdwatch\Api\LinkSessionToUserApi(new GuzzleHttp\Client(), $config);
                $json2 = new \ai\thirdwatch\Model\LinkSessionToUser($sessionInfo);
                $result2 = $api_instance2->linkSessionToUser($json2);
            }
            catch (\Throwable $e) {
                $this->write_debug_log($e->getMessage());
            }
            $this->write_debug_log("Customer ID: ".$customer_id." - Successfully created customer on Thirdwatch.");
        }
    }

    /** Response Handler
     *	This sends a JSON response to the browser
     */
    protected function send_response($msg, $pugs = ''){
        $response['message'] = $msg;
        if($pugs)
            $response['pugs'] = $pugs;
        header('content-type: application/json; charset=utf-8');
        echo json_encode($response)."\n";
        exit;
    }

    public function get_orders($order_id){
        if ($this->enabled == 'yes'){
            try {
                $this->order = wc_get_order($order_id);

                if ($this->order->get_status() == "processing"){
                    $this->write_debug_log("Order ID: ".$this->order->get_order_number()." - Sending to Thirdwatch.");

                    # Check if order is already present in tw_order table
                    $result = $this->get_wp_tw_order_details($order_id);
                    if ($result){
                        $this->write_debug_log("Order ID: ".$this->order->get_order_number()." - Sending to Thirdwatch restricted. Order already sent previously.");
                        return;
                    }

                    # Create order transaction on Thirdwatch
                    $this->tw_order_transaction();

                    $this->write_debug_log("Order ID: ".$this->order->get_order_number()." - Successfully sent to Thirdwatch.");
                }
            }
            catch (\Throwable $e) {
                $this->write_debug_log($e->getMessage());
            }
        }
    }

    public function order_status_changed($order_id, $old_status, $new_status){
        global $wpdb;

        if ($this->enabled == 'yes'){
            $this->order = wc_get_order( $order_id );
            $this->write_debug_log("Order ID: ".$this->order->get_order_number()." - Status changed from '".$old_status."' to '".$new_status."'.");

            $this->get_orders($order_id);

            $dt = new DateTime();

            $config = \ai\thirdwatch\Configuration::getDefaultConfiguration()->setApiKey('X-THIRDWATCH-API-KEY', $this->api_key);

            $result = $this->get_wp_tw_order_details($order_id);

            $orderInfo = array();

            if ($result){
                try{
                    $orderInfo['_order_id'] = (string) $this->order->get_order_number();
                    $orderInfo['_order_status'] = (string) "_wo_".$new_status;
                    $api_instance = new \ai\thirdwatch\Api\OrderStatusApi(new GuzzleHttp\Client(), $config);
                    $json = new \ai\thirdwatch\Model\OrderStatus($orderInfo);
                    $result2 = $api_instance->orderStatus($json);
                }
                catch (\Throwable $e) {
                    $this->write_debug_log($e->getMessage());
                }

                try{
                    if ($old_status == $this->review_status && $result->status == "FLAGGED" && $result->action == ""){
                        $secret = $this->api_key;

                        $status_exchange_set = array(
                            "pending"    => '',
                            "processing" => 'approved',
                            "on-hold"    => 'onhold',
                            "completed"  => 'approved',
                            "cancelled"  => 'declined',
                            "refunded"   => '',
                            "failed"     => '',
                        );

                        $jsonRequest = array(
                            'secret'=>$secret,
                            'order_id'=>$this->order->get_order_number(),
                            'order_timestamp'=>(string) ($this->order->get_date_created()->getTimestamp() * 1000),
                            'action_type' => $status_exchange_set[$new_status],
                            'message' =>'Status updated on clients dashboard. New Status: '.$new_status,
                        );

                        if(!empty($status_exchange_set[$new_status]) && $status_exchange_set[$new_status] != '') {
                            $response = wp_remote_post("https://api.thirdwatch.ai/neo/v1/clientaction", array(
                                'method' => 'POST',
                                'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
                                'httpversion' => '1.0',
                                'sslverify' => false,
                                'body' => json_encode($jsonRequest)
                            ));

                            $tw_orders_action = strtoupper($status_exchange_set[$new_status]);
                        } else {
                            $tw_orders_action = '';
                        }

                        $customers = $wpdb->update($wpdb->prefix."tw_orders", array("action" => $tw_orders_action, "message" => 'Status updated on clients dashboard. New Status: '.$new_status, "date_modified" => $dt->format('Y-m-d H:i:s')), array("order_number" => $this->order->get_order_number()));
                    }
                }
                catch (\Throwable $e){
                    $this->write_debug_log($e->getMessage());
                }
            }
        }
    }

    public function tw_order_transaction(){
        $this->write_debug_log("Order ID: ".$this->order->get_order_number()." - Creating Order on Thirdwatch.");
        $isPrepaid = false;
        $ip = $_SERVER['REMOTE_ADDR'];

        if (($this->order->get_payment_method() == "cod") || ($this->order->get_payment_method() == "robu_cod")){
            $isPrepaid = false;
        }
        else {
            $isPrepaid = true;
        }

        $items = $this->order->get_items();
        $lineItems = array();

        foreach ( $items as $key => $value ) {
            $lineItemData = array();
            $lineItemData['_price'] = (string) $value->get_total();
            $lineItemData['_quantity'] = intval($value->get_quantity());
            $lineItemData['_product_title'] = (string) $value->get_name();
            $lineItemData['_item_id'] = (string) $value->get_product_id();
            $lineItemData['_currency_code'] = (string)"INR";
            $lineItemData['_country'] = (string)"IN";
            $itemJson = new \ai\thirdwatch\Model\Item($lineItemData);
            $lineItems[] = $itemJson;
        }

        $config = \ai\thirdwatch\Configuration::getDefaultConfiguration()->setApiKey('X-THIRDWATCH-API-KEY', $this->api_key);
        $orderData = array();

        if (is_user_logged_in()){
            $orderData['_user_id'] = (string) get_current_user_id();
        }
        else {
            $orderData['_session_id'] = (string) WC()->session->get_customer_id();
        }

        $orderData['_device_ip'] = (string) $ip;
        $orderData['_origin_timestamp'] = (string) ($this->order->get_date_created()->getTimestamp() * 1000);
        $orderData['_order_id'] = (string) $this->order->get_order_number();
        $orderData['_user_email'] = (string) ($this->order->get_billing_email() ) ? $this->order->get_billing_email() : "";
        $orderData['_amount'] = (string) $this->order->get_total();
        $orderData['_currency_code'] = (string) $this->order->get_currency();
        $orderData['_is_pre_paid'] = $isPrepaid;

        $addrArray = array();
        $addrArray['_name'] = $this->order->get_billing_first_name() . " ". $this->order->get_billing_last_name();
        $addrArray['_address1'] = $this->order->get_billing_address_1();
        $addrArray['_address2'] = $this->order->get_billing_address_2();
        $addrArray['_city'] = $this->order->get_billing_city();
        $addrArray['_country'] = $this->order->get_billing_country();
        $addrArray['_region'] = $this->order->get_billing_state();
        $addrArray['_zipcode'] = $this->order->get_billing_postcode();
        $addrArray['_phone'] =$this->order->get_billing_phone();

        $addrArray2 = array();
        $addrArray2['_name'] = $this->order->get_shipping_first_name() . " ". $this->order->get_shipping_last_name();
        $addrArray2['_address1'] = $this->order->get_shipping_address_1();
        $addrArray2['_address2'] = $this->order->get_shipping_address_2();
        $addrArray2['_city'] = $this->order->get_shipping_city();
        $addrArray2['_country'] = $this->order->get_shipping_country();
        $addrArray2['_region'] = $this->order->get_shipping_state();
        $addrArray2['_zipcode'] = $this->order->get_shipping_postcode();
        $addrArray2['_phone'] =$this->order->get_billing_phone();


        if ($this->order->has_shipping_address()){
            $shipping_json = new \ai\thirdwatch\Model\ShippingAddress($addrArray2);
        }
        else {
            $shipping_json = new \ai\thirdwatch\Model\ShippingAddress($addrArray);
        }

        $billing_json = new \ai\thirdwatch\Model\BillingAddress($addrArray);
        $orderData['_billing_address'] = $billing_json;
        $orderData['_shipping_address'] = $shipping_json;

        $paymentData = array();
        $paymentData['_payment_type'] = (string) $this->order->get_payment_method();
        $paymentData['_amount'] = (string) $this->order->get_total();
        $paymentData['_currency_code'] = (string) "INR";
        $paymentData['_payment_gateway'] = (string) $this->order->get_payment_method_title();
        $paymentData['_accountName'] = (string) $this->order->get_billing_first_name() . " ". $this->order->get_billing_last_name();
        $paymentJson = new \ai\thirdwatch\Model\PaymentMethod($paymentData);

        $orderData['_items'] = $lineItems;
        $orderData['_payment_methods'] = array($paymentJson);

        $orderData['_custom_info']['order_notes'] = $this->order->get_customer_note();

        try {
            $api_instance = new \ai\thirdwatch\Api\CreateOrderApi(new GuzzleHttp\Client(), $config);
            $json = new \ai\thirdwatch\Model\CreateOrder($orderData);
            $result = $api_instance->createOrder($json);
            $this->write_debug_log("Order ID: ".$this->order->get_order_number()." - Successfully created order on Thirdwatch.");
        }
        catch (\Throwable $e) {
            $this->write_debug_log($e->getMessage());
        }

        $this->write_debug_log("Order ID: ".$this->order->get_order_number()." - Creating transaction on Thirdwatch.");
        $txnData = array();
        if (is_user_logged_in()){
            $txnData['_user_id'] = (string) get_current_user_id();
        }
        else {
            $txnData['_session_id'] = (string) WC()->session->get_customer_id();
        }

        $txnData['_device_ip'] = (string) $ip;
        $txnData['_origin_timestamp'] = (string) ($this->order->get_date_created()->getTimestamp() * 1000);
        $txnData['_order_id'] = (string) $this->order->get_order_number();
        $txnData['_user_email'] = (string) ($this->order->get_billing_email() ) ? $this->order->get_billing_email() : "";
        $txnData['_amount'] = (string) $this->order->get_total();
        $txnData['_currency_code'] = (string) 'INR';
        $txnData['_billing_address'] = $billing_json;
        $txnData['_shipping_address'] = $shipping_json;
        $txnData['_items'] = $lineItems;
        $txnData['_payment_method'] = $paymentJson;
        $txnData['_transaction_id'] = (string) $this->order->get_order_number();
        $txnData['_transaction_type'] = '_sale';
        $txnData['_transaction_status'] = '_success';

        try {
            $api_instance = new \ai\thirdwatch\Api\TransactionApi(new GuzzleHttp\Client(), $config);
            $jsonTxn = new \ai\thirdwatch\Model\Transaction($txnData);
            $result = $api_instance->transaction($jsonTxn);
            $this->write_debug_log("Order ID: ".$this->order->get_order_number()." - Sucessfully created transaction on Thirdwatch.");
        }
        catch (\Throwable $e) {
            $this->write_debug_log($e->getMessage());
        }

        $dt = new DateTime();

        try {
            global $wpdb;
            $customers = $wpdb->insert($wpdb->prefix."tw_orders", 
                array("order_id"=>$this->order->get_id(), 
                        "order_number"=>$this->order->get_order_number(), 
                        "status" => "HOLD", 
                        "date_created"=> $dt->format('Y-m-d H:i:s'), 
                        "date_modified"=>$dt->format('Y-m-d H:i:s')
                    )
                );
        }
        catch (\Throwable $e) {
            $this->write_debug_log($e->getMessage());
        }
    }

    /**
     * Add risk score column to order list.
     */
    public function add_column( $columns ) {
        if ( $this->enabled != 'yes' ) {
            return $columns;
        }

        $columns = array_merge( array_slice( $columns, 0, 5 ), array( 'thirdwatch_score' => 'Thirdwatch Status' ), array_slice( $columns, 5 ) );
        return $columns;
    }

    /**
     * Fill in Thirdwatch score into risk score column.
     */
    public function render_column( $column ) {
        if (( $this->enabled != 'yes' ) || ( $column != 'thirdwatch_score' )) return;

        global $post;

        $result = $this->get_wp_tw_order_details($post->ID);

        echo "Status: ". $result->status.'<br/>';
        echo "Flag: ". $result->flag.'<br/>';
        echo "Action: ". $result->action.'<br/>';
        echo "Message: ". $result->message.'<br/>';
    }

    /**
     * Write to debug log to record details of process.
     */
    public function write_debug_log($message) {
        if ( $this->debug_log != 'yes' ) return;

        if ( is_array( $message ) || is_object( $message ) ) {
            file_put_contents( THIRDWATCH_ROOT . 'debug.log', gmdate('Y-m-d H:i:s') . "\t" . print_r( $message, true ) . "\n", FILE_APPEND );
        } else {
            file_put_contents( THIRDWATCH_ROOT . 'debug.log', gmdate('Y-m-d H:i:s') . "\t" . $message . "\n", FILE_APPEND );
        }
    }

    /**
     * Get plugin settings.
     */
    private function get_setting( $key ) {
        return get_option( 'wc_settings_woocommerce-thirdwatch_' . $key );
    }

    /**
     * Includes required scripts and styles.
     */
    public function admin_enqueue_scripts( $hook ) {
        if ( is_admin() ) {
            wp_enqueue_script( 'thirdwatch_woocommerce_admin_script', plugins_url( '/assets/js/script.js', TW_PLUGIN_FILE ), array( 'jquery' ), '1.0', true );
        }

        if ( is_admin() && get_user_meta( get_current_user_id(), 'thirdwatch_woocommerce_admin_notice', true ) !== 'dismissed' ) {
            wp_localize_script( 'thirdwatch_woocommerce_admin_script', 'thirdwatch_woocommerce_admin', array( 'thirdwatch_woocommerce_admin_nonce' => wp_create_nonce( 'thirdwatch_woocommerce_admin_nonce' ), ) );
        }

        wp_enqueue_style( 'thirdwatch_woocommerce_admin_menu_styles', untrailingslashit( plugins_url( '/', TW_PLUGIN_FILE ) ) . '/assets/css/style.css', array() );

        if ( $hook != 'toplevel_page_woocommerce-thirdwatch ' ) {
            return;
        }
    }

    /**
     * Add notification in dashboard.
     */
    public function admin_notices() {
        if ( get_user_meta( get_current_user_id(), 'thirdwatch_woocommerce_admin_notice', true ) === 'dismissed' ) {
            return;
        }

        $current_screen = get_current_screen();

        if ( 'plugins' == $current_screen->parent_base ) {
            if ( ! $this->api_key ) {
                echo '
                <div id="thirdwatch-woocommerce-notice" class="error notice is-dismissible">
                    <p>
                        ' . __( 'Thirdwatch setup is not complete. Please go to <a href="' . admin_url( 'admin.php?page=woocommerce-thirdwatch' ) . '">setting page</a> to enter your API key.', $this->namespace ) . '
                    </p>
                </div>
                ';
            }
        }
    }

    /**
     *  Dismiss the admin notice.
     */
    function plugin_dismiss_admin_notice() {
        if ( ! isset( $_POST['thirdwatch_woocommerce_admin_nonce'] ) || ! wp_verify_nonce( $_POST['thirdwatch_woocommerce_admin_nonce'], 'thirdwatch_woocommerce_admin_nonce' ) ) {
            wp_die();
        }
        update_user_meta( get_current_user_id(), 'thirdwatch-woocommerce-notice', 'dismissed' );
    }

        /**
     * Admin menu.
     */
    public function admin_menu() {
        add_menu_page( 'Thirdwatch', 'Thirdwatch', 'manage_options', 'woocommerce-thirdwatch', array( $this, 'settings_page' ), 'dashicons-admin-thirdwatch', 30 );
    }

    private function update_setting( $key, $value = null ) {
        return update_option( 'wc_settings_woocommerce-thirdwatch_' . $key, $value );
    }

    /**
     * Settings page.
     */
    public function settings_page() {
        if ( !is_admin() ) {
            $this->write_debug_log( 'Not logged in as administrator. Settings page will not be shown.' );
            return;
        }

        $form_status = '';
        $wc_order_statuses = array();

        if ( ! tw_is_osm_active() ) {
            $wc_order_statuses = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
        }
        else {

            global $wpdb;
            $tablename = $wpdb->prefix . 'posts';
            $result = $wpdb->get_results("SELECT post_title, post_name FROM  " . $tablename . " WHERE post_type = 'wc_order_status' and post_status = 'publish'");
            foreach ($result as $value) {
                $wc_order_statuses[$value->post_name] = $value->post_title;
            }
        }

        $enable_wc_tw = ( isset( $_POST['submit'] ) && isset( $_POST['enable_wc_tw'] ) ) ? 'yes' : ( ( ( isset( $_POST['submit'] ) && !isset( $_POST['enable_wc_tw'] ) ) ) ? 'no' : $this->get_setting( 'enabled' ) );

        $api_key = ( isset( $_POST['api_key'] ) ) ? $_POST['api_key'] : $this->get_setting( 'api_key' );

        $saved_approve_status = $this->get_setting( 'approve_status' );
        $saved_review_status  = $this->get_setting( 'review_status' );
        $saved_reject_status  = $this->get_setting( 'reject_status' );

        $prefilled_approve_status = ( isset($saved_approve_status) && $saved_approve_status != '' && !empty($saved_approve_status) ) ? $saved_approve_status : 'wc-processing';
        $prefilled_review_status  = ( isset($saved_review_status)  && $saved_review_status  != '' && !empty($saved_review_status) )  ? $saved_review_status  : 'wc-on-hold';
        $prefilled_reject_status  = ( isset($saved_reject_status)  && $saved_reject_status  != '' && !empty($saved_reject_status) )  ? $saved_reject_status  : 'wc-cancelled';

        $approve_status = ( isset( $_POST['approve_status'] ) ) ? $_POST['approve_status'] : $prefilled_approve_status;
        $review_status  = ( isset( $_POST['review_status'] ) )  ? $_POST['review_status']  : $prefilled_review_status;
        $reject_status  = ( isset( $_POST['reject_status'] ) )  ? $_POST['reject_status']  : $prefilled_reject_status;

        $fraud_message  = ( isset( $_POST['fraud_message'] ) )  ? $_POST['fraud_message']  : $this->get_setting( 'fraud_message' );

        $enable_wc_tw_debug_log = ( isset( $_POST['submit'] ) && isset( $_POST['enable_wc_tw_debug_log'] ) ) ? 'yes' : ( ( ( isset( $_POST['submit'] ) && !isset( $_POST['enable_wc_tw_debug_log'] ) ) ) ? 'no' : $this->get_setting( 'debug_log' ) );

        if ( isset( $_POST['submit'] ) ) {
            if ( empty( $form_status ) ) {
                $this->update_setting( 'enabled', $enable_wc_tw );
                $this->update_setting( 'api_key', $api_key );
                $this->update_setting( 'approve_status', $approve_status );
                $this->update_setting( 'review_status', $review_status );
                $this->update_setting( 'reject_status', $reject_status );
                $this->update_setting( 'fraud_message', $fraud_message );
                $this->update_setting( 'debug_log', $enable_wc_tw_debug_log );

                $form_status = '<div id="message" class="updated"><p>Changes saved.</p></div>';

                $url = site_url();
                $actionPostback = $url . "/wp-json/thirdwatch/api/actionpostback/";
                $scorePostback = $url . "/wp-json/thirdwatch/api/scorepostback/";
                $shippingAddressPostback = $url . "/wp-json/thirdwatch/api/shippingaddresspostback/";
                $postbackHandler = $url . "/wp-json/thirdwatch/api/postbackhandler/";
                
                $jsonRequest = array(
                    'score_postback'=>$scorePostback,
                    'action_postback'=>$actionPostback,
                    'shipping_address_postback'=>$shippingAddressPostback,
                    'url'=>$postbackHandler,
                    'secret'=>$api_key
                );
                $response = wp_remote_post("https://api.thirdwatch.ai/neo/v1/addpostbackurl/", array(
                    'method' => 'POST',
                    'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
                    'httpversion' => '1.0',
                    'sslverify' => false,
                    'body' => json_encode($jsonRequest)
                ));
            }
        }

        if ( isset( $_POST['purge'] ) ) {
            global $wpdb;
            $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key LIKE "%thirdwatch%"');
            $form_status = '<div id="message" class="updated"><p>All data have been deleted.</p></div>';
        }

        echo '
        <div class="wrap">
            <h1>Thirdwatch for WooCommerce</h1>

            ' . $form_status . '

            <div class="notice notice-info"><p>If you would like to learn more about the setup process, please visit <a href="https://razorpay.com/blog/thirdwatch-woocommerce-installation/" target="_blank">Thirdwatch for WooCommerce</a>.</p></div>

            <form id="form_settings" method="post" novalidate="novalidate">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_wc_tw">Enable Thirdwatch Validation</label>
                        </th>
                        <td>
                            <input type="checkbox" name="enable_wc_tw" id="enable_wc_tw"' . ( ( $enable_wc_tw == 'yes' ) ? ' checked' : '' ) . '>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="api_key">API Key</label>
                        </th>
                        <td>
                            <input type="text" name="api_key" id="api_key" maxlength="32" value="' . $api_key . '" class="regular-text code" />
                            <p class="description">
                                You can sign up for a free API key at <strong><a href="https://dashboard.thirdwatch.ai/login?utm_source=module&utm_medium=banner&utm_term=woocommerce&utm_campaign=module%20banner&client_type=Woocommerce" target="_blank">Thirdwatch</a></strong>.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="approve_status">Approve Status</label>
                        </th>
                        <td>
                            <select name="approve_status" id="approve_status">';

        foreach ( $wc_order_statuses as $key => $status ) {
            echo '
                                <option value="' . $key . '"' . ( ( $approve_status == $key ) ? ' selected' : '' ) . '> ' . $status . '</option>';
        }

        echo '
                            </select>
                            <p class="description">
                                Update order status when it has been <strong>Passed</strong> by Thirdwatch, or the <strong>Approve</strong> action has been taken from Thirdwatch Dashboard.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="review_status">Review Status</label>
                        </th>
                        <td>
                            <select name="review_status" id="review_status">';

        foreach ( $wc_order_statuses as $key => $status ) {
            echo '
                                <option value="' . $key . '"' . ( ( $review_status == $key ) ? ' selected' : '' ) . '> ' . $status . '</option>';
        }

        echo '
                            </select>
                            <p class="description">
                                Update order status when order has been <strong>FLAGGED</strong> by Thirdwatch.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="reject_status">Reject Status</label>
                        </th>
                        <td>
                            <select name="reject_status" id="reject_status">';

        foreach ( $wc_order_statuses as $key => $status ) {
            echo '
                                <option value="' . $key . '"' . ( ( $reject_status == $key ) ? ' selected' : '' ) . '> ' . $status . '</option>';
        }

        echo '
                            </select>
                            <p class="description">
                                Update order status when the <strong>Decline</strong> action has been taken from Thirdwatch Dashboard.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="fraud_message">Fraud Message</label>
                        </th>
                        <td>
                            <textarea name="fraud_message" id="fraud_message" class="large-text" rows="3">' . $fraud_message . '</textarea>
                            <p class="description">
                                Display this message to customer if the order failed the validation (<strong>REJECT</strong> case).
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="enable_wc_tw_debug_log">Enable Debug Log for development purposes</label>
                        </th>
                        <td>
                            <input type="checkbox" name="enable_wc_tw_debug_log" id="enable_wc_tw_debug_log"' . ( ( $enable_wc_tw_debug_log == 'yes' ) ? ' checked' : '' ) . '>
                        </td>
                    </tr>

                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />
                </p>
            </form>

            <p>
                <form id="form-purge" method="post">
                    <input type="hidden" name="purge" value="true">
                    <p>Remove <strong>all Thirdwatch for WooCommerce data</strong> from storage.</p>
                    <input type="button" name="button" id="button-purge" class="button button-primary" value="Delete All Data" />
                </form>
            </p>

        </div>';
    }


    /* 
    Get order details from TW order table by order ID or order number
     */
    function get_wp_tw_order_details($order_id) {
        try{
            $this->write_debug_log("Order ID: ".$order_id." - Searching order in database.");

            global $wpdb;
            $order_rs = array();
            $tablename = $wpdb->prefix.'tw_orders';

            # Check in order_number column
            $order_rs = $wpdb->get_row( "SELECT * FROM ".$tablename." WHERE order_number = '".$order_id."'" );

            # If not found in order_number column check in order_in column
            if(empty($order_rs)) $order_rs = $wpdb->get_row( "SELECT * FROM ".$tablename." WHERE order_id = '".$order_id."'" );

            # If still no data found
            if(empty($order_rs)) {
                $this->write_debug_log("Order ID: ".$order_id." - No order found with this ID or Number in database.");
            } else {
                $this->write_debug_log("Order ID: ".$order_id." - Order found with ID '".$order_rs->order_id."' & Number '".$order_rs->order_number."'.");
            }

            return $order_rs;
        }
        catch (\Throwable $e) {
            $this->write_debug_log($e->getMessage());
        }
    }

    function woocom_mark_order_paid($payload) {
        $this->write_debug_log("Postback Handler redirected to woocom_mark_order_paid method.");
        $method_rs['status'] = 'failed';

        # Check and verify order ID received in payload
        if($this->is_invalid_param($payload['order_id'])) {
            $method_rs["message"] = "Invaid Order ID.";
            $this->write_debug_log("Invaid Order ID received in woocom_mark_order_paid method.");
            return $method_rs;
        }

        # Get order details from order table
        $this->write_debug_log("Order ID: ".$payload['order_id']." - Started processing woocom_mark_order_paid method.");
        $order_rs = $this->get_wp_tw_order_details($payload['order_id']);

        # Check if the order is available in DB tables
        if(empty($order_rs) || ($this->is_invalid_param($order_rs->order_id))) {
            $method_rs["message"] = "order_id '".$payload['order_id']."' not available in orders table.";
            $this->write_debug_log("Order ID: ".$payload['order_id']." - ".$method_rs["message"]);
            return $method_rs;
        }
        
        # Check payload status for 'paid'. No action on status other than 'paid'.
        if( ($this->is_invalid_param($payload['status'])) || ($payload['status'] != 'paid')) {
            $method_rs["message"] = "Invalid payment status received for order_id '".$payload['order_id']."'.";
            $this->write_debug_log("Order ID: ".$payload['order_id']." - ".$method_rs["message"]);
            return $method_rs;
        }

        # Get order details from woocommerce
        $order_data = wc_get_order( $order_rs->order_id );

        # Check if order exists in WooCommerce
        if($this->is_invalid_param($order_data)) {
            $method_rs["message"] = "Unable to find order in woocommerce with order_id '".$payload['order_id']."'.";
            $this->write_debug_log("Order ID: ".$payload['order_id']." - ".$method_rs["message"]);
            return $method_rs;
        }

        # Check if the order is COD or not
        if(($this->is_invalid_param($order_data->get_payment_method())) || ($order_data->get_payment_method() != 'cod')) {
            $method_rs["message"] = "Cannot mark a non-cod order as paid in woocommerce for order_id '".$payload['order_id']."'.";
            $this->write_debug_log("Order ID: ".$payload['order_id']." - ".$method_rs["message"]);
            return $method_rs;
        }

        # Update payment method of the order
        $this->write_debug_log("Order ID: ".$payload['order_id']." - Setting Payment method as 'other' in woocom_mark_order_paid method.");
        $order_data->set_payment_method('other');
        $this->write_debug_log("Order ID: ".$payload['order_id']." - Payment method updated as 'other' in woocom_mark_order_paid method.");

        try{
            # Get Order total
            $order_total = $order_data->get_total();

            # Create custom coupon
            $coupon_code = 'PREPAYCOD_'.$payload['order_id'];
            $discount_amount = $payload['discount'];
            $discount_type = 'fixed_cart'; # Type: fixed_cart, percent, fixed_product, percent_product

            $coupon = array(
                'post_title' => $coupon_code,
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'shop_coupon'
            );

            $this->write_debug_log("Order ID: ".$payload['order_id']." - Creating a custom coupon in woocom_mark_order_paid method.");
            $new_coupon_id = wp_insert_post($coupon);
            $this->write_debug_log("Order ID: ".$payload['order_id']." - Created custom coupon ".$new_coupon_id." - ".$coupon_code." in woocom_mark_order_paid method.");

            # Add coupon meta
            update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
            update_post_meta( $new_coupon_id, 'coupon_amount', $discount_amount );
            update_post_meta( $new_coupon_id, 'individual_use', 'no' );
            update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
            update_post_meta( $new_coupon_id, 'free_shipping', 'no' );

            # Apply Coupon on order
            $this->write_debug_log("Order ID: ".$payload['order_id']." - Apply custom coupon ".$new_coupon_id." - ".$coupon_code." .");
            $order_data->apply_coupon($coupon_code);
            $this->write_debug_log("Order ID: ".$payload['order_id']." - Applied custom coupon ".$new_coupon_id." - ".$coupon_code." .");

            # Update Order
            $order_data->save();

            # Delete coupon
            $this->write_debug_log("Order ID: ".$payload['order_id']." - Delete custom coupon ".$new_coupon_id." - ".$coupon_code." .");
            wp_delete_post($new_coupon_id);
            $this->write_debug_log("Order ID: ".$payload['order_id']." - Deleted custom coupon ".$new_coupon_id." - ".$coupon_code." .");

            $this->write_debug_log("Order ID: ".$payload['order_id']." - Adding order note for Order Paid in woocom_mark_order_paid method.");
            $order_data->add_order_note("<strong>Paid via Thirdwatch Prepay CoD</strong>\n<i>Payment Amount:</i> ₹".$payload['payment_amount']."\n<i>Discount:</i> ₹".$payload['discount']."\n<i>Order Amount:</i> ₹".$payload['order_amount']."\n<i>Details:</i> <a href='".$payload['payment_link']."' target='_blank'>View</a>");

            $method_rs["message"] = "Order updated with payment link details in woocommerce store - order_id '".$payload['order_id']."'";
            $method_rs["status"] = "success";
            $this->write_debug_log("Order ID: ".$payload['order_id']." - ".$method_rs["message"]);
        }
        catch (\Throwable $e) {
            $this->write_debug_log($e->getMessage());
            $method_rs["message"] = "Failed to mark order as paid in woocommerce for order_id '".$payload['order_id']."': ".$e->getMessage();
        }

        return $method_rs;
    }

    # Validate paramter for invalid values
    function is_invalid_param($param) {
        if(($param == '') || ($param == null) || ($param === true) || ($param === false) || (!$param) || (empty($param)) || (!isset($param))) {
            return true;
        } else {
            return false;
        }
    }

}