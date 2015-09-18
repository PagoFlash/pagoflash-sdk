<?php
/* @var $data array */

if (false === defined('ABSPATH'))
{
  header('Location: http://www.enebruskemlem.com.ve');
  exit;
}
?>
<section>
  <h2><?= __('Your store is experiencing problems', 'pagoflash'); ?></h2>

  <p>
    <?=
    __('We have detected that your store is having dificults to complete the payments using PagoFlash',
      'pagoflash');
    ?>
  </p>

  <p>
    <?=
    __('PagoFlash support team has been advised about the situation, but we recommend you to take some of this actions:',
      'pagoflash');
    ?>
  </p>
  <ul>
    <li><?=
      sprintf(__('Check that the <a href="%s" target="_blank">configuration of the extension</a> match <a href="%s" target="_blank">your PagoFlash info</a>',
          'pagoflash'), //
        admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_gateway_pagoflash'), //
        (isset($data['test-mode']) && $data['test-mode']) ? 'http://app-test.pagoflash.com/backuser.php/salepoints' : 'http://app.pagoflash.com/backuser.php/salepoints'
      );
      ?></li>
  </ul>
</section>