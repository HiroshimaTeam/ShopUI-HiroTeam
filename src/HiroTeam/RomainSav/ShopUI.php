<?php

#ShopUI-HiroTeam plugin by RomainSav | Plugin de ShopUI-HiroTeam par RomainSav
#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝
#description:
#FRA: Ce plugin vous permet d'ajouter une boutique personnalisable sur votre serveur !
#ENG: This plugin allows you to add a customizable store on your server !

namespace HiroTeam\RomainSav;

use HiroTeam\RomainSav\commands\ShopCommand;
use HiroTeam\RomainSav\ui\UiManager;
use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;

class ShopUI extends PluginBase
{
    /** @var UiManager */
    private UiManager $uiManager;

    /** @var EconomyAPI  */
    private EconomyAPI $economyAPI;

    public function onLoad()
    {
        $this->uiManager = new UiManager($this);
    }

    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register('shop', new ShopCommand($this));
        $this->economyAPI = EconomyAPI::getInstance();
    }

    /**
     * @return UiManager
     */
    public function getUiManager(): UiManager
    {
        return $this->uiManager;
    }

    /**
     * @return EconomyAPI
     */
    public function getEconomyAPI(): EconomyAPI
    {
        return $this->economyAPI;
    }
}
