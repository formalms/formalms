Feature: New course creation
  As a website admin
  I need to be able to create a new course

  Scenario: Create course
    Given I am logged as "alessandro" with password "ciaociao"
    Given I go to "/appCore/index.php?r=alms/course/newcourse"
    When I fill in the following:
      | course_code       | PROVA_ALBERTO |
      | course_name       | Corso Alberto |
      | course_descr      | Descrizione Alberto |
    And I press "Salva modifiche"
    Then I should see "Operazione completata con successo"
