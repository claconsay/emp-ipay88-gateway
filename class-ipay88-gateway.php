<?php

/*
 * iPay88 gateway add-on for Events Manager Pro
 * @package   Events_Manager_iPay88
 * @author Christopher Laconsay <claconsay[at]gmail[dot]com>
 * @license GPL-2.0+
 * @copyright 2015 claconsay 
 */

class EM_Gateway_iPay88 extends EM_Gateway {

    var $gateway = 'ipay88';
    var $title = 'iPay88';
    var $status = 4;
    var $status_txt = 'Awaiting iPay88 Payment';
    var $button_enabled = true;
    var $payment_return = true;

    /**
     * Sets up gateaway and adds relevant actions/filters 
     */
    function __construct() {
        parent::__construct();
        $this->status_txt = __('Awaiting iPay88 Payment', 'em-pro');
        if ($this->is_active()) {
            //Booking Interception
            if (absint($this->ipay88_option('booking_timeout')) > 0) {
                //Modify spaces calculations only if bookings are set to time out, in case pending spaces are set to be reserved.
                add_filter('em_bookings_get_pending_spaces', array(&$this, 'em_bookings_get_pending_spaces'), 1, 2);
            }
            add_action('em_gateway_js', array(&$this, 'em_gateway_js'));
            //Gateway-Specific
            add_action('em_template_my_bookings_header', array(&$this, 'say_thanks')); //say thanks on my_bookings page
            add_filter('em_bookings_table_booking_actions_4', array(&$this, 'bookings_table_actions'), 1, 2);
            add_filter('em_my_bookings_booking_actions', array(&$this, 'em_my_bookings_booking_actions'), 1, 2);
            //set up cron
            $timestamp = wp_next_scheduled('emp_cron_hook');
            if (absint(get_option('em_ipay88_booking_timeout')) > 0 && !$timestamp) {
                $result = wp_schedule_event(time(), 'em_minute', 'emp_cron_hook');
            } elseif (!$timestamp) {
                wp_unschedule_event($timestamp, 'emp_cron_hook');
            }
        } else {
            //unschedule the cron
            $timestamp = wp_next_scheduled('emp_cron_hook');
            wp_unschedule_event($timestamp, 'emp_cron_hook');
        }
    }

    /*
     * --------------------------------------------------
     * Booking Interception - functions that modify booking object behaviour
     * --------------------------------------------------
     */

    /**
     * Modifies pending spaces calculations to include ipay88 bookings, but only if iPay88 bookings are set to time-out (i.e. they'll get deleted after x minutes), therefore can be considered as 'pending' and can be reserved temporarily.
     * @param integer $count
     * @param EM_Bookings $EM_Bookings
     * @return integer
     */
    function em_bookings_get_pending_spaces($count, $EM_Bookings) {
        foreach ($EM_Bookings->bookings as $EM_Booking) {
            if ($EM_Booking->booking_status == $this->status && $this->uses_gateway($EM_Booking)) {
                $count += $EM_Booking->get_spaces();
            }
        }
        return $count;
    }

    /**
     * Intercepts return data after a booking has been made and adds ipay88 vars, modifies feedback message.
     * @param array $return
     * @param EM_Booking $EM_Booking
     * @return array
     */
    function booking_form_feedback($return, $EM_Booking = false) {
        //Double check $EM_Booking is an EM_Booking object and that we have a booking awaiting payment.
        if (is_object($EM_Booking) && $this->uses_gateway($EM_Booking)) {
            if (!empty($return['result']) && $EM_Booking->get_price() > 0 && $EM_Booking->booking_status == $this->status) {
                $return['message'] = get_option('em_ipay88_booking_feedback');
                $ipay88_url = $this->get_ipay88_url();
                $ipay88_vars = $this->get_ipay88_vars($EM_Booking);
                $ipay88_return = array('ipay88_url' => $ipay88_url, 'ipay88_vars' => $ipay88_vars);
                $return = array_merge($return, $ipay88_return);
            } else {
                //returning a free message
                $return['message'] = get_option('em_ipay88_booking_feedback_free');
            }
        }
        return $return;
    }

