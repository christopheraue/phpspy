Feature: Spy for object methods
  As someone testing php code
  I want to be able to record calls to methods of objects
  In order to analyze these calls later.

  Background:
    Given There is an object "klass" derived from class "Klass"
      And There is a spy "spy" spying on method "id" of "Klass"

  Scenario: Spying on a method leaves its behavior intact
    When "klass" calls method "id" with: 1
    Then It should have the result: 1

  Scenario Outline: Tracking the correct amount of calls
    When "klass" calls method "id" <n> times with: 1
    Then "spy" should have tracked <n> calls

    Examples:
      | n |
      | 0 |
      | 1 |
      | 5 |

  Scenario Outline: Getting individual calls of methods
    When "klass" calls method "id" with: 0
      And "klass" calls method "id" with: 1
      And "klass" calls method "id" with: 2
    Then The <m>th requested call tracked by "spy" should be its <n>th tracked call

  Examples:
    |  m | n |
    | -3 | 0 |
    | -2 | 1 |
    | -1 | 2 |
    |  0 | 0 |
    |  1 | 1 |
    |  2 | 2 |

  Scenario: Getting the number of arguments a method was called with
    When "klass" calls method "id" with: 1, 2, 3, 4
    Then The call tracked by "spy" received 4 arguments

  Scenario Outline: Getting the arguments a method was called with
    When "klass" calls method "id" with: 1, 2
    Then The call tracked by "spy" received the argument "<arg>" at position <argIdx>

    Examples:
      | argIdx | arg |
      |   -2   |  1  |
      |   -1   |  2  |
      |    0   |  1  |
      |    1   |  2  |

  Scenario: Getting the result of a method
    When "klass" calls method "id" with: 1
    Then The call tracked by "spy" returned the result "1"

  Scenario: Getting the context a method was called in
    When "klass" calls method "id" with: 1
    Then The call tracked by "spy" was in the context of "klass"

  Scenario: Resetting a spy deletes all tracked calls
    Given "klass" calls method "id" 3 times with: 1
    When "spy" is reset
    Then "spy" should have tracked 0 calls

  Scenario: Substitute a spied method for a function
    Given There is a function called "square" defined
      And "spy" delegates calls to function "square"
    When "klass" calls method "id" with: 2
    Then It should have the result: 4
      And The call tracked by "spy" returned the result "4"

  Scenario: Substitute a spied method with a closure
    Given "spy" delegates calls to a closure
    When "klass" calls method "id" with: 2
    Then The call tracked by "spy" was in the context of "klass"
    And The call tracked by "spy" received the argument "2" at position 0
    And The call tracked by "spy" returned the result "Klass instance context"

  Scenario: Revert substitution of a spied method
    Given There is a function called "square" defined
      And "spy" delegates calls to function "square"
      And "spy" delegates calls to its actual implementation
    When "klass" calls method "id" with: 2
    Then It should have the result: 2
      And The call tracked by "spy" returned the result "2"

  Scenario: Killing a spy that spied on a method leaves its behavior intact
    When "spy" is killed
      And "klass" calls method "id" with: 1
    Then It should have the result: 1

  Scenario: Spying on an inherited method
    Given There is an object "inheritedKlass" derived from class "InheritedKlass"
    When There is a spy "spy" spying on method "id" of "InheritedKlass"
    And "inheritedKlass" calls method "id" with: 1
    Then It should have the result: 1

  Scenario: Calling the spied on function without tracking
    When "spy" calls the original function with instance "klass" with: 1
    Then It should have the result: 1
    And "spy" should have tracked 0 calls