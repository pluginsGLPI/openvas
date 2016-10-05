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

/**
* Class to connect to OpenVAS and send commands
* Inspired by the work made php-omp (https://github.com/dmth/php-omp)
*
*/
class PluginOpenvasOmp {

   /**
   * @since 1.0
   *
   * XML to authenticate a user
   * @param username user to authenticate again OpenVAS manager
   * @param password password to use
   * @return the XML string
   */
   private function getXMLForAuthentication($username, $password) {
      return "<authenticate><credentials><username>$username</username><password>$password</password></credentials></authenticate>";
   }

   /**
   * @since 1.0
   *
   * XML to get all targets or one target
   * @param target_id one target uuid or false to get all targets
   * @return the XML string
   */
   private function getXMLForTargets($target_id = false, $tasks_id = false) {
      $command = "<get_targets";
      if ($target_id) {
         $command.= " target_id=\"$target_id\"";
      }
      if ($tasks_id) {
         $command.= " tasks=\"1\"";
      }
      $command.="/>";
      return $command;
   }

   /**
   * @since 1.0
   *
   * XML to get all reports or one report
   * @param report_id one report uuid or false to get all reports
   * @param host_id get report for one host, identified by it's uuid
   * @return the XML string
   */
   private function getXMLForReports($report_id = false, $host_id = false) {
      $command = "<get_reports";
      if ($report_id) {
         $command.= " report_id=\"$report_id\"";
      }
      if ($host_id) {
         $command.= " host=\"$host_id\"";
      }
      $command.="/>";
      return $command;
   }

   /**
   * @since 1.0
   *
   * XML to start a task
   * @param task_id the task to start
   * @return the XML string
   */
   private function getXMLToStartTask($task_id = false) {
      if ($task_id) {
         return "<resume_or_start_task task_id=\"$task_id\"/>";
      }
   }

   /**
   * @since 1.0
   *
   * XML to get all results or just one
   * @param result_id one result uuid of false to get all reports
   * @return the XML string
   */
   private function getXMLForResults($result_id = false, $details = false) {
      $command = "<get_results";
      if ($result_id) {
         $command.= " result_id=\"$result_id\"";
      }
      if ($details) {
         $command.= " details=\"1\"";
      }
      $command.= "/>";
      return $command;
   }

   /**
   * @since 1.0
   *
   * XML to get all tasks or just one
   * @param task_id one result uuid of false to get all tasks
   * @return the XML string
   */
   private function getXMLForTasks($result_id = false, $details = false, $start = 1, $rows=35) {
      $command = "<get_tasks";
      if ($result_id) {
         $command.= " task_id=\"$result_id\"";
      }
      if ($details) {
         $command.= " details=\"1\"";
      }
      $command.= "/>";
      return $command;
   }

   /**
   * @since 1.0
   *
   * Authenticate a user
   * @return true if authenticate, false is not
   */
   static function authenticate() {
      $omp    = new self();
      return $omp->executeCommandWithAuthentication();
   }

   /**
   * @since 1.0
   *
   * Get one or all targets
   * @param target_id the target uuid in OpenVAS
   * @param tasks true si all tasks linked to the target must be collected
   * @return an array of targets, or false if an error occured
   */
   static function getTargets($target_id = false, $tasks = false) {
      $omp    = new self();
      return $omp->executeCommandWithAuthentication($omp->getXMLForTargets($target_id, $tasks));
   }

   /**
   * @since 1.0
   *
   * Get one or all results
   * @param result_id the uuid of a result, or false to get all results
   * @param details true to get full details
   * @return an array of results, or false if an error occured
   */
   static function getResults($result_id = false, $details = false) {
      $omp = new self();
      return $omp->executeCommandWithAuthentication($omp->getXMLForResults($result_id, $details));
   }

   /**
   * @since 1.0
   *
   * Get one or all tasks
   * @param task_id the uuid of a task, or false to get all tasks
   * @param details true to get full details
   * @return an array of tasks, or false if an error occured
   */
   static function getTasks($task_id = false, $details = false) {
      $omp = new self();
      return $omp->executeCommandWithAuthentication($omp->getXMLForTasks($task_id, $details));
   }

   /**
   * @since 1.0
   *
   * Get one or all reports
   * @param report_id the uuid of a report, or false to get all tasks
   * @param host_id on host uuid
   * @return an array of reports, or false if an error occured
   */
   static function getReports($report_id = false, $host_id = false) {
      $omp = new self();
      return $omp->executeCommandWithAuthentication($omp->getXMLForReports($report_id, $host_id));
   }

   function executeCommandWithAuthentication($command = "") {
      $config = new PluginOpenvasConfig();
      $config->getFromDB(1);
      $omp    = new self();
      $content = $this->sendCommand($omp, $config, $command);
      if ($content) {
         return simplexml_load_string($content);
      } else {
         return false;
      }
   }

