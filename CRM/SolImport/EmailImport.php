<?php
/**
 *  process the Emailadressen field in the solimport table
 *
 * @author Holland Open Source
 * @date 21-6-17 17:27
 * @license AGPL-3.0
 *
 */
class CRM_SolImport_EmailImport extends CRM_SolImport_AbstractImport {

  private $contactId;

  function process() {
    $Emailadressen = $this->_sourceData->Emailadressen;
    
    $this->contactId = $this->searchByExternalId($this->_sourceData->Contactnummer);

    if (empty($this->contactId)) {
      $this->_logger->logMessage('E', "could not identify a contact for " . $this->_sourceData->Contactnummer);
      return FALSE;
    }

    $mailadressen = explode(',', $Emailadressen);
    foreach ($mailadressen as $mailadres) {
      if (empty($mailadres)) {
        return TRUE;
      }
      $this->addMail($mailadres);
    }

    return TRUE;
  }

  private function addMail($mailadres) {

    $result = civicrm_api3('Email', 'create', [
      'contact_id' => $this->contactId,
      'email' => $mailadres,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add " . $mailadres . " to " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

}
