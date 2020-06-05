<?php

namespace Synaq\ZasaBundle\Tests\Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;

class RemoveDlMemberTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @test
     */
    public function sendsTheRequestForTheGivenId()
    {
        $this->connector->removeDlMember('some-id', null);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return strpos($body, 'id="some-id"') > 0;
        }), m::any(), m::any(), m::any());
    }


    /**
     * @test
     */
    public function sendsTheRequestForTheDlMember()
    {
        $this->connector->removeDlMember(null, 'foo@bar.com');
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return strpos($body, "<dlm>foo@bar.com</dlm>") > 0;
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
        $message = "<RemoveDistributionListMemberResponse xmlns=\"urn:zimbraAdmin\"/>";

        return new Response($this->httpOkHeaders . $this->soapHeaders . $message . $this->soapFooters);
    }
}
