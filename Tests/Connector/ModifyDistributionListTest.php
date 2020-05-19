<?php

namespace Synaq\ZasaBundle\Tests\Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\CurlBundle\Curl\Wrapper;
use Synaq\ZasaBundle\Connector\ZimbraConnector;

class ModifyDistributionListTest extends ZimbraConnectorTestCase
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
        $this->connector->modifyDistributionList(null, []);
        $this->client->shouldHaveReceived('post')->once();
    }

    /**
     * @test
     */
    public function sendsTheRequestForTheGivenId()
    {
        $expectedMessage = '<id>some-id</id>';
        $this->connector->modifyDistributionList('some-id', []);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) use ($expectedMessage) {
            return strpos($body, $expectedMessage) > 0;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     */
    public function acceptsAnyId()
    {
        $expectedMessage = '<id>any-old-id</id>';
        $this->connector->modifyDistributionList('any-old-id', []);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) use ($expectedMessage) {
            return strpos($body, $expectedMessage) > 0;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     */
    public function setsGivenAttributes()
    {
        $expectedMessage = '<a n="foo">bar</a>';
        $this->connector->modifyDistributionList(null, ['foo' => 'bar']);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) use ($expectedMessage) {
            return strpos($body, $expectedMessage) > 0;
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
        $message = <<<EOF
<ModifyDistributionListResponse xmlns="urn:zimbraAdmin">
  <dl name="foo@bar.com" dynamic="0" id="some-id">
    <a n="uid">foo</a>
    <a n="zimbraHideInGal">FALSE</a>
    <a n="mail">foo@bar.com</a>
    <a n="zimbraId">some-id</a>
    <a n="objectClass">zimbraDistributionList</a>
    <a n="objectClass">zimbraMailRecipient</a>
    <a n="zimbraMailHost">some-host.com</a>
    <a n="zimbraCreateTimestamp">20200514140427Z</a>
    <a n="zimbraMailStatus">enabled</a>
  </dl>
</ModifyDistributionListResponse>
EOF;

        return new Response($this->httpOkHeaders . $this->soapHeaders . $message . $this->soapFooters);
    }
}
