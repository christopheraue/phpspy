Feature: Spy for object methods
  As someone testing php code
  I want to be able to record calls to methods of objects
  In order to analyze these calls later.

  Scenario: Basic spying on an object's method
    Given I have the class "Vip"
      And I have an instance "vip" of class "Vip"
      And I set the spy "spy" on method "learnSecret" of class "Vip"
      And I call method "learnSecret" of "vip" with "'The cake is a lie.'"
    When I call method "getCallCount" of "spy"
      And echo the result
      And I call method "getLastCallArgument" of "spy" with "0"
      And echo the result
    Then I should get:
      """
      1
      'The cake is a lie.'
      """

  Scenario Outline: Using getCallCount to get get the number of calls
    Given I have the class "Vip"
      And I have an instance "vip" of class "Vip"
      And I set the spy "spy" on method "learnSecret" of class "Vip"
      And I call method "learnSecret" of "vip" with "'The cake is a lie.'" <n> times
    When I call method "getCallCount" of "spy"
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

  Scenario: Using getLastCallArgument to get arguments of a single call
    Given I have the class "Vip"
      And I have an instance "vip" of class "Vip"
      And I set the spy "spy" on method "learnSecret" of class "Vip"
      And I call method "learnSecret" of "vip" with "'The first UFO landed 1942', 'Fox Mulder'"
    When I call method "getLastCallArgument" of "spy" with "0"
      And echo the result
    When I call method "getLastCallArgument" of "spy" with "1"
      And echo the result
    Then I should get:
      """
      'The first UFO landed 1942'
      'Fox Mulder'
      """

  Scenario: Using getCallArgument to get arguments of more than one call
    Given I have the class "Vip"
      And I have an instance "vip" of class "Vip"
      And I set the spy "spy" on method "learnSecret" of class "Vip"
      And I call method "learnSecret" of "vip" with "'The cake is a lie.'"
      And I call method "learnSecret" of "vip" with "'The first UFO landed 1942'"
    When I call method "getCallArgument" of "spy" with "0, 0"
      And echo the result
      And I call method "getCallArgument" of "spy" with "1, 0"
      And echo the result
    Then I should get:
      """
      'The cake is a lie.'
      'The first UFO landed 1942'
      """