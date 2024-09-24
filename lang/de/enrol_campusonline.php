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
 * Version metadata for the enrol_campusonline plugin.
 *
 * @package   enrol_campusonline
 * @copyright 2024, Lucas Reeh <lr86gm@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Basics.
$string['pluginname'] = 'CAMPUSonline enrolment';
$string['privacy:metadata'] = 'Das CAMPUSonline-Einschreibungs-Plugin speichert keine persönlichen Daten.';
$string['task:sync'] = 'CAMPUSonline-Kurse & Einschreibungen VOLLSynchronisation';
$string['task:sync_delta'] = 'CAMPUSonline-Kurse & Einschreibungen MODIFIKATIONSSynchronisation';
$string['task:user_sync'] = 'CAMPUSonline-Benutzersynchronisation';

$string['allevents'] = 'Alle Ereignisse';
$string['allowemailupdate'] = 'Erlaube Benutzersynchronisation, um E-Mail-Adressen zu aktualisieren';
$string['allowemailupdate_desc'] = 'Erlaubt der Benutzersynchronisation, die E-Mail-Adressen bestehender Benutzer zu ändern. Beachten Sie, dass dies zu Problemen führen kann, da auf einigen Moodle-Websites Benutzer ihre E-Mail-Adresse zum Einloggen verwenden.';
$string['authmethod'] = 'Authentifizierungsmethode';
$string['availabletokens'] = 'Verfügbare Tokens';
$string['availabletokens_disclaimer'] = 'Einige davon könnten nur für Mitarbeiter oder Studierende verfügbar sein, aber nicht für beide.';
$string['backtosettings'] = 'Zurück zu den Moduleinstellungen';
$string['clientid'] = 'Client-ID';
$string['clientid_desc'] = 'Client-ID für den Zugriff auf CAMPUSonline';
$string['clientsecret'] = 'Client-Secret';
$string['clientsecret_desc'] = 'Geheimer Schlüssel für den Zugriff auf CAMPUSonline';
$string['configuretask'] = 'Geplante Aufgabe konfigurieren';
$string['configuretask_delta'] = 'Geplante Aufgabe für MODIFIKATIONSSynchronisation konfigurieren';
$string['configuretask_full'] = 'Geplante Aufgabe für VOLLSynchronisation konfigurieren';
$string['connectionerror'] = 'Keine Verbindung zu CAMPUSOnline möglich. Überprüfen Sie Ihre Verbindungseinstellungen. Bitte kontaktieren Sie Ihren Administrator.';
$string['connectionsettings'] = 'Verbindung';
$string['coursecatsettings'] = 'Kurskategorie';
$string['coursecatsettings_desc'] = '<ul>
    <li>Die Kurskategoriebäume können mithilfe von Werten aus CAMPUSonline als Tokens erstellt werden</li>
    <li>Wenn sich die resultierende Kurskategorie für einen aktiv synchronisierten Kurs ändert, wird der Kurs verschoben</li></ul>';
$string['coursecount'] = 'Rohdaten für {$a} Kurse:';
$string['coursepreview'] = 'Kurs-Synchronisationsvorschau';
$string['coursesyncsettings'] = 'Kurswerte';
$string['coursesyncsettings_desc'] = '<ul>
    <li>Die Moodle-Kurs-<strong>Id-Nummer</strong> wird immer mit der CAMPUSonline-Kurs-<strong>uid</strong> ausgefüllt</li>
    <li>Stellen Sie sicher, dass die Kurs-<strong>Kurznamen</strong> eindeutig sind und Felder mit gültigen Werten für ihre jeweiligen Feldtypen ausgefüllt sind, da sonst Fehler bei der Kurserstellung auftreten können!</li>
    <li>Diese Werte sind <strong>erforderlich</strong>, sonst schlägt die Kurserstellung fehl: course_fullname, course_shortname, course_format</li>
    <li>Wählen Sie Werte für andere Kursfelder (einschließlich benutzerdefinierter Kursfelder) durch Kombination von Text und <strong>Tokens</strong> für CAMPUSonline-Felder, z.B.: "CAMPUSONLINE_COURSE_{title}</li>
    <li>Zeigen Sie Rohdaten aus CAMPUSonline an, um verfügbare Felder/Tokens zu sehen</li></ul>';
