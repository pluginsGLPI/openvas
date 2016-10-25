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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOpenvasItem extends CommonDBTM {
   public $dohistory       = true;

   static $rightname = 'config';

   public static function getTypeName($nb = 0) {
      return __("Openvas", 'openvas');
   }

   function post_updateItem($history = 1) {
      if (isset($this->oldvalues) && isset($this->oldvalues['openvas_id'])) {
         self::updateItemFromOpenvas($this->getID());
      }
   }

   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      $itemtype = $item->getType();

      // can exists for template
      if ($itemtype::canView()) {
         $nb = countElementsInTable('glpi_plugin_openvas_items',
                                    "`itemtype`='".$item->getType()."'
                                       && `items_id` = '".$item->getID()."'");
         return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
      }
   }


   /**
    * @param $item            CommonGLPI object
    * @param $tabnum          (default 1)
    * @param $withtemplate    (default 0)
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $openvas_item = new self();
      $openvas_item->getFromDBForItem($item->getType(), $item->getID());

      self::showForItem($item, $openvas_item);
      self::showTasksForATarget($item, $openvas_item);
      return true;
   }

   function getFromDBForItem($itemtype, $items_id) {
      global $DB;

      $iterator = $DB->request('glpi_plugin_openvas_items',
                               [ 'itemtype' => $itemtype, 'items_id' => $items_id,
                                 'FIELDS' => ['id']
                               ]);
      if ($result = $iterator->next()) {
         $this->getFromDB($result['id']);
         return true;
      } else {
         $this->getEmpty();
         return false;
      }
   }

   public static function showForItem(CommonDBTM $item, PluginOpenvasItem $openvas_item) {
      global $CFG_GLPI;
      if (isset($openvas_item->fields['id'])) {
         $id = $openvas_item->getID();
      } else {
         $id = 0;
      }

      $form_url = $openvas_item->getFormURL().'?id='.$id.'&itemtype='
                    .$item->getType().'&items_id='.$item->getID();
      $options = array('candel' => false,
                       'formtitle'   => __("OpenVAS", "openvas"),
                       'target' => $form_url, 'colspan' => 4);
      $openvas_item->showFormHeader($options);

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("OpenVAS Target", "openvas") . "</td>";
      echo "<td>";
      PluginOpenvasOmp::dropdownTargets('openvas_id', $openvas_item->fields['openvas_id']);
      if ($openvas_item->fields['openvas_id']) {
         $link = PluginOpenvasConfig::getConsoleURL();
         $link.= "?cmd=get_target&target_id=".$openvas_item->fields['openvas_id'];
         echo "&nbsp;<a href='$link' target='_blank'>";
         echo "<img src='".$CFG_GLPI["root_doc"]."/pics/web.png' class='middle' alt=\""
            .__('View in OpenVAS', 'openvas')."\" title=\""
            .__('View in OpenVAS', 'openvas')."\" >";
         echo "</a></td>";
      }

      $openvas_item->showFormButtons($options);

      echo "<br/>";
      if ($openvas_item->fields['openvas_id']) {
         echo "<form name='formtasks' method='post' action='$form_url&refresh' enctype=\"multipart/form-data\">";

         echo "<input type='hidden' name='id' value='".$openvas_item->fields['id']."'>";

         echo "<div class='spaced' id='tabsbody'>";
         echo "<table class='tab_cadre_fixe' id='taskformtable'>";
         echo "<th colspan='4'>".__('Target Infos', 'openvas')."</th></tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>" . __("Severity", "openvas") . "</td>";
         echo "<td>";
         if ($openvas_item->fields['openvas_severity'] >= 0) {
            echo $openvas_item->fields['openvas_severity'];
         } else {
            echo __('Error');
         }
         echo "<td>" . __("Date of last scan", "openvas") . "</td>";
         echo "<td>";
         echo Html::convDateTime($openvas_item->fields['openvas_date_last_scan']);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>" . __("Target UUID", "openvas") . "</td>";
         echo "<td>";
         echo $openvas_item->fields['openvas_id'];
         echo "</td>";
         echo "<td>";
         if (PluginOpenvasOmp::ping()) {
            echo Html::submit( __('Synchronize'),
                              array('name'  => 'refresh',
                                    'image' => $CFG_GLPI["root_doc"].'/pics/web.png'));
         }
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>" . __("Name") . "</td>";
         echo "<td>";
         echo $openvas_item->fields['openvas_name'];
         echo "</td>";
         echo "<td>" . __("Comment") . "</td>";
         echo "<td>".$openvas_item->fields['openvas_comment']."</td>";
         echo "</tr>";

         echo "</table>";

         $tasks = PluginOpenvasOmp::getTasksForATarget($openvas_item->fields['openvas_id']);
         if (is_array($tasks) && !empty($tasks)) {
            echo "<table class='tab_cadre_fixe' id='taskformtable'>";
            echo "<tr class='tab_bg_1' align='center'>";
            echo "<th colspan='4'>".__('OpenVAS tasks', 'openvas')."</th></tr>";
            echo "<tr class='tab_bg_1' align='center'>";
            echo "<th>".__('Name')."</th><th>"
               .__('State')."</th><th>"
               .__('Severity', 'openvas')."</th><th>"
               .__("Date last scan", 'openvas')."</th></tr>";
            foreach ($tasks as $task_id => $task) {
               echo "<tr class='tab_bg_1' align='center'>";
               $link = PluginOpenvasConfig::getConsoleURL();
               $link.= "?cmd=get_task&task_id=".$task_id;
               echo "<td><a href='$link' target='_blank'>".$task['name']."</a></td>";
               echo "<td>".$task['status']."</td>";
               echo "<td>".$task['severity']."</td>";
               echo "<td>".$task['date_last_scan']."</td>";
               echo "</tr>";
            }
            echo "</table>";
         }
         echo "</div>";
         Html::closeForm();
      }
   }

   /**
   * Update device informations in GLPi by directly requesting OpenVAS
   *
   * @since 1.0
   * @param $target_id the target UUID in OpenVAS
   * @return boolean the update status
   */
   public static function updateItemFromOpenvas($openvas_line_id) {
      $item = new PluginOpenvasItem();
      $item->getFromDB($openvas_line_id);

      //Get the target
      $target = PluginOpenvasOmp::getOneTargetsDetail($item->fields['openvas_id']);
      //If no target, do not go further
      if (is_array($target) && !empty($target)) {
         //Sync target infos
         $tmp = ['openvas_name'    => $target['name'],
                 'openvas_host'    => $target['host'],
                 'openvas_comment' => $target['comment']
               ];

         //Get tasks for this target
         $tasks = PluginOpenvasOmp::getTasksForATarget($item->fields['openvas_id']);
         if (is_array($tasks) && !empty($tasks)) {
            //Get the last task
            $task                          = array_pop($tasks);
            $tmp['openvas_severity']       = $task['severity'];
            $tmp['openvas_date_last_scan'] = $task['date_last_scan'];
            $tmp['id'] = $openvas_line_id;
            $item->update($tmp);
            return true;
         }
      }
      return false;
   }

   public static function showTasksForATarget(CommonDBTM $item, PluginOpenvasItem $openvas_item) {
      if (isset($openvas_item->fields['id'])) {
         $tasks = PluginOpenvasOmp::getTasksForATarget($openvas_item->fields['openvas_id']);
      }
   }

   public static function getItemByHost($host) {
      global $DB;

      $iterator = $DB->request('glpi_plugin_openvas_items', [ 'openvas_host' => $host]);
      if ($iterator->numrows()) {
         return $iterator->next();
      } else {
         return false;
      }
   }

   /**
   * Import or update data coming from OpenVAS
   * @since 1.0
   */
   static function cronOpenvasSynchronize($task) {
      global $DB;

      //Total of export lines
      $index = 0;
      foreach ($DB->request('glpi_plugin_openvas_items',
                            ['FIELDS' => ['openvas_id', 'id', 'openvas_host'] ]) as $target) {
         //Update target first
         if (PluginOpenvasItem::updateItemFromOpenvas($target['id'])) {
            $index++;
         }
      }
      $task->addVolume($index);
      return true;
   }

   /**
   * Clean informations that are too old, and not relevant anymore
   * @since 1.0
   * @return the number of targets deleted
   */
   static function cronOpenvasClean($task) {
      global $DB;

      $config = PluginOpenvasConfig::getInstance();
      $item   = new self();

      $index = 0;

      //TODO to replace by a non SQL query when dbiterator will be able to handle the query
      $query = "SELECT `id`
                FROM `glpi_plugin_openvas_items`
                WHERE `date_mod` < DATE_ADD(CURDATE(), INTERVAL -".$config->fields['retention_delay']." DAY)";
      foreach ($DB->request($query) as $target) {
                                   Toolbox::logDebug($target);
         if ($item->delete($target, true)) {
            $index++;
         }
      }
      $task->addVolume($index);
      return true;
   }

   static function cronInfo($name) {
      return array('description' => __("OpenVAS connector synchronization", "openvas"));
   }

   //----------------- Install & uninstall -------------------//
   public static function install(Migration $migration) {
      global $DB;

      //This class is available since version 1.3.0
      if (!TableExists("glpi_plugin_openvas_items")) {
         $migration->displayMessage("Install glpi_plugin_openvas_items");

         $config = new self();

         //Install
         $query = "CREATE TABLE `glpi_plugin_openvas_items` (
                     `id` int(11) NOT NULL auto_increment,
                     `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                     `itemtype` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                     `items_id` int(11) NOT NULL DEFAULT '0',
                     `openvas_id` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                     `openvas_name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                     `openvas_host` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                     `openvas_comment` text COLLATE utf8_unicode_ci,
                     `openvas_severity` float(11) NOT NULL DEFAULT '0',
                     `openvas_date_last_scan` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                     `date_creation` datetime DEFAULT NULL,
                     `date_mod` datetime DEFAULT NULL,
                     PRIMARY KEY  (`id`),
                     KEY `name` (`name`),
                     KEY `item` (`itemtype`,`items_id`),
                     KEY `openvas_id` (`openvas_id`),
                     KEY `openvas_name` (`openvas_name`),
                     KEY `openvas_host` (`openvas_host`),
                     KEY `openvas_severity` (`openvas_severity`),
                     KEY `openvas_date_last_scan` (`openvas_date_last_scan`),
                     KEY `date_creation` (`date_creation`),
                     KEY `date_mod` (`date_mod`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }

      $cron = new CronTask;
      if (!$cron->getFromDBbyName(__CLASS__, 'openvasSynchronize')) {
         CronTask::Register(__CLASS__, 'openvasSynchronize', DAY_TIMESTAMP,
                            array('param' => 24, 'mode' => CronTask::MODE_EXTERNAL));
      }
      if (!$cron->getFromDBbyName(__CLASS__, 'openvasClean')) {
         CronTask::Register(__CLASS__, 'openvasClean', DAY_TIMESTAMP,
                            array('param' => 24, 'mode' => CronTask::MODE_EXTERNAL));
      }
   }

   public static function uninstall() {
      global $DB;
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_openvas_items`");
   }
}
