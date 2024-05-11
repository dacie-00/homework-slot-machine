<?php

// TODO:

$properties = [
    "width" => 4,
    "height" => 3,
    "winConditions" => []
];


$elements = [createElement("+", 3), createElement("-", 1), createElement("*", 1), ];
function weightedRandom($elements) {
    $total = array_sum(array_column($elements, "weight"));
    $randomValue = rand(0, $total);
    foreach ($elements as $element) {
        echo "$randomValue - $element->weight\n";
        if ($randomValue <= $element->weight) {
            return $element;
        }
        $randomValue -= $element->weight;
    }
    throw new Exception("Weights not correctly defined!");
}
function fillBoard(stdClass $board) {
    for ($y = 0; $y < $board->height; $y++) {
        $board->content[$y] = [];
        for ($x = 0; $x < $board->width; $x++) {
            $board->content[$y][$x] = weightedRandom($board->elements);
        }
    }
}

function createElement($symbol, $weight, $value) {
    $element = new stdClass();
    $element->symbol = $symbol;
    $element->weight = $weight;
    $element->value = $value;
    return $element;
}

function createBoard()
{
    $board = new stdClass();
    $board->elements = [
        createElement("+", 3, 1),
        createElement("-", 1 , 1),
        createElement("*", 1, 1)
    ];
    $board->content = [];
    $board->width = 30;
    $board->height = 8;
    $board->winConditions = [];
    return $board;
}

function displayBoard(stdClass $board)
{
    foreach ($board->content as $row) {
        foreach ($row as $value) {
            echo $value->symbol;
        }
        echo "\n";
    }
}


$board = createBoard();
fillBoard($board);
displayBoard($board);