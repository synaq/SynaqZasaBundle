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
    private $server = 'test.com';

    /**
     * @var string
     */
    private $username = 'dummy-user';

    /**
     * @var string
     */
    private $password = 'dummy-pw';

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

        $this->connector->getFolder('user1@testdomain3.co.za.archive', 2);
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
                            <account id="dummy-account-id" name="test-account@dummy-domain.com"/>
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
}
