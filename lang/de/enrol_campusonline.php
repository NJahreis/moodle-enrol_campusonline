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
$string['task:sync'] = 'CAMPUSonline-Kurse & Einschreibungen FULL Sync';
$string['task:sync_delta'] = 'CAMPUSonline-Kurse & Einschreibungen MODIFICATIONS Sync';
$string['task:user_sync'] = 'CAMPUSonline-Benutzersynchronisation';

$string['allevents'] = 'Alle Ereignisse';
$string['allowemailupdate'] = 'Erlaube Benutzersynchronisation, um E-Mail-Adressen zu aktualisieren';
$string['allowemailupdate_desc'] = 'Erlaubt der Benutzersynchronisation, die E-Mail-Adressen bestehender Benutzer zu ändern. Beachten Sie, dass dies zu Problemen führen kann, da auf einigen Moodle-Websites Benutzer ihre E-Mail-Adresse zum Einloggen verwenden.';
$string['authmethod'] = 'Authentifizierungsmethode';
$string['availableToken'] = 'Verfügbare Token';
$string['availableToken_disclaimer'] = 'Einige davon könnten nur für Mitarbeiter oder Studierende verfügbar sein, aber nicht für beide.';
$string['backtosettings'] = 'Zurück zu den Moduleinstellungen';
$string['clientid'] = 'Client-ID';
$string['clientid_desc'] = 'Client-ID für den Zugriff auf CAMPUSonline';
$string['clientsecret'] = 'Client-Secret';
$string['clientsecret_desc'] = 'Geheimer Schlüssel für den Zugriff auf CAMPUSonline';
$string['configuretask'] = 'Task konfigurieren';
$string['configuretask_delta'] = 'Task für MODIFICATIONS Sync konfigurieren';
$string['configuretask_full'] = 'Task für FULL Sync konfigurieren';
$string['connectionerror'] = 'Keine Verbindung zu CAMPUSOnline möglich. Überprüfen Sie Ihre Verbindungseinstellungen. Bitte kontaktieren Sie Ihren Administrator.';
$string['connectionsettings'] = 'Verbindung';
$string['coursecatsettings'] = 'Kursbereich';
$string['coursecatsettings_desc'] = '<ul>
    <li>Die Kurskategorien können mithilfe von Werten aus CAMPUSonline als <strong>Token</strong> erstellt werden</li>
    <li>Wenn sich die resultierende Kursbereiche für einen aktiv synchronisierten Kurs ändert, wird der Kurs <strong>verschoben</strong></li></ul>';
$string['coursecount'] = 'Rohdaten für {$a} Kurse:';
$string['coursepreview'] = 'Kurs-Sync Vorschau';
$string['coursesyncsettings'] = 'Kurswerte';
$string['coursesyncsettings_desc'] = '<ul>
    <li>Die Moodle-Kurs-<strong>Idnumber</strong> wird immer mit der CAMPUSonline-Kurs-<strong>uid</strong> ausgefüllt</li>
    <li>Stellen Sie sicher, dass die <strong>Kurzbezeichnungen</strong> eindeutig sind und Felder mit gültigen Werten für ihre jeweiligen Feldtypen ausgefüllt sind, da sonst Fehler bei der Kurserstellung auftreten können!</li>
    <li>Diese Felder dürfen <strong>nicht leer</strong> sein, sonst schlägt die Kurserstellung fehl: course_fullname, course_shortname, course_format</li>
    <li>Wählen Sie Werte für andere Kursfelder (einschließlich benutzerdefinierter Kursfelder) durch Kombination von Text und <strong>Token</strong> für CAMPUSonline-Felder, z.B.: "CAMPUSONLINE_COURSE_{title}</li>
    <li>Zeigen Sie Rohdaten aus CAMPUSonline an, um verfügbare Felder/Token zu sehen</li></ul>';
