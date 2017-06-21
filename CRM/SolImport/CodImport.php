<?php
/**
 *  process the cod field in the solimport table
 *
 * @author Klaas Eikelbooml (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @date 20-6-17 11:34
 * @license AGPL-3.0
 *
 */
class CRM_SolImport_CodImport extends CRM_SolImport_AbstractImport {

  private $contactId;

  function process() {
    $cod = $this->_sourceData->cod;

    $this->contactId = $this->searchByExternalId($this->_sourceData->Contactnummer);

    if (empty($this->contactId)) {
      $this->_logger->logMessage('E', "could not identify a contact for " . $this->_sourceData->Contactnummer);
      return FALSE;
    }

    $config = CRM_SolImport_Config::singleton();

    $codes = str_split($cod, 3);

    foreach ($codes as $code) {
      switch ($codes) {
        case 'SYM':
        case 'SY1':
        case 'REL':
          $this->addGroup($code);
          break;
        case 'AGE':
          $this->setOptOut(true);
          break;
      }
    }

    return TRUE;
  }

  private function addGroup($code) {

    $result = civicrm_api3('GroupContact', 'create', [
      'group_id' => $config->getGroupId($code),
      'contact_id' => $contactId,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add " . $code . " code to " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

  private function setOptOut($is_opt_out) {

    $result = civicrm_api3('Contact', 'create', [
      'id' => $contactId,
      'is_opt_out' => $is_opt_out,
    ]);

    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable add opt out code to  $this->_sourceData->Contactnummer");
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }  
  }

}
