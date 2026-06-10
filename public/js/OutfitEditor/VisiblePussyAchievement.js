import { Achievement } from './Achievement.js';

export class VisiblePussyAchievement extends Achievement {
    constructor(outfit) {
        super("visiblePussy", "Visible Pussy", "bonus");
        this.levels = [
            { 
                lvl: 1, 
                description: "Visible XXX part", 
                ptsBaseBonus: 300,
                icon: "../gfx/icon-of-style-1.png", 
                check: () => outfit.has.VisiblePussy
            },
        ]

    }
}
