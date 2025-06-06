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
 */


// Basics.
$string['pluginname'] = 'CAMPUSonline enrolment';
$string['privacy:metadata'] = 'Das CAMPUSonline-Einschreibungs-Plugin speichert keine persönlichen Daten.';

$string['allevents'] = 'Alle Ereignisse';
$string['allowemailupdate'] = 'Nutzerdaten Sync darf E-Mail-Adressen zu ändern';
$string['allowemailupdate_desc'] = 'Erlaubt dem Nutzerdaten Sync, die E-Mail-Adressen bestehender Nutzer/innen zu ändern. Beachten Sie, dass dies zu Problemen führen kann, da auf einigen Moodle-Websites Nutzer/innen ihre E-Mail-Adresse zum Einloggen verwenden.';
$string['allowusernameupdate'] = 'Nutzerdaten Sync darf Anmeldenamen ändern';
$string['allowusernameupdate_desc'] = 'Erlaubt dem Nutzerdaten Sync, die Anmeldenamen bestehender Nutzer/innen zu ändern. Beachten Sie, dass dies zu Problemen bei der Anmeldung führen kann.';
$string['authmethod'] = 'Authentifizierungsmethode';
$string['autoidnewusers'] = 'Neue Nutzer/innen automatisch identifizieren';
$string['autoidnewusers_desc'] = 'Identifiziert neu erstellte Nutzer/innen beim Erstellen. Nützlich, wenn die Nutzer/innen durch SSO angelegt werden.';
$string['availabletokens'] = 'Verfügbare Token';
$string['availabletokens_disclaimer'] = 'Einige davon könnten nur für Mitarbeiter oder Studierende verfügbar sein, aber nicht für beide.';
$string['backtosettings'] = 'Zurück zu den Moduleinstellungen';
$string['campusonline:unenrol'] = 'CAMPUSonline enrolments entfernen';
$string['campusonline:config'] = 'CAMPUSonline enrolments konfigurieren';
$string['campusonline:synccourse'] = 'Einen einzelnen Kurs mit CAMPUSonline syncen';
$string['campusonline:syncuser'] = 'Eine/n einzelne/n Nutzer/in mit CAMPUSonline syncen';
$string['clientid'] = 'Client-ID';
$string['clientid_desc'] = 'Client-ID für den Zugriff auf CAMPUSonline';
$string['clientsecret'] = 'Client-Secret';
$string['clientsecret_desc'] = 'Geheimer Schlüssel für den Zugriff auf CAMPUSonline';
$string['configureorgroles'] = '<ul><li>Um Rollen in Organisationseinheiten nach Moodle zu syncen, <a href="{$a->roleurl}">definieren Sie Rollen</a> in Moodle die den Rollen in CAMPUSonline entsprechen</li>
    <li>Die <strong>Kurzbezeichnung</strong> der Moodle Rolle muss "<strong>campusonline</strong>" enthalten</li>
    <li>Der <strong>angepasste Rollenname</strong> der Moodle Rolle entspricht der Bezeichnung der CAMPUSonline Rolle (Großschreibung beachten!)</li>
    <li>Die <strong>Kontexttypen</strong> in denen die Rolle zugewiesen werden kann, müssen <strong>Kursbereich</strong> inkludieren</li>
    <li>Derzeit haben Sie folgende Rollen in Moodle für den Sync konfiguriert: <strong>{$a->rolestring}</strong></li></ul>';
$string['configuretask'] = 'Task konfigurieren';
$string['configuretask_delta'] = 'Task für MODIFICATIONS Sync konfigurieren';
$string['configuretask_full'] = 'Task für FULL Sync konfigurieren';
$string['connectionerror'] = 'Keine Verbindung zu CAMPUSonline möglich. Überprüfen Sie Ihre Verbindungseinstellungen. Bitte kontaktieren Sie Ihren Administrator.';
$string['connectionsettings'] = 'Verbindung';
$string['connectionsettings_desc'] = '<ul>
    <li>Bitte <strong>speichern</strong> Sie die Einstellungen, bevor Sie die Verbindung testen</li>
    <li>Bitte beachten Sie, dass alle Tasks erst aktiv sind, wenn CAMPUSonline enrolment in den <a href="{$a}">Verfügbaren Einschreibeplugins</a> aktiviert ist
    </ul>';
