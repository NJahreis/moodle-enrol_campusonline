# CAMPUSonline Moodle Plugin





## Unofficial Guide to Moodle DEV

### clone moodle source
- (optional) with moodle sdk (if working) and remember location

### clone moodle-docker from github

- git clone
- set required env vars (see README of project) e.g. MOODLE_DOCKER_WWWROOT pointing to moodle src
- start.sh

- create local.yaml in moodle-docker dir
- add volume mount for plugin source

### Resources

- https://docs.moodle.org/dev/Tutorial
- https://moodledev.io/general/development/gettingstarted
- https://github.com/FMCorz/mdk/wiki/Typical-workflows
- https://github.com/moodlehq/moodle-docker
- https://moodlehq.github.io/moodle-plugin-ci/
- https://github.com/moodlehq/moodle-plugin-ci/pull/223/commits/b8f3933568b9821a64d1d54bc82c107300a63abe
