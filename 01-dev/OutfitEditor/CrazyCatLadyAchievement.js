import { Achievement } from './Achievement.js';

export class CrazyCatLadyAchievement extends Achievement {
    constructor(outfit) {

        super("crazyCatLady", "Crazy Cat Lady", "malus");
        this.levels = [
            { 
                lvl: 1, 
                description: "Crazy Cat Lady", 
                ptsBaseBonus: 300,
                icon: "../gfx/icon-of-style-1.png", 
                check: () => outfit.count.uglyItems > 3
            },
        ]

    }
}
