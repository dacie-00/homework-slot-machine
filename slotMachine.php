<?php

//- Allow enter start amount of virtual coins to play with
//- Allow to set BET amount per single spin
//- Continuously play while there is enough coins
//- Win amount should be sized based on step/size per BET. If base bet is 5 but I set 10 it should give me twice the win
//per win condition.
//If there are elements of 3 that each gives 5, and I bet twice (10) then it should be 3*5*2 = 30 as win amount.
//- There should be option to change board size with few lines of code
//- There should be option to define win conditions with few lines of code
//
//TASK SHOULD BE DONE IN SEPARATE REPOSITORY
//CODE SHOULD BE FORMATTED
//CODE SHOULD MATCH PSR STANDARTS

include ("config.php");

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
    return $elements[0]; // Code should never get here, but in case it does we return the first element
}

function checkMatchRelative(stdClass $board, stdClass $condition, int $x, int $y): bool
{
    $matchSymbol = $board->content[$y][$x]->symbol;
    foreach ($condition->positions as $index => $position) {
        if ($index === 0) {
            continue; // First one is skipped because we don't need to check it against itself
        }
        if (!isset($board->content[$y + $position[1]][$x + $position[0]]->symbol)) {
            return false;
        }
        if ($matchSymbol != $board->content[$y + $position[1]][$x + $position[0]]->symbol) {
            return false;
        }
    }
    return true;
}


function checkMatchAbsolute(stdClass $board, stdClass $condition): bool
{
    $matchSymbol = $board->content[$condition->positions[0][1]][$condition->positions[0][0]]->symbol;
    foreach ($condition->positions as $index => $position) {
        if ($index === 0) {
            continue; // First one is skipped because we don't need to check it against itself
        }
        if (!isset($board->content[$position[1]][$position[0]]->symbol)) {
            return false;
        }
        if ($matchSymbol != $board->content[$position[1]][$position[0]]->symbol) {
            return false;
        }
    }
    return true;
}

function markMatchedElements(stdClass $board, stdClass $match): void
{
    if ($match->type === strtolower("relative")) {
        foreach ($match->condition->positions as $position) {
            $board->content[$match->y + $position[1]][$match->x + $position[0]]->matchCount += 1;
        }
        return;
    }
    foreach ($match->condition->positions as $position) {
        $board->content[$position[1]][$position[0]]->matchCount += 1;
    }
}

function createMatch(stdClass $element, stdClass $condition, string $type, int $x = null, int $y = null): stdClass
{
    $match = new stdClass();
    $match->element = $element;
    $match->condition = clone $condition;
    $match->type = $type;
    $match->x = $x;
    $match->y = $y;
    return $match;
}

function findMatches(stdClass $board, array $winConditions): array
{
    $matches = [];

    foreach ($winConditions as $condition) {
        if ($condition->type === strtolower("relative")) {
            foreach ($board->content as $y => $row) {
                foreach ($row as $x => $element) {
                    if (checkMatchRelative($board, $condition, $x, $y)) {
                        $matches[] = createMatch($element, $condition, "relative", $x, $y);
                    }
                }
            }
        } else {
            $x = $condition->positions[0][0];
            $y = $condition->positions[0][1];
            if (checkMatchAbsolute($board, $condition)) {
                $matches[] = createMatch($board->content[$y][$x], $condition, "absolute", $x, $y);
            }
        }
    }
    return $matches;
}

function fillBoard(stdClass $board): void
{
    for ($y = 0; $y < $board->height; $y++) {
        $board->content[$y] = [];
        for ($x = 0; $x < $board->width; $x++) {
            $board->content[$y][$x] = clone weightedRandom($board->elements);
        }
    }
}

function createElement(string $symbol, int $weight, int $value): stdClass
{
    $element = new stdClass();
    $element->symbol = $symbol;
    $element->weight = $weight;
    $element->matchCount = 0;
    $element->value = $value;
    return $element;
}

