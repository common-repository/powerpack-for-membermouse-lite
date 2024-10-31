<!-- Admin Page HTML -->
<?php
$ppfml_helpers = new PowerPack_Lite_Helpers();
$ppfml_ssl = new PowerPack_Lite_SSL();
?>

<h1 class="mmtitle">PowerPack for MemberMouse <span class="badge">LITE</span></h1>
<p>Select the functionality you'd like below!</p>

<div class="wrap">
  <div class="leftMain">
    <!-- Tab links -->
    <div class="tab">
        <button class="tablinks active" data-menu="main">Main</button>
        <button class="tablinks" data-menu="ecom" id="ecommenu">eCommerce Tracking</button>
        <button class="tablinks" data-menu="logout" id="logoutmenu">Log Out Options</button>
        <button class="tablinks" data-menu="easyssl" id="easysslmenu">EasySSL</button>
    </div>

    <form method="post" action="options.php">

    <?php settings_fields( 'powerpack-settings' ); ?>
    <?php do_settings_sections( 'powerpack-settings' ); ?>
    <?php $options = get_option( 'powerpack-plugin-options' ); ?>


      <!-- Tab content -->
      <div id="main" class="tabcontent">
        <h3>Choose what functionality you want to turn on:</h3>
        <p><?php $ppfml_helpers->ppfml_output_setting_checkbox( 'mmboxdisplay', 'ecom-function', 1, 'ecommenu', 'Google Enhanced eCommerce tracking', $options ) ?></p>
        <p><?php $ppfml_helpers->ppfml_output_setting_checkbox( 'mmboxdisplay', 'logout-function', 1, 'logoutmenu', 'Log Out Options', $options ) ?></p>
        <p><?php $ppfml_helpers->ppfml_output_setting_checkbox( 'mmboxdisplay', 'easyssl-function', 1, 'easysslmenu', 'EasySSL Settings', $options ) ?></p>

        <p><input type="checkbox" name="pp-infusionsoft" value="1" disabled/> Infusionsoft Integration <span class="probadge">PRO</span></p>
        <p><input type="checkbox" name="pp-activecampaign" value="1" disabled/> ActiveCampaign Deeper Integration <span class="probadge">PRO</span></p>
        <p><input type="checkbox" name="pp-metrics" value="1" disabled/> Improved Metrics Forecasting and Engagement with Baremetrics or ProfitWell <span class="probadge">PRO</span></p>
        <p><input type="checkbox" name="pp-misc" value="1" disabled/> Miscellaneous Options <span class="probadge">PRO</span></p>
      </div>

      <!-- Ecommerce Tab -->
      <div id="ecom" class="tabcontent">
        <h3>Google Enhanced eCommerce for MemberMouse</h3>
        <p>Enhanced Ecommerce for MemberMouse will allow you to get detailed statistics for your MemerMouse
            transactions. Track your MemberMouse transactions in Google Analytics with support for initial
            purchases, rebills and refunds. This plugin can also add Google Analytics tracking code to your
            website to reduce the number of plugins on your site.</p>
        <p><strong>VIDEO: </strong><a href="https://youtu.be/bHW01HRiy5k" target="_blank">How to set up Google Analytics</a></p>

        <div class="graybox">
          <div class="row">
            <div class="col1"><label> Google Analytics ID </label>
              <div class="help-tip"><p>Enter your UA code here</p></div>
            </div>
            <div class="col2">
              <input type="text" name="powerpack-plugin-options[google-id]" size="50"  placeholder="UA-XXXXX-X" value="<?php echo $options['google-id']; ?>"/>
            </div>
          </div>
          <div class="clear"></div>

          <div class="row" class="margintop35">
            <div class="col1"><label> Tracking Code</label>
              <div class="help-tip"><p>Choose your tracking options</p></div>
            </div>
            <div class="col2">
              <div class="row" style="margin-top:0;">
                <?php $ppfml_helpers->ppfml_output_setting_checkbox( 'mmboxdisplay', 'google-add-analytics', 1, '', 'Add Universal Analytics Tracking Code (Optional)', $options ) ?>
              </div>
              <div class="row">
                <?php $ppfml_helpers->ppfml_output_setting_checkbox( 'mmboxdisplay', 'google-add-ecommerce', 1, '', 'Add Enhanced Ecommerce Tracking Code', $options ) ?>
              </div>
              <div class="row">
                <?php $ppfml_helpers->ppfml_output_setting_checkbox( 'mmboxdisplay', 'google-track-rebills', 1, '', 'Track Rebills', $options ) ?>
              </div>
              <div class="row">
                <?php $ppfml_helpers->ppfml_output_setting_checkbox( 'mmboxdisplay', 'google-track-refunds', 1, '', 'Track Refunds', $options ) ?>
              </div>
            </div>
          </div>
          <div class="clear"></div>

          <div class="row" class="margintop35">
            <div class="col1"><label> Exclude Tracking For: </label>
              <div class="help-tip"><p>Choose the users you wish to exclude from tracking</p></div>
            </div>
            <!-- To do: Add ability for custom roles based on website and not hardcoded roles -->
            <div class="col2">
              <ul>
                <li><?php $ppfml_helpers->ppfml_output_setting_checkbox( '', 'google-exclude-admin', 1, '', 'Administrator', $options ) ?></li>
                <li><?php $ppfml_helpers->ppfml_output_setting_checkbox( '', 'google-exclude-editor', 1, '', 'Editor', $options ) ?></li>
                <li><?php $ppfml_helpers->ppfml_output_setting_checkbox( '', 'google-exclude-author', 1, '', 'Author', $options ) ?></li>
                <li><?php $ppfml_helpers->ppfml_output_setting_checkbox( '', 'google-exclude-contributor', 1, '', 'Contributor', $options ) ?></li>
                <li><?php $ppfml_helpers->ppfml_output_setting_checkbox( '', 'google-exclude-subscriber', 1, '', 'Subscriber', $options ) ?></li>
              </ul>
            </div>
          </div>
          <div class="clear"></div>
        </div>
        <div class="clear"></div>

        <div class="sidebar"></div>
      </div><!-- End of Ecom -->

      <!-- Logout Tab -->
      <div id="logout" class="tabcontent">
        <h3>Customize Your Member's Log Out Experience</h3>
        <p>Why change it? MemberMouse's default logout behavior is to navigate your member to a "log out page". Most Membership Sites want to automatically log someone out and redirect them to a specific page. Now you can too!</p>

        <strong> When a member logs out: </strong>
        <p><?php $ppfml_helpers->ppfml_output_setting_radio( 'logout-redirect', 'MM', 'Default MemberMouse Logout', $options ) ?></p>
        <p><?php $ppfml_helpers->ppfml_output_setting_radio( 'logout-redirect', 'home', 'Redirect to the Home Page', $options ) ?></p>
        <p><input type="radio" class="mmboxdisplay" data-div="levelsbox" name="powerpack-plugin-options[logout-redirect]" value="custom" <?php isset( $options['logout-redirect'] ) ? checked( $options['logout-redirect'], "custom" ) : ''; ?> > Redirect to a Custom Page Based on Membership Level </p>

        <div id="levelsbox" class="graybox">
          <?php
          // Get list of memberlevels
          $memberlevels = MM_MembershipLevel::getMembershipLevelsList();

          // Get list of pages from site
          $pages = get_pages();

          foreach ( $memberlevels as $memberlevel ) {
            $pagelist = '';
            foreach ( $pages as $page ) {
              $pagelist .= "<option value='" . $page->ID . "' " . selected( $options[ 'mm-' . $memberlevel ], $page->ID, false ) . ">" . ( 0 != $page->post_parent ? "--- " : "" ) . $page->post_title . "</option>";
            }
            ?>

            <p><?php echo "<label>$memberlevel</label>"; ?> <select class="js-select2" name="powerpack-plugin-options[mm-<?php echo $memberlevel; ?>]"><?php echo $memberlevel . $pagelist; ?></select></p>
          <?php } ?> <!-- End of foreach loop -->

        </div>



      </div>  <!-- End of logout tab -->

      <!-- SSL Tab -->
      <div id="easyssl" class="tabcontent">
        <h3> SSL Information </h3>
        <?php
        $currdomain = parse_url( get_home_url() );
        $checkssl   = $ppfml_ssl->ppfml_has_ssl( $currdomain['host'], true );
        ?>

        <h3>Setup Your SSL</h3>
        <p>Easily set up your website to run https content. IMPORTANT: Only turn on once your SSL cert has been configured on your domain or else you may lose access to your site. You can test this by navigating to your site with 'https' in front of your URL.</p>

        <p><?php $ppfml_helpers->ppfml_output_setting_checkbox( 'mmboxdisplay', 'easyssl-support', 1, 'httpbox', 'Turn on SSL Support (Be sure you have an active certificate)', $options ) ?></p>

        <div id="httpbox" class="graybox">
          <strong> Choose Your Desired HTTPS Setup: </strong>
          <p><?php $ppfml_helpers->ppfml_output_setting_radio( 'easyssl-httpsetup', 'all', 'Force SSL for all website pages (We also recommend changing your WordPress Address and Site Address to https when choosing this option. ', $options ) ?></p>
          <p><?php $ppfml_helpers->ppfml_output_setting_radio( 'easyssl-httpsetup', 'MM', 'Force SSL for MemberMouse pages only (Pages protected by MemberMouse, Login, My Account, Dashboard, Checkout)', $options ) ?></p>
        </div>
        <p><?php $ppfml_helpers->ppfml_output_setting_checkbox( '', 'easyssl-mixed', 1, '', 'Fix Mixed Content Warnings (changes all resources to //) ', $options ) ?></p>
      </div>  <!-- End of SSL Tab -->

      <?php submit_button(); ?>

    </form> <!-- End of Form -->
  </div>
  <div class="rightSidebar">
    <div class="theCta red">
      <h3>Get More Features with<br/>PowerPack PRO</h3>
      <p>PowerPack for MemberMouse PRO gives you more features and integrations that help you concentrate on building your business versus working in it!</p>
      <a href="https://www.powerpackmm.com/" target="_blank" class="redbtn">Check It Out</a>
    </div>
    <div class="theCta purple">
      <h3>Need MemberMouse Development Help?</h3>
      <p>The development team behind the PowerPack for MemberMouse plugins is here to help you get started or take your membership site to the next level! We offer development services and customization services.</p>
      <a href="https://www.powerpackmm.com/support/" target="_blank">Yes! Help Me!</a>
    </div>
  </div>
</div> <!-- End of Wrap -->