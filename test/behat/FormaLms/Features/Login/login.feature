Feature: Login
  As a website user
  I need to be able to login into the app

  @forma_login
  Scenario: Login ok
    Given I am logged as "alessandro" with password "ciaociao"
    Then the url should match "appLms/index.php"

  Scenario: login ko
    Given I am logged as "alessandro" with password "pwd_errata"
    Then I should see "Accesso negato"

  Scenario: logout ok
    Given I am logged as "alessandro" with password "ciaociao"
    Given I am logged out
    Then I should see "Login"