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

/**
 * Class PluginEventsmanagerDashboard
 */
class PluginOpenvasDashboard extends CommonGLPI {

   public  $widgets = array();
   private $options;
   private $datas, $form;

   /**
    * PluginOpenvasDashboard constructor.
    *
    * @param array $options
    */
   function __construct($options = array()) {
      $this->options = $options;
   }

   /**
    * @return array
    */
   function getWidgetsForItem() {
      return array(
         $this->getType() . "1" => __("Vulnerabilities' summary", 'openvas'),
         $this->getType() . "2" => __("Reports summary", 'openvas')
      );
   }

   /**
    * @param $widgetId
    *
    * @return PluginMydashboardDatatable
    */
   function getWidgetContentForItem($widgetId) {
      global $CFG_GLPI, $DB;

      switch ($widgetId) {
         case $this->getType() . "1":

            $query  = "SELECT DISTINCT `threat`, COUNT(`id`) AS nb
                            FROM `glpi_plugin_openvas_vulnerabilities`";
            $query  .= "GROUP BY `threat` ORDER BY `severity` DESC";
            $result = $DB->query($query);
            $nb     = $DB->numrows($result);

            $widget = new PluginMydashboardHtml();
            $widget->setWidgetTitle(__("Vulnerabilities' summary", "openvas"));

            $name      = [];
            $tabthreat = [];

            $colors[] = "#FF0303"; //red
            $colors[] = "#FFB800"; //yellow
            $colors[] = "#3C9FB4"; //blue
            $colors[] = "#000000"; //black

            if ($nb) {
               while ($data = $DB->fetch_array($result)) {
                  $tabthreat[] = $data['nb'];
                  $name[]      = $data['threat'];
               }
            }

            $dataPieset         = json_encode($tabthreat);
            $backgroundPieColor = json_encode($colors);
            $labelsPie          = json_encode($name);

            $graph = "<script type='text/javascript'>
         
            var dataVulnerabilitiesPie = {
              datasets: [{
                data: $dataPieset,
                backgroundColor: $backgroundPieColor
              }],
              labels: $labelsPie
            };

             var isChartRendered = false;
             var canvas = document.getElementById('VulnerabilitiesSummary');
             var ctx = canvas.getContext('2d');
             ctx.canvas.width = 700;
             ctx.canvas.height = 400;
             var VulnerabilitiesSummary = new Chart(ctx, {
               type: 'pie',
               data: dataVulnerabilitiesPie,
               options: {
                 responsive: true,
                 maintainAspectRatio: true,
                 animation: {
                     onComplete: function() {
                       isChartRendered = true
                     }
                   }
             }
             });

             </script>";

            $opt       = [];
            $criterias = [];
            $params    = ["widgetId"  => $widgetId,
                          "name"      => 'VulnerabilitiesSummary',
                          "onsubmit"  => false,
                          "opt"       => $opt,
                          "criterias" => $criterias,
                          "export"    => true,
                          "canvas"    => true,
                          "nb"        => $nb];
            $graph     .= PluginMydashboardHelper::getGraphHeader($params);
            $widget->toggleWidgetRefresh();
            $widget->setWidgetHtmlContent(
               $graph
            );

            return $widget;

            break;

         case $this->getType() . "2":
            $plugin = new Plugin();
            if ($plugin->isActivated("openvas")) {
               $widget  = new PluginMydashboardDatatable();
               $headers = array(__('Name'), __('Begin date'), __('Threat', 'openvas'), _x('quantity', 'Number of vulnerabilities', 'openvas'));

               if (PluginOpenvasOmp::ping()) {
                  $tasks = PluginOpenvasOmp::getTasks();
               }
               //               else {
               //                  $tasks   = [];
               //               }
               $data = array();
               $i    = 0;
               //               if (is_array($tasks) && count($tasks) > 0) {
               foreach ($tasks as $task) {
                  $result = PluginOpenvasOmp::getOneTaskInfos($task);
                  if (is_array($result)) {
                     $query   = "SELECT `id`
                            FROM `glpi_computers`
                            WHERE `name` = '" . $result['target_name'] . "'
                            UNION
                            SELECT `id`
                            FROM `glpi_networkequipments`
                            WHERE `name` = '" . $result['target_name'] . "'
                            UNION
                            SELECT `id`
                            FROM `glpi_peripherals`
                            WHERE `name` = '" . $result['target_name'] . "'
                            UNION
                            SELECT `id`
                            FROM `glpi_phones`
                            WHERE `name` = '" . $result['target_name'] . "'
                            UNION
                            SELECT `id`
                            FROM `glpi_printers`
                            WHERE `name` = '" . $result['target_name'] . "'";
                     $results = $DB->query($query);
                     if (isset($results) && $res = $DB->fetch_assoc($results)) {
                        $url         = Toolbox::getItemTypeFormURL('Computer') . "?id=" . $res['id'];
                        $data[$i][0] = "<a href='" . $url . "'>" . $result['target_name'] . "</a>";
                     } else {
                        $data[$i][0] = $result['target_name'];
                     }

                     $simple = $this->xml2array($task);

                     $date        = isset($simple['last_report'][0]['report'][0]['scan_start']) ? $simple['last_report'][0]['report'][0]['scan_start'] : 0;
                     $data[$i][1] = $date;

                     $severity = $result['severity'];
                     if ($result['threat'] == 'None') {
                        $data[$i][2] = PluginOpenvasToolbox::displayThreat($result['threat']);
                     } else {
                        $data[$i][2] = PluginOpenvasToolbox::displayThreat($result['threat']) . "(" . $severity . ")";
                     }

                     $hole         = isset($simple['last_report'][0]['report'][0]['result_count'][0]['hole']) ? $simple['last_report'][0]['report'][0]['result_count'][0]['hole'] : 0;
                     $warning      = isset($simple['last_report'][0]['report'][0]['result_count'][0]['warning']) ? $simple['last_report'][0]['report'][0]['result_count'][0]['warning'] : 0;
                     $log          = isset($simple['last_report'][0]['report'][0]['result_count'][0]['log']) ? $simple['last_report'][0]['report'][0]['result_count'][0]['log'] : 0;
                     $result_count = __('Hole', 'openvas') . ": " . $hole . " - " . __('Warning', 'openvas') . ": " . $warning . " - " . __('Log', 'openvas') . ": " . $log;
                     $data[$i][3]  = $result_count;

                     $i += 1;
                  }
               }
               //               }

               $widget->setTabDatas($data);
               $widget->setTabNames($headers);
               //$temp = $widget->setWidgetType('num-html');
               $widget->setOption("aaSorting", array(array(2, "desc")));
               $widget->toggleWidgetRefresh();
               $widget->setWidgetTitle(__("Reports summary", "openvas"));
               return $widget;
            } else {
               $widget = new PluginMydashboardDatatable();
               $widget->setWidgetTitle(__("Openvas is not activated", 'openvas'));
               return $widget;
            }
            break;
      }
   }

   function xml2array($xml) {
      $arr = array();

      foreach ($xml->children() as $r) {
         $t = array();
         if (count($r->children()) == 0) {
            $arr[$r->getName()] = strval($r);
         } else {
            $arr[$r->getName()][] = $this->xml2array($r);
         }
      }
      return $arr;
   }
}
