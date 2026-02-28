# MoveNode

`Cmuset\ChessTools\Model\MoveNode`

A node in the move tree. Wraps a `Move` together with its move number, before/after-move comments, NAGs (Numeric Annotation Glyphs), and any alternative variation lines branching from this point.

## Constructor

```php
new MoveNode(string|Move|null $move = null, ?int $moveNumber = null)
```

The `$move` parameter accepts a SAN string (parsed automatically via `Move::fromSAN()`), a `Move` object, or `null`. `$moveNumber` defaults to `null` and is computed automatically by `Variation::addNode()`.

```php
use Cmuset\ChessTools\Model\MoveNode;

$node = new MoveNode('Nf3');            // SAN string
$node = new MoveNode($move, 3);         // Move object + explicit move number
$node = new MoveNode();                 // empty node
```

## Move and move number

```php
$node->getMove(): ?Move
$node->setMove(?Move $move): void

$node->getMoveNumber(): ?int
$node->setMoveNumber(?int $number): void

$node->getColor(): ?ColorEnum
// Derived from the move's piece color: ColorEnum::WHITE or ColorEnum::BLACK.
// Returns null when no move is set.
```

## Key

```php
$node->getKey(): string
// Returns the PGN-style addressing key used as the array key in a Variation.
// Format: "{moveNumber}." for white, "{moveNumber}..." for black.
// Examples: "1." "1..." "12." "12..."
// Returns '' when moveNumber is null.
```

## Comments

Each node carries two optional comment strings: one printed **before** the move and one **after** the move in PGN output.

```php
// Unified accessor — defaults to after-move (POST):
$node->getComment(CommentAnchorEnum $anchor = CommentAnchorEnum::POST): ?string
$node->setComment(?string $comment, CommentAnchorEnum $anchor = CommentAnchorEnum::POST): void

// Explicit accessors:
$node->getBeforeMoveComment(): ?string
$node->setBeforeMoveComment(?string $comment): void

$node->getAfterMoveComment(): ?string
$node->setAfterMoveComment(?string $comment): void
```

```php
use Cmuset\ChessTools\Enum\CommentAnchorEnum;

$node->setComment('Great move!', CommentAnchorEnum::POST);   // after move
$node->setComment('Forced.', CommentAnchorEnum::PRE);        // before move

$node->getComment();                   // 'Great move!' (defaults to POST)
$node->getComment(CommentAnchorEnum::PRE); // 'Forced.'
```

## NAGs

Numeric Annotation Glyphs encode move quality and position evaluations. The most common ones:

| NAG | Symbol | Meaning |
|---|---|---|
| `$1` | `!` | Good move |
| `$2` | `?` | Mistake |
| `$3` | `!!` | Brilliant move |
| `$4` | `??` | Blunder |
| `$5` | `!?` | Interesting move |
| `$6` | `?!` | Dubious move |

```php
$node->getNags(): int[]          // e.g. [1, 18]
$node->setNags(array $nags): void
$node->addNag(int $nag): void    // No-op if the NAG already exists
```

## Variations

Alternative lines branching from this node's position (before the move is played). Each variation is a `Variation` object and may itself contain nested variations.

```php
$node->getVariations(): Variation[]
$node->addVariation(Variation $variation): void
```

## Cleanup

```php
$node->clearComments(): void
// Removes before and after-move comments on this node only.

$node->clearVariations(): void
// Removes all variation lines attached to this node.

$node->clearVariationComments(): void
// Recursively clears comments in all nested variation lines.

$node->clearAllComments(): void
// Clears comments on this node and recursively in all nested variations.

$node->clearAll(): void
// Clears both comments and all variation lines.
```

## Cloning

`MoveNode` implements `__clone()` with deep copying of all attached variation lines. The `move` object itself is not cloned (it is shared by reference).

## Example

```php
use Cmuset\ChessTools\Model\MoveNode;
use Cmuset\ChessTools\Enum\CommentAnchorEnum;

$node = new MoveNode('Bxf7+');
$node->setMoveNumber(12);
$node->setComment('A sacrifice!', CommentAnchorEnum::PRE);
$node->setComment('Black is lost.', CommentAnchorEnum::POST);
$node->addNag(3); // !!

echo $node->getKey();        // '12.'
echo $node->getMove()->getSAN(); // 'Bxf7+'
echo $node->getNags();       // [3]
```
