<?php

namespace Cmuset\PgnParser\Tool\Merger;

use Cmuset\PgnParser\Model\Variation;

interface VariationMergerInterface
{
    public function merge(Variation $mainLine, Variation ...$variations): Variation;
}
