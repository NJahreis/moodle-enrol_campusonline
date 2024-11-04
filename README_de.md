# CAMPUSonline Enrolment
Dieses Plugin synchronisiert Kurse, Einschreibungen und (bei Bedarf) Benutzer aus CAMPUSonline.

## Offizielles Moodle Plugin-Verzeichnis
- Dieses Plugin ist auch im offiziellen Moodle Plugin-Verzeichnis verfügbar.
- https://moodle.org/plugins/enrol_campusonline

## Installation in Moodle
- Installieren Sie das Plugin wie jedes andere Moodle-Plugin (platzieren Sie es im Verzeichnis /enrol/campusonline in Ihrer Moodle-Installation).
- Aktivieren Sie die CAMPUSonline-Synchronisation in den Einstellungen für Einschreibe-Plugins (/admin/settings.php?section=manageenrols).

## Installation in CAMPUSonline
- Im Wiki dieses Projekts finden Sie detaillierte Installationsanweisungen, die in Ihrem CAMPUSonline-System abgeschlossen werden müssen.
- https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home/Campusonline-Installation

## Konfigurationen
- Konfigurieren Sie das Plugin auf der Konfigurationsseite und nutzen Sie die integrierten Vorschaufunktionen, um Ihre Synchronisationseinstellungen anzupassen, bevor Sie die Synchronisationsaufgaben aktivieren.
- Es wird empfohlen, eine vollständige Synchronisation manuell oder wöchentlich durchzuführen und anschließend nur die Modifikations-Synchronisationsaufgabe zu planen, um die Leistung zu verbessern.
- Lesen Sie die Informationen auf der Einstellungsseite sorgfältig durch, dort finden Sie alles, was Sie benötigen.

## Einschreibesynchronisation
- Der CAMPUSonline Sync entfernt nur Rollen und setzt Einschreibungen des eigenen Typs aus.
- Wenn Benutzer manuell (oder mit einer anderen Einschreibemethode) zu einem Kurs hinzugefügt werden, wird die CAMPUSonline-Synchronisation diese Rollen- oder Einschreibezuweisungen nicht ändern.