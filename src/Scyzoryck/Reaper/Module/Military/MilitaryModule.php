<?php
namespace Scyzoryck\Reaper\Module\Military;

use Erpk\Harvester\Module\Module;
use Erpk\Harvester\Module\Military\MilitaryModule as ErpkMilitaryModule;
use Guzzle\Http;
use Scyzoryck\Reaper\Module\Military\Exception\NoMUIdException;
use Scyzoryck\Reaper\Module\Military\Exception\CannotChangeMUDOException;

class MilitaryModule extends ErpkMilitaryModule
{
    protected $militaryUnitId;
    
    public function setMUDO($battleId)
    {
        $this->filter($battleId, 'id');
        $this->getClient()->checkLogin();
        $militaryUnitId = $this->getMilitaryUnitId();
        if (!isset($militaryUnitId))
        {
            throw new NoMUIdException;
        }
        $token = $this->getSession()->getToken();    
        $request = $this->getClient()->post('military/group-missions');
        $request->addPostFields(
            array(
                '_token'      => $token,
                'groupId'     => $this->getMilitaryUnitId(),
                'action'      => 'set',
                'typeId'      => 1,
                'referenceId' => $battleId,
            )
        );
        $request->setHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setHeader('Referer', $this->getClient()->getBaseUrl() . 'main/group-show/' . $this->militaryUnitId . '?page=1');
        $response = $request->send();
        $response = json_decode( $response->getBody() );
        if ($response->error)
        {
            throw new CannotChangeMUDOException($response->msg);
        }
    }
    
    public function getMilitaryUnitId() 
    {
        return $this->militaryUnitId;
    }
    
    public function setMilitaryUnitId($id)
    {
            $this->filter($id, 'id');
            $this->militaryUnitId = $id;
            return $this;
    }
    

}
