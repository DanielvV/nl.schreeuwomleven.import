<?php
/**
 *  Voor extensie specifieke configuratie. Maakt gebruik van het Singleton
 * Pattern
 *
 * @author Klaas Eikelboom (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @date 20-6-17 13:28
 * @license AGPL-3.0
 *
 */
class CRM_SolImport_Config {

  private static $_singleton;

  /* Bij de eerste aanroep van deze class worden de onderstaande ids opgezocht.
     Deze kunnen later geraadpleegt worden.
  */

  private $_GroupIds;

  /**
   * CRM_SolImport_Config constructor.
   */
  public function __construct() {

    $this->_groupIds['SYM'] = $this->getGroupIdFromName("Sympathisant Frequent");
    $this->_groupIds['SY1'] = $this->getGroupIdFromName("Sympathisant Jaarlijks");
    $this->_groupIds['REL'] = $this->getGroupIdFromName("Relatie");
    $this->_groupIds['BED'] = $this->getGroupIdFromName("Bedrijf (fondswerving)");
    $this->_groupIds['BRS'] = $this->getGroupIdFromName("Beurs vrijwilliger");
    $this->_groupIds['HUB'] = $this->getGroupIdFromName("Hulpverlening Buddy/Counselor");
    $this->_groupIds['HUO'] = $this->getGroupIdFromName("Hulpverlening Overig");
    $this->_groupIds['HUL'] = $this->getGroupIdFromName("Hulpverlening");
    $this->_groupIds['ORD'] = $this->getGroupIdFromName("Bij Mars inzetbaar");
    $this->_groupIds['PER'] = $this->getGroupIdFromName("Pers Nederland");
    $this->_groupIds['WAK'] = $this->getGroupIdFromName("Dagwake");
  }

 /**
   * @return array
   */
  private function getGroupIdFromName($name) {

    /* opzoeken kan uitstekend gedaan worden met de api
       - getsingle zorgt ervoor dat het om een enkele rij gaat
       - met return wordt aangegeven welke column uit de api call
         als resultaat waarde wordt gebruikt
    */

    return civicrm_api3('Group', 'getsingle', [
      'return' => ["id"],
      'title' => $name,
    ]);
  }

  /**
   * @return array
   */
  public function getGroupId($code) {
    return $this->_groupIds[$code];
  }

  /**
   * Singleton method
   *
   * @return CRM_Migration_Config
   * @access public
   * @static
   */
  public static function singleton() {
    /* Het singleton pattern is een methode die er
       voor zorgt dat van een classe maar
       één instantie wordt aangemaakt. Dat is bij uitstek
       geschikt voor configuratie omdat die overal hetzelfde moet
       zijn.
    */
    if (!self::$_singleton) {
      self::$_singleton = new CRM_SolImport_Config();
    }
    return self::$_singleton;
  }

}
