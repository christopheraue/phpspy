Feature: Spy for object methods
  As someone testing php code
  I want to be able to record calls to methods of objects
  In order to analyze these calls later.

  Scenario Outline: Using getCallCount to get the number of calls
    Given Vip learns <n> secrets
    When I call the spy's method getCallCount
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

  Scenario Outline: Using getCall to get tracked calls
    Given Vip learns 3 secrets
    When I get the spied call with index <callIdx>
      And I get the call's argument with index 0
      And echo the result
    Then I should get:
      """
      secret <realCallIdx>
      """

  Examples:
    | callIdx | realCallIdx |
    |   -3    |      0      |
    |   -2    |      1      |
    |   -1    |      2      |
    |    0    |      0      |
    |    1    |      1      |
    |    2    |      2      |

  Scenario Outline: Using getCall->getArg to get arguments of each call
    Given Vip learns 1 secret
    When I get the spied call with index 0
      And I get the call's argument with index <argIdx>
      And echo the result
    Then I should get:
      """
      <arg>
      """

    Examples:
      | argIdx |     arg    |
      |   -2   |  secret 0  |
      |   -1   |  source 0  |
      |    0   |  secret 0  |
      |    1   |  source 0  |

  Scenario: Using getCall->getResult to get the return value of a call
    Given Vip tells the secret "meaning of life = 42"
      When I get the spied call with index 0
      And I get the call's return value
      And echo the result
    Then I should get:
      """
      meaning of life = 42
      """