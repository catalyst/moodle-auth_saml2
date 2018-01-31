@auth @auth_saml2 @javascript
Feature: SAML2 Dual Login
  In order use Moodle login or SAML2 login
  As a user
  I need to login into SAML2 or Moodle depending on the Dual Login setting

  Background:
    Given the authentication plugin "saml2" is enabled                            # auth_saml2
    And the following config values are set as admin:
      | idpmetadata | http://localhost:8001/saml2/idp/metadata.php | auth_saml2 |


  Scenario: If dual login is no, I should be redirected to the IDP
    Given the following config values are set as admin:
      | duallogin | 0 | auth_saml2 |
    When I go to the login page                             # auth_saml2
    Then I should see "A service has requested you to authenticate yourself"