    /**
     * Called if AJAX isn't being used, i.e. a javascript script failed and forms are being reloaded instead.
     * @param string $feedback
     * @return string
     */
    function booking_form_feedback_fallback($feedback) {
        global $EM_Booking;
        if (is_object($EM_Booking)) {
            $feedback .= "<br />" . __('Please click the following button to proceed to iPay88.', 'dbem') . $this->em_my_bookings_booking_actions('', $EM_Booking);
        }
        return $feedback;
    }

    /**
     * Triggered by the em_booking_add_yourgateway action, hooked in EM_Gateway. Overrides EM_Gateway to account for non-ajax bookings (i.e. broken JS on site).
     * @param EM_Event $EM_Event
     * @param EM_Booking $EM_Booking
     * @param boolean $post_validation
     */
    function booking_add($EM_Event, $EM_Booking, $post_validation = false) {
        parent::booking_add($EM_Event, $EM_Booking, $post_validation);
        if (!defined('DOING_AJAX')) { //we aren't doing ajax here, so we should provide a way to edit the $EM_Notices ojbect.
            add_action('option_dbem_booking_feedback', array(&$this, 'booking_form_feedback_fallback'));
        }
    }

    /*
     * --------------------------------------------------
     * Booking UI - modifications to booking pages and tables containing ipay88 bookings
     * --------------------------------------------------
     */

    /**
     * Instead of a simple status string, a resume payment button is added to the status message so user can resume booking from their my-bookings page.
     * @param string $message
     * @param EM_Booking $EM_Booking
     * @return string
     */
    function em_my_bookings_booking_actions($message, $EM_Booking) {
        global $wpdb;
        if ($this->uses_gateway($EM_Booking) && $EM_Booking->booking_status == $this->status) {
            //first make sure there's no pending payments
            $pending_payments = $wpdb->get_var('SELECT COUNT(*) FROM ' . EM_TRANSACTIONS_TABLE . " WHERE booking_id='{$EM_Booking->booking_id}' AND transaction_gateway='{$this->gateway}' AND transaction_status='Pending'");
            if ($pending_payments == 0) {
                //user owes money!
                $ipay88_vars = $this->get_ipay88_vars($EM_Booking);
                $form = '<form action="' . $this->get_ipay88_url() . '" method="post">';
                foreach ($ipay88_vars as $key => $value) {
                    $form .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
                }
                $form .= '<input type="submit" value="' . __('Resume Payment', 'em-pro') . '">';
                $form .= '</form>';
                $message .= $form;
            }
        }
        return $message;
    }

    /**
     * Outputs extra custom content e.g. the iPay88 logo by default. 
     */
    function booking_form() {
        echo $this->ipay88_option('form');
    }

    /**
     * Outputs some JavaScript during the em_gateway_js action, which is run inside a script html tag, located in gateways/gateway.ipay88.js
     */
    function em_gateway_js() {
        include(plugin_dir_path(__FILE__) . 'js/gateway.ipay88.js');
    }

    /**
     * Adds relevant actions to booking shown in the bookings table
     * @param EM_Booking $EM_Booking
     */
    function bookings_table_actions($actions, $EM_Booking) {
        return array(
            'approve' => '<a class="em-bookings-approve em-bookings-approve-offline" href="' . em_add_get_params($_SERVER['REQUEST_URI'], array('action' => 'bookings_approve', 'booking_id' => $EM_Booking->booking_id)) . '">' . __('Approve', 'dbem') . '</a>',
            'delete' => '<span class="trash"><a class="em-bookings-delete" href="' . em_add_get_params($_SERVER['REQUEST_URI'], array('action' => 'bookings_delete', 'booking_id' => $EM_Booking->booking_id)) . '">' . __('Delete', 'dbem') . '</a></span>',
            'edit' => '<a class="em-bookings-edit" href="' . em_add_get_params($EM_Booking->get_event()->get_bookings_url(), array('booking_id' => $EM_Booking->booking_id, 'em_ajax' => null, 'em_obj' => null)) . '">' . __('Edit/View', 'dbem') . '</a>',
        );
    }

    /*
     * --------------------------------------------------
     * iPay88 Functions - functions specific to ipay88 payments
     * --------------------------------------------------
     */

