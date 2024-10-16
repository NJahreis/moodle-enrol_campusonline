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
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright  2024, Michael Lorenzoni
 */


// Basics.
$string['pluginname'] = 'CAMPUSonline enrolment';
$string['privacy:metadata'] = 'Das CAMPUSonline-Einschreibungs-Plugin speichert keine persönlichen Daten.';
$string['task:sync'] = 'CAMPUSonline-Kurse & Einschreibungen FULL Sync';
$string['task:sync_delta'] = 'CAMPUSonline-Kurse & Einschreibungen MODIFICATIONS Sync';
$string['task:user_id'] = 'CAMPUSonline Benutzer Identifikation';
$string['task:user_sync'] = 'CAMPUSonline Benutzerdaten Sync';

$string['allevents'] = 'Alle Ereignisse';
$string['allowemailupdate'] = 'Benutzerdaten Sync darf E-Mail-Adressen zu ändern';
$string['allowemailupdate_desc'] = 'Erlaubt dem Benutzerdaten Sync, die E-Mail-Adressen bestehender Benutzer zu ändern. Beachten Sie, dass dies zu Problemen führen kann, da auf einigen Moodle-Websites Benutzer ihre E-Mail-Adresse zum Einloggen verwenden.';
$string['allowusernameupdate'] = 'Benutzerdaten Sync darf Anmeldenamen ändern';
$string['allowusernameupdate_desc'] = 'Erlaubt dem Benutzerdaten Sync, die Anmeldenamen bestehender Benutzer zu ändern. Beachten Sie, dass dies zu Problemen bei der Anmeldung führen kann.';
$string['authmethod'] = 'Authentifizierungsmethode';
$string['autoidnewusers'] = 'Neue Benutzer automatisch identifizieren';
$string['autoidnewusers_desc'] = 'Identifiziert neu erstellte Benutzer beim Erstellen. Nützlich, wenn die Benutzer durch SSO angelegt werden.';
$string['availabletokens'] = 'Verfügbare Token';
$string['availabletokens_disclaimer'] = 'Einige davon könnten nur für Mitarbeiter oder Studierende verfügbar sein, aber nicht für beide.';
$string['backtosettings'] = 'Zurück zu den Moduleinstellungen';
$string['clientid'] = 'Client-ID';
$string['clientid_desc'] = 'Client-ID für den Zugriff auf CAMPUSonline';
$string['clientsecret'] = 'Client-Secret';
$string['clientsecret_desc'] = 'Geheimer Schlüssel für den Zugriff auf CAMPUSonline';
$string['configuretask'] = 'Task konfigurieren';
$string['configuretask_delta'] = 'Task für MODIFICATIONS Sync konfigurieren';
$string['configuretask_full'] = 'Task für FULL Sync konfigurieren';
$string['connectionerror'] = 'Keine Verbindung zu CAMPUSonline möglich. Überprüfen Sie Ihre Verbindungseinstellungen. Bitte kontaktieren Sie Ihren Administrator.';
$string['connectionsettings'] = 'Verbindung';
$string['coursecatsettings'] = 'Kursbereich';
$string['coursecatsettings_desc'] = '<ul>
    <li>Die Kursbereiche können mithilfe von Werten aus CAMPUSonline als <strong>Token</strong> erstellt werden</li>
    <li>Wenn sich die resultierende Kursbereiche für einen aktiv synchronisierten Kurs ändert, wird der Kurs <strong>verschoben</strong></li></ul>';
