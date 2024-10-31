<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PRO Sitemaps Connect
 *
 * @package           PRO_Sitemaps_Connect
 * @author            PRO Sitemaps
 * @copyright         2024 PRO Sitemaps
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       PRO Sitemaps Connect
 * Description:       This plugin is turning an XML Sitemap created by PRO Sitemaps service into a self-hosted sitemap by serving it directly using your website domain
 * Version:           1.3
 * Requires at least: 6.0
 * Requires PHP:      7.2
 * Author:            PRO Sitemaps
 * Author URI:        https://pro-sitemaps.com
 * Text Domain:       pro-sitemaps-connect
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * Please check http://www.gnu.org/licenses/gpl-2.0.txt for the GNU General Public License
 *
 */


require_once "pro-sitemaps-settings.php";
require_once "pro-sitemaps-api.php";


$prositemaps_helper = new Pro_Sitemaps_Connect_WPHelper();
$prositemaps_api = new Pro_Sitemaps_Connect_WPAPI(__FILE__, $prositemaps_helper);
$prositemaps_settings = new Pro_Sitemaps_Connect_WPSettings(__FILE__, $prositemaps_helper, $prositemaps_api);

?>