    /**
     * Retreive the ipay88 vars needed to send to the gatway to proceed with payment
     * @param EM_Booking $EM_Booking
     */
    function get_ipay88_vars($EM_Booking) {
        $responseurl = $this->get_payment_return_url();
        
        $ipay88_vars['MerchantCode'] = $this->ipay88_option('mercode');
        $ipay88_vars['MerchantKey'] = $this->ipay88_option('mercode');
        $ipay88_vars['PaymentId'] = $this->ipay88_option('mercode');
        $ipay88_vars['RefNo'] = $this->ipay88_option('mercode');
        $ipay88_vars['Amount'] = number_format($this->get_booking_total_price($EM_Booking), 2, '.', '');
        $ipay88_vars['Currency'] = get_option('dbem_bookings_currency', 'USD');
        $ipay88_vars['ProdDesc'] = '';
        $ipay88_vars['UserName'] = $EM_Booking->get_person()->first_name." ".$EM_Booking->get_person()->last_name;
        $ipay88_vars['UserEmail'] = $EM_Booking->get_person()->user_email;
        $ipay88_vars['UserContact'] = '';
        $ipay88_vars['Remark'] = '';
        $ipay88_vars['ResponseURL'] = $responseurl;
        $ipay88_vars['Lang'] = $this->ipay88_option('mercode');
        $gensign = $ipay88_vars['MerchantKey'] . $ipay88_vars['MerchantCode'] . $ipay88_vars['RefNo'] . str_replace(".", "", $ipay88_vars['Amount']) . $ipay88_vars['Currency'];
        $postsign = compute_signature($gensign);
        $ipay88_vars['Signature'] = $postsign;
        
        return apply_filters('em_gateway_ipay88_get_ipay88_vars', $ipay88_vars, $EM_Booking, $this);
    }

    /**
     * gets ipay88 gateway url
     * @returns string 
     */
    function get_ipay88_url() {
        return "https://www.mobile88.com/ePayment/entry.asp";
    }

    /**
     * gets booking total amount
     * @returns double
     */
    function get_booking_total_price($EM_Booking) {
        $count = 1;
        $purchase_amnt = 0;
        foreach ($EM_Booking->get_tickets_bookings()->tickets_bookings as $EM_Ticket_Booking) {
            $price = $EM_Ticket_Booking->get_ticket()->get_price();
            if ($price > 0) {
                $purchase_amnt += $price;
                $count++;
            }
        }
        return $purchase_amnt * $EM_Booking->get_spaces();
    }

