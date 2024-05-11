<?php

require_once("helpers.php");

$properties = [
    "width" => 3,
    "height" => 3,
    "winConditions" => [[[0, 0], [0, 1], [0, 2], [1, 2]]]
];

function checkMatch(stdClass $board, stdClass $element, array $condition, $x, $y): bool {
    $matchSymbol = $board->content[$y][$x]->symbol;
    foreach ($condition as $position) {
        if (!isset($board->content[$y + $position[1]][$x + $position[0]]->symbol)) {
            return false;
        }
        if ($matchSymbol != $board->content[$y + $position[1]][$x + $position[0]]->symbol) {
            return false;
        }
    }
    return true;
}

function findMatches(stdClass $board, array $winConditions) {
    $matches = [];
    foreach ($winConditions as $condition) {
        foreach ($board->content as $y => $row) {
            foreach ($row as $x => $element) {
                $matchFound = checkMatch($board, $element, $condition, $x, $y);
                if ($matchFound) {
                    $matches[] = [$element, $condition, $x, $y];
                }
            }
        }
    }
    return $matches;
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
    foreach ($board->content as $y => $row) {
        foreach ($row as $x => $element) {
            echo $element->symbol;
        }
        echo "\n";
    }
}


$board = createBoard($properties["width"], $properties["height"]);
fillBoard($board);
displayBoard($board);
$matches = findMatches($board, $properties["winConditions"]);
foreach ($matches as $match) {
    echo "{$match[0]->symbol}, $match[2], $match[3]\n";
}