   /**
   * @since 1.0
   * Send a command to OpenVAS
   * @param $omp OpenVAS object
   * @param $config plugin configuration
   * @param $command the XML command to send to OpenVAS
   * @return the XML response from OpenVAS
   */
   private function sendCommand(PluginOpenvasOmp $omp, PluginOpenvasConfig $config, $command = '') {

      /*
      if ($config->fields['openvas_verify_peer']) {
         $verify_peer = true;
      } else {
         $verify_peer = false;
      }
      if ($config->fields['openvas_allow_self_signed']) {
         $allow_self_signed = true;
      } else {
         $allow_self_signed = false;
      }

      //Set SSL options
      $context = stream_context_create(array(
          'ssl' => array(
             'verify_peer' => $verify_peer,
             'allow_self_signed' => $allow_self_signed
          )
      ));

      $response = null;
      $errno    = null;
      $errstr   = null;
      $content  = '';

      //Connect to OpenVAS using TLS
      $url    = "tls://".$config->fields['openvas_host'].":".$config->fields['openvas_port'];
      $socket = @stream_socket_client($url, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
      if ($errno) {
         return false;
      } else {
         Toolbox::logDebug("Sending command", $command);
        //Write command in the PHP socket
        fwrite($socket, $command);
        //Get the results
        $content = stream_get_contents($socket);
        if (!Toolbox::seems_utf8($content)) {
           $content = Toolbox::encodeInUtf8($content);
        }
        //Close the socket
        fclose($socket);
     }*/

     //Check if omp exists && is executable
     if (!file_exists($config->fields['openvas_omp_path'])
        || !is_executable($config->fields['openvas_omp_path'])
           || !$this->ping($config)) {
        return false;
     }

     //Build the omp command line
     //By using the -X flag, we can send XML commands
     $url = $config->fields['openvas_omp_path']." -h "
         .$config->fields['openvas_host']." -p "
         .$config->fields['openvas_port']."  -u "
         .$config->fields['openvas_username']." -w "
         .$config->fields['openvas_password']." -X \"$command\"";
     //Launch omp executable and get the command's result in $content array
     exec($url, $content);
     if (!is_array($content) && !empty($content)) {
        return false;
     } else {
        return $content[0];
     }
   }


   static function doPing() {
      $config = new PluginOpenvasConfig();
      $config->getFromDB(1);
      $omp = new self();
      return $omp->ping($config);
   }
   
   /**
   * @since 1.0
   *
   * Try to open a connection to the server
   * @param $config a plugin configuration object
   * @return true if a connection can be opened to the server
   */
   function ping(PluginOpenvasConfig $config) {
      $errCode = $errStr = '';
      $result  = false;
      $fp = @fsockopen($config->fields['openvas_host'], $config->fields['openvas_port'],
                       $errCode, $errStr, 1);
      if ($errCode == 0) {
         $result = true;
         fclose($fp);
      }
      return $result;
   }

   /**
   * @since 1.0
   *
   * Show a dropdown displaying OpenVAS targets
   * @param name the dropdown name
   * @param value the selected value to show
   * @return the dropdown ID (is needed)
   */
   static function dropdownTargets($name, $value='') {
      global $DB;

      $results = self::getTargetsAsArray();
      $query   = "SELECT `openvas_id`
                  FROM `glpi_plugin_openvas_items`
                  WHERE `openvas_id` NOT IN ('$value')";
      $used    = array();
      foreach ($DB->request($query) as $val) {
         $used[$val['openvas_id']] = $val['openvas_id'];
      }

      asort($results);
      return Dropdown::showFromArray($name, $results,
                                     array('value' => $value,
                                           'used'  => $used, 'display_emptychoice' => true));
   }

   /**
   * @since 1.0
   * Get all available targets in OpenVAS
   * @return all targets as an array of target uuid => target name or IP address
   */
   static function getTargetsAsArray() {
      $target_response = self::getTargets();

      $results       = array();
      foreach ($target_response->target as $response) {
         $host         = $response->hosts->__toString();
         $id           = $response->attributes()->id->__toString();
         $results[$id] = $host;
      }
      return $results;
   }

   static function getTasksForATarget($target_id = false) {
      if (!$target_id) {
         return true;
      }
      $tasks_response = self::getTasks();
      $results         = array();
      foreach ($tasks_response->task as $response) {
         $tid = $response->target->attributes()->id->__toString();
         if (!$response->attributes()->id->__toString()) {
            continue;
         }
         $id = $response->attributes()->id->__toString();
         if ($tid == $target_id) {

            $progress = "";
            $severity = 0;

            $name   = $response->name->__toString();
            $status = $response->status->__toString();
            if ($status != 'done') {
               $progress = $response->progress->__toString();
               $severity = $response->last_report->report->severity->__toString();
               $tmp_scan_date = $response->last_report->report->scan_end->__toString();
               if (!empty($tmp_scan_date)) {
                  $date_scan_end = new DateTime($tmp_scan_date);
                  $scan_date = date_format($date_scan_end, 'Y-m-d H:i:s');
               }
            }

            $config = $response->config->name->__toString();
            $scanner = $response->scanner->name->__toString();

            $results[$id] = array('name' => $name, 'config' => $config,
                                  'scanner' => $scanner, 'status' => $status, 'progress' => $progress,
                                  'date_last_scan' => $scan_date, 'severity' => $severity);
         }
      }

      return $results;

   }

   static function getReportsForATarget($target_id = false) {
      $reports_response = self::getReports($target_id);
      $results         = array();
      foreach ($target_response->reports as $response) {
         $host         = $response->hosts->__toString();
         $id           = $response->attributes()->id->__toString();
         $results[$id] = $host;
      }

      return $results;

   }

}
