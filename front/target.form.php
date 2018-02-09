<?php
/* @version $Id$
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of the openvas plugin.
 *
 * OpenVAS plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * openvas plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI; along with openvas. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 * @package   openvas
 * @author    Teclib'
 * @copyright Copyright (c) 2016 Teclib'
 * @license   GPLv3
 * http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/openvas
 * @link      http://www.glpi-project.org/
 * @link      http://www.teclib-edition.com/
 * @since     2016
 * ----------------------------------------------------------------------*/

include('../../../inc/includes.php');

$task = new PluginOpenvasTask();

if (isset($_GET['_in_modal'])) {
   Html::popHeader(__('Add a new target', 'openvas'), $_SERVER['PHP_SELF']);
   $task->formForTarget();
   Html::popFooter();
} else if (isset($_POST['add'])) {
   $item = new $_POST['item']();
   $item->getFromDB($_POST['add_items_id']);
   $_POST['name'] = $item->fields['name'];
   $ip            = new IPAddress();
   if (!empty($ip->getFromDBByQuery("WHERE `mainitems_id` = '" . $item->getID() . "' AND `mainitemtype` = '" . $_POST['item'] . "'"))) {
      $_POST['host'] = $ip->fields['name'];
      if (PluginOpenvasOmp::addTarget($_POST)) {
         Session::addMessageAfterRedirect(__('Target created', 'openvas'), true);
         Html::redirect($_SERVER['HTTP_REFERER']);
      }
   } else {
      Session::addMessageAfterRedirect(__("No ip address for item, can't create target", 'openvas'), true);
      Html::back();
   }
}