$string['coursecatsettings'] = 'Unterbereiche für Kurse';
$string['coursecatsettings_desc'] = '<ul>
    <li>Hier können Sie <strong>optional</strong> die Struktur für die Kursbereiche festlegen, in denen die Kurse vom weiter unten zu konfigurierenden <strong>Kurs & Einschreibungssync</strong> abgelegt werden</li>
    <li>Kurse werden prinzipiell im <strong>Kursbereich, der ihrer Organisation zugeordnet</strong> ist, abgelegt, sofern <strong>Organisationseinheiten Sync</strong> aktiv ist, und diese bereits angelegt wurden</li>
    <li>Wird kein Kursbereich für die Organisation gefunden, landen die Kurse direkt im oben konfigurierten <strong>Kursbereich für die CAMPUSonline Struktur</strong></li>
    <li>Optional können Sie die Kurse unterhalb dieser Kategorie noch weiter strukturieren, indem Sie mittels Token <strong>Unterkategorien</strong> erstellen lassen</li>
    <li>Wenn sich die resultierende Kursbereiche für einen aktiv synchronisierten Kurs ändert, wird der Kurs <strong>verschoben</strong></li></ul>';
$string['coursecount'] = 'Rohdaten für {$a} Kurse:';
$string['coursecount_syncdata'] = '{$a->co} Kurse von CAMPUSonline geholt, Vorschau der Werte für {$a->moodle} zu syncende Moodle Kurse:';
$string['coursepreview'] = 'Kurs-Sync Vorschau';
$string['coursesyncsettings'] = 'Kursdaten';
$string['coursesyncsettings_desc'] = '<ul>
    <li>Die Moodle-Kurs-<strong>Idnumber</strong> wird immer mit der CAMPUSonline-Kurs-<strong>uid</strong> ausgefüllt</li>
    <li>Stellen Sie sicher, dass die <strong>Kurzbezeichnungen</strong> eindeutig sind und Felder mit gültigen Werten für ihre jeweiligen Feldtypen ausgefüllt sind, da sonst Fehler bei der Kurserstellung auftreten können!</li>
    <li>Folgende Felder dürfen <strong>nicht leer</strong> sein, sonst schlägt die Kurserstellung fehl: course_fullname, course_shortname, course_format</li>
    <li>Wählen Sie Werte für andere Kursfelder (einschließlich benutzerdefinierter Kursfelder) durch Kombination von Text und <strong>Token</strong> für CAMPUSonline-Felder, z.B.: "CAMPUSONLINE_COURSE_{title}</li>
    <li>Zeigen Sie Rohdaten aus CAMPUSonline an, um verfügbare Felder/Token zu sehen</li></ul>';
$string['createcoursecategories'] = 'Kursbereiche erstellen';
$string['createcoursecategories_desc'] = 'Erlaubt dem Kurs & Einschreibungs Sync Task, Kursbereiche zu erstellen, wenn sie nicht existieren.';
$string['deletedcourse'] = 'Gelöschter Kurs (ID: {$a})';
$string['donotsyncrole'] = '- Diese Rolle nicht synchronisieren -';
$string['employees'] = 'Mitarbeiter';
$string['endpoint'] = 'CAMPUSonline-Endpoint';
$string['endpoint_desc'] = 'Adresse des CAMPUSonline OAuth2-Endpoints';
$string['enrolmentsyncsettings'] = 'Kurs & Einschreibungs Sync';
$string['enrolmentsyncsettings_desc'] = '<ul>
    <li>Dieser Task erstellt und aktualisiert <strong>Kurse</strong> und deren <strong>Einschreibungen</strong></li>
    <li>Es gibt zwei Tasks: einen für <strong>vollständige</strong> Synchronisation und eine nur für <strong>Änderungen</strong></li>
    <li>Bei Performance Problemen wird empfohlen, einen <strong>FULL Sync</strong> manuell oder wöchentlich durchzuführen und nur den <strong>MODIFICATION Sync</strong> automatisch auszuführen</li>
    <li>Zusätzlich kann man einen <strong>einzelnen Kurs synchronisieren</strong>, indem man die Schaltfläche "Kurs mit CAMPUSonline synchronisieren" auf der Kurs-Teilnehmerseite betätigt (nur für Kurse verfügbar, die über CAMPUSonline erstellt wurden, und erfordert die Berechtigung enrol/campusonline:synccourse)</li></ul>';
