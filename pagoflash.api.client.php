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
      
      if(!array_key_exists('pr_id', $product_item)){$product_item['pr_id'] = null;}

        $PagoFlashTokenBuilder->addProduct(
                    $product_item['pr_name'],
                    $product_item['pr_desc'],
                    $product_item['pr_price'],
                    $product_item['pr_qty'],
                    $product_item['pr_img'],
                    $product_item['pr_id']
                );
    }
    $response=$PagoFlashTokenBuilder->send(apiPagoflash::$GLOBAL_PARAMETERS[$this->_env]["domain"]);
    return $response;
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
    $PagoFlashVerifyToken = new PagoFlashVerifyToken($this->_key_token, $this->_key_secret);
    $PagoFlashVerifyToken->setTransactionToken($token_de_transaction);
    $response = $PagoFlashVerifyToken->send(self::$GLOBAL_PARAMETERS[$this->_env]["domain"]);

    return $response;
  }
}
/*End of class*/

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
    public function addProduct($pr_name, $pr_desc, $pr_price, $pr_qty, $pr_img, $pr_id){
        $this->products[]=array(
                    'pr_name'    => $pr_name,        // Nombre.  127 char max.
                    'pr_desc'    => $pr_desc, // Descripción .  Maximo 230 caracteres.
                    'pr_price'   => $pr_price,                                         // Precio individual. Float, sin separadores de miles, utilizamos el punto (.) como separadores de Decimales. Máximo dos decimales
                    'pr_qty'     => $pr_qty,                                         // Cantidad, Entero sin separadores de miles  
                    'pr_img'     => $pr_img, // Dirección de imagen.  Debe ser una dirección (url) válida para la imagen.
                    'pr_id'      => $pr_id //Optional   
        );
    }
    
    
    public function send($domain){
        $request=new PagoFlashHTTPRequest();
        $amount_str=number_format($this->order_info["PC_AMOUNT"], 2, '.', '');
        $key_to_encript=$amount_str.$this->order_info["PC_ORDER_NUMBER"].$this->authParams["KEY_TOKEN"];
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
/*End of class*/


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
        //No se pudo inicializar la sesión
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

        curl_setopt($v_curl, CURLOPT_HTTPHEADER,$this->getHeadersToCurl());
        $response=curl_exec($v_curl);

        return $response;

    }
    
}
/*End of class*/


class PagoFlashVerifyToken{
    private $requiredParams=array();
    private $authParams=array();
    
    function __construct($key_token, $key_secret)
    {
        $this->authParams["KEY_TOKEN"]=$key_token;
        $this->authParams["KEY_SECRET"]=$key_secret;
    }
    
    public function setTransactionToken($transaction_token)
    {
        $this->requiredParams['SELL_TOKEN']=$transaction_token;
    }    
        
    public function send($domain)
    {
        $request = new PagoFlashHTTPRequest();
        $key_to_encript = $this->requiredParams['SELL_TOKEN'].$this->authParams['KEY_TOKEN'];
        $encripted_key = hash_hmac("sha256",$key_to_encript,$this->authParams["KEY_SECRET"]);

        $postData = $this->requiredParams;

        $request->setData($postData);
        $request->addHeader("X-Signature", $encripted_key);
        $request->addHeader("X-Auth-Token", $this->authParams["KEY_TOKEN"]);
        $url = $domain.'/payment/validate-payment';

        return $request->send($url);
    }
}
/*End of class*/

?>