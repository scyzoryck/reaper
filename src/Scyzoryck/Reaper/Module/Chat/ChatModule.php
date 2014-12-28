<?php
namespace Scyzoryck\Reaper\Module\Chat;

use Scyzoryck\Reaper\Client\Selector\Filter;
use Erpk\Harvester\Module\Module;
use Erpk\Harvester\Exception\InvalidArgumentException;
use GuzzleHttp\Cookie\SetCookie;
use Erpk\Harvester\Client\Client;

class ChatModule extends Module 
{
    /**
     * @var array
     */
    protected $erpkChat = '';

    /**
     * @var string
     */
    protected $currentRoomId = '';

    /**
     * @var SetCookie
     */
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
        if (!preg_match("/[0-9a-f]{6}/i", $color))
            throw new InvalidArgumentException('Invalid color ' . $color . '.');
        $color = strtoupper($color);
        if (isset($this->colorCookie)) {
            $this->colorCookie->setValue($color);
            return $this;
        }
        $colorCookie = new SetCookie();
        $colorCookie->setName('l_chatroomcolor');
        $colorCookie->setValue($color);
        $colorCookie->setDomain('.erepublik.com');
        $this->getSession()->getCookieJar()->setCookie($colorCookie);
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
        if (empty($message))
            throw new InvalidArgumentException('Empty message');
        $this->getClient()->checkLogin();
        if (empty($this->erpkChat))
            $this->setErpkChatOptions();

        $token = $this->getSession()->getToken();
        $roomId = $this->getCurrentRoomId();
        $request = $this->getClient()->post('main/cometchat-room-send');
        $request->addPostFields([
            '_token'      => $token,
            'currentroom' => $roomId,
            'message'     => $message
        ]);
        $request->markXHR();
        $request->setRelativeReferer(
            'main/cometchat-chatrooms?roomid=' . $this->getCurrentRoomId().
            '&inviteid=' . $this->erpkChat['inviteId'] .
            '&roomname=' . $this->erpkChat['roomName'] .
            '&basedata=null'
        );
        $request->send();
    }
    
    protected function setErpkChatOptions()
    {
        $request = $this->getClient()->get('');
        $response = $request->send();
        $this->erpkChat = Filter::extractJSObject('erpkChat', $response->getBody(true));
    }
}
