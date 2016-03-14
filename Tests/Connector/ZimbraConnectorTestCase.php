<?php
/**
 * Created by PhpStorm.
 * User: willemv
 * Date: 2016/03/14
 * Time: 08:39
 */

namespace Synaq\ZasaBundle\Tests\Connector;


use Mockery as m;
use Synaq\CurlBundle\Curl\Wrapper;

class ZimbraConnectorTestCase extends \PHPUnit_Framework_TestCase
{
    protected $httpOkHeaders;
    protected $soapHeaders;
    protected $soapFooters;
    /**
     * @var Wrapper | m\Mock
     */
    protected $client;

    protected function setUp()
    {
        $this->httpOkHeaders = "HTTP/1.1 200 OK\r\n".
            "Content-Type: text/xml;charset=UTF-8\r\n\r\n";

        $this->soapHeaders = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="16996"/>
                        </context>
                    </soap:Header>
                    <soap:Body>';

        $this->soapFooters = '        </soap:Body>
                </soap:Envelope>';

        /** @var Wrapper | m\Mock $client */
        $this->client = m::mock('\Synaq\CurlBundle\Curl\Wrapper');
        $this->client->shouldIgnoreMissing();
    }
}