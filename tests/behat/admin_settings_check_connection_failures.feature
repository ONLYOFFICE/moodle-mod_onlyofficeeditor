@mod @mod_onlyofficeeditor
Feature: The settings page surfaces server-side failures from the Document Server connectivity check
  In order to know what is wrong with the Document Server configuration
  As an administrator
  I need a clear field-level error message for each failure path of the connectivity check

  Background:
    Given the ONLYOFFICE Document Server connection is configured from the environment

  @javascript
  Scenario: An unreachable internal URL is reported on the internal URL field
    Given the following config values are set as admin:
      | documentserverinternal | http://invalid.docs.com | onlyofficeeditor |
    And I log in as "admin"
    And I visit "/admin/settings.php?section=modsettingonlyofficeeditor"
    When I press "Check Docs connection"
    Then I should see "Unable to connect to ONLYOFFICE Docs. Please check if the server is running and accessible."

  @javascript
  Scenario: A wrong secret is reported on the secret field
    Given the following config values are set as admin:
      | documentserversecret | wrong-secret | onlyofficeeditor |
    And I log in as "admin"
    And I visit "/admin/settings.php?section=modsettingonlyofficeeditor"
    When I press "Check Docs connection"
    Then I should see "Unable to connect to ONLYOFFICE Docs. Please check if the Secret key is correct."

  @javascript
  Scenario: A wrong JWT header name is reported on the JWT header field
    Given the following config values are set as admin:
      | jwtheader | X-Wrong-Header | onlyofficeeditor |
    And I log in as "admin"
    And I visit "/admin/settings.php?section=modsettingonlyofficeeditor"
    When I press "Check Docs connection"
    Then I should see "Unable to connect to ONLYOFFICE Docs. Please check if the Authorization header is correct."
