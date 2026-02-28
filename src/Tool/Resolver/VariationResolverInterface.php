<?php

namespace Cmuset\ChessTools\Tool\Resolver;

use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Model\Variation;

interface VariationResolverInterface
{
    public function resolve(Position $position, Variation $variation): void;
}
