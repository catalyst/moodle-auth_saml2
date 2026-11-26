@auth @auth_saml2 @javascript
Feature: Testing manageidps in auth_saml2
  In order to manage IdPs
  As admin user
  I should be able to see and use and filter the IdP table and edit the IdPs

  Scenario: Edit the Idps
    Given the authentication plugin saml2 is enabled  # auth_saml2
    And the mock SAML IdP is configured               # auth_saml2
    And I log in as "admin"
    When I visit "/auth/saml2/manageidps.php"
    Then I should see "Manage available Identity Providers"
    And I should see "Login via SAML2" in the "auth_saml2_idps" "table"
    And I should see "http://webserver/auth/saml2/tests/fixtures/mockidp/idpmetadata.php" in the "auth_saml2_idps" "table"
    And I should see "http://webserver/auth/saml2/tests/fixtures/mockidp/idpmetadata.php" in the "Login via SAML2" "table_row"
    And I click on "Edit" "button" in the "Login via SAML2" "table_row"
    Then I should see "Edit Identity Provider: Login via SAML2"
    # Fill fields in the modal form.
    And I set the field "displayname" to "Custom display name"
    And I set the field "whitelist" to "10.0.0.0/8"
    And I set the field "defaultidp" to "1"
    And I set the field "adminidp" to "0"
    And I press "Save changes"
    And I reload the page
    Then I should see "Custom display name" in the "auth_saml2_idps" "table"

Scenario: Filter the Idps
    Given the authentication plugin saml2 is enabled  # auth_saml2
    And the mock SAML IdP is configured               # auth_saml2
    And I log in as "admin"
    When I visit "/auth/saml2/manageidps.php"
    And I set the field "Filter type" to "Active"
    And I wait "1" seconds
    And I set the field "Active" to "Yes"
    And I press "Apply filters"
    Then I should see "Login via SAML2" in the "auth_saml2_idps" "table"
    And I set the field "Active" to "No"
    And I press "Apply filters"
    Then I should see "Nothing to display"
    And I press "Clear filters"
    When I click on "Deactivate Identity Provider" "button" in the "Login via SAML2" "table_row"
    And I set the field "Filter type" to "Active"
    And I wait "1" seconds
    And I set the field "Active" to "No"
    And I press "Apply filters"
    Then I should see "Login via SAML2" in the "auth_saml2_idps" "table"

Scenario: Default IdP filter reflects changes made in Edit modal
  Given the authentication plugin saml2 is enabled  # auth_saml2
  And the mock SAML IdP is configured               # auth_saml2
  And I log in as "admin"
  When I visit "/auth/saml2/manageidps.php"

  # Initial check: Default IdP filter 'Yes' should find no IdPs.
  And I set the field "Filter type" to "Default IdP"
  And I wait "1" seconds
  And I set the field "Default IdP" to "Yes"
  And I press "Apply filters"
  Then I should see "Nothing to display"

  # Clear filters and ensure table is back.
  And I press "Clear filters"
  Then I should see "Login via SAML2" in the "auth_saml2_idps" "table"

  # Edit the IdP to set defaultidp = 1.
  And I click on "Edit" "button" in the "Login via SAML2" "table_row"
  Then I should see "Edit Identity Provider: Login via SAML2"
  And I set the field "defaultidp" to "1"
  And I press "Save changes"

  # Now the Default IdP filter 'Yes' should show the IdP.
  And I set the field "Filter type" to "Default IdP"
  And I wait "1" seconds
  And I set the field "Default IdP" to "Yes"
  And I press "Apply filters"
  Then I should see "Login via SAML2" in the "auth_saml2_idps" "table"

Scenario: 'For admin users only' filter reflects changes made in Edit modal
  Given the authentication plugin saml2 is enabled  # auth_saml2
  And the mock SAML IdP is configured               # auth_saml2
  And I log in as "admin"
  When I visit "/auth/saml2/manageidps.php"

  # Initial filter: 'For admin users only = Yes' should show nothing.
  And I set the field "Filter type" to "For admin users only"
  And I wait "1" seconds
  And I set the field "For admin users only" to "Yes"
  And I press "Apply filters"
  Then I should see "Nothing to display"

  # Clear filters and confirm the table.
  And I press "Clear filters"
  Then I should see "Login via SAML2" in the "auth_saml2_idps" "table"

  # Edit the IdP to set adminidp = 1.
  And I click on "Edit" "button" in the "Login via SAML2" "table_row"
  Then I should see "Edit Identity Provider: Login via SAML2"
  And I set the field "adminidp" to "1"
  And I press "Save changes"
  And I reload the page

  # Now 'For admin users only = Yes' should show the IdP.
  And I set the field "Filter type" to "For admin users only"
  And I wait "1" seconds
  And I set the field "For admin users only" to "Yes"
  And I press "Apply filters"
  Then I should see "Login via SAML2" in the "auth_saml2_idps" "table"

