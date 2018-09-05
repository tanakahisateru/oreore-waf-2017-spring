<?php
namespace Acme\Tests\Functional;

use FunctionalTester;

class SiteCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function index(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->seeInTitle('Index');
    }

    public function contact(FunctionalTester $I)
    {
        $I->amOnPage('/contact');
        $I->see('contact page');
    }

    public function privacy(FunctionalTester $I)
    {
        $I->amOnPage('/privacy');
        $I->seeInTitle('Privacy Policy');
    }

    public function notFound(FunctionalTester $I)
    {
        $I->amOnPage('/xxx');
        $I->seePageNotFound();
        $I->see('Sorry,');
    }
}
