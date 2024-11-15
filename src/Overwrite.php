<?php

declare(strict_types=1);

use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class Overwrite
{
    protected array $deserializers;
    protected array $itemSerializers;

    protected array $typeIndex;

    public static function overwriteItem(string $typeName, Item $item): void {
        CreativeInventory::getInstance()->remove($item);
        StringToItemParser::getInstance()->override($typeName, fn() => $item);

        (function(string $id, Closure $deserializer): void {
            $this->deserializers[$id] = $deserializer;
        })->call(GlobalItemDataHandlers::getDeserializer(), $typeName, fn() => clone $item);

        (function(Item $item, Closure $serializer): void {
            $this->itemSerializers[$item->getTypeId()] = $serializer;
        })->call(GlobalItemDataHandlers::getSerializer(), $item, fn() => new SavedItemData($typeName));

        CreativeInventory::getInstance()->add($item);
    }

    public static function overwriteBlock(int $blockTypeId, Block $block): void {
        $instance = RuntimeBlockStateRegistry::getInstance();

        (function(int $blockTypeId, Block $block) use ($instance): void {
            unset($this->typeIndex[$blockTypeId]);
            $instance->register($block);
        })->call($instance, $blockTypeId, $block);
    }
}