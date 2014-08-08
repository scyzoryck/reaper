<?php
namespace Scyzoryck\Reaper\Module\Chat;

use Scyzoryck\Reaper\Client\Selector\Filter;
use Erpk\Harvester\Module\Module;
use Erpk\Harvester\Exception\InvalidArgumentException;
use Guzzle\Plugin\Cookie\Cookie;
use Erpk\Harvester\Client\Client; 

class ChatModule extends Module 
{
    protected $response;
    
    protected $erpkChat = ''; 
    
    protected $currentRoomId = '';
    
    protected $colorCookie;
    
    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->setColor();
    }
    
    public function setCurrentRoomId($id = '')
    {
        $this->currentRoomId = $id;
        return $this;    
    }
    
    public function getCurrentRoomId()
    {
        return empty($this->currentRoomId) ? $this->erpkChat['roomId'] : $this->currentRoomId;
    }
    
    public function setColor($color = '2F2F2F')
    {
        if(!preg_match("/[0-9a-f]{6}/i", $color))
            throw new InvalidArgumentException('Invalid color ' . $color . '.');
        $color = strtoupper($color);
        if(isset($this->colorCookie))
        {
            $this->colorCookie->setValue($color);
            return $this;
        }
        $colorCookie = new Cookie( array( 'name' => 'l_chatroomcolor', 'value' => $color, 'domain' => '.erepublik.com' ) );
        $this->getSession()->getCookieJar()->add($colorCookie);
        return $this;
    }

    public function getColor()
    {
        return $this->colorCookie->getValue();
    }
     
    public function sendMUMessage($message) 
    {
        $this->setCurrentRoomID();
        $this->sendMessage($message);
    }
    
    public function sendMessage($message) 
    {
        if(empty($message))
            throw new InvalidArgumentException('Empty message');
        $this->getClient()->checkLogin();
        if(empty($this->erpkChat))
            $this->setErpkChatOptions();
        $token = $this->getSession()->getToken();
        $roomId = $this->getCurrentRoomId();
        $request = $this->getClient()->post('main/cometchat-room-send');
        $request->addPostFields(
            array( 
                '_token'      => $token,
                'currentroom' => $roomId,
                'message'     => $message
            )
        );
        $request->setHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setHeader('Referer', $this->getClient()->getBaseUrl() . 'main/cometchat-chatrooms?roomid=' . $this->getCurrentRoomId() . '&inviteid=' . $this->erpkChat['inviteId'] . '&roomname=' . $this->erpkChat['roomName'] . '&basedata=null');
        $response = $request->send();
    }
    
    protected function setErpkChatOptions() {
        $request = $this->getClient()->get('');
        $response = $request->send();
        $this->erpkChat = Filter::extractJSObiect('erpkChat',$response);
    }

}
