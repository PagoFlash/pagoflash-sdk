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
  private  $_env='prod';
  
  static $GLOBAL_PARAMETERS=array(); 

  /**
   * Crea una nueva instancia de la clase
   * 
   * @param string $p_key_token Cadena que representa la ficha de autenticación
   * recibida al momento de contratar el servicio de PagoFlash
   * @param string $p_key_secret Cadena que representa la clave de autenticación
   * recibida al momento de contratar el servicio de PagoFlash
   * @param string $p_url_punto_venta [deprecated] URL del punto de venta virtual desde el cual
   * se está realizando la llamada al servicio central de PagoFlash
   * @param boolean $p_modo_prueba Bandera que indica si las operaciones que
   * se realicen serán tratadas como pruebas de la aplicación
   */
  function __construct($p_key_token, $p_key_secret, $p_url_punto_venta=null, $p_modo_prueba = FALSE)
  {
    self::$GLOBAL_PARAMETERS=require dirname(__FILE__)."/pagoflash_parameters.php";
    $this->_codigo_error = 0;
    $this->_key_token = $p_key_token;
    $this->_key_secret = $p_key_secret;
    $this->_url_punto_venta = $p_url_punto_venta;
    $v_entorno = '';
    
    
    if($p_modo_prueba){
        $this->_env="dev";
    }
    return $this;
    
    
    
    
    
    
    
    
    // se está utilizando la versión de prueba
    if($p_modo_prueba)
    {
      $this->_dominio_base = 'http://127.0.0.1:8000/payment/generate-token';
      //$this->_dominio_base = 'http://pagoflash/api.php';
      $v_entorno = self::ENTORNO_PRUEBA;
    }	
    // no se está utilizando la versión de prueba
    else
    {
      $this->_dominio_base = 'http://127.0.0.1:8000/payment/generate-token';  
      //$this->_dominio_base = 'http://pagoflash/api.php';
      $v_entorno = self::ENTORNO_PRODUCCION;
    }

    // genera los parametros de autenticacion
    $this->_credenciales_pf = 
	"AUTH_KEY_SECRET={$this->_key_secret}"
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
    return ($this->_modo_prueba)? self::ENTORNO_PRUEBA : self::ENTORNO_PRODUCCION;
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
    foreach($v_cabecera_compra as $k=>$val){
	$v_cabecera_compra[strtoupper($k)]=$val;
    }
    
    $PagoFlashTokenBuilder= new PagoFlashTokenBuilder($this->_key_token,$this->_key_secret);
    $PagoFlashTokenBuilder->setOrderInformation($p_datos['cabecera_de_compra']['pc_order_number'], $p_datos['cabecera_de_compra']['pc_amount'] );
    foreach($p_datos['productos_items'] as $product_item){
        $PagoFlashTokenBuilder->addProduct(
                    $product_item['pr_name'],
                    $product_item['pr_desc'],
                    $product_item['pr_price'],
                    $product_item['pr_qty'],
                    $product_item['pr_img']
                );
    }
    $response=$PagoFlashTokenBuilder->send(apiPagoflash::$GLOBAL_PARAMETERS[$this->_env]["domain"]);
    return $response;
    
    // recorre los elementos indicados como cabecera
    $v_parametros_compra= http_build_query($v_cabecera_compra);
    /*foreach ($v_cabecera_compra as $v_entrada => $v_valor)
    {
      // se indico un valor para el elemento
      if($v_valor != '')
      {
        $v_parametros_compra .= '&' . strtoupper($v_entrada) . '=' . urlencode($v_valor);
      }
    }*/

    // obtiene los valores dados para los productos
    $v_datos_productos["PRODUCTS"]=array();
    $v_datos_productos["PRODUCTS"] = isset($p_datos['productos_items']) ? $p_datos['productos_items'] : array();
    $v_datos_productos["PARAMS"]=array("url_ok_request"=>"HOLA.MUNDO");
    // inicializa el contador de productos
    $n = 0;
    
     $v_productos_enviar .= '&'.http_build_query($v_datos_productos);
//die($v_productos_enviar);
    // recorre los datos establecidos para cada producto
    /*foreach ($v_datos_productos as $v_producto)
    {
      // recorre cada dato del producto para agregarlo a la trama a enviar
      foreach ($v_producto as $v_entrada => $v_valor)
      {
        // se indicó un valor para el elemento
        if($v_valor != '')
        {
          $v_productos_enviar .= '&' . strtoupper($v_entrada) . "_" . $n . '=' . urlencode($v_valor);
        }
      }
      
      // incrementa el contador de productos
      $n++;
    }*/

    // genera la cadena de parámetros a enviar a la aplicación central de PagoFlash
    $key_to_encript=$v_cabecera_compra["PC_AMOUNT"].$v_cabecera_compra["PC_ORDER_NUMBER"].$this->_key_token;
    $encripted_key=hash_hmac("sha256",$key_to_encript,$this->_key_secret);
    $auth="AUTH_KEY_TOKEN={$this->_key_token}"
      . "&AUTH_SITE_URL={$this->_url_punto_venta}"
      . "&AUTH_ENV={$v_entorno}"
      . "&AUTH_SIGNATURE={$encripted_key}";
    $v_datos_post = $auth ."&". $v_parametros_compra . $v_productos_enviar;
    
	//print $this->_dominio_base."?".$v_datos_post;
    // solicita la validación de la aplicación central de PagoFlash
    $v_respuesta = $this->generarSolicitudPOSTPagoFlash(
      $v_datos_post,
      $p_navegador
    );

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
      ),
      $p_parametros
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




  private function sendHTTPPOSTREquest(array $data, $format='json',array $auth=array()){
      
  }
  
  private function generarSolicitudPOSTPagoFlash($p_datos, $p_navegador)
  {
    $this->_codigo_error = 0;
    
    // crea una nueva sesion para utilizar objeto CURL
    $v_curl = curl_init();
    
    // no se pudo inicializar la sesión
    if(false == $v_curl)
    {
      $this->_codigo_error = self::ERROR_CURL_INIT;
      return false;
    }
    
    // establece los parametors necesarios para la conexión
    if(false == curl_setopt($v_curl, CURLOPT_VERBOSE, 1)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_VERBOSE; }
    if(false == curl_setopt($v_curl, CURLOPT_USERAGENT, $p_navegador)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_USERAGENT; }
    if(false == curl_setopt($v_curl, CURLOPT_SSL_VERIFYPEER, FALSE)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_SSL; }
    if(false == curl_setopt($v_curl, CURLOPT_TIMEOUT, 30)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_TIMEOUT; }
    if(false == curl_setopt($v_curl, CURLOPT_URL, $this->_dominio_base)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_URL; }
    if(false == curl_setopt($v_curl, CURLOPT_RETURNTRANSFER, 1)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_RETURN; }
    if(false == curl_setopt($v_curl, CURLOPT_POSTFIELDS, $p_datos)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_POST; }

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
    $v_datos_post = $this->_credenciales_pf ."&V_TK=" . $token_de_transaction;
    
    // solicita la validación de la aplicación central de PagoFlash
    $v_respuesta = $this->generarSolicitudPOSTPagoFlash(
      $v_datos_post,
      $p_navegador
    );
      
    return $v_respuesta;
  }
}


