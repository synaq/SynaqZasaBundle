<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Exception\SoapFaultException;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class DeleteMountPointTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector | m\Mock
     */
    private $connector;

    /**
     * @test
     * @throws SoapFaultException
     */
    public function delegatesAuthenticationOnce()
    {
        $this->connector->deleteMountPoint('foo@bar.com', 42);
        $this->connector->shouldHaveReceived('delegateAuth')->once();
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function delegatesForTheGivenAccountName()
    {
        $this->connector->deleteMountPoint('foo@bar.com', 42);
        $this->connector->shouldHaveReceived('delegateAuth')->with('foo@bar.com');
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function acceptsAnyAccountName()
    {
        $this->connector->deleteMountPoint('bar@bar.com', 42);
        $this->connector->shouldHaveReceived('delegateAuth')->with('bar@bar.com');
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function deletesTheGivenFolderId()
    {
        $this->connector->deleteMountPoint('foo@bar.com', 42);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<FolderActionRequest.*>.*<action op="delete" id="42".*\\/>.*<\\/FolderActionRequest>/s', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    /**
     * @test
     * @throws SoapFaultException
     */
    public function acceptsAnyFolderId()
    {
        $this->connector->deleteMountPoint('foo@bar.com', 123);
        $this->client->shouldHaveReceived('post')->with(m::any(), m::on(function ($body) {
            return preg_match('/<FolderActionRequest.*>.*<action op="delete" id="123".*\\/>.*<\\/FolderActionRequest>/s', $body) === 1;
        }), m::any(), m::any(), m::any());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->client->shouldReceive('post')->andReturn($this->genericResponse())->byDefault();
        $this->connector = m::mock(
            'Synaq\ZasaBundle\Connector\ZimbraConnector[delegateAuth]',
            [$this->client, null, null, null, true, __DIR__.'/Fixtures/token']
        );
        $this->connector->shouldIgnoreMissing();
    }

    private function genericResponse()
    {
        $message = '<FolderActionResponse xmlns="urn:zimbraMail">
                        <action op="delete" id="257"/>
                    </FolderActionResponse>';

        return new Response($this->httpOkHeaders.$this->soapHeaders.$message.$this->soapFooters);
    }
}
