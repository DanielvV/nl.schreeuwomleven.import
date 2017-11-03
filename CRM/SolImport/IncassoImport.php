<?php
/**
 *  process the solimportincasso table
 *
 * @author Holland Open Source
 * @date 1-8-17 13:26
 * @license AGPL-3.0
 *
 */
class CRM_SolImport_IncassoImport extends CRM_SolImport_AbstractImport {

  private $contactId;
  private $recurId;

  function process() {
    $source = $this->_sourceData;

    $this->contactId = $this->searchByExternalId($this->_sourceData->contact_id);

    if (empty($this->contactId)) {
      $this->_logger->logMessage('E', "could not identify a contact for " . $this->_sourceData->contact_id);
      return FALSE;
    }

    $this->createRecur($source);
    $this->createMandateRcur($source);

    $result = civicrm_api3('Contribution', 'get', [
      'return' => ["payment_instrument_id"],
      'contact_id' => $this->contactId,
      'total_amount' => $source->amount,
    ]);

    foreach ($result['values'] as $contributionId => $contribution) {

      $result = civicrm_api3('Note', 'get', [
        'return' => ["note"],
        'entity_table' => "civicrm_contribution",
        'entity_id' => $contributionId,
      ]);

      if ( $contribution['payment_instrument'] = "SEPA DD One-off Transaction" || $result['values'][$result['id']]['note'] = $source->note ) {
        $result = civicrm_api3('Contribution', 'create', [
          'id' => $contributionId,
          'payment_instrument_id' => "RCUR",
          'contribution_recur_id' => $this->recurId,
        ]);
        $result = civicrm_api3('Payment', 'get', [
           'contribution_id' => $contributionId,
        ]);
        foreach ($result['values'] as $paymentId => $payment) {
          $result = civicrm_api3('Payment', 'delete', [
            'id' => $paymentId,
          ]);
        }
      }
    }

    return TRUE;
  }

  function processOneOff() {
    $source = $this->_sourceData;
    
    $this->createMandateOoff($source);

    return TRUE;
  }

  private function createRecur($source) {
    //hack: put start/create/modified date 10 years before next scheduled contribution date 
    $startdate = date("Y-m-d", strtotime($source->next_sched_contribution_date . " -10 year"));

    $result = civicrm_api3('ContributionRecur', 'create', [
      'contact_id' => $this->contactId,
      'amount' => $source->amount,
      'frequency_unit' => "month",
      'frequency_interval' => $source->frequency_interval,
      'start_date' => $startdate,
      'create_date' => $startdate,
      'modified_date' => $startdate,
      'contribution_status_id' => "Pending",
      'cycle_day' => 26,
// not yet implemented in CiviBanking
//      'next_sched_contribution_date' => $source->next_sched_contribution_date,
      'financial_type_id' => $source->financial_type_id,
      'payment_instrument_id' => "RCUR",
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add recurring contribution to " . $this->_sourceData->contact_id);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }

    $this->recurId = $result['id'];
  }

  private function createMandateRcur($source) {
    $result = civicrm_api3('SepaMandate', 'create', [
      'reference' => $source->MndtId,
      'entity_table' => "civicrm_contribution_recur",
      'entity_id' => $this->recurId,
      'date' => $source->DtOfSgntr,
      'creditor_id' => 2,
      'contact_id' => $this->contactId,
      'account_holder' => $source->account_holder,
      'iban' => $source->iban,
      'type' => "RCUR",
      'status' => "RCUR",
      'creation_date' => $source->DtOfSgntr,
      'validation_date' => $source->DtOfSgntr,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add mandate from recurring contribution " . $this->$recurId . " to " . $this->_sourceData->contact_id);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

  private function createMandateOoff($source) {

    $result = civicrm_api3('SepaMandate', 'create', [
      'entity_table' => 'civicrm_contribution',
      'entity_id' => $source->id,
      'type' => 'OOFF',
      'status' => 'SENT',
      'contact_id' => $source->contact_id,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add mandate for contribution to " . $this->_sourceData->contact_id);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }
}
