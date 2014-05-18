Feature: Spy
  As someone testing php code
  I want to be able to record calls methods
  In order to analyze these calls later.

  Scenario: Basic spying on an object's method
    Given I have the class "Vip"
      And I have an instance "vip" of class "Vip"
      And I set the spy "spy" on method "learnSecret" of class "Vip"
      And I call method "learnSecret" of instance "vip" with "'The cake is a lie.'"
    When I call method "getCallCount" of instance "spy"
      And echo the result
      And I call method "getLastCallArgument" of instance "spy" with "0"
      And echo the result
    Then I should get:
      """
      1
      'The cake is a lie.'
      """

  Scenario Outline: Spying on an methods and track how many times it was called
    Given I have the class "Vip"
      And I have an instance "vip" of class "Vip"
      And I set the spy "spy" on method "learnSecret" of class "Vip"
      And I call method "learnSecret" of instance "vip" with "'The cake is a lie.'" <n> times
    When I call method "getCallCount" of instance "spy"
      And echo the result
    Then I should get:
      """
      <n>
      """

    Examples:
      | n |
      | 0 |
      | 1 |
      | 5 |