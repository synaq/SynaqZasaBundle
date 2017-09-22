<?php
/**
 * Created by PhpStorm.
 * User: willemv
 * Date: 2017/09/21
 * Time: 15:27
 */

namespace Tests\Connector;

use Mockery as m;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\ZasaBundle\Tests\Connector\ZimbraConnectorTestCase;

class ZimbraConnectorImportCalendarTest extends ZimbraConnectorTestCase
{
    /**
     * @var ZimbraConnector | m\Mock
     */
    private $connector;

    /**
     * @test
     */
    public function performsDelegatedAuthOnce()
    {
        $this->connector->importCalendar(null, null);
        $this->connector->shouldHaveReceived('delegateAuth')->once();
    }

    /**
     * @test
     */
    public function performsDelegatedAuthOnTheGivenAccount()
    {
        $this->connector->importCalendar('foo@bar.com', null);
        $this->connector->shouldHaveReceived('delegateAuth')->with('foo@bar.com');
    }

    /**
     * @test
     */
    public function acceptsAnyAccountForDelegatedAuth()
    {
        $this->connector->importCalendar('bar@baz.com', null);
        $this->connector->shouldHaveReceived('delegateAuth')->with('bar@baz.com');
    }

    /**
     * @test
     * @expectedException \Synaq\ZasaBundle\Exception\DelegatedAuthDeniedException
     * @expectedExceptionMessage Could not delegate authentication for foo@bar.com
     */
    public function throwsDelegatedAuthDeniedExceptionIfDelegatedAuthFails()
    {
        $this->connector->shouldReceive('delegateAuth')->andReturn(false);
        $this->connector->importCalendar('foo@bar.com', null);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->connector = m::mock('\Synaq\ZasaBundle\Connector\ZimbraConnector[delegateAuth]' , array($this->client, null, null, null, true, __DIR__.'/Fixtures/token'));
        $this->connector->shouldReceive('delegateAuth')->andReturn(array(
            'authToken' => null,
            'lifetime' => null
        ))->byDefault();
        $this->connector->shouldIgnoreMissing();
    }
}
