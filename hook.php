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
          $sopt[6100]['joinparams']    = array('jointype' => 'child');
          $sopt[6100]['massiveaction'] = FALSE;

          $sopt[6001]['table']         = 'glpi_plugin_openvas_items';
          $sopt[6001]['field']         = 'imei';
          $sopt[6001]['name']          = __('Airwatch', 'airwatch').'-'.__('IMEI', 'airwatch');
          $sopt[6001]['datatype']      = 'integer';
          $sopt[6001]['joinparams']    = array('jointype' => 'child');
          $sopt[6001]['massiveaction'] = FALSE;

         $sopt[6002]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6002]['field']         = 'simcard_serial';
         $sopt[6002]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Simcard serial number', 'airwatch');
         $sopt[6002]['datatype']      = 'integer';
         $sopt[6002]['joinparams']    = array('jointype' => 'child');
         $sopt[6002]['massiveaction'] = FALSE;

         $sopt[6003]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6003]['field']         = 'date_last_seen';
         $sopt[6003]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Last seen', 'airwatch');
         $sopt[6003]['datatype']      = 'datetime';
         $sopt[6003]['joinparams']    = array('jointype' => 'child');
         $sopt[6003]['massiveaction'] = FALSE;

         $sopt[6004]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6004]['field']         = 'date_last_enrollment';
         $sopt[6004]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Last enrollment date', 'airwatch');
         $sopt[6004]['datatype']      = 'datetime';
         $sopt[6004]['joinparams']    = array('jointype' => 'child');
         $sopt[6004]['massiveaction'] = FALSE;

         $sopt[6005]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6005]['field']         = 'date_last_enrollment_check';
         $sopt[6005]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Last enrollment check date', 'airwatch');
         $sopt[6005]['datatype']      = 'datetime';
         $sopt[6005]['joinparams']    = array('jointype' => 'child');
         $sopt[6005]['massiveaction'] = FALSE;

         $sopt[6006]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6006]['field']         = 'date_last_compliance_check';
         $sopt[6006]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Last compliance check date', 'airwatch');
         $sopt[6006]['datatype']      = 'datetime';
         $sopt[6006]['joinparams']    = array('jointype' => 'child');
         $sopt[6006]['massiveaction'] = FALSE;

         $sopt[6007]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6007]['field']         = 'date_last_compromised_check';
         $sopt[6007]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Last compromised check date', 'airwatch');
         $sopt[6007]['datatype']      = 'datetime';
         $sopt[6007]['joinparams']    = array('jointype' => 'child');
         $sopt[6007]['massiveaction'] = FALSE;

         $sopt[6008]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6008]['field']         = 'is_enrolled';
         $sopt[6008]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Enrollment status', 'airwatch');
         $sopt[6008]['datatype']      = 'bool';
         $sopt[6008]['joinparams']    = array('jointype' => 'child');
         $sopt[6008]['massiveaction'] = FALSE;

         $sopt[6009]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6009]['field']         = 'is_compliant';
         $sopt[6009]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Compliance status', 'airwatch');
         $sopt[6009]['datatype']      = 'bool';
         $sopt[6009]['joinparams']    = array('jointype' => 'child');
         $sopt[6009]['massiveaction'] = FALSE;

         $sopt[6010]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6010]['field']         = 'is_compromised';
         $sopt[6010]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Compromised status', 'airwatch');
         $sopt[6010]['datatype']      = 'bool';
         $sopt[6010]['joinparams']    = array('jointype' => 'child');
         $sopt[6010]['massiveaction'] = FALSE;

         $sopt[6011]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6011]['field']         = 'is_dataencryption';
         $sopt[6011]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Data encryption', 'airwatch');
         $sopt[6011]['datatype']      = 'bool';
         $sopt[6011]['joinparams']    = array('jointype' => 'child');
         $sopt[6011]['massiveaction'] = FALSE;
         $sopt[6011]['searchtype']     = array('equals', 'notequals');

         $sopt[6012]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6012]['field']         = 'is_roaming_enabled';
         $sopt[6012]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Roaming enabled', 'airwatch');
         $sopt[6012]['datatype']      = 'bool';
         $sopt[6012]['joinparams']    = array('jointype' => 'child');
         $sopt[6012]['massiveaction'] = FALSE;
         $sopt[6012]['searchtype']     = array('equals', 'notequals');

         $sopt[6013]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6013]['field']         = 'is_data_roaming_enabled';
         $sopt[6013]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Data roaming enabled', 'airwatch');
         $sopt[6013]['datatype']      = 'bool';
         $sopt[6013]['joinparams']    = array('jointype' => 'child');
         $sopt[6013]['massiveaction'] = FALSE;
         $sopt[6013]['searchtype']     = array('equals', 'notequals');

         $sopt[6014]['table']         = 'glpi_plugin_openvas_items';
         $sopt[6014]['field']         = 'is_voice_roaming_enabled';
         $sopt[6014]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Voice roaming enabled', 'airwatch');
         $sopt[6014]['datatype']      = 'bool';
         $sopt[6014]['joinparams']    = array('jointype' => 'child');
         $sopt[6014]['massiveaction'] = FALSE;
         $sopt[6014]['searchtype']     = array('equals', 'notequals');

         $sopt[6015]['table']         = 'glpi_plugin_airwatch_compliances';
         $sopt[6015]['field']         = 'name';
         $sopt[6015]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Profile', 'airwatch');
         $sopt[6015]['datatype']      = 'string';
         $sopt[6015]['joinparams']    = array('jointype' => 'child');
         $sopt[6015]['massiveaction'] = false;
         $sopt[6015]['forcegroupby']  = true;

         $sopt[6016]['table']         = 'glpi_plugin_airwatch_compliances';
         $sopt[6016]['field']         = 'is_compliant';
         $sopt[6016]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Profile', 'airwatch').'-'.
                                          __('Compliance status', 'airwatch');
         $sopt[6016]['datatype']      = 'airwatch_bool';
         $sopt[6016]['joinparams']    = array('jointype' => 'child');
         $sopt[6016]['massiveaction'] = false;
         $sopt[6016]['forcegroupby']  = true;
         $sopt[6016]['searchtype']    = array('equals', 'notequals');

         $sopt[6017]['table']         = 'glpi_plugin_airwatch_compliances';
         $sopt[6017]['field']         = 'date_last_check';
         $sopt[6017]['name']          = __('Airwatch', 'airwatch').'-'.
                                          __('Profile', 'airwatch').'-'.
                                          __('Last check date', 'airwatch');
         $sopt[6017]['datatype']      = 'datetime';
         $sopt[6017]['joinparams']    = array('jointype' => 'child');
         $sopt[6017]['forcegroupby']  = false;
         $sopt[6017]['massiveaction'] = true;
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
