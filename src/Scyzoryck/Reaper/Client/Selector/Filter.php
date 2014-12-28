<?php
namespace Scyzoryck\Reaper\Client\Selector;

use Erpk\Harvester\Client\Selector\Filter as ErpkFilter;

class Filter extends ErpkFilter
{
    /*  input like:
     *  erpkChat.isMentor = 0;
	 *	erpkChat.chatStatus = 1;
	 *	erpkChat.chatDataFeedStatus = 1;
	 *  output array
    */
    public static function extractJSObject($name, $html)
    {
        preg_match_all( "/$name\.(.+) = (.+);/", $html, $reg);
        $arr = array_combine($reg[1], $reg[2]);
        array_walk($arr, function(&$val, $key) {
            $val = str_replace(array("'", '"'), '', $val);
        });
        return $arr;
    }
}
