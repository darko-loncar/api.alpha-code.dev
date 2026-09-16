const OFFICE_SKIRT_INDEX = 2;
const OFFICE_SHIRT_INDEX = 6;
const HAIR_FACE_SPECIAL = [2, 3];

const isOfficeBase = (outfit) => outfit && outfit.selected &&
    outfit.selected.mid && outfit.selected.upper &&
    outfit.selected.mid.index === OFFICE_SKIRT_INDEX &&
    outfit.selected.upper.index === OFFICE_SHIRT_INDEX;

const hasHairOrFaceSpecial = (outfit) => outfit && outfit.selected && (
    HAIR_FACE_SPECIAL.includes(outfit.selected.hair.index) ||
    HAIR_FACE_SPECIAL.includes(outfit.selected.face.index)
);

export const sexySecretaryAchievement = Object.freeze({
    id: "sexySecretary",
    name: "Sexy Secretary",
    type: "bonus",
    levels: [
        {
            lvl: 1,
            description: "Basic secretary outfit",
            icon: "sexy-secretary-1.png",
            bonus: {
                flatAdds: {
                    heelHeight: 20
                },
                multipliers: {
                    heelHeight: 0.2,
                    skirtLength: 0.3
                }
            },
            check: outfit => isOfficeBase(outfit) && outfit.has.heels && outfit.has.glasses,
        },
        {
            lvl: 2,
            description: "Upgraded secretary outfit",
            icon: "sexy-secretary-2.png",
            bonus: {
                flatAdds: {
                    heelHeight: 50,
                    skirtLength: 50
                },
                multipliers: {
                    heelHeight: 0.5,
                    skirtLength: 0.8
                }
            },
            check: outfit => isOfficeBase(outfit) && outfit.has.heels && outfit.has.glasses && outfit.has.pantyhose,
        },
        {
            lvl: 3,
            description: "Ultimate & Epic secretary outfit",
            icon: "sexy-secretary-3.png",
            bonus: {
                flatAdds: {
                    heelHeight: 80,
                    skirtLength: 100,
                    legs: 100
                },
                multipliers: {
                    heelHeight: 1.2,
                    skirtLength: 1.3
                }
            },
            check: outfit => isOfficeBase(outfit) && outfit.has.heels && outfit.has.glasses && outfit.has.pantyhose && hasHairOrFaceSpecial(outfit)

        }
    ]
});

