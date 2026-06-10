<?php $this->includeTemplatePart("Header"); ?>

<section role="region">
    <h2><?= $pageTitle ?></h2>

    <form id="outfit" action="<?= $formAction ?>" method="post">

    	<input type="hidden" name="id" value="<?php echo $outfit->getId() ?>">

        <div class="input-container">
            <label for="outfit_title">Naziv Outfita</label>
            <input type="text" id="name" name="name" required placeholder="Naziv outfita npr. Seksi knjižničarka - novogodišnje izdanje" value="<?php echo $outfit->name ?>">
        </div>

        <h3>Odabir odjeće</h3>

        <div class="input-container">
            <label for="hair">Vlasište</label><?= $hairSelect ?>
        </div>
        <div class="input-container">
            <label for="face">Faca</label><?= $faceSelect ?>
        </div>
        <div class="input-container">
            <label for="upper">Gornji dio</label><?= $upperSelect ?>
        </div>
        <div class="input-container">
            <label for="bra">Grudnjak</label><?= $braSelect ?>
        </div>
        <div class="input-container">
            <label for="mid">Srednji dio</label><?= $midSelect ?>
        </div>
        <div class="input-container">
            <label for="between-legs">Međunožje</label><?= $betweenLegsSelect ?>
        </div>
        <div class="input-container">
            <label for="legs">Noge</label><?= $legsSelect ?>
        </div>
        <div class="input-container">
            <label for="footwear">Obuća</label><?= $footwearSelect ?>
        </div>

        <h3>Dodatna prilagodba</h3>

        <div class="input-container">
            <label>Visina štikli/čizmi (8-22cm): <span id="heelHeightValue"></span>cm. <span id="heelHeightBonus"></span></label><br>
            <input id="heelHeight" width="400" name="heel-height" type="range" value="<?php echo ($outfit->heel_height) ?>" min="8" max="22">
        </div>

        <div class="input-container">
            <label>Duljina mini suknje (10-50cm): <span id="skirtLengthValue"></span>cm. <span id="skirtLengthBonus"></span></label><br>
            <input id="skirtLength" name="skirt-length" type="range" value="<?php echo ($outfit->skirt_length) ?>" min="10" max="50">
        </div>

        <div class="input-container">
            <label>Prorez na mini suknji (+100)<span id="skirtSlitBonus"></span></label><br>
            <input id="skirtSlit" name="skirt-slit" type="checkbox" <?php echo ($outfit->skirt_slit) ? "checked" : ""; ?> >
        </div>

        <div class="input-container">
            <fieldset>
                <legend>Odabir naočala</legend>
                <label>
                Ništa <input type="radio" name="glasses" <?php echo ($outfit->glasses === 0) ? "checked" : ""; ?> value="0">
                </label>
                <label>
                Dioptrijske <input type="radio" name="glasses" <?php echo ($outfit->glasses === 1) ? "checked" : ""; ?> value="1">
                </label>
                <label>
                Sunčane <input type="radio" name="glasses" <?php echo ($outfit->glasses === 2) ? "checked" : ""; ?> value="2"> 
                </label>
            </fieldset>
        </div>
        <div class="input-container">
            <input type="submit" value="Spremi Outfit">
        </div>

        <input type="hidden" name="value" id="grandTotalValue" value="0">

    </form>
</section>

<aside role="complementary">
    
    <h3>Bodovi</h3>

    <table id="totals">
        <tr><th>#</th><th>Base Pts</th><th>+</th><th>-</th><th>Total</th></tr>
        <tr><td>Izbor Outfita</td><td id="base-points"></td><td id="base-points-bonus"></td><td id="base-points-malus"></td><td id="base-points-total"></td></tr>
        <tr><td>Prilagodbe</td><td id="adjustment-points"></td><td id="adjustment-points-bonus"></td><td id="adjustment-points-malus"></td><td id="adjustment-points-total"></td></tr>
        <tr><td>Ukupno</td><td id="total-points"></td><td id="total-points-bonus"></td><td id="total-points-malus"></td><td id="grand-total-points"></td></tr>

    </table>

    <h3>Achievement bonuses</h3>
    
    <div class="achievements">
    <div class="achievement" data-id="sexySecretary">
        <img class="achievement-icon" src="gfx/icon-of-style-1.png" width="48" height="48">
        <div class="description">
        <h4 class="achievement-name"></h4>
        <small class="achievement-desc"></small>
        </div>
    </div>

    <div class="achievement" data-id="visiblePussy">
        <img class="achievement-icon" src="gfx/foxy-lady.png" width="48" height="48">
        <div class="description">
        <h4 class="achievement-name"></h4>
        <small class="achievement-desc"></small>
        </div>
    </div>

    <div class="achievement" data-id="invisibleLegs">
        <img class="achievement-icon" src="gfx/foxy-lady.png" width="48" height="48">
        <div class="description">
        <h4 class="achievement-name"></h4>
        <small class="achievement-desc"></small>
        </div>
    </div>


    <!-- add more, no JS changes needed -->
    </div>
    <h3>Penalizacije</h3>
    <div class="achievements negative">
        <div loading="lazy" id="point-of-inaccessibility" class="achievement">
            <img src="<?= url("gfx/lock.png") ?>" width="48" height="48">
            <div class="description">
                <h4>Point of inaccessibility! (-40%)</h4>
                <small>Fizički pristup vašem međunožju je nemoguć!!!! Da se bar može vidjeti, ali ne može ni to... Vaš outfit pati sa <strong style>-40%</strong> negativnog utjecaja na osnovni izbor</small>
            </div>
        </div>
        <div loading="lazy" id="crazy-cat-lady" class="achievement">
            <img src="<?= url("gfx/crazy-cat.png") ?>" width="48" height="48">
            <div class="description">
                <h4>Crazy Cat Lady! (-400pts)</h4>
                <small>Malo smo se zapustili? Raščupana bijela kosa, kučni ogrtać, prištevi, brkovi... Hoardate kutije i bilježnice! Muž vas je ostavio ako ste ga ikada i imali! Rodbina vas izbjegava, ali mačke su uvijek tu uz vas.... do kraja. :(  <strong style>-400pts</strong></small>
            </div>
        </div>
        <div loading="lazy" id="non-visible-legs" class="achievement">
            <img src="<?= url("gfx/crazy-cat.png") ?>" width="48" height="48">
            <div class="description">
                <h4>Legs not visible (-1000pts)</h4>
                <small>Noge se ne vide.... neprihvatljivo...<strong style>-1000pts</strong></small>
            </div>
        </div>

    </div>
</aside>

<?php $this->includeTemplatePart("Footer"); ?>
