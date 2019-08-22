<?php
namespace path;

use path\command\RegistrationCommands;
use path\world\pathfinding\PathNavigateGround;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener{

	public $pos = [];
    public $on = [];
    public $navi = [];

	public static $instance;

	public function onEnable(){
		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->registerCommands();
        $this->getServer()->getLogger()->info(' §a' . $this->getName() . ' is Loaded!');
        $this->getScheduler()->scheduleRepeatingTask(new NaviTask($this), 20);
	}

    public function registerCommands(): void{
        foreach(RegistrationCommands::REGISTRATION_COMMANDS as $command){
            $cmd = 'path\\command\\commands\\' . $command;
            $this->getServer()->getCommandMap()->register('Path', new $cmd($this));
        }
    }

	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->pos[$player->getName()] = $player->getPosition();
		$this->on[$player->getName()] = false;
		$this->navi[$player->getName()] = new PathNavigateGround(new Pos($player), $player->getLevel());
	}

	public function tick(){
	    foreach($this->getServer()->getOnlinePlayers() as $player){
	        if(isset($this->on[$player->getName()]) && $this->on[$player->getName()]){
	            $this->navi[$player->getName()]->tryMoveToXYZ($this->pos[$player->getName()]->x, $this->pos[$player->getName()]->y, $this->pos[$player->getName()]->z);
	            $this->navi[$player->getName()]->onUpdateNavigation();
            }
        }
    }

	public static function getInstance(){
		return self::$instance;
	}
}
class NaviTask extends Task{

    private $main;

    public function __construct(Main $main){
        $this->main = $main;
    }

    public function onRun(int $currentTick){
        $this->main->tick();
    }
}