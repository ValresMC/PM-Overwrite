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
    protected array $creative;

    protected array $deserializers;
    protected array $itemSerializers;

    protected array $typeIndex;

    public static function overwriteItem(string $typeName, Item $item): void {
        $creativeInventory = CreativeInventory::getInstance();
        $creativeIndex = $creativeInventory->getItemIndex($item);

        $creativeInventory->remove($item);
        StringToItemParser::getInstance()->override($typeName, fn() => $item);

        (function(string $id, Closure $deserializer): void {
            $this->deserializers[$id] = $deserializer;
        })->call(GlobalItemDataHandlers::getDeserializer(), $typeName, fn() => clone $item);

        (function(Item $item, Closure $serializer): void {
            $this->itemSerializers[$item->getTypeId()] = $serializer;
        })->call(GlobalItemDataHandlers::getSerializer(), $item, fn() => new SavedItemData($typeName));

        (function(Item $item) use ($creativeIndex): void {
            array_splice($this->creative, $creativeIndex, 0, [$item]);
            $this->creative = array_values($this->creative);
        })->call($creativeInventory, $item);
    }


    /** @throws ReflectionException */
    public static function overwriteBlock(Block $block): void {
        $blockRegistry = RuntimeBlockStateRegistry::getInstance();
        $creativeInv = CreativeInventory::getInstance();

        $creativeInv->remove($block->asItem());

        try {
            $blockRegistry->register($block);
        } catch (InvalidArgumentException) {
            $property = (new ReflectionProperty($blockRegistry, "typeIndex"));
            $typeIndex = $property->getValue($blockRegistry);
            $typeIndex[$block->getTypeId()] = $block;
            $property->setValue($blockRegistry, $typeIndex);
        }

        $creativeInv->add($block->asItem());
    }
}
