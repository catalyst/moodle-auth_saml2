@auth @auth_saml2 @javascript
Feature: SAML2 Login
  In order use Moodle login or SAML2 login
  As a user
  I need to login into SAML2 or Moodle depending on the Flagged Account status

  Scenario: If my account is flagged and I log in via SAML2 I should not have Moodle access
    Given the authentication plugin saml2 is enabled                                     # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                               # auth_saml2
    And the saml2 setting "Flagged response type" is set to "Display custom message"     # auth_saml2
    And the saml2 setting "Response message" is set to "No access"                       # auth_saml2
    When I go to the login page                                                          # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "studentflagged"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "You are not logged in."


  Scenario: If my account is flagged and redirect is set, on SAML2 login I should see the redirect page
    Given the authentication plugin saml2 is enabled                                     # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                               # auth_saml2
    And the saml2 setting "Flagged response type" is set to "Redirect to external URL"   # auth_saml2
    And the saml2 setting "Redirect URL" is set to "https://en.wikipedia.org"            # auth_saml2
    When I go to the login page                                                          # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "studentflagged"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "Wikipedia"
    And I should not see "Moodle"
    
  Scenario: If my account is flagged and response message is set, on SAML2 login I should see the response message
    Given the authentication plugin saml2 is enabled                                     # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                               # auth_saml2
    And the saml2 setting "Flagged response type" is set to "Display custom message"     # auth_saml2
    And the saml2 setting "Response message" is set to "No access"                       # auth_saml2
    When I go to the login page                                                          # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "studentflagged"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "No access"

  Scenario: If the account flagging is turned off, I should always be able to log in to Moodle
    Given the authentication plugin saml2 is enabled                                     # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                               # auth_saml2
    And the saml2 setting "Flagged response type" is set to "Do no respond (off)"        # auth_saml2
    When I go to the login page                                                          # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "studentflagged"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "Student Bravo"

  Scenario: If my account is not flagged, I should be able to log into Moodle
    Given the authentication plugin saml2 is enabled                                     # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                               # auth_saml2
    And the saml2 setting "Flagged response type" is set to "Display custom message"     # auth_saml2
    And the saml2 setting "Response message" is set to "No access"                       # auth_saml2
    When I go to the login page                                                          # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "student"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "Student Alpha"