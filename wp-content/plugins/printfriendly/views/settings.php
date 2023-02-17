      <div id="pf_settings" class="wrap">
        
        <h2><img src="<?php echo PRINTFRIENDLY_BASEURL . '/assets/images/pf-icon.png'; ?>"><?php _e('Print Friendly & PDF Settings', 'printfriendly'); ?></h2>

        <form id="pf-options-form" action="options.php" method="post">
          <?php wp_nonce_field('pf-options', 'pf-nonce'); ?>
          <?php settings_fields($this->option_name); ?>

          <?php include_once PRINTFRIENDLY_BASEPATH . '/views/tabs.php'; ?>

          <input type="hidden" name="tab" id="current-tab" value="<?php echo get_transient('pf-tab'); ?>">

        <footer id="after-submit" class="pf-bu-footer">
          <div class="pf-bu-content pf-bu-has-text-centered">
            <p>
              <?php echo sprintf(__('If you like <strong>PrintFriendly</strong> please leave us a %s rating. A huge thanks in advance!', 'printfriendly'), '<a href="https://wordpress.org/support/plugin/printfriendly/reviews?rate=5#new-post" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'); ?>
            </p>
            <p><?php _e('Need professional options for your corporate, education, or agency developed website? Check out', 'printfriendly'); ?> <a href="https://www.printfriendly.com/button/pro?utm_source=wp&utm_medium=link&utm_campaign=wp-link" target="_blank">PrintFriendly Pro</a>.</p>
            <p>
              <?php _e('Need help or have suggestions?', 'printfriendly'); ?> <a href="mailto:support@printfriendly.com?subject=Support%20for%20PrintFriendly%20WordPress%20plugin">support@PrintFriendly.com</a>
            </p>
          </div>
        </footer>

        </form>
      </div>
