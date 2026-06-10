const SKIRT_LENGTH_POINTS_MULTIPLIER = 10;
const HEEL_HEIGHT_POINTS_MULTIPLIER = 10

/**
 * Load selected objects from form
 */
export const outfitForm = () => {
    const getSelectData = (select) => {
        const i = select.selectedIndex;
        const opt = select.options[i];
        return {
            index: Number(opt.dataset.id),
            value: Number(opt.dataset.value) || 0
        };
    };

    return {
        hair: getSelectData(getById("hair")),
        face: getSelectData(getById("face")),
        upper: getSelectData(getById("upper")),
        bra: getSelectData(getById("bra")),
        mid: getSelectData(getById("mid")),
        underwear: getSelectData(getById("between-legs")),
        legs: getSelectData(getById("legs")),
        footwear: getSelectData(getById("footwear")),
        heelHeight: Number(getById("heelHeight")?.value) || 0,
        skirtLength: Number(getById("skirtLength")?.value) || 0,
        skirtSlit: getById("skirtSlit")?.checked || false,
        glasses: getSelectedRadio(document.getElementsByName("glasses")),
        minSkirtLength: Number(getById("skirtLength")?.min) || 0,
        maxSkirtLength: Number(getById("skirtLength")?.max) || 0,
    };
};


export class Outfit {

    constructor(outfitData) {
        this.outfitData = outfitData;
    }

    // Returns 
    get selected() {
        return this.outfitData;
    }

    // Boolean outfit helpers
    get has() {
        return {
            skirtSlit:      this.selected.skirtSlit,
            glasses:        this.selected.glasses > 0,
            heels:          [2,5].includes(this.selected.footwear.index),
            miniSkirt:      [2,3,4,5,9].includes(this.selected.mid.index),
            pantyhose:      [2,3,4].includes(this.selected.legs.index),
            visibleLegs:    [2,3,4,5,6,7,9].includes(this.selected.mid.index),
            visiblePussy:   [1].includes(this.selected.underwear.index) && 
                                ([2,3,4,5,9].includes(this.selected.mid.index) || [1].includes(this.selected.underwear.index))
        }
    };

   // Return calculated results based on outfit state and values in outfit form
    getPoints () {
        return {
            // Plain base points (raw values = points)
            hair: this.outfitData.hair.value,
            face: this.outfitData.face.value,
            upper: this.outfitData.upper.value,
            bra: this.outfitData.bra.value,
            mid: this.outfitData.mid.value,
            underwear: this.outfitData.underwear.value,
            legs: this.outfitData.legs.value,
            footwear: this.outfitData.footwear.value,

            // Computed base outfit points (points are computed from raw values )
            base: getBaseOutfitTotalPts(this),
            skirtLength: getSkirtLengthPts(this),
            heelHeight: getHeelHeightPts(this),
            skirtSlit: getSkirtSlitPts(this),
        }
    }       
}

/* Calculate total base outfit selection points */
const getBaseOutfitTotalPts = (outfit) => 
    ["hair","face","upper","bra","mid","underwear","legs","footwear"]
        .reduce((total, key) => total + (parseInt(outfit.selected[key].value) || 0), 0);

/* Bonus points for skirt slit - 0 points if skirt not selected */
const getSkirtSlitPts = (outfit) => 
    outfit.has.miniSkirt && outfit.has.skirtSlit 
        ? 100 
        : 0;

/* Calculate points for high heels - 0 points if no heels selected */
const getHeelHeightPts = (outfit) => 
    outfit.has.heels 
        ? Math.ceil((outfit.selected.heelHeight - 8) * HEEL_HEIGHT_POINTS_MULTIPLIER) 
        : 0;

/* Calculate points for skirt length - 0 points if skirt not selected */
const getSkirtLengthPts = (outfit) => 
    outfit.has.miniSkirt 
        ? Math.ceil(outfit.selected.maxSkirtLength - outfit.selected.skirtLength) * SKIRT_LENGTH_POINTS_MULTIPLIER
        : 0;


