<?php
/**
 *  Voor extensie specifieke configuratie. Maakt gebruik van het Singleton
 * Pattern
 *
 * @author Klaas Eikelbooml (CiviCooP) <klaas.eikelboom@civicoop.org>
 * @date 20-6-17 13:28
 * @license AGPL-3.0
 *
 */
class CRM_SolImport_Config {

  private static $_singleton;

  /* Bij de eerste aanroep van deze class worden de onderstaande ids opgezocht.
     Deze kunnen later geraadpleegt worden.
  */

  private $_symGroupId;

  private $_sy1GroupId;

  private $_relGroupId;

  /**
   * CRM_SolImport_Config constructor.
   */
  public function __construct() {

    /* opzoeken kan uitstekend gedaan worden met de api
       - getsingle zorgt ervoor dat het om een enkele rij gaat
       - met return wordt aangegeven welke column uit de api call
         als resultaat waarde wordt gebruikt
    */
    $this->_symGroupId = civicrm_api3('Group', 'getsingle', [
      'return' => ["id"],
      'title' => "Sympathisant Frequent",
    ]);

    $this->_sy1GroupId = civicrm_api3('Group', 'getsingle', [
      'return' => ["id"],
      'title' => "Sympathisant Jaarlijks",
    ]);

    $this->_relGroupId = civicrm_api3('Group', 'getsingle', [
      'return' => ["id"],
      'title' => "Relatie",
    ]);
  }

  /**
   * @return array
   */
  public function getSymGroupId() {
    return $this->_symGroupId;
  }

  /**
   * @return array
   */
  public function getSy1GroupId() {
    return $this->_sy1GroupId;
  }

  /**
   * @return array
   */
  public function getRelGroupId() {
    return $this->_relGroupId;
  }

  /**
   * Singleton method
   *
   * @return CRM_Migration_Config
   * @access public
   * @static
   */
  public static function singleton() {
    /* Het signleton pattern is een methode die er
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