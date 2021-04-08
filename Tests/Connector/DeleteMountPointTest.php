<?php

namespace Connector;

use Mockery as m;
use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class DeleteMountPointTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector | m\Mock
     */
    private $connector;

    /**
     * @test
     */
    public function delegatesAuthenticationOnce()
    {
        $this->patriallyMockConnectorToTestDelegatedAuth();
        $this->connector->deleteMountPoint('foo@bar.com', 42);
        $this->connector->shouldHaveReceived('delegateAuth')->once();
    }

    /**
     * @test
     */
    public function delegatesForTheGivenAccountName()
    {
        $this->patriallyMockConnectorToTestDelegatedAuth();
        $this->connector->deleteMountPoint('foo@bar.com', 42);
        $this->connector->shouldHaveReceived('delegateAuth')->with('foo@bar.com');
    }

    /**
     * @test
     */
    public function acceptsAnyAccountName()
    {
        $this->patriallyMockConnectorToTestDelegatedAuth();
        $this->connector->deleteMountPoint('bar@bar.com', 42);
        $this->connector->shouldHaveReceived('delegateAuth')->with('bar@bar.com');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->client->shouldReceive('post')->andReturn($this->genericResponse())->byDefault();
        $this->connector = new ZimbraConnector($this->client, null, null, null, true, __DIR__.'/Fixtures/token');
    }

    private function genericResponse()
    {
        $message = '<FolderActionResponse xmlns="urn:zimbraMail">
                        <action op="delete" id="257"/>
                    </FolderActionResponse>';

        return new Response($this->httpOkHeaders.$this->soapHeaders.$message.$this->soapFooters);
    }

    private function patriallyMockConnectorToTestDelegatedAuth()
    {
        $this->connector = m::mock(
            'Synaq\ZasaBundle\Connector\ZimbraConnector[delegateAuth]',
            [$this->client, null, null, null, true, __DIR__.'/Fixtures/token']
        );
        $this->connector->shouldIgnoreMissing();
    }
}
