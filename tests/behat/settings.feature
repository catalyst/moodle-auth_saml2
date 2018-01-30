@auth @auth_saml2 @javascript
Feature: SAML2 settings
  In order to configure the plugin
  As an administrator
  I need to change the settings in Moodle

  Scenario: I can navigate to the settings page
    Given the authentication plugin "saml2" is enabled                                  # auth_saml2
    And I am an administrator                                                           # auth_saml2
    When I navigate to "SAML2" node in "Site administration > Plugins > Authentication"
    Then I should see "SAML2"
    And I should see "Authenticate with a SAML2 IdP"
