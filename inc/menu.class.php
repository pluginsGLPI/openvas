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

      if (PluginOpenvasVulnerability::canView()) {
         $menu['options']['vulnerability']['title'] = PluginOpenvasVulnerability::getTypeName(2);
         $menu['options']['vulnerability']['page']  = PluginOpenvasVulnerability::getSearchURL(false);
         $menu['options']['vulnerability']['links']['search'] = PluginOpenvasVulnerability::getSearchURL(false);

         $menu['rulevulnerability']['title'] = PluginOpenvasRuleVulnerabilityCollection::getTypeName(2);
         $menu['rulevulnerability']['page']  = PluginOpenvasRuleVulnerabilityCollection::getSearchURL(false);
         $menu['rulevulnerability']['links']['search'] = PluginOpenvasRuleVulnerabilityCollection::getSearchURL(false);

      }
      return $menu;
   }
}
