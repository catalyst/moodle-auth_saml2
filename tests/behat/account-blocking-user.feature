@auth @auth_saml2 @javascript
Feature: SAML2 flagged accounts login
  In order to have correct Moodle access
  As a user
  I need to have my account checked for group restrictions when logging in through SAML2 service provider

  Scenario: If my account is blocked and I log in via SAML2 I should not have Moodle access
    Given the authentication plugin saml2 is enabled                                              # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                                        # auth_saml2
    And the saml2 setting "Groups Attribute" is set to "groups"                                   # auth_saml2
    And the saml2 setting "Restricted Groups" is set to "block"                                   # auth_saml2
    And the saml2 setting "Allowed Groups" is set to "allow"                                      # auth_saml2
    And the saml2 setting "Account blocking response type" is set to "Display custom message"     # auth_saml2
    When I go to the login page                                                                   # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "studentflagged"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "You are not logged in."
    And I should see "You are logged in to your identity provider however, this account has limited access to Moodle, please contact your administrator for more details."

  Scenario: If my account is blocked and redirect page is set, on SAML2 login I should see the redirect page
    Given the authentication plugin saml2 is enabled                                              # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                                        # auth_saml2
    And the saml2 setting "Groups Attribute" is set to "groups"                                   # auth_saml2
    And the saml2 setting "Restricted Groups" is set to "block"                                   # auth_saml2
    And the saml2 setting "Allowed Groups" is set to "allow"                                      # auth_saml2
    And the saml2 setting "Account blocking response type" is set to "Redirect to external URL"   # auth_saml2
    And the saml2 setting "Redirect URL" is set to "https://en.wikipedia.org"                     # auth_saml2
    When I go to the login page                                                                   # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "studentflagged"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "Wikipedia"
    And I should not see "Moodle"

  Scenario: If my account is blocked and response message is set, on SAML2 login I should see the response message
    Given the authentication plugin saml2 is enabled                                              # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                                        # auth_saml2
    And the saml2 setting "Groups Attribute" is set to "groups"                                   # auth_saml2
    And the saml2 setting "Restricted Groups" is set to "block"                                   # auth_saml2
    And the saml2 setting "Allowed Groups" is set to "allow"                                      # auth_saml2
    And the saml2 setting "Account blocking response type" is set to "Display custom message"     # auth_saml2
    And the saml2 setting "Response message" is set to "No access"                                # auth_saml2
    When I go to the login page                                                                   # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "studentflagged"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "No access"

  Scenario: If my account is blocked, but group restrictions is turned off, I should always be able to log in to Moodle
    Given the authentication plugin saml2 is enabled                                              # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                                        # auth_saml2
    And the saml2 setting "Groups Attribute" is set to ""                                         # auth_saml2
    And the saml2 setting "Restricted Groups" is set to "block"                                   # auth_saml2
    And the saml2 setting "Allowed Groups" is set to "allow"                                      # auth_saml2
    And the saml2 setting "Account blocking response type" is set to "Display custom message"     # auth_saml2
    When I go to the login page                                                                   # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "studentflagged"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "Student Bravo"

  Scenario: If my account is not blocked, I should be able to log into Moodle
    Given the authentication plugin saml2 is enabled                                              # auth_saml2
    And the saml2 setting "Dual Login" is set to "passive"                                        # auth_saml2
    And the saml2 setting "Groups Attribute" is set to "groups"                                   # auth_saml2
    And the saml2 setting "Restricted Groups" is set to "block"                                   # auth_saml2
    And the saml2 setting "Allowed Groups" is set to "allow"                                      # auth_saml2
    And the saml2 setting "Account blocking response type" is set to "Display custom message"     # auth_saml2
    And the saml2 setting "Response message" is set to "No access"                                # auth_saml2
    When I go to the login page                                                                   # auth_saml2
    And I follow "Login via SAML2"
    Then I should see "A service has requested you to authenticate yourself"
    And I set the field "Username" to "student"
    And I set the field "Password" to "studentpass"
    And I press "Login"
    Then I should see "Student Alpha"