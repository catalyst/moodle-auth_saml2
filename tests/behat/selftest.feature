@auth @auth_saml2 @javascript
Feature: Self tests
  In order to test for known configuration issues
  As any user (not login required)
  I should be able to run self tests

  Scenario: Access the self test page
    When I go to the self-test page         # auth_saml2
    Then I should not see "Error"
