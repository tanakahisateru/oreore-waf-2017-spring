<?php
namespace My\Web\Lib\Util;

use Detection\MobileDetect;
use Psr\Http\Message\RequestInterface;

class MobileDetectFactory
{
    public static function detect(RequestInterface $request)
    {
        $phpStyledHeaders = [];

        foreach ($request->getHeaders() as $k => $v) {
            $k = 'HTTP_' . strtoupper(str_replace('-', '_', $k));
            $phpStyledHeaders[$k] = $v[0];
        }

        return new MobileDetect($phpStyledHeaders);
    }
}
