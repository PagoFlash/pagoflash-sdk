<?php

if (false === defined('ABSPATH'))
{
  header('Location: http://www.enebruskemlem.com.ve');
  exit;
}

/**
 * Pasarela de pago de PagoFlash International
 *
 * @author Enebrus Kem Lem, C.A. <contacto@enebruskemlem.com.ve>
 */
class WC_Gateway_Pagoflash extends WC_Payment_Gateway
{

  /**
   * @var string URL que se utilizará para recibir la respuesta dada por PagoFlash
   */
  protected $callback_url = null;

  /**
   * @var boolean Indica si se está utilizando la plataforma de prueba de PagoFlash
   */
  protected $is_test_mode = null;

  /**
   * @var boolean Indica si el log está activo
   */
  protected $log_enabled = false;

  /**
   * @var WC_Logger Objeto que es permite escribir en el log
   */
  protected $log = null;

  public function __construct()
  {
    global $pagoflash_woocommerce;
    /* @var $pagoflash_woocommerce \pagoflash\woocommerce\inc\Plugin */

    // identificador único del método de pago
    $this->id = 'pagoflash';

    // ícono que se mostrara junto al método de pago
    $this->icon = "{$pagoflash_woocommerce->base_url}/img/pagoflash-icon.png";

    // si el metodo de pago es una integración directa deberá incluir campos en el formulario, este no lo es
    $this->has_fields = false;

    // título del método de pago
    $this->method_title = __('PagoFlash', 'pagoflash');

    // texto del botón de pago
    $this->order_button_text = __('Proceed to PagoFlash');

    // URL que atiende la respuesta de PagoFlash
    $this->callback_url = site_url() . '/pagoflash-callback';

    // descripción del método de pago
    $this->method_description = __('Allow your customers to pay using PagoFlash International',
      'pagoflash');
    $this->method_description .= $pagoflash_woocommerce->template_manager->loadTemplate('gateway/part/signin',
      [], true);

    $this->init_form_fields();

    $this->init_settings();

    // obtiene la configuración del plugin
    $this->title = $this->get_option('title');
    $this->description = $this->get_option('description');
    $this->is_test_mode = ('yes' === $this->get_option('testMode'));
    $this->log_enabled = ('yes' === $this->get_option('enableLog'));

    add_action("woocommerce_update_options_payment_gateways_{$this->id}",
      [$this, 'process_admin_options']);
  }

