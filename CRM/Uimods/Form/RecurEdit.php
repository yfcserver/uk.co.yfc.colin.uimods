<?php
/*-------------------------------------------------------+
| YfC UI modicications                                   |
| Copyright (C) 2015 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Uimods_Form_RecurEdit extends CRM_Core_Form {

  public function buildQuickForm() {
    $rcontribution_id = 0;
    $contact_id       = 0;

    if (!empty($_REQUEST['rcid'])) {
      // EDIT existing contribution recur
      $rcontribution_id = (int) $_REQUEST['rcid'];
      $rcontribution = civicrm_api3('ContributionRecur', 'getsingle', array('id' => $rcontribution_id));
      $contact_id = $rcontribution['contact_id'];
    } elseif (!empty($this->_submitValues['rcontribution_id'])) {
      $rcontribution_id = (int) $this->_submitValues['rcontribution_id'];
      $rcontribution = civicrm_api3('ContributionRecur', 'getsingle', array('id' => $rcontribution_id));
      $contact_id = $rcontribution['contact_id'];
    } elseif (!empty($_REQUEST['cid'])) {
      $contact_id = (int) $_REQUEST['cid'];
      $rcontribution = array('contact_id' => $contact_id);
    } elseif (!empty($this->_submitValues['contact_id'])) {
      $contact_id = (int) $this->_submitValues['contact_id'];
      $rcontribution = array('contact_id' => $contact_id);
    } else {
      // no rcid or cid: ERROR
      CRM_Core_Session::setStatus('Error. You need provide cid or rcid.', 'Error', 'error');
      $dashboard_url = CRM_Utils_System::url('civicrm/dashboard', 'reset=1');
      CRM_Utils_System::redirect($dashboard_url);
      return;
    }

    // LOAD contact
    $contact = civicrm_api3('Contact', 'getsingle', array('id' => $contact_id));
    $this->assign('contact', $contact);

    // LOAD lists
    $campaign_query = civicrm_api3('Campaign', 'get', array('version'=>3, 'is_active'=>1, 'option.limit' => 9999, 'option.sort'=>'title'));
    $campaigns = array();
    foreach ($campaign_query['values'] as $campaign_id => $campaign) {
      $campaigns[$campaign_id] = $campaign['title'];
    }

    $cycle_day_list = range(0, 30);
    unset($cycle_day_list[0]);

    $frequencies = array(
      '1-month'   => ts('monthly'),
      '3-month'   => ts('quartely'),
      '6-month'   => ts('semi-anually'),
      '1-year'    => ts('anually'),
      );

    $status_list = CRM_Core_OptionGroup::values('contribution_status', FALSE, FALSE, FALSE, NULL, 'label');

    
    // FORM ELEMENTS
    $this->add(
      'text',
      'amount',
      ts('Amount'),
      array('value' => $this->getCurrentValue('amount', $rcontribution), 'size'=>4),
      true
    );
    $this->addRule('amount', "Please enter a valid amount.", 'money');

    $frequency = $this->add(
      'select',
      'frequency',
      ts('Frequency'),
      $frequencies,
      true,
      array('class' => 'crm-select2')
    );
    $selected_frequency = $this->getCurrentValue('frequency', $rcontribution);
    if ($selected_frequency) {
      $frequency->setSelected($selected_frequency);
    } else {
      $frequency_interval = $this->getCurrentValue('frequency_interval', $rcontribution);
      $frequency_unit     = $this->getCurrentValue('frequency_unit', $rcontribution);
      if ($frequency_interval && $frequency_unit) {
        $frequency->setSelected($frequency_interval . '-' . $frequency_unit);
      }
    }

    $campaign_id = $this->add(
      'select',
      'campaign_id',
      ts('Destination Code'),
      $campaigns,
      true,
      array('class' => 'crm-select2')
    );
    $campaign_id->setSelected($this->getCurrentValue('campaign_id', $rcontribution));

    $financial_type_id = $this->add(
      'select',
      'financial_type_id',
      ts('Financial Type'),
      CRM_Contribute_PseudoConstant::financialType(),
      true,
      array('class' => 'crm-select2')
    );
    $financial_type_id->setSelected($this->getCurrentValue('financial_type_id', $rcontribution));


    // DATES
    $cycle_day = $this->add(
      'select',
      'cycle_day',
      ts('Collection Day'),
      $cycle_day_list,
      true,
      array('class' => 'crm-select2')
    );
    $cycle_day->setSelected($this->getCurrentValue('cycle_day', $rcontribution));

    $this->addDate(
      'start_date',
      'Begins',
      true, 
      array('formatType' => 'searchDate', 'value' => $this->getCurrentDate('start_date', $rcontribution))
      );

    $this->addDate(
      'end_date',
      'Ends',
      false, 
      array('formatType' => 'searchDate', 'value' => $this->getCurrentDate('end_date', $rcontribution))
      );

    $contribution_status_id = $this->add(
      'select',
      'contribution_status_id',
      'Status',
      $status_list,
      true,
      array('class' => 'crm-select2')
    );
    $contribution_status_id->setSelected($this->getCurrentValue('contribution_status_id', $rcontribution));

    // DATA
    $this->add(
      'text',
      'invoice_id',
      'Invoice ID',
      array('invoice_id' => $this->getCurrentValue('invoice_id', $rcontribution), 'size'=>30),
      false
    );

    $this->add(
      'text',
      'trxn_id',
      'Transaction ID',
      array('trxn_id' => $this->getCurrentValue('trxn_id', $rcontribution), 'size'=>30),
      false
    );

    // special fields
    $this->add(
      'text',
      'contact_id',
      'Contact',
      array('value' => $contact_id),
      false
    );
    
    $this->add('text', 'rcontribution_id', '', array('value' => $rcontribution_id, 'hidden'=>1), true);



    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
  }


  public function postProcess() {
    $values = $this->exportValues();

    // store last selection (per user)
    $session = CRM_Core_Session::singleton();
    $user_contact = (int) $session->get('userID');
    CRM_Core_BAO_Setting::setItem($values['campaign_id'],            'uk.co.yfc.colin.uimods', 'last_campaign_id', NULL, $user_contact);
    CRM_Core_BAO_Setting::setItem($values['cycle_day'],              'uk.co.yfc.colin.uimods', 'last_cycle_day', NULL, $user_contact);
    CRM_Core_BAO_Setting::setItem($values['financial_type_id'],      'uk.co.yfc.colin.uimods', 'last_financial_type_id', NULL, $user_contact);
    CRM_Core_BAO_Setting::setItem($values['frequency'],              'uk.co.yfc.colin.uimods', 'last_frequency', NULL, $user_contact);
    CRM_Core_BAO_Setting::setItem($values['contribution_status_id'], 'uk.co.yfc.colin.uimods', 'last_contribution_status_id', NULL, $user_contact);

    // compile contribution object with required values
    $rcontribution = array(
      'contact_id'             => $values['contact_id'],
      'amount'                 => $values['amount'],
      'currency'               => 'GBP',
      'cycle_day'              => $values['cycle_day'],
      'campaign_id'            => $values['campaign_id'],
      'contribution_status_id' => $values['contribution_status_id'],
      'financial_type_id'      => $values['financial_type_id'],
      );

    // set ID (causes update instead of create)
    if (!empty($values['rcontribution_id'])) {
      $rcontribution['id'] = (int) $values['rcontribution_id'];
    }

    // add cycle period
    $period = preg_split("/-/", $values['frequency']);
    $rcontribution['frequency_interval'] = $period[0];
    $rcontribution['frequency_unit']     = $period[1];
    
    // add dates
    $rcontribution['start_date']         = date('Y-m-d', strtotime($values['start_date']));
    if (!empty($rcontribution['end_date'])) {
      $rcontribution['end_date']         = date('Y-m-d', strtotime($values['end_date']));
    }

    // add non-required values
    $rcontribution['trxn_id']            = $values['trxn_id'];
    $rcontribution['invoice_id']         = $values['invoice_id'];

    $result = civicrm_api3('ContributionRecur', 'create', $rcontribution);
    if (empty($rcontribution['id'])) {
      CRM_Core_Session::setStatus(ts('Recurring contribution [%1] created.', array(1 => $result['id'])), ts("Success"), "info");
    } else {
      CRM_Core_Session::setStatus(ts('Recurring contribution [%1] updated.', array(1 => $result['id'])), ts("Success"), "info");
    }

    // $contact_url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$rcontribution['contact_id']}&selectedChild=contribute");
    // CRM_Utils_System::redirect($contact_url);

    parent::postProcess();
  }

  /**
   * get the current value of a key either from the values
   * that are about to be submitted (in case of a validation error)
   * or the ones stored in the settings (in case of a fresh start)
   */
  public function getCurrentValue($key, $rcontribution) {
    if (!empty($this->_submitValues)) {
      return CRM_Utils_array::value($key, $this->_submitValues);
    } elseif (CRM_Utils_Array::value($key, $rcontribution)) {
      return CRM_Utils_Array::value($key, $rcontribution);
    } else {
      $session = CRM_Core_Session::singleton();
      $user_contact = (int) $session->get('userID');
      return CRM_Core_BAO_Setting::getItem('uk.co.yfc.colin.uimods', "last_$key", NULL, NULL, $user_contact);
    }
  }

  /**
   * same as getCurrentValue but adds date formatting
   */
  public function getCurrentDate($key, $rcontribution) {
    $date = $this->getCurrentValue($key, $rcontribution);
    if (empty($date)) {
      return NULL;
    } else {
      return date('m/d/Y', strtotime($date));
    }
  }
}
