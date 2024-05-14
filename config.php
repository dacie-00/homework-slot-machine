<?php

$properties = [
    "width" => 5,
    "height" => 3,
    "winConditions" => [
        createWinCondition("relative", [[0, 0], [1, 0], [2, 0], [3, 0], [4, 0]]),
        createWinCondition("relative", [[0, 0], [0, 1], [0, 2]]),
        createWinCondition("absolute", [[1, 1], [2, 1], [3, 1]]),
        createWinCondition("absolute", [[0, 2], [1, 1], [2, 0], [3, 1], [4, 2]]),
        createWinCondition("absolute", [[0, 2], [1, 1], [2, 0], [3, 1], [4, 2]]),
    ],
    "baseBet" => 5,
    "elements" => [
        createElement("A", 7, 1),
        createElement("B", 1, 5),
        createElement("C", 3, 2),
        createElement("D", 4, 1)
    ]
];
