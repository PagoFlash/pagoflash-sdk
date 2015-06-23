#Libreria Cliente PHP para el API de [PagoFlash.com](http://pagoflash.com)

Aquí encontrará la información necesaria para integrar y utilizar el API de [PagoFlash](http://pagoflash.com) en su sitio web. Para utilizar nuestro metodo de pago debes [registrarte](https://app.pagoflash.com/backuser.php/user/new.html?tipo=empresa) como empresa en [nuestro site](http://pagoflash.com). De necesitar algún tipo de ayuda puede comunicarse al siguiente correo **developers@pagoflash.com**

##Requisitos
- PHP 5.3 o superior
- libreria curl

##Instalacion

- Descargar el [sdk](https://raw.githubusercontent.com/PagoFlash/pagoflash-sdk/master/pagoflash.api.client.php) de PagoFlash para php
- Incluir el sdk en su script principal

##Uso Basico
```php
<?php
include_once('pagoflash.api.client.php');

$urlCallbacks =urlencode("http://www.misitio.com/callback.php");
// Key (Clave) cadena de 32 caracteres generado por la aplicación
$key_public = "key_public"; 
// (Clave secreta) cadena de 20 caracteres generado por la aplicación.
$key_secret = "key_secret"; 

// si desea ejecutar en el entorno de pruebas pasar (true) en el 4to parametro
$api = new apiPagoflash($key_token,$key_secret, $urlCallbacks,false);

$cabeceraDeCompra = array(
    // Alfanumérico de máximo 45 caracteres.
    "pc_order_number"   => "8", 
    // Float, sin separadores de miles, utilizamos el punto (.) como separadores 
    // de Decimales. Máximo dos decimales
    "pc_amount"         => "40" 
);

$ProductItems = array();
$product_1 = array(
    // Nombre.  127 char max.
    'pr_name'    => 'Nombre del producto/servicio vendido', 
    // Descripción .  Maximo 230 caracteres.
    'pr_desc'    => ' Descripción del producto/servicio vendido.', 
    // Precio individual. Float, sin separadores de miles, utilizamos 
    // el punto (.) como separadores de Decimales. Máximo dos decimales
    'pr_price'   => '20',
    // Cantidad, Entero sin separadores de miles  
    'pr_qty'     => '1', 
    // Dirección de imagen.  Debe ser una dirección (url) válida para la imagen.
    'pr_img'     => 'http://www.misitio.com/producto/image/imagen.jpg', 
);

array_push($ProductItems, $product_1);

$pagoFlashRequestData = array(
    'cabecera_de_compra'    => $cabeceraDeCompra, 
    'productos_items'       => $ProductItems
);
$response = $api->procesarPago($pagoFlashRequestData, $_SERVER['HTTP_USER_AGENT']);
$pfResponse = json_decode($response);

if($pfResponse->success)
    header("Location: ".$pfResponse->url_to_buy);
else{
    //manejo del error.
}
?>
```
    
##Documentacion del sdk

###Parametros

- **$key_public**: identificador del punto de venta, se genera al crear un punto de venta en una cuenta tipo empresa de PagoFlash, formato: UOmRvAQ4FodjSfqd6trsvpJPETgT9hxZ 
- **key_secret** clave privada del punto de venta, se genera al crear un punto de venta en una cuenta tipo empresa de PagoFlash, formato: h0lmI11KlPpsVBCT8EZi
- **site_url (Callback)** *requerido*: url del sitio al cual se realizara la llamada de retorno desde PagoFlash cuando se complete una transaccion.
- **test_mode** parametro booleano que indica si las transacciones se ralizaran en el entorno de pruebas o el real.

###Plataformas con Plugins PagoFlash
Desarrollamos plugins para las principales plataformas de e-commerce existentes, de esta manera el proceso de integración con PagoFlash es mucho mas sencillo.


![OpenCart](http://www.paygatewayonline.com/wp-content/uploads/2014/10/opencart.png "OpenCart") ![Python](http://snag.gy/pyEp4.jpg "Python") ![Magento](http://www.web-design-phuket.com/images/magento.jpg "Magento") ![Prestashop](http://webpay.svea.com/PageFiles/16088/Prestashop_150x75.png "Prestashop")

###Valores retornados por PagoFlash
Al finalizar la transacción retornamos un parámetro ('tk') con el cual podrán verificar si la transacción fue satisfactoria o no. Para ello existe el método en nuestro API llamado validarTokenDeTransaccion . A continuación definimos su uso.
```php
<?php 
include_once('api_client/pagoflash.api.client.php');
$urlCallbacks =urlencode("http://www.mitienda.co/payprocess");
// Key (Clave) cadena de 32 caracteres generado por la aplicación
$key_public  = "key_public"; 
//  (Clave secreta) cadena de 20 caracteres generado por la aplicación.
$key_secret = "key_secret"; 

$api = new apiPagoflash($key_public,$key_secret, $urlCallbacks);
$response = $api->validarTokenDeTransaccion($_GET["tk"], $_SERVER['HTTP_USER_AGENT']);

switch ($responseObj->cod)
{
    // Sucede cuando los parámetros para identificar el punto de venta no coinciden 
    // con los almacenados en la plataforma PagoFlash
    case "4" : 
        print "Prametros recibidos no coinciden"; 
        break;
    // Sucede cuando el token enviado para ser verificado no pertenece al punto de 
    // venta.
    case "6" : 
        print "Transaccion no pertenece a su punto de venta";
        break;
    // Sucede cuando la transacción enviada para ser verificada no fue completada 
    // en la plataforma.
    case "5" : 
        print "Esta transaccion no completada";
        break;
    // Sucede cuando la transacción enviada para ser verificada fue completada 
    // de manera satisfactoria.
    case "1" : 
        print "Transaccion valida y procesada satisfactoriamente";
        break;
}

?>
```