$string['enrolsynccreateusers'] = 'Nutzer/innen beim Kurs Sync erstellen';
$string['enrolsynccreateusers_desc'] = 'Erlaubt dem Kurs Sync Tasks, Nutzer/innen zu erstellen. <ul><li>Beachten Sie, dass Nutzer/innen normalerweise durch SSO oder den Nutzerdaten Sync Task angelegt werden!</li>
<li><strong>Aktivieren Sie dies nur, nachdem Sie sichergestellt haben, dass die Useridentifikation korrekt funktioniert, andernfalls könnten viele doppelte Nutzer/innen in Moodle entstehen!</strong></li></ul>';
$string['error:cannotconnect'] = 'Keine Verbindung zum CAMPUSonline-Endpoint möglich. Fehler: {$a}';
$string['error:config'] = 'Fehlende Verbindungseinstellungen!';
$string['error:endpointmissing'] = 'Sie müssen in den Einstellungen einen gültigen Endpoint angeben.';
$string['error:notenabled'] = 'Die CAMPUSonline enrolment Einschreibemethode ist in den Einstellungen für verfügbare Einschreibeplugins nicht aktiviert.';
$string['error:uidfieldnotfound'] = 'CAMPUSonline-Feld nicht gefunden: {$a}. Installieren Sie das Plugin erneut oder legen Sie das Feld manuell an.';
$string['error:unknown'] = 'Unbekannter Fehler.';
$string['event'] = 'Ereignis';
$string['errorsonly'] = 'Nur Fehler';
$string['externalkey'] = 'Externer Schlüssel';
$string['externalsystemkey'] = 'Externer Systemschlüssel';
$string['externalsystemkey_desc'] = 'Falls für die Useridentifikation <strong>EXTERNAL_SYSYSTEM_UID</strong> verwendet wird, ist die Angabe von external_system_key und external_key notwendig, damit diese von CAMPUSonline geholt werden können.';
$string['flatcourse'] = 'Kurs ohne Gruppen';
$string['flatcourse_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese eLearningEventTypeKeys Kurse angelegt, aber Gruppen ignoriert.';
$string['grouptocourse'] = 'Gruppe zu Kurs';
$string['grouptocourse_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese eLearningEventTypeKeys werden separate Moodle-Kurse für jede der Gruppen erstellt.';
$string['grouptogroup'] = 'Gruppe zu Gruppe';
$string['grouptogroup_desc'] = 'Komma-separierte Liste von elearningEventTypeKeys. Für diese eLearningEventTypeKeys werden CAMPUSonline-Gruppen in Moodle-Gruppen synchronisiert.
<p>Wenn leer werden Moodle-Gruppen für <strong>ALLE</strong> Typen erstellt, die nicht für separate Kurse konfiguriert sind (empfohlen).</p>';
$string['groupsyncsettings'] = 'Modus für LV-Gruppen';
$string['groupsyncsettings_desc'] = '<ul>
    <li>CAMPUSonline-<strong>Gruppen</strong> können entweder in Moodle-Kursgruppen synchronisiert oder es können <strong>separate Kurse</strong> für jede Gruppe erstellt werden</li>
    <li>Konfigurieren Sie alle <strong>eLearningEventTypeKeys</strong> die Sie synchronisieren möchten, Kurse mit nicht konfigurierte eLearningEventTypeKeys werden übersprungen.</li>
    <li>Ein Sonderfall: falls Sie umgekehrt mehrere Moodle-Kurse mit einer einzigen LV in CAMPUSonline zu verknüpfen möchten, ist das möglich, indem Sie die UID des Kurses in das Kursfeld <strong>Other CAMPUSonline courses linked to this Moodle course</strong> eingeben.</li>
    </ul>';
$string['idattempts'] = 'Anzahl von Versuchen';
$string['idattempts_desc'] = 'Ein Zähler für die fehlgeschlagenen Versuche einen Nutzer/innen zu identifizieren wird in einem CAMPUSonline Nutzer/innen Profilfeld geführt. Dieser Zähler kann von Admins zurückgesetzt werden, um erneut zu versuchen, den Nutzer/innen in CAMPUSonline zu finden.';
$string['initialpassword'] = 'Initialpasswort für neu erstellte Benutzer/innen';
$string['initialpassword_desc'] = 'Leerlassen, um eine zufällig generiertes Password für neue User zu verwenden (empfohlen). <p> Wenn Sie das Password selbst setzen, stellen Sie sicher, dass Sie ein Initialpasswort festlegen, das den Komplexitätsstandards entspricht, oder die Benutzererstellung schlägt fehl, <strong>selbst für Nutzer/innen mit Authentifizierungsmethoden, die das Passwort nicht verwenden!</strong></p>';
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
$string['nocoursesfound'] = 'Für das/die ausgewählte(n) Semester wurden keine als eLearning markierten Lehrveranstaltungen in CAMPUSonline gefunden.';
$string['orgfilter'] = 'Organisationen';
$string['orgfilter_desc'] = 'Nur die <strong>Kurse</strong> bestimmter Organisationen syncen. <strong>Beeinflusst nicht den Organisations Sync</strong>. Nutzen Sie dies, um testweise den Kurs- und Einschreibungssync nur für einzelne Organisationen laufen zu lassen. Geben Sie die Organisations-UID(s) ein, bei mehreren trennen Sie diese mit einem Komma.';
$string['orgkey'] = 'Kennzeichnungs-Key für zu syncende Organisationseinheiten';
$string['orgkey_desc'] = 'Mit diesem Schlüssel werden Organisationseinheiten in CAMPUSonline identifiziert.';
$string['orgsyncsettings'] = 'Organisationseinheiten Sync';
$string['orgsyncsettings_desc'] = '<ul><li>Dieser Task synchronisiert <strong>ausgewählte Organisationseinheiten</strong> als <strong>Kursbereiche</strong> nach Moodle</li>
    <li>Er ist <strong>optional</strong>, und sollte nur verwendet werden, wenn er wirklich benötigt wird, weil die Rollenzuweisungen für die Kategorien gesynct werden müssen</li>
    <li>Alternativ kann eine eigene Struktur in Moodle erstellt werden, indem im nächsten Punkt die <strong>Unterbereiche für Kurse</strong> konfiguriert werden</li>
    <li>Organisationseinheiten können in CAMPUSonline für den Sync nach Moodle markiert werden</li>
    <li>Alle Organisationseinheiten, die Moodle Kurse besitzen, sollten ausgewählt werden, ansonsten werden deren Kurse direkt im Kursbereich für die CAMPUSonline Struktur erstellt</li>
    </ul>';
$string['phplogging'] = 'Zusätzlich ins PHP error log schreiben';
$string['previewcourses'] = 'Vorschau für Kurse mit diesen Einstellungen';
$string['previewusers'] = 'Vorschau für Nutzer/innen mit diesen Einstellungen';
$string['readme'] = 'Bitte lesen Sie die Dokumentation für mehr Information, wie man das Plugin in verschiedenen Anwendungsszenarien richtig konfiguriert.';
$string['rolemappings'] = 'Rollen-Zuordnungen';
$string['rolemappings_desc'] = '<ul><li>Wählen Sie Moodle-Rollen für CAMPUSonline Rollen aus</li>
    <li>Die Rollen müssen die Zuweisung zum <strong>Kursen</strong> erlauben</li></ul>';
$string['rolemappings_notconnected'] = 'Keine Verbindung zu CAMPUSonline möglich. Überprüfen Sie Ihre Verbindungseinstellungen und laden Sie diese Seite neu, um Zuordnungen für CAMPUSonline-Rollen hinzuzufügen.';
$string['rootcoursecategory'] = 'Kursbereich für CAMPUSonline Struktur';
$string['rootcoursecategory_desc'] = 'Kursbereich, in dem die CAMPUSonline Kursbereichs-Hierarchie für Organisationen gebaut wird. <p>
Dient auch als Kursbereich für Kurse, die durch den weiter unten konfigurierten <strong>Kurs- und Einschreibungssync</strong> angelegt werden, wenn deren Organisation nicht gefunden wird.</p>';
$string['restcalls'] = 'Zeige jeden REST Call beim Ausführen der Tasks';
$string['restcalls_desc'] = 'Zeigt Informationen über jeden REST Call beim Ausführen des Tasks an, schreibt aber nicht ins Logfile. Für Debugging.';
$string['runtask'] = 'Task ausführen';
$string['runtask_delta'] = 'Task für MODIFICATIONS Sync ausführen';
$string['runtask_full'] = 'Task für FULL Sync ausführen';
$string['semester'] = 'Semester';
$string['semester_desc'] = 'Semester, die synchronisiert werden sollen. Bei mehreren Semestern trennen Sie diese mit einem Komma.';
$string['showrawcoursedata'] = 'Token und Rohdaten anzeigen';
$string['showrawuserdata'] = 'Token und Rohdaten anzeigen';
$string['skip'] = 'Übersprungen - ELearningEventTypeKey <strong>{$a}</strong> nicht für Sync konfiguriert';
$string['skippedcourses'] = '{$a} Kurse wurden übersprungen, weil ihre Organisation nicht dem gesetzten Filter (Einstellung orgfilter) entspricht.';
$string['sourceclaim'] = 'CAMPUSonline ID';
$string['sourceclaim_desc'] = 'Wählen Sie die ID, welche die Moodle Nutzer/innen gesetzt haben.';
$string['sourcefield'] = 'Moodle Feld';
$string['sourcefield_desc'] = 'Wählen Sie das Moodle Feld, welches die ID enthält.';
$string['studentrole'] = 'Studierende';
$string['students'] = 'Studierende';
$string['subcategories'] = 'Unterbereiche';
$string['subcategories_desc'] = 'Geben Sie an, wie die <strong>Kursbereichs-Unterstruktur für den Kurs & Einschreibungssync</strong> aufgebaut werden soll.
    <li>Verwenden Sie <strong>Token</strong>, um die Namen für Kursbereiche zu erstellen, und <strong>Backslashes</strong>, um Kursbereiche zu trennen, z.B.: "{org:code}\{course:semesterKey}\{course:courseClassificationKey}"</li>
    <li>Stellen Sie sicher, dass kein Name für einen Kursbereich leer bleiben kann</li>
    <li>Zeigen Sie Rohdaten aus CAMPUSonline an, um verfügbare Felder/Token zu sehen</li>';
$string['success:connected'] = 'Erfolgreich mit CAMPUSonline-Endpoint verbunden.';
$string['syncorgroles'] = 'Rollenzuweisungen in Organisationen syncen';
$string['syncthiscourse'] = 'Kurs mit CAMPUSonline synchronisieren';
$string['syncingcourse'] = 'Synchronisiere Moodle-Kurs mit CAMPUSonline...';
$string['syncthisuser'] = 'Synchronisiere Nutzer/innen mit CAMPUSonline';
$string['syncinguser'] = 'Synchronisiere Nutzerdaten mit CAMPUSonline...';
$string['syncusersonlogin'] = 'Nutzerdaten beim Login syncen';
$string['syncusersonlogin_desc'] = 'Neben dem Sync Task werden Nutzerdaten auch bei jedem Login gesynct.';
$string['task:org_sync'] = 'CAMPUSonline Organisations Sync';
$string['task:sync'] = 'CAMPUSonline-Kurse & Einschreibungen FULL Sync';
$string['task:sync_delta'] = 'CAMPUSonline-Kurse & Einschreibungen MODIFICATIONS Sync';
$string['task:user_id'] = 'CAMPUSonline User Identifikation';
$string['testconnection'] = 'Verbindung testen';
$string['testsettings'] = 'Diese Einstellungen testen';
$string['updatecourseurls'] = 'Kurs-URLs aktualisieren';
$string['updatecourseurls_desc'] = 'Schreibt die Moodle-Kurs-URL jedes Mal nach CAMPUSonline zurück, wenn ein Kurs synchronisiert wird. Normalerweise wird dies nur bei der Kurserstellung durchgeführt. Wenn etwas schiefgegangen ist, kann man das aktivieren, aber aus Performancegründen sollte diese Einstellung langfristig deaktiviert bleiben.';
$string['updateexistingcourses'] = 'Vorhandene Kurse aktualisieren';
$string['updateexistingcourses_desc'] = 'Erlaubt diesem Tasks, Namen oder Kursbereiche bestehender Moodle-Kurse zu ändern, wenn sie sich in CAMPUSonline ändern.';
$string['usercount'] = 'Rohdaten für {$a} Benutzer/innen:';
$string['usercount_syncdata'] = 'Vorschau von Daten für {$a} Benutzer/innen:';
$string['useridsettings'] = 'Identifikation bestehender Benutzer/innen';
$string['useridsettings_desc'] = '<ul>
    <li>Dieser Sync Task verknüpft bestehende Moodle-Nutzer/innen mit CAMPUSonline Personen. Standardmäßig wird dieser Task nicht geplant ausgeführt, und sollte nur manuell gestartet werden</li>
    <li>Es wird der CAMPUSonline person-identifiers/mappings Endpoint benutzt, um die <strong>Person UID</strong>mittels einer beliebigen anderen ID zu ermitteln</li>
    <li>Sobald ein/e Nutzer/in in CAMPUSonline gefunden wird, wird das Benutzerprofilfeld <strong>campusonline_person_uid</strong> mit der CAMPUSonline Person UID befüllt (dieses Feld wird bei der Plugin-Installation erstellt)</li></ul>';
$string['usermoodlefield'] = 'Benutzerdefiniertes Feld als Fallback für die Useridentifikation';
$string['usermoodlefield_desc'] = 'Wenn ein/e Nutzer/in auf keine andere Weise gefunden wird (siehe oben), wird dieses Feld verwendet, um den/die Nutzer/in in Moodle zu finden.';
$string['usersyncsettings'] = 'Nutzerdaten Sync';
$string['usersyncsettings_desc'] = '<ul>
    <li>Nutzer/innen können bei der Anmeldung automatisch gesynct werden, oder manuell über den Link <strong>Diese/n Nutzer/in mit CAMPUSonline syncen</strong> auf der Benutzerprofil Seite.</li>
    <li>Die konfigurierten Feldwerte werden auch für <strong>Benutzererstellung</strong> durch den Kurs Sync verwendet</li>
    <li><strong>Benutzernamen müssen eindeutig</strong> sein und Felder müssen mit gültigen Werten für ihre jeweiligen Feldtypen gefüllt sein, da sonst Fehler bei der Benutzererstellung auftreten!</li>
    <li>Folgende Felder dürfen <strong>nicht leer</strong> sein, sonst schlägt die Benutzererstellung fehl: user_auth, user_password, user_username, user_email</li>
    <li>CAMPUSonline <strong>Personen-UID</strong>, <strong>Studenten-UID</strong> und <strong>Mitarbeiter-UID</strong> werden automatisch in den entsprechenden Benutzerprofilfeldern synchronisiert</li>
    <li>Klicken Sie auf <strong>Token und Rohdaten anzeigen</strong>, um verfügbare Felder/Token zu sehen</li></ul>';
$string['usersyncsingle'] = 'Diese/n Nutzer/innen mit CAMPUSonline synchronisieren';
$string['viewlogs'] = 'Logs anzeigen';
$string['warningsanderrors'] = 'Warnings und Errors';
