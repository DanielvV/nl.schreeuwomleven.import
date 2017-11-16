<?php
/**
 *  process the cod field in the solimport table
 *
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @date 20-6-17 11:34
 * @license AGPL-3.0
 *
 */
class CRM_SolImport_CodImport extends CRM_SolImport_AbstractImport {

  private $contactId;
  private $config;

  function process() {
    $cod = $this->_sourceData->cod;

    $this->contactId = $this->searchByExternalId($this->_sourceData->Contactnummer);

    if (empty($this->contactId)) {
      $this->_logger->logMessage('E', "could not identify a contact for " . $this->_sourceData->Contactnummer);
      return FALSE;
    }

    $this->config = CRM_SolImport_Config::singleton();

    $codes = str_split($cod, 3);

    foreach ($codes as $code) {
      $this->addGroup($code);

      switch ($code) {
        case 'ART':
          $this->addGroup('SYM');
          break;
        case 'BED':
          $this->addGroup('SY1');
          break;
        case 'KDI':
        case 'KER':
        case 'KOR':
        case 'KVG':
          $this->addGroup('SYE');
          break;
        case 'PER':
          $this->addGroup('SYM');
          $this->addGroup('SYE');
          break;
        case 'K00':
        case 'K01':
        case 'K02':
        case 'K03':
        case 'K04':
        case 'K05':
        case 'K06':
        case 'K07':
        case 'K08':
        case 'K09':
        case 'K10':
        case 'K11':
        case 'K12':
        case 'K13':
        case 'K14':
        case 'K15':
        case 'K16':
        case 'K17':
        case 'K18':
        case 'K19':
        case 'K20':
        case 'K21':
        case 'K22':
        case 'K23':
        case 'K24':
        case 'K25':
        case 'K26':
          $this->addNote($code, 'Kerkelijke richting');
          break;
      }
    }

    foreach ($codes as $code) {
      switch ($code) {
        case 'SYM':
          $this->removeGroup('SY1');
          $this->addGroup('SYE');
          break 2;
        case 'SY1':
        case 'SYA':
          $this->removeGroup('SYM');
          $this->addGroup('SYE');
          break 2;
        case 'SSE':
        case 'SYE':
        case 'SYH':
          $this->removeGroup('SYM');
          $this->removeGroup('SY1');
          break 2;
      }
    }

    foreach ($codes as $code) {
      switch ($code) {
        case 'AGE':
        // Niet in groepen Leef per e-mail (6x per jaar)
          $this->removeGroup('SYE');
          $this->mailToNote('Geen e-mail naar');
          break;
        case 'AOE':
        // Alle e-mailadressen verwijderen bij contact
          $this->mailToNote('Retour van');
          break;
        case 'ADU':
        // Niet in groepen Leef per post (6x per jaar) en Leef per post (1x per jaar)
        // Niet in groepen Leef per e-mail (6x per jaar)
          $this->removeGroup('SYM');
          $this->removeGroup('SY1');
          $this->removeGroup('SYE');
          break;
        case 'AGP':
        // Niet in groepen Leef per post (6x per jaar) en Leef per post (1x per jaar)
          $this->removeGroup('SYM');
          $this->removeGroup('SY1');
          break;
        case 'AON':
        // Alle postadressen verwijderen bij contact
          $this->addressToNote('Onbekend adres');
          break;
        case 'AOV':
        // Overleden
          $this->setDeceased(true);
          $this->mailToNote('Overleden');
          break;
      }
    }

    return TRUE;
  }

  private function addGroup($code) {
  // add corresponding group from code if the code exists in the config

    $groupId = $this->config->getGroupId($code);
    If (empty($groupId)) {
      return TRUE;
    }
    $result = civicrm_api3('GroupContact', 'create', [
      'group_id' => $groupId,
      'contact_id' => $this->contactId,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add " . $code . " code to " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

  private function removeGroup($code) {
  // set corresponding group status to Removed if the code exists in the config and the group exists on the contact

    $groupId = $this->config->getGroupId($code);
    If (empty($groupId)) {
      return TRUE;
    }

    $result = civicrm_api3('GroupContact', 'get', [
      'group_id' => $groupId,
      'contact_id' => $this->contactId,
    ]);

    if ($result['count']) {
      $result = civicrm_api3('GroupContact', 'create', [
        'group_id' => $groupId,
        'contact_id' => $this->contactId,
        'status' => "Removed",
      ]);
    }

    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to remove " . $code . " code from " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

  private function addNote($note, $subject) {
  // add note to existing contact

    $result = civicrm_api3('Note', 'create', [
      'entity_table' => "civicrm_contact",
      'entity_id' => $this->contactId,
      'note' => $note,
      'subject' => $subject,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable to add note to " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }
  }

  private function mailToNote($subject) {
  // remove e-mail and add it to a note

    $result = civicrm_api3('Email', 'get', [
      'contact_id' => $this->contactId,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable get an e-mailaddress from " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }

    foreach ($result['values'] as $mailId => $mail) {
      $this->addNote($mail['email'], $subject);

      $result = civicrm_api3('Email', 'delete', [
        'id' => $mailId,
      ]);
      if ($result['is_error']) {
        $this->_logger->logMessage('E', "unable to delete e-mailaddress from " . $this->_sourceData->Contactnummer);
        $this->_logger->logMessage('E', print_r($result, TRUE));
        return FALSE;
      }
    }
  }

  private function addressToNote($subject) {
  // remove address and add it to a note

    $result = civicrm_api3('Address', 'get', [
      'contact_id' => $this->contactId,
    ]);
    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable get an address from " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }

    foreach ($result['values'] as $addressId => $address) {
      $this->addNote($address['street_address'] . ', ' . $address['postal_code'] . '  ' . $address['city'], $subject);

      $result = civicrm_api3('Address', 'delete', [
        'id' => $addressId,
      ]);
      if ($result['is_error']) {
        $this->_logger->logMessage('E', "unable to delete address from " . $this->_sourceData->Contactnummer);
        $this->_logger->logMessage('E', print_r($result, TRUE));
        return FALSE;
      }
    }
  }

  private function setDeceased($is_deceased) {
  // people die

    $result = civicrm_api3('Contact', 'create', [
      'id' => $this->contactId,
      'is_deceased' => $is_deceased,
    ]);

    if ($result['is_error']) {
      $this->_logger->logMessage('E', "unable change is_deceased to " . $is_deceased . " for " . $this->_sourceData->Contactnummer);
      $this->_logger->logMessage('E', print_r($result, TRUE));
      return FALSE;
    }  
  }

}
