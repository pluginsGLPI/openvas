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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOpenvasToolbox {

   /**
    * Get a threat based on a severity value
    *
    * @since 1.0
    * @param $severity the value
    * @param label get the threat label (true) or value (false)
    * @return the threat label or value
    */
   static function getThreatForSeverity($severity, $label = true) {
      if ($severity > 6.9) {
         $threat = PluginOpenvasOmp::THREAT_HIGH;
      } else if ($severity > 3.9) {
         $threat = PluginOpenvasOmp::THREAT_MEDIUM;
      } else if ($severity > 0) {
         $threat = PluginOpenvasOmp::THREAT_LOW;
      } else if ($severity == 0) {
         $threat = PluginOpenvasOmp::THREAT_NONE;
      } else if ($severity < -1) {
         $threat = PluginOpenvasOmp::THREAT_ERROR;
      }
      if ($label) {
         return self::getThreat($threat);
      } else {
         return $threat;
      }
   }

   /**
   * Get a threat label
   *
   * @since 1.0
   * @param $threat the threat value
   * @return the threat label
   */
   static function getThreat($threat) {
      $threats = [PluginOpenvasOmp::THREAT_HIGH   => _x('priority', 'High'),
                  PluginOpenvasOmp::THREAT_MEDIUM => _x('priority', 'Medium'),
                  PluginOpenvasOmp::THREAT_LOW    => _x('priority', 'Low'),
                  PluginOpenvasOmp::THREAT_NONE   => __('None'),
                  PluginOpenvasOmp::THREAT_ERROR  => __('Error'),
                  PluginOpenvasOmp::THREAT_LOG    => __('Log')
                 ];

      if (isset($threats[$threat])) {
         return $threats[$threat];
      } else {
         return '';
      }
   }

   /**
   * Get the color associated with a threat
   * as defined in the plugin's configuration
   * @since 1.0
   *
   * @param $threat the threat level
   * @return the threat color
   */
   static function getThreatColor($threat) {
      $config = PluginOpenvasConfig::getInstance();

      $color = false;
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
         case PluginOpenvasOmp::THREAT_LOG:
            $color = $config->fields['severity_none_color'];
            break;
      }
      return $color;
   }

   /**
   * Clean informations that are too old, and not relevant anymore
   * @since 1.0
   *
   * @param $task_status the task status
   * @param $threat the threat status
   * @param $severity the severity value
   * @return the HTML display of the threat
   */
   static function displayThreat($threat) {

      $config = PluginOpenvasConfig::getInstance();
      $out    = '';
      $color  = '';

      $text  = self::getThreat($threat);
      $color = self::getThreatColor($threat);
      if (!$color) {
         return $text;
      }
      $out  = "<div class='center' style='color: white; background-color: #ffffff; width: 100%;
                border: 0px solid #9BA563; position: relative;' >";
      $out .= "<div style='position:absolute;'>&nbsp;".$text."</div>";
      $out .= "<div class='center' style='background-color: ".$color.";
                 width: 70px;height: 20px' ></div>";
      $out .= "</div>";

      return $out;
   }
}
