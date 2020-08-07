<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Exception\SoapFaultException;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class DeleteCalendarResourceTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;


    /**
     * @test
     * @throws SoapFaultException
     */
    public function sendsOnePostRequestToZimbra()
    {
        $this->connector->deleteCalendarResource('WHAT-EVER');
        $this->client->shouldHaveReceived('post')->once();
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function sendsTheGivenIdForTheDeleteRequest()
    {
        $this->connector->deleteCalendarResource('SOME-ID');
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/DeleteCalendarResourceRequest.*id="SOME-ID"/', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->client->shouldReceive('post')->andReturn($this->genericResponse())->byDefault();
        $this->connector = new ZimbraConnector($this->client, null, null, null, true, __DIR__.'/Fixtures/token');
    }

    private function genericResponse()
    {
        $message = '<DeleteCalendarResourceResponse xmlns="urn:zimbraAdmin"/>';

        return new Response($this->httpOkHeaders . $this->soapHeaders . $message . $this->soapFooters);
    }
}
