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

    $this->_groupIds['BRS'] = $this->getGroupIdFromName("Beursmedewerkers");
    $this->_groupIds['ORD'] = $this->getGroupIdFromName("Marsmedewerkers (ordedienst en ehbo)");
    $this->_groupIds['WAK'] = $this->getGroupIdFromName("Wakers");

    $this->_groupIds['HUL'] = $this->getGroupIdFromName("Hulpverleners");
    $this->_groupIds['HUB'] = $this->getGroupIdFromName("Buddy's en Counselors");
    $this->_groupIds['HUO'] = $this->getGroupIdFromName("Speciale relaties hulpverlening");

    $this->_groupIds['SYE'] = $this->getGroupIdFromName("Leef per email (6x per jaar)");
    $this->_groupIds['SSE'] = $this->getGroupIdFromName("Leef per email (6x per jaar)");
    $this->_groupIds['SYH'] = $this->getGroupIdFromName("Leef per email (6x per jaar)");
    $this->_groupIds['SYM'] = $this->getGroupIdFromName("Leef per post (6x per jaar)");
    $this->_groupIds['SY1'] = $this->getGroupIdFromName("Leef per post (1x per jaar)");
    $this->_groupIds['SYA'] = $this->getGroupIdFromName("Leef per post (1x per jaar)");

    $this->_groupIds['ART'] = $this->getGroupIdFromName("Artsen");
    $this->_groupIds['BED'] = $this->getGroupIdFromName("Bedrijven");
    $this->_groupIds['PRM'] = $this->getGroupIdFromName("Buitenlanders")
    $this->_groupIds['CRE'] = $this->getGroupIdFromName("Crediteuren");
    $this->_groupIds['KER'] = $this->getGroupIdFromName("Kerken");
    $this->_groupIds['KDI'] = $this->getGroupIdFromName("Kerken");
    $this->_groupIds['KOR'] = $this->getGroupIdFromName("Kerken");
    $this->_groupIds['KVG'] = $this->getGroupIdFromName("Kerken");
    $this->_groupIds['REL'] = $this->getGroupIdFromName("Nederlanders");
    $this->_groupIds['PER'] = $this->getGroupIdFromName("Pers");
    $this->_groupIds['TST'] = $this->getGroupIdFromName("Testadressen");
    $this->_groupIds['ZAK'] = $this->getGroupIdFromName("Zakelijke adressen");
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
      'title' => "$name",
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
