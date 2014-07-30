<?php
namespace Scyzoryck\Reaper\Module\Chat;

use Scyzoryck\Reaper\Client\Selector\Filter;
use Erpk\Harvester\Module\Module;
use Erpk\Harvester\Exception\InvalidArgumentException;

class ChatModule extends Module 
{
    protected $response;
    
    protected $erpkChat; 
    
    public function sendMUMessage($message) 
    {
        if(empty($message))
            throw new InvalidArgumentException('Empty message');
        $this->getClient()->checkLogin();
        $this->setErpkChatOptions();
        $request = $this->getClient()->post('main/cometchat-room-send');
        $request->addPostFields(
            array( 
                '_token'      => $this->getSession()->getToken(),
                'currentroom' => $this->erpkChat['roomId'],
                'message'     => $message
            )
        );
        $request->setHeader('X-Requested-With', 'XMLHttpRequest');
        $response = $request->send();
    }
    
    public function setResponse(\Guzzle\Http\Message\Response $response) 
    {
        $this->response = $response ;
    }
    
    public function getResponse()
    {
        if(!isset($this->response))
            $this->makeRequest();
        return $this->response;
    }
    
    protected function makeRequest()
    {
        $request = $this->getClient()->get('');
        $response = $request->send();
        $this->setResponse($response);
    }

    protected function setErpkChatOptions() {
        $this->erpkChat = Filter::extractJSObiect('erpkChat',$this->getResponse());
        $this->erpkChat['roomName'] = str_replace('=', '', $this->erpkChat['roomName']);
        $this->erpkChat['inviteId'] = md5(''); //fuck this shit. 
    }

}
