# CAMPUSonline enrolment sync

This plugin syncs courses, enrolments and (on demand) users from CAMPUSoline.

## Official Moodle Plugin Directory

- This plugin is also available in the official Moodle Plugin Directory.
- https://moodle.org/plugins/enrol_campusonline

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


