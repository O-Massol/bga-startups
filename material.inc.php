<?php

/*
 * Static game data. Company id equals its card count and its scoring order (ascending).
 */

const CARDS_REMOVED_AT_SETUP = 5;
const STARTING_HAND_SIZE = 3;
const STARTING_MONEY = 10;

const LOCATION_DECK = 'deck';
const LOCATION_HAND = 'hand';
const LOCATION_MARKET = 'market';
const LOCATION_PORTFOLIO = 'portfolio';
const LOCATION_REMOVED = 'removed';

$this->companies = [
    5 => ['name' => clienttranslate('Giraffe Beer'), 'cards' => 5],
    6 => ['name' => clienttranslate('Bowwow Games'), 'cards' => 6],
    7 => ['name' => clienttranslate('Flamingo Soft'), 'cards' => 7],
    8 => ['name' => clienttranslate('Octo Coffee'), 'cards' => 8],
    9 => ['name' => clienttranslate('Hippo Powertech'), 'cards' => 9],
    10 => ['name' => clienttranslate('Elephant Mars Travel'), 'cards' => 10],
];
