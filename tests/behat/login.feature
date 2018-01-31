@auth @auth_saml2 @javascript
Feature: Login
  In order to allow single sign on
  As a SAML2 user
  I need to be able to login into Moodle

  Scenario: Use Moodle Login if SAML2 is disabled
    Given the authentication plugin saml2 is disabled         # auth_saml2
    When I go to the login page                               # auth_saml2
    Then I should see "Acceptance test site"
    And I should see "Log in"
    But I should not see "A service has requested you to authenticate yourself"
