<?php 
    

    include_once('pagoflash.api.client.php');

    $urlCallbacks =urlencode("http://localhost.com/pf_test/hola.php");
    
    $key_token  = "1EwYKsfo7sVqjMtINiASLAoq7x9jacB1"; // Key (Clave) cadena de 32 caracteres generado por la aplicación
    $key_secret = "HXQhfWwdehtnUMWxUmfw"; //  (Clave secreta) cadena de 20 caracteres generado por la aplicación.

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
    var_dump($response);
    $pfResponse = json_decode($response);
    
    if($pfResponse->success)    
		header("Location: ".$pfResponse->url_to_buy);
    else{
        //Hacer algo con la excepción.
    }



?>
