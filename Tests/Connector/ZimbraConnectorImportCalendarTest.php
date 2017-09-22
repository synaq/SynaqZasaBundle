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

    protected function setUp()
    {
        parent::setUp();

        $this->connector = m::mock('\Synaq\ZasaBundle\Connector\ZimbraConnector[delegateAuth]' , array($this->client, null, null, null, true, __DIR__.'/Fixtures/token'));
        $this->connector->shouldIgnoreMissing();
    }
}
