<?php
if (false === defined('ABSPATH'))
{
  header('Location: http://www.enebruskemlem.com.ve');
  exit;
}

global $pagoflash_woocommerce;
/* @var $pagoflash_woocommerce \pagoflash\woocommerce\inc\Plugin */

wp_enqueue_style('font-awesome',
  "{$pagoflash_woocommerce->base_url}/lib/font-awesome/css/font-awesome.min.css");
?>
<hr/>
<a href="https://app.pagoflash.com/backuser.php/user/new.html?tipo=empresa" class="button button-hero button-primary" style="vertical-align:top" target="_blank"><i class="fa fa-plus"></i> <?=
__('Create Account', 'pagoflash');
?></a>&nbsp;&nbsp;<img src="<?= $pagoflash_woocommerce->base_url; ?>/img/pagoflash-full.png" width="187" height="46" style="border-radius:3px; border:1px solid #D7D7D7" />
<hr/>