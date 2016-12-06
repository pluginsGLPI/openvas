<?php
/*
 -------------------------------------------------------------------------
 {NAME} plugin for GLPI
 Copyright (C) {YEAR} by the {NAME} Development Team.

 https://github.com/pluginsGLPI/openvas
 -------------------------------------------------------------------------

 LICENSE

 This file is part of OpenVAS.

 OpenVAS is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 OpenVAS is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with OpenVAS. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   openvas
 @author    Teclib'
 @copyright Copyright (c) 2016 Teclib'
 @license   GPLv3
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/pluginsGLPI/openvas
 @link      http://www.glpi-project.org/
 @link      http://www.teclib-edition.com/
 @since     2016
 ----------------------------------------------------------------------
 */

define('PLUGIN_OPENVAS_VERSION', '1.0');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_openvas() {
   global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
   $PLUGIN_HOOKS['csrf_compliant']['openvas'] = true;

   $plugin = new Plugin();
   if ($plugin->isActivated('openvas')) {

     Plugin::registerClass('PluginOpenvasProfile',
                          array('addtabon' => array('Profile')));

      if (Session::haveRight('plugin_openvas_item', READ)) {
        Plugin::registerClass('PluginOpenvasItem',
                              [ 'addtabon' => $CFG_GLPI['networkport_types'] ] );
      }
      if (Session::haveRight('plugin_openvas_vulnerability', READ)) {
        Plugin::registerClass('PluginOpenvasVulnerability_Item',
                              [ 'addtabon' => ['PluginOpenvasVulnerability'] ]);
      }

      $PLUGIN_HOOKS['use_massive_action']['openvas'] = 1;
      $PLUGIN_HOOKS['config_page']['openvas'] = 'front/config.form.php';

      if (Session::haveRight('plugin_openvas_vulnerability', READ)) {
        Plugin::registerClass('PluginOpenvasRuleVulnerabilityCollection',
                              [ 'rulecollections_types' => true]);
      }
      Plugin::registerClass('PluginOpenvasVulnerability',
                            ['ticket_types' => true,
                             'helpdesk_visible_types' => true]);

      foreach ($CFG_GLPI['networkport_types'] as $itemtype) {
          $PLUGIN_HOOKS['pre_item_purge']['openvas'][$itemtype] = 'plugin_openvas_purgeItems';
      }

      $PLUGIN_HOOKS['menu_toadd']['openvas']['tools'] = 'PluginOpenvasMenu';
   }
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_openvas() {
   global $LANG;

   return [ 'name'           => __("GLPi openvas Connector", 'openvas'),
            'version'        => PLUGIN_OPENVAS_VERSION,
            'author'         => "<a href='http://www.teclib-edition.com'>Teclib'</a>",
            'license'        => 'GPLv2+',
            'homepage'       => 'https://github.com/pluginsglpi/openvas',
            'minGlpiVersion' => "9.1.1"
          ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_openvas_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.1.1', 'lt')) {
      echo "This plugin requires GLPI 9.1 or higher";
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_openvas_check_config() {

   return true;
}
?>
