@auth @auth_saml2 @javascript
Feature: SAML2 Dual Login
  In order use Moodle login or SAML2 login
  As a user
  I need to login into SAML2 or Moodle depending on the Dual Login setting

  Scenario: If dual login is "no", redirect to IDP
    Given the authentication plugin saml2 is enabled        # auth_saml2
    And the saml2 setting "Dual Login" is set to "no"       # auth_saml2
    When I go to the login page                             # auth_saml2
    Then I should see "A service has requested you to authenticate yourself"

  Scenario: If dual login is "no", I can bypass the saml2 redirect
    Given the authentication plugin saml2 is enabled        # auth_saml2
    And the saml2 setting "Dual Login" is set to "no"       # auth_saml2
    When I go to the login page with "saml=0"               # auth_saml2
    Then I should see "Log in"
    And I should not see "A service has requested you to authenticate yourself"

  Scenario: If dual login is "yes" then I need to select SAML2
    Given the authentication plugin saml2 is enabled        # auth_saml2
    And the saml2 setting "Dual Login" is set to "yes"      # auth_saml2
    When I go to the login page                             # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"

  Scenario: If dual login is "passive" and I am not logged in SAML2, use Moodle Login
    Given the authentication plugin saml2 is enabled        # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"  # auth_saml2
    When I go to the login page                             # auth_saml2
    Then I should see "Log in"
    And I should see "Login via SAML2"
    And I should not see "A service has requested you to authenticate yourself"
    When I set the field "Username" to "admin"
    And I set the field "Password" to "admin"
    And I press "Log in"
    Then I should see "Admin User"

  Scenario: If dual login is "passive" and I am not logged in SAML2, I can still use SAML2
    Given the authentication plugin saml2 is enabled        # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"  # auth_saml2
    When I go to the login page                             # auth_saml2
    Then I should see "Log in"
    And I should see "Login via SAML2"
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "student"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    And I should see "Student Alpha"

  Scenario: If dual login is "passive" and I am logged in SAML2, auto-login
    Given the authentication plugin saml2 is enabled        # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"  # auth_saml2
    And I am already logged in as "student" in SAML2        # auth_saml2
    When I go to the login page                             # auth_saml2
    Then I should see "Student Alpha"
