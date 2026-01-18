<?php

namespace Cmuset\PgnParser\Tool\Splitter;

use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Variation;

interface VariationSplitterInterface
{
    /**
     * @return Variation[]
     */
    public function split(Game|Variation $variation): array;
}
