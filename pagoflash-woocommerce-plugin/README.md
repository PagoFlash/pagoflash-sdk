== =================================================================================================
== PagoFlash - Método de Pago para WooCommerce
== Desarrollado por Enebrus Kem Lem, C.A.
== Versión 1.1-2015
== contacto@enebruskemlem.com.ve
== =================================================================================================



-- -------------------------------------------------------------------------------------------------
-- Aspectos técnicos
-- -------------------------------------------------------------------------------------------------

Requerimientos
--------------
- PHP 5.5 o superior
- Wordpress 4.1 o superior


Instalación
------------
1. Colocar la carpeta "pagoflash-woocommerce" dentro del directorio "wp-content/plugins"
2. Iniciar sesión en al área administrativa de Wordpress para tu sitio web
3. Ir al área de administración de plugins y activar el plugin
  "PagoFlash - Método de Pago para WooCommerce"



-- -------------------------------------------------------------------------------------------------
-- Uso
-- -------------------------------------------------------------------------------------------------
01. Entra a la sección de ajustes de WooCommerce y configura el plugin de PagoFlash. Esto lo puedes
  hacer a través de la URL "{mi-sitio-web}/wp-admin/admin.php?page=wc-settings&tab=checkout&section"

02. Eso es todo, ahora tus clientes podrán realizarte los pagos utilizando PagoFlash



-- -------------------------------------------------------------------------------------------------
-- Configuración
-- -------------------------------------------------------------------------------------------------
El área de configuración del plugin se encuentra dentro de la sección de ajustes de WooCommerce, en
el apartado de "Finalizar Compra" y posee las siguientes opciones configurables:

  - Activo: Activa o desactiva el uso de PagoFlash como pasarela de pago.

  - Título: Requerido. Título de la opción de pago que se mostrará cuando el usuario valla a
    completar su compra.

  - Descripción: Requerido. Descripción o instrucciones que se mostrarán al usuario cuando valla a
    seleccionar el pago.

  - Mensaje al terminar exitosamente: Requerido. Mensaje que se mostrará al usuario cuando la compra
    haya sido completada exitosamente.

  - Mensaje en caso de error: Requerido. Mensaje que se mostrará al usuario cuando no haya sido
    posible completar su pago de forma exitosa.

  - Key Token: Requerido. Ficha única que genera PagoFlash al momento de registrar un punto de venta
    virtual.

  - Key Secret: Requerido. Ficha única complementaria que genera PagoFlash al momento de registrar
    un punto de venta virtual.

  - URL Callback: Solo lectura. El contenido de este campo debe ser copiado y pegado en el campo
    "URL callback" del formulario de registro del punto de venta virtual en PagoFlash.

  - Modo de prueba: Activa o desactiva el modo de prueba del plugin. Cuando se está en modo de
    prueba las transacciones no implican un movimiento real de dinero.

  - Log detallado: Activa o desactiva la escritura detallada del funcionamiento del plugin en el
    registro de WooCommerce (Estado del sistema -> Registro). Si esta opción está desactivada solo
    se escribirán los mensajes de error.

  - Email para notificar errores: Requerido. Dirección de email hacia la cual se enviarán los
    detalles técnicos de los errores que ocurran mientras los usuarios utilizan PagoFlash como
    pasarela de pago. Generalmente esta dirección de email será la del personal técnico responsable
    del funcionamiento del sitio web.

  - Notificar errores a PagoFlash: Permítenos saber que ocurrió un error con nuestro plugin y así
    podremos ayudarte proactivamente a solucionarlo.

    Esta opción envía una copia de los detalles técnicos de los errores hacia nuestro equipo de
    soporte para determinar que está sucediendo y darte soluciones.

    El mensaje no contiene información sensible, solo nos avisa que algo no va bien y nos entrega
    los datos, que ya están en nuestra plataforma, para atender la eventualidad puntualmente.



-- -------------------------------------------------------------------------------------------------
-- Personalización
-- -------------------------------------------------------------------------------------------------

Plantillas
----------
Para personalizar la organización visual de los elementos mostrados por el plugin, pueden editarse
los archivos que se encuentran dentro del directorio "templates".