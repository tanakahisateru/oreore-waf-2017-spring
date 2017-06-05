<?php
namespace Acme\Tests\Acceptance;

use AcceptanceTester;

class ApiCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function posts(AcceptanceTester $I)
    {
        $I->sendGET('/api/posts');
        $I->seeResponseIsJson([
            'posts' => [],
        ]);
    }
}
