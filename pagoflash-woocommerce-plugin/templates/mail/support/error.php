<?php
/* @var $data array */

if (false === defined('ABSPATH'))
{
  header('Location: http://www.enebruskemlem.com.ve');
  exit;
}
?>
<section>
  <p>Ha ocurrido un error mientras se utilizaba la pasarela de pago para WooCommerce.</p>
  <p>Los datos que ha devuelto el servidor de PagoFlash son:</p>
  <pre>
    <?php var_dump($data); ?>
  </pre>

  <p>
    Mensaje generado en la fecha y hora <b><?= date('d/m/Y H:i:s', current_time('timestamp')); ?></b>
  </p>
</section>