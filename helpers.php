<?php

function formatCurrency(int $amount): string
{
    return '$' . number_format($amount / 100, 2);
}

function weightedRandom(array $elements): stdClass
{
    $randomValue = mt_rand(1, (int)array_sum(array_column($elements, "weight")));

    foreach ($elements as $element) {
        if ($element->weight < 0) {
            throw new InvalidArgumentException("Element weight cannot be negative");
        }
        $randomValue -= $element->weight;
        if ($randomValue <= 0) {
            return $element;
        }
    }
}