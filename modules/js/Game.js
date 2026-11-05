/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * startups implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

/**
 * @typedef {Player} startupsPlayer
 */
/**
 * @typedef {Gamedatas<startupsPlayer>} startupsGamedatas
 */
/**
 * @typedef {Object} PlayerTurnArgs
 * @property {number[]} playableCardsIds
 */

class PlayerTurn {
    /** @type {PlayerTurnArgs} */
    args;

    /**
     * @param {Game} game
     * @param {Bga} bga
     */
    constructor(game, bga) {
        this.game = game;
        this.bga = bga;
    }

    /**
     * @param {PlayerTurnArgs} args
     * @param {boolean} isCurrentPlayerActive
     */
    onEnteringState(args, isCurrentPlayerActive) {
        this.bga.statusBar.setTitle(isCurrentPlayerActive ?
            _('${you} must play a card or pass') :
            _('${actplayer} must play a card or pass')
        );

        if (isCurrentPlayerActive) {
            args.playableCardsIds.forEach(
                cardId => this.bga.statusBar.addActionButton(_('Play card with id ${card_id}').replace('${card_id}', `${cardId}`), () => this.onCardClick(cardId))
            );

            this.bga.statusBar.addActionButton(_('Pass'), () => this.bga.actions.performAction("actPass"), { color: 'secondary' });
        }
    }

    /**
     * @param {number} card_id
     */
    onCardClick(card_id) {
        this.bga.actions.performAction("actPlayCard", { card_id });
    }
}

export class Game {
    /**
     * @param {Bga} bga
     */
    constructor(bga) {
        this.bga = bga;

        this.playerTurn = new PlayerTurn(this, bga);
        this.bga.states.register('PlayerTurn', this.playerTurn);
    }

    /**
     * @param {startupsGamedatas} gamedatas
     */
    setup(gamedatas) {
        this.gamedatas = gamedatas;

        this.bga.gameArea.getElement().insertAdjacentHTML('beforeend', `<div id="player-tables"></div>`);

        Object.values(gamedatas.players).forEach(player => {
            document.getElementById('player-tables').insertAdjacentHTML('beforeend', `
                <div id="player-table-${player.id}">
                    <strong>${player.name}</strong>
                </div>
            `);
        });

        this.setupNotifications();
    }

    setupNotifications() {
        this.bga.notifications.setupPromiseNotifications({});
    }
}
