<?php
/**
 * PowerPack for MemberMouse LITE - Google Enhanced eCommerce Functionality
 * @since 1.0.0
 * @author Mintun Media
 * Uses https://developers.google.com/analytics/devguides/collection/protocol/v1/reference for sending enhanced ecommerce events
 */

if (!defined('ABSPATH')) {
  exit;
}

class PowerPack_Lite_Ecommerce {
  private static $instance;

  public static function instance() {
    // Check instance
    if (!isset(self::$instance)) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
  * Load all class functionality
  */
  public function __construct() {

    if (get_option('powerpack-plugin-options')) {
      $options = get_option('powerpack-plugin-options');

      if (isset($options['ecom-function']) && 1 == $options['ecom-function']) {
        // Add Analytics to Head
        if (isset($options['google-add-analytics']) && 1 == $options['google-add-analytics']) {
          add_action('wp_head', array($this, 'ppfml_add_ua_code'));
        }
        // Record Payments to GA Ecommerce
        if (isset($options['google-add-ecommerce']) && 1 == $options['google-add-ecommerce']) {
          add_action('mm_payment_received', array($this, 'ppfml_record_payment'));
        }
        // Record Rebills to GA Ecommerce
        if (isset($options['google-track-rebills']) && 1 == $options['google-track-rebills']) {
          add_action('mm_payment_rebill', array($this, 'ppfml_record_rebill'));
        }
        // Record Refunds to GA Ecommerce
        if (isset($options['google-track-refunds']) && 1 == $options['google-track-refunds']) {
          add_action('mm_refund_issued', array($this, 'ppfml_record_refund'));
        }
      }
    }
  }

  /**
  * Add Google Analytics Pixel to site
  * Includes functionality to exclude certain user roles
  * @since 1.0.0
  */
  public function ppfml_add_ua_code() {

    $options = get_option('powerpack-plugin-options');
    $uacode = $options['google-id'];
    $exclroles = [];

    if (isset($options['google-exclude-admin']) && 1 == $options['google-exclude-admin']) {
      $exclroles[] = 'administrator';
    }
    if (isset($options['google-exclude-editor']) && 1 == $options['google-exclude-editor']) {
      $exclroles[] = 'editor';
    }
    if (isset($options['google-exclude-author']) && 1 == $options['google-exclude-author']) {
      $exclroles[] = 'author';
    }
    if (isset($options['google-exclude-contributor']) && 1 == $options['google-exclude-contributor']) {
      $exclroles[] = 'contributor';
    }
    if (isset($options['google-exclude-subscriber']) && 1 == $options['google-exclude-subscriber']) {
      $exclroles[] = 'subscriber';
    }
    if (count($exclroles) > 0) {
      if ($this->ppfml_track_curr_user($exclroles)) {
        return;
      }
    }
    ?>
    <!-- Google UA Code -->
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      ga('create', '<?php echo $uacode; ?>', 'auto');
      ga('send', 'pageview');
    </script>
    <?php
  }

  /**
  * Function that records payment events and sends to GA Enhanced eCommerce
  * @since 1.0.0
  * Contributor: Chip Oglesby
  */
  public function ppfml_record_payment($orderdata) {

    if (get_option('powerpack-plugin-options')) {
      $options = get_option('powerpack-plugin-options');
      $uacode = $options['google-id'];
      $website = get_bloginfo('wpurl');
      $sitename = get_bloginfo('name');
      $products = json_decode(stripslashes($orderdata["order_products"]));
      $gaUserId = preg_replace("/^.+\.(.+?\..+?)$/", "\\1", @$_COOKIE['_ga']);

      $data = array(
        'v' => 1,
        'tid' => $uacode,
        'cid' => $gaUserId,
        't' => 'event',
        'dh' => $website,
        'dp' => '/checkout',
        'ec'  => 'Ecommerce', // Event Category
        'ea'  => 'Initial Payment', // Event Action
        'ev'  => round($orderdata['order_total']), // Event value
        'ti'  => $orderdata['order_number'], // Transaction ID
        'tr'  => $orderdata['order_total'], // Transaction Revenue - includes discounts or coupons applied
        'tt'  => 0, // Transaction Tax
        'ts'  => 0, // Transaction Shipping
        'pa'  => 'purchase', // Product Action
      );

      /**
       * Product Handling - Add Products to Array. Used for Enhanced Ecommerce
       * Note: MemberMouse only ever has 1 product during checkout, so we're setting the product "counter" to 1.
       */
      $counter = 1;
      foreach ($products as $product) {
        $data['pr' . $counter . 'nm'] = $product->name; // Product Name
        $data['pr' . $counter . 'id'] = $product->id; // Product ID
        $data['pr' . $counter . 'pr'] = $orderdata['order_total']; // Product Price
        $data['pr' . $counter . 'qt'] = 1; // Product Quantity
      }

      /**
       * Coupon Handling
       */
      if (!empty($orderdata["order_coupons"])) {
          $coupons = json_decode(stripslashes($orderdata["order_coupons"]));
          foreach ($coupons as $coupon) {
              $data['pr' . $counter . 'cc'] = $coupon->code;
          }
      }
      $url = 'https://www.google-analytics.com/collect';
      $this->ppfml_send_google_data($url, $data);
    }
  }

  /**
  * Function records rebill (recurring) payments
  * @since 1.0.0
  */
  public function ppfml_record_rebill($orderdata) {

    if (get_option('powerpack-plugin-options')) {
      $options = get_option('powerpack-plugin-options');
      $uacode = $options['google-id'];
      $website = get_bloginfo('wpurl');
      $sitename = get_bloginfo('name');
      $products = json_decode(stripslashes($orderdata["order_products"]));
      $counter = 1;
      $gaUserId = preg_replace("/^.+\.(.+?\..+?)$/", "\\1", @$_COOKIE['_ga']);

      $data = array(
        'v'   => 1,
        'tid' => $uacode,
        'cid' => $gaUserId,
        't'   => 'event',
        'dh'  => $website,
        'dp'  => '/checkout',
        'ec'  => 'Ecommerce', // Event Category
        'ea'  => 'Rebill', // Event Action
        'ev'  => round($orderdata['order_total']), // Event value
        'ti'  => $orderdata['order_number'], // Transaction ID
        'tr'  => $orderdata['order_total'], // Transaction Revenue - includes discounts or coupons applied
        'tt'  => 0, // Transaction Tax
        'ts'  => 0, // Transaction Shipping
        'pa'  => 'purchase', // Product Action
      );

      /**
       * Product Handling - Add Products to Array. Used for Enhanced Ecommerce
       * Note: MemberMouse only ever has 1 product during checkout, so we're setting the product "counter" to 1.
       */
      $counter = 1;
      foreach ($products as $product) {
        $data['pr' . $counter . 'nm'] = $product->name; // Product Name
        $data['pr' . $counter . 'id'] = $product->id; // Product ID
        $data['pr' . $counter . 'pr'] = $orderdata['order_total']; // Product Price
        $data['pr' . $counter . 'qt'] = 1; // Product Quantity
      }

      /**
       * Coupon Handling
       */
      if (!empty($orderdata["order_coupons"])) {
        $coupons = json_decode(stripslashes($orderdata["order_coupons"]));
        foreach ($coupons as $coupon) {
          $data['pr' . $counter . 'cc'] = $coupon->code;
        }
      }

      $url = 'https://www.google-analytics.com/collect';
      $this->ppfml_send_google_data($url, $data);

    }
  }

  /**
  * Function records refunds
  * @since 1.0.0
  */

  public function ppfml_record_refund($orderdata) {

    if (get_option('powerpack-plugin-options')) {

      $options = get_option('powerpack-plugin-options');
      $uacode = $options['google-id'];
      $website = get_bloginfo('wpurl');
      $sitename = get_bloginfo('name');
      $counter = 1;

      $products = json_decode(stripslashes($orderdata["order_products"]));

      $data = array(
        'v' => 1,
        'tid' => $uacode,
        't' => 'event',
        'dh' => $website,
        'dp' => '/checkout',
        'ec'  => 'Ecommerce', // Event Category
        'ea'  => 'Refund', // Event Action
        'ev'  => round($orderdata['order_total']), // Event value
        'ni'  => 1,
        'ti'  => $orderdata['order_number'], // Transaction ID
        'tr'  => $orderdata['order_total'], // Transaction Revenue - includes discounts or coupons applied
        'tt'  => 0, // Transaction Tax
        'ts'  => 0, // Transaction Shipping
        'pa'  => 'refund', // Product Action
      );

      /**
       * Product Handling - Add Products to Array. Used for Enhanced Ecommerce
       * Note: MemberMouse only ever has 1 product during checkout, so we're setting the product "counter" to 1.
       */
      $counter = 1;
      foreach ($products as $product) {
        $data['pr' . $counter . 'nm'] = $product->name; // Product Name
        $data['pr' . $counter . 'id'] = $product->id; // Product ID
        $data['pr' . $counter . 'pr'] = $orderdata['order_total']; // Product Price
        $data['pr' . $counter . 'qt'] = 1; // Product Quantity
      }

      /**
       * Coupon Handling
       */
      if (!empty($orderdata["order_coupons"])) {
        $coupons = json_decode(stripslashes($orderdata["order_coupons"]));
        foreach ($coupons as $coupon) {
          $data['pr' . $counter . 'cc'] = $coupon->code;
        }
      }

      $url = 'https://www.google-analytics.com/collect';
      $this->ppfml_send_google_data($url, $data);

    }
  }

  /**
  * Send data to Google Analytics
  * @since 1.0.0
  */
  public function ppfml_send_google_data($url, $data) {

    $response = wp_remote_post($url, array(
      'method' => 'POST',
      'timeout' => 45,
      'redirection' => 5,
      'httpversion' => '1.0',
      'blocking' => true,
      'headers' => array(),
      'body' => $data,
      'cookies' => array(),
    ));

    return $response;
  }

  /**
    * Function tracks current user. If they are a specific role, it doesn't send the data.
    * @since 1.0.0
    */
  public function ppfml_track_curr_user($access_level) {

    if (is_user_logged_in() && isset($access_level)) {
      $current_user = wp_get_current_user();
      $roles = (array) $current_user->roles;

      if (count(array_intersect($roles, $access_level)) > 0) {
        return true;
      } else {
        return false;
      }
    }
  }

} // End class

PowerPack_Lite_Ecommerce::instance();