Feature:
  In order to get committees' information
  As a referent
  I should be able to acces committees API data

  Background:
    Given I freeze the clock to "2018-04-15"
    And the following fixtures are loaded:
      | LoadUserData                        |
      | LoadAdherentData                    |
      | LoadEventData                       |
      | LoadCommitteeMembershipHistoryData  |

  Scenario: As a non logged-in user I can not access the committee supervisors count managed by referent information
    When I am on "/api/committees/count-for-referent-area"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not access the committee supervisors count managed by referent information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/committees/count-for-referent-area"
    Then the response status code should be 403

  Scenario: As a referent I can access the committee supervisors count managed by referent information
    When I am logged as "referent@en-marche-dev.fr"
    And I am on "/api/committees/count-for-referent-area"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "committees":4,
      "members": {
        "female":1,
        "male":5,
        "total":6
      },
      "supervisors": {
        "female":1,
        "male":2,
        "total":3
      }
    }
    """

  Scenario: As a non logged-in user I can not get the most active committees in referent managed zone
    When I am on "/api/committees/top-5-in-referent-area"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not get the most active committees in referent managed zone
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/committees/top-5-in-referent-area"
    Then the response status code should be 403

  Scenario: As a referent I can get the most active committees in referent managed zone
    When I am logged as "referent@en-marche-dev.fr"
    And I am on "/api/committees/top-5-in-referent-area"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "most_active":
        [
          {"name":"En Marche Dammarie-les-Lys","events":"1"},
          {"name":"Antenne En Marche de Fontainebleau","events":"1"},
          {"name":"En Marche - Suisse","events":"1"}
        ],
      "least_active":
      [
        {"name":"En Marche - Suisse","events":"1"},
        {"name":"Antenne En Marche de Fontainebleau","events":"1"},
        {"name":"En Marche Dammarie-les-Lys","events":"1"}
      ]
    }
    """

  Scenario: As a non logged-in user I can not get the committee members count in referent managed zone
    When I am on "/api/committees/members/count-by-month"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not get the committee members count in referent managed zone
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/committees/members/count-by-month"
    Then the response status code should be 403

  Scenario: As a referent I can get the committee members count in referent managed zone
    When I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "/api/committees/members/count-by-month"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-04": {"committee_memebers":3},
      "2018-03": {"committee_memebers":3},
      "2018-02": {"committee_memebers":2},
      "2018-01": {"committee_memebers":2},
      "2017-12": {"committee_memebers":2},
      "2017-11": {"committee_memebers":2}
    }
    """

    When I am on "/api/committees/members/count-by-month?country=fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-04": {"committee_memebers":3},
      "2018-03": {"committee_memebers":3},
      "2018-02": {"committee_memebers":2},
      "2018-01": {"committee_memebers":2},
      "2017-12": {"committee_memebers":2},
      "2017-11": {"committee_memebers":2}
    }
    """

    When I am on "/api/committees/members/count-by-month?country=ch"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-04": {"committee_memebers":0},
      "2018-03": {"committee_memebers":0},
      "2018-02": {"committee_memebers":0},
      "2018-01": {"committee_memebers":0},
      "2017-12": {"committee_memebers":0},
      "2017-11": {"committee_memebers":0}
    }
    """

    When I am on "/api/committees/members/count-by-month?city=Paris 8e"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-04": {"committee_memebers":2},
      "2018-03": {"committee_memebers":2},
      "2018-02": {"committee_memebers":1},
      "2018-01": {"committee_memebers":1},
      "2017-12": {"committee_memebers":1},
      "2017-11": {"committee_memebers":1}
    }
    """

    When I am on "/api/committees/members/count-by-month?city=Dammarie-les-Lys"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "2018-04": {"committee_memebers":1},
        "2018-03": {"committee_memebers":1},
        "2018-02": {"committee_memebers":1},
        "2018-01": {"committee_memebers":1},
        "2017-12": {"committee_memebers":1},
        "2017-11": {"committee_memebers":1}
      }
    """

    When I am on "/api/committees/members/count-by-month?committee=515a56c0-bde8-56ef-b90c-4745b1c93818"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-04": {"committee_memebers":2},
      "2018-03": {"committee_memebers":2},
      "2018-02": {"committee_memebers":1},
      "2018-01": {"committee_memebers":1},
      "2017-12": {"committee_memebers":1},
      "2017-11": {"committee_memebers":1}
    }
    """

    # Test get stats for committee with scheduled events but not managed by referent
    When I am on "/api/committees/members/count-by-month?committee=b0cd0e52-a5a4-410b-bba3-37afdd326a0a"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-04": {"committee_memebers":1},
      "2018-03": {"committee_memebers":1},
      "2018-02": {"committee_memebers":1},
      "2018-01": {"committee_memebers":1},
      "2017-12": {"committee_memebers":1},
      "2017-11": {"committee_memebers":1}
    }
    """
