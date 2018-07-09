<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 typology plugin for GLPI
 Copyright (C) 2009-2016 by the typology Development Team.

 https://github.com/InfotelGLPI/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of typology.

 typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   include ('../../../inc/includes.php');
}

Session::checkCentralAccess();

if (isset($_POST["sub_type"])) {
   $sub_type = $_POST["sub_type"];
} else if (isset($_GET["sub_type"])) {
   $sub_type = $_GET["sub_type"];
} else {
   $sub_type = 0;
}

if (isset($_POST["rules_id"])) {
   $rules_id = $_POST["rules_id"];
} else if (isset($_GET["rules_id"])) {
   $rules_id = $_GET["rules_id"];
} else {
   $rules_id = 0;
}

if (!$rule = getItemForItemtype($sub_type)) {
   exit;
}
$rule->checkGlobal(READ);

$test_rule_output = null;

Html::popHeader(__('Setup'), $_SERVER['PHP_SELF']);

$rule->showRulePreviewCriteriasForm($_SERVER['PHP_SELF'], $rules_id);

if (isset($_POST["test_rule"])) {
   $params = array();
   //Unset values that must not be processed by the rule
   unset($_POST["test_rule"]);
   unset($_POST["rules_id"]);
   unset($_POST["sub_type"]);
   $rule->getRuleWithCriteriasAndActions($rules_id, 1, 1);

   // Need for RuleEngines
   foreach ($_POST as $key => $val) {
      $_POST[$key] = stripslashes($_POST[$key]);
   }
   //Add rules specific POST fields to the param array
   $params = $rule->addSpecificParamsForPreview($params);

   $input = $rule->prepareAllInputDataForProcess($_POST, $params);
   //$rule->regex_results = array();
   echo "<br>";
   $rule->showRulePreviewResultsForm($_SERVER['PHP_SELF'], $input, $params);
}

Html::popFooter();
