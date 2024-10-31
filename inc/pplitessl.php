<?php
/**
 * PowerPack for MemberMouse LITE - SSL functionality
 * @since 1.0.0
 * @author Mintun Media & Premium Biz Themes
 */

if (!defined('ABSPATH')) {
	exit;
}

class PowerPack_Lite_SSL {

	private static $instance;

	public $siteurl = false;
	public $mmoptions;

	public static function instance() {

		// Check instance
		if (!isset(self::$instance))
			self::$instance = new self;

		// Done
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->siteurl = site_url('', 'http');
		$this->mmoptions = get_option('powerpack-plugin-options');

		if(!is_admin() && isset($this->mmoptions['easyssl-function']) && $this->mmoptions['easyssl-function'] ) {

			if(isset($this->mmoptions['easyssl-function']) && $this->mmoptions['easyssl-function'] && isset($this->mmoptions['easyssl-support']) && $this->mmoptions['easyssl-support'] ) {
				add_action('template_redirect', array($this,'ppfml_force_ssl'));
			}

			if(isset($this->mmoptions['easyssl-mixed']) && $this->mmoptions['easyssl-mixed']) {
				add_filter('script_loader_src', array(&$this, 'ppfml_URLfilter'), 99999);
				add_filter('style_loader_src', array(&$this, 'ppfml_URLfilter'), 99999);

				// Attachments URL in frontend or AJAX context
				if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX))
					add_filter('wp_get_attachment_url', array(&$this, 'ppfml_URLfilter'), 99999);

				// Content filters
				add_filter('the_content', array(&$this, 'ppfml_contentFilter'), 99999);
				add_filter('widget_text', array(&$this, 'ppfml_contentFilter'), 99999);

				// Gravity Forms confirmation content
				add_filter('gform_confirmation', array(&$this, 'ppfml_contentFilter'), 99999);

				// Upload URLs
				add_filter('upload_dir', array(&$this, 'ppfml_uploadFilter'), 99999);

				// Image Widget plugin
				add_filter('image_widget_image_url', array(&$this, 'ppfml_URLfilter'), 99999);
			}
		}

	}

	/**
	 * Function that forces SSL on all urls given options
	 * @since 1.0.0
	 */
	public function ppfml_force_ssl() {

		global $post;
		$isCorePage = MM_CorePageEngine::isCorePage($post->ID);


		if($this->mmoptions['easyssl-httpsetup'] == "all" || ($this->mmoptions['easyssl-httpsetup'] == "MM" && $isCorePage)) {

			$servhost = $_SERVER['HTTP_HOST'];

			if(!is_ssl()) {
				$redirurl = 'https://' . $servhost . $_SERVER['REQUEST_URI'];
				wp_redirect($redirurl, 301 );
				exit();
			}
		}
	}

	/**
	 * Function redirects user based on URL structure selected in settings
	 * @since 1.0.0
	 */
	public function ppfml_redirect_user($url) {

		global $pagenow;

		$crntUrl = MM_Utils::constructPageUrl();

		if(!MM_CorePageEngine::isCorePageOfType($crntUrl,MM_CorePageType::$LOGIN_PAGE)) {

			$url = "https://" . $url;

			wp_redirect($url, 301 );
			exit();

		}
	}

	/**
	 * Filters content URLs
	 * Source: https://wordpress.org/plugins/ssl-insecure-content-fixer/
	 * @since 1.0.0
	 */
	public function ppfml_contentFilter($content) {
		static $searches = array(
			'#<(?:img|iframe) .*?src=[\'"]\Khttp://[^\'"]+#i',		// fix image and iframe elements
			'#<link [^>]+href=[\'"]\Khttp://[^\'"]+#i',						// fix link elements
			'#<script [^>]*?src=[\'"]\Khttp://[^\'"]+#i',					// fix script elements
			'#url\([\'"]?\Khttp://[^)]+#i',												// inline CSS e.g. background images
		);
		$content = preg_replace_callback($searches, array($this, 'content_callback'), $content);

		// fix object embeds
		static $embed_searches = array(
			'#<object .*?</object>#is',								// fix object elements, including contained embed elements
			'#<embed .*?(?:/>|</embed>)#is',					// fix embed elements, not contained in object elements
			'#<img [^>]+srcset=["\']\K[^"\']+#is',		// responsive image srcset links (to external images; WordPress already handles local images)
		);
		$content = preg_replace_callback($embed_searches, array($this, 'embed_callback'), $content);

		return $content;
	}

	/**
	 * Callback for internal links
	 * @since 1.0.0
	 */
	public function ppfml_content_callback($matches) {

		if ($this->siteurl && stripos($matches[0], $this->siteurl) !== 0) {
			return $matches[0];
		}

		return 'https' . substr($matches[0], 4);
	}

	/**
	 * Callback for embedded objects
	 * @since 1.0.0
	 */
	public function ppfml_embed_callback($matches) {

		// Matches from start of http: and uses content_callback function
		$content = preg_replace_callback('#http://[^\'"&\? ]+#i', array($this, 'ppfml_content_callback'), $matches[0]);

		return $content;
	}


	/**
	 * Filter Uploads dir
	 * @since 1.0.0
	 */
	public function ppfml_uploadFilter($uploads) {
		$uploads['url']	= $this->ppfml_URLfilter($uploads['url']);
		$uploads['baseurl']	= $this->ppfml_URLfilter($uploads['baseurl']);
		return $uploads;
	}


	/**
	 * Filter URL
	 * @since 1.0.0
 	 */
	public function ppfml_URLfilter($url) {
		// only fix if source URL starts with http://
		if (stripos($url, 'http://') === 0) {
			$url = '' . substr($url, 5);
		}

		return $url;
	}

	/**
	 * Function checks that website has SSL certficate
	 * @since 1.0.0
	 */
	public static function ppfml_has_ssl( $domain, $show_msg = false ) {

		$res = false;
		$stream = @stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true ) ) );
		$socket = @stream_socket_client( 'ssl://' . $domain . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $stream );

		// If we got a ssl certificate we check here, if the certificate domain
		// matches the website domain.

		if ( $socket ) {
			$cont = stream_context_get_params( $socket );
			$cert_ressource = $cont['options']['ssl']['peer_certificate'];
			$cert = openssl_x509_parse( $cert_ressource );

			// Expected name has format "/CN=*.yourdomain.com"
			$namepart = explode( '=', $cert['name'] );

			// We want to correctly confirm the certificate even

			if ( count( $namepart ) == 2 ) {
				$cert_domain = trim( $namepart[1], '*. ' );
				$check_domain = substr( $domain, -strlen( $cert_domain ) );
				$res = ($cert_domain == $check_domain);
			}

			// Check if there is an alternative name
			if(!$res && strpos($cert['extensions']['subjectAltName'],"DNS:$domain") !== false)
				$res = true;
		}

		if($show_msg) {
			echo '<div class="pp_ssl_validation">';
			if($res) {
				$currdate = new DateTime();
				$certdate2 = new DateTime();
				$certdate2->setTimestamp($cert['validTo_time_t']);
				$certdate = date("m/d/Y",$cert['validTo_time_t']);
				$datediff = date_diff($currdate,$certdate2);

				echo "<p><i class='fas fa-check' style='color:green;'></i>&nbsp;";
				echo "SSL Validation: SSL Certificate is Active! </p>";
				echo "<p>Expiration: " . $certdate . "</p>";

				if($datediff->days < 31) {
					echo "<p>Your certificate expires in ". $datediff->days . " days! </p>";
					echo "<a href='https://shareasale.com/r.cfm?b=518812&u=1712557&m=46483&urllink=&afftrack=\"pplite\"' class='button' target='_blank' /> Renew SSL Certificate </a>";
				} else if ($datediff->days >=31) {
					echo "<p>Your certificate expires in ". $datediff->days . " days! </p>";
				}

			}
			else {
				echo "<i class='fas fa-times' style='color:red;'></i>&nbsp;";
				echo "SSL Validation: SSL Certificate Is Not Active!";
				echo "<a href='https://shareasale.com/r.cfm?b=518812&u=1712557&m=46483&urllink=&afftrack=\"pplite\"' class='button' target='_blank' /> Buy SSL Certificate </a>";
			}
			echo '</div>';
		}

		return $res;
	}

}

// New Instance of class
PowerPack_Lite_SSL::instance();
