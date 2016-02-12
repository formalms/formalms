Feature: Check course statistics
  As a course teacher
  I need to be able to create a new course
  I need to be able to create a new test object
  I need to be able to complete the test
  I want to check that the statistics are correct

#  Scenario: Create course
#    Given I am logged as "alessandro" with password "ciaociao"
#    Given I go to "/appCore/index.php?r=alms/course/newcourse"
#    When I fill in the following:
#      | course_code       | CHECK_STATS |
#      | course_name       | Check stats |
#      | course_descr      | Check course statistics |
#    And I press "Salva modifiche"
#    Then I should see "Operazione completata con successo"

  Scenario: Create test object
    Given I am logged as "alessandro" with password "ciaociao"
    Given I go to course page with code "TEST_REGISTRO_VALUTAZIONI"
    Given I follow "Area Docenti"
    Given I follow "Gestione oggetti didattici"
    And I press "Oggetti del corso"
    And I press "Nuovo oggetto didattico"
    When I select "test" from "radiolo"
    And I press "Nuovo"
    When I fill in the following:
      | title             | Test A |
      | textof            | Descrizione Test A |
    And I press "Crea test"
    And I press "Modifica: Test A"
    And I press "Aggiungi una domanda"
    And I click on "'SC - Domanda a risposta singola'"

    Given I follow "Area Studenti"
    Given I follow "Materiali"
    Given I follow "Test A"
    And I press "Clicca per iniziare"

    Then I should see "Elenco delle domande"
