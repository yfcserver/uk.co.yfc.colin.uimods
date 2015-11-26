<?php

require_once 'uimods.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function uimods_civicrm_config(&$config) {
  _uimods_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function uimods_civicrm_xmlMenu(&$files) {
  _uimods_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function uimods_civicrm_install() {
  _uimods_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function uimods_civicrm_uninstall() {
  _uimods_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function uimods_civicrm_enable() {
  _uimods_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function uimods_civicrm_disable() {
  _uimods_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function uimods_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _uimods_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function uimods_civicrm_managed(&$entities) {
  _uimods_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function uimods_civicrm_caseTypes(&$caseTypes) {
  _uimods_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function uimods_civicrm_angularModules(&$angularModules) {
_uimods_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function uimods_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _uimods_civix_civicrm_alterSettingsFolders($metaDataFolders);
}





/**
 * Extend contribution search to display destination codes
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_searchColumns
 */
function uimods_civicrm_searchColumns( $objectName, &$headers,  &$values, &$selector ) {
  if ( $objectName == 'contribution' ) {
    // replace the 'Premium' column with 'Destination Code'
    foreach ($headers as $id => $header) {
      if (isset($header['name']) && $header['name']=='Premium') {
        $headers[$id]['name'] = 'Destination Code';
        unset( $headers[$id]['sort'] );
      }
    }

    // collect campaign IDs
    $campaign_ids = array();
    foreach ($values as $id => $contribution) {
      if (!empty($contribution['campaign_id'])) {
        $campaign_id = (int) $contribution['campaign_id'];
        $campaign_ids[$campaign_id] = $campaign_id;
      }
    }

    $campaign2destinationcode = array();
    if (!empty($campaign_ids)) {
      $campaign_id_list = implode(',', $campaign_ids);
      $destinationcode_query_sql = "SELECT id, external_identifier FROM civicrm_campaign WHERE id IN ($campaign_id_list);";
      $destinationcode_query = CRM_Core_DAO::executeQuery($destinationcode_query_sql);
      while ($destinationcode_query->fetch()) {
        $campaign2destinationcode[$destinationcode_query->id] = $destinationcode_query->external_identifier;
      }
    }

    // Now we'll override the values of 'Premium' ('product_name')
    foreach ($values as $id => &$contribution) {
      if (!empty($contribution['campaign_id'])) {
        $campaign_id = (int) $contribution['campaign_id'];
        $contribution['product_name'] = $campaign2destinationcode[$campaign_id];
      } else {
        $contribution['product_name'] = '';
      }
    }
  }
}

/** 
 * Make campaign_id (destination code) required for contributions
 */
function uimods_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_Contribution') {
    $form->addRule('campaign_id', ts('Please enter a destination code'), 'required', NULL, NULL, NULL, TRUE);
  }
}


/**
 * put the new rcontribtion edit mask in the action links
 */
function uimods_civicrm_links( $op, $objectName, $objectId, &$links, &$mask, &$values ) {
  if ($op == 'contribution.selector.recurring') {
    foreach ($links as $key => &$link) {
      if ($link['name'] == 'Edit') {
        $link['url'] = 'civicrm/rcontribution/edit';
        $link['qs'] = 'reset=1&rcid=%%crid%%';
        // $link['class'] = 'no-popup';
      }
    }
  }
}

/**
 * put a new rcontribtion edit button in rcontribution view
 */
function uimods_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Contribute_Page_ContributionRecur') {
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/Uimods/Page/ContributionRecur.uimods.tpl'
    ));
  }
}

/**
 * put a new rcontribtion action in summary action list
 */
function uimods_civicrm_summaryActions( &$actions, $contactID ) {
  $actions['add_rcontribution'] = array(
      'title'           => ts("Add Recurring Contribution"),
      'weight'          => 5,
      'ref'             => 'add-recurring-contribution',
      'key'             => 'add_rcontribution',
      'component'       => 'CiviContribute',
      'href'            => CRM_Utils_System::url('civicrm/rcontribution/edit', "cid=$contactID"),
      'permissions'     => array('access CiviContribute', 'edit contributions')
    );
}
