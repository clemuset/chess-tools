# Resolvers

The resolver layer (`src/Tool/Resolver/`) derives information that is absent in SAN notation: source squares, capture flags, check/checkmate markers, and the game result. Resolvers **mutate** `Move` objects in place without modifying the `Position`.

Use resolvers when you need fully annotated moves (verbose PGN) or when building a `Game` programmatically from moves that lack source squares.

---

## MoveResolver

`Cmuset\ChessTools\Tool\Resolver\MoveResolver`

Resolves a single `Move` against a `Position`, computing:

- The full source square (`squareFrom`).
- The capture flag (`isCapture`).
- The check and checkmate flags (`isCheck`, `isCheckmate`).

### Interface

```php
interface MoveResolverInterface {
    public function resolve(Position $position, Move $move): void;
}
```

### Instantiation

```php
$resolver = MoveResolver::create();
// or: new MoveResolver()
```

### What it does

1. For non-castling moves: calls `PieceMoveApplier::findWherePieceIs()` to locate the piece and sets `$move->setSquareFrom()`.
2. Determines `isCapture`: true when the destination square is occupied, or (for pawns) when the destination equals the en passant target.
3. Clones the position, applies the move on the clone, and checks the resulting position:
   - Sets `isCheckmate` if the resulting position is checkmate.
   - Sets `isCheck` (only when not checkmate) if the resulting position is check.

### Usage

```php
use Cmuset\ChessTools\Tool\Resolver\MoveResolver;

$resolver = MoveResolver::create();
$resolver->resolve($position, $move);

// After resolution:
$move->getSquareFrom(); // CoordinatesEnum
$move->isCapture();     // bool
$move->isCheck();       // bool
$move->isCheckmate();   // bool
```

---

## VariationResolver

`Cmuset\ChessTools\Tool\Resolver\VariationResolver`

Resolves all moves in a `Variation` sequentially, advancing a position copy after each move.

### Interface

```php
interface VariationResolverInterface {
    public function resolve(Position $position, Variation $variation): void;
}
```

### Instantiation

```php
$resolver = VariationResolver::create();
// Internally creates a MoveResolver.
```

### What it does

Iterates the variation's nodes in order. For each node:
1. Calls `MoveResolver::resolve()` with the current position and the node's move.
2. Clones the position and applies the move to produce the next position.

Sub-variations attached to nodes are **not** resolved; use `GameResolver` for full recursive resolution.

### Usage

```php
use Cmuset\ChessTools\Tool\Resolver\VariationResolver;

$resolver = VariationResolver::create();
$resolver->resolve($game->getInitialPosition(), $game->getMainLine());
```

---

## GameResolver

`Cmuset\ChessTools\Tool\Resolver\GameResolver`

Resolves the entire game: all moves in the main line receive full source squares, capture flags, and check/checkmate markers. Also detects the game result (checkmate or stalemate) at the end of the main line.

### Interface

```php
interface GameResolverInterface {
    public function resolve(Game $game): void;
}
```

### Instantiation

```php
$resolver = GameResolver::create();
// Internally creates a VariationResolver and MoveResolver.
```

### What it does

1. Calls `VariationResolver::resolve()` on the main line from the initial position.
2. Replays the full main line on a clone of the initial position.
3. If the final position is checkmate, sets the result to `ResultEnum::fromColor(side_that_just_moved)`.
4. If the final position is stalemate, sets the result to `ResultEnum::DRAW`.

### Usage

```php
use Cmuset\ChessTools\Tool\Resolver\GameResolver;

$resolver = GameResolver::create();
$resolver->resolve($game);

// All main-line moves now have squareFrom, isCapture, isCheck, isCheckmate populated.
// The result is set if checkmate/stalemate was detected.
echo $game->getVerbosePgn(); // internally calls GameResolver::resolve()
```

### Shortcut via `Game::getVerbosePgn()`

`Game::getVerbosePgn()` clones the game, runs `GameResolver::resolve()`, and then exports the resolved clone. The original game is not mutated.

---

## When to use resolvers

| Scenario | Tool |
|---|---|
| Need verbose PGN with source squares | `$game->getVerbosePgn()` |
| Resolve a single move before applying it | `MoveResolver::resolve()` |
| Resolve moves in a standalone variation | `VariationResolver::resolve()` |
| Resolve the full game and detect result | `GameResolver::resolve()` |
| Validate move legality only | `MoveApplier::apply()` or `GameValidator::validate()` |
