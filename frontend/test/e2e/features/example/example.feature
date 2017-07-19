@example
Feature: ciffi.it

  Scenario: Check ciffi.it

    Given I open Ciffi's home page
    Then the title is ""
    And the canvas animation exists