  /**
   * @inheritdoc
   *
   * @see WC_Payment_Gateway
   */
  public function init_form_fields()
  {
    $this->form_fields = [
      'enabled' => [
        'title' => __('Enabled', 'pagoflash'),
        'type' => 'checkbox',
        'label' => __('Enable PagoFlash Payment', 'pagoflash'),
        'default' => 'yes'
      ],
      'title' => [
        'title' => __('Title', 'pagoflash'),
        'type' => 'text',
        'description' => __('This controls the title which the user sees during checkout.',
          'pagoflash'),
        'default' => __('Pagoflash', 'pagoflash'),
        'desc_tip' => true,
        'custom_attributes' => [
          'required' => 'required'
        ]
      ],
      'description' => [
        'title' => __('Description', 'pagoflash'),
        'type' => 'textarea',
        'description' => __('Payment method description that the customer will see on your checkout.',
          'pagoflash'),
        'default' => '',
        'desc_tip' => true,
        'custom_attributes' => [
          'required' => 'required'
        ]
      ],
      'successMessage' => [
        'title' => __('Message on Successful Finish'),
        'type' => 'textarea',
        'default' => __('Your payment has been accepted', 'pagoflash'),
        'description' => __('Message to display when the payment process has been completed successfully',
          'pagoflash'),
        'desc_tip' => true,
      ],
      'errorMessage' => [
        'title' => __('Message on Payment Error'),
        'type' => 'textarea',
        'default' => __('Your payment has been rejected', 'pagoflash'),
        'description' => __('Message to display when the payment process has some error',
          'pagoflash'),
        'desc_tip' => true,
      ],
      'separator1' => ['type' => 'separator'],
      'keyToken' => [
        'title' => __('Key Token', 'pagoflash'),
        'type' => 'text',
        'custom_attributes' => [
          'required' => 'required'
        ]
      ],
      'keySecret' => [
        'title' => __('Key Secret', 'pagoflash'),
        'type' => 'text',
        'custom_attributes' => [
          'required' => 'required'
        ]
      ],
      'callback' => [
        'title' => __('Site URL Callback', 'pagoflash'),
        'type' => 'callback',
        'url' => $this->callback_url
      ],
      'testMode' => [
        'title' => __('Test Mode', 'pagoflash'),
        'type' => 'checkbox',
        'label' => __('Enable Test Mode', 'pagoflash'),
        'default' => 'yes'
      ],
      'separator2' => ['type' => 'separator'],
      'enableLog' => [
        'title' => __('Verbose Log'),
        'description' => __('Write on log the steps of the payment process. If this option is unchecked only errors are written to log',
          'pagoflash'),
        'desc_tip' => true,
        'type' => 'checkbox',
        'label' => __('Write process steps on log', 'pagoflash'),
        'default' => 'yes'
      ],
      'supportEmail' => [
        'title' => __('Email to notify errors', 'pagoflash'),
        'description' => __('Email address where error notifications will be sent', 'pagoflash'),
        'desc_tip' => true,
        'type' => 'text',
        'default' => get_bloginfo('admin_email')
      ],
      'notifyErrorsToPagoflash' => [
        'title' => __('Notify errors to PagoFlash', 'pagoflash'),
        'type' => 'checkbox',
        'label' => __('Send a copy of the error emails to PagoFlash support team (soporte@pagoflash.com)',
          'pagoflash'),
        'default' => 'yes',
        'description' => __('This allow to PagoFlash to be notified when an error occurs in order to help you to solve it efficiently',
          'pagoflash'),
        'desc_tip' => true,
      ]
    ];
  }

  /**
   * Crea el HTML que se utiliza para el campo que muestra la URL a donde PagoFlash enviará los
   * resultados
   * 
   * @param string $p_id identificador del campo
   * @param array $p_options Opciones que se configuraron para el campo
   */
  public function generate_callback_html($p_id, $p_options)
  {
    global $pagoflash_woocommerce;
    /* @var $pagoflash_woocommerce \pagoflash\woocommerce\inc\Plugin */

    return $pagoflash_woocommerce->template_manager->loadTemplate('gateway/field/callback',
        ['options' => $p_options], true);
  }

