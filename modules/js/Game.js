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
 * @typedef {Object} TakeArgs
 * @property {number} drawCost
 * @property {boolean} canDraw
 * @property {number[]} takeableCardIds
 */
/**
 * @typedef {Object} PlayArgs
 * @property {string|null} forbiddenMarketCompany company just taken from the market, which cannot go back in it
 */

class PlayerTurnTake {
    /**
     * @param {Game} game
     * @param {Bga} bga
     */
    constructor(game, bga) {
        this.game = game;
        this.bga = bga;
    }

    /**
     * @param {TakeArgs} args
     * @param {boolean} isCurrentPlayerActive
     */
    onEnteringState(args, isCurrentPlayerActive) {
        if (!isCurrentPlayerActive) {
            return;
        }
        this.bga.statusBar.addActionButton(
            _('Draw a card (cost: ${cost})').replace('${cost}', `${args.drawCost}`),
            () => this.bga.actions.performAction('actDrawFromDeck'),
            { disabled: !args.canDraw }
        );
        this.game.state.market
            .filter(card => args.takeableCardIds.includes(card.id))
            .forEach(card => this.bga.statusBar.addActionButton(
                _('Take ${company} (${chips} chip(s))')
                    .replace('${company}', this.game.companyName(card.company))
                    .replace('${chips}', `${card.chips}`),
                () => this.bga.actions.performAction('actTakeFromMarket', { cardId: card.id }),
                { color: 'secondary' }
            ));
    }
}

class PlayerTurnPlay {
    /**
     * @param {Game} game
     * @param {Bga} bga
     */
    constructor(game, bga) {
        this.game = game;
        this.bga = bga;
    }

    /**
     * @param {PlayArgs} args
     * @param {boolean} isCurrentPlayerActive
     */
    onEnteringState(args, isCurrentPlayerActive) {
        if (!isCurrentPlayerActive) {
            return;
        }
        // Cards of one company are interchangeable: one pair of buttons per company in hand.
        const handByCompany = new Map();
        this.game.state.hand.forEach(card => {
            if (!handByCompany.has(card.company)) {
                handByCompany.set(card.company, card.id);
            }
        });
        handByCompany.forEach((cardId, company) => {
            const name = this.game.companyName(company);
            this.bga.statusBar.addActionButton(
                _('${company}: portfolio').replace('${company}', name),
                () => this.bga.actions.performAction('actPlayToPortfolio', { cardId })
            );
            this.bga.statusBar.addActionButton(
                _('${company}: market').replace('${company}', name),
                () => this.bga.actions.performAction('actPlayToMarket', { cardId }),
                { color: 'secondary', disabled: args.forbiddenMarketCompany === company }
            );
        });
    }
}

export class Game {
    /**
     * @param {Bga} bga
     */
    constructor(bga) {
        this.bga = bga;

        this.bga.states.register('PlayerTurnTake', new PlayerTurnTake(this, bga));
        this.bga.states.register('PlayerTurnPlay', new PlayerTurnPlay(this, bga));
    }

    /**
     * @param {startupsGamedatas} gamedatas
     */
    setup(gamedatas) {
        this.state = gamedatas;

        this.bga.gameArea.getElement().insertAdjacentHTML('beforeend', `
            <div id="player-tables"></div>
            <div id="deck"></div>
            <div id="market"></div>
            <div id="my-hand"></div>
        `);
        this.render();

        this.bga.notifications.setupPromiseNotifications({});
    }

    companyName(id) {
        return _(this.state.companies[id].name);
    }

    playerName(id) {
        return this.state.players[id].name;
    }

    /** Plain DOM rendering of the whole state; the notification handlers only update `this.state`. */
    render() {
        const state = this.state;
        const cardLabel = card => `${this.companyName(card.company)}${card.chips ? ` (+${card.chips})` : ''}`;

        document.getElementById('player-tables').innerHTML = Object.values(state.players).map(player => {
            const portfolio = (state.portfolios[player.id] ?? []).map(cardLabel).join(', ') || '-';
            const chips = Object.entries(state.chipOwners)
                .filter(([, owner]) => owner === player.id)
                .map(([company]) => this.companyName(company))
                .join(', ') || '-';
            return `
                <div id="player-table-${player.id}">
                    <strong>${player.name}</strong>
                    <span>${_('Money')}: ${player.money}</span>
                    <span>${_('Cards in hand')}: ${state.handCounts[player.id] ?? 0}</span>
                    <div>${_('Portfolio')}: ${portfolio}</div>
                    <div>${_('Anti-Monopoly chips')}: ${chips}</div>
                </div>`;
        }).join('');

        document.getElementById('deck').innerHTML = `${_('Draw pile')}: ${state.deckCount}`;
        document.getElementById('market').innerHTML =
            `<strong>${_('Market')}</strong>: ${state.market.map(cardLabel).join(', ') || '-'}`;
        document.getElementById('my-hand').innerHTML =
            `<strong>${_('Your hand')}</strong>: ${state.hand.map(cardLabel).join(', ') || '-'}`;
    }

    changeHandCount(playerId, delta) {
        this.state.handCounts[playerId] = (this.state.handCounts[playerId] ?? 0) + delta;
    }

    removeFromHand(cardId) {
        this.state.hand = this.state.hand.filter(card => card.id !== cardId);
    }

    notif_cardDrawn(args) {
        this.state.players[args.player_id].money = args.money;
        this.state.market.filter(card => args.chippedCardIds.includes(card.id)).forEach(card => card.chips++);
        this.state.deckCount = args.deckCount;
        this.changeHandCount(args.player_id, 1);
        this.render();
    }

    notif_cardDrawnPrivate(args) {
        this.state.hand.push({ ...args.card, chips: 0 });
        this.render();
    }

    notif_cardTakenFromMarket(args) {
        this.state.market = this.state.market.filter(card => card.id !== args.card.id);
        this.state.players[args.player_id].money = args.money;
        this.changeHandCount(args.player_id, 1);
        if (args.player_id === this.bga.players.getCurrentPlayerId()) {
            this.state.hand.push({ ...args.card, chips: 0 });
        }
        this.render();
    }

    notif_cardPlayedToPortfolio(args) {
        this.removeFromHand(args.card.id);
        this.changeHandCount(args.player_id, -1);
        (this.state.portfolios[args.player_id] ??= []).push({ ...args.card, chips: 0 });
        this.render();
    }

    notif_antiMonopolyChipMoved(args) {
        this.state.chipOwners[args.company] = args.to;
        this.render();
    }

    notif_cardPlayedToMarket(args) {
        this.removeFromHand(args.card.id);
        this.changeHandCount(args.player_id, -1);
        this.state.market.push({ ...args.card, chips: 0 });
        this.render();
    }
}
