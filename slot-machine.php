<?php

require_once("helpers.php");

function checkMatch(stdClass $board, array $condition, $x, $y): bool {
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

function createMatch(stdClass $element, array $condition, int $x, int $y) {
    $match = new stdClass();
    $match->element = $element;
    $match->condition = $condition;
    $match->x = $x;
    $match->y = $y;
    return $match;
}

function findMatches(stdClass $board, array $winConditions) {
    $matches = [];
    foreach ($winConditions as $condition) {
        foreach ($board->content as $y => $row) {
            foreach ($row as $x => $element) {
                if (checkMatch($board, $condition, $x, $y)) {
                    $matches[] = createMatch($element, $condition, $x, $y);
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
        createElement("/", 3, 1),
        createElement("-", 1 , 2),
        createElement("q", 1, 2)
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

function calculateMatchPayout($element, $condition, $basePayout, $ratio) {
    return $element->value * count($condition) * $basePayout;
}

$properties = [
    "width" => 5,
    "height" => 5,
    "winConditions" => [[[0, 0], [1, 0], [2, 0]], [[0, 0], [1, 0], [2, 0], [3, 0]]],
    "basePay" => 5
];

$money = 1000;
$bet = 100;
$betRatio = $properties["basePay"] / 2;

$board = createBoard($properties["width"], $properties["height"]);
fillBoard($board);
displayBoard($board);
$matches = findMatches($board, $properties["winConditions"]);

$moneyBefore = $money;
$money -= $bet;
foreach ($matches as $match) {
    $payout = calculateMatchPayout($match->element, $match->condition, $properties["basePay"], $betRatio);
    $money += $payout;
    echo "{$match->element->symbol}, ($match->x $match->y), matched!, $payout dollars!\n";
}
$moneyDelta = $money - $moneyBefore;
$moneyDeltaDisplay = abs($moneyDelta);
if ($moneyDelta > 0) {
    echo "Congratulations! You made a profit of $moneyDeltaDisplay dollars!\n";
}
if ($moneyDelta < 0) {
    echo "Oh no! You made a loss of $moneyDeltaDisplay dollars!\n";
}
if ($moneyDelta === 0) {
    echo "You broke even!\n";
}
