<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class FlushCacheTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     */
    public function sendsFlushCacheRequestWithSelectedTypeAndServerOptions()
    {
        $this->connector->flushCache('account', true, false, []);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<FlushCacheRequest xmlns="urn:zimbraAdmin">.*<cache allServers="1" type="account" imapServers="0"\/>.*<\/FlushCacheRequest>/s', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     */
    public function acceptsAnyCombinationOfTypeAndFlags()
    {
        $this->connector->flushCache('zimlet', false, true, []);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<FlushCacheRequest xmlns="urn:zimbraAdmin">.*<cache allServers="0" type="zimlet" imapServers="1"\/>.*<\/FlushCacheRequest>/s', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     */
    public function treatsListOfNamesAsEntriesByName()
    {
        $this->connector->flushCache('account', false, true, ['foo@bar.com']);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<FlushCacheRequest xmlns="urn:zimbraAdmin">.*<cache allServers="0" type="account" imapServers="1">.*<entry by="name">foo@bar.com<\/entry>.*<\/FlushCacheRequest>/s', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    protected function setUp()
    {
        parent::setUp();

        $flushCacheResponseXml =
            <<<'XML'
<FlushCacheResponse xmlns="urn:zimbraAdmin"/>
XML;

        $flushCacheResponse = new Response(
            $this->httpOkHeaders.$this->soapHeaders.$flushCacheResponseXml.$this->soapFooters
        );

        $this->client->shouldReceive('post')->andReturn($flushCacheResponse)->byDefault();

        $this->connector = new ZimbraConnector($this->client, null, null, null, true, __DIR__ . '/Fixtures/token');
    }
}
