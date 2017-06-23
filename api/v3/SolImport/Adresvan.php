<?php
/**
 * Doel verwerken van de column 'Adresvan' vanuit de laad tabel
 * De api verzorgt de afhandeling van de laad tabel
 * Dat is
 *  - aanroepbaar maken van buiten CiviCRM
 *  - defentitie van de log file
 *  - het ophalen van de te verwerken records uit de laadtabel
 *  - goed verwerkte records op P
 *  - uitval (records die om een op andere reden niet verwerkt kunnen worden op F zetten
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function civicrm_api3_sol_import_Adresvan($params) {
  set_time_limit(0);
  // gevolg geen time_out op dit script - dat past ook bij data verwerking
  // maar roep het bij voorkeur niet vanuit de webinterface aan
  if(!isset($params['limit'])){
    $params['limit']=1000;
  }
  // met de limit parameter kan het aantal record worden bepaald
  // indien niet opgegeven dan worden er duizend verwerkt.
  $element = "Adresvan";
  $returnValues = array();
  $createCount = 0;  // aantal succesvol verwerkte records
  $logCount = 0;     // niet verwerkte records - deze geven F in de tabel en een schrijven een record weg in de logfile
  $logger = new CRM_SolImport_Logger($element);
  $daoSource = CRM_Core_DAO::executeQuery("SELECT * FROM sol_import WHERE status = 'N' ORDER BY Contactnummer LIMIT %1", array(
     1 => array($params['limit'],'Integer')
  ));
  // hier worden te te verwerken records opgehaalt.
  while ($daoSource->fetch()) {
    $adresvanImport = new CRM_SolImport_AdresvanImport($element, $daoSource, $logger);
    $result = $adresvanImport->process();
    if ($result == FALSE) {
      $updateQuery = 'UPDATE sol_import SET status = %1 WHERE Contactnummer = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array('F', 'String'), // Failed
        2 => array($daoSource->Contactnummer, 'String'),));
      $logCount++;
    } else {
      $createCount++;
      $updateQuery = 'UPDATE sol_import SET status = %1  WHERE Contactnummer = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array('P', 'String'), // Processed
        2 => array($daoSource->Contactnummer, 'String'),));
    }
  }
  /*
   *  mocht de select geen resultaat opleveren meldt dit ook
   */
  if (empty($daoSource->N)) {
    $returnValues[] = 'Nothing to import - all Adresvan connections are created';
  } else {
    $returnValues[] = $createCount.' rows import to CiviCRM, '.$logCount.' with logged errors that were not imported';
  }
  // deze api vind zichtzelf altijd succesvol
  // dat kan omdat hij zijn fouten kwijtkan in de log file
  return civicrm_api3_create_success($returnValues, $params, 'ImportSol', 'Adresvan');
}