Scenario: 'Allow list enabled' filter reflects whitelist changes
  Given the authentication plugin saml2 is enabled  # auth_saml2
  And the mock SAML IdP is configured               # auth_saml2
  And I log in as "admin"
  When I visit "/auth/saml2/manageidps.php"

  # Initial state: Allow list enabled = Yes should show nothing.
  And I set the field "Filter type" to "Allow list enabled"
  And I wait "1" seconds
  And I set the field "Allow list enabled" to "Yes"
  And I press "Apply filters"
  Then I should see "Nothing to display"

  # Clear filters.
  And I press "Clear filters"
  Then I should see "Login via SAML2" in the "auth_saml2_idps" "table"

  # Edit the IdP: set whitelist to something non-empty.
  And I click on "Edit" "button" in the "Login via SAML2" "table_row"
  Then I should see "Edit Identity Provider: Login via SAML2"
  And I set the field "whitelist" to "10.0.0.0/8"
  And I press "Save changes"
  And I reload the page

  # Now Allow list enabled = Yes should show the IdP.
  And I set the field "Filter type" to "Allow list enabled"
  And I wait "1" seconds
  And I set the field "Allow list enabled" to "Yes"
  And I press "Apply filters"
  Then I should see "Login via SAML2" in the "auth_saml2_idps" "table"

  Scenario: View multiple Idps
    Given the authentication plugin saml2 is enabled  # auth_saml2
    And "100" mock SAML IdPs are configured   # auth_saml2
    And I log in as "admin"
    When I visit "/auth/saml2/manageidps.php"
    Then I should see "Manage available Identity Providers"
    And I should see "IdP 1" in the "auth_saml2_idps" "table"
    And I should see "IdP 2" in the "auth_saml2_idps" "table"
    And I should see "IdP 10" in the "auth_saml2_idps" "table"
    And I should see "Show all 100"

  Scenario: Activate and filter some out of many Idps
    Given the authentication plugin saml2 is enabled  # auth_saml2
    And "100" mock SAML IdPs are configured   # auth_saml2
    And I log in as "admin"
    When I visit "/auth/saml2/manageidps.php"
    And I should see "Manage available Identity Providers"
    And I should see "IdP 1" in the "auth_saml2_idps" "table"
    And I should see "IdP 2" in the "auth_saml2_idps" "table"
    And I should see "IdP 3" in the "auth_saml2_idps" "table"
    And I should see "IdP 4" in the "auth_saml2_idps" "table"
    And I should see "Show all 100"
    And I click on "Activate Identity Provider" "button" in the "IdP 1" "table_row"
    And I click on "Activate Identity Provider" "button" in the "IdP 4" "table_row"
    And I set the field "Filter type" to "Active"
    And I wait "1" seconds
    And I set the field "Active" to "Yes"
    And I press "Apply filters"
    Then I should see "IdP 1" in the "auth_saml2_idps" "table"
    And I should not see "IdP 2" in the "auth_saml2_idps" "table"
    And I should not see "IdP 3" in the "auth_saml2_idps" "table"
    And I should see "IdP 4" in the "auth_saml2_idps" "table"

  Scenario: Edit and filter some out of many Idps
    Given the authentication plugin saml2 is enabled  # auth_saml2
    And "100" mock SAML IdPs are configured   # auth_saml2
    And I log in as "admin"
    When I visit "/auth/saml2/manageidps.php"
    And I should see "Manage available Identity Providers"
    And I should see "IdP 1" in the "auth_saml2_idps" "table"
    And I should see "IdP 2" in the "auth_saml2_idps" "table"
    And I should see "IdP 3" in the "auth_saml2_idps" "table"
    And I should see "IdP 4" in the "auth_saml2_idps" "table"
    And I should see "Show all 100"
    And I click on "Edit" "button" in the "IdP 1" "table_row"
    Then I should see "Edit Identity Provider: IdP 1"
    And I set the field "defaultidp" to "1"
    And I press "Save changes"
    And I click on "Edit" "button" in the "IdP 2" "table_row"
    Then I should see "Edit Identity Provider: IdP 2"
    And I set the field "defaultidp" to "1"
    And I press "Save changes"
    And I set the field "Filter type" to "Default IdP"
    And I wait "1" seconds
    And I set the field "Default IdP" to "Yes"
    And I press "Apply filters"
    Then I should see "IdP 1" in the "auth_saml2_idps" "table"
    And I should see "IdP 2" in the "auth_saml2_idps" "table"
    And I should not see "IdP 3" in the "auth_saml2_idps" "table"
    And I should not see "IdP 4" in the "auth_saml2_idps" "table"
