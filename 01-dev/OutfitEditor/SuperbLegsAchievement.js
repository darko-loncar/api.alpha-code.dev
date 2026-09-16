
export const superbLegsAchievement = {
    id: "superbLegs",
    name: "Superb Legs",
    type: "bonus",
    levels: [
        {
            lvl: 1,
            description: "Perfect legs",
            icon: "sexy-secretary-1.png",
            bonus: {
                flatAdds : {
                    skirtLength: 100,
                    heelHeight: 100,
                    legs: 300,
                    footwear: 100,
                    skirtSlit: 0
                    
                },
                multipliers : {
                    heelHeight: 0,
                    skirtLength: 0,
                    skirtSlit: 0
                }
            },
            check: outfit => 
                outfit.has.visibleLegs && 
                outfit.has.heels && 
                outfit.has.pantyhose &&
                outfit.selected.heelHeight > 10
        },

        {
            lvl: 2,
            description: "Perfect legs",
            icon: "sexy-secretary-1.png",
            bonus: {
                flatAdds : {
                    skirtLength: 10,
                    heelHeight: 100,
                    legs: 300,
                    footwear: 100,
                    skirtSlit: 50
                    
                },
                multipliers : {
                    heelHeight: 2,
                    skirtLength: 2,
                    skirtSlit: 2
                }
            },
            check: outfit => 
                outfit.has.visibleLegs && 
                outfit.has.heels && 
                outfit.has.pantyhose &&
                outfit.selected.heelHeight > 15
        },


    ]
};

