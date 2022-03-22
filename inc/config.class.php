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

class PluginOpenvasConfig extends CommonDBTM {
   static $rightname = 'config';

   //Store plugin's configuration
   private static $_config = null;

   /**
   * Singleton to get configuration
   * @since 1.0
   * @return an instance of the plugin's configuration
   */
   public static function getInstance() {
      if (is_null(self::$_config)) {
         $config = new self();
         if (strpos($_SERVER['HTTP_REFERER'], '/config.form.php?id=')>0) {
            $id = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], '?id=')+4);
            $config->getFromDB($id);
         } else {
            $id_entities = $_SESSION["glpiactive_entity"];
            $config->getFromDBByCrit(["entities_id = ".$id_entities]);
         }
         if (!isset($config->fields['id'])) {
            return false;
         } else {
            self::$_config = $config;
         }
      }
      return self::$_config;
   }

   public static function getTypeName($nb = 0) {
      return __("GLPi openvas Connector", 'openvas');
   }

   function rawSearchOptions() {

      $tab = [];

       $tab[] = [
           'id'     => 'common',
           'name'   => self::getTypeName()
       ];

       $tab[] = [
           'id'             => 1,
           'table'          => $this->getTable(),
           'field'          => 'openvas_host',
           'name'           => __("Host", "openvas"),
           'datatype'       => 'itemlink',
           'itemlink_type'  => $this->getType(),
           'massiveaction'  => false
       ];

       $tab[] = [
           'id'             => 2,
           'table'          => $this->getTable(),
           'field'          => 'id',
           'name'           => __('ID'),
           'massiveaction'  => false,
       ];

       $tab[] = [
           'id'             => 3,
           'table'          => $this->getTable(),
           'field'          => 'openvas_port',
           'name'           => __('Manager port')
       ];

       $tab[] = [
           'id'             => 4,
           'table'          => $this->getTable(),
           'field'          => 'openvas_console_port',
           'name'           => __('Console port')
       ];

       $tab[] = [
           'id'             => 5,
           'table'          => $this->getTable(),
           'field'          => 'requesttypes_id',
           'name'           => RequestType::getTypeName(1)
       ];

       $tab[] = [
         'id'                 => '80',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
       ];

       $tab[] = [
           'id'             => 7,
           'table'          => $this->getTable(),
           'field'          => 'openvas_username',
           'name'           => User::getTypeName(1)
       ];

       $tab[] = [
           'id'             => 8,
           'table'          => $this->getTable(),
           'field'          => 'openvas_password',
           'name'           => __("Password")
       ];

       $tab[] = [
           'id'             => 9,
           'table'          => $this->getTable(),
           'field'          => 'openvas_omp_path',
           'name'           => __("Path to omp", "openvas")
       ];

       $tab[] = [
           'id'             => 10,
           'table'          => $this->getTable(),
           'field'          => 'retention_delay',
           'name'           => __("Target retention delay", "openvas")
       ];

       $tab[] = [
           'id'             => 11,
           'table'          => $this->getTable(),
           'field'          => 'search_max_days',
           'name'           => __("Number of days for searches", "openvas")
       ];

       $tab[] = [
           'id'             => 12,
           'table'          => $this->getTable(),
           'field'          => 'severity_medium_color',
           'name'           => _x('priority', 'Medium')
       ];

       $tab[] = [
           'id'             => 13,
           'table'          => $this->getTable(),
           'field'          => 'severity_low_color',
           'name'           => _x('priority', 'Low')
       ];

       $tab[] = [
           'id'             => 14,
           'table'          => $this->getTable(),
           'field'          => 'severity_high_color',
           'name'           => _x('priority', 'High')
       ];

       $tab[] = [
           'id'             => 15,
           'table'          => $this->getTable(),
           'field'          => 'severity_none_color',
           'name'           => __("None")
       ];

       return $tab;
   }

   public function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<div class='center'>";
      echo "<form name='form' method='post' action='" . $this->getFormURL() . "'>";

      echo "<input type='hidden' name='id' value='1'>";

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='4'>" . __("Configuration") . "</th></tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Host", "openvas") . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "openvas_host");
      echo "</td>";
      echo "<td>" . __("Manager port", "openvas") . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "openvas_port");
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Path to omp", "openvas") . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "openvas_omp_path");
      echo "</td>";
      echo "<td>" . __("Console port", "openvas") . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "openvas_console_port");
      echo "</td>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . User::getTypeName(1) . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "openvas_username");
      echo "</td>";
      echo "<td>" . __("Password") . "</td>";
      echo "<td><input type='password' name='openvas_password' value='".$this->fields['openvas_password']."'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Target retention delay", "openvas") . "</td>";
      echo "<td>";
      Dropdown::showNumber("retention_delay", ['value' => $this->fields['retention_delay'],
      'unit' => _n('Day', 'Days', $this->fields['retention_delay'])]);
      echo "</td>";
      echo "<td>" . __("Number of days for searches", "openvas") . "</td>";
      echo "<td>";
      Dropdown::showNumber("search_max_days", ['value' => $this->fields['search_max_days'],
      'unit' => _n('Day', 'Days', $this->fields['search_max_days'])]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".RequestType::getTypeName(1)."</td>";
      echo "<td>";
      Dropdown::show('RequestType', [ 'name' => 'requesttypes_id',  'value' => $this->fields['requesttypes_id']]);
      echo "</td>";
      echo "<td>".__('Entity')."</td>";
      echo "<td>";
      Dropdown::show('Entity', [ 'name' => 'entities_id',  'value' => $this->fields['entities_id']]);
      echo  "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<th colspan='4'>" . __('Vulnerability', 'openvas'). ' - '. __("Color palette") . "</th></tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . _x('priority', 'High') . "</td>";
      echo "<td>";
      Html::showColorField('severity_high_color', ['value' => $this->fields["severity_high_color"]]);
      echo "</td>";
      echo "<td>" . _x('priority', 'Medium') . "</td>";
      echo "<td>";
      Html::showColorField('severity_medium_color', ['value' => $this->fields["severity_medium_color"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . _x('priority', 'Low') . "</td>";
      echo "<td>";
      Html::showColorField('severity_low_color', ['value' => $this->fields["severity_low_color"]]);
      echo "</td>";
      echo "<td>" . __("None") . "</td>";
      echo "<td>";
      Html::showColorField('severity_none_color', ['value' => $this->fields["severity_none_color"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td colspan='4' align='center'>";
      echo "&nbsp<input type='submit' name='test' value=\"" . _sx("button", "Test") . "\" class='submit' >";
      echo"</td>";
      echo "</tr>";

      $this->showFormButtons();

      Html::closeForm();
   }


   /**
   * @since 1.0
   * Get OpenVAS console URL
   * @return the URL
   */
   public static function getConsoleURL() {
      $config = new self();
      $config->getFromDB(1);
      return 'https://'.$config->fields['openvas_host'].':'
      .$config->fields['openvas_console_port'].'/omp';
   }

   /**
   * Set the last Vulnerability scan date to the current time
   * @since 1.0
   */
   public static function updateVulnerabilitySyncDate() {
      $config = new self();
      $config->update(['id' => 1, 'openvas_results_last_sync' => $_SESSION['glpi_currenttime']]);
   }

   //----------------- Install & uninstall -------------------//
   public static function install(Migration $migration) {
      global $DB;

      if (!countElementsInTable('glpi_requesttypes', ['name' => 'OpenVAS'])) {
         $requesttype = new RequestType();
         $requesttypes_id = $requesttype->add(['name'         => 'OpenVAS',
         'entities_id'  => 0,
         'is_recursive' => 1]);
      } else {
         $iterator = $DB->request('glpi_requesttypes', ['name' => 'OpenVAS']);
         if ($iterator->numrows()) {
            $data = $iterator->next();
            $requesttypes_id = $data['id'];
         } else {
            $requesttypes_id = 0;
         }
      }

      //This class is available since version 1.3.0
      if (!$DB->tableExists("glpi_plugin_openvas_configs")) {
         $migration->displayMessage("Install glpi_plugin_openvas_configs");

         $config = new self();

         //Install
         $query = "CREATE TABLE `glpi_plugin_openvas_configs` (
            `id` int(11) NOT NULL auto_increment,
            `openvas_host` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
            `openvas_port` int(11) NOT NULL DEFAULT '0',
            `openvas_console_port` int(11) NOT NULL DEFAULT '0',
            `requesttypes_id` int(11) NOT NULL DEFAULT '0',
            `entities_id` int(11) NOT NULL DEFAULT '0',
            `openvas_username` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
            `openvas_password` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
            `openvas_omp_path` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
            `retention_delay` int(11) NOT NULL DEFAULT '0',
            `search_max_days` int(11) NOT NULL DEFAULT '0',
            `openvas_results_last_sync` datetime DEFAULT NULL,
            `severity_medium_color` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
            `severity_low_color` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
            `severity_high_color` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
            `severity_none_color` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
            PRIMARY KEY  (`id`),
            KEY `openvas_host` (`openvas_host`),
            KEY `openvas_results_last_sync` (`openvas_results_last_sync`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());

         $tmp = [ 'id'                    => 1,
                  'openvas_host'   => 'localhost',
                  'openvas_port'          => '9390',
                  'openvas_console_port'  => '9392',
                  'openvas_username'      => 'admin',
                  'openvas_password'      => '',
                  'openvas_omp_path'      => '/usr/bin/omp',
                  'retention_delay'       => 30,
                  'search_max_days'       => 10,
                  'severity_high_color'   => '#ff0000',
                  'severity_medium_color' => '#ffb800',
                  'severity_low_color'    => '#3c9fb4',
                  'severity_none_color'   => '#000000',
                  'requesttypes_id'        => $requesttypes_id
               ];
         $config->add($tmp);
      }
      if ($DB->tableExists("glpi_plugin_openvas_configs") && !$DB->fieldExists("glpi_plugin_openvas_configs", "entities_id")) {
         $query = "ALTER TABLE `glpi_plugin_openvas_configs` ADD `entities_id` int(11) NOT NULL DEFAULT '0';";
         $DB->query($query) or die ($DB->error());
      }
   }

   public static function uninstall() {
      global $DB;
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_openvas_configs`");
   }
}
