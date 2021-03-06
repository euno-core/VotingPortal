<?php
namespace EunoVoting\Api\Controllers;

use EunoVoting\Common\Libraries\jsonRPCClient;
use EunoVoting\Common\Models\ApiKeys;
use EunoVoting\Common\Models\GovernanceMembers;
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;
use Phalcon\Http\Request;

class GovernanceController extends Controller
{
    public function initialize()
    {
        $this->view->disable();

        $request = new Request();
        $auth_api_key = $request->getHeader('euno-api-key');

        $this->_verify_auth_headers($auth_api_key);
    }

    public function membersAction()
    {
        $core_members = [
            '@RektME_EUNO', '@Drogert', '@ACryptKeeper', '@EunoNutter', '@AGangel', '@EUNO1', '@Eugenisinius',
            '@watoshi', '@supadupadoug', '@PrincessEuna', '@rundoau', '@arrie1992'
        ];
        $members = GovernanceMembers::find([
            'deleted = 0 OR deleted IS NULL'
        ]);

        $connect_string = sprintf('http://%s:%s@%s:%s/', $this->config->eunod->user, $this->config->eunod->pass, $this->config->eunod->host, $this->config->eunod->port);
        $coind = new jsonRPCClient($connect_string);

        $nodes = $coind->masternode('list', 'pubkey');

        if(!is_array($nodes) || !$nodes)
        {
            $response = new Response();
            $response->setContentType('application/json', 'UTF-8');
            $response->setContent(json_encode([
                "status" => false,
                "msg" => "Failed to fetch the running masternodes"
            ]));

            return $response;
        }
        else
        {
            $nodes = array_values($nodes);
        }

        $membersList = [];
        foreach ($members as $member)
        {
            if(!isset($membersList[$member->telegram_username]))
                $membersList[$member->telegram_username] = false;

            if($membersList[$member->telegram_username] === false)
            {
                $membersList[$member->telegram_username] = in_array($member->masternode_address, $nodes);
            }
        }

        foreach ($core_members as $core_member)
        {
            if(!isset($membersList[$core_member]))
                $membersList[$core_member] = true;
        }

        $response = new Response();
        $response->setContentType('application/json', 'UTF-8');
        $response->setContent(json_encode([
            "status" => ($members->count() > 0 ? true : false),
            "members" => $membersList
        ]));

        return $response;
    }

    public function membersExtendedAction()
    {
        $core_members = [
            '@RektME_EUNO', '@Drogert', '@ACryptKeeper', '@EunoNutter', '@AGangel', '@EUNO1', '@Eugenisinius',
            '@watoshi', '@supadupadoug', '@PrincessEuna', '@rundoau', '@arrie1992'
        ];
        $members = GovernanceMembers::find([
            'deleted = 0 OR deleted IS NULL'
        ]);

        $connect_string = sprintf('http://%s:%s@%s:%s/', $this->config->eunod->user, $this->config->eunod->pass, $this->config->eunod->host, $this->config->eunod->port);
        $coind = new jsonRPCClient($connect_string);

        $nodes = $coind->masternode('list', 'pubkey');

        if(!is_array($nodes) || !$nodes)
        {
            $response = new Response();
            $response->setContentType('application/json', 'UTF-8');
            $response->setContent(json_encode([
                "status" => false,
                "msg" => "Failed to fetch the running masternodes"
            ]));

            return $response;
        }
        else
        {
            $nodes = array_values($nodes);
        }

        $membersList = [];
        foreach ($members as $member)
        {
            if(!isset($membersList[$member->telegram_username]))
                $membersList[$member->telegram_username] = [
                    'status' => false,
                    'last_seen' => 'na'
                ];

            if($membersList[$member->telegram_username]['status'] === false)
            {
                $membersList[$member->telegram_username] = [
                    'status' => in_array($member->masternode_address, $nodes),
                    'last_seen' => $member->last_seen
                ];
            }
        }

        foreach ($core_members as $core_member)
        {
            if(!isset($membersList[$core_member]))
            {
                $membersList[$core_member] = [
                    'status' => true,
                    'last_seen' => time()
                ];
            }
        }

        $membersListResponse = [];
        foreach ($membersList as $member => $data)
        {
            $membersListResponse[] = [
                "member" => $member,
                "status" => $data['status'],
                "last_seen_unix" => (int)$data['last_seen'],
                "last_seen_date" => date("Y-m-d h:iA", $data['last_seen'])." Europe/Amsterdam",
                "last_seen_hours" => ($data['last_seen'] ? ( round((time() - $data['last_seen']) / 3600, 2)) : null),
            ];
        }

        $response = new Response();
        $response->setContentType('application/json', 'UTF-8');
        $response->setContent(json_encode([
            "status" => ($members->count() > 0 ? true : false),
            "members" => $membersListResponse
        ]));

        return $response;
    }

    protected function _verify_auth_headers($auth_header_key = '')
    {
        $keys = ApiKeys::find([
            'columns' => 'key'
        ]);

        $found_valid_key = false;

        foreach ($keys as $key)
        {
            if(password_verify($auth_header_key, $key->key))
            {
                $found_valid_key = true;
                break;
            }
        }

        if(!$found_valid_key)
        {
            echo json_encode([
                "status" => false,
                "msg" => "Invalid Auth API Key provided"
            ]);

            exit;
        }
    }
}
