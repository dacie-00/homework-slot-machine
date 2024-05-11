<?php

require_once("helpers.php");

$properties = [
    "width" => 4,
    "height" => 3,
    "winConditions" => []
];

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

function createBoard($width, $height)
{
    $board = new stdClass();
    $board->elements = [
        createElement("+", 3, 1),
        createElement("-", 1 , 1),
        createElement("*", 1, 1)
    ];
    $board->content = [];
    $board->width = $width;
    $board->height = $height;
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


$board = createBoard($properties["width"], $properties["height"]);
fillBoard($board);
displayBoard($board);