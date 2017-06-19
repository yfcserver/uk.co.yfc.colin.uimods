<?php
/*-------------------------------------------------------+
| YFC Colin/CiviCRM UI Modifications                     |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

/**
 * This wrapper will simply add the contact ID to the label in Contact.getlist
 *  which is used e.g. for AJAX autocomplete boxes
 */
class CRM_Uimods_ContributionBatches {

  /**
   * run a query to see which batches the given contribution is in,
   * and return a datastructure containing some information on that
   */
  public static function getBatchData($contribution_id) {
    $result = array();

    // find financial batches connected to this contribution
    $contribution_id = (int) $contribution_id;
    $query = "SELECT
                 civicrm_batch.name AS batch_name,
                 civicrm_batch.id   AS batch_id
              FROM civicrm_entity_financial_trxn
              LEFT JOIN civicrm_entity_batch ON civicrm_entity_batch.entity_table = 'civicrm_financial_trxn' AND civicrm_entity_batch.entity_id = financial_trxn_id
              LEFT JOIN civicrm_batch ON civicrm_batch.id = civicrm_entity_batch.batch_id
              WHERE civicrm_entity_financial_trxn.entity_table = 'civicrm_contribution'
                AND civicrm_entity_financial_trxn.entity_id = {$contribution_id}";
    $batch = CRM_Core_DAO::executeQuery($query);

    // evaluate results
    $batch_info = array();
    while ($batch->fetch()) {
      if ($batch->batch_id) {
        $batch_info[$batch->batch_id] = $batch->batch_name;
      }
    }
    $result['data'] = $batch_info;

    // prepare string representation
    if (empty($batch_info)) {
      $result['string'] = 'None';
    } else {
      $result['string'] = implode(', ', $batch_info);
    }

    return $result;
  }
}