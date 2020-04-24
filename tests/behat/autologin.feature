@auth @auth_saml2 @javascript
Feature: Automatically log in
  In order to have correct Moodle access on pages that allow public access
  As a user
  I should be automatically logged in if I am logged into the IdP

  Scenario: Autologin on first request in session (logged in)
    Given the authentication plugin saml2 is enabled # auth_saml2
    And the mock SAML IdP is configured # auth_saml2
    And the following "users" exist:
      | username | auth  | firstname | lastname |
      | student1 | saml2 | Eigh      | Person   |
    And the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    And the following "course enrolments" exist:
      | user     | course | role |
      | student1 | C1     | student |
    And the following config values are set as admin:
      | auth      | saml2 |            |
      | autologin | 1     | auth_saml2 |
    When I am on site homepage
    And the mock SAML IdP allows passive login with the following attributes: # auth_saml2
      | uid | student1 |
    Then I should see "Course 1"
    And I should see "Eigh Person" in the ".userbutton" "css_element"

    # Future requests should not contact the IdP (obviously, because logged in).
    When I follow "Course 1"
    Then I should see "Course 1"
    And I should see "Participants"

  Scenario: Autologin on first request in session (not logged in)
    Given the authentication plugin saml2 is enabled # auth_saml2
    And the mock SAML IdP is configured # auth_saml2
    And the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    And the following config values are set as admin:
      | auth      | saml2 |            |
      | autologin | 1     | auth_saml2 |
    When I am on site homepage
    And the mock SAML IdP does not allow passive login # auth_saml2
    Then I should see "You are not logged in."

    # Future requests should not contact the IdP.
    When I follow "Course 1"
    Then I should see "Forgotten your username or password?"

  Scenario: Autologin on cookie change
    Given the authentication plugin saml2 is enabled # auth_saml2
    And the mock SAML IdP is configured # auth_saml2
    And the following "users" exist:
      | username | auth  | firstname | lastname |
      | student1 | saml2 | Eigh      | Person   |
    And the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    And the following "course enrolments" exist:
      | user     | course | role |
      | student1 | C1     | student |
    And the following config values are set as admin:
      | auth            | saml2 |            |
      | autologin       | 2     | auth_saml2 |
      | autologincookie | frog  | auth_saml2 |

    # No login attempt initially.
    When I am on site homepage
    Then I should see "You are not logged in."

    # Changing the cookies results in a login attempt.
    When the cookie "frog" is set to "Kermit" # auth_saml2
    And I am on site homepage
    And the mock SAML IdP does not allow passive login # auth_saml2
    Then I should see "You are not logged in."

    # No login attempt on another page request.
    When I am on site homepage
    Then I should see "You are not logged in."

    # Changing cookies again, there will be another login attempt.
    When the cookie "frog" is set to "Mr Toad" # auth_saml2
    And I am on site homepage
    And the mock SAML IdP allows passive login with the following attributes: # auth_saml2
      | uid | student1 |
    Then I should see "Eigh Person" in the ".userbutton" "css_element"

    # No login attempt on another page request, even if the cookie changes
    # or is removed, because the user is logged in now.
    When the cookie "frog" is set to "Kermit" # auth_saml2
    And I am on site homepage
    Then I should see "Eigh Person" in the ".userbutton" "css_element"
    When the cookie "frog" is removed # auth_saml2
    And I am on site homepage
    Then I should see "Eigh Person" in the ".userbutton" "css_element"

  Scenario: Situations which are excluded from autologin
    Given the authentication plugin saml2 is enabled # auth_saml2
    And the mock SAML IdP is configured # auth_saml2
    And the following "users" exist:
      | username | auth  | firstname | lastname |
      | student1 | saml2 | Eigh      | Person   |
    And the following config values are set as admin:
      | auth            | saml2 |            |
      | autologin       | 2     | auth_saml2 |
      | autologincookie | frog  | auth_saml2 |
    And I am on site homepage

    # With this config, changing the cookie would usually result in an autologin attempt.
    When the cookie "frog" is set to "Kermit" # auth_saml2

    # Situation 1: Autologin does not run on login screens.
    And I follow "Log in"
    Then I should see "You are not logged in."

    # Situation 2: Autologin does not run if turned off (obviously).
    When the following config values are set as admin:
      | autologin | 0 | auth_saml2 |
    And I am on site homepage
    Then I should see "You are not logged in."

    # Situation 3: Autologin does not run if the plugin is not enabled.
    When the following config values are set as admin:
      | autologin | 2      | auth_saml2 |
      | auth      | manual |            |
    And I am on site homepage
    Then I should see "You are not logged in."

    # Set up the homepage so that we can test POST requests
    When I log in as "admin"
    And I follow "Site home"
    And I navigate to "Edit settings" in current page administration
    And I set the field "summary" to "<form method='post' action='.'><div><button type='submit'>PostTest</button></div></form>"
    And I press "Save changes"
    And I follow "Site home"
    And I navigate to "Turn editing on" in current page administration
    And I add the "Course/site summary" block
    And I log out

    # Situation 4: Autologin does not run on POST requests.
    When the following config values are set as admin:
      | auth | saml2 |
    And I press "PostTest"
    Then I should see "You are not logged in."

    # Finally, just confirm we have things set up right by trying a normal GET request.
    When I am on site homepage
    And the mock SAML IdP allows passive login with the following attributes: # auth_saml2
      | uid | student1 |
    Then I should see "Eigh Person"
