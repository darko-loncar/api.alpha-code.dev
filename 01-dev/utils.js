/**
 * Simplify tedious getting by id 
 * @param {*} elementId 
 * @returns 
 */
function getById(elementId){
    return document.getElementById(elementId)
}

/**
 * Simplify getting selected option from <select> elements
 * @param selectElement
 * @returns 
 */
function getSelectedOption(selectElement){   
    return selectElement.options[selectElement.selectedIndex];    
    return selected;
    //return selected ? parseInt(selected.value, 10) : null;
}

/*
 * Add output element for range sliders 
 */
function initRange(rangeId, valueOutputId){

    const rangeInput = document.getElementById(rangeId);
    const valueOutput = document.getElementById(valueOutputId);

    // Check if the range input exists
    if (!rangeInput) return;
    if (!valueOutput) return;

    // Set the initial value in the range input and display it
    valueOutput.textContent = rangeInput.value;

    rangeInput.addEventListener("input", () => {
        valueOutput.textContent = rangeInput.value;
    });
}


/**
 * Returns selected radio group option
 * @param {*} radioGroup 
 * @returns int
 */
function getSelectedRadio(radioGroup) {
    const selected = Array.from(radioGroup).find(radio => radio.checked);
    return selected ? parseInt(selected.value, 10) : null;
}
