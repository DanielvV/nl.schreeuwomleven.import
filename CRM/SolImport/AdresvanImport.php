<?php
/**
 *  process the Adresvan field in the solimport table
 *
 * @author Holland Open Source
 * @date 23-6-17 12:05
 * @license AGPL-3.0
 *
 */
class CRM_SolImport_AdresvanImport extends CRM_SolImport_AbstractImport {

  private $contactId;

  function process() {
    $Adresvan = $this->_sourceData->Adresvan;

    if (empty($Adresvan) || $Adresvan == 0) {
      return TRUE;
    }

    $this->contactId = $this->searchByExternalId($this->_sourceData->Contactnummer);

    if (empty($this->contactId)) {
      $this->_logger->logMessage('E', "could not identify a contact for " . $this->_sourceData->Contactnummer);
      return FALSE;
    }

    $id = $this->getAddressId($this->contactId);
    $master_id = $this->getAddressId($Adresvan);

    if (!empty($id) && !empty($master_id)) {
      $this->addAddressConnection($id, $master_id);
    }

    return TRUE;
  }

  private function getAddressId($contactId) {

    $result = civicrm_api3('Contact', 'get', [
      'return' => array("address_id"),
      'id' => $contactId,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add address connection from " . $addressMasterContactId . " to " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
    return $result['values'][$contactId]['address_id'];
  }

  private function addAddressConnection($id, $master_id) {

    $result = civicrm_api3('Address', 'create', [
      'contact_id' => $this->contactId,
      'location_type_id' => "Billing",
      'id' => $id,
      'master_id' => $master_id,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add address connection from " . $addressMasterContactId . " to " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

}

