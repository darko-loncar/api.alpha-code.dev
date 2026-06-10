<?php $this->includeTemplatePart("Header"); ?>

<section role="region">
    <h2>Radni nalozi</h2>

        <div class="input-container">
            <label for="work-items">Odabir stavke</label>
            <?= $workItemsSelect ?>
        </div>
    <div class="input-container">
        <button id="addItem" class="inline">Dodaj stavku</button>
    </div>


    <table id="itemTable">
    <thead>
        <tr>
            <th>#</th>
            <th>Stavka</th>
            <th>Jed. mj.</th>
            <th>Vrijednost</th>
            <th>Količina</th>
            <th>Ukupno</th>
            <th>Akcija</th>
        </tr>
    </thead>
    <tbody>
        <!-- Rows will be added here -->
    </tbody>
    </table>

</section>

<aside role="complementary">
    
    <div class="input-container">
        <input type="submit" value="Spremi Radni nalog">
    </div>

    <h3>Odabir outfita</h3>
    <div class="input-item">
        <p>Odabirom outifta, bodovi iz njega biti će pribrojeni ukupnoj vrijednosti radnog naloga.</p>
        <button id="addItem" class="inline">Dodaj outfit</button>
    </div>



    <h3>Upute i savjeti za popunjavanje radnog naloga</h3>
    <p>Dodajte stavke na popis na način da ih prvo odaberete i zatim kliknete gumb "Dodaj Stavku". Na popisu možete uređivati količini po svakoj stavci.</p>
    <p>Po završetku uređivanja kliknite na gumb "Spremi radni nalog". Dobro provjerite sve stavke jer se radni nalog nakon spremanja ne može naknadno uređivati</p>
    <p>Uz radni nalog preporučljivo je odabrati i seksi outfit s obzirom da na taj način ostvarujete ekstra bodove po svakom radnom nalogu!!!!!</p>
    
    <h3>Uvijeti i obveze</h3>
    <p><small class="almost-hidden">Klikom na "Spremi radni nalog"  Radni nalog se šalje u elektroničkom obliku putem maila uz privitak .pdf datoteke na adrese izvršitelja i primatelja usluge. Radni nalog se mora izvršiti najkasnije u roku od 37 (slovima "tridesetisedam" dana). Ukoliko u navedenom roku ne dođe do ispunjavanja obveza iz radnog naloga, na dnevnoj bazi se računaju negativni bodovi. Ukoliko ni nakon 143 (slovima "stočetrdesititri") dana ne dođe do ispunjenja obveza po radnom nalogu, radni nalog ulazi u postupak razrješenja gdje će sukobljene stranke načelno pokušati postići dogovor češkanjem.</small></p>
</aside>

<script>
document.getElementById("addItem").addEventListener("click", function (event) {
    event.preventDefault(); // Prevent form submission
    
    let table = document.getElementById("itemTable").getElementsByTagName('tbody')[0];
    let selectElement = document.getElementById("workItemSelect");

    // Get the selected option text
    let selectedText = selectElement.options[selectElement.selectedIndex].text;

    // Check if the user has selected a valid item (assuming "not chosen" has an empty value or specific placeholder value)
    let selectedValue = selectElement.value;
    if (selectedValue === "" || selectedValue === "null") {
        alert("Molimo odaberite stavku prije dodavanja.");
        return;
    }

    // Check if the item is already added by comparing the hidden input value
    let existingItems = document.querySelectorAll("#itemTable tbody input[name='item-id[]']"); // Correct selector
    for (let item of existingItems) {
        if (item.value === selectedValue) {
            alert("Ova stavka je već dodana.");
            return;
        }
    }

    // Create a new row
    let newRow = table.insertRow(table.rows.length - 1);  // Insert before the last row (total row)

    // Add columns (number, item text, quantity, action)
    let cellNumber = newRow.insertCell(0);
    let cellItem = newRow.insertCell(1);
    let cellUnit = newRow.insertCell(2);
    let cellBaseValue = newRow.insertCell(3);
    let cellQuantity = newRow.insertCell(4);
    let cellCalculatedValue = newRow.insertCell(5);
    let cellAction = newRow.insertCell(6);

    // Auto-increment row numbers
    cellNumber.innerText = table.rows.length;

    // Add selected item text to the row
    cellItem.innerText = selectedText;

    // Add hidden input to store item value
    let hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = "item-id[]";
    hiddenInput.value = selectElement.value;
    cellItem.appendChild(hiddenInput);

    // Add unit
    cellUnit.innerText = selectElement.options[selectElement.selectedIndex].dataset.unit;

    // Add unit value text
    cellBaseValue.innerText = selectElement.options[selectElement.selectedIndex].dataset.value;

    // Add input for quantity
    let quantityInput = document.createElement("input");
    quantityInput.type = "number";
    quantityInput.name = "quantities[]";
    quantityInput.classList.add("inline");
    quantityInput.min = 1;
    quantityInput.value = 1;
    quantityInput.addEventListener("input", function () {
        // Update calculated value when quantity is changed
        let calculatedValue = parseFloat(cellBaseValue.innerText) * parseFloat(quantityInput.value);
        cellCalculatedValue.innerText = calculatedValue.toFixed(2);

        // Update the total value
        updateTotalValue();
    });
    cellQuantity.appendChild(quantityInput);

    // Add calculated value column
    let calculatedValue = parseFloat(cellBaseValue.innerText) * parseFloat(quantityInput.value);
    cellCalculatedValue.innerText = calculatedValue.toFixed(2);

    // Add remove button
    let removeButton = document.createElement("button");
    removeButton.innerText = "Remove";
    removeButton.type = "button";  // Prevent default form submission
    removeButton.classList.add("inline");
    removeButton.onclick = function () {
        table.deleteRow(newRow.rowIndex - 1);
        updateRowNumbers();
        updateTotalValue();
    };
    cellAction.appendChild(removeButton);

    // Ensure total row is at the bottom
    updateTotalValue();
});

// Function to update row numbers when an item is removed
function updateRowNumbers() {
    let rows = document.querySelectorAll("#itemTable tbody tr");
    rows.forEach((row, index) => {
        row.cells[0].innerText = index + 1;
    });
}

// Function to update the total value
function updateTotalValue() {
    let rows = document.querySelectorAll("#itemTable tbody tr");
    let total = 0;

    rows.forEach((row) => {
        // Ensure the row has enough cells (at least 6, including the calculated value)
        if (row.cells.length > 5) {
            let calculatedValue = parseFloat(row.cells[5].innerText);
            if (!isNaN(calculatedValue)) {
                total += calculatedValue;
            }
        }
    });

    // Check if the "total" row exists, if not create it
    let totalRow = document.getElementById("totalRow");
    if (!totalRow) {
        totalRow = document.createElement("tr");
        totalRow.id = "totalRow";
        let totalCell = totalRow.insertCell(0);
        totalCell.colSpan = 5;
        totalCell.innerText = "Ukupna vrijednost:";
        let totalValueCell = totalRow.insertCell(1);
        totalValueCell.innerText = total.toFixed(2);
        totalValueCell.colSpan = 2;
        document.getElementById("itemTable").getElementsByTagName('tbody')[0].appendChild(totalRow);
    } else {
        totalRow.cells[1].innerText = total.toFixed(2);
    }
}

</script>

<?php $this->includeTemplatePart("Footer"); ?>
