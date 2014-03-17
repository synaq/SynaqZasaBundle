<?php
namespace Synaq\ZasaBundle\Connector;

use Synaq\CurlBundle\Curl\Wrapper;
use Synaq\ZasaBundle\Exception\SoapFaultException;
use Synaq\ZasaBundle\Util\Array2Xml;
use Synaq\ZasaBundle\Util\Xml2Array;

class ZimbraConnector
{
    /**
     * @var Wrapper
     */
    private $httpClient;

    /**
     * @var string
     */
    private $server;

    /**
     * @var string
     */
    private $adminUser;

    /**
     * @var string
     */
    private $adminPass;

    /**
     * @var string
     */
    private $authToken = false;

    /**
     * @var string
     */
    private $delegatedAuthToken = false;

    /**
     * @var string
     */
    private $delegatedAuthAccount = false;


    /**
     * @param \Synaq\CurlBundle\Curl\Wrapper $httpClient
     * @param $server
     * @param $adminUser
     * @param $adminPass
     */
    public function __construct(Wrapper $httpClient, $server, $adminUser, $adminPass)
    {
        $this->httpClient = $httpClient;
        $this->server = $server;
        $this->adminUser = $adminUser;
        $this->adminPass = $adminPass;

        $this->login();
    }

    private function request($request, $attributes = array(), $parameters = array(), $delegate = false)
    {
        //header
        if ($delegate) {
            $header = array (
                'context' => array(
                    '@attributes' => array(
                        'xmlns' => 'urn:zimbra'
                    ),
                    'authToken' => array(
                        '@value' => $this->delegatedAuthToken
                    ),
                    'account' => array(
                        '@attributes' => array(
                            'by' => 'name'
                        ),
                        '@value' => $this->delegatedAuthAccount
                    )
                )
            );
        } elseif ($this->authToken) {
            $header = array (
                'context' => array(
                    '@attributes' => array(
                        'xmlns' => 'urn:zimbra'
                    ),
                    'authToken' => array(
                        '@value' => $this->authToken
                    )
                )
            );
        } else {
            $header = array (
                'context' => array(
                    '@attributes' => array(
                        'xmlns' => 'urn:zimbra'
                    )
                )
            );
        }

        //body
        if ($delegate) {
            $attributes['xmlns'] = 'urn:zimbraMail';
        } else {
            $attributes['xmlns'] = 'urn:zimbraAdmin';
        }
        $body[$request . 'Request'] = array_merge(array('@attributes' => $attributes), $parameters);

        $message = array(
            '@attributes' => array(
                'xmlns:soap' => 'http://www.w3.org/2003/05/soap-envelope'
            ),
            'soap:Header' => $header,
            'soap:Body' => $body
        );
        $xml = Array2Xml::createXML('soap:Envelope', $message)->saveXML();

        $response = $this->httpClient->post($this->server, $xml);
        $responseContent = $response->getBody();

        $responseArray = Xml2Array::createArray($responseContent);

        if (array_key_exists('soap:Fault', $responseArray['soap:Envelope']['soap:Body'])) {

            throw new SoapFaultException('Zimbra Soap Fault: ' . $responseArray['soap:Envelope']['soap:Body']['soap:Fault']['soap:Reason']['soap:Text']);
        }

        return $responseArray['soap:Envelope']['soap:Body'][$request . 'Response'];
    }

    public function login()
    {
        $response = $this->request('Auth', array(), array('name' => $this->adminUser, 'password' => $this->adminPass));
        $this->authToken = $response['authToken'];
    }

    public function countAccount($domain, $by = 'name')
    {
        $response = $this->request('CountAccount', array(), array(
            'domain' => array(
                '@attributes' => array(
                    'by' => $by
                ),
                '@value' => $domain
            )
        ));

        $coses = array();
        if (is_array($response)) {
            if (array_key_exists('@attributes', $response['cos'])) {
                $coses[$response['cos']['@attributes']['name']] = array('count' => $response['cos']['@value'], 'id' => $response['cos']['@attributes']['id']);
            } else {
                foreach ($response['cos'] as $cos) {
                    $coses[$cos['@attributes']['name']] = array('count' => $cos['@value'], 'id' => $cos['@attributes']['id']);
                }
            }
        }

        return $coses;
    }

    public function createDomain($domain, $attributes, $vhosts = array())
    {
        $a = $this->getAArray($attributes);
        foreach ($vhosts as $vhost) {
            $a[] = array('@attributes' => array('n' => 'zimbraVirtualHostname'), '@value' => $vhost);
        }
        $response = $this->request('CreateDomain', array('name' => $domain), array('a' => $a));

        return $response['domain']['@attributes']['id'];
    }

    private function getAArray($attributes)
    {
        $a = array();
        foreach ($attributes as $key => $value) {
            $a[] = array(
                '@attributes' => array(
                    'n' => $key
                ),
                '@value' => $value
            );
        }

        return $a;
    }

