<?php

/*
 * Static game data. Companies are listed in scoring order (ascending card count).
 */

const CARDS_REMOVED_AT_SETUP = 5;
const STARTING_HAND_SIZE = 3;
const STARTING_MONEY = 10;

const LOCATION_DECK = 'deck';
const LOCATION_HAND = 'hand';
const LOCATION_MARKET = 'market';
const LOCATION_PORTFOLIO = 'portfolio';
const LOCATION_REMOVED = 'removed';

const COMPANY_GIRAFFE_BEER = 'giraffe_beer';
const COMPANY_BOWWOW_GAMES = 'bowwow_games';
const COMPANY_FLAMINGO_SOFT = 'flamingo_soft';
const COMPANY_OCTO_COFFEE = 'octo_coffee';
const COMPANY_HIPPO_POWERTECH = 'hippo_powertech';
const COMPANY_ELEPHANT_MARS_TRAVEL = 'elephant_mars_travel';

$this->companies = [
    COMPANY_GIRAFFE_BEER => ['name' => clienttranslate('Giraffe Beer'), 'cards' => 5],
    COMPANY_BOWWOW_GAMES => ['name' => clienttranslate('Bowwow Games'), 'cards' => 6],
    COMPANY_FLAMINGO_SOFT => ['name' => clienttranslate('Flamingo Soft'), 'cards' => 7],
    COMPANY_OCTO_COFFEE => ['name' => clienttranslate('Octo Coffee'), 'cards' => 8],
    COMPANY_HIPPO_POWERTECH => ['name' => clienttranslate('Hippo Powertech'), 'cards' => 9],
    COMPANY_ELEPHANT_MARS_TRAVEL => ['name' => clienttranslate('Elephant Mars Travel'), 'cards' => 10],
];
