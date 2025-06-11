# CAMPUSonline Einschreibungs-Synchronisation
Dieses Plugin synchronisiert Kurse, Einschreibungen und (auf Wunsch) Benutzer aus CAMPUSonline.

## Offizielles Moodle-Plugin-Verzeichnis
- Dieses Plugin ist auch im offiziellen Moodle-Plugin-Verzeichnis verfügbar.
- https://moodle.org/plugins/enrol_campusonline

## Weitere Informationen
- **WIKI:** Weitere Informationen finden Sie im [CAMPUSonline Moodle Plugin Wiki](https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home).
- **FAQ:** Häufig gestellte Fragen finden Sie im [CAMPUSonline Moodle Plugin FAQ](https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home/FAQ/FAQ).
- **CAMPUSonline Doku:** CAMPUSonline stellt eine allgemeine Dokumentation zur Konfiguration der Synchronisierung von Lernmanagementsystemen bereit. [CAMPUSonline LMS-Schnittstelle](https://www.campusonline.at/COdocumentation/documentation/new/webhelp/usage/KeyUserDokumentation/IntegrationSchnittstellen/LMSSchnittstelle/LMSSchnittstelle.html)
- **CAMPUSonline REST API:** CAMPUSonline stellt eine REST API sowie eine Dokumentation zur Integration mit anderen Systemen bereit. Alle REST-Endpunkte und Verweise auf deren Beschreibung, die vom Plugin verwendet werden, finden Sie unter
[CAMPUSonline LMS-Endpunkte](https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home/CAMPUSonline-Endpunkte).
- **Moodle Matrix Chat:** Für Fragen und Support besuchen Sie bitte den Moodle Matrix Chat: https://matrix.to/#/#moodle:matrix.campusonline.community

## Video-Tutorials
- Video zu den [Funktionen des CAMPUSonline Moodle Plugins](https://cloud.tugraz.at/index.php/s/J5SgwmBSC34mApX)
- Video zum Thema [Benutzerverwaltung in CO Moodle](https://cloud.tugraz.at/index.php/s/4RSw9bRccccywJ4)

## Installation in Moodle
- Installieren Sie das Plugin wie jedes andere Moodle-Plugin (legen Sie es im Verzeichnis /enrol/campusonline Ihrer Moodle-Installation ab).
- Aktivieren Sie die CAMPUSonline-Synchronisation in den Einstellungen unter "Einschreibungs-Plugins verwalten" (/admin/settings.php?section=manageenrols)

## Installation in CAMPUSonline
- Im Wiki dieses Projekts finden Sie eine detaillierte Installationsanleitung, die in Ihrem CAMPUSonline-System durchgeführt werden muss.
- https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home/Campusonline-Installation

## Konfigurationen
- Konfigurieren Sie das Plugin auf seiner Konfigurationsseite und nutzen Sie die integrierten Vorschaufunktionen, um Ihre Synchronisationseinstellungen fein abzustimmen, bevor Sie die Synchronisationsaufgaben aktivieren.
- Es wird empfohlen, regelmäßig manuell oder wöchentlich eine vollständige Synchronisation durchzuführen und dann nur die Modifikations-Synchronisation einzuplanen, um die Performance zu verbessern.
- Lesen Sie die Hinweise auf der Einstellungsseite sorgfältig durch – alle benötigten Informationen sollten dort bereitgestellt werden.

## Einschreibungs-Synchronisation
- Die CAMPUSonline-Einschreibungs-Synchronisation entfernt nur Rollen und setzt Einschreibungen außer Kraft, die von diesem Plugin stammen.
- Wenn Benutzer manuell oder über eine andere Einschreibemethode zum Kurs hinzugefügt wurden, werden deren Rollen oder Einschreibungen von der CAMPUSonline-Synchronisation nicht verändert.
