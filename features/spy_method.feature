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

  Scenario Outline: Using getCallArgument to get arguments of each call
    Given Vip learns 3 secrets
    When I call the spy's method getCallArgument with: <call>, 0
      And echo the result
      And I call the spy's method getCallArgument with: <call>, 1
      And echo the result
    Then I should get:
      """
      secret <call>
      source <call>
      """

    Examples:
      | call |
      |  0   |
      |  1   |
      |  2   |

  Scenario: Using getLastCallArgument to get the arguments of the last call
    Given Vip learns 3 secrets
    When I call the spy's method getLastCallArgument with: 0
      And echo the result
      And I call the spy's method getLastCallArgument with: 1
      And echo the result
    Then I should get:
      """
      secret 2
      source 2
      """