$string['coursecount'] = 'Rohdaten für {$a} Kurse:';
$string['coursepreview'] = 'Kurs-Sync Vorschau';
$string['coursesyncsettings'] = 'Kursdaten';
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
$string['employees'] = 'Mitarbeiter';
$string['endpoint'] = 'CAMPUSonline-Endpoint';
$string['endpoint_desc'] = 'Adresse des CAMPUSonline OAuth2-Endpoints';
$string['enrolmentsyncsettings'] = 'Kurs Sync';
$string['enrolmentsyncsettings_desc'] = '<ul>
    <li>Dieser Task erstellt und aktualisiert <strong>Kurse</strong> und deren <strong>Einschreibungen</strong></li>
    <li>Es gibt zwei Tasks: einen für <strong>vollständige</strong> Synchronisation und eine nur für <strong>Änderungen</strong></li>
    <li>Bei Performance Problemen wird empfohlen, einen <strong>FULL Sync</strong> manuell oder wöchentlich durchzuführen und nur den <strong>MODIFICATION Sync</strong> automatisch auszuführen</li>
    <li>Die Tasks sollten nachts laufen, da sie ziemlich lange dauern können</li>
    <li>Zusätzlich kann man einen <strong>einzelnen Kurs synchronisieren</strong>, indem man die Schaltfläche "Kurs mit CAMPUSonline synchronisieren" auf der Kurs-Teilnehmerseite betätigt (nur für Kurse verfügbar, die über CAMPUSonline erstellt wurden, und erfordert die Berechtigung enrol/campusonline:synccourse)</li></ul>';
