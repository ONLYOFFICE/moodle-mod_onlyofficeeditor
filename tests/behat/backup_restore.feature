@mod @mod_onlyofficeeditor
Feature: An ONLYOFFICE activity survives a course backup and restore
  In order to migrate ONLYOFFICE activities between courses
  As an administrator
  I need the activity to be preserved through the standard backup and restore flow

  Background:
    Given the following "courses" exist:
      | fullname              | shortname | category |
      | Behat acceptance test | BEHAT-1   | 0        |
    And the following "activities" exist:
      | activity         | course  | name        |
      | onlyofficeeditor | BEHAT-1 | Lesson plan |
    And the following config values are set as admin:
      | enableasyncbackup | 0 |

  @javascript
  Scenario: Activity is preserved through a course backup and restore
    Given I log in as "admin"
    And I backup "Behat acceptance test" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    When I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Restored acceptance test |
    Then I am on the "Restored acceptance test copy 1" course page
    And I should see "Lesson plan"
