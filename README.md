Reaper
======

**Reaper** is an extension for [erpk/harvester](https://github.com/erpk/harvester/). It add some new features for **Harvester**.


Installation
------------

The best way to install Reaper is through [Composer](http://getcomposer.org/).

If you have installed Harvester, you have to run following command in your project directory:

``` php composer.phar require scyzoryck/reaper master-dev ```

Else create file `composer.json` file in your project directory:

```json
{
    "minimum-stability": "dev",
    "require": {
      "scyzoryck/reaper": "master-dev"
    }
}
```
And run command

``` php composer.phar install ```

Modules
-------

###Chat
Allows to send messages via erepublik chat. 
```php
use Scyzoryck\Reaper\Module\Chat\ChatModule;

$module = new ChatModule;
//set message color to blue (#45d7d7)
$module->setColor('45d7d7');
//send message "Black is white" to military unit chat
$module->sendMessage('Black is white');
//get last used color. 
echo $module->getColor();
```
Default colors using by erepublik chat are: *2F2F2F*, *45D7D7*, *4545D7*, *8ED745*, *D745D*, *8E8E8E*, *407D40*, *45D745*, *7D4040*, *40407D*, *7D7D40*, *D7A045*, *7D407D*, *D74545*, *BDBDBD*, *407D7D*, *DC93DC* and *D7D745*, but it's working with all hex color codes. 

If you want to send message to other room you can use:
```php
//set other room id than MU room id
$module->setCurrentRoomId($roomId);
$module->sendMessage('blablabla');
//get current room id
echo $module->getCurrentRoomId(); // if you didn't set any roomId it will display MU room id
```
###Military 
This module extends MilitaryModule from [erpk/harvester](https://github.com/erpk/harvester/). New features is setting MUDO. 
```php
use Scyzoryck\Reaper\Module\Military\MilitaryModule;

$module = new MilitaryModule;
//firstly set your military unit ID
$module->setMilitaryUnitId(12345);
//next set battle with id 654321 as MUDO
$module->setMUDO(654321);
```
If MUDO can't be change a `CannotChangeMUDOExpection` is thrown. 

