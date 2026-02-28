# Game

`Cmuset\ChessTools\Model\Game`

Represents a complete PGN game: header tags, initial position, main line move tree, and result.

## Static Factories

```php
Game::fromPGN(string $pgn): Game
// Parses one or more games from a PGN string.
// Returns a single Game (if PGN contains multiple games, first is returned)
```

## Export

```php
$game->getPGN(): string
// Full PGN output: tags, move text with comments, variations, and result.

$game->getLitePGN(): string
// Moves only — strips tags and comments. Useful for comparison.

$game->getVerbosePgn(): string
// PGN with computed source squares, capture flags, and check/checkmate markers
// on every move (runs GameResolver before exporting).
```

## Tags (PGN headers)

Tags are stored as `array<string, string>` key-value pairs.

```php
$game->getTags(): array // ['Event' => '...', 'White' => '...', ...]
$game->setTags(array $tags): void
$game->setTag(string $key, string $value): void
$game->getTag(string $key): ?string // null when tag is absent
$game->removeTag(string $key): void
```

Standard Seven-Tag Roster keys: `Event`, `Site`, `Date`, `Round`, `White`, `Black`, `Result`. Any additional tags are preserved.

## Initial position

```php
$game->getInitialPosition(): Position
// Returns the starting position for this game.
// Default: standard starting position (PGNParser::INITIAL_FEN).

$game->setInitialPosition(string|Position $pos): void
// Accepts either a Position object or a FEN string (parsed automatically).
```

## Main line

```php
$game->getMainLine(): Variation
$game->setMainLine(Variation $mainLine): void

// Access individual nodes by PGN key ("1.", "1...", "2.", …):
$game->getNode(string $key): ?MoveNode
$game->getMove(string $key): ?Move
$game->getLastNode(): ?MoveNode

// Append moves:
$game->addMoveNode(string|MoveNode $moveNode): void
// Accepts a SAN string (e.g. 'e4') or a MoveNode object.

$game->addMoveNodes(string|MoveNode ...$moveNodes): void
```

## Result

```php
$game->getResult(): ?ResultEnum
$game->setResult(?ResultEnum $result): void
```

## Variation utilities

```php
$game->split(): Variation[]
// Splits the main line into individual flat variations (no nested sub-variations).
// The first element is always the main line itself.
// See docs/tools/splitter.md.

$game->merge(Variation ...$variations): void
// Merges external variations back into the main line.
// See docs/tools/merger.md.

$game->clearAllComments(): void
// Removes all before-move and after-move comments from every node in the main line,
// including nested variation lines.
```

## Cloning

`Game` implements `__clone()` with deep copying of the main line and initial position. Use `clone $game` when you need an independent copy.

## Example

```php
use Cmuset\ChessTools\Model\Game;
use Cmuset\ChessTools\Enum\ResultEnum;

$pgn = <<<PGN
[Event "World Championship"]
[White "Kasparov"]
[Black "Karpov"]
[Result "1-0"]

1. e4 e5 2. Nf3 Nc6 3. Bb5 a6 {Ruy Lopez} 4. Ba4 Nf6 1-0
PGN;

$game = Game::fromPGN($pgn);

echo $game->getTag('White');      // 'Kasparov'
echo $game->getResult()->value;   // '1-0'

$node = $game->getNode('3...');   // MoveNode for 3... a6
echo $node->getAfterMoveComment(); // 'Ruy Lopez'

$game->addMoveNode('O-O');
echo $game->getPGN();
// [Event "World Championship"]
// [White "Kasparov"]
// [Black "Karpov"]
// [Result "1-0"]
//
// 1. e4 e5 2. Nf3 Nc6 3. Bb5 a6 {Ruy Lopez} 4. Ba4 Nf6 5. O-O 1-0
```
