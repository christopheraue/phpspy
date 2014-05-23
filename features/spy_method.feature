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
    Then The call tracked by "spy" returned the result "meaning of life = 42"

  Scenario: Getting the context a method was called in
    When "klass" calls method "id" with: 1
    Then The call tracked by "spy" was in the context of "klass"

  Scenario: Resetting a spy deletes all tracked calls
    Given "klass" calls method "id" 3 times with: 1
    When "spy" is reset
    Then "spy" should have tracked 0 calls

  Scenario: Killing a spy that spied on a method leaves its behavior intact
    When "spy" is killed
      And "klass" calls method "id" with: 1
    Then It should have the result: 1