$string['enrolsynccreateusers'] = 'Benutzer beim Kurs-  erstellen';
$string['enrolsynccreateusers_desc'] = 'Erlaubt dem Kurs Sync Tasks, Benutzer zu erstellen. <ul><li>Beachten Sie, dass Benutzer normalerweise durch SSO oder den Benutzerdaten Sync Task angelegt werden!</li>
<li><strong>Aktivieren Sie dies nur, nachdem Sie sichergestellt haben, dass die Benutzeridentifikation korrekt funktioniert, andernfalls könnten viele doppelte Benutzer in Moodle entstehen!</strong></li></ul>';
$string['error:cannotconnect'] = 'Keine Verbindung zum CAMPUSonline-Endpoint möglich. Fehler: {$a}';
$string['error:config'] = 'Fehlende Verbindungseinstellungen!';
$string['error:endpointmissing'] = 'Sie müssen in den Einstellungen einen gültigen Endpoint angeben.';
$string['error:uidfieldnotfound'] = 'CAMPUSonline-Benutzerprofilfeld nicht gefunden - installieren Sie das Plugin erneut oder erstellen Sie das/die Feld(er) manuell neu.';
$string['error:unknown'] = 'Unbekannter Fehler.';
$string['event'] = 'Ereignis';
$string['errorsonly'] = 'Nur Fehler';
$string['externalkey'] = 'Externer Schlüssel';
$string['externalsystemkey'] = 'Externer Systemschlüssel';
$string['externalsystemkey_desc'] = 'Falls für die Benutzeridentifikation <strong>EXTERNAL_SYSYSTEM_UID</strong> verwendet wird, ist die Angabe von external_system_key und external_key notwendig, damit diese von CAMPUSonline geholt werden können.';
$string['grouptocourse'] = 'Gruppe zu Kurs';
$string['grouptocourse_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese E-Learning-Ereignistypen werden separate Moodle-Kurse für jede der Gruppen erstellt.';
$string['grouptogroup'] = 'Gruppe zu Gruppe';
$string['grouptogroup_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese E-Learning-Ereignistypen werden CAMPUSonline-Gruppen in Moodle-Gruppen synchronisiert.
<p>Wenn leer werden Moodle-Gruppen für <strong>ALLE</strong> Typen erstellt, die nicht für separate Kurse konfiguriert sind (empfohlen).</p>';
$string['groupsyncsettings'] = 'Gruppeneinstellungen';
$string['groupsyncsettings_desc'] = '<ul>
    <li>CAMPUSonline-<strong>Gruppen</strong> können entweder in Moodle-Kursgruppen synchronisiert oder es können <strong>separate Kurse</strong> für jede Gruppe erstellt werden</li>
    <li>Wenn Sie keine Gruppen für einige E-Learning-Typen synchronisieren möchten, können Sie die Gruppenzuordnung nur für bestimmte Ereignistypen konfigurieren</li>
    </ul>';
$string['idattempts'] = 'Anzahl von Versuchen';
$string['idattempts_desc'] = 'Ein Zähler für die fehlgeschlagenen Versuche einen Benutzer zu identifizieren wird in einem CAMPUSonline Benutzer Profilfeld geführt. Dieser Zähler kann von Admins zurückgesetzt werden, um erneut zu versuchen, den Benutzer in CAMPUSonline zu finden.';
$string['initialpassword'] = 'Initialpasswort';
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
    <li>Derzeit stellt CAMPUSonline maximal <strong>7 Tage</strong> Änderungen zur Verfügung - wenn Sie den Modification Sync in längeren Intervallen ausführen, gehen Modifikationen verloren. Stellen Sie daher sicher, dass Sie den Aufgabenplan entsprechend konfigurieren</li></ul>';
$string['previewcourses'] = 'Vorschau für Kurse mit diesen Einstellungen';
$string['previewusers'] = 'Vorschau für Benutzer mit diesen Einstellungen';
$string['readme'] = 'Bitte lesen Sie die Dokumentation für mehr Information, wie man das Plugin in verschiedenen Anwendungsszenarien richtig konfiguriert.';
$string['rolemappings'] = 'Rollen-Zuordnungen';
$string['rolemappings_desc'] = 'Wählen Sie Moodle-Rollen für CAMPUSonline Rollen aus.';
$string['rolemappings_notconnected'] = 'Keine Verbindung zu CAMPUSonline möglich. Überprüfen Sie Ihre Verbindungseinstellungen und laden Sie diese Seite neu, um Zuordnungen für CAMPUSonline-Rollen hinzuzufügen.';
$string['rootcoursecategory'] = 'Oberster Kursbereich';
$string['rootcoursecategory_desc'] = 'Kursbereich, in die Kurse synchronisiert werden. Wenn Sie "TOP" auswählen, benötigen Sie Regeln, um Unterbereiche zu erstellen, andernfalls schlägt die Synchronisation fehl.';
$string['restcalls'] = 'Zeige jeden REST Call beim Ausführen der Tasks';
$string['restcalls_desc'] = 'Zeigt Informationen über jeden REST Call beim Ausführen des Tasks an, schreibt aber nicht ins Logfile. Für Debugging.';
$string['runtask'] = 'Task ausführen';
$string['runtask_delta'] = 'Task für MODIFICATIONS Sync ausführen';
$string['runtask_full'] = 'Task für FULL Sync ausführen';
$string['semester'] = 'Semester';
$string['semester_desc'] = 'Semester, die synchronisiert werden sollen. Bei mehreren Semestern trennen Sie diese mit einem Komma.';
$string['showrawcoursedata'] = 'Token und Rohdaten anzeigen';
$string['showrawuserdata'] = 'Token und Rohdaten anzeigen';
$string['sourceclaim'] = 'CAMPUSonline ID';
$string['sourceclaim_desc'] = 'Wählen Sie die ID, welche die Moodle Benutzer gesetzt haben.';
$string['sourcefield'] = 'Moodle Feld';
$string['sourcefield_desc'] = 'Wählen Sie das Moodle Feld, welches die ID enthält.';
$string['studentrole'] = 'Studenten';
$string['students'] = 'Studenten';
$string['subcategories'] = 'Unterbereiche';
$string['subcategories_desc'] = 'Geben Sie an, wie die Kursbereichs-Struktur aufgebaut werden soll.
    <li>Verwenden Sie Token, um die Namen für Kursbereiche zu erstellen, und Backslashes, um Kursbereiche zu trennen, z.B.: "{org:code}\{course:semesterKey}\{course:courseClassificationKey}"</li>
    <li>Stellen Sie sicher, dass kein Name für einen Kursbereich leer bleiben kann</li>
    <li>Zeigen Sie Rohdaten aus CAMPUSonline an, um verfügbare Felder/Token zu sehen</li>';
$string['success:connected'] = 'Erfolgreich mit CAMPUSonline-Endpoint verbunden.';
$string['syncthiscourse'] = 'Kurs mit CAMPUSonline synchronisieren';
$string['syncingcourse'] = 'Synchronisiere Moodle-Kurs mit CAMPUSonline...';
$string['syncusersonlogin'] = 'Benutzerdaten beim Login syncen';
$string['syncusersonlogin_desc'] = 'Neben dem Sync Task werden Benutzerdaten auch bei jedem Login gesynct.';
$string['testconnection'] = 'Verbindung testen';
$string['testsettings'] = 'Diese Einstellungen testen';
$string['updatecourseurls'] = 'Kurs-URLs aktualisieren';
$string['updatecourseurls_desc'] = 'Schreibt die Moodle-Kurs-URL jedes Mal nach CAMPUSonline zurück, wenn ein Kurs synchronisiert wird. Normalerweise wird dies nur bei der Kurserstellung durchgeführt. Wenn etwas schiefgegangen ist, kann man das aktivieren, aber aus Performancegründen sollte diese Einstellung langfristig deaktiviert bleiben.';
$string['updateexistingcourses'] = 'Vorhandene Kurse aktualisieren';
$string['updateexistingcourses_desc'] = 'Erlaubt diesem Tasks, Namen oder Kursbereiche bestehender Moodle-Kurse zu ändern, wenn sie sich in CAMPUSonline ändern.';
$string['usercount'] = 'Rohdaten für {$a} Benutzer:';
$string['useridsettings'] = 'Identifikation bestehender Benutzer';
$string['useridsettings_desc'] = '<ul>
    <li>Falls Ihre Moodle-Benutzer <strong>nicht über CAMPUSonline-Sync</strong> erstellt wurden, müssen sie über andere Methoden identifiziert werden</li>
    <li>Falls notwendig, können Sie zusätzliche <strong>externe IDs</strong> von CAMPUSonline abrufen, um Benutzer über diese Werte in Moodle zu finden</li>
    <li>Sobald ein Benutzer in CAMPUSonline gefunden wird, wird das Benutzerprofilfeld <strong>campusonline_person_uid</strong> mit der CAMPUSonline Person UID befüllt (dieses Feld wird bei der Plugin-Installation erstellt)</li></ul>';
$string['usermoodlefield'] = 'Benutzerdefiniertes Feld als Fallback für die Benutzeridentifikation';
$string['usermoodlefield_desc'] = 'Wenn ein Benutzer auf keine andere Weise gefunden wird (siehe oben), wird dieses Feld verwendet, um den Benutzer in Moodle zu finden.';
$string['usersynccreateusers'] = 'Benutzer erstellen';
$string['usersynccreateusers_desc'] = 'Erlaubt dem Benutzerdaten Sync Task, Benutzer zu erstellen, die in Moodle nicht existieren oder nicht gefunden werden können. <strong>Aktivieren Sie dies nur, nachdem Sie sichergestellt haben, dass die Benutzeridentifikation korrekt funktioniert</strong>, andernfalls könnten viele doppelte Benutzer in Moodle entstehen!';
$string['usersyncsettings'] = 'Benutzerdaten Sync';
$string['usersyncsettings_desc'] = '<ul>
    <li>Dieser Task ist <strong>optional</strong> und sollte deaktiviert bleiben, wenn die Nutzerdaten bereits auf anderem Weg nach Moodle gesynct werden (z.B. SSO-Systeme)</li>
    <li>Unabhängig davon, ob die Synchronisationsaufgabe aktiv ist, sollten Sie die Feldwerte dennoch festlegen, da sie auch für die <strong>Identifikation bestehender Benutzer</strong> und <strong>Benutzererstellung</strong> (wenn konfiguriert) verwendet werden</li>
    <li><strong>Benutzernamen müssen eindeutig</strong> sein und Felder müssen mit gültigen Werten für ihre jeweiligen Feldtypen gefüllt sein, da sonst Fehler bei der Benutzererstellung auftreten!</li>
    <li>Folgende Felder dürfen <strong>nicht leer</strong> sein, sonst schlägt die Benutzererstellung fehl: user_auth, user_password, user_username, user_email</li>
    <li>CAMPUSonline <strong>Personen-UID</strong>, <strong>Studenten-UID</strong> und <strong>Mitarbeiter-UID</strong> werden automatisch in den entsprechenden Benutzerprofilfeldern synchronisiert</li>
    <li>Klicken Sie auf <strong>Token und Rohdaten anzeigen</strong>, um verfügbare Felder/Token zu sehen</li></ul>';
$string['viewlogs'] = 'Logs anzeigen';
$string['warningsanderrors'] = 'Warnings und Errors';
