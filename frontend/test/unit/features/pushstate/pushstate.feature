@pushstate
  Feature: PushState

    @pushstate-change
    Scenario: I want change title when click a button
      Given I have a title content
      And I have a test button for change url
      When I click button
      Then I should have a new title content
