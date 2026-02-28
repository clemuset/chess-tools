# VariationSplitter

`Cmuset\ChessTools\Tool\Splitter\VariationSplitter`

Extracts all nested variations from a `Game` or `Variation` and returns them as a flat list of independent `Variation` objects, each starting from the same position as the original divergence point.

## Interface

```php
interface VariationSplitterInterface {
    /** @return Variation[] */
    public function split(Game|Variation $variation): array;
}
```

## Instantiation

```php
$splitter = VariationSplitter::create();
// or: new VariationSplitter()
```

Shortcuts are available directly on `Game` and `Variation`:

```php
$game->split(): Variation[]
$variation->split(): Variation[]
```

## What `split()` returns

- The **first element** is always the main line itself (or the variation itself), cloned and stripped of sub-variations.
- Subsequent elements are the extracted sub-variations, each prefixed with the moves that preceded the divergence point so they can be replayed from the initial position.
- All nested variation lines are themselves extracted recursively.
- Duplicate variations (same lite PGN) are deduplicated.

## Example

Given a game with this structure:

```
1. e4 e5 2. Nf3 (2. Nc3 Nf6) 2... Nc6 3. Bb5 (3. Bc4 Bc5)
```

`$game->split()` returns three variations:

1. `1. e4 e5 2. Nf3 Nc6 3. Bb5` (main line, no sub-variations)
2. `1. e4 e5 2. Nc3 Nf6` (prefixed with the preceding `1. e4 e5`)
3. `1. e4 e5 2. Nf3 Nc6 3. Bc4 Bc5` (prefixed with moves up to the divergence)

## Usage

```php
use Cmuset\ChessTools\Model\Game;

$game = Game::fromPGN($pgn);

$variations = $game->split();

foreach ($variations as $i => $variation) {
    echo "Variation $i: " . $variation->getLitePGN() . "\n";
}

// Apply each variation independently:
foreach ($variations as $variation) {
    $pos = clone $game->getInitialPosition();
    $pos->applyVariation($variation);
    echo $pos->getFEN() . "\n";
}
```

## Relationship with VariationMerger

`split()` and `merge()` are inverse operations. You can split a game into flat variations, process each one independently, and then merge them back:

```php
$variations = $game->split();

// … process variations …

$game->merge(...$variations);
```

See [merger.md](merger.md) for details on `VariationMerger`.
