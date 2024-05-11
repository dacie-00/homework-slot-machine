<?php

function formatCurrency(int $amount): string
{
    return '$' . number_format($amount / 100, 2);
}

//function weightedRandom($elements) {
//    $total = array_sum(array_column($elements, "weight"));
//    $randomValue = rand(0, $total);
//    foreach ($elements as $element) {
//        if ($randomValue <= $element->weight) {
//            return $element;
//        }
//        $randomValue -= $element->weight;
//    }
//    throw new Exception("Weights not correctly defined!");
//}
function weightedRandom($elements)
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