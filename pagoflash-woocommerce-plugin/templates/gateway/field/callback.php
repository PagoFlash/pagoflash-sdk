<?php
/* @var $options array */

if (false === defined('ABSPATH'))
{
  header('Location: http://www.enebruskemlem.com.ve');
  exit;
}

wp_enqueue_script('pagoflash-woocommerce-field-callback');
?>
<!-- callback URL -->
<tr>
  <th class="titledesc" scope="row"><?= $options['title']; ?></th>
  <td>
    <fieldset>
      <input id="callback-url" type="text" value="<?= $options['url']; ?>" readonly="readonly" class="regular-input" />
      <button id="callback-url-select-button" type="button" class="button">
        <?= __('Select Text', 'pagoflash'); ?>
      </button>
    </fieldset>
  </td>
</tr>
<!--/ callback URL -->