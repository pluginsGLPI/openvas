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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOpenvasTask extends CommonDBTM {

   static $rightname     = 'config';

   public static function getTypeName($nb = 0) {
      return _n("Task", 'Tasks', $nb);
   }


   /**
   * Display the list of OpenVAS tasks
   * @since 1.0
   *
   * @return nothing
   */
   static function showTasks() {
      global $DB, $CFG_GLPI;

      $alive = PluginOpenvasOmp::ping();
      if ($alive) {

         $tasks = PluginOpenvasOmp::getTasks();

         echo "<table class='tab_cadre_fixe' id='taskformtable'>";
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<th>"._n('Task', 'Tasks', 1)."</th><th>"
         .__('Target', 'openvas')."</th><th>"
         ._n("Status", "Statuses", 1)."</th><th>"
         ."</th><th>"
         .__('Threat', 'openvas')."</th><th>"
         .__('Setup')."</th><th>"
         .__('Scanner', 'openvas')."</th><th>"
         .__("Last run")."</th><th>"
         ._n("Report", "Reports", 1)."</th></tr>";

         foreach ($tasks as $task) {
            $result = PluginOpenvasOmp::getOneTaskInfos($task);
            if (!is_array($result)) {
               continue;
            }
            echo "<tr class='tab_bg_1' align='center'>";
            $link = PluginOpenvasConfig::getConsoleURL();
            $link .= "?cmd=get_task&task_id=" . $task['id'];
            echo "<td><a href='$link' target='_blank'>" . $result['name'] . "</a></td>";

            $link .= "?cmd=get_target&target_id=" . $result['target'];
            echo "<td><a href='$link' target='_blank'>" . $result['target_name'] . "</a></td>";

            $status = $result['status'];
            if ($result['progress'] && $result['progress'] > 0) {
               $status .= " (" . $result['progress'] . "%)";
            }
            echo "<td>$status</td>";
            echo "<td>" . self::getTaskActionButton(self::getFormURL(true),
                                                    $result['id'],
                                                    $result['status']) . "</td>";
            echo "<td>" . PluginOpenvasToolbox::displayThreat($result['threat']) . "</td>";
            echo "<td>" . $result['scanner'] . "</td>";
            echo "<td>" . $result['config'] . "</td>";
            echo "<td>" . $result['date_last_scan'] . "</td>";
            echo "<td>";
            if (!PluginOpenvasOmp::isTaskRunning($result['status'])) {
               $link = PluginOpenvasConfig::getConsoleURL();
               $link .= "?cmd=get_report&report_id=" . $result['report'];
               echo "<a href='$link' target='_blank'>";
               echo "<img src='" . $CFG_GLPI["root_doc"] . "/pics/web.png' class='middle' alt=\""
                    . __('View in OpenVAS', 'openvas') . "\" title=\""
                    . __('View in OpenVAS', 'openvas') . "\" >";
               echo "</a>";
            }
         }
         echo "</td></tr>";
         echo "</table>";
      } else {
         echo "<table class='tab_cadre_fixe' id='taskformtable'>";
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<th>".__("Cannot contact OpenVAS", "openvas")."</th>";
         echo "</tr></table>";
      }
   }

   /**
   * Display a form to add a task
   * @since 1.0
   *
   * @return nothing
   */
   static function showAddTaskForm() {
      global $CFG_GLPI;

      echo "<form name='addtask' method='post'
      action='".PluginOpenvasTask::getFormURL(true)."'>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<table class='tab_cadre_fixe' id='taskformtable'>";
      echo "<tr class='tab_bg_1' align='center'>";
      echo "<th colspan='3'>".__('New task')."</th></tr>";
      if (!PluginOpenvasOmp::ping()) {
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<th>".__("Cannot contact OpenVAS", "openvas")."</th>";
         echo "</tr>";
      } else {
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".__('Name')."</td>";
         echo "<td><input type='text' name='name' value=''></td></tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".__('Comments')."</td>";
         echo "<td><input type='text' name='comment' value=''></td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".__('Scanner', 'openvas')."</td>";
         echo "<td>";
         PluginOpenvasOmp::displayDropdown(PluginOpenvasOmp::SCANNER, 'scanner');
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".__('Config', 'openvas')."</td>";
         echo "<td>";
         PluginOpenvasOmp::displayDropdown(PluginOpenvasOmp::CONFIG, 'config');
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".__('Target', 'openvas')."</td>";
         echo "<td>";
         PluginOpenvasOmp::displayDropdown(PluginOpenvasOmp::TARGET, 'target');
         echo "<img alt='' title=\"" . __s('Add') . "\" src='" . $CFG_GLPI["root_doc"] .
              "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
                            onClick=\"" . Html::jsGetElementbyID('add_target') . ".dialog('open');\">";
         echo Ajax::createIframeModalWindow('add_target',
                                            $CFG_GLPI['root_doc'] . "/plugins/openvas/front/target.form.php",
                                            array('display' => false, 'reloadonclose' => true));
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>" . __('Schedule', 'openvas') . "</td>";
         echo "<td>";
         PluginOpenvasOmp::displayDropdown(PluginOpenvasOmp::SCHEDULE, 'schedule', true);
         echo "<img alt='' title=\"" . __s('Add') . "\" src='" . $CFG_GLPI["root_doc"] .
              "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
                            onClick=\"" . Html::jsGetElementbyID('add_schedule') . ".dialog('open');\">";
         echo Ajax::createIframeModalWindow('add_schedule',
                                            $CFG_GLPI['root_doc'] . "/plugins/openvas/front/schedule.form.php",
                                            array('display' => false, 'reloadonclose' => true));
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td colspan='2'>";
         echo "<input type='submit' class='submit' name='save' value='".__('Add')."'>";
         echo "</td>";
         echo "</tr>";

      }
      echo "</tr></table>";
      Html::closeForm();

   }

   /**
   * Return HTML code of a button to start or stop a task
   *
   * @since 1.0
   * @param $task the task ID
   * @param $status the task current status
   * @return the HTML code to be displayed
   */
   static function getTaskActionButton($url, $task_id, $status) {
      global $CFG_GLPI;

      $html = '';
      switch ($status) {
         case 'Done':
         case 'New':
         case 'Stopped':
            $label = __('Start Requested', 'openvas');
            $html = "<a href='$url?task_id=$task_id&action=".PluginOpenvasOmp::START_TASK."'>"
            ."<img src='".$CFG_GLPI["root_doc"]."/plugins/openvas/pics/start.png'
            alt='$label' title='$label'></a>";
         break;

         case 'Running':
         case 'Internal Error':
         case 'Requested':
            $label = __('Stop Requested', 'openvas');
            $html = "<a href='$url?task_id=$task_id&action=".PluginOpenvasOmp::CANCEL_TASK."'>"
            ."<img src='".$CFG_GLPI["root_doc"]."/plugins/openvas/pics/stop.png'
            alt='$label' title='$label'></a>";
         break;

         case 'Delete requested':
         case 'Stop Requested':
         break;
      }
      return $html;
   }

   function formForTarget() {

      global $CFG_GLPI;

      echo "<form name='addtarget' method='post' action='" . $CFG_GLPI['root_doc'] . "/plugins/openvas/front/target.form.php'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='headerRow'>";
      echo '<th colspan="4">' . __('New target', 'openvas') . '</th>';
      echo '</tr>';

      if (!PluginOpenvasOmp::ping()) {
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<th>" . __('Cannot contact OpenVAS', 'openvas') . "</th>";
         echo "</tr>";
      } else {

         echo '<tr class="tab_bg_1">';
         echo '<td colspan="4">';
         Dropdown::showSelectItemFromItemtypes(array('itemtype_name'       => 'item',
                                                     'items_id_name'       => 'add_items_id',
                                                     'itemtypes'           => $CFG_GLPI["asset_types"]));
         echo '</td>';
         echo '</tr>';

         echo '<tr class="tab_bg_1">';
         echo "<td>";
         echo __('Credentials for ', 'openvas') . __('SSH');
         echo "</td>";
         echo "<td>";
         PluginOpenvasOmp::displayDropdown(PluginOpenvasOmp::CREDENTIAL, 'credential', 'credential_ssh');
         echo "</td>";
         echo "<td>";
         echo __('On port:', 'openvas');
         echo "</td>";
         echo "<td>";
         echo "<input id='text' type='text' name='port' value='' class='autocompletion-text-field'>";
         echo "</td>";
         echo '</tr>';

         echo '<tr class="tab_bg_1">';
         echo "<td>";
         echo __('Credentials for ', 'openvas') . __('SMB');
         echo "</td>";
         echo "<td colspan='3'>";
         PluginOpenvasOmp::displayDropdown(PluginOpenvasOmp::CREDENTIAL, 'credential', 'credential_smb');
         echo "</td>";
         echo '</tr>';

         echo "<tr class='tab_bg_2 center'><td colspan='4'>";
         echo Html::submit(_x('button', 'Add'), array('name' => 'add'));
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();

      }

   }


   function formForSchedule() {

      global $CFG_GLPI;

      echo "<form name='addschedule' method='post' action='" . $CFG_GLPI['root_doc'] . "/plugins/openvas/front/schedule.form.php'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='headerRow'>";
      echo '<th colspan="2">' . __('New schedule', 'openvas') . '</th>';
      echo '</tr>';

      if (!PluginOpenvasOmp::ping()) {
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<th>" . __('Cannot contact OpenVAS', 'openvas') . "</th>";
         echo "</tr>";
      } else {

         echo '<tr class="tab_bg_1">';
         echo "<td>";
         echo __('Name');
         echo "</td>";
         echo "<td>";
         echo "<input type='text' name='name' value=''>";
         echo "</td>";
         echo '</tr>';

         echo '<tr class="tab_bg_1">';
         echo "<td>";
         echo __('First time', 'openvas');
         echo "</td>";
         echo "<td>";
         Html::showDateField("date", ['maybeempty' => false]);
         echo "</td>";
         echo '</tr>';

         echo '<tr class="tab_bg_1">';
         echo "<td>";
         echo __('Period (in days)', 'openvas');
         echo "</td>";
         echo "<td>";
         echo "<input type='number' name='period' value='0'>";
         echo "</td>";
         echo '</tr>';

         echo "<tr class='tab_bg_2 center'><td colspan='2'>";
         echo Html::submit(_x('button', 'Add'), array('name' => 'add'));
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();

      }

   }

}
