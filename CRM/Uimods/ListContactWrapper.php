<?php
/*-------------------------------------------------------+
| YFC Colin/CiviCRM UI Modifications                     |
| Copyright (C) 2016 SYSTOPIA                            |
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
class CRM_Uimods_ListContactWrapper implements API_Wrapper {

  /**
   * the wrapper contains a method that allows you to alter the parameters of the api request (including the action and the entity)
   */
  public function fromApiInput($apiRequest) {
    // nothing to do here
    return $apiRequest;
  }
 
  /**
   * alter the result before returning it to the caller.
   */
  public function toApiOutput($apiRequest, $result) {
    // add the ID to the label
    foreach ($result['values'] as &$entry) {
      $entry['label'] = "[{$entry['id']}] {$entry['label']}";
    }
    return $result;
  }
}