#Import 

Importeert (tot nu toe) één element vanuit een laadtabel in CiviCRM met behulp van de api.
Kan als voorbeeld gebruikt worden om uit te breiden om zo de import compleet te maken.

### Structuur laad tabel 
De structuur van de laad tabel is te vinden in de file 
[solimport_install.sql](sql/solimport_install.sql). Bij het installeren wordt deze tabel aangemaakt.

### Gebruik
Niet alleen maakt deze extensie een api, hij breidt hem ook uit. Aanroepen
kan met drush

    drush cvapi SolImport.cod

Het aantal te verwerken rijen is normaal 1000, maar kan aangepast worden met de limit parameter:

    drush cvapi SolImport.cod limit=21