$string['createcoursecatetories'] = 'Kursbereiche erstellen';
$string['createcoursecatetories_desc'] = 'Erlaubt diesen Tasks, Kursbereiche zu erstellen, wenn sie nicht existieren.';
$string['deletedcourse'] = 'Gelöschter Kurs (ID: {$a})';
$string['donotsyncrole'] = '- Diese Rolle nicht synchronisieren -';
$string['endpoint'] = 'CAMPUSonline-Endpoint';
$string['endpoint_desc'] = 'Adresse des CAMPUSonline OAuth2-Endpoints';
$string['enrolmentsyncsettings'] = 'Kurs- und Teilnehmer Sync';
$string['enrolmentsyncsettings_desc'] = '<ul>
    <li>Dieser Task erstellt und aktualisiert <strong>Kurse</strong> und deren <strong>Einschreibungen</strong></li>
    <li>Es gibt zwei Tasks: einen für <strong>vollständige</strong> Synchronisation und eine nur für <strong>Änderungen</strong></li>
    <li>Bei Performance Problemen wird empfohlen, einen <strong>FULL Sync</strong> manuell oder wöchentlich durchzuführen und nur den <strong>MODIFICATION Sync</strong> automatisch auszuführen</li>
    <li>Die Tasks sollten nachts laufen, da sie ziemlich lange dauern können</li>
    <li>Zusätzlich kann man einen <strong>einzelnen Kurs synchronisieren</strong>, indem man die Schaltfläche "Kurs mit CAMPUSonline synchronisieren" auf der Kurs-Teilnehmerseite betätigt (nur für Kurse verfügbar, die über CAMPUSonline erstellt wurden, und erfordert die Berechtigung enrol/campusonline:synccourse)</li></ul>';
