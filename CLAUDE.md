# CLAUDE.md

## Commands

```bash
composer install                           # Install dependencies
composer dump-autoload                     # Regenerate autoloader

vendor/bin/phpunit                         # Run all tests
vendor/bin/phpunit tests/path/to/Test.php  # Run a single test file
vendor/bin/phpunit --filter methodName     # Run a single test method

vendor/bin/phpstan analyse                 # Static analysis (level 7)
vendor/bin/php-cs-fixer fix                # Fix code style
vendor/bin/php-cs-fixer fix --dry-run      # Check code style without changes

make check                                 # fix + analyse + test (Docker)
make test / make analyse / make fix        # Individual Docker targets
```

## Conventions

- **Namespace root:** `Cmuset\ChessTools`
- **PHP:** 8.4 — use enums, readonly, strict types, no nullable shorthand on unions
- **Code style:** PSR-12 + Symfony rules, single quotes, imports ordered alphabetically (php-cs-fixer enforces)
- **PHPStan:** level 7 — all types must be exact, no `mixed`, no missing return types
- **Tests:** mirror `src/` under `tests/`; test data (PGN files) in `tests/resources/`
- Each tool class exposes a `static create(): self` factory for zero-config instantiation

## Architecture

Three layers: **Model** → **Enum** → **Tool**. Tools operate on models; models never depend on tools except through static factory shortcuts (e.g. `Game::fromPGN` delegates to `PGNParser`).

### Model — `src/Model/`

> Full API reference: `docs/models/`

| Class | File | Role |
|---|---|---|
| `Game` | [`docs/models/game.md`](docs/models/game.md) | Full PGN game: tags, initial `Position`, main line `Variation`, `ResultEnum` |
| `Position` | [`docs/models/position.md`](docs/models/position.md) | Board state (FEN-compatible): 64 `Square`s, side to move, castling rights, en passant, counters |
| `Variation` | [`docs/models/variation.md`](docs/models/variation.md) | `IteratorAggregate` of `MoveNode`, keyed `"1."` / `"1..."`. Implements `ArrayAccess`, `Countable` |
| `MoveNode` | [`docs/models/move-node.md`](docs/models/move-node.md) | Move + move number + PRE/POST comments + NAGs + nested `Variation[]` |
| `Move` | [`docs/models/move.md`](docs/models/move.md) | Parsed SAN: piece, destination, source disambiguation, capture/check/castling/promotion flags |
| `Square` | [`docs/models/square.md`](docs/models/square.md) | `CoordinatesEnum` + nullable `PieceEnum` |

### Enums — `src/Enum/`

> Full reference: `docs/enums/`

| Enum | Docs | Key notes |
|---|---|---|
| `ColorEnum` | [`docs/enums/color.md`](docs/enums/color.md) | `WHITE='w'`, `BLACK='b'`; `->opposite()` |
| `PieceEnum` | [`docs/enums/piece.md`](docs/enums/piece.md) | 12 cases; factories `::king($color)` … `::pawn($color)`; `->color()`, `->opposite()`, `->isPawn()` |
| `CoordinatesEnum` | [`docs/enums/coordinates.md`](docs/enums/coordinates.md) | 64 cases `A1`–`H8`; `->file()`, `->rank()`, `->up/down/left/right()`, `->isPromotionSquare()` |
| `CastlingEnum` | [`docs/enums/castling.md`](docs/enums/castling.md) | 4 cases; `::kingside/queenside($color)`; `->color()` |
| `ResultEnum` | [`docs/enums/result.md`](docs/enums/result.md) | `WHITE_WINS='1-0'`, `BLACK_WINS='0-1'`, `DRAW='1/2-1/2'`, `ONGOING='*'`; `::fromColor($color)` |
| `CommentAnchorEnum` | — | `PRE` / `POST` — position of a comment relative to its move |
| `MoveViolationEnum` | [`docs/tools/validator.md`](docs/tools/validator.md) | Why a move was rejected by `MoveApplier` |
| `PositionViolationEnum` | [`docs/tools/validator.md`](docs/tools/validator.md) | Why a position is illegal (missing king, pawn on rank 1/8, …) |

### Tools — `src/Tool/`

> Full reference: `docs/tools/`

| Subdirectory | Classes | Docs |
|---|---|---|
| `Parser/` | `PGNParser`, `SANParser`, `FENParser` | [`docs/tools/parser.md`](docs/tools/parser.md) |
| `Exporter/` | `GameExporter`, `MoveExporter`, `PositionExporter` | [`docs/tools/exporter.md`](docs/tools/exporter.md) |
| `MoveApplier/` | `MoveApplier`, `MoveHelper`, per-piece `PieceMoveApplier` subclasses | [`docs/tools/move-applier.md`](docs/tools/move-applier.md) |
| `Validator/` | `PositionValidator`, `GameValidator`, `GameViolation` | [`docs/tools/validator.md`](docs/tools/validator.md) |
| `Resolver/` | `GameResolver`, `VariationResolver`, `MoveResolver` | [`docs/tools/resolver.md`](docs/tools/resolver.md) |
| `Splitter/` | `VariationSplitter` | [`docs/tools/splitter.md`](docs/tools/splitter.md) |
| `Merger/` | `VariationMerger` | [`docs/tools/merger.md`](docs/tools/merger.md) |

## Key entry points

```php
Game::fromPGN(string $pgn): Game|Game[]          // parse one or many games
Position::fromFEN(string $fen): Position          // parse a FEN string
Move::fromSAN(string $san, ColorEnum $color): Move // parse a SAN move

$position->applyMove(string|Move $move): void     // apply a move (throws MoveApplyingException)
$position->getLegalMoves(): Move[]                // all legal moves for the side to move
$position->getFEN(): string                       // export to FEN

$game->getPGN(): string                           // export to PGN
$game->getLitePGN(): string                       // export moves only (no tags, no comments)
$game->getVerbosePgn(): string                    // export with resolved source squares

PGNParser::INITIAL_FEN                            // standard starting position constant
```

## Gotchas

- `Variation::addNode()` auto-computes move numbers and corrects color conflicts — do not set move numbers manually unless building a variation from scratch outside a `Game`.
- `MoveApplier` mutates the `Position` in place. Always `clone` before speculative application.
- `MoveResolver` must be run before exporting verbose PGN — it populates `squareFrom`, `isCapture`, `isCheck`, `isCheckmate` on each `Move`.
- `VariationSplitter` and `VariationMerger` are inverse operations: `split()` → flat `Variation[]`, `merge()` → nested structure.
- Parser exceptions extend `Cmuset\ChessTools\Tool\Parser\Exception\ParsingException`. Move errors throw `MoveApplyingException` (not a `ParsingException`).
