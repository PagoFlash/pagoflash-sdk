<?php

namespace pagoflash\woocommerce;

if (false === defined('ABSPATH'))
{
  header('Location: http://www.enebruskemlem.com.ve');
  exit;
}

/*
 * Copyright 2015  Enebrus Kem Lem, C.A. - email : contacto@enebruskemlem.com.ve
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, version 2, as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*
 * Plugin Name: PagoFlash - Método de Pago para WooCommerce
 * Plugin URI: http://pagoflash.com/
 * Description: Permite a tus clientes de la tienda virtual realizarte los pagos usando la plataforma de PagoFlash International
 * Version: 1.1-2015
 * Author: Enebrus Kem Lem, C.A.
 * Author URI: http://www.enebruskemlem.com.ve
 * License: GPL2
 */

// incorpora el punto de entrada del plugin
require_once dirname(__FILE__) . '/inc/Plugin.php';

// establece la función que se ejecutará al activar el plugin
register_activation_hook(__FILE__, ['pagoflash\woocommerce\inc\Plugin', 'onActivate']);
// establece la función que se ejecutará al desactivar el plugin
register_deactivation_hook(__FILE__, ['pagoflash\woocommerce\inc\Plugin', 'onDeactivate']);
