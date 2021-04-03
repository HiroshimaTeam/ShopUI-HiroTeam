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

namespace HiroTeam\RomainSav\ui;

use HiroTeam\RomainSav\forms\CustomForm;
use HiroTeam\RomainSav\forms\SimpleForm;
use HiroTeam\RomainSav\ShopUI;
use pocketmine\item\Item;
use pocketmine\Player;

class UiManager
{
    /** @var ShopUI */
    private ShopUI $main;

    /**
     * UiManager constructor.
     * @param ShopUI $main
     */
    public function __construct(ShopUI $main)
    {
        $this->main = $main;
    }

    /**
     * @param Player $player
     */
    public function mainPage(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            $target = $data;
            if (is_null($target)) return;
            $this->categoryItems($player, $target);
        });

        $form->setTitle($this->main->getConfig()->get('main-title'));
        foreach ($this->main->getConfig()->get('shop') as $category => $name) {
            if (isset($name['image'])) {
                if (filter_var($name['image'], FILTER_VALIDATE_URL)) {
                    $form->addButton($name['category_name'], SimpleForm::IMAGE_TYPE_URL, $name['image'], $category);
                } else {
                    $form->addButton($name['category_name'], SimpleForm::IMAGE_TYPE_PATH, $name['image'], $category);
                }
            } else {
                $form->addButton($name['category_name'], -1, "", $category);
            }
        }
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param string $category
     */
    private function categoryItems(Player $player, string $category)
    {
        $form = new SimpleForm(function (Player $player, $data) use ($category) {
            $target = $data;
            if (is_null($target)) return;

            $itemConfig = $this->main->getConfig()->get('shop')[$category]['items'][$target];

            if (isset($itemConfig['sell']) && !isset($itemConfig['buy'])) {

                $this->sell($player, $category, $target);
            } elseif (!isset($itemConfig['sell']) && isset($itemConfig['buy'])) {

                $this->buy($player, $category, $target);
            } else {

                $this->buyAndSell($player, $category, $target);
            }
        });

        $form->setTitle($this->main->getConfig()->get('shop')[$category]['category_name']);
        foreach ($this->main->getConfig()->get('shop')[$category]['items'] as $index => $item) {
            if (isset($item['image'])) {
                if (filter_var($item['image'], FILTER_VALIDATE_URL)) {
                    $form->addButton($item['name'], SimpleForm::IMAGE_TYPE_URL, $item['image'], $index);
                } else {
                    $form->addButton($item['name'], SimpleForm::IMAGE_TYPE_PATH, $item['image'], $index);
                }
            } else {
                $form->addButton($item['name'], -1, "", $index);
            }
        }
        $form->sendToPlayer($player);
    }

    //////////////////////////////////////////// SHARED /////////////////////////////////////////////////////////

    /**
     * @param Player $player
     * @param string $category
     * @param $index
     */
    private function buy(Player $player, string $category, $index)
    {
        $form = new CustomForm(function (Player $player, $data) use ($category, $index) {
            $target = $data;
            if (is_null($target)) {
                $this->categoryItems($player, $category);
                return;
            }

            $itemConfig = $this->main->getConfig()->get('shop')[$category]['items'][$index];

            $money = $this->main->getEconomyAPI()->myMoney($player);

            if ($money < $itemConfig['buy'] * $target[2]) {
                $player->sendMessage($this->main->getConfig()->get('not-enought-money'));
                return;
            }

            $itemIdMeta = explode(":", $itemConfig['idMeta']);
            $item = Item::get($itemIdMeta[0], $itemIdMeta[1], $target[2]);
            if (!$player->getInventory()->canAddItem($item)) {
                $player->sendMessage($this->main->getConfig()->get('no-place-in-inventory'));
                return;
            }

            $player->getInventory()->addItem($item);
            $player->sendMessage($this->replace($this->main->getConfig()->get('buyMessage'), [
                'item' => $itemConfig['name'],
                'price' => $target[2] * $itemConfig['buy']
            ]));
            $this->main->getEconomyAPI()->reduceMoney($player, $target[2] * $itemConfig['buy']);
        });

        $itemConfig = $this->main->getConfig()->get('shop')[$category]['items'][$index];
        $money = $this->main->getEconomyAPI()->myMoney($player);

        $form->setTitle($itemConfig['name']);
        $form->addLabel("§aAcheter : " . $itemConfig['buy'] . "\$");
        $form->addDropdown("Items", [$itemConfig['name']]);
        $form->addSlider("Quantité", 0, (floor($money / $itemConfig['buy'])), 1, 1);
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param string $category
     * @param $index
     */
    private function sell(Player $player, string $category, $index)
    {
        $form = new CustomForm(function (Player $player, $data) use ($category, $index) {
            $target = $data;
            if (is_null($target)) {
                $this->categoryItems($player, $category);
                return;
            }

            $itemConfig = $this->main->getConfig()->get('shop')[$category]['items'][$index];
            $itemIdMeta = explode(":", $itemConfig['idMeta']);
            $item = Item::get($itemIdMeta[0], $itemIdMeta[1], $target[2]);

            if (!$player->getInventory()->contains($item)) {
                $player->sendMessage($this->main->getConfig()->get('not-enought-items'));
                return;
            }

            $this->main->getEconomyAPI()->addMoney($player, $target[2] * $itemConfig['sell']);
            $player->getInventory()->removeItem($item);
            $player->sendMessage($this->replace($this->main->getConfig()->get('sellMessage'), [
                'item' => $itemConfig['name'],
                'price' => $target[2] * $itemConfig['sell']
            ]));
        });

        $itemConfig = $this->main->getConfig()->get('shop')[$category]['items'][$index];
        $itemIdMeta = explode(":", $itemConfig['idMeta']);
        $item = Item::get($itemIdMeta[0], $itemIdMeta[1]);

        $form->setTitle($itemConfig['name']);
        $form->addLabel("§cVendre : " . $itemConfig['sell'] . "\$");
        $form->addDropdown("Items", [$itemConfig['name']]);
        $form->addSlider("Quantité", 0, $this->getItemInInventory($player, $item), 1, 1);
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param string $category
     * @param $index
     */
    private function buyAndSell(Player $player, string $category, $index)
    {
        $form = new CustomForm(function (Player $player, $data) use ($category, $index) {
            $target = $data;
            if (is_null($target)) {
                $this->categoryItems($player, $category);
                return;
            }

            $itemConfig = $this->main->getConfig()->get('shop')[$category]['items'][$index];
            $itemIdMeta = explode(":", $itemConfig['idMeta']);
            $item = Item::get($itemIdMeta[0], $itemIdMeta[1], $target[3]);

            if (!$target[2]) {

                $money = $this->main->getEconomyAPI()->myMoney($player);

                if ($money < $itemConfig['buy'] * $target[3]) {
                    $player->sendMessage($this->main->getConfig()->get('not-enought-money'));
                    return;
                }
                if (!$player->getInventory()->canAddItem($item)) {
                    $player->sendMessage($this->main->getConfig()->get('no-place-in-inventory'));
                    return;
                }
                $player->getInventory()->addItem($item);
                $player->sendMessage($this->replace($this->main->getConfig()->get('buyMessage'), [
                    'item' => $itemConfig['name'],
                    'price' => $target[3] * $itemConfig['buy']
                ]));
                $this->main->getEconomyAPI()->reduceMoney($player, $target[3] * $itemConfig['buy']);

            } else {

                if (!$player->getInventory()->contains($item)) {
                    $player->sendMessage($this->main->getConfig()->get('not-enought-items'));
                    return;
                }

                $this->main->getEconomyAPI()->addMoney($player, $target[3] * $itemConfig['sell']);
                $player->getInventory()->removeItem($item);
                $player->sendMessage($this->replace($this->main->getConfig()->get('sellMessage'), [
                    'item' => $itemConfig['name'],
                    'price' => $target[3] * $itemConfig['sell']
                ]));

            }
        });
        $itemConfig = $this->main->getConfig()->get('shop')[$category]['items'][$index];

        $form->setTitle($itemConfig['name']);
        $form->addLabel("§aAcheter : " . $itemConfig['buy'] . "\$\n§cVendre : " . $itemConfig['sell'] . "\$");
        $form->addDropdown("Items", [$itemConfig['name']]);
        $form->addToggle("Acheter/Vendre", false);
        $form->addSlider("Quantité", 0, 64, 1, 1);
        $form->sendToPlayer($player);
    }


    private function replace(string $str, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $str = str_replace("{" . $key . "}", $value, $str);
        }
        return $str;
    }

    /**
     * @param Player $player
     * @param Item $item
     * @return int
     */
    private function getItemInInventory(Player $player, Item $item): int
    {
        $result = array_map(function (Item $invItem) use ($item) {
            if ($invItem->getId() === $item->getId() && $invItem->getDamage() === $item->getDamage()) {
                return $invItem->getCount();
            }
            return 0;
        }, $player->getInventory()->getContents());

        return array_sum($result);
    }
}