    public function deleteDomain($id)
    {
        $this->request('DeleteDomain', array('id' => $id));
    }

    public function getDomainId($name)
    {
        $response = $this->request('GetDomain', array(), array(
            'domain' => array(
                '@attributes' => array(
                    'by' => 'name'
                ),
                '@value' => $name
            )
        ));

        return $response['domain']['@attributes']['id'];
    }

    public function modifyDomain($id, $attributes)
    {
        $a =  $this->getAArray($attributes);
        $this->request('ModifyDomain', array(), array(
            'id' => $id,
            'a' => $a
        ));
    }

    public function createAccount($name, $password, $attributes)
    {
        $a = $this->getAArray($attributes);
        $response = $this->request('CreateAccount', array(), array(
            'name' => $name,
            'password' => $password,
            'a' => $a
        ));

        return $response['account']['@attributes']['id'];
    }

    public function getAccountCosId($name)
    {
        $response = $this->request('GetAccountInfo', array(), array(
            'account' => array(
                '@attributes' => array(
                    'by' => 'name'
                ),
                '@value' => $name
            )
        ));

        return $response['cos']['@attributes']['id'];
    }

    public function getAccount($name)
    {
        $response = $this->request('GetAccount', array(), array(
            'account' => array(
                '@attributes' => array(
                    'by' => 'name'
                ),
                '@value' => $name
            )
        ));

        $account = array();
        $account['id'] = $response['account']['@attributes']['id'];
        foreach ($response['account']['a'] as $a) {
            $account[$a['@attributes']['n']] = $a['@value'];
        }

        return $account;
    }

    public function deleteAccount($id)
    {
        $this->request('DeleteAccount', array('id' => $id));
    }

    public function modifyAccount($id, $attributes)
    {
        $a = $this->getAArray($attributes);
        $response = $this->request('ModifyAccount', array(), array(
            'id' => $id,
            'a' => $a
        ));

        return $response;
    }

    public function getAccountId($name)
    {
        $response = $this->request('GetAccount', array(), array(
            'account' => array(
                '@attributes' => array(
                    'by' => 'name'
                ),
                '@value' => $name
            )
        ));

        return $response['account']['@attributes']['id'];
    }

    public function addAccountAlias($id, $alias)
    {
        $this->request('AddAccountAlias', array(), array(
            'id' => $id,
            'alias' => $alias
        ));
    }

    public function removeAccountAlias($id, $alias)
    {
        $this->request('RemoveAccountAlias', array(), array(
            'id' => $id,
            'alias' => $alias
        ));
    }

    public function getDlId($name)
    {
        $response = $this->request('GetDistributionList', array(), array(
            'dl' => array(
                '@attributes' => array(
                    'by' => 'name'
                ),
                '@value' => $name
            )
        ));

        return $response['dl']['@attributes']['id'];
    }

    public function addDlMember($id, $member)
    {
        $this->request('AddDistributionListMember', array(), array(
            'id' => $id,
            'dlm' => $member
        ));
    }

    public function removeDlMember($id, $member)
    {
        $this->request('RemoveDistributionListMember', array(), array(
            'id' => $id,
            'member' => $member
        ));
    }

    public function createDl($name, $attributes, $views)
    {
        $a = $this->getAArray($attributes);
        foreach ($views as $view) {
            $a[] = array(
                '@attributes' => array(
                    'n' => 'zimbraAdminConsoleUIComponents'
                ),
                '@value' => $view
            );
        }
        $response = $this->request('CreateDistributionList', array(), array(
            'name' => array(
                '@value' => $name
            ),
            'a' => $a
        ));


        return $response['dl']['@attributes']['id'];
    }

    public function deleteDl($id)
    {
        $this->request('DeleteDistributionList', array(), array(
            'id' => $id
        ));
    }

    public function getCosId($name)
    {
        $response = $this->request('GetCos', array(), array(
            'cos' => array(
                '@attributes' => array(
                    'by' => 'name'
                ),
                '@value' => $name
            )
        ));

        return $response['cos']['@attributes']['id'];
    }

    public function getAllCoses()
    {
        $response = $this->request('GetAllCos', array(), array());

        $coses = array();
        foreach ($response['cos'] as $cos) {
            $coses[] = array('id' => $cos['@attributes']['id'], 'name' => $cos['@attributes']['name']);
        }

        return $coses;
    }

    public function grantRight($target, $targetType, $grantee, $granteeType, $right, $deny = 0)
    {
        $response = $this->request('GrantRight', array(), array(
            'target' => array(
                '@attributes' => array(
                    'by' => 'name',
                    'type' => $targetType
                ),
                '@value' => $target
            ),
            'grantee' => array(
                '@attributes' => array(
                    'by' => 'name',
                    'type' => $granteeType
                ),
                '@value' => $grantee
            ),
            'right' => array(
                '@attributes' => array(
                    'deny' => $deny
                ),
                '@value' => $right
            )
        ));

        return $response;
    }