$string['createcoursecatetories'] = 'Kurskategorien erstellen';
$string['createcoursecatetories_desc'] = 'Erlaubt der Einschreibungssynchronisation, Kurskategorien zu erstellen, wenn sie nicht existieren.';
$string['deletedcourse'] = 'Gelöschter Kurs (ID: {$a})';
$string['donotsyncrole'] = '- Diese Rolle nicht synchronisieren -';
$string['endpoint'] = 'CAMPUSonline-Endpunkt';
$string['endpoint_desc'] = 'Adresse des CAMPUSonline OAuth2-Endpunkts';
$string['enrolmentsyncsettings'] = 'Kurs- und Einschreibungssynchronisation';
$string['enrolmentsyncsettings_desc'] = '<ul>
    <li>Diese Synchronisationsaufgaben erstellen und aktualisieren <strong>Kurse</strong> und deren <strong>Einschreibungen</strong></li>
    <li>Es wird empfohlen, eine <strong>Vollsynchronisation</strong> manuell oder wöchentlich durchzuführen und nur die <strong>Modifikationssynchronisation</strong> zu planen, um die Leistung zu verbessern.</li>
    <li>Es gibt zwei Synchronisationsaufgaben: eine für <strong>vollständige</strong> Synchronisation und eine nur für <strong>Modifikationen</strong></li>
    <li>Es wird empfohlen, die vollständige Synchronisationsaufgabe nachts auszuführen, da sie ziemlich lange dauern kann</li>
    <li>Die Modifikationssynchronisationsaufgabe kann häufiger geplant oder bei Bedarf manuell ausgeführt werden. Standardmäßig ist sie nicht geplant.</li>
    <li>Zusätzlich können Sie einen einzelnen Kurs synchronisieren, indem Sie die Schaltfläche "Kurs mit CAMPUSonline synchronisieren" auf der Kurs-Teilnehmerseite verwenden (nur für Kurse verfügbar, die über CAMPUSonline erstellt wurden, und erfordert die Berechtigung enrol/campusonline:synccourse)</li></ul>';
