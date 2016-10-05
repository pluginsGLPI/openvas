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

      $query = "SELECT `id`
                FROM `glpi_plugin_openvas_items`
                WHERE `itemtype`='".$itemtype."'
                   AND `items_id`='$items_id'";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         $id = $DB->result($result, 0, 'id');
         $this->getFromDB($id);
         return true;
      } else {
         $this->getEmpty();
         return false;
      }
   }

   public static function showForItem(CommonDBTM $item, PluginOpenvasItem $openvas_item) {
      if (isset($openvas_item->fields['id'])) {
         $id = $openvas_item->getID();
      } else {
         $id = 0;
      }
      $options = array('formtitle'   => __("OpenVAS", "openvas"),
                       'target' => $openvas_item->getFormURL().
                                   '?id='.$id.'&itemtype='
                                     .$item->getType().'&items_id='.$item->getID());
      $openvas_item->showFormHeader($options);

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("OpenVAS Target UUID", "openvas") . "</td>";
      echo "<td>";
      PluginOpenvasOmp::dropdownTargets('openvas_id', $openvas_item->fields['openvas_id']);
      echo "</td>";
      echo "<td>" . __("Severity", "openvas") . "</td>";
      echo "<td>";
      if ($openvas_item->fields['openvas_severity'] >= 0) {
         echo $openvas_item->fields['openvas_severity'];
      } else {
         echo __('Error');
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Date of last scan", "openvas") . "</td>";
      echo "<td>";
      echo Html::convDateTime($openvas_item->fields['openvas_date_last_scan']);
      echo "</td><td colspan='2'>";
      echo "</tr>";

      if (PluginOpenvasOmp::doPing()) {
         $buttons = array('addbuttons' => array('refresh' => __('Synchronize')));
      } else {
         $buttons = array();
      }
      $openvas_item->showFormButtons($buttons);
   }

   /**
   * @since 1.0
   * Update device informations in GLPi by directly requesting OpenVAS
   * @param $target_id the target UUID in OpenVAS
   * @return boolean the update status
   */
   public static function updateItemFromOpenvas($openvas_line_id) {
      $item = new PluginOpenvasItem();
      $item->getFromDB($openvas_line_id);
      $tasks = PluginOpenvasOmp::getTasksForATarget($item->fields['openvas_id']);
      if (!empty($tasks)) {
         $task                          = array_pop($tasks);
         $tmp['openvas_severity']       = $task['severity'];
         $tmp['openvas_date_last_scan'] = $task['date_last_scan'];
         $tmp['id'] = $openvas_line_id;
         Toolbox::logDebug($tmp);
         $item->update($tmp);
         return true;
      } else {
         return false;
      }
   }

   public static function showTasksForATarget(CommonDBTM $item, PluginOpenvasItem $openvas_item) {
      if (isset($openvas_item->fields['id'])) {
         $tasks = PluginOpenvasOmp::getTasksForATarget($openvas_item->fields['openvas_id']);
      }
   }

   /**
   * Import or update data coming from OpenVAS
   */
   static function cronOpenvasSynchronize($task) {

      //Total of export lines
      $index   = 0;
      $targets = PluginOpenvasOmp::getTargetsAsArray();
      foreach ($targets as $uuid => $host) {

      }
      $task->addVolume($index);
      return true;
   }

   /**
   * Clean informations that are too old, and not relevant anymore
   */
   static function cronOpenvasClean($task) {

      //Total of export lines
      $index = 0;
      $item  = new self();
      $query = "SELECT `id`
                FROM `glpi_plugin_openvas_items`
                WHERE `openvas_date_last_scan`";
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
                     `openvas_severity` float(11) NOT NULL DEFAULT '0',
                     `openvas_date_last_scan` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
                     `date_creation` datetime DEFAULT NULL,
                     `date_mod` datetime DEFAULT NULL,
                     PRIMARY KEY  (`id`),
                     KEY `name` (`name`),
                     KEY `item` (`itemtype`,`items_id`),
                     KEY `openvas_id` (`openvas_id`),
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
