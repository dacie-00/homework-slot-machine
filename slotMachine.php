<?php

require_once("helpers.php");
require_once("userInput.php");

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
    return $element->value * count($condition) * $basePayout * $ratio;
}

$properties = [
    "width" => 5,
    "height" => 5,
    "winConditions" => [[[0, 0], [1, 0], [2, 0]], [[0, 0], [1, 0], [2, 0], [3, 0]]],
    "baseBet" => 5
];

$money = 1000;

echo "Welcome!\n";
echo "Enter the total amount of coins you wish to play with!\n";
$money = getUserChoiceFromRange(1, 100000, null, "count");
$bet = 5;
$bet = min($bet, $money);

while (true) {
    echo "You have $money coins.\n";
    while(true) {
        echo "1) Play with a bet of $bet coins\n";
        echo "2) Change bet amount\n";
        $choice = getUserChoiceFromArray(["1", "2"], "choice");
        switch ($choice) {
            case 1:
                break 2;
            case 2:
                $bet = getUserChoiceFromRange(1, $money, null, "bet amount");
                break;
        }
    }
    $betRatio = $bet / $properties["baseBet"];

    $board = createBoard($properties["width"], $properties["height"]);
    fillBoard($board);
    displayBoard($board);
    $matches = findMatches($board, $properties["winConditions"]);

    $moneyBefore = $money;
    $money -= $bet;
    foreach ($matches as $match) {
        $payout = calculateMatchPayout($match->element, $match->condition, $properties["baseBet"], $betRatio);
        $money += $payout;
        echo "{$match->element->symbol}, ($match->x $match->y), matched!, $payout coins!\n";
    }
    $moneyDelta = $money - $moneyBefore;
    $moneyDeltaDisplay = abs($moneyDelta);
    if ($moneyDelta > 0) {
        echo "Congratulations! You made a profit of $moneyDeltaDisplay coins!\n";
    }
    if ($moneyDelta < 0) {
        echo "Oh no! You made a loss of $moneyDeltaDisplay coins!\n";
        if ($bet > $money) {
            $bet = $money;
        }
    }
    if ($moneyDelta === 0) {
        echo "You broke even!\n";
    }
    if ($money <= 0) {
        echo "You ran out of money! Oh well, thanks for playing!\n";
        exit();
    }
}