    /**
     * Runs when iPay88 sends IPNs to the return URL provided during bookings and EM setup. Bookings are updated and transactions are recorded accordingly. 
     */
    function handle_payment_return() {
        //check if post variables has been set
        if (isset($_POST['ResponseCode']) && isset($_POST['ReasonCode']) && isset($_POST['ReasonCodeDesc'])) {
            //collect variables from the server: basic
            $respCode = $_POST['ResponseCode'];
            $reasCode = $_POST['ReasonCode'];
            $reasCodeDesc = $_POST['ReasonCodeDesc'];
            $cusID = $_POST['customerid'];

            /*
             * TODO: Handle responses from the gateway
             */
            switch ($respCode) {
                case 1: //case: payment is approved
                    $merId = $_POST['MerID'];
                    $acqId = $_POST['AcqID'];
                    $orderId = $_POST['OrderID'];
                    $respCode = $_POST['ResponseCode'];
                    $reasCode = $_POST['ReasonCode'];
                    $reasCodeDesc = $_POST['ReasonCodeDesc'];
                    $refNum = $_POST['ReferenceNo'];
                    $padCardNum = $_POST['PaddedCardNo'];
                    $authCode = $_POST['AuthCode'];
                    $sig = $_POST['Signature'];

                    /**
                     * check if signature is matched
                     * Make sure no alteration happened during the whole cycle
                     */
                    $hashString = $this->ipay88_option("password") . $merId . $acqId . $orderId . $respCode . $reasCode;
                    $hashSig = base64_encode(sha1($hashString, true)); // default sha1
                    /**
                     * Still have to check for $hashSig							
                     */
                    if ($this->ipay88_option("sigmethod") == "SHA1")
                        $hashSig = base64_encode(sha1($hashString, true));
                    else
                        $hashSig = base64_encode(md5($hashString, true));

                    if ($hashSig == $sig) {  //signature matched

                        /**
                         * get Booking ID and Event ID, extract from OrderID
                         * OderID is formatted as [BookingID]-[EventID]-[RandomNumber]	
                         */
                        $ids = explode('-', $orderId);
                        $bookingId = $ids[0];
                        $eventId = !empty($ids[1]) ? $ids[1] : 0;
                        $EM_Booking = new EM_Booking($bookingId);
                        if (!empty($EM_Booking->booking_id) && count($ids) == 3) { //booking exists

                            /**
                             * ipay88 doesn't get back how much the user has paid,
                             * We could just extract the amount from the Booking itself
                             */
                            $count = 1;
                            $purchase_amnt = 0;
                            foreach ($EM_Booking->get_tickets_bookings()->tickets_bookings as $EM_Ticket_Booking) {
                                $price = $EM_Ticket_Booking->get_ticket()->get_price();
                                if ($price > 0) {
                                    $purchase_amnt += $price;
                                    $count++;
                                }
                            }
                            $this->record_transaction($EM_Booking, $purchase_amnt, get_option('dbem_bookings_currency', 'USD'), current_time('mysql'), $refNum, "Confirmed", $reasCodeDesc);

                            if (!$this->ipay88_option('manual_approval', false) || !get_option('dbem_bookings_approval')) {
                                $EM_Booking->approve(true, true); //approve and ignore spaces
                            } else {
                                //TODO do something if payment not enough
                                $EM_Booking->set_status(0); //Set back to normal "pending"
                            }
                            do_action('em_payment_processed', $EM_Booking, $this);

                            //redirect user to a thank you page, if that you is set
                            if ($this->ipay88_option("thankyouurl")) {
                                header("Location: " . $this->ipay88_option("thankyouurl"));
                                /* Make sure that code below does not get executed when we redirect. */
                                exit;
                            }
                            echo $reasCodeDesc;
                        } else {
                            echo "booking not found! B: $respCode - E: $eventId";
                        }
                    } else {
                        echo "Signature did not much!";
                    }
                    break;
                case 2: //case: payment is decline				
                    $ids = explode('-', $cusID);
                    $bookingId = $ids[0];
                    $EM_Booking = new EM_Booking($bookingId);

                    //$this->record_transaction($EM_Booking, "0.00", get_option('dbem_bookings_currency', 'USD'), current_time('mysql'), $refNum, "Awaiting Payment", $reasCodeDesc);	
                   //do_action('em_payment_denied', $EM_Booking, $this);
       
                    if ($this->ipay88_option("paycancelled")) {
                        header("Location: " . $this->ipay88_option("paycancelled"));
                        /* Make sure that code below does not get executed when we redirect. */
                        exit;
                    }
                    echo $reasCodeDesc;
                    break;
                case 3: //case: error was found
                    $ids = explode('-', $cusID);
                    $bookingId = $ids[0];
                    $this->record_transaction($EM_Booking, "0.00", get_option('dbem_bookings_currency', 'USD'), current_time('mysql'), $refNum, "Awaiting Payment", $reasCodeDesc);
                    do_action('em_payment_denied', $EM_Booking, $this);
                    if ($this->ipay88_option("paycancelled")) {
                        header("Location: " . $this->ipay88_option("paycancelled"));
                        /* Make sure that code below does not get executed when we redirect. */
                        exit;
                    }
                    break;
                default:
                    header("Location: " . $this->ipay88_option("paycancelled"));
                    exit;
                // case: various error cases
            }
        } else {
            if ($this->ipay88_option("paycancelled")) {
                header("Location: " . $this->ipay88_option("paycancelled"));
                /* Make sure that code below does not get executed when we redirect. */
                exit;
            } else {
                echo "Sorry but I don't trust this transaction.";
            }
        }
    }

    /*
     * --------------------------------------------------
     * Gateway Settings Functions
     * --------------------------------------------------
     */

    /**
     * Outputs custom iPay88 setting fields in the settings page 
     */
    function mysettings() {
        include ( plugin_dir_path(__FILE__) . 'views/settings.php' );
    }

