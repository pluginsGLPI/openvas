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

class PluginOpenvasItem extends CommonDBChild {
   // From CommonDBChild
   static public $itemtype             = 'itemtype';
   static public $items_id             = 'items_id';
   public $dohistory                   = true;

   static public $checkParentRights    = CommonDBConnexity::DONT_CHECK_ITEM_RIGHTS;

   static $rightname      = 'plugin_openvas_item';
   static $host_matching  = [];
   static $tasks_response = null;

   public static function getTypeName($nb = 0) {
      return __("Openvas Item", 'openvas');
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
         AND `items_id`='".$item->getID()."'");
         return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
      }
   }


   /**
   * @param $item            CommonGLPI object
   * @param $tabnum          (default 1)
   * @param $withtemplate    (default 0)
   */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $ovitem = new self();
      $ovitem->getFromDBForItem($item->getType(), $item->getID());

      self::showForItem($item, $ovitem);
      return true;
   }

   function getFromDBForItem($itemtype, $items_id) {
      global $DB;

      $iterator = $DB->request('glpi_plugin_openvas_items',
                               [ 'itemtype' => $itemtype,
                                 'items_id' => $items_id,
                                 'FIELDS' => [ 'id' ]
                               ]);
      if ($result = $iterator->next()) {
         $this->getFromDB($result['id']);
         return true;
      } else {
         $this->getEmpty();
         return false;
      }
   }

   /**
   * Show OpenVAS infos for an asset
   *
   * @since 1.0
   * @param $item the asset's object
   * @param $openvas_item the OpenVAs item linked to the asset
   * @return nothing
   */
   public static function showForItem(CommonDBTM $item, PluginOpenvasItem $openvas_item) {
      global $CFG_GLPI;
      if (isset($openvas_item->fields['id'])) {
         $id = $openvas_item->getID();
      } else {
         $id = 0;
      }

      $real_host = ($openvas_item->fields['openvas_id']
      && $openvas_item->fields['openvas_id'] != NOT_AVAILABLE);
      $alive = PluginOpenvasOmp::ping();

      $form_url = $openvas_item->getFormURL().'?id='.$id.'&itemtype='
      .$item->getType().'&items_id='.$item->getID();

      $options = ['candel'    => false,
      'formtitle' => __("OpenVAS", "openvas"),
      'target'    => $form_url,
      'colspan'   => 4];

      $openvas_item->showFormHeader($options);

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Target", "openvas") . "</td>";
      echo "<td>";
      if ($alive && $item->canUpdate()) {
         PluginOpenvasOmp::dropdownTargets('openvas_id', $openvas_item->fields['openvas_id']);
      } else {
         echo __("Cannot contact OpenVAS", "openvas");
      }
      if ($openvas_item->fields['openvas_id']) {
         $link = PluginOpenvasConfig::getConsoleURL();
         $link.= "?cmd=get_target&target_id=".$openvas_item->fields['openvas_id'];
         echo "&nbsp;<a href='$link' target='_blank'>";
         echo "<img src='".$CFG_GLPI["root_doc"]."/pics/web.png' class='middle' alt=\""
         .__('View in OpenVAS', 'openvas')."\" title=\""
         .__('View in OpenVAS', 'openvas')."\" >";
         echo "</a>";

         if ($alive && self::canUpdate()) {
            echo "&nbsp;";
            $form = self::getFormURL(true);
            echo "<a href='$form?id=$id&refresh=1'>"
            ."<img src='".$CFG_GLPI["root_doc"]."/pics/refresh.png'
            alt='".__('Refresh')."' title='".__('Refresh')."'></a>";
         }
         echo "</td>";

         if ($openvas_item->fields['openvas_host']) {
            echo "<td>".__('Host')."</td>";
            echo "<td>".$openvas_item->fields['openvas_host']."</td>";
         } else {
            echo "<td colspan='2'></td>";
         }
      }
      echo "</tr>";

      $openvas_item->showFormButtons($options);

      if ($real_host) {

         $tasks = PluginOpenvasOmp::getTasksForATarget($openvas_item->fields['openvas_id']);
         if (is_array($tasks) && !empty($tasks)) {
            foreach ($tasks as $task_id => $task) {
               $tmp['openvas_severity']       = $task['severity'];
               $tmp['openvas_date_last_scan'] = $task['date_last_scan'];
               $tmp['id'] = $id;
               $openvas_item->update($tmp);
               break;
            }
         }
         echo "<form name='formtasks' method='post'
         action='$form_url&refresh' enctype=\"multipart/form-data\">";

         echo "<input type='hidden' name='id' value='".$openvas_item->fields['id']."'>";

         echo "<div class='spaced' id='tabsbody'>";
         echo "<table class='tab_cadre_fixe' id='taskformtable'>";
         echo "<th colspan='4'>".__('General')."</th></tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>" . __("Name") . "</td>";
         echo "<td>";
         echo $openvas_item->fields['openvas_name'];
         echo "</td>";
         echo "<td>" . __("Comments") . "</td>";
         echo "<td>".$openvas_item->fields['openvas_comment']."</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>" . __("Severity", "openvas") . "</td>";
         echo "<td>";
         echo PluginOpenvasToolbox::displayThreat($openvas_item->fields['openvas_threat'],
         $openvas_item->fields['openvas_severity']);
         echo "</td>";
         echo "<td>" . __("Last run") . "</td>";
         echo "<td>";
         echo Html::convDateTime($openvas_item->fields['openvas_date_last_scan']);
         echo "</td>";
         echo "</tr>";

         echo "</table>";

         if ($alive
         && Session::haveRight('plugin_openvas_task', READ)
         && $openvas_item->fields['openvas_id'] != NOT_AVAILABLE) {
            $tasks = PluginOpenvasOmp::getTasksForATarget($openvas_item->fields['openvas_id']);
            if (is_array($tasks) && !empty($tasks)) {
               echo "<table class='tab_cadre_fixe' id='taskformtable'>";
               echo "<tr class='tab_bg_1' align='center'>";
               echo "<th>"._n('Task', 'Tasks', 1)."</th><th>"
               ._n("Status", "Statuses", 1)."</th><th>"
               ."</th><th>"
               .__('Severity', 'openvas')."</th><th>"
               .__('Setup')."</th><th>"
               .__('Scanner', 'openvas')."</th><th>"
               .__("Last run")."</th><th>"
               ._n("Report", "Reports", 1)."</th></tr>";
               foreach ($tasks as $task_id => $task) {
                  echo "<tr class='tab_bg_1' align='center'>";
                  $link = PluginOpenvasConfig::getConsoleURL();
                  $link.= "?cmd=get_task&task_id=".$task_id;
                  echo "<td><a href='$link' target='_blank'>".$task['name']."</a></td>";
                  $status = $task['status'];
                  if ($task['progress'] && $task['progress'] > 0) {
                     $status .= " (".$task['progress']."%)";
                  }
                  echo "<td>$status</td>";
                  if (Session::haveRight('plugin_openvas_task', UPDATE)) {
                     echo "<td>".PluginOpenvasTask::getTaskActionButton(self::getFormURL(true), $task_id, $task['status'])."</td>";
                  } else {
                     echo "<td></td>";
                  }
                  $threat = PluginOpenvasToolbox::getThreatForSeverity($task['severity'], false);
                  echo "<td>".PluginOpenvasToolbox::displayThreat($threat, $task['severity'])."</td>";
                  echo "<td>".$task['scanner']."</td>";
                  echo "<td>".$task['config']."</td>";
                  echo "<td>".$task['date_last_scan']."</td>";
                  echo "<td>";
                  if (!PluginOpenvasOmp::isTaskRunning($task['status'])) {
                     $link = PluginOpenvasConfig::getConsoleURL();
                     $link.= "?cmd=get_report&report_id=".$task['report'];
                     echo "<a href='$link' target='_blank'>";
                     echo "<img src='".$CFG_GLPI["root_doc"]."/pics/web.png' class='middle' alt=\""
                     .__('View in OpenVAS', 'openvas')."\" title=\""
                     .__('View in OpenVAS', 'openvas')."\" >";
                     echo "</a>";

                  }
                  echo "</td>";
                  echo "</tr>";
               }
               echo "</table>";
            }
         }

         echo "</div>";

         Html::closeForm();
      }
      if (Session::haveRight('plugin_openvas_vulnerability', READ)) {
         PluginOpenvasVulnerability_Item::showForItem($item);
      }
   }

   /**
   * Fill a PluginOpenvasItem by providing an itemtype and items_id
   *
   * @since 1.0
   * @param itemtype the item type
   * @param items_id the asset ID
   * @return true if successfully loaded
   */
   function getFromDBByID($itemtype, $items_id) {
      global $DB;

      $iterator = $DB->request('glpi_plugin_openvas_items',
                               [ 'AND'   => [ 'itemtype' => $itemtype,
                                              'items_id' => $items_id
                                            ],
                                 'LIMIT' => 1
                                ], '');
      if (!$iterator->numrows()) {
         return false;
      } else {
         $this->fields = $iterator->next();
         return true;
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
         $tmp = [ 'openvas_name'    => $target['name'],
                  'openvas_host'    => $target['host'],
                  'openvas_comment' => $target['comment'],
                  'id'              => $openvas_line_id
                ];
         $tmp = Toolbox::addslashes_deep($tmp);
         $item->update($tmp);
         self::updateTaskInfosForTarget($item->fields['openvas_id'], $openvas_line_id);
         return true;
      } else {
         return false;
      }
   }

   /**
   * Try to get an OpenVAS target by it's host
   * @since 1.0
   * @param $host the host as provided by OpenVAS (in general an IP address)
   * @return an array representing a PluginOpenvasItem or false if none found
   */
   public static function getItemByHost($host, $check_fqdn = false) {
      global $DB, $CFG_GLPI;

      //If host is already in the cache
      if (isset(self::$host_matching[$host])) {
         return self::$host_matching[$host];
      }

      //First: check if the host provided is already associated with an asset
      $iterator = $DB->request('glpi_plugin_openvas_items',
                               [ 'FIELDS' => [ 'itemtype', 'items_id'],
                                 'OR'     => [ 'openvas_host' => $host,
                                 'openvas_name' => $host]
                               ]);
      if ($iterator->numrows()) {
         $tmp = $iterator->next();
         self::$host_matching[$host] = [ 'itemtype' => $tmp['itemtype'],
                                         'items_id' => $tmp['items_id']
                                       ];
         return self::$host_matching[$host];
      } else {
         //Second step: check if the host refers to an IP address
         $iterator_ip = $DB->request('glpi_ipaddresses', [ 'name' => $host]);
         if ($iterator_ip->numrows()) {
            $tmp = $iterator_ip->next();
            self::$host_matching[$host] = [ 'itemtype' => $tmp['mainitemtype'],
                                            'items_id' => $tmp['mainitems_id']
                                          ];
            return self::$host_matching[$host];
         } else if ($check_fqdn) {

            //Third step: try the FQDN
            foreach ($CFG_GLPI['networkport_types'] as $itemtype) {
               $table = getTableForItemtype($itemtype);
               if ($DB->fieldExists($table, 'domains_id')) {
                  $concat    = "CONCAT_WS('.', `$table`.`name`, `glpi_domains`.`name`)";
                  $left_join = "LEFT JOIN `glpi_domains`
                  ON `glpi_domains`.`id`=`$table`.`domains_id`";
               } else {
                  $concat    = "`$table`.`name`";
                  $left_join = "";
               }
               $query = "SELECT `$table`.`id`, $concat AS `fqdn`
               FROM `$table` $left_join
               HAVING `fqdn`='".$host."'";
               $iterator_fqdn = $DB->request($query);
               if ($iterator_fqdn->numrows()) {
                  $asset = $iterator_fqdn->next();
                  self::$host_matching[$host] = [ 'itemtype' => $itemtype,
                                                  'items_id' => $asset['id']
                                                ];
                  return self::$host_matching[$host];
               }
            }
         }

         //Forth step : check the hostname only
         //only if the host provided is a fqdn
         if (preg_match("/[.]/", $host)) {
            foreach ($CFG_GLPI['networkport_types'] as $itemtype) {
               $table = getTableForItemtype($itemtype);
               $iterator = $DB->request($table, [ 'name' =>  $host]);
               if ($iterator->numrows()) {
                  $asset = $iterator->next();
                  self::$host_matching[$host] = [ 'itemtype' => $itemtype,
                                                  'items_id' => $asset['id']
                                                ];
                  return self::$host_matching[$host];
               }
            }
         }
      }
      return false;
   }

   /**
   * Import or update data coming from OpenVAS
   *
   * @since 1.0
   * @param $task the automatic task object
   * @return nothing
   */
   static function cronOpenvasSynchronize($task) {
      global $DB, $CFG_GLPI;

      $item = new self();
      //Total of export lines
      $index = 0;

      $config = PluginOpenvasConfig::getInstance();
      $days   = $config->fields['search_max_days'];
      $hosts  = [];

      //First step : request targets
      $response = PluginOpenvasOmp::getTargets();
      foreach ($response->target as $target) {

         //Do not process target without host,
         //or 127.0.0.1 or localhost (to large to match a specific asset)
         if (!isset($target->hosts)
            || $target->hosts->__toString() == '127.0.0.1'
            || $target->hosts->__toString() == 'localhost') {
            continue;
         }

         //Check if the host has already been processed
         if (!in_array($target->hosts->__toString(), $hosts)) {
            $hosts[] = $target->hosts->__toString();
         } else {
            continue;
         }

         //Get openvas UUID
         $openvas_id = $target->attributes()->id->__toString();

         $tmp = [ 'openvas_host'    => $target->hosts->__toString(),
                  'openvas_name'    => $target->name->__toString(),
                  'openvas_id'      => $target->attributes()->id->__toString(),
                  'openvas_comment' => $target->comment->__toString()
                ];

         $id = $item->addOrUpdateItem($openvas_id, $tmp, $tmp['openvas_host'],
         $index);

         //If the host is linked to an asset: update last task infos
         if ($id) {
            self::updateTaskInfosForTarget($tmp['openvas_id'], $id);
         }
      }

      //Second step : try to get assets from reports
      $response = PluginOpenvasOmp::getReports([ 'type'   => 'assets',
                                                 'pos'    => 1,
                                                 'filter' => [ 'extra' => 'modification_time<'.$days.'d' ]
                                               ]);
      if (isset($response->report->report->host)) {
         foreach ($response->report->report->host as $ovhost) {
            $host = $ovhost->ip->__toString();
            $id   = $item->addOrUpdateItem(NOT_AVAILABLE,
                                           [ 'openvas_host' => $host,
                                             'openvas_id'   => NOT_AVAILABLE
                                           ], $host, $index);
            if ($id) {
               self::updateTaskInfosForTarget($tmp['openvas_id'], $id);
            }
         }
      }

      $task->addVolume($index);
      return true;
   }

   /**
   * Add or update an OpenVAS item linked to an asset
   *
   * @since 1.0
   * @param $item the asset's object
   * @param $openvas_item the OpenVAs item linked to the asset
   * @return nothing
   */
   function addOrUpdateItem($openvas_id, array $params, $host, &$index) {
      global $DB;

      $id = false;

      if ($openvas_id != NOT_AVAILABLE) {
         $sql = [ 'openvas_id' => $openvas_id];
      } else {
         $sql = ['openvas_host' => $host, 'openvas_id' => NOT_AVAILABLE];
      }
      //Check if the host is already linked to a GLPi asset
      $iterator = $DB->request('glpi_plugin_openvas_items', $sql);
      if (!$iterator->numrows()) {
         //Not linked: check if a link could be done
         if ($asset = self::getItemByHost($host, true)) {
            //Link the host to the asset
            $params['itemtype'] = $asset['itemtype'];
            $params['items_id'] = $asset['items_id'];
            $params = Toolbox::addslashes_deep($params);
            if ($id = $this->add($params)) {
               $index++;
            }
         }
      } else {
         //The host was already linked to an asset: update the line in DB
         $current      = $iterator->next();
         $params['id'] = $id = $current['id'];
         $params       = Toolbox::addslashes_deep($params);
         if ($this->update($params)) {
            $index++;
         }
      }
      return $id;
   }


   /**
   * Update last scan date, severity & threat from a task's last report
   *
   * @since 1.0
   * @param $item the asset's object
   * @param $host the OpenVAS host ID
   * @param $line_id the glpi_plugin_openvas_items line identifier
   * @return nothing
   */
   static function updateHostFromLastReport(PluginOpenvasItem $item, $host, $line_id) {
      $report = PluginOpenvasOmp::getLastReportForAHost($host);
      if (PluginOpenvasOmp::isCodeOK(intval($report->attributes()->status))) {
         if (isset($report->report->host->end)) {
            Toolbox::logDebug($report->report);
            $tmp['openvas_date_last_scan'] = $report->report->host->end->__toString();

            //Update severity : a little bit of processing is needed
            //First : get all vulnerabilities
            //Second : pick the highest one
            $severity = false;
            if (isset($report->report->detail)) {
               foreach ($report->report->detail as $detail) {
                  if (isset($detail->value)) {
                     $value = strval($report->report->detail->value);
                     if (preg_match("/[\d.]{3}/", $value)) {
                        if ($severity < floatval($value)) {
                           $severity = floatval($value);
                        }
                     }
                  }
               }
            }
            if ($severity) {
               if ($severity < 0) {
                  $severity = 0;
               }
               $tmp['openvas_severity'] = $severity;
               $tmp['openvas_threat']   = PluginOpenvasToolbox::getThreatForSeverity($severity, false);
            }
            $tmp['id'] = $line_id;
            $item->update($tmp);

         }
      }
   }

   /**
   * Update last scan date, severity & threat from a task's last report
   *
   * @since 1.0
   * @param $item the asset's object
   * @param $host the OpenVAS host ID
   * @param $line_id the glpi_plugin_openvas_items line identifier
   * @return nothing
   */
   static function updateTaskInfosForTarget($openvas_id, $line_id) {
      //Get tasks for this target
      $ovtasks = PluginOpenvasOmp::getTasksForATarget($openvas_id);
      if (is_array($ovtasks) && !empty($ovtasks)) {
         $item = new self();
         //Get the last task
         $ovtask = array_shift($ovtasks);
         $tmp    = [ 'openvas_severity'       => $ovtask['severity'],
                     'openvas_threat'         => PluginOpenvasToolbox::getThreatForSeverity($ovtask['severity'], false),
                     'openvas_date_last_scan' => $ovtask['date_last_scan'],
                     'id'                     => $line_id
                   ];
         $item->update($tmp);
      }
   }

   /**
   * Display severity for an asset
   * @since 1.0
   * @return the number of targets deleted
   */
   static function showSeverityForAnAsset($itemtype, $items_id) {
      global $DB;

      $item = new self();
      if ($item->getFromDBByID($itemtype, $items_id)) {
         return PluginOpenvasToolbox::displayThreat($item->fields['openvas_threat']);
      } else {
         return '';
      }
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
      $index  = 0;

      //TODO to replace by a non SQL query when dbiterator will be able to handle the query
      $query = "SELECT `id`
                FROM `glpi_plugin_openvas_items`
                WHERE `openvas_date_last_scan` < DATE_ADD(CURDATE(),
                   INTERVAL -".$config->fields['retention_delay']." DAY)";
      foreach ($DB->request($query) as $target) {
         $tmp = ['id'               => $target['id'],
                 'openvas_threat'   => NULL,
                 'openvas_severity' => NULL
                ];
         if ($item->update($tmp)) {
            $index++;
         }
      }

      $vuln_item = new PluginOpenvasVulnerability_Item();
      $ids = [];
      $query = "SELECT `id`
                FROM `glpi_plugin_openvas_vulnerabilities_items`
                WHERE `creation_time` < DATE_ADD(CURDATE(),
                   INTERVAL -".$config->fields['retention_delay']." DAY)";
      foreach ($DB->request($query, '', true) as $target) {
         $vuln_item->delete($target);
         $index++;
      }

      $vuln = new PluginOpenvasVulnerability();
      $query = "SELECT `id`
                FROM `glpi_plugin_openvas_vulnerabilities`
                WHERE `id` NOT IN (SELECT DISTINCT `plugin_openvas_vulnerabilities_id`
                   FROM `glpi_plugin_openvas_vulnerabilities_items`)";
      foreach ($DB->request($query, '', true) as $target) {
         $vuln->delete($target);
         $index++;
      }
      $task->addVolume($index);
      return true;
   }

   static function cronInfo($name) {
      if ($name == 'openvasSynchronize') {
         return ['description' => __("Synchronize OpenVAS hosts", "openvas")];
      } else {
         return ['description' => __("Clean OpenVAS infos", "openvas")];
      }
   }

   //----------------- Install & uninstall -------------------//
   public static function install(Migration $migration) {
      global $DB;

      //This class is available since version 1.3.0
      if (!$DB->tableExists("glpi_plugin_openvas_items")) {
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
            `openvas_severity` float(11) NOT NULL DEFAULT '-1',
            `openvas_threat` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
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
            KEY `openvas_threat` (`openvas_threat`),
            KEY `openvas_date_last_scan` (`openvas_date_last_scan`),
            KEY `date_creation` (`date_creation`),
            KEY `date_mod` (`date_mod`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());

         //New installation : add default profile
         PluginOpenvasProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
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
