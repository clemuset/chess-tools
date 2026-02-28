# VariationMerger

`Cmuset\ChessTools\Tool\Merger\VariationMerger`

Merges one or more `Variation` objects into a main line, inserting diverging moves as nested sub-variations at the correct branching points.

## Interface

```php
interface VariationMergerInterface {
    public function merge(Variation $mainLine, Variation ...$variations): Variation;
}
```

## Instantiation

```php
$merger = VariationMerger::create();
// or: new VariationMerger()
```

Shortcuts are also available directly on `Game` and `Variation`:

```php
$game->merge(Variation ...$variations): void        // merges into the main line
$variation->merge(Variation ...$variations): void   // merges into the variation
```

## How `merge()` works

1. Every incoming variation is first **split** into its own flat sub-variations (via `VariationSplitter`). This ensures each divergence is handled independently.
2. For each flat variation, `merge()` walks through its nodes one by one, comparing each node's key (`"1."`, `"1..."`, …) against the main line:
   - **Key absent**: the node (and everything after) is appended to the main line.
   - **Key present, SAN matches**: the node is a continuation of the same line; skip (no change needed).
   - **Key present, SAN differs**: the diverging move becomes a new sub-variation on the existing main-line node. If a sub-variation with the same identifier already exists, the merge recurses into it.

## Usage

```php
use Cmuset\ChessTools\Tool\Merger\VariationMerger;
use Cmuset\ChessTools\Model\Variation;
use Cmuset\ChessTools\Model\Game;

// Merge two variations into a game's main line:
$main = Variation::fromPGN('1. e4 e5 2. Nf3 Nc6');
$alt  = Variation::fromPGN('1. e4 e5 2. Nc3 Nf6');

$game = Game::fromPGN('1. e4 e5 2. Nf3 Nc6');
$game->merge($alt);
echo $game->getPGN();
// 1. e4 e5 2. Nf3 (2. Nc3 Nf6) 2... Nc6

// Or directly via the merger:
$merger = VariationMerger::create();
$merger->merge($main, $alt);
echo $main->getPGN();
// 1. e4 e5 2. Nf3 (2. Nc3 Nf6) 2... Nc6
```

## Relationship with VariationSplitter

`merge()` and `split()` are complementary operations:

```php
// Split a game into flat variations, process each, then merge back:
$variations = $game->split();

// Modify individual variations…

$mainLine = array_shift($variations);
$game->setMainLine($mainLine);
$game->merge(...$variations);
```

See [splitter.md](splitter.md) for details on `VariationSplitter`.

## Notes

- The merger does not validate move legality; it only compares SAN strings structurally.
- Comments and NAGs on nodes in the incoming variations are preserved when the node is inserted as a new sub-variation, but are ignored when the move already exists in the main line.
- The returned `Variation` from the interface method is the mutated `$mainLine` (merge is in-place).
