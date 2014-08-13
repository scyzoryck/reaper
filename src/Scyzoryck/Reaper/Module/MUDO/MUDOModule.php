<?php
namespace Scyzoryck\Reaper\Module\MUDO;

use Erpk\Harvester\Module\Module;
use Erpk\Harvester\Client\Selector;
use Erpk\Common\Entity\Campaign;
use Erpk\Harvester\Exception\ScrapeException;
use Scyzoryck\Reaper\Module\MUDO\Exception\NoMUIdException;
use Scyzoryck\Reaper\Module\MUDO\Exception\CannotChangeMUDOException;
use Scyzoryck\Reaper\Client\Selector\Filter;


class MUDOModule extends Module
{
    protected $militaryUnitId;
    
    public function setDayliOrder($battleId)
    {
        $this->filter($battleId, 'id');
        $this->getClient()->checkLogin();
        $militaryUnitId = $this->getMilitaryUnitId();
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
    
    public function getAvailableCampaigns()
    {
        $this->getClient()->checkLogin();
        $request = $this->getClient()->get('main/group-show/' . $this->getMilitaryUnitId() . '?page=1');
        $response = $request->send();
        $hxs = Selector\XPath::loadHTML($response->getBody(true));
        
        //get MU location
        $militaryUnitLocation = $hxs->select('//span[@id="error_message"]/../a')->extract();
        $militaryUnitLocation = trim($militaryUnitLocation);
        
        //get info about all battles
        $allBattlesSelect = $hxs->select('//div[@id="group_orders"]/../script');
        if (!$allBattlesSelect->hasResults())
        {
            throw new ScrapeException;
        }
        $allBattlesJS = $allBattlesSelect->extract();
        $allBattlesJS = explode( 'var tempBattle = new Object();', $allBattlesJS);
        array_shift ($allBattlesJS);
        $allBattles = array();
        foreach ($allBattlesJS as $battleJS)
        {
            preg_match('/allBattles\[\"(\d+)\"\]/', $battleJS, $matches);
            $allBattles[$matches[1]] = Filter::extractJSObiect('tempBattle', $battleJS);
        }
        
        $regions = $this->getEntityManager()->getRepository('Erpk\Common\Entity\Region');
        $countries = $this->getEntityManager()->getRepository('Erpk\Common\Entity\Country');
        $xpath = '//div[@class="mission pusher"]/div[@class="sublist"]/a';
        $list = $hxs->select($xpath);
        if (!$list->hasResults())
        {
            return array();
        }

        $result = array();
        foreach ($list as $battle)
        {   
            $id = (int)$battle->select('@reference_id')->extract();
            
            preg_match_all( '/[\w\-]+/', $battle, $matches);
            array_shift($matches[0]);
            $regionName = join(' ', $matches[0]);
            
            $isResistance = false;
            $fightForName = $militaryUnitLocation;
            
            foreach ($battle->select('//a[@reference_id=' . $id . ']/span/img') as $image)
            {
                $url = $image->select('@src')->extract();
                if (preg_match('/\/small_mpp\.png/', $url))
                {
                    $fightForName = $image->select('@title')->extract();
                }
                else if (preg_match('/\/small_resistance\.png/', $url))
                {
                    $isResistanceWar = true;
                }
            }
            $campaign = new Campaign;
            $campaign->setId($id);
            $attackerId = $allBattles[$id]['invader_country_id'];
            $campaign->setAttacker($countries->find((int)$attackerId));
            $defenderId =  $allBattles[$id]['defender_country_id'];
            $campaign->setDefender($countries->find((int)$defenderId));
            $campaign->setRegion($regions->findOneByName($regionName));
            $campaign->setResistance($isResistance);
            $fightFor = $countries->findOneByName($fightForName);
            $result[] = array( 
                                'campaign' => $campaign,
                                'figthFor' => $fightFor,
                             );
        }
        
        return $result;
    }
    
    public function getMilitaryUnitId() 
    {
        if (!isset($this->militaryUnitId))
        {
            throw new NoMUIdException;
        }
        return $this->militaryUnitId;
    }
    
    public function setMilitaryUnitId($id)
    {
            $this->filter($id, 'id');
            $this->militaryUnitId = $id;
            return $this;
    }
}
