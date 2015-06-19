#Libreria Cliente PHP para el API de [PagoFlash.com](http://pagoflash.com)

Aquí encontrará la información necesaria para utilizar de manera correcta el API de [PagoFlash](http://pagoflash.com) y así poder realizar el proceso de pago en su sitio Web. De necesitar algún tipo de ayuda puede comunicarse al siguiente correo **developers@pagoflash.com**

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

$key_public = "key_public"; // Key (Clave) cadena de 32 caracteres generado por la aplicación
$key_secret = "key_secret"; // (Clave secreta) cadena de 20 caracteres generado por la aplicación.

// si desea ejecutar en el entorno de pruebas pasar (true) en el 4to parametro
$api = new apiPagoflash($key_token,$key_secret, $urlCallbacks,false);

$cabeceraDeCompra = array(
    "pc_order_number"   => "8", // Alfanumérico de máximo 45 caracteres.
    "pc_amount"         => "40" // Float, sin separadores de miles, utilizamos el punto (.) como separadores de Decimales. Máximo dos decimales
);

$ProductItems = array();
$product_1 = array(
    'pr_name'    => 'Nombre del producto/servicio vendido', // Nombre.  127 char max.
    'pr_desc'    => ' Descripción del producto/servicio vendido.', // Descripción .  Maximo 230 caracteres.
    'pr_price'   => '20',// Precio individual. Float, sin separadores de miles, utilizamos el punto (.) como separadores de Decimales. Máximo dos decimales
    'pr_qty'     => '1', // Cantidad, Entero sin separadores de miles  
    'pr_img'     => 'http://www.misitio.com/producto/image/imagen.jpg', // Dirección de imagen.  Debe ser una dirección (url) válida para la imagen.   
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

###Pagar con PagoFlash

	<?php

    include_once('api_client/pagoflash.api.client.php');

    $urlCallbacks =urlencode("http://www.mitienda.co/payprocess");
    
    $key_token  = "QomRvCxz5FollLfqd6trsvpJP3TgTpmm"; // Key (Clave) cadena de 32 caracteres generado por la aplicación
    $key_secret = "40lmIlI1KlPpsU8CT8EZi"; //  (Clave secreta) cadena de 20 caracteres generado por la aplicación.

    $api = new apiPagoflash($key_token,$key_secret, $urlCallbacks);

    $parameters = array();
    
    $cabeceraDeCompra = array(
                    "pc_order_number"       => "8", // Alfanumérico de máximo 45 caracteres.
                    "pc_amount"             => "40" // Float, sin separadores de miles, utilizamos el punto (.) como separadores de Decimales. Máximo dos decimales
                );
    
    $ProductItems = array();
    $product_1 = array(
                    'pr_name'    => 'Nombre del producto/servicio vendido',        // Nombre.  127 char max.
                    'pr_desc'    => ' Descripción del producto/servicio vendido.', // Descripción .  Maximo 230 caracteres.
                    'pr_price'   => '20',                                         // Precio individual. Float, sin separadores de miles, utilizamos el punto (.) como separadores de Decimales. Máximo dos decimales
                    'pr_qty'     => '1',                                         // Cantidad, Entero sin separadores de miles  
                    'pr_img'     => 'http://www.mitienda.com/producto/image/imagen.jpg', // Dirección de imagen.  Debe ser una dirección (url) válida para la imagen.   
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
        //Hacer algo con la excepción.
    }



	?>

###Plataformas con Plugins PagoFlash
Desarrollamos plugins para las principales plataformas de e-commerce existentes, de esta manera el proceso de integración con PagoFlash es mucho mas sencillo.


![OpenCart](http://www.paygatewayonline.com/wp-content/uploads/2014/10/opencart.png "OpenCart") ![Python](http://snag.gy/pyEp4.jpg "Python") ![Magento](http://www.web-design-phuket.com/images/magento.jpg "Magento") ![Prestashop](http://webpay.svea.com/PageFiles/16088/Prestashop_150x75.png "Prestashop")

###Valores retornados por PagoFlash
Al finalizar la transacción retornamos un parámetro ('tk') con el cual podrán verificar si la transacción fue satisfactoria o no. Para ello existe el método en nuestro API llamado validarTokenDeTransaccion . A continuación definimos su uso.

	<?php 
	include_once('api_client/pagoflash.api.client.php');
	$urlCallbacks =urlencode("http://www.mitienda.co/payprocess");
	$key_token  = "QomRvCxz5FollLfqd6trsvpJP3TgTpmm"; // Key (Clave) cadena de 32 caracteres generado por la aplicación
	$key_secret = "40lmIlI1KlPpsU8CT8EZi"; //  (Clave secreta) cadena de 20 caracteres generado por la aplicación.

	$api = new apiPagoflash($key_token,$key_secret, $urlCallbacks);
	$response = $api->validarTokenDeTransaccion($_GET["tk"], $_SERVER['HTTP_USER_AGENT']);

	switch ($responseObj->cod)
	{
	    case "4" : // Sucede cuando los parámetros para identificar el punto de venta no coinciden con los almacenados en la plataforma PagoFlash
	        print "Prametros recibidos no coinciden"; 
	        break;
	    case "6" : // Sucede cuando el token enviado para ser verificado no pertenece al punto de venta.
	        print "Transaccion no pertenece a su punto de venta";
	        break;
	    case "5" : // Sucede cuando la transacción enviada para ser verificada no fue completada en la plataforma.
	        print "Esta transaccion no completada";
	        break;
	    case "1" : // Sucede cuando la transacción enviada para ser verificada fue completada de manera satisfactoria.
	        print "Transaccion valida y procesada satisfactoriamente";
	        break;

	}

	?>

###Botones PagoFlash
Todas estas imagenes de los botones lo podras encontrar en http://www.pagoflash.com/images/

###Clientes del API PagoFlash

- PHP: 