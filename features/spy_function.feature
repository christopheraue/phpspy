Feature: Spy for functions
  As someone testing php code
  I want to be able to record calls to functions
  In order to analyze these calls later.

  Background:
    Given There is a function called "id" defined
      And There is a spy "spy" spying on function "id"

  Scenario: Spying on a function leaves its behavior intact
    When "id" is called with: 1
    Then It should have the result: 1

  Scenario Outline: Tracking the correct amount of calls
    When "id" is called <n> times with: 1
    Then "spy" should have tracked <n> calls

  Examples:
    | n |
    | 0 |
    | 1 |
    | 5 |

  Scenario Outline: Getting individual calls of functions
    When "id" is called with: 0
      And "id" is called with: 1
      And "id" is called with: 2
    Then The <m>th requested call tracked by "spy" should be its <n>th tracked call

  Examples:
    |  m | n |
    | -3 | 0 |
    | -2 | 1 |
    | -1 | 2 |
    |  0 | 0 |
    |  1 | 1 |
    |  2 | 2 |

  Scenario Outline: Getting the arguments a function was called with
    When "id" is called with: 1, 2
    Then The call tracked by "spy" received the argument "<arg>" at position <argIdx>

  Examples:
    | argIdx | arg |
    |   -2   |  1  |
    |   -1   |  2  |
    |    0   |  1  |
    |    1   |  2  |

  Scenario: Getting the result of a function
    When "id" is called with: 1
    Then The call tracked by "spy" returned the result "1"

  Scenario: Functions are called in no context
    When "id" is called with: 1
    Then The call tracked by "spy" was in the context of "null"

  Scenario: Resetting a spy deletes all tracked calls
    Given "id" is called 3 times with: 1
    When "spy" is reset
    Then "spy" should have tracked 0 calls

  Scenario: Killing a spy that spied on a method leaves its behavior intact
    When "spy" is killed
      And "id" is called with: 1
    Then It should have the result: 1