    public function revokeRight($target, $targetType, $grantee, $granteeType, $right, $deny = 0)
    {
        $response = $this->request('RevokeRight', array(), array(
            'target' => array(
                '@attributes' => array(
                    'by' => 'name',
                    'type' => $targetType
                ),
                '@value' => $target
            ),
            'grantee' => array(
                '@attributes' => array(
                    'by' => 'name',
                    'type' => $granteeType
                ),
                '@value' => $grantee
            ),
            'right' => array(
                '@attributes' => array(
                    'deny' => $deny
                ),
                '@value' => $right
            )
        ));

        return $response;
    }

    public function enableArchive($account, $archiveAccount, $cos, $archive = true)
    {
        $response = $this->request('EnableArchive', array(), array(
            'account' => array(
                '@attributes' => array(
                    'by' => 'name',
                ),
                '@value' => $account
            ),
            'archive' => array(
                '@attributes' => array(
                    'create' => $archive ? '1' : '0',
                ),
                'name' => array(
                    '@value' => $archiveAccount
                ),
                'cos' => array(
                    '@attributes' => array(
                        'by' => 'name',
                    ),
                    '@value' => $cos
                )
            )
        ));

        return $response;
    }

    public function delegateAuth($account)
    {
        if ($this->delegatedAuthAccount != $account) {
            $response = $this->request('DelegateAuth', array(), array(
                'account' => array(
                    '@attributes' => array(
                        'by' => 'name'
                    ),
                    '@value' => $account
                )
            ));

            $this->delegatedAuthToken = $response['authToken'];
            $this->delegatedAuthAccount = $account;

            return $response;
        }
    }

    public function addArchiveReadFilterRule($account)
    {
        $this->delegateAuth($account);

        $response = $this->request('ModifyFilterRules', array(), array(
            'filterRules' => array(
                'filterRule' => array(
                    '@attributes' => array(
                        'name' => 'Archive_Read',
                        'active' => '1'
                    ),
                    'filterTests' => array(
                        '@attributes' => array(
                            'condition' => 'anyof'
                        ),
                        'headerTest' => array(
                            '@attributes' => array(
                                'index' => '0',
                                'caseSensitive' => '0',
                                'value' => '*',
                                'negative' => '0',
                                'stringComparison' => 'matches',
                                'header' => 'from'
                            )
                        )
                    ),
                    'filterActions' => array(
                        'actionFlag' => array(
                            '@attributes' => array(
                                'index' => 0,
                                'flagName' => 'read'
                            )
                        )
                    )
                )
            )
        ), true);

        return $response;
    }

    public function folderGrantAction($account, $folderId, $grantee, $permission)
    {
        $this->delegateAuth($account);

        $response = $this->request('FolderAction', array(), array(
            'action' => array(
                '@attributes' => array(
                    'id' => $folderId,
                    'op' => 'grant'
                ),
                'grant' => array(
                    '@attributes' => array(
                        'd' => $grantee,
                        'gt' => 'usr',
                        'perm' => $permission
                    )
                )
            )
        ), true);

        return $response;
    }

    public function getFolder($account, $folderId)
    {
        $this->delegateAuth($account);

        $response = $this->request('GetFolder', array(), array(
            'folder' => array(
                '@attributes' => array(
                    'l' => $folderId
                )
            )
        ), true);

        return $response;
    }

    public function createMountPoint($account, $reminder, $name, $path, $owner, $view)
    {
        $this->delegateAuth($account);

        $response = $this->request('CreateMountpoint', array(), array(
            'link' => array(
                '@attributes' => array(
                    'reminder' => $reminder,
                    'name' => $name,
                    'path' => $path,
                    'owner' => $owner,
                    'view' => $view,
                    'l' => 1
                )
            )
        ), true);

        return $response;
    }

    public function disableArchive($account)
    {
        $response = $this->request('DisableArchive', array(), array(
            'account' => array(
                '@attributes' => array(
                    'by' => 'name'
                ),
                '@value' => $account
            )
        ));

        return $response;
    }

    public function createGalSyncAccount($account, $domain)
    {
        $response = $this->request('CreateGalSyncAccount', array(
            'name' => 'InternalGAL',
            'domain' => $domain,
            'type' => 'zimbra',
            'folder' => '_InternalGal'
        ), array(
            'account' => array(
                '@attributes' => array(
                    'by' => 'name'
                ),
                '@value' => $account
            )
        ));

        return $response;
    }

    public function createAliasDomain($alias, $domainId)
    {
        $a = $this->getAArray(array('zimbraDomainType' => 'alias', 'zimbraDomainAliasTargetId' => $domainId));
        $response = $this->request('CreateDomain', array(),
            array(
                'domain' => array(
                    '@attributes' => array(
                        'name' => $alias
                    ),
                    'a' => $a
                )
            )
        );

        return $response['domain']['@attributes']['id'];
    }
}