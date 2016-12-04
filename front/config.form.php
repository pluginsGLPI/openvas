<?php
/*
/*
 * @version $Id$
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
 ---------------------------------------------------------------------- */
 ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");

$config = new PluginOpenvasConfig();

if (isset($_POST["update"])) {
   if ($config->update($_POST)) {
     PluginOpenvasConfig::reloadConfiguration();
   }

   Html::back();
} elseif (isset($_POST["test"])) {
   $result = PluginOpenvasOmp::ping();
   if (!$result) {
      Session::addMessageAfterRedirect(__("Connection failed"), false, ERROR);
   } else {
      Session::addMessageAfterRedirect(__("Test successful"), true, INFO);
   }
   Html::back();
} else {

Html::header(__("OpenVAS", "openvas"), $_SERVER['PHP_SELF'],
             "tools", "PluginOpenvasMenu", "PluginOpenvasConfig");

Session::checkRight("config", UPDATE);
$config->showForm();

Html::footer();

}
