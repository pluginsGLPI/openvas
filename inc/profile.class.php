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

class PluginOpenvasProfile extends Profile {

   static $rightname = "profile";

   static function getAllRights() {
      return [
                ['itemtype' => 'PluginOpenvasItem',
                  'label'   => __('Assets'),
                  'field'   => 'plugin_openvas_item'],
                ['itemtype' => 'PluginOpenvasTask',
                 'label'    => _n('Task', 'Tasks', 2),
                 'field'    => 'plugin_openvas_task'],
                ['itemtype' => 'PluginOpenvasVulnerability',
                  'label'   => __('Vulnerability', 'openvas'),
                  'field'   => 'plugin_openvas_vulnerability' ]
           ];
   }

   /**
    * Clean profiles_id from plugin's profile table
    *
    * @param $ID
   **/
   function cleanProfiles($ID) {
      global $DB;
      $query = "DELETE FROM `glpi_profiles`
                WHERE `profiles_id`='$ID'
                   AND `name` LIKE '%plugin_openvas%'";
      $DB->query($query);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         if ($item->getField('interface') == 'central') {
            return __('OpenVAS', 'openvas');
         }
         return '';
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         $profile = new self();
         $ID   = $item->getField('id');
         //In case there's no right datainjection for this profile, create it
         self::addDefaultProfileInfos($item->getID(), ['plugin_openvas_item' => 0]);
         $profile->showForm($ID);
      }
      return true;
   }


   /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights) {
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'")) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   /**
    * @param $ID  integer
    */
   static function createFirstAccess($profiles_id) {
      include_once(GLPI_ROOT."/plugins/openvas/inc/profile.class.php");
      foreach (self::getAllRights() as $right) {
         self::addDefaultProfileInfos($profiles_id,
                                    ['plugin_openvas_item' => ALLSTANDARDRIGHT,
                                     'plugin_openvas_task' => ALLSTANDARDRIGHT,
                                     'plugin_openvas_vulnerability'  => ALLSTANDARDRIGHT]);
      }
   }

    /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = self::getAllRights();
      $profile->displayRightsChoiceMatrix(self::getAllRights(),
                                          array('canedit'       => $canedit,
                                                'default_class' => 'tab_bg_2',
                                                'title'         => __('General')));
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }
}
