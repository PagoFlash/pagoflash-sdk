<?php

namespace pagoflash\woocommerce\inc;

if (false === defined('ABSPATH'))
{
  header('Location: http://www.enebruskemlem.com.ve');
  exit;
}

require dirname(__FILE__) . '/TemplateManager.php';

/**
 * Prepara el plugin para ser utilizado, centraliza las funciones de uso general del plugin y limpia
 * el estatus de Wordpress una vez que el plugin es desactivado
 *
 * @author Enebrus Kem Lem, C.A. <contacto@enebruskemlem.com.ve>
 */
class Plugin
{

  /**
   * @var string Directorio a partir del cual se puede acceder a los archivos del plugin
   */
  public $base_dir = null;

  /**
   * @var string URL a partir de la cual se cargarán los recursos del plugin
   */
  public $base_url = null;

  /**
   * @var TemplateManager Gestor de las plantillas del plugin. Se utiliza para
   * mostrar los archivos de plantilla dentro del directorio [[templates]]
   */
  public $template_manager = null;

  public function __construct()
  {
    // define el directorio base del plugin
    $this->base_dir = plugin_dir_path(__FILE__);

    // define el directorio base de recursos del plugin
    $this->base_url = WP_PLUGIN_URL . '/pagoflash-woocommerce/web';

    // instancia el gestor de las plantillas
    $this->template_manager = new TemplateManager;
  }

  /**
   * @see https://codex.wordpress.org/Plugin_API/Action_Reference/init
   */
  public function onActionInit()
  {
    // carga los hooks
    $this->loadHooks();

    // agrega las rutas personalizadas
    $this->addRewriteRules();
  }

  /**
   * @see https://codex.wordpress.org/Plugin_API/Action_Reference/shutdown
   */
  public function onActionShutdown()
  {
    
  }

  /**
   * @see https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
   */
  public function onActionWpEnqueueScripts()
  {
    
  }

  /**
   * @see https://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
   */
  public function onActionAdminEnqueueSripts()
  {
    // obtiene los datos de la página actual
    $v_current_page = get_current_screen();
    /* @var $v_current_page WP_Screen */

    // se está en el area de configuración del plugin en WooCommerce
    if (strpos($v_current_page->id, 'woocommerce_page_wc-settings') !== false)
    {
      wp_register_script('jquery', "{$this->base_url}/lib/jquery/jquery-1.11.3.min.js", [], false,
        true);
      wp_register_script('pagoflash-woocommerce-field-callback',
        "{$this->base_url}/js/gateway/field/callback.js", ['jquery'], false, true);
    }
  }

  /**
   * @see https://codex.wordpress.org/Plugin_API/Action_Reference/plugins_loaded
   */
  public function onActionPluginsLoaded()
  {
    // registra las traducciones
    load_plugin_textdomain('pagoflash', false, 'pagoflash-woocommerce/languages');
  }

  /**
   * Lee y ejecuta la configuración de los interceptores de filtros y acciones
   */
  public function loadHooks()
  {
    // obtiene los archivos que definen los hooks
    $v_files = glob("{$this->base_dir}hooks/*.php", GLOB_NOSORT);

    // recorre los archivos para ser procesados por Wordpress
    foreach ($v_files as $v_file)
    {
      // incluye el archivo
      require $v_file;
    }
  }

  /**
   * Calcula el promedio en base a una promedio anterior y un nuevo promedio
   * 
   * @param double $p_prev_mean Promedio que se tiene hasta el momento
   * @param int $p_prev_mean_items Cantidad de elementos que se utilizaron para
   * calcular el promedio que se tiene hasta el momento
   * @param double $p_current_mean Promedio calculado en base a un conjunto de
   * nuevos elementos
   * @param int $p_current_mean_items Cantidad de elementos que se utilizaron
   * para calcular el nuevo promedio
   * 
   * @return double
   */
  public function calculateMean($p_prev_mean, $p_prev_mean_items, $p_current_mean,
    $p_current_mean_items)
  {
    $v_numerator = ($p_prev_mean_items * $p_prev_mean) + ($p_current_mean_items * $p_current_mean);
    $v_denominator = ($p_prev_mean_items + $p_current_mean_items) * ($p_prev_mean + $p_current_mean);

    return ($v_numerator / $v_denominator) * ($p_prev_mean + $p_current_mean);
  }

  /**
   * Devuelve una instancia de la clase que permite hacer uso del API de PagoFlash.
   * 
   * @param string $p_key_token Ficha de control que genera PagoFlash para el punto de venta virtual
   * @param string $p_key_secret Código de seguridad, privado, que genera PagoFlash para el punto de
   * venta virtual
   * @param string $p_url_callback URL hacia la cual se enviará al cliente luego de completar el
   * proceso de pago
   * @param boolean $p_test_mode Opcional. Indica si la ejecución debe realizarse en modo de prueba.
   * Por defecto si el valor de este parámetro se omite se considerará como que si está en modo de
   * prueba
   * 
   * @return \apiPagoflash
   */
  public function retrievePagoFlashAPI($p_key_token, $p_key_secret, $p_url_callback,
    $p_test_mode = true)
  {
    require_once WP_PLUGIN_DIR . '/pagoflash-woocommerce/libs/pagoflash-sdk/pagoflash.api.client.php';

    return new \apiPagoflash($p_key_token, $p_key_secret, urlencode($p_url_callback), $p_test_mode);
  }

