<?php

namespace Synaq\ZasaBundle\Tests\Connector;

use Synaq\CurlBundle\Curl\Response;
use Synaq\ZasaBundle\Connector\ZimbraConnector;
use Synaq\CurlBundle\Curl\Wrapper;
use Mockery as m;

class ZimbraConnectorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Wrapper | m\MockInterface
     */
    private $mockClient;

    /**
     * @var ZimbraConnector
     */
    private $connector;

    /**
     * @var bool
     */
    private $mock = true;

    /**
     * @var string
     */
    private $server = 'https://10.1.5.145:7071/service/admin/soap';

    /**
     * @var string
     */
    private $username = 'admin@demo.synaq.com';

    /**
     * @var string
     */
    private $password = '!@synaq()';

    /**
     * @var Response
     */
    private $loginResponse;

    /**
     * @var Response
     */
    private $delegateResponse;

    /**
     * @var string
     */
    private $httpHead;


    public function setup()
    {
        if ($this->mock) {
            $this->mockClient = \Mockery::mock('Synaq\CurlBundle\Curl\Wrapper');

            $this->httpHead = "HTTP/1.1 200 OK\r\n";
            $this->httpHead .= "Date: Wed, 07 Aug 2013 11:09:37 GMT\r\n";
            $this->httpHead .= "Expires: Thu, 01 Jan 1970 00:00:00 GMT\r\n";
            $this->httpHead .= "Content-Type: text/xml;charset=UTF-8\r\n";
            $this->httpHead .= "Cache-Control: no-store, no-cache\r\n";
            $this->httpHead .= "Content-Length: 519\r\n";
            $this->httpHead .= "\r\n";

            $loginRaw = $this->httpHead;
            $loginRaw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="14213"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <AuthResponse xmlns="urn:zimbraAdmin">
                            <authToken>
                                0_d5b6d1b0eeb17438e16fed1b46964f21b1a760d7_69643d33363a30313639323938332d393931382d343861322d613663332d3661323139316630363466643b6578703d31333a313337353931363937373439353b61646d696e3d313a313b747970653d363a7a696d6272613b
                            </authToken>
                            <lifetime>43200000</lifetime>
                        </AuthResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $this->loginResponse = new Response($loginRaw);

            $delegateRaw = $this->httpHead;
            $delegateRaw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19441"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <DelegateAuthResponse xmlns="urn:zimbraAdmin">
                            <authToken>
                                0_78aa1c994ad070a169746182fc26bda32ef0c172_69643d33363a38313465383033322d663364322d343230652d613238362d3639636466343663646635313b6578703d31333a313338343234393338313135303b6169643d33363a30313639323938332d393931382d343861322d613663332d3661323139316630363466643b747970653d363a7a696d6272613b
                            </authToken>
                            <lifetime>3600000</lifetime>
                        </DelegateAuthResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $this->delegateResponse = new Response($delegateRaw);
        } else {
            $this->mockClient = new Wrapper(null, false, true, false, array('CURLOPT_RETURNTRANSFER' => true, 'CURLOPT_SSL_VERIFYPEER' => false, 'CURLOPT_SSL_VERIFYHOST' => false), array());
        }
    }

    public function testAddDlToDl()
    {
        if ($this->mock) {
            $getDl = $this->httpHead;
            $getDl .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="14213"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <GetDistributionListResponse total="8" more="0" xmlns="urn:zimbraAdmin">
                            <dl dynamic="0" id="3800ee3c-8fdc-4395-92c6-8ebf26399d0e" name="all_members@testdomain1.co.za">
                                <a n="uid">all_members</a>
                                <a n="mail">all_members@testdomain1.co.za</a>
                                <a n="zimbraMailStatus">enabled</a>
                                <a n="zimbraMailHost">cms-ah-zcs-cluster.synaq.com</a>
                                <a n="zimbraId">3800ee3c-8fdc-4395-92c6-8ebf26399d0e</a>
                                <a n="zimbraCreateTimestamp">20130725125042Z</a>
                                <a n="objectClass">zimbraDistributionList</a>
                                <a n="objectClass">zimbraMailRecipient</a>
                                <a n="zimbraMailAlias">all_members@testdomain1.co.za</a>
                                <dlm>all_members@testdomain2.co.za</dlm>
                                <dlm>user1@testdomain1.co.za</dlm>
                                <dlm>user2@testdomain1.co.za</dlm>
                                <dlm>user3@testdomain1.co.za</dlm>
                                <dlm>user4@testdomain1.co.za</dlm>
                                <dlm>user5@testdomain1.co.za</dlm>
                                <dlm>user6@testdomain1.co.za</dlm>
                                <dlm>user{1..}@testdomain1.co.za</dlm>
                            </dl>
                        </GetDistributionListResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $getDlResponse = new Response($getDl);

            $add = $this->httpHead;
            $add .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"><change token="14213"/></context>
                    </soap:Header>
                    <soap:Body>
                        <AddDistributionListMemberResponse xmlns="urn:zimbraAdmin"/>
                    </soap:Body>
                </soap:Envelope>
XML;

            $addDlMemberResponse = new Response($add);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $getDlResponse,
                    $addDlMemberResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $id = $this->connector->getDlId('all_members@testdomain1.co.za');
        $this->connector->addDlMember($id, 'all_members@testdomain2.co.za');
    }

    public function testGetAllCoses()
    {
        if ($this->mock) {
            //mocks
            $gac = $this->httpHead;
            $gac .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="16996"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <GetAllCosResponse xmlns="urn:zimbraAdmin">
                            <cos id="150dbb00-ecba-431c-a239-98c69cf42b5f" name="amadeus">
                                <a n="zimbraFeatureNotebookEnabled">FALSE</a>
                                <a n="zimbraFeatureSkinChangeEnabled">TRUE</a>
                            </cos>
                            <cos id="150dbb00-ecba-431c-a239-98c69cf42b52" name="test-cos">
                                <a n="zimbraFeatureNotebookEnabled">FALSE</a>
                                <a n="zimbraFeatureSkinChangeEnabled">TRUE</a>
                            </cos>
                        </GetAllCosResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $gacResponse = new Response($gac);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $gacResponse,
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $coses = $this->connector->getAllCoses();

        $this->assertEquals('amadeus', $coses[0]['name']);
        $this->assertEquals('test-cos', $coses[1]['name']);
    }

    public function testRevokeRight()
    {
        if ($this->mock) {
            //mocks
            $rvr = $this->httpHead;
            $rvr .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="17009"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <RevokeRightResponse xmlns="urn:zimbraAdmin"/>
                    </soap:Body>
                </soap:Envelope>
XML;
            $rvrResponse = new Response($rvr);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $rvrResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->revokeRight('basic-pop-imap-2gb', 'cos', 'zimbradomainadmins@fixture-test-portal.co.za', 'grp', 'listCos');

        $this->assertEquals('', $response);
    }

    /**
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: no such grant: [grantee name=zimbradomainadmins@fixture-test-portal.co.za, grantee id=19a65c8c-aa73-4014-9165-b535970d95f0, grantee type=grp, right=listCos]
     */
    public function testRevokeRightFault()
    {
        if ($this->mock) {
            //mocks
            $rvr = $this->httpHead;
            $rvr .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="17015"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <soap:Fault>
                            <soap:Code>
                                <soap:Value>soap:Sender</soap:Value>
                            </soap:Code>
                            <soap:Reason>
                                <soap:Text>no such grant: [grantee name=zimbradomainadmins@fixture-test-portal.co.za, grantee id=19a65c8c-aa73-4014-9165-b535970d95f0, grantee type=grp, right=listCos]
                                </soap:Text>
                            </soap:Reason>
                            <soap:Detail>
                                <Error xmlns="urn:zimbra">
                                    <Code>account.NO_SUCH_GRANT</Code>
                                    <Trace>
                                        qtp1456226908-260:https://192.168.3.104:7071/service/admin/soap:1380612910563:4fa889d922e219b3
                                    </Trace>
                                </Error>
                            </soap:Detail>
                        </soap:Fault>
                    </soap:Body>
                </soap:Envelope>
XML;
            $rvrResponse = new Response($rvr);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $rvrResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $this->connector->revokeRight('basic-pop-imap-2gb', 'cos', 'zimbradomainadmins@fixture-test-portal.co.za', 'grp', 'listCos');
    }


    public function testEnableArchive()
    {
        if ($this->mock) {
            //mocks
            $ear = $this->httpHead;
            $ear .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19441"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <EnableArchiveResponse xmlns="urn:zimbraAdmin"/>
                    </soap:Body>
                </soap:Envelope>
XML;
            $earResponse = new Response($ear);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $earResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->enableArchive('user1@testdomain3.co.za', 'user1@testdomain3.co.za.archive', 'zimbra-archive-cos');

        $this->assertEquals('', $response);
    }

    /**
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: email address already exists: user1@testdomain3.co.za.archive, at DN: uid=user1,ou=people,dc=testdomain3,dc=co,dc=za,dc=archive
     */
    public function testEnableArchiveFault()
    {
        if ($this->mock) {
            //mocks
            $ear = $this->httpHead;
            $ear .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19447"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <soap:Fault>
                            <soap:Code>
                                <soap:Value>soap:Sender</soap:Value>
                            </soap:Code>
                            <soap:Reason>
                                <soap:Text>email address already exists: user1@testdomain3.co.za.archive, at DN: uid=user1,ou=people,dc=testdomain3,dc=co,dc=za,dc=archive
                                </soap:Text>
                            </soap:Reason>
                            <soap:Detail>
                                <Error xmlns="urn:zimbra">
                                    <Code>account.ACCOUNT_EXISTS</Code>
                                    <Trace>
                                        qtp1290340102-35061:https://192.168.3.104:7071/service/admin/soap:1384408651392:3545528d1a7c45af
                                    </Trace>
                                </Error>
                            </soap:Detail>
                        </soap:Fault>
                    </soap:Body>
                </soap:Envelope>
XML;
            $earResponse = new Response($ear);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $earResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->enableArchive('user1@testdomain3.co.za', 'user1@testdomain3.co.za.archive', 'zimbra-archive-cos');

        $this->assertEquals('', $response);
    }

    public function testDelegateAuth()
    {
        if ($this->mock) {
            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->delegateAuth('user1@testdomain2.co.za.archive');
        $this->assertEquals('0_78aa1c994ad070a169746182fc26bda32ef0c172_69643d33363a38313465383033322d663364322d343230'
            . '652d613238362d3639636466343663646635313b6578703d31333a313338343234393338313135303b6169643d33363a3031363'
            . '9323938332d393931382d343861322d613663332d3661323139316630363466643b747970653d363a7a696d6272613b',
            $response['authToken']);
        $this->assertEquals('3600000', $response['lifetime']);
    }

    /**
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: no such account: user1@testdomain2123123123.co.za.archive
     */
    public function testDelegateAuthFault()
    {
        if ($this->mock) {
            //mocks
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19447"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <soap:Fault>
                            <soap:Code>
                                <soap:Value>soap:Sender</soap:Value>
                            </soap:Code>
                            <soap:Reason>
                                <soap:Text>no such account: user1@testdomain2123123123.co.za.archive</soap:Text>
                            </soap:Reason>
                            <soap:Detail>
                                <Error xmlns="urn:zimbra">
                                    <Code>account.NO_SUCH_ACCOUNT</Code>
                                    <Trace>
                                        qtp1290340102-35149:https://192.168.3.104:7071/service/admin/soap:1384410921923:3545528d1a7c45af
                                    </Trace>
                                </Error>
                            </soap:Detail>
                        </soap:Fault>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $this->connector->delegateAuth('user1@testdomain2123123123.co.za.archive');
    }

    public function testAddArchiveReadFilterRule()
    {
        if ($this->mock) {
            //mocks
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="2"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <ModifyFilterRulesResponse xmlns="urn:zimbraMail"/>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $this->connector->addArchiveReadFilterRule('user1@testdomain3.co.za.archive');
    }

    /**
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: no such account: user1@testdomain3123.co.za.archive
     */
    public function testAddArchiveReadFilterRuleFault()
    {
        if ($this->mock) {
            //mocks
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19447"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <soap:Fault>
                            <soap:Code>
                                <soap:Value>soap:Sender</soap:Value>
                            </soap:Code>
                            <soap:Reason>
                                <soap:Text>no such account: user1@testdomain3123.co.za.archive</soap:Text>
                            </soap:Reason>
                            <soap:Detail>
                                <Error xmlns="urn:zimbra">
                                    <Code>account.NO_SUCH_ACCOUNT</Code>
                                    <Trace>
                                        qtp1290340102-35167:https://192.168.3.104:7071/service/admin/soap:1384411417600:3545528d1a7c45af
                                    </Trace>
                                </Error>
                            </soap:Detail>
                        </soap:Fault>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $this->connector->addArchiveReadFilterRule('user1@testdomain3123.co.za.archive');
    }

    public function testGetFolder()
    {
        if ($this->mock) {
            //mocks
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="1"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <GetFolderResponse xmlns="urn:zimbraMail">
                            <folder rev="1" i4next="3" i4ms="1" ms="1" n="0" activesyncdisabled="0" l="1" id="2" s="0" name="Inbox"
                                    uuid="12e18744-ed19-49b0-b36d-5666ba3d95c7" view="message"
                                    luuid="a9a09b64-dce6-495d-886a-355efc6d8055"/>
                        </GetFolderResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $folder = $this->connector->getFolder('user1@testdomain3.co.za.archive', 2);
    }

    /**
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: no such account: user1@testdomain3123.co.za.archive
     */
    public function testGetFolderFault()
    {
        if ($this->mock) {
            //mocks
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19447"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <soap:Fault>
                            <soap:Code>
                                <soap:Value>soap:Sender</soap:Value>
                            </soap:Code>
                            <soap:Reason>
                                <soap:Text>no such account: user1@testdomain3123.co.za.archive</soap:Text>
                            </soap:Reason>
                            <soap:Detail>
                                <Error xmlns="urn:zimbra">
                                    <Code>account.NO_SUCH_ACCOUNT</Code>
                                    <Trace>
                                        qtp1290340102-35167:https://192.168.3.104:7071/service/admin/soap:1384411417600:3545528d1a7c45af
                                    </Trace>
                                </Error>
                            </soap:Detail>
                        </soap:Fault>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $this->connector->getFolder('user1@testdomain31234.co.za.archive', 2);
    }

    public function testCreateFolder()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
  <soap:Header>
    <context xmlns="urn:zimbra">
      <session id="17393">17393</session>
      <change token="16"/>
      <notify seq="3">
        <created>
          <folder i4ms="16" rev="16" i4next="266" ms="16" l="1" uuid="6009ed8b-fc82-489c-97e3-3bd4080670e0" n="0" luuid="1dcfb61c-90e9-4a64-91e4-dd8b48fd6898" activesyncdisabled="0" absFolderPath="/Test" s="0" name="Test" id="265" webOfflineSyncDays="0"/>
        </created>
        <modified>
          <folder id="1" uuid="1dcfb61c-90e9-4a64-91e4-dd8b48fd6898"/>
        </modified>
      </notify>
    </context>
  </soap:Header>
  <soap:Body>
    <CreateFolderResponse xmlns="urn:zimbraMail">
      <folder i4ms="16" rev="16" i4next="266" ms="16" l="1" uuid="6009ed8b-fc82-489c-97e3-3bd4080670e0" n="0" luuid="1dcfb61c-90e9-4a64-91e4-dd8b48fd6898" activesyncdisabled="0" absFolderPath="/Test" s="0" name="Test" id="265" webOfflineSyncDays="0"/>
    </CreateFolderResponse>
  </soap:Body>
</soap:Envelope>
XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $response
                )
            );

        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $id = $this->connector->createFolder('user01@testdomain3.co.za', "Test", 1);

        $this->assertEquals(265, $id, "Incorrect folder ID returned");
    }

    public function testCreateMountPoint()
    {
        if ($this->mock) {
            //mocks
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <session id="160">160</session>
                            <change token="1207"/>
                            <notify seq="1">
                                <created>
                                    <link reminder="0" rev="1207" oname="Inbox" ms="1207" n="0" activesyncdisabled="0" l="1"
                                          ruuid="12e18744-ed19-49b0-b36d-5666ba3d95c7" perm="r" id="260" s="0" rid="2"
                                          zid="815e06c0-63d4-499e-bc47-69f7ae8171f2" name="Archive"
                                          owner="user1@testdomain3.co.za.archive" view="message"
                                          uuid="545114e4-f738-452d-afdb-d6bfdd26b052" luuid="5be5c0dd-e8be-4990-9e0f-37da45329683"/>
                                </created>
                                <modified>
                                    <folder id="1" uuid="5be5c0dd-e8be-4990-9e0f-37da45329683"/>
                                </modified>
                            </notify>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <CreateMountpointResponse xmlns="urn:zimbraMail">
                            <link reminder="0" rev="1207" oname="Inbox" ms="1207" n="0" activesyncdisabled="0" l="1"
                                  ruuid="12e18744-ed19-49b0-b36d-5666ba3d95c7" perm="r" id="260" s="0" rid="2"
                                  zid="815e06c0-63d4-499e-bc47-69f7ae8171f2" name="Archive" owner="user1@testdomain3.co.za.archive"
                                  view="message" uuid="545114e4-f738-452d-afdb-d6bfdd26b052"
                                  luuid="5be5c0dd-e8be-4990-9e0f-37da45329683"/>
                        </CreateMountpointResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $this->connector->createMountPoint('user1@testdomain3.co.za', 0, 'Archive', '/Inbox', 'user1@testdomain3.co.za.archive', 'message');
    }

    /**
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: object with that name already exists: Archive
     */
    public function testCreateMountPointFault()
    {
        if ($this->mock) {
            //mocks
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="1207"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <soap:Fault>
                            <soap:Code>
                                <soap:Value>soap:Sender</soap:Value>
                            </soap:Code>
                            <soap:Reason>
                                <soap:Text>object with that name already exists: Archive</soap:Text>
                            </soap:Reason>
                            <soap:Detail>
                                <Error xmlns="urn:zimbra">
                                    <Code>mail.ALREADY_EXISTS</Code>
                                    <Trace>
                                        qtp1290340102-35193:https://192.168.3.104:7071/service/admin/soap:1384412357849:3545528d1a7c45af
                                    </Trace>
                                    <a t="STR" n="name">Archive</a>
                                </Error>
                            </soap:Detail>
                        </soap:Fault>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $this->connector->createMountPoint('user1@testdomain3.co.za', 0, 'Archive', '/Inbox', 'user1@testdomain3.co.za.archive', 'message');
    }

    public function testDisableArchive()
    {
        if ($this->mock) {
            //mocks
            $da = $this->httpHead;
            $da .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19447"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <DisableArchiveResponse xmlns="urn:zimbraAdmin"/>
                    </soap:Body>
                </soap:Envelope>
XML;
            $daResponse = new Response($da);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $daResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->disableArchive('user1@testdomain2.co.za');

        $this->assertEquals('', $response);
    }

    /**
     * @expectedException \Synaq\ZasaBundle\Exception\SoapFaultException
     * @expectedExceptionMessage Zimbra Soap Fault: system failure: java.lang.NullPointerException
     */
    public function testDisableArchiveFault()
    {
        if ($this->mock) {
            //mocks
            $da = $this->httpHead;
            $da .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19447"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <soap:Fault>
                            <soap:Code>
                                <soap:Value>soap:Receiver</soap:Value>
                            </soap:Code>
                            <soap:Reason>
                                <soap:Text>system failure: java.lang.NullPointerException</soap:Text>
                            </soap:Reason>
                            <soap:Detail>
                                <Error xmlns="urn:zimbra">
                                    <Code>service.FAILURE</Code>
                                    <Trace>
                                        qtp1290340102-35217:https://192.168.3.104:7071/service/admin/soap:1384412667686:3545528d1a7c45af
                                    </Trace>
                                </Error>
                            </soap:Detail>
                        </soap:Fault>
                    </soap:Body>
                </soap:Envelope>
XML;
            $daResponse = new Response($da);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $daResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->disableArchive('user1@testdomain31234.co.za');

        $this->assertEquals('', $response);
    }

    public function testCreateGalSyncAccount()
    {
        if ($this->mock) {
            //mocks
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope/">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19507"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <CreateGalSyncAccountResponse xmlns="urn:zimbraAdmin">
                            <account id="224f142a-41ba-4aea-9005-ab1dcbc68f1c" name="galsync@test-cos.com"/>
                        </CreateGalSyncAccountResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->createGalSyncAccount('galsync@test-cos.com', 'test-cos.com');

        $this->assertEquals(array(
            'account' => array(
                '@value' => '',
                '@attributes' => array(
                    'id' => '224f142a-41ba-4aea-9005-ab1dcbc68f1c',
                    'name' => 'galsync@test-cos.com'
                )
            )
        ), $response);
    }

    public function testCreateAliasDomain()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope/">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19507"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <CreateDomainResponse xmlns="urn:zimbraAdmin">
                            <domain id="69e5e6c5-fb88-4ba3-acd3-c8139379b284" name="test-alias.com">
                                <a n="zimbraId">69e5e6c5-fb88-4ba3-acd3-c8139379b284</a>
                                <a n="zimbraMailCatchAllAddress">@test-alias.com</a>
                                <a n="zimbraDomainType">alias</a>
                                <a n="zimbraDomainAliasTargetId">d5c53785-889d-4e8e-b809-4b30c5b00ad9</a>
                            </domain>
                        </CreateDomainResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->createAliasDomain('test-alias.com', 'test.com');

        $this->assertEquals('69e5e6c5-fb88-4ba3-acd3-c8139379b284', $response);
    }

    public function testGetDomain()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope/">
                    <soap:Header>
                        <context xmlns="urn:zimbra">
                            <change token="19507"/>
                        </context>
                    </soap:Header>
                    <soap:Body>
                        <GetDomainResponse xmlns="urn:zimbraAdmin">
                            <domain id="69e5e6c5-fb88-4ba3-acd3-c8139379b284" name="test-alias.com">
                                <a n="zimbraId">69e5e6c5-fb88-4ba3-acd3-c8139379b284</a>
                                <a n="zimbraDomainName">test-alias.com</a>
                                <a n="zimbraDomainStatus">active</a>
                                <a n="zimbraDomainType">alias</a>
                                <a n="zimbraDomainAliasTargetId">d5c53785-889d-4e8e-b809-4b30c5b00ad9</a>
                            </domain>
                        </GetDomainResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->getDomain('test-alias.com');

        $this->assertEquals('69e5e6c5-fb88-4ba3-acd3-c8139379b284', $response['zimbraId']);
        $this->assertEquals('test-alias.com', $response['zimbraDomainName']);
        $this->assertEquals('active', $response['zimbraDomainStatus']);
        $this->assertEquals('alias', $response['zimbraDomainType']);
        $this->assertEquals('d5c53785-889d-4e8e-b809-4b30c5b00ad9', $response['zimbraDomainAliasTargetId']);
    }

    public function testGetAccountsOneAccount()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"/>
                    </soap:Header>
                    <soap:Body>
                        <GetAllAccountsResponse xmlns="urn:zimbraAdmin">
                            <account name="test-account@test-domain.com" id="bc85eaf1-dfe0-4879-b5e0-314979ae0009">
                                <a n="attribute-1">value-1</a>
                                <a n="attribute-2">TRUE</a>
                                <a n="attribute-1">value-2</a>
                            </account>
                        </GetAllAccountsResponse>
                    </soap:Body>
                </soap:Envelope>
XML;
            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->getAccounts('synaq.com');
        $this->assertArrayHasKey('test-account@test-domain.com', $response);
        $this->assertArrayHasKey('attribute-1', $response['test-account@test-domain.com']);
        $this->assertArrayHasKey(0, $response['test-account@test-domain.com']['attribute-1']);
        $this->assertEquals('value-1', $response['test-account@test-domain.com']['attribute-1'][0]);
        $this->assertArrayHasKey(1, $response['test-account@test-domain.com']['attribute-1']);
        $this->assertEquals('value-2', $response['test-account@test-domain.com']['attribute-1'][1]);
        $this->assertArrayHasKey('attribute-2', $response['test-account@test-domain.com']);
        $this->assertEquals('TRUE', $response['test-account@test-domain.com']['attribute-2']);
    }

    public function testGetAccountMultiple()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"/>
                    </soap:Header>
                    <soap:Body>
                        <GetAllAccountsResponse xmlns="urn:zimbraAdmin">
                            <account name="test-account@test-domain.com" id="dummy-id">
                                <a n="attribute-1">value-1</a>
                                <a n="attribute-2">TRUE</a>
                                <a n="attribute-1">value-2</a>
                            </account>
                            <account name="test-account2@test-domain.com" id="dummy-id2">
                                <a n="attribute-1">value-1</a>
                                <a n="attribute-2">TRUE</a>
                                <a n="attribute-1">value-2</a>
                            </account>
                        </GetAllAccountsResponse>
                    </soap:Body>
                </soap:Envelope>
XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $response = $this->connector->getAccounts('synaq.com');
        $this->assertArrayHasKey('test-account@test-domain.com', $response);
        $this->assertArrayHasKey('attribute-1', $response['test-account@test-domain.com']);
        $this->assertArrayHasKey(0, $response['test-account@test-domain.com']['attribute-1']);
        $this->assertEquals('value-1', $response['test-account@test-domain.com']['attribute-1'][0]);
        $this->assertArrayHasKey(1, $response['test-account@test-domain.com']['attribute-1']);
        $this->assertEquals('value-2', $response['test-account@test-domain.com']['attribute-1'][1]);
        $this->assertArrayHasKey('attribute-2', $response['test-account@test-domain.com']);
        $this->assertEquals('TRUE', $response['test-account@test-domain.com']['attribute-2']);

        $this->assertArrayHasKey('test-account2@test-domain.com', $response);
        $this->assertArrayHasKey('attribute-1', $response['test-account2@test-domain.com']);
        $this->assertArrayHasKey(0, $response['test-account2@test-domain.com']['attribute-1']);
        $this->assertEquals('value-1', $response['test-account2@test-domain.com']['attribute-1'][0]);
        $this->assertArrayHasKey(1, $response['test-account2@test-domain.com']['attribute-1']);
        $this->assertEquals('value-2', $response['test-account2@test-domain.com']['attribute-1'][1]);
        $this->assertArrayHasKey('attribute-2', $response['test-account2@test-domain.com']);
        $this->assertEquals('TRUE', $response['test-account2@test-domain.com']['attribute-2']);
    }

    public function testCreateDomain()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"/>
                    </soap:Header>
                    <soap:Body>
                        <CreateDomainResponse xmlns="urn:zimbraAdmin">
                            <domain id="dummy-domain-id" name="dummy-domain.com"/>
                        </CreateDomainResponse>
                    </soap:Body>
                </soap:Envelope>
XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $attr = array(
            'zimbraDomainStatus' => 'active',
            'zimbraPrefTimeZoneId' => '(GMT+02.00) Harare / Pretoria',
            'description' => 'domain description',
            'zimbraDomainDefaultCOSId' => 'DUMMY-COS-ID',
            'zimbraVirtualHostname' => 'mail.dummy-domain.com',
            'zimbraPublicServiceHostname' => 'mail.dummy-domain.com',
        );
        $id = $this->connector->createDomain('dummy-domain.com', $attr);
        $this->assertEquals('dummy-domain-id', $id);
    }

    public function testCreateDl()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"/>
                    </soap:Header>
                    <soap:Body>
                        <CreateDistributionListResponse xmlns="urn:zimbraAdmin">
                            <dl id="dummy-dl-id" name="zimbradomainadmins@dummy-domain.com"/>
                        </CreateDistributionListResponse>
                    </soap:Body>
                </soap:Envelope>
XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);
        $attr = array(
            'zimbraHideInGal'=> 'TRUE',
            'zimbraIsAdminGroup' => 'TRUE',
            'zimbraMailStatus' => 'disabled'
        );
        $views = array(
                'accountListView',
                'aliasListView',
                'resourceListView',
                'DLListView'
        );
        $id = $this->connector->createDl('zimbradoaminadmins@dummy-domain.com', $attr, $views);
        $this->assertEquals('dummy-dl-id', $id);
    }

    public function testGrantRight()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"/>
                    </soap:Header>
                    <soap:Body>
                        <GrantRightResponse xmlns="urn:zimbraAdmin"/>
                    </soap:Body>
                </soap:Envelope>
XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);
        $this->connector->grantRight('dummy-domain.com', 'domain', 'zimbradomainadmins@dummy-domain.com', 'grp', 'getAccount', 0);
    }

    public function testCreateMailbox()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"/>
                    </soap:Header>
                    <soap:Body>
                        <CreateAccountResponse xmlns="urn:zimbraAdmin">
                            <account id="dummy-account-id" name="test-account@dummy-domain.com">
                                <a n="zimbraMailHost">sample-host.sample-domain.com</a>
                                <a n="zimbraMailTrashLifetime">30d</a>
                            </account>
                        </CreateAccountResponse>
                    </soap:Body>
                </soap:Envelope>
XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);
        $attr = array(
            'displayName' => 'Joe Schmoe',
            'givenName' => 'Joe',
            'sn' => 'Schmoe',
            'zimbraPasswordMustChange' => 'TRUE',
            'zimbraIsDelegatedAdminAccount' => 'FALSE',
            'zimbraHideInGal' => 'FALSE',
            'zimbraCOSId' => 'dummy-cos-id',
            'description' => 'dummy description',
            'company' => 'Acme Ltd'
        );

        $id = $this->connector->createAccount('test-account@dummy-domain.com', 'dummy-password', $attr, $returnAttrs);

        $this->assertEquals('dummy-account-id', $id);
        $this->assertInternalType('array', $returnAttrs, "Return attributes not array");
        $this->assertArrayHasKey('zimbraMailHost', $returnAttrs, "Zimbra mail host not returned");
        $this->assertEquals('sample-host.sample-domain.com', $returnAttrs['zimbraMailHost'], "Incorrect Zimbra mail host returned");
        $this->assertArrayHasKey('zimbraMailTrashLifetime', $returnAttrs, "Zimbra trash lifetime not returned");
        $this->assertEquals('30d', $returnAttrs['zimbraMailTrashLifetime'], "Incorrect Zimbra trash lifetime returned");
    }

    public function testCreateMailboxIgnoreProperties()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"/>
                    </soap:Header>
                    <soap:Body>
                        <CreateAccountResponse xmlns="urn:zimbraAdmin">
                            <account id="dummy-account-id" name="test-account@dummy-domain.com">
                                <a n="zimbraMailHost">sample-host.sample-domain.com</a>
                                <a n="zimbraMailTrashLifetime">30d</a>
                            </account>
                        </CreateAccountResponse>
                    </soap:Body>
                </soap:Envelope>
XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);
        $attr = array(
            'displayName' => 'Joe Schmoe',
            'givenName' => 'Joe',
            'sn' => 'Schmoe',
            'zimbraPasswordMustChange' => 'TRUE',
            'zimbraIsDelegatedAdminAccount' => 'FALSE',
            'zimbraHideInGal' => 'FALSE',
            'zimbraCOSId' => 'dummy-cos-id',
            'description' => 'dummy description',
            'company' => 'Acme Ltd'
        );

        $id = $this->connector->createAccount('test-account@dummy-domain.com', 'dummy-password', $attr);

        $this->assertEquals('dummy-account-id', $id);
    }

    public function testAddDlMember()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
                    <soap:Header>
                        <context xmlns="urn:zimbra"/>
                    </soap:Header>
                    <soap:Body>
                        <AddDistributionListMemberResponse xmlns="urn:zimbraAdmin"/>
                    </soap:Body>
                </soap:Envelope>
XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(2)->andReturnValues(
                array(
                    $this->loginResponse,
                    $response
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);
        $this->connector->addDlMember('dummy-dl-id', 'test-account@dummy-domain.com');
    }

    public function testGetAccountQuotaUsed()
    {
        if ($this->mock) {
            $raw = $this->httpHead;
            $raw .= <<<'XML'
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
    <soap:Header>
        <context xmlns="urn:zimbra">
            <change token="113"/>
        </context>
    </soap:Header>
    <soap:Body>
        <GetInfoResponse docSizeLimit="10485760" attSizeLimit="10240000" xmlns="urn:zimbraAccount">
            <version>8.5.1_GA_3056 20141103151728 20141103-1535 NETWORK</version>
            <id>3548d7cf-5adc-4151-af80-2eead4a889ac</id>
            <name>test@test-domain19.com</name>
            <crumb>a0ef3a741659a88850edb25ea51aec1b</crumb>
            <lifetime>3519456</lifetime>
            <adminDelegated>1</adminDelegated>
            <rest>http://cloudmail.synaq.com/home/test@test-domain19.com</rest>
            <used>932</used>
            <prevSession>1424156588000</prevSession>
            <accessed>1424156588000</accessed>
            <recent>0</recent>
            <cos name="default" id="e00428a1-0c00-11d9-836a-000d93afea2a"/>
            <prefs>
                <pref name="zimbraPrefCalendarReminderMobile">FALSE</pref>
                <pref name="zimbraPrefIMLogChats">TRUE</pref>
                <pref name="zimbraPrefFileSharingApplication">briefcase</pref>
                <pref name="zimbraPrefCalendarWorkingHours">
                    1:N:0800:1700,2:Y:0800:1700,3:Y:0800:1700,4:Y:0800:1700,5:Y:0800:1700,6:Y:0800:1700,7:N:0800:1700
                </pref>
                <pref name="zimbraPrefCalendarViewTimeInterval">1h</pref>
                <pref name="zimbraPrefComposeFormat">html</pref>
                <pref name="zimbraPrefIMNotifyStatus">TRUE</pref>
                <pref name="zimbraPrefIMReportIdle">TRUE</pref>
                <pref name="zimbraPrefSaveToSent">TRUE</pref>
                <pref name="zimbraPrefDisplayExternalImages">FALSE</pref>
                <pref name="zimbraPrefOutOfOfficeCacheDuration">7d</pref>
                <pref name="zimbraPrefConvReadingPaneLocation">bottom</pref>
                <pref name="zimbraPrefShowSearchString">FALSE</pref>
                <pref name="zimbraPrefMailSelectAfterDelete">next</pref>
                <pref name="zimbraPrefAppleIcalDelegationEnabled">FALSE</pref>
                <pref name="zimbraPrefHtmlEditorDefaultFontFamily">arial, helvetica, sans-serif</pref>
                <pref name="zimbraPrefConvShowCalendar">FALSE</pref>
                <pref name="zimbraPrefCalendarShowPastDueReminders">TRUE</pref>
                <pref name="zimbraPrefWarnOnExit">TRUE</pref>
                <pref name="zimbraPrefReadingPaneEnabled">TRUE</pref>
                <pref name="zimbraPrefIMToasterEnabled">FALSE</pref>
                <pref name="zimbraPrefOutOfOfficeStatusAlertOnLogin">TRUE</pref>
                <pref name="zimbraPrefAutocompleteAddressBubblesEnabled">TRUE</pref>
                <pref name="zimbraPrefContactsInitialView">list</pref>
                <pref name="zimbraPrefVoiceItemsPerPage">25</pref>
                <pref name="zimbraPrefMailToasterEnabled">FALSE</pref>
                <pref name="zimbraPrefForwardReplyInOriginalFormat">TRUE</pref>
                <pref name="zimbraPrefBriefcaseReadingPaneLocation">right</pref>
                <pref name="zimbraPrefContactsPerPage">25</pref>
                <pref name="zimbraPrefMarkMsgRead">0</pref>
                <pref name="zimbraPrefMessageIdDedupingEnabled">TRUE</pref>
                <pref name="zimbraPrefCalendarApptReminderWarningTime">5</pref>
                <pref name="zimbraPrefCalendarReminderYMessenger">FALSE</pref>
                <pref name="zimbraPrefDeleteInviteOnReply">TRUE</pref>
                <pref name="zimbraPrefCalendarDefaultApptDuration">60m</pref>
                <pref name="zimbraPrefCalendarDayHourStart">8</pref>
                <pref name="zimbraPrefPop3DeleteOption">delete</pref>
                <pref name="zimbraPrefCalendarAutoAddInvites">TRUE</pref>
                <pref name="zimbraPrefExternalSendersType">ALL</pref>
                <pref name="zimbraPrefContactsDisableAutocompleteOnContactGroupMembers">FALSE</pref>
                <pref name="zimbraPrefIMFlashTitle">TRUE</pref>
                <pref name="zimbraPrefSentLifetime">0</pref>
                <pref name="zimbraPrefAutoCompleteQuickCompletionOnComma">TRUE</pref>
                <pref name="zimbraPrefMailFlashIcon">FALSE</pref>
                <pref name="zimbraPrefMailSoundsEnabled">FALSE</pref>
                <pref name="zimbraPrefFolderColorEnabled">TRUE</pref>
                <pref name="zimbraPrefIMSoundsEnabled">TRUE</pref>
                <pref name="zimbraPrefGalAutoCompleteEnabled">TRUE</pref>
                <pref name="zimbraPrefIMHideBlockedBuddies">FALSE</pref>
                <pref name="zimbraPrefCalendarReminderSoundsEnabled">TRUE</pref>
                <pref name="zimbraPrefCalendarShowDeclinedMeetings">TRUE</pref>
                <pref name="zimbraPrefIMInstantNotify">TRUE</pref>
                <pref name="zimbraPrefMailInitialSearch">in:inbox</pref>
                <pref name="zimbraPrefIMNotifyPresence">TRUE</pref>
                <pref name="zimbraPrefMandatorySpellCheckEnabled">FALSE</pref>
                <pref name="zimbraPrefDedupeMessagesSentToSelf">dedupeNone</pref>
                <pref name="zimbraPrefHtmlEditorDefaultFontSize">12pt</pref>
                <pref name="zimbraPrefSentMailFolder">sent</pref>
                <pref name="zimbraPrefCalendarApptVisibility">public</pref>
                <pref name="zimbraPrefCalendarDayHourEnd">18</pref>
                <pref name="zimbraPrefShowComposeDirection">FALSE</pref>
                <pref name="zimbraPrefShowCalendarWeek">FALSE</pref>
                <pref name="zimbraPrefClientType">advanced</pref>
                <pref name="zimbraPrefIMAutoLogin">FALSE</pref>
                <pref name="zimbraPrefCalendarAlwaysShowMiniCal">TRUE</pref>
                <pref name="zimbraPrefHtmlEditorDefaultFontColor">#000000</pref>
                <pref name="zimbraPrefTasksReadingPaneLocation">right</pref>
                <pref name="zimbraPrefItemsPerVirtualPage">50</pref>
                <pref name="zimbraPrefSearchTreeOpen">TRUE</pref>
                <pref name="zimbraPrefStandardClientAccessibilityMode">FALSE</pref>
                <pref name="zimbraPrefUseRfc2231">FALSE</pref>
                <pref name="zimbraPrefCalendarNotifyDelegatedChanges">FALSE</pref>
                <pref name="zimbraPrefConversationOrder">dateDesc</pref>
                <pref name="zimbraPrefMailSignature">test bloop sig</pref>
                <pref name="zimbraPrefIncludeSharedItemsInSearch">FALSE</pref>
                <pref name="zimbraPrefShowSelectionCheckbox">FALSE</pref>
                <pref name="zimbraPrefPop3IncludeSpam">FALSE</pref>
                <pref name="zimbraPrefCalendarReminderFlashTitle">TRUE</pref>
                <pref name="zimbraPrefDefaultPrintFontSize">12pt</pref>
                <pref name="zimbraPrefMessageViewHtmlPreferred">TRUE</pref>
                <pref name="zimbraPrefMailFlashTitle">FALSE</pref>
                <pref name="zimbraPrefMailPollingInterval">5m</pref>
                <pref name="zimbraPrefFontSize">normal</pref>
                <pref name="zimbraPrefIMLogChatsEnabled">TRUE</pref>
                <pref name="zimbraPrefReplyIncludeOriginalText">includeBody</pref>
                <pref name="zimbraPrefIncludeTrashInSearch">FALSE</pref>
                <pref name="zimbraPrefSharedAddrBookAutoCompleteEnabled">FALSE</pref>
                <pref name="zimbraPrefCalendarAllowCancelEmailToSelf">FALSE</pref>
                <pref name="zimbraPrefCalendarAllowPublishMethodInvite">FALSE</pref>
                <pref name="zimbraPrefIMIdleStatus">away</pref>
                <pref name="zimbraPrefGroupMailBy">conversation</pref>
                <pref name="zimbraPrefCalendarAllowForwardedInvite">TRUE</pref>
                <pref name="zimbraPrefZimletTreeOpen">FALSE</pref>
                <pref name="zimbraPrefMailSignatureEnabled">TRUE</pref>
                <pref name="zimbraPrefCalendarUseQuickAdd">TRUE</pref>
                <pref name="zimbraPrefComposeInNewWindow">FALSE</pref>
                <pref name="zimbraPrefGalSearchEnabled">TRUE</pref>
                <pref name="zimbraPrefJunkLifetime">0</pref>
                <pref name="zimbraPrefSpellIgnoreAllCaps">TRUE</pref>
                <pref name="zimbraPrefUseTimeZoneListInCalendar">FALSE</pref>
                <pref name="zimbraPrefCalendarAllowedTargetsForInviteDeniedAutoReply">internal</pref>
                <pref name="zimbraPrefOpenMailInNewWindow">FALSE</pref>
                <pref name="zimbraPrefMailSignatureStyle">outlook</pref>
                <pref name="zimbraPrefAdminConsoleWarnOnExit">TRUE</pref>
                <pref name="zimbraPrefTrashLifetime">0</pref>
                <pref name="zimbraPrefShowFragments">TRUE</pref>
                <pref name="zimbraPrefContactsExpandAppleContactGroups">FALSE</pref>
                <pref name="zimbraPrefOutOfOfficeReplyEnabled">FALSE</pref>
                <pref name="zimbraPrefIMFlashIcon">TRUE</pref>
                <pref name="zimbraPrefMailRequestReadReceipts">FALSE</pref>
                <pref name="zimbraPrefCalendarReminderDuration1">-PT15M</pref>
                <pref name="zimbraPrefAdvancedClientEnforceMinDisplay">TRUE</pref>
                <pref name="zimbraPrefCalendarFirstDayOfWeek">0</pref>
                <pref name="zimbraPrefSkin">harmony</pref>
                <pref name="zimbraPrefForwardReplyPrefixChar">></pref>
                <pref name="zimbraPrefAccountTreeOpen">TRUE</pref>
                <pref name="zimbraPrefAutoSaveDraftInterval">30s</pref>
                <pref name="zimbraPrefCalendarToasterEnabled">FALSE</pref>
                <pref name="zimbraPrefColorMessagesEnabled">FALSE</pref>
                <pref name="zimbraPrefCalendarApptAllowAtendeeEdit">TRUE</pref>
                <pref name="zimbraPrefIncludeSpamInSearch">FALSE</pref>
                <pref name="zimbraPrefCalendarInitialView">workWeek</pref>
                <pref name="zimbraPrefFolderTreeOpen">TRUE</pref>
                <pref name="zimbraPrefInboxUnreadLifetime">0</pref>
                <pref name="zimbraPrefImapSearchFoldersEnabled">TRUE</pref>
                <pref name="zimbraPrefMailSendReadReceipts">prompt</pref>
                <pref name="zimbraPrefForwardIncludeOriginalText">includeBody</pref>
                <pref name="zimbraPrefMailItemsPerPage">25</pref>
                <pref name="zimbraPrefUseKeyboardShortcuts">TRUE</pref>
                <pref name="zimbraPrefTimeZoneId">Africa/Maputo</pref>
                <pref name="zimbraPrefShortEmailAddress">TRUE</pref>
                <pref name="zimbraPrefIMHideOfflineBuddies">FALSE</pref>
                <pref name="zimbraPrefInboxReadLifetime">0</pref>
                <pref name="zimbraPrefTagTreeOpen">TRUE</pref>
                <pref name="zimbraPrefGetMailAction">default</pref>
                <pref name="zimbraPrefAutoAddAddressEnabled">TRUE</pref>
                <pref name="zimbraPrefReadingPaneLocation">right</pref>
                <pref name="zimbraPrefCalendarReminderSendEmail">FALSE</pref>
                <pref name="zimbraPrefCalendarSendInviteDeniedAutoReply">FALSE</pref>
                <pref name="zimbraPrefIMIdleTimeout">10</pref>
            </prefs>
            <attrs>
                <attr name="zimbraDeviceLockWhenInactive">FALSE</attr>
                <attr name="zimbraFeatureImportFolderEnabled">TRUE</attr>
                <attr name="zimbraFeatureOptionsEnabled">TRUE</attr>
                <attr name="zimbraFeatureAdvancedSearchEnabled">TRUE</attr>
                <attr name="zimbraFeatureTasksEnabled">TRUE</attr>
                <attr name="zimbraFeatureOutOfOfficeReplyEnabled">TRUE</attr>
                <attr name="zimbraDevicePasscodeEnabled">FALSE</attr>
                <attr name="zimbraFeatureNewAddrBookEnabled">TRUE</attr>
                <attr name="zimbraFeatureMailForwardingEnabled">TRUE</attr>
                <attr name="zimbraFeatureVoiceChangePinEnabled">TRUE</attr>
                <attr name="zimbraPasswordMinAlphaChars">0</attr>
                <attr name="zimbraMailSpamLifetime">30d</attr>
                <attr name="zimbraMailForwardingAddressMaxNumAddrs">100</attr>
                <attr name="zimbraFileUploadMaxSize">10485760</attr>
                <attr name="zimbraMailTrustedSenderListMaxNumEntries">500</attr>
                <attr name="zimbraMailQuota">0</attr>
                <attr name="zimbraFeatureZimbraAssistantEnabled">TRUE</attr>
                <attr name="displayName">last</attr>
                <attr name="zimbraFeatureGroupCalendarEnabled">TRUE</attr>
                <attr name="zimbraFilterBatchSize">10000</attr>
                <attr name="zimbraSignatureMaxNumEntries">20</attr>
                <attr name="uid">test</attr>
                <attr name="zimbraAttachmentsBlocked">FALSE</attr>
                <attr name="zimbraFeatureManageSMIMECertificateEnabled">FALSE</attr>
                <attr name="zimbraMailDumpsterLifetime">30d</attr>
                <attr name="zimbraDataSourceMinPollingInterval">1m</attr>
                <attr name="zimbraCalendarKeepExceptionsOnSeriesTimeChange">FALSE</attr>
                <attr name="cn">last</attr>
                <attr name="zimbraFileExternalShareLifetime">90d</attr>
                <attr name="zimbraFeaturePriorityInboxEnabled">TRUE</attr>
                <attr name="zimbraFeatureTaggingEnabled">TRUE</attr>
                <attr name="zimbraFeatureBriefcaseSpreadsheetEnabled">FALSE</attr>
                <attr name="zimbraCalendarShowResourceTabs">TRUE</attr>
                <attr name="zimbraMailIdleSessionTimeout">0</attr>
                <attr name="zimbraDeviceOfflineCacheEnabled">FALSE</attr>
                <attr name="zimbraMobileMetadataMaxSizeEnabled">FALSE</attr>
                <attr name="zimbraPop3Enabled">TRUE</attr>
                <attr name="zimbraFeatureMailPriorityEnabled">TRUE</attr>
                <attr name="zimbraDataSourceCalendarPollingInterval">12h</attr>
                <attr name="zimbraFeatureManageZimlets">TRUE</attr>
                <attr name="zimbraPasswordMinNumericChars">0</attr>
                <attr name="zimbraWebClientShowOfflineLink">TRUE</attr>
                <attr name="zimbraFeatureCalendarEnabled">TRUE</attr>
                <attr name="zimbraMailBlacklistMaxNumEntries">100</attr>
                <attr name="zimbraFeatureDiscardInFiltersEnabled">TRUE</attr>
                <attr name="zimbraMailMinPollingInterval">2m</attr>
                <attr name="zimbraMailHighlightObjectsMaxSize">70</attr>
                <attr name="zimbraFeatureSocialExternalEnabled">FALSE</attr>
                <attr name="zimbraFeaturePop3DataSourceEnabled">TRUE</attr>
                <attr name="zimbraFileAndroidCrashReportingEnabled">TRUE</attr>
                <attr name="zimbraFeatureWebSearchEnabled">TRUE</attr>
                <attr name="zimbraPasswordMinUpperCaseChars">0</attr>
                <attr name="zimbraMobileForceProtocol25">FALSE</attr>
                <attr name="zimbraMaxVoiceItemsPerPage">100</attr>
                <attr name="zimbraPublicSharingEnabled">TRUE</attr>
                <attr name="zimbraDataSourceMaxNumEntries">20</attr>
                <attr name="zimbraZimletLoadSynchronously">FALSE</attr>
                <attr name="zimbraFeatureViewInHtmlEnabled">FALSE</attr>
                <attr name="zimbraFeatureIMEnabled">FALSE</attr>
                <attr name="zimbraMailSignatureMaxLength">10240</attr>
                <attr name="zimbraContactAutoCompleteMaxResults">20</attr>
                <attr name="zimbraFeatureSignaturesEnabled">TRUE</attr>
                <attr name="zimbraPasswordMinDigitsOrPuncs">0</attr>
                <attr name="zimbraPasswordMinPunctuationChars">0</attr>
                <attr name="zimbraFilePublicShareLifetime">0</attr>
                <attr name="zimbraMtaMaxMessageSize">10240000</attr>
                <attr name="zimbraExternalShareLifetime">0</attr>
                <attr name="zimbraFeatureWebClientOfflineAccessEnabled">TRUE</attr>
                <attr name="zimbraMobileTombstoneEnabled">TRUE</attr>
                <attr name="zimbraFeatureImapDataSourceEnabled">TRUE</attr>
                <attr name="zimbraFeatureSocialEnabled">FALSE</attr>
                <attr name="zimbraSignatureMinNumEntries">1</attr>
                <attr name="zimbraMaxMailItemsPerPage">100</attr>
                <attr name="zimbraLocale">en_US</attr>
                <attr name="zimbraFeatureSharingEnabled">TRUE</attr>
                <attr name="zimbraFeatureMailUpsellEnabled">FALSE</attr>
                <attr name="zimbraFeatureSavedSearchesEnabled">TRUE</attr>
                <attr name="zimbraFeatureMailSendLaterEnabled">FALSE</attr>
                <attr name="zimbraPortalName">example</attr>
                <attr name="zimbraPasswordMaxLength">64</attr>
                <attr name="zimbraFeatureFreeBusyViewEnabled">FALSE</attr>
                <attr name="zimbraZimletAvailableZimlets">!com_zimbra_attachmail</attr>
                <attr name="zimbraZimletAvailableZimlets">+com_zimbra_phone</attr>
                <attr name="zimbraZimletAvailableZimlets">+com_zimbra_srchhighlighter</attr>
                <attr name="zimbraZimletAvailableZimlets">!com_zimbra_url</attr>
                <attr name="zimbraZimletAvailableZimlets">+com_zimbra_mailarchive</attr>
                <attr name="zimbraZimletAvailableZimlets">+com_zimbra_linkedinimage</attr>
                <attr name="zimbraZimletAvailableZimlets">!com_zimbra_email</attr>
                <attr name="zimbraZimletAvailableZimlets">+com_zimbra_smime</attr>
                <attr name="zimbraZimletAvailableZimlets">+com_zimbra_webex</attr>
                <attr name="zimbraZimletAvailableZimlets">+com_zimbra_ymemoticons</attr>
                <attr name="zimbraZimletAvailableZimlets">!com_zimbra_date</attr>
                <attr name="zimbraZimletAvailableZimlets">!com_zimbra_attachcontacts</attr>
                <attr name="zimbraFeatureTouchClientEnabled">FALSE</attr>
                <attr name="zimbraDumpsterEnabled">FALSE</attr>
                <attr name="zimbraAttachmentsViewInHtmlOnly">FALSE</attr>
                <attr name="zimbraPrefColorMessagesEnabled">FALSE</attr>
                <attr name="zimbraMaxContactsPerPage">100</attr>
                <attr name="zimbraFeatureBriefcasesEnabled">TRUE</attr>
                <attr name="zimbraFeatureCrocodocEnabled">FALSE</attr>
                <attr name="zimbraFeatureContactsUpsellEnabled">FALSE</attr>
                <attr name="zimbraMobileOutlookSyncEnabled">TRUE</attr>
                <attr name="zimbraFeatureVoiceUpsellEnabled">FALSE</attr>
                <attr name="zimbraDeviceAllowedPasscodeLockoutDuration">10m</attr>
                <attr name="zimbraDeviceAllowedPasscodeLockoutDuration">1m</attr>
                <attr name="zimbraDeviceAllowedPasscodeLockoutDuration">2m</attr>
                <attr name="zimbraDeviceAllowedPasscodeLockoutDuration">30m</attr>
                <attr name="zimbraDeviceAllowedPasscodeLockoutDuration">5m</attr>
                <attr name="zimbraFeatureContactsEnabled">TRUE</attr>
                <attr name="zimbraFeatureComposeInNewWindowEnabled">TRUE</attr>
                <attr name="zimbraFeatureFlaggingEnabled">TRUE</attr>
                <attr name="zimbraFeatureContactsDetailedSearchEnabled">FALSE</attr>
                <attr name="zimbraFeatureInstantNotify">TRUE</attr>
                <attr name="zimbraFeatureSocialFiltersEnabled">Facebook</attr>
                <attr name="zimbraFeatureSocialFiltersEnabled">LinkedIn</attr>
                <attr name="zimbraFeatureSocialFiltersEnabled">SocialCast</attr>
                <attr name="zimbraFeatureSocialFiltersEnabled">Twitter</attr>
                <attr name="zimbraFeatureMailPollingIntervalPreferenceEnabled">TRUE</attr>
                <attr name="zimbraIdentityMaxNumEntries">20</attr>
                <attr name="zimbraFeatureAdminMailEnabled">TRUE</attr>
                <attr name="zimbraFeatureDistributionListFolderEnabled">TRUE</attr>
                <attr name="zimbraDataSourceImportOnLogin">FALSE</attr>
                <attr name="zimbraFeatureMAPIConnectorEnabled">TRUE</attr>
                <attr name="zimbraShareLifetime">0</attr>
                <attr name="zimbraMailWhitelistMaxNumEntries">100</attr>
                <attr name="zimbraPublicShareLifetime">0</attr>
                <attr name="zimbraCalendarResourceDoubleBookingAllowed">TRUE</attr>
                <attr name="zimbraFileIOSCrashReportingEnabled">TRUE</attr>
                <attr name="zimbraFeatureConfirmationPageEnabled">FALSE</attr>
                <attr name="zimbraWebClientOfflineSyncMaxDays">30</attr>
                <attr name="zimbraFeatureConversationsEnabled">TRUE</attr>
                <attr name="zimbraFeatureDistributionListExpandMembersEnabled">TRUE</attr>
                <attr name="zimbraFeatureNewMailNotificationEnabled">TRUE</attr>
                <attr name="zimbraPasswordMinLength">6</attr>
                <attr name="zimbraFeatureImportExportFolderEnabled">TRUE</attr>
                <attr name="zimbraFeatureOpenMailInNewWindowEnabled">TRUE</attr>
                <attr name="zimbraFileShareLifetime">0</attr>
                <attr name="zimbraMobileNotificationEnabled">FALSE</attr>
                <attr name="zimbraFeatureGalEnabled">TRUE</attr>
                <attr name="zimbraFilePreviewMaxSize">20971520</attr>
                <attr name="zimbraPasswordMinLowerCaseChars">0</attr>
                <attr name="zimbraFeaturePeopleSearchEnabled">TRUE</attr>
                <attr name="zimbraContactMaxNumEntries">10000</attr>
                <attr name="zimbraMailMessageLifetime">0</attr>
                <attr name="zimbraAllowAnyFromAddress">FALSE</attr>
                <attr name="zimbraFeatureExternalFeedbackEnabled">FALSE</attr>
                <attr name="zimbraSmtpRestrictEnvelopeFrom">TRUE</attr>
                <attr name="zimbraIMService">zimbra</attr>
                <attr name="zimbraFeatureBriefcaseDocsEnabled">TRUE</attr>
                <attr name="zimbraFeatureReadReceiptsEnabled">TRUE</attr>
                <attr name="zimbraExternalSharingEnabled">TRUE</attr>
                <attr name="zimbraMobileShareContactEnabled">FALSE</attr>
                <attr name="zimbraFeatureAntispamEnabled">TRUE</attr>
                <attr name="zimbraFeatureGalAutoCompleteEnabled">TRUE</attr>
                <attr name="zimbraTouchJSErrorTrackingEnabled">FALSE</attr>
                <attr name="zimbraFeatureNotebookEnabled">FALSE</attr>
                <attr name="zimbraFeatureChangePasswordEnabled">TRUE</attr>
                <attr name="zimbraFeatureSkinChangeEnabled">TRUE</attr>
                <attr name="zimbraFeatureMobilePolicyEnabled">TRUE</attr>
                <attr name="zimbraDeviceFileOpenWithEnabled">TRUE</attr>
                <attr name="zimbraDataSourceRssPollingInterval">12h</attr>
                <attr name="zimbraMailForwardingAddressMaxLength">4096</attr>
                <attr name="zimbraStandardClientCustomPrefTabsEnabled">FALSE</attr>
                <attr name="zimbraFeatureMailEnabled">TRUE</attr>
                <attr name="zimbraFeaturePortalEnabled">FALSE</attr>
                <attr name="zimbraFeatureBriefcaseSlidesEnabled">FALSE</attr>
                <attr name="zimbraMobileAttachSkippedItemEnabled">FALSE</attr>
                <attr name="zimbraFeatureMailForwardingInFiltersEnabled">TRUE</attr>
                <attr name="zimbraFeatureCalendarReminderDeviceEmailEnabled">FALSE</attr>
                <attr name="zimbraFeatureShortcutAliasesEnabled">TRUE</attr>
                <attr name="zimbraFeatureSocialcastEnabled">FALSE</attr>
                <attr name="zimbraFeatureHtmlComposeEnabled">TRUE</attr>
                <attr name="zimbraFeatureCalendarUpsellEnabled">FALSE</attr>
                <attr name="zimbraFeatureFiltersEnabled">TRUE</attr>
                <attr name="zimbraFeatureFromDisplayEnabled">TRUE</attr>
                <attr name="zimbraMobileForceSamsungProtocol25">FALSE</attr>
                <attr name="zimbraFeatureInitialSearchPreferenceEnabled">TRUE</attr>
                <attr name="zimbraFeatureMobileSyncEnabled">FALSE</attr>
                <attr name="zimbraId">3548d7cf-5adc-4151-af80-2eead4a889ac</attr>
                <attr name="zimbraFeatureExportFolderEnabled">TRUE</attr>
                <attr name="zimbraMailTrashLifetime">30d</attr>
                <attr name="zimbraFeatureGalSyncEnabled">TRUE</attr>
                <attr name="zimbraFeatureIdentitiesEnabled">TRUE</attr>
                <attr name="zimbraMobileSyncRedoMaxAttempts">default:1</attr>
                <attr name="zimbraMobileSyncRedoMaxAttempts">windows:2</attr>
            </attrs>
    </GetInfoResponse></soap:Body></soap:Envelope>

XML;

            $response = new Response($raw);

            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $response
                )
            );
        }
        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);
        $quota = $this->connector->getAccountQuotaUsed('test@test-domain19.com');

        $this->assertEquals('932/0', $quota);
    }

    public function testCreateContact()
    {
        if ($this->mock) {
            $gfr = $this->httpHead;
            $gfr .= <<<'XML'
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
    <soap:Header>
        <context xmlns="urn:zimbra">
            <change token="180"/>
        </context>
    </soap:Header>
    <soap:Body>
        <GetFolderResponse xmlns="urn:zimbraMail">
            <folder i4ms="1" rev="1" i4next="2" ms="1" l="11" uuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" n="0"
                    luuid="03bef865-57aa-44ca-bc85-922b03f742f5" activesyncdisabled="0" absFolderPath="/" s="0"
                    name="USER_ROOT" id="1" webOfflineSyncDays="0">
                <folder i4ms="1" rev="1" i4next="17" ms="1" l="1" uuid="93d4cd09-c226-4606-92bc-407f27e6164d" n="0"
                        luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Briefcase"
                        view="document" s="0" name="Briefcase" id="16" webOfflineSyncDays="0"/>
                <folder i4ms="1" rev="1" i4next="11" f="#" ms="1" l="1" uuid="7b190a56-cda8-4804-9986-cc9868dd0903"
                        n="0" luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0"
                        absFolderPath="/Calendar" view="appointment" s="0" name="Calendar" id="10"
                        webOfflineSyncDays="0"/>
                <folder i4ms="1" rev="1" i4next="15" ms="1" l="1" uuid="6efdca68-8aa4-46a1-bfbe-421dc41bf4e7" n="0"
                        luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Chats"
                        view="message" s="0" name="Chats" id="14" webOfflineSyncDays="0"/>
                <folder i4ms="171" rev="1" i4next="262" ms="1" l="1" uuid="63939fe0-7fe7-4e20-8cb9-dee09ab6e813" n="2"
                        luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Contacts"
                        view="contact" s="0" name="Contacts" id="7" webOfflineSyncDays="0"/>
                <folder i4ms="81" rev="1" i4next="258" ms="1" l="1" uuid="c06f4582-f79e-4811-ae68-6055a152f5b8" n="0"
                        luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Drafts"
                        view="message" s="0" name="Drafts" id="6" webOfflineSyncDays="30"/>
                <folder i4ms="80" rev="1" i4next="260" ms="1" l="1" uuid="58050e44-f23a-491a-8e5d-def48cdaf338" n="1"
                        luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0"
                        absFolderPath="/Emailed Contacts" view="contact" s="0" name="Emailed Contacts" id="13"
                        webOfflineSyncDays="0"/>
                <folder i4ms="83" rev="1" i4next="260" f="u" ms="1" l="1" uuid="9eadda48-a1d7-4be0-991c-94265fc05b8b"
                        n="1" luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Inbox"
                        view="message" s="932" u="1" name="Inbox" id="2" webOfflineSyncDays="30"/>
                <folder i4ms="1" rev="1" i4next="5" ms="1" l="1" uuid="b9f8e890-2a16-456c-ba04-39a94f2df084" n="0"
                        luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Junk"
                        view="message" s="0" name="Junk" id="4" webOfflineSyncDays="0"/>
                <folder i4ms="82" rev="1" i4next="259" ms="1" l="1" uuid="d207e35f-df71-4f50-9105-cade5b54ed32" n="0"
                        luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Sent"
                        view="message" s="0" name="Sent" id="5" webOfflineSyncDays="30"/>
                <folder i4ms="1" rev="1" i4next="16" f="#" ms="1" l="1" uuid="6b7e86d7-1f2c-441e-9b03-9de76b1c2acc"
                        n="0" luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Tasks"
                        view="task" s="0" name="Tasks" id="15" webOfflineSyncDays="0"/>
                <folder i4ms="1" rev="1" i4next="4" ms="1" l="1" uuid="17870a64-ad40-479b-b3ea-ecca6673dd85" n="0"
                        luuid="ef77d0ed-8f27-4c49-98d1-906ccdb5dda4" activesyncdisabled="0" absFolderPath="/Trash" s="0"
                        name="Trash" id="3" webOfflineSyncDays="30"/>
            </folder>
        </GetFolderResponse>
    </soap:Body>
</soap:Envelope>
XML;
            $getFoldersResponse = new Response($gfr);

            $ccr = $this->httpHead;
            $ccr .= <<<'XML'
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
    <soap:Header>
        <context xmlns="urn:zimbra">
            <change token="181"/>
        </context>
    </soap:Header>
    <soap:Body>
        <CreateContactResponse xmlns="urn:zimbraMail">
            <cn fileAsStr="last, first" rev="181" d="1424264251000" id="262" l="7">
                <a n="firstName">first</a>
                <a n="lastName">last</a>
                <a n="email">test@test.com</a>
            </cn>
        </CreateContactResponse>
    </soap:Body>
</soap:Envelope>
XML;
            $createContactResponse = new Response($ccr);

            $this->mockClient->shouldReceive('post')->times(4)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $getFoldersResponse,
                    $createContactResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $this->connector->createContact('test@test-domain19.com', array('firstName' => 'first', 'lastName' => 'last', 'email' => 'test@test.com'));
    }

    public function testCreateSignature()
    {
        if ($this->mock) {
            $csr = $this->httpHead;
            $csr .= <<<'XML'
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
    <soap:Header>
        <context xmlns="urn:zimbra">
            <change token="247"/>
        </context>
    </soap:Header>
    <soap:Body>
        <CreateSignatureResponse xmlns="urn:zimbraAccount">
            <signature name="Primary" id="b7f7d8d2-da88-4da4-8572-84f1408f0696"/>
        </CreateSignatureResponse>
    </soap:Body>
</soap:Envelope>
XML;
            $csResponse = new Response($csr);
            $this->mockClient->shouldReceive('post')->times(3)->andReturnValues(
                array(
                    $this->loginResponse,
                    $this->delegateResponse,
                    $csResponse
                )
            );
        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $id = $this->connector->createSignature('test@test-domain19.com', 'Primary', 'text/plain', 'Signature content');
        $this->assertEquals('b7f7d8d2-da88-4da4-8572-84f1408f0696', $id);
    }

    public function testRenameAccount()
    {
        if ($this->mock) {
            $rar = $this->httpHead;
            $rar .= <<<'XML'
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
    <soap:Header>
        <context xmlns="urn:zimbra">
            <change token="247"/>
        </context>
    </soap:Header>
    <soap:Body>
        <RenameAccountResponse xmlns="urn:zimbraAccount">
            <account name="updated-test2@displayname2.com" id="dummy-id"/>
        </RenameAccountResponse>
    </soap:Body>
</soap:Envelope>
XML;

        }

        $this->connector = new ZimbraConnector($this->mockClient, $this->server, $this->username, $this->password);

        $id = 'dummy-id';
        $newAddress = 'updated-test2@displayname1.com';

        $this->connector->renameAccount($id, $newAddress);
    }
}