$string['enrolsynccreateusers'] = 'Benutzer erstellen';
$string['enrolsynccreateusers_desc'] = 'Erlaubt diesen Tasks, Benutzer zu erstellen, die in Moodle nicht existieren oder nicht gefunden werden können. <strong>Aktivieren Sie dies nur, nachdem Sie sichergestellt haben, dass die Benutzeridentifikation korrekt funktioniert</strong>, andernfalls könnten viele doppelte Benutzer in Moodle entstehen!';
$string['error:cannotconnect'] = 'Keine Verbindung zum CAMPUSonline-Endpoint möglich. Fehler: {$a}';
$string['error:endpointmissing'] = 'Sie müssen in den Einstellungen einen gültigen Endpoint angeben.';
$string['error:uidfieldnotfound'] = 'CAMPUSOnline-Benutzerprofilfeld nicht gefunden - installieren Sie das Plugin erneut oder erstellen Sie das/die Feld(er) manuell neu.';
$string['error:unknown'] = 'Unbekannter Fehler.';
$string['event'] = 'Ereignis';
$string['errorsonly'] = 'Nur Fehler';
$string['externalkey'] = 'Externer Schlüssel';
$string['externalkey_desc'] = 'Falls für die Benutzeridentifikation erforderlich, können Sie eine <strong>externen System-UID</strong> von CAMPUSonline abrufen. Eine externe System-UID besteht aus dem Schlüssel des externen Systems (external_system_key) und der eindeutigen ID im externen System (external_key).';
$string['externalsystemkey'] = 'Externer Systemschlüssel';
$string['externalsystemkey_desc'] = 'Siehe oben - wenn beide Werte gesetzt sind, wird die externe System-UID zu den verfügbaren Token für Benutzer hinzugefügt und kann zur Identifikation verwendet werden.';
$string['grouptocourse'] = 'Gruppe zu Kurs';
$string['grouptocourse_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese E-Learning-Ereignistypen werden separate Moodle-Kurse für jede der Gruppen erstellt.';
$string['grouptogroup'] = 'Gruppe zu Gruppe';
$string['grouptogroup_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese E-Learning-Ereignistypen werden CAMPUSonline-Gruppen in Moodle-Gruppen synchronisiert.
<p>Wenn leer werden Moodle-Gruppen für <strong>ALLE</strong> Typen erstellt, die nicht für separate Kurse konfiguriert sind (empfohlen).</p>.';
$string['groupsyncsettings'] = 'Gruppeneinstellungen';
$string['groupsyncsettings_desc'] = '<ul>
    <li>CAMPUSonline-<strong>Gruppen</strong> können entweder in Moodle-Kursgruppen synchronisiert oder es können <strong>separate Kurse</strong> für jede Gruppe erstellt werden</li>
    <li>Wenn Sie keine Gruppen für einige E-Learning-Typen synchronisieren möchten, können Sie die Gruppenzuordnung nur für bestimmte Ereignistypen konfigurieren</li>
    </ul>';
$string['initialpassword'] = 'Passwort';
$string['initialpassword_desc'] = 'Stellen Sie sicher, dass Sie ein Initialpasswort festlegen, das den Komplexitätsstandards entspricht, oder die Benutzererstellung schlägt fehl, <strong>selbst für Benutzer mit Authentifizierungsmethoden, die das Passwort nicht verwenden!</strong>';
$string['lectureshiproles'] = 'Wählen Sie Moodle-Rollen für CAMPUSonline-Lehramtrollen aus.';
$string['logduration'] = 'Logs aufbewahren für (Tage)';
$string['loglevel'] = 'Log Level';
$string['logsettings'] = 'Log Einstellungen';
$string['logs'] = 'Logs';
$string['modificationtimeframe'] = 'Tage für Modification Sync';
$string['modificationtimeframe_desc'] = '<ul>
    <li>Wie viele Tage zurück sollen Änderungen aus CAMPUSonline für den <strong>Modification Sync</strong> abgerufen werden</li>
    <li>0 = nur heutige Änderungen abrufen</li>
    <li>Derzeit stellt CAMPUSonline maximal <strong>7 Tage</strong> Änderungen zur Verfügung</li>
    <li>Wenn Sie den Modification Sync in längeren Intervallen ausführen, gehen Modifikationen verloren. Stellen Sie daher sicher, dass Sie den Aufgabenplan entsprechend konfigurieren</li></ul>';
$string['previewcourses'] = 'Vorschau für Kurse mit diesen Einstellungen';
$string['previewusers'] = 'Vorschau für Benutzer mit diesen Einstellungen';
$string['rolemappings'] = 'Rollen-Zuordnungen';
$string['rolemappings_desc'] = 'Wählen Sie Moodle-Rollen für CAMPUSonline Rollen aus.';
$string['rolemappings_notconnected'] = 'Keine Verbindung zu CAMPUSOnline möglich. Überprüfen Sie Ihre Verbindungseinstellungen und laden Sie diese Seite neu, um Zuordnungen für CAMPUSonline-Rollen hinzuzufügen.';
$string['rootcoursecategory'] = 'Oberster Kursbereich';
$string['rootcoursecategory_desc'] = 'Kursbereich, in die Kurse synchronisiert werden. Wenn Sie "TOP" auswählen, benötigen Sie Regeln, um Unterkategorien zu erstellen, andernfalls schlägt die Synchronisation fehl.';
$string['runtask'] = 'Task ausführen';
$string['runtask_delta'] = 'Task für MODIFICATIONS Sync ausführen';
$string['runtask_full'] = 'Task für FULL Sync ausführen';
$string['semester'] = 'Semester';
$string['semester_desc'] = 'Semester, die synchronisiert werden sollen. Bei mehreren Semestern trennen Sie diese mit einem Komma.';
$string['showrawcoursedata'] = 'Token und Rohdaten anzeigen';
$string['showrawuserdata'] = 'Token und Rohdaten anzeigen';
$string['studentrole'] = 'Studenten';
$string['students'] = 'Studenten';
$string['subcategories'] = 'Unterkategorien';
$string['subcategories_desc'] = 'Geben Sie an, wie die Kursbereichs-Struktur aufgebaut werden soll.
    <li>Verwenden Sie Token, um die Namen für Kursbereiche zu erstellen, und Backslashes, um Kursbereiche zu trennen, z.B.: "{org:code}\{course:semesterKey}\{course:courseClassificationKey}"</li>
    <li>Stellen Sie sicher, dass kein Unterkategoriename leer bleibt</li>
    <li>Zeigen Sie Rohdaten aus CAMPUSonline an, um verfügbare Felder/Token zu sehen</li>';
$string['success:connected'] = 'Erfolgreich mit CAMPUSonline-Endpoint verbunden.';
$string['syncthiscourse'] = 'Kurs mit CAMPUSonline synchronisieren';
$string['syncingcourse'] = 'Synchronisiere Moodle-Kurs mit CAMPUSonline...';
$string['testconnection'] = 'Verbindung testen';
$string['testsettings'] = 'Diese Einstellungen testen';
$string['updatecourseurls'] = 'Kurs-URLs aktualisieren';
$string['updatecourseurls_desc'] = 'Schreibt die Moodle-Kurs-URL jedes Mal nach CAMPUSonline zurück, wenn ein Kurs synchronisiert wird. Normalerweise wird dies nur bei der Kurserstellung durchgeführt. Wenn etwas schiefgegangen ist, kann man das aktivieren, aber aus Performancegründen sollte diese Einstellung langfristig deaktiviert bleiben.';
$string['updateexistingcourses'] = 'Vorhandene Kurse aktualisieren';
$string['updateexistingcourses_desc'] = 'Erlaubt diesen Tasks, Namen oder Kategorien bestehender Moodle-Kurse zu ändern, wenn sie sich in CAMPUSonline ändern.';
$string['usercount'] = 'Rohdaten für {$a} Benutzer:';
$string['useridsettings'] = 'Identifikation bestehender Benutzer';
$string['useridsettings_desc'] = '<ul>
    <li>Moodle-Benutzer werden identifiziert, indem die CAMPUSOnline-Personen-UID mit dem Benutzerprofilfeld <strong>campusonline_person_uid</strong> abgeglichen wird (wird bei der Plugin-Installation erstellt)</li>
    <li>Falls Ihre Moodle-Benutzer <strong>nicht über CAMPUSoline-Sync</strong> erstellt wurden, müssen sie über andere Methoden identifiziert werden</li>
    <li>Wenn ein Benutzer nicht über seine Personen-UID gefunden wird, werden die folgende Kriterien in dieser Reihenfolge verwendet, um den Benutzer in Moodle zu finden: <strong>Benutzername</strong>, <strong>Idnumber</strong>, <strong>E-Mail</strong></li>
    <li>Wenn Sie ein weiteres Benutzerfeld als <strong>Fallback</strong> verwenden möchten, kann dies unten konfiguriert werden</li>
    <li>Die in <strong>Benutzer Sync</strong> konfigurierten Werte für dieses Feld werden als Suchkriterien verwendet</li></ul>';
$string['usermoodlefield'] = 'Benutzerdefiniertes Feld als Fallback für die Benutzeridentifikation';
$string['usermoodlefield_desc'] = 'Wenn ein Benutzer auf keine andere Weise gefunden wird (siehe oben), wird dieses Feld verwendet, um den Benutzer in Moodle zu finden.';
$string['usersyncsettings'] = 'Benutzer Sync';
$string['usersyncsettings_desc'] = '<ul>
    <li>Dieser Task ist <strong>optional</strong> und sollte deaktiviert bleiben, wenn die Nutzerdaten bereits auf anderem Weg nach Moodle gesynct werden (z.B. SSO-Systeme)</li>
    <li>Unabhängig davon, ob die Synchronisationsaufgabe aktiv ist, sollten Sie die Feldwerte dennoch festlegen, da sie auch für die <strong>Identifikation bestehender Benutzer</strong> und <strong>Benutzererstellung</strong> (wenn konfiguriert) verwendet werden</li>
    <li><strong>Benutzernamen müssen eindeutig</strong> sein und Felder müssen mit gültigen Werten für ihre jeweiligen Feldtypen gefüllt sein, da sonst Fehler bei der Benutzererstellung auftreten!</li>
    <li>Folgende Felder dürfen <strong>nicht leer</strong> sein, sonst schlägt die Benutzererstellung fehl: user_auth, user_password, user_username, user_email</li>
    <li>CAMPUSonline <strong>Personen-UID</strong>, <strong>Studenten-UID</strong> und <strong>Mitarbeiter-UID</strong> werden automatisch in den entsprechenden Benutzerprofilfeldern synchronisiert</li>
    <li>Klicken Sie auf <strong>Token und Rohdaten anzeigen</strong>, um verfügbare Felder/Token zu sehen</li></ul>';
$string['viewlogs'] = 'Logs anzeigen';
$string['warningsanderrors'] = 'Warnings und Errors';
