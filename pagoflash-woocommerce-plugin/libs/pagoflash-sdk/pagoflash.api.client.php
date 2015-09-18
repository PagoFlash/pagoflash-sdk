<?php

/**
 * Description of pagoflash
 *
 * @author Gregorio Escalona gregescalona
 */
class apiPagoflash
{

  const ENTORNO_PRUEBA = 0;
  const ENTORNO_PRODUCCION = 1;
  const ERROR_CURL_INIT = 0x0020;
  const ERROR_OPC_VERBOSE = 0x0021;
  const ERROR_OPC_USERAGENT = 0x0022;
  const ERROR_OPC_SSL = 0x0023;
  const ERROR_OPC_TIMEOUT = 0x0024;
  const ERROR_OPC_URL = 0x0025;
  const ERROR_OPC_RETURN = 0X0026;
  const ERROR_OPC_POST = 0x0027;

  private $_key_token;
  private $_key_secret;
  private $_modo_prueba = FALSE;
  private $_dominio_base = '';
  private $_url_punto_venta;
  private $_credenciales_pf;
  private $_codigo_error;

  /**
   * Crea una nueva instancia de la clase
   * 
   * @param string $p_key_token Cadena que representa la ficha de autenticación
   * recibida al momento de contratar el servicio de PagoFlash
   * @param string $p_key_secret Cadena que representa la clave de autenticación
   * recibida al momento de contratar el servicio de PagoFlash
   * @param string $p_url_punto_venta URL del punto de venta virtual desde el cual
   * se está realizando la llamada al servicio central de PagoFlash
   * @param boolean $p_modo_prueba Bandera que indica si las operaciones que
   * se realicen serán tratadas como pruebas de la aplicación
   */
  function __construct($p_key_token, $p_key_secret, $p_url_punto_venta, $p_modo_prueba = FALSE)
  {
    $this->_codigo_error = 0;
    $this->_key_token = $p_key_token;
    $this->_key_secret = $p_key_secret;
    $this->_url_punto_venta = $p_url_punto_venta;
    $v_entorno = '';

    // se está utilizando la versión de prueba
    if ($p_modo_prueba)
    {
      $this->_dominio_base = 'http://api-test.pagoflash.com';
      $v_entorno = self::ENTORNO_PRUEBA;
    }
    // no se está utilizando la versión de prueba
    else
    {
      $this->_dominio_base = 'https://api.pagoflash.com';
      $v_entorno = self::ENTORNO_PRODUCCION;
    }

    $v_entorno = self::ENTORNO_PRUEBA;
    // genera los parametros de autenticacion
    $this->_credenciales_pf = "AUTH_KEY_SECRET={$this->_key_secret}"
      . "&AUTH_KEY_TOKEN={$this->_key_token}"
      . "&AUTH_SITE_URL={$this->_url_punto_venta}"
      . "&AUTH_ENV={$v_entorno}";
  }

  /**
   * Indica si se está utilizando el modo de prueba de la API
   * 
   * @return boolean
   */
  public function esModoPrueba()
  {
    return $this->_modo_prueba;
  }

  /**
   * Devuelve el modo actual en el cual opera la API
   * 
   * return int
   */
  public function getModo()
  {
    return ($this->_modo_prueba) ? self::ENTORNO_PRUEBA : self::ENTORNO_PRODUCCION;
  }

  /**
   * Valida los parámetros requeridos por el API
   * 
   * @param array $p_requeridos Arreglo con el nombre de los campos que son
   * obligatorios
   * @param array $p_campos Arreglo con el nombre de los campos que serán
   * evaluados
   * 
   * @throws Exception
   */
  protected function validarParametros($p_requeridos, $p_campos)
  {
    // recorre todos los parametros que se indicaron ocmo obligatorios
    foreach ($p_requeridos as $v_requerido)
    {
      // no se estableció un valor para el parámetro
      if (false == isset($p_campos[$v_requerido]))
      {
        throw new Exception("El campo {$v_requerido} es obligatorio");
      }
    }
  }

  /**
   * 
   * @param array $p_datos Datos a utilizar para procesar el pago a través
   * de la plataforma de PagoFlash
   *    - cabecera_de_compra (array(key=>value)):
   *    - productos_items (array(key=>value)):
   * @param string $p_navegador Cadena que identifica el navegador web desde el
   * cual el cliente está conectado
   * 
   * @return mixed
   */
  public function procesarPago($p_datos, $p_navegador)
  {
    $v_productos_enviar = $v_parametros_compra = '';

    // obtiene los parametros indicados como cabecera para la compra
    $v_cabecera_compra = isset($p_datos['cabecera_de_compra']) ? $p_datos['cabecera_de_compra'] : array();

    // recorre los elementos indicados como cabecera
    foreach ($v_cabecera_compra as $v_entrada => $v_valor)
    {
      // se indico un valor para el elemento
      if ($v_valor != '')
      {
        $v_parametros_compra .= '&' . strtoupper($v_entrada) . '=' . urlencode($v_valor);
      }
    }

    // obtiene los valores dados para los productos
    $v_datos_productos = isset($p_datos['productos_items']) ? $p_datos['productos_items'] : array();

    // inicializa el contador de productos
    $n = 0;

    // recorre los datos establecidos para cada producto
    foreach ($v_datos_productos as $v_producto)
    {
      // recorre cada dato del producto para agregarlo a la trama a enviar
      foreach ($v_producto as $v_entrada => $v_valor)
      {
        // se indicó un valor para el elemento
        if ($v_valor != '')
        {
          $v_productos_enviar .= '&' . strtoupper($v_entrada) . "_" . $n . '=' . urlencode($v_valor);
        }
      }

      // incrementa el contador de productos
      $n++;
    }

    // genera la cadena de parámetros a enviar a la aplicación central de PagoFlash
    $v_datos_post = $this->_credenciales_pf . $v_parametros_compra . $v_productos_enviar;

    //print $this->_dominio_base."?".$v_datos_post; exit();
    // solicita la validación de la aplicación central de PagoFlash
    $v_respuesta = $this->generarSolicitudPOSTPagoFlash(
      $v_datos_post, $p_navegador
    );

    //print $this->_dominio_base."?".$v_datos_post." ";    exit();
    return $v_respuesta;
  }