  /**
   * Agrega las rutas adicionales que pueden ser utilizadas para acceder a las funcionalidades del
   * plugin
   */
  protected function addRewriteRules()
  {
    // agrega la ruta para atender la respuesta de PagoFlash
    add_rewrite_endpoint('pagoflash-callback', EP_NONE);
  }

  /**
   * Devuelve una instancia de la pasarela de pago de PagoFlash
   * 
   * @return \WC_Gateway_Pagoflash
   */
  public function retrievePagoflashGateway()
  {
    require_once "{$this->base_dir}/hooks/class-wc-gateway-pagoflash.php";

    return new \WC_Gateway_Pagoflash;
  }

  /**
   * @see https://codex.wordpress.org/Function_Reference/register_activation_hook
   */
  public static function onActivate()
  {
    // regenera las URLs para que se tomen en cuenta los puntos de entrada que agrega el plugin
    flush_rewrite_rules(false);
  }

  /**
   * @see https://codex.wordpress.org/Function_Reference/register_deactivation_hook
   */
  public function onDeactivate()
  {
    // regenera las URLs para que ya no se tomen en cuenta los puntos de entrada que agregó el plugin
    flush_rewrite_rules(false);
  }

  /**
   * Envía un mensaje de error al responsable del sitio web y al equipo de soporte de PagoFlash para
   * atender alguna eventualidad que ocurra con el plugin
   * 
   * @param array $p_data Opcional. Datos adicionales que se enviarán en el mensaje de correo
   * @param \WC_Gateway_Pagoflash $p_gateway_instance Opcional. Instancia de la pasarela de pago.
   * Este parámetro facilita la obtención de la configuración de la pasarela de pago. Si no se
   * indica se creará una nueva instancia de la misma
   */
  public function sendErrorOnRequestEmail($p_data = [], &$p_gateway_instance = null)
  {
    // no se indicaron datos para el administrador
    if (false === isset($p_data['admin']))
    {
      $p_data['admin'] = null;
    }

    // no se indicaron datos para el equipo de soporte de PagoFlash
    if (false === $p_data['support'])
    {
      $p_data['support'] = null;
    }

    // no se indicó una instancia de la pasarela de pago
    if (null === $p_gateway_instance)
    {
      // instancia la pasarela de pago
      $p_gateway_instance = $this->retrievePagoflashGateway();
    }

    // agrega los filtros necesarios para enviar el mensaje de correo adecuadamente
    add_filter('wp_mail_content_type', [$this, 'onFilterWpMailContentType']);

    // envía el mensaje de correo electrónico al administrador
    wp_mail(
      get_bloginfo('admin_email'), //
      sprintf(__('[PagoFlash] Your site %s is experiencing problems on payment process'), site_url()), //
      $this->template_manager->loadTemplate('mail/admin/error', ['data' => $p_data['admin']], true)
    );

    // envía el mensaje de correo electrónico al equipo de soporte de la tienda
    wp_mail(
      $p_gateway_instance->get_option('supportEmail'), //
      sprintf(__('[%s] Error with PagoFlash WooCommerce extension'), site_url()), //
      $this->template_manager->loadTemplate('mail/support/error', ['data' => $p_data['support']],
        true)
    );

    // se enviarán los mensajes de error a PagoFlash
    if ($p_gateway_instance->mustNotifyErrorsToPagoflash())
    {
      // envia el mensaje de correo electrónico al equipo de sopote de PagoFlash
      wp_mail(
        'soporte@pagoflash.com', //
        sprintf(__('[%s] Error with WooCommerce extension'), site_url()), //
        $this->template_manager->loadTemplate('mail/support/error', ['data' => $p_data['support']],
          true)
      );
    }

    // elimina los filtros para no afectar otros envíos
    remove_filter('wp_mail_content_type', [$this, 'onFilterWpMailContentType']);
  }

  /**
   * Devuelve el tipo de contenido que se utilizará para enviar los mensajes de correo electrónico
   * 
   * @return string
   */
  public function onFilterWpMailContentType()
  {
    return 'text/html';
  }

}

$v_plugin = new Plugin;

// guarda de forma global la referencia al plugin
global $pagoflash_woocommerce;
$pagoflash_woocommerce = $v_plugin;

// establece la función que se ejecutará al inicializar el CMS
add_action('plugins_loaded', [$v_plugin, 'onActionPluginsLoaded']);
add_action('init', [$v_plugin, 'onActionInit']);
add_action('shutdown', [$v_plugin, 'onActionShutdown']);
//add_action('wp_enqueue_scripts', [$v_plugin, 'onActionWpEnqueueScripts']);
add_action('admin_enqueue_scripts', [$v_plugin, 'onActionAdminEnqueueSripts']);
unset($v_plugin);
