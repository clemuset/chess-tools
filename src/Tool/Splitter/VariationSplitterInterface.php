<?php

namespace Cmuset\ChessTools\Tool\Splitter;

use Cmuset\ChessTools\Model\Game;
use Cmuset\ChessTools\Model\Variation;

interface VariationSplitterInterface
{
    /**
     * @return Variation[]
     */
    public function split(Game|Variation $variation): array;
}
