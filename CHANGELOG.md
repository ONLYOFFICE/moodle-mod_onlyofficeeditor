# Change Log

## 6.1.0
## Added
- add docs check connection button in settings to test connectivity with the document server
- add admin settings validation for document server related fields
- ca, cs, da, hu, id, ro, sq, ur language templates

## Changed
- rewrite admin settings validation logic to validate only for input validity
- fix unsuccessful file rename
- fix unsupported file upload
- fix approval blockers: namespace collisions, superglobals usage
- fix incorrect disabling of certificate verification in curl

## 6.0.1
## Added
- fi, he, no, sl language templates

## Changed
- fix JWT header issue
- remove trailing slashes from setting urls after update

## 6.0.0
## Added
- link to docs cloud
- certificate verification setting
- advanced server settings for specifying internal addresses
- check the url address of the save as document

## Changed
- replace docxf with pdf as a form template
- fix exit full screan
- disable scrolling page to editor
- disable chat in editor for guest

## 4.4.0
## Changed
- fixed saveas dialog
- support moodle v4.3

## Added
- desktop mode
- disable plugins setting
- macro launch setting
- si-LK, ar-SA and sr-Latn-RS empty file template
- service for checking editor versions

## 4.1.0
## Added
- support Moodle AssignSubmission ONLYOFFICE Integration plugin
- the ability to change the JWT header

## 3.0.0
## Changed
- redesign to moodle 4.*

## 2.2.0
## Added
- add protect option for editor
- add ability to open editor in fullscreen mode
- add mentions in comments in editor
- "save as" in editor
- add ability to create activity with empty file
- add favicon on editor page
- editor interface customization
- de, es, fr, it, ja, ru, zh_cn translations

## Changed
- set editor interface language
- fixes building backup and restore

## 2.1.0
## Added
- backup api
- privacy api

## Changed
- fixes according to markets code prechecks

## 2.0.0
## Changed
- fixes according to markets code prechecks
- renaming module name

## 1.0.0
## Changed
- fix saving intermediate versions when editing (forcesave)
