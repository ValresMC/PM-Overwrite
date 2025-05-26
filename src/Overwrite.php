<?php

declare(strict_types=1);

use Closure;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionException;
use ReflectionProperty;
use Throwable;

final class Overwrite
{
    /**
     * Overwrite an item.
     *
     * @param  string $typeName
     * @param  Item   $item
     * @return void
     */
    public static function overwriteItem(string $typeName, Item $item): void {
        try {
            $creativeInventory = CreativeInventory::getInstance();

            $creativeInventory->remove($item);
            StringToItemParser::getInstance()->override($typeName, fn() => clone $item);

            $deserializer = GlobalItemDataHandlers::getDeserializer();
            (function(string $id, Closure $deserializer): void {
                $this->deserializers[$id] = $deserializer;
            })->call($deserializer, $typeName, fn() => clone $item);

            $serializer = GlobalItemDataHandlers::getSerializer();
            (function(Item $item, Closure $serializer): void {
                $this->itemSerializers[$item->getTypeId()] = $serializer;
            })->call($serializer, $item, fn() => new SavedItemData($typeName));

            $creativeInventory->add($item);
        } catch (Throwable) {}
    }

    /**
     * Overwrite a block.
     *
     * @param  Closure $blockClosure
     * @return void
     * @throws ReflectionException
     */
    public static function overwriteBlock(Closure $blockClosure): void {
        $block = $blockClosure();
        $runtimeBlockStateRegistry = RuntimeBlockStateRegistry::getInstance();

        $typeIndexProperty = new ReflectionProperty($runtimeBlockStateRegistry, "typeIndex");
        $value = $typeIndexProperty->getValue($runtimeBlockStateRegistry);
        $value[$block->getTypeId()] = $block;
        $typeIndexProperty->setValue($runtimeBlockStateRegistry, $value);
    }
}
