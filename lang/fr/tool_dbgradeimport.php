<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'tool_dbgradeimport', language 'fr'.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



$string['courseid'] = 'ID du cours';
$string['dbencoding'] = 'Encodage de la base de données';
$string['dbhost'] = 'Adresse IP ou nom de domaine du serveur';
$string['dbhost_desc'] = 'Saisir l\'adresse IP ou le nom de domaine du serveur de base de données. Si vous utilisez ODBC, veuillez utiliser un entrée système DSN.';
$string['dbname'] = 'Nom de la base de données';
$string['dbname_desc'] = 'Ne pas renseigner si vous utiliser un nom DSN sur le serveur de base de données.';
$string['dbpass'] = 'Mot de passe de la base de données';
$string['dbsetupsql'] = 'Commande de configuration de la base de données';
$string['dbsetupsql_desc'] = 'Commande SQL pour une configuration particulière de la base de données. Une telle commande est souvent utilisée pour configurer l\'encodage de communication. Par exemple, pour MySQL et PostgreSQL : <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Utiliser le mode Sybase pour les apostrophes';
$string['dbsybasequoting_desc'] = 'Style Sybase pour l\'échappement des apostrophes. Ce réglage est nécessaire pour les bases de données Oracle, MS SQL et d\'autres types de base de données. Ne pas utiliser avec MySQL !';
$string['dbtype'] = 'Pilote de base de données';
$string['dbtype_desc'] = 'Nom du pilote de base de données ADOdb, type de moteur de base de données externe.';
$string['dbuser'] = 'Nom d\'utilisateur de la base de données';
$string['debugdb'] = 'Débogage ADOdb';
$string['debugdb_desc'] = 'Débogage de la connexion ADOdb vers la base de données externe. À utiliser lorsque vous obtenez une page blanche lors de la connexion à Moodle. Ne convient pas à un site en production !';
$string['externaltablenotspecified'] = 'Le nom de la table externe {$a} n\'a pas été spécifié.';
$string['externaltableempty'] = 'La table externe {$a} est vide.';
$string['externaltablecontains'] = 'La table externe {$a} contient les colonnes suivantes :';
$string['gradeitems'] = 'Éléments d\'évaluation';
$string['gradeitemsprefix'] = 'Préfixe des éléments d\'évaluation';
$string['gradeitemsprefix_desc'] = 'Ce préfixe sera inséré lors de la création des éléments d\'évaluation et sera utilisé lors de la synchronisation des évaluations.';
$string['ignorehiddencourses'] = 'Ignorer les cours cachés';
$string['ignorehiddencourses_desc'] = 'Si cette option est activée, les utilisateurs ne seront pas inscrits aux cours non disponibles aux étudiants.';
$string['localcoursefield'] = 'Champ du cours local';
$string['localcoursefield_desc'] = 'Le champ utilisé pour identifier le cours local.';
$string['localuserfield'] = 'Champ de l\'utilisateur local';
$string['localuserfield_desc'] = 'Le nom du champ utilisé pour identifier l\'utilisateur local.';
$string['pluginname'] = 'Importation des notes à partir d\'une base de données externe';
$string['pluginname_desc'] = 'Vous pouvez utiliser une base de données externe (de presque n\'importe quel type) afin d\'importer des éléments d\'évaluation dans vos cours. On suppose que la base de données externe comporte deux tables. L\'une pour créer les éléments d\'évaluations et l\'autre pour insérer les évaluations des étudiants.';
$string['remotegradeitemscoursefield'] = 'Champ distant du cours des éléments d\'évaluation';
$string['remotegradeitemscoursefield_desc'] = 'Le champ distant utilisé pour identifier le cours contenant les éléments d\'évaluation.';
$string['remotegradeitemsnamefield'] = 'Champ distant du nom des éléments d\'évaluation';
$string['remotegradeitemsnamefield_desc'] = 'Le champ distant utilisé pour remplir le nom des éléments d\'évaluation.';
$string['remotegradeitemsfield'] = 'Champ distant des éléments d\'évaluation';
$string['remotegradeitemsfield_desc'] = 'Le nom du champ distant utilisé pour remplir le numéro d\'identification des éléments d\'évaluation.';
$string['remotegradeitemstable'] = 'Nom de la table distante des éléments d\'évaluation';
$string['remotegradeitemstable_desc'] = 'Le nom de la table distante utilisée pour créer les éléments d\'évaluation. Les éléments d\'évaluation déjà existants ne seront pas mis à jour. Aucun élément d\'évaluation ne sera créé si ce champ est laissé vide.';
$string['remotegradescoursefield'] = 'Champ distant du cours des évaluations';
$string['remotegradescoursefield_desc'] = 'Le nom du champ distant utilisé pour identifier le cours contenant les évaluations.';
$string['remotegradesfield'] = 'Champ distant des éléments d\'évaluation';
$string['remotegradesfield_desc'] = 'Le nom du champ distant utiliser pour identifier les éléments d\'évaluation basé sur leur numéro d\'identification.';
$string['remotegradestable'] = 'Nom de la table distante des évaluations';
$string['remotegradestable_desc'] = 'Le nom de la table distante utilisée pour insérer les évaluations. Aucun élément d\'évaluation ne sera inséré si ce champ est laissé vide.';
$string['remotegradesuserfield'] = 'Champ distant de l\'utilisateur évalué';
$string['remotegradesuserfield_desc'] = 'Le nom du champ utilisé pour identifier l\'utilisateur évalué.';
$string['remotegradesvaluefield'] = 'Champ distant de la valeur de l\'évaluation';
$string['remotegradesvaluefield_desc'] = 'Le nom du champ contenant les évaluations.';
$string['remotemaxgradefield'] = 'Champ distant de la note maximale';
$string['remotemaxgradefield_desc'] = 'Le nom du champ distant utilisé pour insérer la note maximale des éléments d\'évaluation.';
$string['settingsheaderdb'] = 'Connexion de la base de données externe';
$string['settingsheaderlocal'] = 'Correspondance des champs locaux';
$string['settingsheaderremotegrade'] = 'Synchronisation des évaluations';
$string['settingsheaderremotegradeitem'] = 'Création des éléments d\'évaluation';
$string['testsettings'] = 'Tester les paramètres';
$string['userid'] = 'ID utilisateur';
$string['usergrades'] = 'Notes des utilisateurs';
