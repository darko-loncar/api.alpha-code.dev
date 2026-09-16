import { Outfit, outfitForm } from './OutfitEditor/Outfit.js';
import { sexySecretaryAchievement } from './OutfitEditor/SexySecretaryAchievement.js';
import { superbLegsAchievement } from './OutfitEditor/SuperbLegsAchievement.js';


const BONUS_TEMPLATE = {
    flatAdds : {
        base: 0,
        skirtLength: 0,
        heelHeight: 0,
        skirtSlit: 0,
        hair: 0,
        face: 0,
        upper: 0,
        bra: 0,
        mid: 0,
        legs: 0,
        underwear: 0,
        footwear: 0,
    },
    multipliers : {
        base: 0,
        skirtLength: 0,
        heelHeight: 0,
        skirtSlit: 0,
        hair: 0,
        face: 0,
        upper: 0,
        bra: 0,
        mid: 0,
        legs: 0,
        underwear: 0,
        footwear: 0,
    }   
}


// Entry point
document.addEventListener("DOMContentLoaded", () => {
    // Initialize range inputs
    initRange("heelHeight", "heelHeightValue");
    initRange("skirtLength", "skirtLengthValue");

    // Unified handler for recalculation and field states
    const updateAll = () => {

        // Instantate Outfit and achievement objects using form data (global for now) 
        const currentForm = outfitForm();
        const outfit = new Outfit(currentForm);

        const basePts = outfit.getPoints();

        const achievements = [
            evaluateAchievement(sexySecretaryAchievement, outfit),
            evaluateAchievement(superbLegsAchievement, outfit)
        ];

        console.log(sumBonus(skirtSlit, achievements));

        const fields = ["hair", "face", "upper", "bra", "mid", "underwear", "legs", "footwear", "heelHeight", "skirtLength", "skirtSlit"];

        // Update base points column        
        document.querySelector("#totals .hair .base").innerHTML = basePts.hair;
        document.querySelector("#totals .face .base").innerHTML = basePts.face;
        document.querySelector("#totals .upper .base").innerHTML = basePts.upper;
        document.querySelector("#totals .bra .base").innerHTML = basePts.bra;
        document.querySelector("#totals .mid .base").innerHTML = basePts.mid;
        document.querySelector("#totals .underwear .base").innerHTML = basePts.underwear;
        document.querySelector("#totals .legs .base").innerHTML = basePts.legs; 
        document.querySelector("#totals .footwear .base").innerHTML = basePts.footwear;
        document.querySelector("#totals .heelHeight .base").innerHTML = basePts.heelHeight;
        document.querySelector("#totals .skirtLength .base").innerHTML = basePts.skirtLength;
        document.querySelector("#totals .skirtSlit .base").innerHTML = basePts.skirtSlit;
        

        
        fields.forEach(field => {
            const bonusEl = document.querySelector(`#totals .${field} .bonus`);
            const multiplierEl = document.querySelector(`#totals .${field} .multipliers`);

            // Combine bonuses from all achievements
            bonusEl.innerHTML = achievements
                .map(ach => getAchievementBonusHTML(ach, field))
                .filter(Boolean)
                .join("<br>");
            
            // Combine multipliers from all achievements
            multiplierEl.innerHTML = achievements
                .map(ach => getAchievementMultiplierHTML(ach, field))
                .filter(Boolean)
                .join("<br>");
            }
        );

        // Update UI
        updateEditorUI(outfit, currentForm);
    };

    // Listen for both input and change events
    ["input", "change"].forEach(event =>
        document.getElementById("outfit").addEventListener(event, updateAll)
    );

    // Initial calculation on page load
    updateAll();
});

function sumBonus(outfitProperty, achievements){

    const totalBonus = {
        flatAdds: 0,
        multipliers: 0
    }

    achievements.forEach(ach => {

        const bonus = ach.bonus;
        if (!bonus) return;
        console.log(ach.bonus);

       if (bonus.flatAdds && outfitProperty in bonus.flatAdds) {
            totalBonus.flatAdds[outfitProperty] = 
                (totalBonus.flatAdds[outfitProperty] || 0) + bonus.flatAdds[outfitProperty];
        }

        if (bonus.multipliers && outfitProperty in bonus.multipliers) {
            totalBonus.multipliers[outfitProperty] = 
                (totalBonus.multipliers[outfitProperty] || 0) + bonus.multipliers[outfitProperty];
        }

    });
    return totalBonus;
}


function getAchievementBonusHTML(achievement, property){
    return (achievement.level && achievement.bonus.flatAdds[property]) 
        ? "<small>" + achievement.name + " (lvl." + achievement.level + "): + " + achievement.bonus.flatAdds[property] + "</small>"
        : "";
}

function getAchievementMultiplierHTML(achievement, property){    
    return (achievement.level && achievement.bonus.multipliers[property]) 
        ? "<small>" + achievement.name + " (lvl." + achievement.level + "): x " + achievement.bonus.multipliers[property] + "</small>"
        : "";
}

// Generic function to evaluate an achievement
function evaluateAchievement(achievement, outfitState) {
    // Find the highest level for which the check passes
    const awardedLevel = achievement.levels
        .slice()
        .reverse()
        .find(level => level.check(outfitState));

    if (!awardedLevel) return { awarded: false };
    
    return {
        awarded: true,
        name: achievement.name,
        bonus: normalizeAchievementBonus(awardedLevel.bonus),
        level: awardedLevel.lvl,
        description: awardedLevel.description,
        icon: awardedLevel.icon,
    };
}

function normalizeAchievementBonus(bonus) {
    const normalized = {
        flatAdds: { ...BONUS_TEMPLATE.flatAdds },
        multipliers: { ...BONUS_TEMPLATE.multipliers }
    };

    if (bonus?.flatAdds) {
        for (const [key, value] of Object.entries(bonus.flatAdds)) {
            if (key in normalized.flatAdds) normalized.flatAdds[key] = value;
        }
    }

    if (bonus?.multipliers) {
        for (const [key, value] of Object.entries(bonus.multipliers)) {
            if (key in normalized.multipliers) normalized.multipliers[key] = value;
        }
    }

    return normalized;
}

/* Handle field states depending on current selection */
function updateEditorUI(formState) {    
       
    getById("heelHeight").disabled = !formState.has.heels;
    getById("skirtLength").disabled = !formState.has.miniSkirt;
    getById("skirtSlit").disabled = !formState.has.miniSkirt;
}