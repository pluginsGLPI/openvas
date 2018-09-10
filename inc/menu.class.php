<?php
/* @version $Id$
--------------------------------------------------------------------------
LICENSE

 This file is part of the openvas plugin.

OpenVAS plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
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
@license   GPLv3
           http://www.gnu.org/licenses/gpl.txt
@link      https://github.com/pluginsGLPI/openvas
@link      http://www.glpi-project.org/
@link      http://www.teclib-edition.com/
@since     2016
----------------------------------------------------------------------*/

class PluginOpenvasMenu extends CommonGLPI {

   public static function getTypeName($nb = 0) {
      return __("OpenVAS", "openvas");
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      if (!Session::haveRight('plugin_openvas_task', READ)
      && !Session::haveRight('plugin_openvas_vulnerability', READ)) {
         return [];
      }
    /*  $menu                    = [];
      $menu['title']           = self::getMenuName();
      $menu['page']            = "/plugins/openvas/front/config.php";
      $menu['links']['search'] = "/plugins/openvas/front/config.php";*/

      $menu          = [];
      $menu['title'] = self::getTypeName(2);
      $menu['page']  = PluginOpenvasVulnerability::getSearchURL(false);
      $menu['links']['search']  = PluginOpenvasVulnerability::getSearchURL(false);
      if (Session::haveRight('plugin_openvas_vulnerability', READ)) {

         $image_task  = "<img src='".$CFG_GLPI["root_doc"]."/pics/plan.png' title='";
         $image_task .= PluginOpenvasTask::getTypeName();
         $image_task .= "' alt='".PluginOpenvasTask::getTypeName()."'>";

         $menu['options']['PluginOpenvasVulnerability']['title'] = PluginOpenvasVulnerability::getTypeName(2);
         $menu['options']['PluginOpenvasVulnerability']['page']  = PluginOpenvasVulnerability::getSearchURL(false);
         $menu['options']['PluginOpenvasVulnerability']['links']['search'] = PluginOpenvasVulnerability::getSearchURL(false);

         $menu['options']['openvasconfig']['title']           = PluginOpenvasConfig::getTypeName(2);
         $menu['options']['openvasconfig']['page']           = PluginOpenvasConfig::getSearchURL(false);
         $menu['options']['openvasconfig']['links']['add']          = '/plugins/openvas/front/config.form.php';
         $menu['options']['openvasconfig']['links']['search']           = PluginOpenvasConfig::getSearchURL(false);

         if (Session::haveRight('plugin_openvas_task', READ)) {
            $menu['options']['PluginOpenvasVulnerability']['links'][$image_task]  = PluginOpenvasTask::getSearchURL(false);
         }

         $menu['rulevulnerability']['title'] = PluginOpenvasRuleVulnerabilityCollection::getTypeName(2);
         $menu['rulevulnerability']['page']  = PluginOpenvasRuleVulnerabilityCollection::getSearchURL(false);
         $menu['rulevulnerability']['links']['search'] = PluginOpenvasRuleVulnerabilityCollection::getSearchURL(false);

      }


      if (Session::haveRight('plugin_openvas_task', READ)) {
         $image_vuln  = "<img src='".$CFG_GLPI["root_doc"]."/pics/menu_show.png' title='";
         $image_vuln .= PluginOpenvasVulnerability::getTypeName();
         $image_vuln .= "' alt='".PluginOpenvasVulnerability::getTypeName()."'>";

         $menu['options']['PluginOpenvasTask']['title'] = _n('Task', 'Tasks', 2);
         $menu['options']['PluginOpenvasTask']['page']  = PluginOpenvasTask::getSearchURL(false);
         $menu['options']['PluginOpenvasTask']['links']['search'] = PluginOpenvasTask::getSearchURL(false);
         if (Session::haveRight('plugin_openvas_vulnerability', READ)) {
            $menu['options']['PluginOpenvasTask']['links'][$image_vuln]  = PluginOpenvasVulnerability::getSearchURL(false);
         }
         if (Session::haveRight('plugin_openvas_task', CREATE)) {
            $menu['options']['PluginOpenvasTask']['links']['add'] = PluginOpenvasTask::getFormURL(false)."?add=1";
         }
      }
      return $menu;
   }
}
