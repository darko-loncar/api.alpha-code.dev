import { Achievement } from './Achievement.js';

export class InvisibleLegsAchievement extends Achievement {
    constructor(outfit) {
        super("invisibleLegs", "Invisible Legs", "malus");
        this.levels = [
            { 
                lvl: 1, 
                description: "Invisible legs", 
                ptsBaseBonus: -500,
                icon: "../gfx/icon-of-style-1.png", 
                check: () => !outfit.has.VisibleLegs
            },
        ]

    }
}
