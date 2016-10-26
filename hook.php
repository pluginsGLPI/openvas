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

 function plugin_openvas_getAddSearchOptions($itemtype) {

    $sopt = array();
    if ($itemtype == 'Computer' || $itemtype == 'NetworkEquipment') {
          $sopt[6100]['table']         = 'glpi_plugin_openvas_items';
          $sopt[6100]['field']         = 'openvas_id';
          $sopt[6100]['name']          = __('OpenVAS', 'openvas').'-'.__("OpenVAS Target UUID", "openvas");
          $sopt[6100]['datatype']      = 'string';
          $sopt[6100]['joinparams']    = array('jointype' => 'itemtype_item');
          $sopt[6100]['massiveaction'] = FALSE;

          $sopt[6101]['table']         = 'glpi_plugin_openvas_items';
          $sopt[6101]['field']         = 'openvas_severity';
          $sopt[6101]['name']          = __('OpenVAS', 'openvas').'-'.__('Severity', 'openvas');
          $sopt[6101]['datatype']      = 'float';
          $sopt[6101]['joinparams']    = array('jointype' => 'itemtype_item');
          $sopt[6101]['massiveaction'] = FALSE;

         $sopt[6102]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6102]['field']         = 'openvas_date_last_scan';
         $sopt[6102]['name']          = __('OpenVAS', 'openvas').'-'.
                                          __('Date of last scan', 'openvas');
         $sopt[6102]['datatype']      = 'datetime';
         $sopt[6102]['joinparams']    = array('jointype' => 'itemtype_item');
         $sopt[6102]['massiveaction'] = FALSE;

         $sopt[6103]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6103]['field']         = 'openvas_name';
         $sopt[6103]['name']          = __('OpenVAS', 'openvas').'-'.
                                          __('Target Name', 'openvas');
         $sopt[6103]['datatype']      = 'string';
         $sopt[6103]['joinparams']    = array('jointype' => 'itemtype_item');
         $sopt[6103]['massiveaction'] = FALSE;

         $sopt[6104]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6104]['field']         = 'openvas_name';
         $sopt[6104]['name']          = __('OpenVAS', 'openvas').'-'.
                                          __('Comment');
         $sopt[6104]['datatype']      = 'text';
         $sopt[6104]['joinparams']    = array('jointype' => 'itemtype_item');
         $sopt[6104]['massiveaction'] = FALSE;

         $sopt[6105]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6105]['field']         = 'openvas_host';
         $sopt[6105]['name']          = __('OpenVAS', 'openvas').'-'.
                                          __('Host', 'openvas');
         $sopt[6105]['datatype']      = 'string';
         $sopt[6105]['joinparams']    = array('jointype' => 'itemtype_item');
         $sopt[6105]['massiveaction'] = FALSE;

         $sopt[6106]['table']         = 'glpi_plugin_openvas_vulnerabilities';
         $sopt[6106]['field']         = 'name';
         $sopt[6106]['name']          = __('OpenVAS', 'openvas').'-'.
                                          __('Vulnerability', 'openvas');
         $sopt[6106]['datatype']      = 'string';
         $sopt[6106]['joinparams']    = array('jointype' => 'itemtype_item');
         $sopt[6106]['massiveaction'] = FALSE;

         $sopt[6107]['table']         = 'glpi_plugin_openvas_vulnerabilities';
         $sopt[6107]['field']         = 'name';
         $sopt[6107]['name']          = __('OpenVAS', 'openvas').'-'.
                                          __('Severity', 'openvas');
         $sopt[6107]['datatype']      = 'number';
         $sopt[6107]['joinparams']    = array('jointype' => 'itemtype_item');
         $sopt[6107]['massiveaction'] = FALSE;

         $sopt[6108]['table']         = 'glpi_plugin_openvas_vulnerabilities';
         $sopt[6108]['field']         = 'comment';
         $sopt[6108]['name']          = __('OpenVAS', 'openvas').'-'.
                                          __('Comment');
         $sopt[6108]['datatype']      = 'text';
         $sopt[6108]['joinparams']    = array('jointype' => 'itemtype_item');
         $sopt[6108]['massiveaction'] = FALSE;
       }

   return $sopt;
 }

/***************** Install / uninstall functions **************/

function plugin_openvas_install() {
   $migration = new Migration(PLUGIN_OPENVAS_VERSION);
   include (GLPI_ROOT."/plugins/openvas/inc/config.class.php");
   include (GLPI_ROOT."/plugins/openvas/inc/vulnerability.class.php");
   include (GLPI_ROOT."/plugins/openvas/inc/item.class.php");
   PluginopenvasConfig::install($migration);
   PluginOpenvasVulnerability::install($migration);
   PluginOpenvasItem::install($migration);
   return true;
}

function plugin_openvas_uninstall() {
   $migration = new Migration(PLUGIN_OPENVAS_VERSION);
   include (GLPI_ROOT."/plugins/openvas/inc/config.class.php");
   include (GLPI_ROOT."/plugins/openvas/inc/vulnerability.class.php");
   include (GLPI_ROOT."/plugins/openvas/inc/item.class.php");
   PluginopenvasConfig::uninstall($migration);
   PluginOpenvasVulnerability::uninstall($migration);
   PluginOpenvasItem::uninstall($migration);
   return true;
}
