<?php
/* @var $response stdClass */
/* @var $message string */

if (false === defined('ABSPATH'))
{
  header('Location: http://www.enebruskemlem.com.ve');
  exit;
}

global $pagoflash_woocommerce;
/* @var $pagoflash_woocommerce \pagoflash\woocommerce\inc\Plugin */

global $woocommerce;
/* @var $woocommerce WooCommerce */
?>

<?php get_header(); ?>

<section>
  <img style="border-radius:3px; border:1px solid #D7D7D7" src="<?= $pagoflash_woocommerce->base_url; ?>/img/pagoflash-full.png" />
  <br/>

  <h2><?= __('Your payment could not be completed', 'pagoflash'); ?></h2>

  <p><?= $message; ?></p>

  <p>
    <a class="button button-primary" href="<?= $woocommerce->cart->get_cart_url(); ?>">
      <?= __('Back to Cart', 'pagoflash'); ?>
    </a>
  </p>
</section>

<?php get_footer(); ?>