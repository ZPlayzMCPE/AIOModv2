<?php
namespace AIOMod\Tasks;
use pocketmine\{Player, Server};
use pocketmine\utils\Config;
use DateTime;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\scheduler\PluginTask;
use AIOMod\Loader;
class Check extends PluginTask{
	public function __construct($plugin, $oldreports) {
        $this->plugin = $plugin;
        $this->oldreports = $oldreports;
        parent::__construct($plugin);
    }

    public function onRun(int $tick) {
			foreach(Server::getInstance()->getOnlinePlayers() as $p){
				$config = new Config("/AIOMod/Spieler/". $p->getName() . ".yml", Config::YAML);
				if($config->get("Aktiv") != null){
					$cfg = new Config("/AIOMod/Bans/". $config->get("Aktiv") . ".yml", Config::YAML);
						if($cfg->get("Typ") == "Ban"){
							if(new DateTime("now") < new DateTime($cfg->get("Dauer"))){
							$reason = $cfg->get("Grund");
							$id = $config->get("Aktiv");
							$dauer = $cfg->get("Dauer");
								$data = [
									'type' => 'form',
									'title' => "§cDu wurdest gebannt",
									'content' => "   §cDein Account wurde aufgrund von §eFehlverhalten §cvon einem Teammitglied gesperrt \n §eGrund: §4". $cfg->get("Grund"). "§7[§4". $config->get("Aktiv") ."§7] \n §eGebannt bis: §c" . $cfg->get("Dauer") ."(!) \n §eDu kannst einen Entbannungsantrag im §cForum §e stellen: \n §bhttps://revengermc.de/forum/",
									'buttons' => []
								];
								$pk = new ModalFormRequestPacket();
								$pk->formId = 73;
								$pk->formData = json_encode($data);
								$p->dataPacket($pk);
							}else{
								$cfg->set("Status", "Inaktiv");
								$cfg->save();
								$config->set("Aktiv", null);
								$config->save();
							}
						}
				}
			}
        $new  = @mkdir("/AIOMod/Bans/");
        $tobc = array_diff($new, $this->oldreports);
        if ($tobc !== []) {
            foreach ($tobc as $repfile) {
                $rep = yaml_parse_file("/AIOMod/Bans/" . $repfile);
                foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                    if ($p->hasPermission("universe.notify")) {
                        foreach(Server::getInstance()->getOnlinePlayers() as $p){
										if(file_exists("/AIOMod/Notify/".$p->getName().".yml")){
										$tcfg = new Config("/AIOMod/Notify/".$p->getName().".yml", Config::YAML);
										if($tcfg->get("Notify") == true){
											$p->sendMessage("§7[§c§lAIOMod§r§7]§b< BAN §7| §c". $rep["Name"] ." §7| §c".$rep["Grund"] . "§7 | §c".$rep["Dauer"]." §7|§b>§r");
										}
									}
								}
                    }
                }

            }
        }
        $this->oldreports = $new;

    }
  }
