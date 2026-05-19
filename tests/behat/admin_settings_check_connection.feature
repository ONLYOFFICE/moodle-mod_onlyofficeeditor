@mod @mod_onlyofficeeditor
Feature: In the settings page admins can check connection with the Document Server
  In order to verify the connection with the Document Server
  As an administrator
  I need to use Check Docs connection button

  Background:
    Given the ONLYOFFICE Document Server connection is configured from the environment
    And I log in as "admin"
    And I visit "/admin/settings.php?section=modsettingonlyofficeeditor"

  @javascript
  Scenario: Check Docs connection reports success against a healthy Document Server
    When I press "Check Docs connection"
    Then I should see "Connection is stable"
