# Validators

The library provides two validators under `src/Tool/Validator/`.

---

## PositionValidator

`Cmuset\ChessTools\Tool\Validator\PositionValidator`

Checks a `Position` object for illegal board states and returns a list of violations.

### Interface

```php
interface PositionValidatorInterface {
    /** @return PositionViolationEnum[] */
    public function validate(Position $position): array;
}
```

### Usage

```php
use Cmuset\ChessTools\Tool\Validator\PositionValidator;

$violations = (new PositionValidator())->validate($position);

if (empty($violations)) {
    echo 'Position is legal.';
} else {
    foreach ($violations as $v) {
        echo $v->value . "\n"; // e.g. 'King in check'
    }
}
```

### Checks performed

Violations are accumulated and all applicable ones are returned. King-presence checks run first; if any king is missing or duplicated, the remaining checks are skipped.

| Violation | Condition |
|---|---|
| `NO_WHITE_KING` | No `WHITE_KING` piece on the board |
| `NO_BLACK_KING` | No `BLACK_KING` piece on the board |
| `MULTIPLE_WHITE_KINGS` | More than one `WHITE_KING` |
| `MULTIPLE_BLACK_KINGS` | More than one `BLACK_KING` |
| `KING_IN_CHECK` | The **opponent's** king (the side that just moved) is under attack by the side to move |
| `PAWN_ON_INVALID_RANK` | Any pawn found on rank 1 or rank 8 |
| `TOO_MANY_PAWNS` | More than 8 pawns of the same color |
| `EN_PASSANT_SQUARE_INVALID` | En passant target is not on the expected rank, or the square is occupied |

### PositionViolationEnum

`Cmuset\ChessTools\Tool\Validator\Enum\PositionViolationEnum`

| Case | Value |
|---|---|
| `KING_IN_CHECK` | `'King in check'` |
| `NO_WHITE_KING` | `'No white king present'` |
| `NO_BLACK_KING` | `'No black king present'` |
| `MULTIPLE_WHITE_KINGS` | `'Multiple white kings present'` |
| `MULTIPLE_BLACK_KINGS` | `'Multiple black kings present'` |
| `PAWN_ON_INVALID_RANK` | `'Pawn on invalid rank'` |
| `TOO_MANY_PAWNS` | `'Too many pawns for one color'` |
| `EN_PASSANT_SQUARE_INVALID` | `'En passant square is invalid'` |

`PositionValidator` is called automatically by `MoveApplier` after every move. Any violations throw a `MoveApplyingException` with `NEXT_POSITION_INVALID`.

---

## GameValidator

`Cmuset\ChessTools\Tool\Validator\GameValidator`

Validates an entire `Game` — including all nested variations — by replaying every move from the initial position and catching the first illegal one.

### Interface

```php
interface GameValidatorInterface {
    public function validate(Game $game): ?GameViolation;
}
```

Returns `null` when the game is fully legal, or a `GameViolation` describing the first illegal move found.

### Usage

```php
use Cmuset\ChessTools\Tool\Validator\GameValidator;

$violation = (new GameValidator())->validate($game);

if (null === $violation) {
    echo 'Game is valid.';
} else {
    echo 'Illegal move in: ' . $violation->getPath() . "\n";
    echo 'Reason: ' . $violation->getMoveViolation()->value . "\n";

    foreach ($violation->getPositionViolations() as $pv) {
        echo '  Position violation: ' . $pv->value . "\n";
    }
}
```

### GameViolation

`Cmuset\ChessTools\Tool\Validator\Model\GameViolation`

Carries context about the first illegal move found:

```php
$violation->getPath(): string
// PGN of the move sequence up to and including the offending move.

$violation->getMoveViolation(): MoveViolationEnum
// Why the move was rejected.

$violation->getPositionViolations(): PositionViolationEnum[]
// Additional position violations (non-empty only when MoveViolationEnum::NEXT_POSITION_INVALID).
```

### Traversal order

The validator replays variations **depth-first**: for each node, all sub-variations are validated before the main-line move is applied. This means a nested variation error is reported before any subsequent main-line error.

### MoveViolationEnum

`Cmuset\ChessTools\Tool\Validator\Enum\MoveViolationEnum`

| Case | Value |
|---|---|
| `PIECE_NOT_FOUND` | `'No piece found for the move'` |
| `MULTIPLE_PIECES_MATCH` | `'Multiple pieces match the move piece'` |
| `NO_PIECE_TO_CAPTURE` | `'No piece to capture on the target square'` |
| `CASTLING_IS_NOT_ALLOWED` | `'Castling is not allowed in the current position'` |
| `WRONG_COLOR_TO_MOVE` | `'It is not the correct color to move'` |
| `NEXT_POSITION_INVALID` | `'The resulting position after the move is invalid'` |
| `MOVE_NOT_CHECKMATE` | `'The move does not result in checkmate when expected'` |
| `MOVE_NOT_CHECK` | `'The move does not result in check when expected'` |
| `SQUARE_OCCUPIED_BY_OWN_PIECE` | `'The target square is occupied by own piece'` |
