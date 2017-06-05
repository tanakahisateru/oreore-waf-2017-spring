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

        $logger = $I->getMiddlewareContainer()->get('logger');
        $I->assertInstanceOf(\Psr\Log\LoggerInterface::class, $logger);
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
}
