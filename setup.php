<?php
/*
 * @version $Id$
 LICENSE

  This file is part of the openvas plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 openvas plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with openvas. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   openvas
 @author    Teclib'
 @copyright Copyright (c) 2016 Teclib'
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/pluginsglpi/openvas
 @link      http://www.glpi-project.org/
 @link      http://www.teclib-edition.com/
 @since     2016
 ---------------------------------------------------------------------- */

define ("PLUGIN_OPENVAS_VERSION", "1.0");

function plugin_init_openvas() {
   global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
   $PLUGIN_HOOKS['csrf_compliant']['openvas'] = true;

   $plugin = new Plugin();
   if ($plugin->isActivated('openvas')) {

      Plugin::registerClass('PluginOpenvasItem',
                            [ 'addtabon' => $CFG_GLPI['networkport_types'] ] );
      Plugin::registerClass('PluginOpenvasVulnerability_Item',
                            [ 'addtabon' => ['PluginOpenvasVulnerability'] ]);

      $PLUGIN_HOOKS['use_massive_action']['openvas'] = 1;
      $PLUGIN_HOOKS['config_page']['openvas'] = 'front/config.form.php';

      Plugin::registerClass('PluginOpenvasRuleVulnerabilityCollection',
                            [ 'rulecollections_types' => true]);
      Plugin::registerClass('PluginOpenvasVulnerability',
                            ['ticket_types' => true,
                             'helpdesk_visible_types' => true]);

      foreach ($CFG_GLPI['networkport_types'] as $itemtype) {
          $PLUGIN_HOOKS['pre_item_purge']['openvas'][$itemtype] = 'plugin_openvas_purgeItems';
      }

      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['menu_toadd']['openvas']['tools'] = 'PluginOpenvasMenu';
      }
   }
}

function plugin_version_openvas() {
   global $LANG;

   return [ 'name'           => __("Openvas connector for GLPi", 'openvas'),
            'version'        => PLUGIN_OPENVAS_VERSION,
            'author'         => "<a href='http://www.teclib-edition.com'>Teclib'</a>",
            'license'        => 'GPLv2+',
            'homepage'       => 'https://github.com/pluginsglpi/openvas',
            'minGlpiVersion' => "9.1.1"
          ];
}

function plugin_openvas_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.1.1', 'lt')) {
      echo "This plugin requires GLPI 9.1 or higher";
      return false;
   }

   return true;
}

function plugin_openvas_check_config() {

   return true;
}
?>
