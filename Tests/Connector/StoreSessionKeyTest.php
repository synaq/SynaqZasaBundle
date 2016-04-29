<?php
/**
 * Created by PhpStorm.
 * User: nicholasp
 * Date: 2016/04/29
 * Time: 1:19 PM
 */

namespace Synaq\ZasaBundle\Tests\Connector;


use Synaq\CurlBundle\Curl\Wrapper;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Mockery as m;

class StoreSessionKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldNotAuthOnConstructionIfSessionFileIsPresent()
    {
        /** @var Wrapper | m\Mock $httpClient */
        $httpClient = \Mockery::mock('Synaq\CurlBundle\Curl\Wrapper');
        $httpClient->shouldIgnoreMissing();
        $server = 'https://my-server.com:7071/service/admin/soap';
        $username = 'admin@my-server.com';
        $password = 'my-password';
        $sessionFile = 'Fixtures/token';

        $connector = new ZimbraConnector($httpClient, $server, $username, $password, $sessionFile, true, $sessionFile);
        
        $httpClient->shouldNotHaveReceived('post');
    }
}