class PagoFlashTokenBuilder{
    private $order_info=array();
    private $products=array();
    private $parameters=array();
    
    
    public function __construct($key_token, $key_secret){
        $this->authParams["KEY_TOKEN"]=$key_token;
        $this->authParams["KEY_SECRET"]=$key_secret;
    }
    
    public function setUrlOKRediect($url_ok_redirect){
        $this->parameters["url_ok_redirect"]=$url_redirect;
    }
    /**
     * URL a la que se hará la llamada HTTP una vez que el pago haya sido satisfactorio
     * Usar este parámetro para hacer validaciones del pago de forma segura
     * @param string $url_ok_request URL a la que se le hará un llamado una vez que el pago haya sido satisfactorio
     * 
     */
    public function setUrlOKRequest($url_ok_request){
        $this->parameters["url_ok_request"]=$url_redirect;
    }
    
    
    public function setOrderInformation($pc_order_number,$pc_amount){
        $this->order_info=array(
                "PC_ORDER_NUMBER"=>$pc_order_number,
                "PC_AMOUNT"=>$pc_amount
            );
    }
    public function addProduct($pr_name, $pr_desc, $pr_price, $pr_qty, $pr_img){
        $this->products[]=array(
                    'pr_name'    => $pr_name,        // Nombre.  127 char max.
                    'pr_desc'    => $pr_desc, // Descripción .  Maximo 230 caracteres.
                    'pr_price'   => $pr_price,                                         // Precio individual. Float, sin separadores de miles, utilizamos el punto (.) como separadores de Decimales. Máximo dos decimales
                    'pr_qty'     => $pr_qty,                                         // Cantidad, Entero sin separadores de miles  
                    'pr_img'     => $pr_img, // Dirección de imagen.  Debe ser una dirección (url) válida para la imagen.   
        );
    }
    
    
    