  /**
   * @inheritdoc
   *
   * @see WC_Payment_Gateway
   */
  public function process_payment($p_order_id)
  {
    global $pagoflash_woocommerce;
    /* @var $pagoflash_woocommerce \pagoflash\woocommerce\inc\Plugin */

    $v_order = wc_get_order($p_order_id);
    /* @var $v_order WC_Order */

    // instancia la clase que permite hacer uso del API de PagoFlash
    $v_pagoflash_api = $pagoflash_woocommerce->retrievePagoFlashAPI(
      $this->get_option('keyToken'), //
      $this->get_option('keySecret'), //
      $this->callback_url, //
      $this->is_test_mode);
    /* @var $v_pagoflash_api apiPagoflash */

    // crea la cabecera que contiene los datos generales de la compra
    $v_header = [
      'pc_order_number' => substr($v_order->order_key, 0, 45),
      'pc_amount' => $v_order->order_total
    ];

    // prepara los datos de los productos
    $v_products = [];
    $v_order_items = $v_order->get_items();

    foreach ($v_order_items as $v_order_item)
    {
      $v_product = new WC_Product($v_order_item['product_id']);
      /* @var $v_product WC_Product */

      $v_description = get_post($v_order_item['product_id'])->post_content;
      $v_image_id = $v_product->get_image_id();

      $v_products[] = [
        'pr_name' => substr($v_order_item['name'], 0, 127),
        'pr_desc' => ('' === $v_description) ? '' : substr($v_description, 0, 230),
        'pr_price' => $v_order_item['line_total'] / $v_order_item['qty'],
        'pr_qty' => $v_order_item['qty'],
        'pr_img' => (0 === $v_image_id) ? '' : wp_get_attachment_url($v_image_id)
      ];
    }

    // envía los datos hacia PagoFlash y espera la respuesta
    $v_raw_response = $v_pagoflash_api->procesarPago([
      'cabecera_de_compra' => $v_header,
      'productos_items' => $v_products
      ], $_SERVER['HTTP_USER_AGENT']
    );
    $v_response = json_decode($v_raw_response);

    // ocurrió un error al obtener la respuesta
    if (null === $v_response)
    {
      // genera el identificador del error
      $v_error_id = "pagoflash-{$v_order->order_key}" . time();

      // agrega el error en el log de errores del servidor
      $this->log("{$v_error_id}: {$v_raw_response}");

      // notifica el error via correo electrónico
      $pagoflash_woocommerce->sendErrorOnRequestEmail([
        'admin' => [
          'test-mode' => $this->is_test_mode
        ],
        'support' => [
          'error_log_id' => $v_error_id,
          'raw_response' => $v_raw_response,
          'test_mode' => $this->is_test_mode
        ]
      ]);

      // notifica acerca del error
      wc_add_notice(
        sprintf(__('Error procesing payment, please let us check and try again in 10 minutes')),
        'error');
      return;
    }

    // ocurrió un error al ejecutar la operación
    if (0 == $v_response->success)
    {
      // genera el identificador del error
      $v_error_id = "pagoflash-{$v_order->order_key}" . time();

      // agrega el error en el log de errores del servidor
      $this->log("{$v_error_id}: {$v_raw_response}");

      // notifica el error via correo electrónico
      $pagoflash_woocommerce->sendErrorOnRequestEmail([
        'admin' => [
          'test-mode' => $this->is_test_mode
        ],
        'support' => [
          'error_log_id' => $v_error_id,
          'raw_response' => $v_raw_response,
          'test_mode' => $this->is_test_mode
        ]
      ]);

      // notifica acerca del error
      wc_add_notice(
        sprintf(__('Error procesing payment, please let us check and try again in 10 minutes')),
        'error');
      return;
    }

    // la operación se completó exitosamente
    return [
      'result' => 'success',
      'redirect' => $v_response->url_to_buy
    ];
  }

  /**
   * Indica si se está utilizando la plataforma de prueba de PagoFlash
   * 
   * @return boolean
   */
  public function isTestMode()
  {
    return $this->is_test_mode;
  }

  /**
   * Devuelve la URL que atiende las respuestas dadas por el servidor de PagoFlash
   * 
   * @return string
   */
  public function retrieveCallbackUrl()
  {
    return $this->callback_url;
  }

  /**
   * Indica si se deben notificar los errores al equipo de soporte de PagoFlash
   * 
   * @return boolean
   */
  public function mustNotifyErrorsToPagoflash()
  {
    return 'yes' === $this->get_option('notifyErrorsToPagoflash');
  }

  /**
   * Crea el HTML que se utiliza para el separador de las filas
   * 
   * @param string $p_id identificador del campo
   * @param array $p_options Opciones que se configuraron para el campo
   */
  public function generate_separator_html($p_id, $p_options)
  {
    global $pagoflash_woocommerce;
    /* @var $pagoflash_woocommerce \pagoflash\woocommerce\inc\Plugin */

    return $pagoflash_woocommerce->template_manager->loadTemplate('gateway/field/separator',
        ['options' => $p_options], true);
  }

  /**
   * Escribe en el log de PagoFlash para WooCommerce
   * 
   * @param string $p_message Mensaje que se escribirá en el log
   */
  public function log($p_message)
  {
    // no está activo el log
    if (false === $this->log_enabled)
    {
      return;
    }

    // no existe una instancia del log
    if (null === $this->log)
    {
      $this->log = new WC_Logger;
    }

    $this->log->add('pagoflash', $p_message);
  }

}

/**
 * Registra el método de pago
 * 
 * @param array $p_methods Métodos de pago ya registrados
 * 
 * @return array
 */
function onFilterWoocommercePaymentGateways($p_methods)
{
  // agrega el método de pago
  $p_methods[] = 'WC_Gateway_Pagoflash';

  return $p_methods;
}

add_filter('woocommerce_payment_gateways', 'onFilterWoocommercePaymentGateways');
