<?php
namespace Acme\Tests\Acceptance;

use AcceptanceTester;

class SiteCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function index(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->seeInTitle('Index');
    }

    public function contact(AcceptanceTester $I)
    {
        $I->amOnPage('/contact');
        $I->see('contact page');
    }

    public function privacy(AcceptanceTester $I)
    {
        $I->amOnPage('/privacy');
        $I->seeInTitle('Privacy Policy');
    }
}
