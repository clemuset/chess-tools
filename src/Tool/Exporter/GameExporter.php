<?php

namespace Cmuset\ChessTools\Tool\Exporter;

use Cmuset\ChessTools\Enum\ColorEnum;
use Cmuset\ChessTools\Model\Game;
use Cmuset\ChessTools\Model\MoveNode;
use Cmuset\ChessTools\Model\Variation;

class GameExporter implements GameExporterInterface
{
    public function __construct(
        private readonly MoveExporterInterface $moveExporter
    ) {
    }

    public static function create(): self
    {
        return new self(new MoveExporter());
    }

    public function export(Game|Variation $game): string
    {
        if ($game instanceof Variation) {
            return trim($this->exportLine($game));
        }

        $pgn = '';

        // Export tags
        foreach ($game->getTags() as $tag => $value) {
            $pgn .= "[$tag \"$value\"]\n";
        }

        $pgn .= "\n";

        $pgn .= $this->exportLine($game->getMainLine());
        $pgn .= $game->getResult()->value ?? '*';

        return trim($pgn);
    }

    private function exportLine(Variation $line): string
    {
        $pgn = '';
        $ellipsis = true;

        /** @var MoveNode $node */
        foreach ($line as $node) {
            if ($beforeComment = $node->getBeforeMoveComment()) {
                $pgn .= '{' . $beforeComment . '} ';
                $ellipsis = true;
            }

            if (ColorEnum::WHITE === $node->getColor()) {
                $pgn .= $node->getMoveNumber() . '. ';
                $ellipsis = false;
            } elseif ($ellipsis) {
                $pgn .= $node->getMoveNumber() . '... ';
            }

            $pgn .= $this->moveExporter->export($node->getMove()) . ' ';

            foreach ($node->getNags() as $nag) {
                $pgn .= '$' . $nag . ' ';
            }

            if ($comment = $node->getComment()) {
                $pgn .= '{' . $comment . '} ';
                $ellipsis = true;
            }

            foreach ($node->getVariations() as $variation) {
                $pgn .= '(' . trim($this->exportLine($variation)) . ') ';
                $ellipsis = true;
            }
        }

        return $pgn;
    }
}
