Feature: Spy for object methods
  As someone testing php code
  I want to be able to record calls to methods of objects
  In order to analyze these calls later.

  Background:
    Given There is an object "vip" derived from class "Vip"

  Scenario: Spying on a method leaves its behavior intact
    Given There is a spy "spy" spying on method "tellSecret" of "Vip"
    When "vip" tells the secret: meaning of life = 42
    Then It should have the result: meaning of life = 42

  Scenario Outline: Tracking the correct amount of calls
    Given There is a spy "spy" spying on method "learnSecret" of "Vip"
    When "vip" learns <n> secrets: secret
    Then "spy" should have tracked <n> calls

    Examples:
      | n |
      | 0 |
      | 1 |
      | 5 |

  Scenario Outline: Getting individual calls of methods
    Given There is a spy "spy" spying on method "learnSecret" of "Vip"
    When "vip" learns the secret: 0
      And "vip" learns the secret: 1
      And "vip" learns the secret: 2
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
    Given There is a spy "spy" spying on method "learnSecret" of "Vip"
    When "vip" learns the secret: secret, source
    Then The call tracked by "spy" received the argument "<arg>" at position <argIdx>

    Examples:
      | argIdx |     arg    |
      |   -2   |  secret 0  |
      |   -1   |  source 0  |
      |    0   |  secret 0  |
      |    1   |  source 0  |

  Scenario: Getting the result of a method
    Given There is a spy "spy" spying on method "tellSecret" of "Vip"
    When "vip" tells the secret: meaning of life = 42
    Then The call tracked by "spy" returned the result "meaning of life = 42"

  Scenario: Getting the context a method was called in
    Given There is a spy "spy" spying on method "tellSecret" of "Vip"
    When "vip" tells the secret: secret
    Then The call tracked by "spy" was in the context of "vip"

  Scenario: Resetting a spy deletes all tracked calls
    Given There is a spy "spy" spying on method "tellSecret" of "Vip"
      And "vip" tells 3 secrets: secret
    When "spy" is reset
    Then "spy" should have tracked 0 calls

  Scenario: Killing a spy that spied on a method leaves its behavior intact
    Given There is a spy "spy" spying on method "tellSecret" of "Vip"
    When "spy" is killed
    And "vip" tells the secret: meaning of life = 42
    Then It should have the result: meaning of life = 42