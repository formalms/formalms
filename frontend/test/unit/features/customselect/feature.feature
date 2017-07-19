@customselect
Feature: Customselect

  Scenario: I want a custom select plugin
    Given I have a select with class js-customselect
    And My tag has been wrapped
    When I change select value
    Then My custom element should be filled