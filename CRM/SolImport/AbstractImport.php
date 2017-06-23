<?php
/**
 *  Bevat alle functies die de verschillende Import classes met elkaar delen.
 *
 * @author Klaas Eikelbooml (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @date 19-6-17 16:01
 * @license AGPL-3.0
 **/
abstract class CRM_SolImport_AbstractImport {

  protected $_logger = NULL;

  protected $_sourceData = [];

  protected $_element = NULL;

  /**
   * CRM_Migratie_ForumZfd constructor.
   *
   * @param string $element
   * @param object $sourceData
   * @param object $logger
   *
   * @throws Exception when entity invalid
   */
  public function __construct($element, $sourceData = NULL, $logger = NULL) {
    $element = strtolower($element);
    if (!$this->elementCanBeImported($element)) {
      throw new Exception('Element ' . $element . ' can not be imported.');
    }
    else {
      /* hier worden de variabelen van de class gezet
         ze kunnen in de subclass geraadpleegd worden */
      $this->_element = $element;
      $this->_sourceData = $sourceData;
      $this->_logger = $logger;
    }
  }

  /**
   *  zoek het contact id op aan de hand van de external_identifier. Mocht er
   * niets gevonden worden dan is het resultaat null.
   *
   * @param $external code
   *
   * @return null|string
   */
  protected function searchByExternalId($external) {
    /* we raadplegen hier direct de database (en maken geen gebruik van de api
       Reden: api code maakt de code niet leesbaarder.
              direct raadplegen is wat sneller.
              als de gegevens niet aangpast worden, doet de api ook minder
    */
    $contactId = CRM_Core_DAO::singleValueQuery('SELECT id FROM civicrm_contact WHERE external_identifier = %1', [
        '1' => [$external, 'String'],
      ]);
    return $contactId;
  }

  /* controle die voorkomt dat als er een typefout gemaakt wordt in een element dit tot onbegrijpelijke fouten leidt */
  private function elementCanBeImported($element) {
    if ($element == 'cod') {
      return TRUE;
    }
    elseif ($element == 'email') {
      return TRUE;
    }
    elseif ($element == 'addressvan') {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}