    public function send($domain){
        $request=new PagoFlashHTTPRequest();
        $key_to_encript=$this->order_info["PC_AMOUNT"].$this->order_info["PC_ORDER_NUMBER"].$this->authParams["KEY_TOKEN"];
        $encripted_key=hash_hmac("sha256",$key_to_encript,$this->authParams["KEY_SECRET"]);
        $dataToSend=$this->order_info;
        $dataToSend["PRODUCTS"]=$this->products;
        if(count($this->parameters)>0){
            $dataToSend["PARAMETERS"]=$this->parameters;
        }
        $request->setRequestMethod("POST");
        $request->setData($dataToSend);
        $request->addHeader("X-Signature",$encripted_key);
        $request->addHeader("X-Auth-Token", $this->authParams["KEY_TOKEN"] );
        $url=$domain.'/payment/generate-token';
        return $request->send($url);
    }
}

class PagoFlashHTTPRequest{
    private $headers=array();
    private $data=array();
    private $requestMethod='POST';
    private $requestFormat="JSON";
    
    public function addHeader($key, $val){
        $this->headers[$key]=$val;
    }
    public function setData(array $data){
        $this->data=$data;
    }
    public function setRequestMethod($method){
        $this->requestMethod=$method;
    }
    
    public function getHeadersToCurl(){
        $ret=array();
        foreach ($this->headers as $k=>$val){
            if(null==$val){
                $ret[]=$k;
            }else{
                $ret[]=sprintf("%s: %s",$k,$val);
            }
        }
        return $ret;
    }
    
    public function send($url){
        $dataToSend=$this->data;
        $data_string = json_encode($dataToSend);
        $this->_codigo_error = 0;
    
        $v_curl = curl_init();
        // no se pudo inicializar la sesión
        if(false == $v_curl)
        {
          $this->_codigo_error = apiPagoflash::ERROR_CURL_INIT;
          return false;
        }
        if(false == curl_setopt($v_curl, CURLOPT_VERBOSE, 1)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_VERBOSE; }
        if(false == curl_setopt($v_curl, CURLOPT_USERAGENT, 'pagoflash/SDK')){ $this->_codigo_error = apiPagoflash::ERROR_OPC_USERAGENT; }
        if(false == curl_setopt($v_curl, CURLOPT_SSL_VERIFYPEER, FALSE)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_SSL; }
        if(false == curl_setopt($v_curl, CURLOPT_TIMEOUT, 30)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_TIMEOUT; }
        if(false == curl_setopt($v_curl, CURLOPT_URL, $url)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_URL; }
        if(false == curl_setopt($v_curl, CURLOPT_RETURNTRANSFER, 1)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_RETURN; }
        
        if("JSON"==$this->requestFormat){
            if(false == curl_setopt($v_curl, CURLOPT_POSTFIELDS, $data_string)){ $this->_codigo_error = apiPagoflash::ERROR_OPC_POST; }
            $this->addHeader('Content-Type','application/json');
            $this->addHeader('Content-Length', strlen($data_string));
        }
        var_dump($this->getHeadersToCurl());
        curl_setopt($v_curl, CURLOPT_HTTPHEADER,$this->getHeadersToCurl());
        $response=curl_exec($v_curl);
        var_dump($response);
        return $response;

    }
    
}
?>
