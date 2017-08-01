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
    $this->getRecurId($source);
    $this->createMandate($this->recurId);
    
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
        ]);
      }
    }


    return TRUE;
  }

  private function createRecur($source) {

    $result = civicrm_api3('ContributionRecur', 'create', [
      'contact_id' => $this->contactId,
      'frequency_interval' => $source->frequency_interval,
      'financial_type_id' => $source->financial_type_id,
      'amount' => $source->amount,
      'payment_instrument_id' => $source->payment_instrument_id,
      'frequency_unit' => $source->frequency_unit,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add recurring contribution to " . $this->_sourceData->contact_id);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

  private function getRecurId($source) {

    $result = civicrm_api3('ContributionRecur', 'get', [
      'return' => ["id"],
      'contact_id' => $this->contactId,
      'frequency_interval' => $source->frequency_interval,
      'financial_type_id' => $source->financial_type_id,
      'amount' => $source->amount,
      'payment_instrument_id' => $source->payment_instrument_id,
      'frequency_unit' => $source->frequency_unit,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to get created recurring contribution from " . $this->_sourceData->contact_id);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }

    $this->recurId = $result['id'];
  }

  private function createMandate($recurId) {

    $result = civicrm_api3('SepaMandate', 'create', [
      'entity_table' => "civicrm_contribution_recur",
      'entity_id' => $recurId,
      'type' => "RCUR",
      'contact_id' => $this->contactId,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add mandate from recurring contribution " . $recurId . " to " . $this->_sourceData->contact_id);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

}
