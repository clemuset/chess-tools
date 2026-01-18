<?php

namespace Cmuset\PgnParser\Tool\Resolver;

use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Model\Variation;

interface VariationResolverInterface
{
    public function resolve(Position $position, Variation $variation): void;
}
