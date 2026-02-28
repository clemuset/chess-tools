# MoveApplier

`Cmuset\ChessTools\Tool\MoveApplier\MoveApplier`

Applies a `Move` to a `Position`, mutating it in place. Enforces all chess rules: piece legality, captures, castling, en passant, promotion, counter updates, and check/checkmate validation after the move.

## Interface

```php
interface MoveApplierInterface {
    public function apply(Position $position, Move $move): void;
}
```

## Instantiation

```php
// Static factory:
$applier = MoveApplier::create();

// Or directly (no dependencies):
$applier = new MoveApplier();
```

The most convenient entry point is `Position::applyMove()`:

```php
$position->applyMove('e4');          // SAN string — parsed with current side to move
$position->applyMove($move);         // Move object
$position->applyVariation($variation); // Apply all moves in a Variation
```

## What `apply()` does

1. **Pre-move validation** (`throwMoveViolationException`):
   - Throws `WRONG_COLOR_TO_MOVE` if the piece color does not match `sideToMove`.
   - Throws `SQUARE_OCCUPIED_BY_OWN_PIECE` if the destination holds a friendly piece.
   - Throws `NO_PIECE_TO_CAPTURE` if the SAN marks a capture but the destination is empty.

2. **Piece-specific move application** — delegates to the matching `PieceMoveApplier` subclass:
   - Locates the source square via `findWherePieceIs`.
   - Clears the source square, places the piece on the destination.
   - Handles special logic (castling rook placement, en passant capture, pawn double-step en-passant target, promotion).

3. **Castling right revocation** — after each move, rights are removed when:
   - The king moves (all rights for that color).
   - A rook moves from or is captured on its starting square (the affected right).

4. **Counter updates**:
   - Halfmove clock is reset to 0 on captures or pawn moves; otherwise incremented.
   - Fullmove number is incremented after black moves.

5. **Side to move toggle** — `toggleSideToMove()` is called.

6. **Post-move validation**:
   - If the `Move` has `isCheck() = true`, verifies the resulting position is actually in check; throws `MOVE_NOT_CHECK` otherwise.
   - If the `Move` has `isCheckmate() = true`, verifies checkmate; throws `MOVE_NOT_CHECKMATE` otherwise.
   - Runs `PositionValidator` on the resulting position; throws `NEXT_POSITION_INVALID` if violations are found.

## Piece move appliers

Each piece type has a dedicated subclass of `PieceMoveApplier` (abstract base):

| Class | Piece types |
|---|---|
| `KingMoveApplier` | `WHITE_KING`, `BLACK_KING` |
| `QueenMoveApplier` | `WHITE_QUEEN`, `BLACK_QUEEN` |
| `RookMoveApplier` | `WHITE_ROOK`, `BLACK_ROOK` |
| `BishopMoveApplier` | `WHITE_BISHOP`, `BLACK_BISHOP` |
| `KnightMoveApplier` | `WHITE_KNIGHT`, `BLACK_KNIGHT` |
| `PawnMoveApplier` | `WHITE_PAWN`, `BLACK_PAWN` |

The correct subclass is selected via `PieceMoveApplier::createFromPiece(PieceEnum $piece)`.

Each subclass implements:

```php
abstract public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool;
// Whether the piece at $from attacks $to (used by Position::findAttackers).

public function canMove(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool;
// For non-pawn pieces this is identical to isAttacking.
// PawnMoveApplier overrides this to distinguish push moves from capture moves.
```

The base class provides:

```php
final public function findWherePieceIs(Position $position, Move $move): CoordinatesEnum;
// Finds the source square by filtering all candidate squares for the piece type
// through canMove(). Throws PIECE_NOT_FOUND or MULTIPLE_PIECES_MATCH accordingly.
```

## MoveHelper

`Cmuset\ChessTools\Tool\MoveApplier\MoveHelper`

Static utility class with geometric predicates used by the piece appliers:

```php
MoveHelper::isPathClear(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
// True when no piece occupies any square between $from and $to (exclusive).
// Works for straight and diagonal directions.

MoveHelper::isSlidingMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
// True when the move is diagonal (|fileDiff| === |rankDiff|).

MoveHelper::isVerticalMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
MoveHelper::isHorizontalMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
MoveHelper::isStraightMove(CoordinatesEnum $from, CoordinatesEnum $to): bool

MoveHelper::isKnightMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
// True for the L-shaped knight pattern (2+1 squares).

MoveHelper::isPawnMove(CoordinatesEnum $from, CoordinatesEnum $to, ColorEnum $color): bool
// True for a forward pawn push (one step, or two from starting rank).

MoveHelper::isPawnCaptureMove(CoordinatesEnum $from, CoordinatesEnum $to, ColorEnum $color): bool
// True for a diagonal pawn capture.

MoveHelper::isKingMove(CoordinatesEnum $from, CoordinatesEnum $to): bool
// True for any one-square king move (non-castling).

MoveHelper::isCastlingPathClear(Position $position, CastlingEnum $castling): bool
// True when all squares between king and rook are empty.

MoveHelper::areCastlingSquaresAttacked(Position $position, CastlingEnum $castling): bool
// True when any of the king's transit squares (including start/end) are attacked.
```

## Exception

```php
use Cmuset\ChessTools\Tool\MoveApplier\Exception\MoveApplyingException;

$e->getMoveViolation(): MoveViolationEnum
$e->getPositionViolations(): PositionViolationEnum[]  // only when NEXT_POSITION_INVALID
```

## Usage

```php
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\Parser\PGNParser;
use Cmuset\ChessTools\Tool\MoveApplier\Exception\MoveApplyingException;

$pos = Position::fromFEN(PGNParser::INITIAL_FEN);

try {
    $pos->applyMove('e4');
    $pos->applyMove('e5');
    $pos->applyMove('Nf3');
} catch (MoveApplyingException $e) {
    echo $e->getMoveViolation()->value; // e.g. 'No piece found for the move'
}

// Speculative application without modifying the original:
$copy = clone $pos;
$copy->applyMove('Nc3');
// $pos is unchanged
```