  /**
   * Genera la URL que será utilizada para comunicarse con la aplicación central
   * de PagoFlash para efectuar la operación
   * 
   * @param array $p_parametros Parámetros adicionales a ser utilizados para
   * la validación de la operación con la aplicación central de PagoFlash
   * 
   * @return string
   * 
   * @throws Exception
   */
  public function generarURLPago($p_parametros)
  {
    // valida los parametros pasados a la función
    $this->validarParametros(
      array(
      'SITE_URL',
      'AMOUNT',
      'ITEM_DESC',
      'ITEM_QTY',
      'ITEM_IMG'
      ), $p_parametros
    );

    // establece la llave secreta de validación
    $p_parametros["KEY_SECRET"] = $this->_key_secret;

    // establece la ficha de validación de la llave
    $p_parametros["KEY_TOKEN"] = $this->_key_token;

    // establece el ambiente de trabajo a utilizar
    $p_parametros["ENV"] = $this->getModo();

    // genera la URL a utilizar para la validación
    return "{$this->_dominio_base}?" . http_build_query($p_parametros);
  }

  /**
   * Devuelve el último código de error registrado durante el proceso
   * @return int
   */
  public function getError()
  {
    return $this->_codigo_error;
  }

  /**
   * Genera una solicitud POST a la plataforma de PagoFlash
   * 
   * @param string $p_datos Datos a ser enviados a través de la trama HTTP. La
   * cadena debe estar en el formato "atributo_1=valor_1&atributo_n=valor_n"
   * @param string $p_navegador Cadena que identifica el navegador utilizado
   * por el cliente
   * 
   * @return Un objeto mixto si la llamada fue exitosa o FALSE en caso contrario
   */
  private function generarSolicitudPOSTPagoFlash($p_datos, $p_navegador)
  {
    $this->_codigo_error = 0;

    // crea una nueva sesion para utilizar objeto CURL
    $v_curl = curl_init();

    // no se pudo inicializar la sesión
    if (false == $v_curl)
    {
      $this->_codigo_error = self::ERROR_CURL_INIT;
      return false;
    }

    // establece los parametors necesarios para la conexión
    if (false == curl_setopt($v_curl, CURLOPT_VERBOSE, 1))
    {
      $this->_codigo_error = self::ERROR_OPC_VERBOSE;
    }
    if (false == curl_setopt($v_curl, CURLOPT_USERAGENT, $p_navegador))
    {
      $this->_codigo_error = self::ERROR_OPC_USERAGENT;
    }
    if (false == curl_setopt($v_curl, CURLOPT_SSL_VERIFYPEER, FALSE))
    {
      $this->_codigo_error = self::ERROR_OPC_SSL;
    }
    if (false == curl_setopt($v_curl, CURLOPT_TIMEOUT, 30))
    {
      $this->_codigo_error = self::ERROR_OPC_TIMEOUT;
    }
    if (false == curl_setopt($v_curl, CURLOPT_URL, $this->_dominio_base))
    {
      $this->_codigo_error = self::ERROR_OPC_URL;
    }
    if (false == curl_setopt($v_curl, CURLOPT_RETURNTRANSFER, 1))
    {
      $this->_codigo_error = self::ERROR_OPC_RETURN;
    }
    if (false == curl_setopt($v_curl, CURLOPT_POSTFIELDS, $p_datos))
    {
      $this->_codigo_error = self::ERROR_OPC_POST;
    }

    // envia la trama HTTP a la URL indicada
    $v_respuesta = curl_exec($v_curl);

    // cierra la sesión de uso del objeto CURL
    curl_close($v_curl);

    return $v_respuesta;
  }

  /**
   * 
   * @param string $token_de_transaction Dato a utilizar para verificar el pago exitoso o no a través
   * de la plataforma de PagoFlash
   * 
   * @return boolean
   */
  public function validarTokenDeTransaccion($token_de_transaction, $p_navegador)
  {

    // genera la cadena de parámetros a enviar a la aplicación central de PagoFlash
    $this->_dominio_base = $this->_dominio_base . "/transaction/valid-transaction.html";
    $v_datos_post = $this->_credenciales_pf . "&V_TK=" . $token_de_transaction;

    //print $this->_dominio_base." ".$v_datos_post;
    // solicita la validación de la aplicación central de PagoFlash
    $v_respuesta = $this->generarSolicitudPOSTPagoFlash(
      $v_datos_post, $p_navegador
    );

    return $v_respuesta;
  }

}