$string['enrolsynccreateusers'] = 'Benutzer erstellen';
$string['enrolsynccreateusers_desc'] = 'Erlaubt der Einschreibungssynchronisation, Benutzer zu erstellen, die in Moodle nicht existieren oder nicht gefunden werden können. <strong>Aktivieren Sie dies nur, nachdem Sie sichergestellt haben, dass die Benutzeridentifikation korrekt funktioniert</strong>, andernfalls könnten viele doppelte Benutzer in Moodle entstehen!';
$string['error:cannotconnect'] = 'Keine Verbindung zum CAMPUSonline-Endpunkt möglich. Fehler: {$a}';
$string['error:endpointmissing'] = 'Sie müssen in den Einstellungen einen gültigen Endpunkt angeben.';
$string['error:uidfieldnotfound'] = 'CAMPUSOnline-Benutzerprofilfeld nicht gefunden - installieren Sie das Plugin erneut oder erstellen Sie das/die Feld(er) manuell neu.';
$string['error:unknown'] = 'Unbekannter Fehler.';
$string['event'] = 'Ereignis';
$string['errorsonly'] = 'Nur Fehler';
$string['externalkey'] = 'Externer Schlüssel';
$string['externalkey_desc'] = 'Falls für die Benutzeridentifikation erforderlich, können Sie die externe System-UID von CAMPUSonline abrufen. Eine externe System-UID besteht aus dem Schlüssel des externen Systems (external_system_key) und der eindeutigen ID im externen System (external_key).';
$string['externalsystemkey'] = 'Externer Systemschlüssel';
$string['externalsystemkey_desc'] = 'Siehe oben - wenn beide Werte gesetzt sind, wird die externe System-UID zu den verfügbaren Tokens für Benutzer hinzugefügt und kann zur Identifikation verwendet werden.';
$string['grouptocourse'] = 'Gruppe zu Kurs';
$string['grouptocourse_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese E-Learning-Ereignistypen werden separate Moodle-Kurse für jede der Gruppen erstellt.';
$string['grouptogroup'] = 'Gruppe zu Gruppe';
$string['grouptogroup_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese E-Learning-Ereignistypen werden CAMPUSonline-Gruppen in Moodle-Gruppen synchronisiert.
<p>Wenn leer werden Moodle-Gruppen für <strong>ALLE</strong> Typen erstellt, die nicht für separate Kurse konfiguriert sind!</p>.';
$string['groupsyncsettings'] = 'Gruppeneinstellungen';
$string['groupsyncsettings_desc'] = '<ul>
    <li>CAMPUSonline-<strong>Gruppen</strong> können entweder in Moodle-Kursgruppen synchronisiert oder es können separate Kurse für jede Gruppe erstellt werden</li>
    <li>Wenn Sie aus irgendeinem Grund keine Gruppen für einige E-Learning-Typen synchronisieren möchten, können Sie die Gruppenzuordnung nur für bestimmte Ereignistypen konfigurieren</li>
    </ul>';
$string['initialpassword'] = 'Anfangspasswort';
$string['initialpassword_desc'] = 'Stellen Sie sicher, dass Sie ein Anfangspasswort festlegen, das den Komplexitätsstandards entspricht, oder die Benutzererstellung schlägt fehl, selbst für Benutzer mit Authentifizierungsmethoden, die das Passwort nicht verwenden!';
$string['lectureshiproles'] = 'Wählen Sie Moodle-Rollen für CAMPUSonline-Lehramtrollen aus.';
$string['logduration'] = 'Protokolle aufbewahren für (Tage)';
$string['loglevel'] = 'Protokollierungsebene';
$string['logsettings'] = 'Protokolleinstellungen';
$string['logs'] = 'Protokolle';
$string['modificationtimeframe'] = 'Tage für die Modifikationssynchronisation';
$string['modificationtimeframe_desc'] = '<ul>
    <li>Wie viele Tage zurück sollen Modifikationen aus CAMPUSonline für die <strong>Modifikationssynchronisation</strong> abgerufen werden</li>
    <li>0 = nur heutige Modifikationen abrufen</li>
    <li>Derzeit stellt CAMPUSonline maximal <strong>7 Tage</strong> Modifikationen zur Verfügung</li>
    <li>Wenn Sie die Modifikationssynchronisationsaufgabe in längeren Intervallen ausführen, gehen Modifikationen verloren. Stellen Sie daher sicher, dass Sie den Aufgabenplan entsprechend konfigurieren</li></ul>';
$string['previewcourses'] = 'Kurse mit diesen Einstellungen anzeigen';
$string['previewusers'] = 'Benutzer mit diesen Einstellungen anzeigen';
$string['rolemappings'] = 'Rollen-Zuordnungen';
$string['rolemappings_desc'] = 'Wählen Sie Moodle-Rollen für CAMPUSonline-Studenten und Lehramtrollen aus.';
$string['rolemappings_notconnected'] = 'Keine Verbindung zu CAMPUSOnline möglich. Überprüfen Sie Ihre Verbindungseinstellungen und laden Sie diese Seite neu, um Zuordnungen für CAMPUSonline-Rollen hinzuzufügen.';
$string['rootcoursecategory'] = 'Stammkurskategorie';
$string['rootcoursecategory_desc'] = 'Kurskategorie, in die Kurse synchronisiert werden. Wenn Sie "TOP" auswählen, benötigen Sie Regeln, um Unterkategorien zu erstellen, andernfalls schlägt die Synchronisation fehl.';
$string['runtask'] = 'Geplante Aufgabe ausführen';
$string['runtask_delta'] = 'Geplante Aufgabe für MODIFIKATIONSSynchronisation ausführen';
$string['runtask_full'] = 'Geplante Aufgabe für VOLLSynchronisation ausführen';
$string['semester'] = 'Semester';
$string['semester_desc'] = 'Semester, die synchronisiert werden sollen. Bei mehreren Semestern trennen Sie diese mit einem Komma.';
$string['showrawcoursedata'] = 'Tokens und Rohkursdaten anzeigen';
$string['showrawuserdata'] = 'Tokens und Rohbenutzerdaten anzeigen';
$string['studentrole'] = 'Studenten';
$string['students'] = 'Studenten';
$string['subcategories'] = 'Unterkategorien';
$string['subcategories_desc'] = 'Geben Sie an, wie die Unterkategorienstruktur aufgebaut werden soll.
    <li>Verwenden Sie Tokens, um die Kategorienamen zu erstellen, und Backslashes, um Kategorien zu trennen, z.B.: "{org:code}\{course:semesterKey}\{course:courseClassificationKey}"</li>
    <li>Stellen Sie sicher, dass kein Unterkategoriename leer bleibt</li>
    <li>Zeigen Sie Rohdaten aus CAMPUSonline an, um verfügbare Felder/Tokens zu sehen</li>';
$string['success:connected'] = 'Erfolgreich mit CAMPUSonline-Endpunkt verbunden.';
$string['syncthiscourse'] = 'Kurs mit CAMPUSonline synchronisieren';
$string['syncingcourse'] = 'Synchronisiere Moodle-Kurs mit CAMPUSonline...';
$string['testconnection'] = 'Verbindung testen';
$string['testsettings'] = 'Diese Einstellungen testen';
$string['updatecourseurls'] = 'Kurs-URLs aktualisieren';
$string['updatecourseurls_desc'] = 'Schreibt die Moodle-Kurs-URL jedes Mal nach CAMPUSonline zurück, wenn ein Kurs synchronisiert wird. Normalerweise wird dies nur bei der Kurserstellung durchgeführt. Wenn etwas schiefgegangen ist, können Sie dies aktivieren, aber es sollte langfristig deaktiviert bleiben, um die Leistung zu verbessern.';
$string['updateexistingcourses'] = 'Vorhandene Kurse aktualisieren';
$string['updateexistingcourses_desc'] = 'Erlaubt der Einschreibungssynchronisation, Namen oder Kategorien bestehender Moodle-Kurse zu ändern, wenn sie sich in CAMPUSonline ändern.';
$string['usercount'] = 'Rohdaten für {$a} Benutzer:';
$string['useridsettings'] = 'Identifikation bestehender Benutzer';
$string['useridsettings_desc'] = '<ul>
    <li>Moodle-Benutzer werden identifiziert, indem die CAMPUSOnline-Personen-UID mit dem Benutzerprofilfeld <strong>campusonline_person_uid</strong> abgeglichen wird (wird bei der Plugin-Installation erstellt)</li>
    <li>Falls Ihre Moodle-Benutzer <strong>nicht über CAMPUSoline-Synchronisation</strong> erstellt wurden, müssen sie über andere Methoden identifiziert werden</li>
    <li>Wenn ein Benutzer nicht über seine Personen-UID gefunden wird, werden die konfigurierten Benutzer-Synchronisationswerte in dieser Reihenfolge verwendet, um den Benutzer in Moodle zu finden: <strong>Benutzername</strong>, <strong>Id-Nummer</strong>, <strong>E-Mail</strong></li>
    <li>Wenn Sie ein anderes Benutzerfeld als <strong>Fallback</strong> verwenden möchten, kann dies unten konfiguriert werden</li>
    <li>Die in <strong>Benutzersynchronisation & Werte</strong> konfigurierten Werte für dieses Feld werden als Suchkriterien verwendet</li></ul>';
$string['usermoodlefield'] = 'Benutzerdefiniertes Feld als Fallback für die Benutzeridentifikation';
$string['usermoodlefield_desc'] = 'Wenn ein Benutzer auf keine andere Weise gefunden wird (siehe oben), wird dieses Feld verwendet, um den Benutzer in Moodle zu finden.';
$string['usersyncsettings'] = 'Benutzersynchronisation & Werte';
$string['usersyncsettings_desc'] = '<ul>
    <li>Diese Synchronisationsaufgabe ist vollständig <strong>optional</strong> und sollte deaktiviert bleiben, wenn sie nicht ausdrücklich benötigt wird</li>
    <li>Aktivieren Sie diese Aufgabe nur, wenn Ihre Benutzerdaten <strong>nicht bereits auf andere Weise synchronisiert</strong> werden (z.B. SSO-Systeme)</li>
    <li>Unabhängig davon, ob die Synchronisationsaufgabe aktiv ist, sollten Sie die Feldwerte dennoch festlegen, da sie auch für die <strong>Benutzeridentifikation</strong> und <strong>Benutzererstellung</strong> (wenn konfiguriert) verwendet werden</li>
    <li><strong>Benutzernamen müssen eindeutig</strong> sein und Felder müssen mit gültigen Werten für ihre jeweiligen Feldtypen gefüllt sein, da sonst Fehler bei der Benutzererstellung auftreten!</li>
    <li>Diese Werte sind <strong>erforderlich</strong>, sonst schlägt die Benutzererstellung fehl: user_auth, user_password, user_username, user_email</li>
    <li>CAMPUSonline <strong>Personen-UID</strong>, <strong>Studenten-UID</strong> und <strong>Mitarbeiter-UID</strong> werden automatisch in den entsprechenden Benutzerprofilfeldern synchronisiert</li>
    <li>Klicken Sie auf <strong>Tokens und Rohdaten anzeigen</strong>, um verfügbare Felder/Tokens zu sehen</li></ul>';
$string['viewlogs'] = 'Protokolle anzeigen';
$string['warningsanderrors'] = 'Warnings und Errors';
