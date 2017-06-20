<?php
/**
 *  Schrijft een log file weg. Hier kunnen records in worden opgezocht die
 *  niet verwerkt konden worden (die z.g uitgevallen zijn).
 *
 *  @author Klaas Eikelbooml (CiviCooP) <klaas.eikelboom@civicoop.org>
 *  @date 19-6-17 15:11
 *  @license AGPL-3.0
 *
 */
class CRM_SolImport_Logger {

  private $_logFile = null;
  /**
   * CRM_Solmport_Logger constructor.
   *
   * @param string $element
   */
  function __construct($element = '') {
    if (empty($element)) {
      $fileName = 'solimport';
    } else {
      $fileName = 'solimport_' . $element;
    }
    $config = CRM_Core_Config::singleton();
    $runDate = new DateTime('now');

    /* De configAndLogDirectory bevind zich meestal in ../default/files/civicrm/ConfigAndLogDir
       Reden om hier naartoe te schrijven is hier zich ook de andere log bestanden van
       CiviCRM bevinden.
    */
    $fileName = $config->configAndLogDir.$fileName."_".$runDate->format('YmdHis').'.log';
    $this->_logFile = fopen($fileName, 'w');
  }
  /**
   * Method to add message to logger
   *
   * @param $type
   * @param $message
   */
  public function logMessage($type, $message) {
    $this->addMessage($type, $message);
  }
  /**
   * Method to log the message
   *
   * @param $type
   * @param $message
   */
  private function addMessage($type, $message) {
    fputs($this->_logFile, date('Y-m-d h:i:s'));
    fputs($this->_logFile, ' ');
    fputs($this->_logFile, $type);
    fputs($this->_logFile, ' ');
    fputs($this->_logFile, $message);
    fputs($this->_logFile, "\n");
  }
}

