# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer install                          # Install dependencies

vendor/bin/phpunit                        # Run all tests
vendor/bin/phpunit tests/path/to/Test.php # Run a single test file
vendor/bin/phpunit --filter methodName    # Run a single test method

vendor/bin/phpstan analyse                # Static analysis (level 7)
vendor/bin/php-cs-fixer fix               # Fix code style
vendor/bin/php-cs-fixer fix --dry-run    # Check code style without changes

make check                                # Run fix + analyse + test sequentially (Docker)
make test / make analyse / make fix       # Individual Docker targets
```

## Architecture

This is a PHP library for parsing and exporting chess notations (PGN, SAN, FEN). The codebase has three layers:

### Model (`src/Model/`)
Data structures representing chess concepts:
- `Game` — full PGN game (tags, initial position, main line as `Variation`, result)
- `Position` — board state (piece placement, side to move, castling rights, en passant, halfmove/fullmove counters); exported as FEN via `getFEN()` or `Position::fromFEN()`
- `Move` — parsed SAN move with piece, destination, capture, promotion, check/checkmate flags; shorthand via `Move::fromSAN()` or `Game::fromPGN()`
- `MoveNode` — node in the move tree (move + before/after comments + NAGs + variations)
- `Variation` — iterable container of `MoveNode` instances, keyed by `"1."` / `"1..."` notation
- `Square` — a board square (coordinate enum + piece)

### Enums (`src/Enum/`)
All board concepts are type-safe PHP enums: `ColorEnum`, `PieceEnum` (color-prefixed, e.g. `WHITE_PAWN`), `CoordinatesEnum` (all 64 squares `a1`–`h8`), `CastlingEnum`, `ResultEnum`, `CommentAnchorEnum`.

Violation enums used by validators: `MoveViolationEnum`, `PositionViolationEnum`.

### Tools (`src/Tool/`)
Processing tools organized by function:

| Subdirectory   | Purpose                                                                                                                    |
|----------------|----------------------------------------------------------------------------------------------------------------------------|
| `Parser/`      | `PGNParser`, `SANParser`, `FENParser` — strings → objects                                                                  |
| `Exporter/`    | `GameExporter`, `MoveExporter`, `PositionExporter` — objects → strings                                                     |
| `MoveApplier/` | `MoveApplier` + per-piece appliers — apply SAN to a `Position` with full rules (castling, en passant, promotion, counters) |
| `Validator/`   | `PositionValidator`, `GameValidator` — return violation enums                                                              |
| `Resolver/`    | `GameResolver`, `VariationResolver`, `MoveResolver` — derive positions and move numbers                                    |
| `Merger/`      | `VariationMerger` — merge variations back into move sequences                                                              |
| `Splitter/`    | `VariationSplitter` — split variations from the main line                                                                  |

### Key entry points
- `Game::fromPGN($string)` — parse a full PGN
- `Position::fromFEN($string)` — parse a FEN position
- `Move::fromSAN($san, $color)` — parse a SAN move
- `Position::applyMove($move)` — apply a move, returns new `Position`; throws `MoveApplyingException` on illegal moves
- `Position::getLegalMoves()` — returns `Move[]` for the side to move
- `PGNParser::INITIAL_FEN` — the standard starting position FEN constant

### Conventions
- PHPStan level 7; use strict types throughout
- Code style: PSR-12 + Symfony rules, single quotes, ordered imports (enforced by php-cs-fixer)
- Namespace root: `Cmuset\ChessTools`
- Tests mirror `src/` structure under `tests/`; sample PGN data in `tests/resources/`
