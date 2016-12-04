<?php
/*
 * @version $Id$
 LICENSE

  This file is part of the openvas plugin.

 OpenVAS plugin is free software; you can redistribute it and/or modify
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

class PluginOpenvasToolbox {

   static function getThreatForSeverity($severity, $label = true) {
     if ($severity > 6.9) {
       $threat = PluginOpenvasOmp::THREAT_HIGH;
     } elseif ($severity > 3.9) {
       $threat = PluginOpenvasOmp::THREAT_MEDIUM;
     } elseif ($severity > 0) {
       $threat = PluginOpenvasOmp::THREAT_LOW;
     } elseif ($severity == 0) {
       $threat = PluginOpenvasOmp::THREAT_NONE;
     } elseif ($severity < -1) {
       $threat = PluginOpenvasOmp::THREAT_ERROR;
     }
     if ($label) {
       return self::getThreat($threat);
     } else {
       return $threat;
     }
   }

   static function getThreat($threat) {
     $threats = [PluginOpenvasOmp::THREAT_HIGH   => _x('priority', 'High'),
                 PluginOpenvasOmp::THREAT_MEDIUM => _x('priority', 'Medium'),
                 PluginOpenvasOmp::THREAT_LOW    => _x('priority', 'Low'),
                 PluginOpenvasOmp::THREAT_NONE   => __('None'),
                 PluginOpenvasOmp::THREAT_ERROR  => __('Error')
                ];

     if (isset($threats[$threat])) {
       return $threats[$threat];
     } else {
       return '';
     }
   }

   static function dropdownThreat($threat) {
     return  Dropdown::showFromArray('threat', self::getThreat(),
                                     [ 'value' => $threat]);
   }

   /**
   * Clean informations that are too old, and not relevant anymore
   * @since 1.0
   * @return the number of targets deleted
   */
   static function displayThreat($task_status, $threat, $severity) {

     $config = PluginOpenvasConfig::getInstance();
     $out    = '';
     $color  = '';

     if ($task_status && PluginOpenvasOmp::isTaskRunning($task_status)) {
       return NOT_AVAILABLE;
     }

     $text = self::getThreat($threat);
     if ($severity > 0 ) {
       $text.= " ($severity)";
     }

     if ($severity == 0) {
       return $text;
     }

     switch ($threat) {
       case PluginOpenvasOmp::THREAT_HIGH:
          $color = $config->fields['severity_high_color'];
          break;
       case PluginOpenvasOmp::THREAT_MEDIUM:
          $color = $config->fields['severity_medium_color'];
          break;
       case PluginOpenvasOmp::THREAT_LOW:
          $color = $config->fields['severity_low_color'];
          break;
       case PluginOpenvasOmp::THREAT_ERROR:
          $color = $config->fields['severity_none_color'];
          break;
     }

     $out  = "<div class='center' style='color: white; background-color: #ffffff; width: 100%;
               border: 0px solid #9BA563; position: relative;' >";
     $out .= "<div style='position:absolute;'>&nbsp;".$text."</div>";
     $out .= "<div class='center' style='background-color: ".$color.";
               width: 90px; height: 30px' ></div>";
     $out .= "</div>";

     return $out;
   }
}
