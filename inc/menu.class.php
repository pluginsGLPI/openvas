<?php
/*
 * @version $Id$
 LICENSE

  This file is part of the openvas plugin.

 openvas plugin is free software; you can redistribute it and/or modify
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

class PluginOpenvasMenu extends CommonGLPI {

   public static function getTypeName($nb = 0) {
      return __("OpenVAS", "openvas");
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu          = array();
      $menu['title'] = self::getTypeName(2);
      $menu['page']  = PluginOpenvasVulnerability::getSearchURL(false);
      $menu['links']['search']  = PluginOpenvasTask::getSearchURL(false);

      if (PluginOpenvasVulnerability::canView()) {

         $image_task  = "<img src='".$CFG_GLPI["root_doc"]."/pics/plan.png' title='";
         $image_task .= PluginOpenvasTask::getTypeName();
         $image_task .= "' alt='".PluginOpenvasTask::getTypeName()."'>";

         $image_vuln  = "<img src='".$CFG_GLPI["root_doc"]."/pics/menu_show.png' title='";
         $image_vuln .= PluginOpenvasVulnerability::getTypeName();
         $image_vuln .= "' alt='".PluginOpenvasVulnerability::getTypeName()."'>";

         $menu['options']['PluginOpenvasVulnerability']['title'] = PluginOpenvasVulnerability::getTypeName(2);
         $menu['options']['PluginOpenvasVulnerability']['page']  = PluginOpenvasVulnerability::getSearchURL(false);
         $menu['options']['PluginOpenvasVulnerability']['links']['search'] = PluginOpenvasVulnerability::getSearchURL(false);
         $menu['options']['PluginOpenvasVulnerability']['links'][$image_task]  = PluginOpenvasTask::getSearchURL(false);

         $menu['rulevulnerability']['title'] = PluginOpenvasRuleVulnerabilityCollection::getTypeName(2);
         $menu['rulevulnerability']['page']  = PluginOpenvasRuleVulnerabilityCollection::getSearchURL(false);
         $menu['rulevulnerability']['links']['search'] = PluginOpenvasRuleVulnerabilityCollection::getSearchURL(false);

         $menu['options']['PluginOpenvasTask']['title'] = _n('Task', 'Tasks', 2);
         $menu['options']['PluginOpenvasTask']['page']  = PluginOpenvasTask::getSearchURL(false);
         $menu['options']['PluginOpenvasTask']['links']['search'] = PluginOpenvasTask::getSearchURL(false);
         $menu['options']['PluginOpenvasTask']['links'][$image_vuln]  = PluginOpenvasVulnerability::getSearchURL(false);

      }
      return $menu;
   }
}
