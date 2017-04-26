@form
Feature: Form

  @form-validator-email
  Scenario Outline: I need an email validator
    Given Email to check is <address>
    Then My email should be <isvalid>

    Examples:
      | address          | isvalid |
      | alessio@ciffi.it | true    |
      | alessio@ciffi    | false   |
      | alessio@ciffi.t  | false   |
      | alessiociffi.it  | false   |
      | alessiociffiit   | false   |
      | NULL             | false   |

  @form-validator-text-required
  Scenario: I need an input text validator
    Given I have an input text
    And I leave it blank
    When I blur it
    Then I should have an error

  @form-validator-phone
  Scenario: I need an input phone validator
    Given I have an input phone
    And I leave it blank
    When I blur it
    Then I should have an error