# PM-Overwrite :
A PocketMine-MP virion for easier overwrite items and blocks.

---

### Usage :
Just intergrate the virion itself into your plugin.

---

### Exemples :
You must put it in the onLoad function in your Main class.
```php
//For items :
Overwrite::overwriteItem(ItemTypeNames::FISHING_ROD, new FishingRod(new ItemIdentifier(ItemTypeIds::FISHING_ROD), "Fishing Rod"));

//For blocks :
Overwrite::overwriteBlock(static fn() => new Anvil(new BlockIdentifier(BlockTypeIds::ANVIL), "Anvil", new BlockTypeInfo(VanillaBlocks::ANVIL()->getBreakInfo())));
```

---

**This framework is a trial. For any feedback or improvement, please create an issue.**
