<?php

namespace FormaLms\Features\Contexts;

use Behat\Behat\Definition\Call as Step;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;


define("IN_FORMA", true);
require_once(dirname(__FILE__).'/../../../../../html/base.php');

require_once(_lib_.'/lib.bootstrap.php');
\Boot::init(BOOT_PAGE_WR);
require_once(_lms_ . '/lib/lib.course.php');

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext
{

    /**
     * @Given /I am logged as "(?P<username>(?:[^"]|\\")*)" with password "(?P<password>(?:[^"]|\\")*)"/
     */
    public function iAmLoggedAsStep($username, $password)
    {
        $this->logoutStep();
        $this->iAmOnHomepage();
        $this->fillField("login_userid", $username);
        $this->fillField("login_pwd", $password);
        $this->pressButton("login");
    }

    /**
     * @Given /I am logged out/
     */
    public function logoutStep()
    {
        $this->visit("/appLms/index.php?modname=login&op=logout");
    }


    /**
     * Get the couse Id from the course code
     *
     * @param $code
     */
    protected function getCourseIdByCode($code)
    {
        $manCourse = new \Man_Course();
        $idCourse = $manCourse->getCourseIdByName($code);

        return $idCourse;
    }

    /**
     * Opens specified course page.
     *
     * @Given /^(?:|I )go to course page with code "(?P<code>[^"]+)"$/
     */
    public function visitCoursePage($code)
    {
        $courseId = $this->getCourseIdByCode($code);
        $coursePageLink = "/appLms/index.php?modname=course&op=aula&idCourse=" . $courseId;
        $this->visitPath($coursePageLink);
    }

    /**
     * Click on the element with the provided CSS Selector
     *
     * @When /^I click on "([^"]*)"$/
     */
    public function clickOn($cssSelector)
    {
        $cssSelector = 'a:contains("SC")';
        $session = $this->getSession();

        $escapedValue = $session->getSelectorsHandler()->xpathLiteral($cssSelector);

        $element = $session->getPage()->find('named', array('link', $escapedValue));

        $element->click();

    }

    /** @BeforeScenario */
    public function before(BeforeScenarioScope $scope)
    {
        // @TODO reset db with phing
    }

}
