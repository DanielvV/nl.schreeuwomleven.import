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

  function process() {
    $cod = $this->_sourceData->cod;

    $contactId = $this->searchByExternalId($this->_sourceData->Contactnummer);

    if (empty($contactId)) {
      $this->_logger->logMessage('E', "could not identify a contact for  $this->_sourceData->Contactnummer");
      return FALSE;
    }

    $config = CRM_SolImport_Config::singleton();

    $codes = str_split($cod, 3);

    if (in_array('SYM', $codes)) {
      $result = civicrm_api3('GroupContact', 'create', [
        'group_id' => $config->getSymGroupId(),
        'contact_id' => $contactId,
      ]);
      if ($result['is_error']) {
        $this->_logger->logMessage('E', "unable to add SYM code to   $this->_sourceData->Contactnummer");
        $this->_logger->logMessage('E', print_r($result, TRUE));
        return FALSE;
      }

    }

    if (in_array('SY1', $codes)) {
      $result = civicrm_api3('GroupContact', 'create', [
        'group_id' => $config->getSy1GroupId(),
        'contact_id' => $contactId,
      ]);
      if ($result['is_error']) {
        $this->_logger->logMessage('E', "unable to add SY1 code to   $this->_sourceData->Contactnummer");
        $this->_logger->logMessage('E', print_r($result, TRUE));
        return FALSE;
      }
    }

    if (in_array('REL', $codes)) {
      $result = civicrm_api3('GroupContact', 'create', [
        'group_id' => $config->getRelGroupId(),
        'contact_id' => $contactId,
      ]);

      if ($result['is_error']) {
        $this->_logger->logMessage('E', "unable to add RELT code to   $this->_sourceData->Contactnummer");
        $this->_logger->logMessage('E', print_r($result, TRUE));
        return FALSE;
      }
    }

    $is_opt_out = (in_array('AGE', $codes)) ? 1 : 0;

    $result = civicrm_api3('Contact', 'create', [
      'id' => $contactId,
      'is_opt_out' => $is_opt_out,
    ]);

    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable add opt out code to  $this->_sourceData->Contactnummer");
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }

    return TRUE;
  }

}