    /*
     * Run when saving iPay88 settings, saves the settings available in EM_Gateway_iPay88::mysettings()
     */

    function update() {
        parent::update();
        $gateway_options = array(
            "status" => wp_kses_data($_REQUEST[$this->gateway . '_status']),
            "booking_feedback" => wp_kses_data($_REQUEST[$this->gateway . '_booking_feedback']),
            "booking_feedback_free" => wp_kses_data($_REQUEST[$this->gateway . '_booking_feedback_free']),
            "mercode" => wp_kses_data($_REQUEST[$this->gateway . '_mercode']),
            "merkey" => wp_kses_data($_REQUEST[$this->gateway . '_merkey']),
            "payid" => wp_kses_data($_REQUEST[$this->gateway . '_payid']),
            "lang" => wp_kses_data($_REQUEST[$this->gateway . '_lang']),
            "thankyouurl" => wp_kses_data($_REQUEST[$this->gateway . '_thankyouurl']),
            "paycancelled" => wp_kses_data($_REQUEST[$this->gateway . '_paycancelled']),
            "booking_timeout" => $_REQUEST[$this->gateway . '_booking_timeout']
        );
        foreach ($gateway_options as $key => $option) {
            $this->update_option($key, $option);
        }
        //default action is to return true
        return true;
    }

    /**
     * Gets the gateway option from the correct place. Does not require prefixing of em_gatewayname_
     * Will be particularly useful when restricting possible gateway settings in MultiSite mode and sharing accross networks, use this and you're future-proof.
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    function ipay88_option($name) {
        return get_option('em_' . $this->gateway . '_' . $name);
    }

    /**
     * Updates the gateway option to the correct place. Does not require prefixing of em_gatewayname_
     * Will be particularly useful when restricting possible gateway settings in MultiSite mode and sharing accross networks, use this and you're future-proof.
     * @param string $name
     * @param mixed $value
     * @return boolean
     */
    function update_option($name, $value) {
        $v = stripslashes($value);
        return update_option('em_' . $this->gateway . '_' . $name, $v);
    }

    /*
     * Creates SHA1 hash to be use for signature
     * You may verify your signature with online tool provided by iPay88
     * http://www.mobile88.com/epayment/testing/TestSignature.asp
     * @param string $source
     */

    private function compute_signature($source) {
        return base64_encode($this->hex2bin(sha1($source)));
    }

    /*
     * Hex to bin operation.
     * 
     * @param string $hexSource
     */

    private function hex2bin($hexSource) {
        for ($i = 0; $i < strlen($hexSource); $i = $i + 2) {
            $bin .= chr(hexdec(substr($hexSource, $i, 2)));
        }
        return $bin;
    }

}

/**
 * Deletes bookings pending payment that are more than x minutes old, defined by ipay88 options.  
 */
function em_gateway_ipay88_booking_timeout() {
    global $wpdb;
    //Get a time from when to delete
    $minutes_to_subtract = absint(get_option('em_ipay88_booking_timeout'));
    if ($minutes_to_subtract > 0) {
        //get booking IDs without pending transactions
        $booking_ids = $wpdb->get_col('SELECT b.booking_id FROM ' . EM_BOOKINGS_TABLE . ' b LEFT JOIN ' . EM_TRANSACTIONS_TABLE . " t ON t.booking_id=b.booking_id  WHERE booking_date < TIMESTAMPADD(MINUTE, -{$minutes_to_subtract}, NOW()) AND booking_status=4 AND transaction_id IS NULL");
        if (count($booking_ids) > 0) {
            //first delete ticket_bookings with expired bookings
            $sql = "DELETE FROM " . EM_TICKETS_BOOKINGS_TABLE . " WHERE booking_id IN (" . implode(',', $booking_ids) . ");";
            $wpdb->query($sql);
            //then delete the bookings themselves
            $sql = "DELETE FROM " . EM_BOOKINGS_TABLE . " WHERE booking_id IN (" . implode(',', $booking_ids) . ");";
            $wpdb->query($sql);
        }
    }
}

add_action('emp_cron_hook', 'em_gateway_ipay88_booking_timeout');
?>