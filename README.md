# CAMPUSonline enrolment sync

This plugin syncs courses, enrolments and (on demand) users from CAMPUSonline.

## Official Moodle Plugin Directory

- This plugin is also available in the official Moodle Plugin Directory.
- https://moodle.org/plugins/enrol_campusonline

## More information

- **WIKI:** For more information, please visit the 
[CAMPUSonline Moodle Plugin Wiki](https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home).
- **FAQ:** For frequently asked questions, please visit the
[CAMPUSonline Moodle Plugin FAQ](https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home/FAQ/FAQ).
- **CAMPUSonline Doku**: CAMPUSonline provides general documentation for configuring the synchronization of Learning Management Systems.
[CAMPUSonline LMS-Schnittstelle](https://www.campusonline.at/COdocumentation/documentation/new/webhelp/usage/KeyUserDokumentation/IntegrationSchnittstellen/LMSSchnittstelle/LMSSchnittstelle.html)
- **CAMPUSonline REST API**: CAMPUSonline provides a REST API and a documentation for integration with other systems. 
You can find all REST-endpoints and links to the description of the REST-endpoints which are used by the plugin in the 
[CAMPUSonline LMS-Endpoints](https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home/CAMPUSonline-Endpunkte).

## Video Tutorials
* Video on the [features of the CAMPUSOnline Moodle plugin](https://cloud.tugraz.at/index.php/s/4RSw9bRccccywJ4)
* Video on the topic of [CO Moodle user management](https://cloud.tugraz.at/index.php/s/J5SgwmBSC34mApX)

## Installation in Moodle
- Install like any other Moodle plugin (put the plugin into /enrol/campusonline in your Moodle installation)
- Enable CAMPUSonline sync in the 'manage enrol plugin' settings (/admin/settings.php?section=manageenrols)

## Installation in CAMPUSonline

- In the Wiki of this project, you will find detailed installation instructions that need to be completed in your CAMPUSonline system.
- https://gitlab.campusonline.community/community/moodle-enrol_campusonline/-/wikis/home/Campusonline-Installation

## Configurations
- Configure the plugin on its configuration page, and use the in-built preview functions to fine-tune your sync settings, before enabling the sync tasks.
- It is recommended to run a full sync manually or weekly, and then schedule only the modification sync task, to improve performance.
- Read the info on the settings page carefully, everything you need should be provided there.

## Enrolment sync

- CAMPUSonline enrolment sync will only remove roles and suspend enrolments of its own type.
- If users are added to the course manually (or with any other enrolment method), CAMPUSonline sync will not touch those role assignments or enrolments.


