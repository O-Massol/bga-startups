<?php

use Bga\Games\startups\Rules\Companies;

/*
 * Translatable static data. Rules data (ids, card counts, setup values) lives in modules/php/Rules.
 */

$this->companies = [
    Companies::GIRAFFE_BEER => ['name' => clienttranslate('Giraffe Beer')],
    Companies::BOWWOW_GAMES => ['name' => clienttranslate('Bowwow Games')],
    Companies::FLAMINGO_SOFT => ['name' => clienttranslate('Flamingo Soft')],
    Companies::OCTO_COFFEE => ['name' => clienttranslate('Octo Coffee')],
    Companies::HIPPO_POWERTECH => ['name' => clienttranslate('Hippo Powertech')],
    Companies::ELEPHANT_MARS_TRAVEL => ['name' => clienttranslate('Elephant Mars Travel')],
];
