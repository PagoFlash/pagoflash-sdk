#Libreria Cliente PHP para el API de [PagoFlash.com](http://pagoflash.com)

Aquí encontrará la información necesaria para integrar y utilizar el API de [PagoFlash](http://pagoflash.com) en su sitio web. Para utilizar nuestro método de pago debes **[crear una cuenta](https://app.pagoflash.com/profile/account_selection)** de negocios en nuestro site y registrar un **punto de venta**, con esto obtendras la **clave pública (key public)** y la **clave privada (key secret)**, necesarios para integrar el servicio en su sitio web. Si necesitas algún tipo de ayuda puedes envíar un correo a **contacto@pagoflash.com** con el asunto **Integración de botón de pago**.

##Requisitos
- PHP 5.3 o superior
- libreria curl

##Instalación

- Descargar el [sdk](https://raw.githubusercontent.com/PagoFlash/pagoflash-sdk/master/pagoflash.api.client.php) de PagoFlash para php
- Incluir el sdk en su script principal

##Pasos para la integración

Para hacer pruebas ingresa en nuestro sitio de pruebas y [regístra una cuenta de negocios](http://app-test2.pagoflash.com/profile/register/business), luego de llenar y confirmar tus datos, completa los datos de tu perfil, registra un punto de venta, llena los datos necesarios y una vez registrado el punto, la plataforma generará códigos **key_token** y **key_secret** que encontrarás en la pestaña **Integración** del punto de venta, utilízalos en el sdk como se muestra a continuación:

```php
<?php
//Importa el archivo pagoflas.api.client.php que contiene las clases que permiten la conexión con el API
include_once('pagoflash.api.client.php');
// url de tu sitio donde deberás procesar el pago
$urlCallbacks = "http://www.misitio.com/procesar_pago.php";
// cadena de 32 caracteres generada por la aplicación, Ej. aslkasjlkjl2LKLKjkjdkjkljlk&as87
$key_public = "tu_clave_publica";
// cadena de 20 caracteres generado por la aplicación. Ej. KJHjhKJH644GGr769jjh
$key_secret = "tu_clave_secreta";
// Si deseas ejecutar en el entorno de producción pasar (false) en el 4to parametro
$api = new apiPagoflash($key_public,$key_secret, $urlCallbacks,true);


//Cabecera de la transacción
$cabeceraDeCompra = array(
    // Código de la orden (Alfanumérico de máximo 45 caracteres).
    "pc_order_number"   => "001", 
    // Monto total de la orden, número decimal sin separadores de miles, 
    // utiliza el punto (.) como separadores de decimales. Máximo dos decimales
    // Ej. 9999.99
    "pc_amount"         => "20000" 
);

//Producto o productos que serán el motivo de la transacción
$ProductItems = array();
$product_1 = array(
    // Id. de tu porducto. Ej. 1
    'pr_id'    => 1,
    // Nombre.  127 caracteres máximo.
    'pr_name'    => 'Nombre del producto-servicio vendido', 
    // Descripción .  Maximo 230 caracteres.
    'pr_desc'    => 'Descripción del producto-servicio vendido.', 
    // Precio individual del producto. sin separadores de miles, 
    // utiliza el punto (.) como separadores de decimales. Máximo dos decimales
    // Ej. 9999.99
    'pr_price'   => '20000',
    // Cantidad, Entero sin separadores de miles  
    'pr_qty'     => '1', 
    // Dirección de imagen. debe ser una dirección (url) válida para la imagen.
    'pr_img'     => 'http://www.misitio.com/producto/image/imagen.jpg', 
);

array_push($ProductItems, $product_1);

//La información conjunta para ser procesada
$pagoFlashRequestData = array(
    'cabecera_de_compra'    => $cabeceraDeCompra, 
    'productos_items'       => $ProductItems
);

//Se realiza el proceso de pago, devuelve JSON con la respuesta del servidor
$response = $api->procesarPago($pagoFlashRequestData, $_SERVER['HTTP_USER_AGENT']);
$pfResponse = json_decode($response);

//Si es exitoso, genera y guarda un link de pago en (url_to_buy) el cual se usará para redirigir al formulario de pago
if($pfResponse->success){
    ?>
    <a href="<?php echo $pfResponse->url_to_buy ?>" target="_blank">Pagar</a>
    <?php
    //header("Location: ".$pfResponse->url_to_buy);
}else{
    //manejo del error.
}
?>
```
    
##Documentación del sdk

###Parametros

- **$key_public** *requerido*: identificador del punto de venta, se genera al crear un punto de venta en una cuenta tipo empresa de PagoFlash, formato: UOmRvAQ4FodjSfqd6trsvpJPETgT9hxZ 
- **$key_secret** *requerido*: clave privada del punto de venta, se genera al crear un punto de venta en una cuenta tipo empresa de PagoFlash, formato: h0lmI11KlPpsVBCT8EZi
- **$url_process** *requerido*: url del sitio al cual se realizara la llamada de retorno desde PagoFlash cuando se complete una transaccion.
- **$test_mode**: parámetro booleano que indica si las transacciones se ralizaran en el entorno de pruebas o el real.

Utiliza estos números de tarjeta de crédito para realizar las pruebas:
###- Transacción exitosa:   2222444455556666
###- Transacción rechazada: 4444444444445555
(Puedes ingresar cualquier otra información relacionada con la tarjeta)

###Valores retornados por PagoFlash
Al finalizar la transacción retornamos un parámetro ('tk') con el cual podrán verificar si la transacción fue satisfactoria o no. Para ello existe el método en nuestro API llamado validarTokenDeTransaccion . A continuación definimos su uso.
```php
<?php
include_once('pagoflash.api.client.php');
// url de tu sitio donde deberás procesar el pago
$url_process = "http://www.misitio.com/procesar_pago.php";
// cadena de 32 caracteres generada por la aplicación, Ej. aslkasjlkjl2LKLKjkjdkjkljlk&as87
$key_public = "tu_clave_publica";
// cadena de 20 caracteres generado por la aplicación. Ej. KJHjhKJH644GGr769jjh
$key_secret = "tu_clave_secreta";
$test_mode = true
//el cuarto parámetro (true) es para activar el modo de pruebas, para desactivar colocar en **false**
$api = new apiPagoflash($key_public,$key_secret, $urlCallbacks,$test_mode);

$response = $api->validarTokenDeTransaccion($_GET["tk"], $_SERVER['HTTP_USER_AGENT']);
$responseObj = json_decode($response, true);

switch ($responseObj["cod"])
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
