@auth @auth_saml2 @javascript
Feature: SAML2 settings
  In order to handle flagged accounts
  As an administrator
  I need to change flagged account settings in Moodle

  Scenario: I can navigate to the settings page
    Given the authentication plugin saml2 is enabled                                    # auth_saml2
    And I am an administrator                                                           # auth_saml2
    When I navigate to "SAML2" node in "Site administration > Plugins > Authentication"
    Then I should see "Flagged account blocking / redirection"
    And I should see "Redirect or display message to SAML2 logins based on defined flag"

  Scenario Outline: I can change the Flagged response type options
    Given the authentication plugin saml2 is enabled                   # auth_saml2
    And I am an administrator                                          # auth_saml2
    And I am on the saml2 settings page                                # auth_saml2
    When I change the setting "Flagged response type" to "<Option>"    # auth_saml2
    And I press "Save changes"
    Then I go to the saml2 settings page again                         # auth_saml2
    And the setting "Flagged response type" should be "<Option>"       # auth_saml2
    Examples:
      | Option                             |
      | Do not respond (off)               |
      | Display custom message             |
      | Redirect to external URL           |

  Scenario: I can use https URLs for the Redirect URL mapping
    Given the authentication plugin saml2 is enabled                       # auth_saml2
    And I am an administrator                                              # auth_saml2
    And I am on the saml2 settings page                                    # auth_saml2
    When I change the setting "Redirect URL" to "https://www.google.com"   # auth_saml2
    And I press "Save changes"
    Then I go to the saml2 settings page again                             # auth_saml2
    And the setting "Redirect URL" should be "https://www.google.com"      # auth_saml2

  Scenario: I can not use URLs which are not http or https for the Redirect URL mapping
    Given the authentication plugin saml2 is enabled                       # auth_saml2
    And I am an administrator                                              # auth_saml2
    And I am on the saml2 settings page                                    # auth_saml2
    When I change the setting "Redirect URL" to "www.google.com"           # auth_saml2
    And I press "Save changes"
    Then I should see "Some settings were not changed due to an error."