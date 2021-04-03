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

namespace HiroTeam\RomainSav\commands;

use HiroTeam\RomainSav\ShopUI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class ShopCommand extends Command
{
    /** @var ShopUI  */
    private ShopUI $main;

    /**
     * ShopCommand constructor.
     */
    public function __construct(ShopUI $main)
    {
        parent::__construct('shop', 'Ouvrir l\'interface de la boutique', '/shop');
        $this->main = $main;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage('Please execute this command in game');
            return;
        }

        $this->main->getUiManager()->mainPage($sender);
    }
}
