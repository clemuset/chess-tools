<?php

namespace Cmuset\PgnParser\Tool\Resolver;

use Cmuset\PgnParser\Enum\ResultEnum;
use Cmuset\PgnParser\Model\Game;

class GameResolver implements GameResolverInterface
{
    public function __construct(
        private readonly VariationResolverInterface $variationResolver,
    ) {
    }

    public static function create(): self
    {
        return new self(VariationResolver::create());
    }

    public function resolve(Game $game): void
    {
        $this->variationResolver->resolve($game->getInitialPosition(), $game->getMainLine());

        ($finalPosition = clone $game->getInitialPosition())->applyVariation($game->getMainLine());

        if ($finalPosition->isCheckmate()) {
            $game->setResult(ResultEnum::fromColor($finalPosition->getSideToMove()->opposite()));
        } elseif ($finalPosition->isStalemate()) {
            $game->setResult(ResultEnum::DRAW);
        }
    }
}