function createWinCondition(string $type, array $positions): stdClass
{
    if ($type != strtolower("absolute") && $type != strtolower("relative")) {
        throw new Exception("Invalid win condition type - $type. Must be either absolute or relative");
    }
    $winCondition = new stdClass();
    $winCondition->positions = $positions;
    $winCondition->type = $type;
    if ($winCondition->type === strtolower("relative")) { // force relative condition to start at 0, 0
        $baseX = $winCondition->positions[0][0];
        $baseY = $winCondition->positions[0][1];
        foreach ($winCondition->positions as &$position) {
            $position[0] -= $baseX;
            $position[1] -= $baseY;
        }
    }
    return $winCondition;
}

function createBoard(int $width, int $height, array $elements): stdClass
{
    $board = new stdClass();
    $board->elements = $elements;
    $board->content = [];
    $board->matched = [];
    $board->width = $width;
    $board->height = $height;
    $board->winConditions = [];
    return $board;
}

function displayBoard(stdClass $board): void
{
    $matchSymbols = [" ", "*", "&"]; // & gets drawn when two matches are overlapping
    $horizontalLine = str_repeat("+---", $board->width) . "+\n";
    foreach ($board->content as $row) {
        echo $horizontalLine;
        foreach ($row as $element) {
            echo "|";
            $matchSymbol = $matchSymbols[min($element->matchCount, 2)];
            echo $matchSymbol . $element->symbol . $matchSymbol;
        }
        echo "|";
        echo "\n";
    }
    echo $horizontalLine;
}

function calculateMatchPayout(stdClass $element, stdClass $condition, int $ratio): int
{
    return (int)$element->value * count($condition->positions) * $ratio;
}

function promptStartingCoins() {
    echo "Enter the total amount of coins you wish to play with!\n";
    while (true) {
        $coins = readline("Amount - ");
        if (!is_numeric($coins)) {
            echo "Coin amount must be a numeric value!\n";
            continue;
        }
        $coins = (int)$coins;
        if ($coins <= 0) {
            echo "Coin amount must be greater than 0!\n";
            continue;
        }
        if ($coins >= 1000000) {
            echo "Coin amount must be less than 1 000 000!\n";
            continue;
        }
        break;
    }
    return $coins;
}

function promptBetAmount($coins)
{
    echo "Enter your bet amount\n";
    while (true) {
        $bet = readline("Amount - ");
        if (!is_numeric($bet)) {
            echo "Bet amount must be a numeric value!\n";
            continue;
        }
        $bet = (int)$bet;
        if ($bet <= 0) {
            echo "Bet amount must be greater than 0!\n";
            continue;
        }
        if ($bet > $coins) {
            echo "Bet amount must be less than your coin count!\n";
            continue;
        }
        return $bet;
    }
}

echo "Welcome!\n";
$coins = promptStartingCoins();
$bet = $properties["baseBet"];
$bet = min($bet, $coins);

while (true) {
    echo "You have $coins coins.\n";
    while (true) {
        echo "1) Play with a bet of $bet coins\n";
        echo "2) Change bet amount\n";
        $choice = readline("Choice: ");
        switch ($choice) {
            case 1:
                break 2;
            case 2:
                $bet = promptBetAmount($coins);
                break;
            default:
                echo "Invalid choice!";
        }
    }
    $board = createBoard($properties["width"], $properties["height"], $properties["elements"]);
    fillBoard($board);
    $matches = findMatches($board, $properties["winConditions"]);

    $coinsBeforeSpin = $coins;
    $coins -= $bet;

    $betRatio = $bet / $properties["baseBet"];
    foreach ($matches as $match) {
        markMatchedElements($board, $match);
        $payout = calculateMatchPayout($match->element, $match->condition, $betRatio);
        $coins += $payout;
    }
    displayBoard($board);

    $coinsDelta = $coins - $coinsBeforeSpin;
    $coinsDeltaDisplay = abs($coinsDelta);
    if ($coinsDelta > 0) {
        echo "Nice! You made a profit of $coinsDeltaDisplay coins!\n";
    }
    if ($coinsDelta < 0) {
        echo "Oh no! You made a loss of $coinsDeltaDisplay coins!\n";
        if ($bet > $coins) {
            $bet = $coins;
        }
    }
    if ($coinsDelta === 0) {
        echo "You broke even!\n";
    }
    if ($coins <= 0) {
        echo "You ran out of money! Oh well, thanks for playing!\n";
        exit();
    }
}
