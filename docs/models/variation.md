# Variation

`Cmuset\ChessTools\Model\Variation`

An ordered collection of `MoveNode` instances keyed by their PGN-style move notation (`"1."`, `"1..."`, `"2."`, …). Implements `IteratorAggregate`, `ArrayAccess`, and `Countable`.

## Constructor and factories

```php
new Variation(string|MoveNode ...$nodes)
// Accepts SAN strings (wrapped in MoveNode automatically) or MoveNode objects.
// Move numbers and colors are computed automatically when nodes are added.

Variation::fromPGN(string $pgn): Variation
// Parses a PGN string and returns the main line as a Variation.
```

```php
use Cmuset\ChessTools\Model\Variation;

$variation = new Variation('e4', 'e5', 'Nf3', 'Nc6');
$variation = Variation::fromPGN('1. d4 d5 2. c4');
```

## Export

```php
$variation->getPGN(): string
// Full move text including move numbers, comments, NAGs, and nested variations.

$variation->getLitePGN(): string
// Moves only — no comments or nested variations. Used for deduplication.
```

## Node access

Keys follow the format `"{number}."` for white and `"{number}..."` for black.

```php
$variation->getNode(string $key): ?MoveNode   // e.g. '3.', '3...'
$variation->getMove(string $key): ?Move
$variation->getFirstNode(): ?MoveNode
$variation->getLastNode(): ?MoveNode
```

## Adding nodes

When a node is added without a move number, the number is computed automatically from the previous node. When the color would repeat, the piece color is corrected automatically.

```php
$variation->addNode(string|MoveNode $node): void
// Accepts a SAN string or a MoveNode object.

$variation->addNodes(string|MoveNode ...$nodes): void
```

## Removing nodes

```php
$variation->removeNodesFrom(string $key): void
// Removes the node at $key and all subsequent nodes.
// Example: removeNodesFrom('3...') removes from black's 3rd move onward.
```

## Variation utilities

```php
$variation->split(): Variation[]
// Returns the variation itself plus all nested sub-variations as flat,
// independent Variation objects (no nested variations in the results).
// See docs/tools/splitter.md.

$variation->merge(Variation ...$variations): void
// Merges additional lines into this variation.
// See docs/tools/merger.md.
```

## Identifier

```php
$variation->getIdentifier(): string
// Returns the SAN of the first move in this variation.
// Used by VariationMerger to match variation lines.
```

## Partial clone

```php
$variation->cloneFrom(string $key): Variation
// Returns a new Variation containing all nodes from $key onward (inclusive).
```

## Cleanup

```php
$variation->clearAllComments(): void
// Removes all comments from every node, including nested variation nodes.

$variation->clearVariations(?ColorEnum $colorToClear = null): void
// Removes all sub-variation lines from nodes.
// Pass a ColorEnum to restrict to nodes of that color.
```

## Collection interface

```php
// IteratorAggregate — iterate over nodes:
foreach ($variation as $key => $node) {
    // $key: '1.', '1...', '2.', ...
    // $node: MoveNode
}

// ArrayAccess — direct access:
$node = $variation['2.'];    // ?MoveNode
isset($variation['1...']);   // bool

// Countable:
count($variation);           // number of MoveNode instances

// Emptiness check:
$variation->isEmpty(): bool
```

## Internal ordering

Nodes are stored in a `string => MoveNode` array keyed by their move notation and kept sorted using natural order (`ksort` with `SORT_NATURAL`). This ensures `"1."`, `"1..."`, `"2."`, `"2..."`, … always stay in the correct sequence regardless of insertion order.

## Cloning

`Variation` implements `__clone()` with deep copying of all nodes and their nested variations. Use `clone $variation` when you need an independent copy.

## Example

```php
use Cmuset\ChessTools\Model\Variation;

$variation = new Variation('e4', 'c5', 'Nf3');

echo count($variation);              // 3
echo $variation->getIdentifier();    // 'e4'

$variation->getNode('1.');           // MoveNode for 1. e4
$variation->getLastNode();           // MoveNode for 2. Nf3
$variation->getMove('1...');         // Move for 1... c5

foreach ($variation as $key => $node) {
    echo $key . ' ' . $node->getMove()->getSAN() . "\n";
    // 1. e4
    // 1... c5
    // 2. Nf3
}
```
