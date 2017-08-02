<?php
/**
 * Doel verwerken van alle incassos uit de laad tabel 'sol_import_incasso'
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
function civicrm_api3_sol_import_Incasso($params) {
  set_time_limit(0);
  // gevolg geen time_out op dit script - dat past ook bij data verwerking
  // maar roep het bij voorkeur niet vanuit de webinterface aan
  if(!isset($params['limit'])){
    $params['limit']=1000;
  }
  // met de limit parameter kan het aantal record worden bepaald
  // indien niet opgegeven dan worden er duizend verwerkt.
  $element = "Incasso";
  $returnValues = array();
  $createCount = 0;  // aantal succesvol verwerkte records
  $logCount = 0;     // niet verwerkte records - deze geven F in de tabel en een schrijven een record weg in de logfile
  $logger = new CRM_SolImport_Logger($element);
  $daoSource = CRM_Core_DAO::executeQuery("SELECT * FROM sol_import_incasso WHERE status = 'N' ORDER BY id LIMIT %1", array(
     1 => array($params['limit'],'Integer')
  ));
  // hier worden te te verwerken records opgehaalt.
  while ($daoSource->fetch()) {
    $incassoImport = new CRM_SolImport_IncassoImport($element, $daoSource, $logger);
    $result = $incassoImport->process();
    if ($result == FALSE) {
      $updateQuery = 'UPDATE sol_import_incasso SET status = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array('F', 'String'), // Failed
        2 => array($daoSource->id, 'String'),));
      $logCount++;
    } else {
      $createCount++;
      $updateQuery = 'UPDATE sol_import_incasso SET status = %1  WHERE id = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array('P', 'String'), // Processed
        2 => array($daoSource->id, 'String'),));
    }
  }
  /*
   *  mocht de select geen resultaat opleveren meldt dit ook
   */
  if (empty($daoSource->N)) {
    $returnValues[] = 'Nothing to import - all incasso rows are done';
  } else {
    $returnValues[] = $createCount.' rows import to CiviCRM, '.$logCount.' with logged errors that were not imported';
    /*
     *  doe nog wat extra
     */
    $daoOptionValue = CRM_Core_DAO::executeQuery("SELECT value FROM `civicrm_option_value` WHERE name = 'OOFF'", array());
    $daoOptionValue->fetch();
    // Voor elk record in civicrm_contribution waar civicrm_contribution.payment_instrument_id = OOFF
    $extraDaoSource = CRM_Core_DAO::executeQuery("SELECT id, contact_id FROM `civicrm_contribution` WHERE payment_instrument_id = %1 ORDER BY id", array(
      1 => array($daoOptionValue->value,'Integer')
    ));
    while ($extraDaoSource->fetch()) {
      // -> Maak een mandaat aan
      $extraIncassoImport = new CRM_SolImport_IncassoImport($element, $daoSource, $logger);
      $result = $incassoImport->processOneOff();
    }
  }
  // deze api vind zichtzelf altijd succesvol
  // dat kan omdat hij zijn fouten kwijtkan in de log file
  return civicrm_api3_create_success($returnValues, $params, 'ImportSol', 'Incasso');
}
