<?php

namespace Cmuset\ChessTools\Tool\Merger;

use Cmuset\ChessTools\Model\Variation;

interface VariationMergerInterface
{
    public function merge(Variation $mainLine, Variation ...$variations): Variation;
}
