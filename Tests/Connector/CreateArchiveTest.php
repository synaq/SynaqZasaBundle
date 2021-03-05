<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class CreateArchiveTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     */
    public function sendsOnePostRequestToZimbra()
    {
        $this->connector->createArchive(null, null, null);
        $this->client->shouldHaveReceived('post')->once();
    }

    /**
     * @test
     */
    public function sendsTheGivenAccountId()
    {
        $this->connector->createArchive('SOME-ID', null, null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<CreateArchiveRequest.*>.*<account by="id">SOME-ID<\\/account>.*<\\/CreateArchiveRequest>/s', $body) === 1;
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
        $message = '<CreateArchiveResponse xmlns="urn:zimbraAdmin"/>';

        return new Response($this->httpOkHeaders . $this->soapHeaders . $message . $this->soapFooters);
    }
}
