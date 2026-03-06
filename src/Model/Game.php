<?php

namespace Cmuset\ChessTools\Model;

use Cmuset\ChessTools\Enum\ResultEnum;
use Cmuset\ChessTools\Tool\Exporter\GameExporter;
use Cmuset\ChessTools\Tool\Merger\VariationMerger;
use Cmuset\ChessTools\Tool\Parser\PGNParser;
use Cmuset\ChessTools\Tool\Resolver\GameResolver;
use Cmuset\ChessTools\Tool\Splitter\VariationSplitter;

class Game
{
    /** @var array<string,string> */
    private array $tags = [];
    private ?Position $initialPosition = null;
    private Variation $mainLine;
    private ?ResultEnum $result = null;

    public function __construct()
    {
        $this->mainLine = new Variation();
    }

    public static function fromPGN(string $pgn): self
    {
        $result = PGNParser::create()->parse($pgn);

        return is_array($result) ? $result[0] : $result;
    }

    public function getPGN(): string
    {
        return GameExporter::create()->export($this);
    }

    public function getLitePGN(): string
    {
        $clonedGame = clone $this;
        $clonedGame->clearAllComments();
        $clonedGame->tags = [];

        return GameExporter::create()->export($clonedGame);
    }

    public function getVerbosePgn(): string
    {
        $clonedGame = clone $this;
        GameResolver::create()->resolve($clonedGame);

        return GameExporter::create()->export($clonedGame);
    }

    /**
     * @return Variation[]
     */
    public function split(): array
    {
        return VariationSplitter::create()->split($this);
    }

    public function merge(Variation ...$variations): void
    {
        VariationMerger::create()->merge($this->mainLine, ...$variations);
    }

    public function getInitialPosition(): Position
    {
        return $this->initialPosition;
    }

    public function setInitialPosition(string|Position $initialPosition): void
    {
        if (is_string($initialPosition)) {
            $initialPosition = Position::fromFEN($initialPosition);
        }

        $this->initialPosition = $initialPosition;
    }

    public function getMainLine(): Variation
    {
        return $this->mainLine;
    }

    public function setMainLine(Variation $mainLine): void
    {
        $this->mainLine = $mainLine;
    }

    public function getNode(string $key): ?MoveNode
    {
        return $this->mainLine[$key] ?? null;
    }

    public function getMove(string $key): ?Move
    {
        return $this->getNode($key)?->getMove();
    }

    public function getLastNode(): ?MoveNode
    {
        return $this->mainLine->getLastNode();
    }

    /**
     * @param string|MoveNode $moveNode can be a SAN string or a MoveNode object
     */
    public function addMoveNode(string|MoveNode $moveNode): void
    {
        $this->mainLine->addNode(is_string($moveNode) ? new MoveNode($moveNode) : $moveNode);
    }

    /**
     * @param string|MoveNode ...$moveNodes can be SAN strings or MoveNode objects
     */
    public function addMoveNodes(string|MoveNode ...$moveNodes): void
    {
        foreach ($moveNodes as $moveNode) {
            $this->addMoveNode($moveNode);
        }
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function setTag(string $key, string $value): void
    {
        $this->tags[$key] = $value;
    }

    public function getTag(string $key): ?string
    {
        return $this->tags[$key] ?? null;
    }

    public function removeTag(string $key): void
    {
        unset($this->tags[$key]);
    }

    public function getResult(): ?ResultEnum
    {
        return $this->result;
    }

    public function setResult(?ResultEnum $result): void
    {
        $this->result = $result;
    }

    public function clearAllComments(): void
    {
        $this->mainLine->clearAllComments();
    }

    public function toArray(): array
    {
        return [
            'tags' => $this->tags,
            'result' => $this->result?->value,
            'initialPosition' => $this->initialPosition?->toArray(),
            'mainLine' => $this->mainLine->toArray(),
        ];
    }

    public function __clone(): void
    {
        $this->mainLine = clone $this->mainLine;
        $this->initialPosition = $this->initialPosition ? clone $this->initialPosition : null;
    }
}
