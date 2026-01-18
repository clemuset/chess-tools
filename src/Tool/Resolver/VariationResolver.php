<?php

namespace Cmuset\PgnParser\Tool\Resolver;

use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Model\Variation;

class VariationResolver implements VariationResolverInterface
{
    public function __construct(
        private readonly MoveResolverInterface $moveResolver,
    ) {
    }

    public static function create(): self
    {
        return new self(MoveResolver::create());
    }

    public function resolve(Position $position, Variation $variation): void
    {
        foreach ($variation as $node) {
            $this->moveResolver->resolve($position, $node->getMove());
            $position = (clone $position);
            $position->applyMove($node->getMove());
        }
    }
}
