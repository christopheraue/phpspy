Feature: Spy for a class' static methods
  As someone testing php code
  I want to be able to record calls to static methods of classes
  In order to analyze these calls later.

  Background:
    Given There is a class "Klass"
      And There is a spy "spy" spying on method "staticId" of "Klass"

  Scenario: Spying on a static method leaves its behavior intact
    When "Klass" calls method "staticId" with: 1
    Then It should have the result: 1

  Scenario Outline: Tracking the correct amount of calls
    When "Klass" calls method "staticId" <n> times with: 1
    Then "spy" should have tracked <n> calls

    Examples:
      | n |
      | 0 |
      | 1 |
      | 5 |

  Scenario Outline: Getting individual calls of static methods
    When "Klass" calls method "staticId" with: 0
      And "Klass" calls method "staticId" with: 1
      And "Klass" calls method "staticId" with: 2
    Then The <m>th requested call tracked by "spy" should be its <n>th tracked call

  Examples:
    |  m | n |
    | -3 | 0 |
    | -2 | 1 |
    | -1 | 2 |
    |  0 | 0 |
    |  1 | 1 |
    |  2 | 2 |

  Scenario: Getting the number of arguments a static method was called with
    When "Klass" calls method "staticId" with: 1, 2, 3, 4
    Then The call tracked by "spy" received 4 arguments

  Scenario Outline: Getting the arguments a static method was called with
    When "Klass" calls method "staticId" with: 1, 2
    Then The call tracked by "spy" received the argument "<arg>" at position <argIdx>

    Examples:
      | argIdx | arg |
      |   -2   |  1  |
      |   -1   |  2  |
      |    0   |  1  |
      |    1   |  2  |

  Scenario: Getting the result of a static method
    When "Klass" calls method "staticId" with: 1
    Then The call tracked by "spy" returned the result "1"

  Scenario: Getting the context a static method was called in
    When "Klass" calls method "staticId" with: 1
    Then The call tracked by "spy" was in the context of "Klass"

  Scenario: Resetting a spy deletes all tracked calls
    Given "Klass" calls method "staticId" 3 times with: 1
    When "spy" is reset
    Then "spy" should have tracked 0 calls

  Scenario: Substitute a spied on method
    Given There is a function called "square" defined
      And "spy" delegates calls to function "square"
    When "Klass" calls method "staticId" with: 2
    Then It should have the result: 4
      And The call tracked by "spy" returned the result "4"

  Scenario: Substitute a spied method for a closure
    Given "spy" delegates calls to a closure
    When "Klass" calls method "staticId" with: 2
    Then The call tracked by "spy" was in the context of "Klass"
    And The call tracked by "spy" received the argument "2" at position 0
    And The call tracked by "spy" returned the result "Klass static context"

  Scenario: Revert substitution of a spied on method
    Given There is a function called "square" defined
      And "spy" delegates calls to function "square"
      And "spy" delegates calls to its actual implementation
    When "Klass" calls method "staticId" with: 2
    Then It should have the result: 2
      And The call tracked by "spy" returned the result "2"

  Scenario: Killing a spy that spied on a static method leaves its behavior intact
    When "spy" is killed
      And "Klass" calls method "staticId" with: 1
    Then It should have the result: 1

  Scenario: Spying on an inherited method
    Given There is a class "InheritedKlass"
    When There is a spy "spy" spying on method "staticId" of "InheritedKlass"
    And "InheritedKlass" calls method "staticId" with: 1
    Then It should have the result: 1
  
  Scenario: Calling the spied on function without tracking
    When "spy" calls the original function with: 1
    Then It should have the result: 1
    And "spy" should have tracked 0 calls