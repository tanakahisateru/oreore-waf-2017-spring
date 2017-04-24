<?php


class SiteCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
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
