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

// obtiene el ID de la página que muestra el resumen de la cuenta del usuario
$v_myaccount_page_id = get_option('woocommerce_myaccount_page_id');
?>

<?php get_header(); ?>

<section>
  <img style="border-radius:3px; border:1px solid #D7D7D7" src="<?= $pagoflash_woocommerce->base_url; ?>/img/pagoflash-full.png" />
  <br/>

  <h2><?= __('Your payment has been completed', 'pagoflash'); ?></h2>

  <p><?= $message; ?></p>

  <p>
    <?php if ($v_myaccount_page_id): ?>
      <a class="button button-primary" href="<?= get_permalink($v_myaccount_page_id); ?>">
        <?= __('Go To My Account', 'pagoflash'); ?>
      </a>
    <?php else: ?>
      <a class="button button-primary" href="<?= get_permalink(woocommerce_get_page_id('shop')) ?>">
        <?= __('Back To Shop', 'pagoflash'); ?>
      </a>
    <?php endif; ?>
  </p>
</section>

<?php get_footer(); ?>