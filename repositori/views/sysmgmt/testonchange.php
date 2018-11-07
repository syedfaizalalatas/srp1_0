<!DOCTYPE html>
<html>
<head>
	<title></title>
	<!-- css here -->
</head>
<body>
	<select class="selectpicker" name="choixVarX" id="choixVarX" size="1" onChange="populateMap('#DDDDC2','#000000',listeComparaison);">
		<option value="Pop">Population</option>
		<option value="FoyersImposes">Nombre de foyers imposés</option>
		<option value="FoyersFiscaux">Nombre de foyers fiscaux</option>
		<option value="PropRetraite">Part des retraites dans les revenus</option>
		<option value="PropFoyersImposes">Proportion de foyers imposés</option>
		<option value="RevMoyen">Revenu moyen</option>
	</select>
	<select class="selectpicker" multiple name="parametresX[]" id="parametresX" size="8" data-width="150px" onChange="populateMap('#DDDDC2','#000000',listeComparaison);" data-max-options="3">
		<optgroup label="Centrage des données" data-max-options="1" id="centrageX">
			<option value="median">Médiane des communes</option>
			<option value="moyenne">Moyenne des communes</option>
			<option value="pasCentre">Ne pas centrer</option>
		</optgroup>
		<optgroup label="Dispersion" id="dispersionX" data-max-options="1">
			<option value="minmax">Min-Max</option>
			<option value="ecarttype">Ecart-type</option>
			<option value="noDispersion">Aucun</option>
		</optgroup>
		<optgroup label="Echelle" id="echelleX" data-max-options="1">
			<option value="echelleComplete">Echelle étendue</option>
			<option value="echelleAdaptee">Echelle réduite</option>
		</optgroup>
	</select>
<br>
---
<br>
	<select class="selectpicker" name="pilihanstatus" id="pilihanstatus" size="1" onChange="populateMap('#DDDDC2','#000000',listeComparaison);">
		<option value="masih">Masih Berkuatkuasa</option>
		<option value="mansuh">Dimansuhkan</option>
		<option value="serah">Diserahkan kepada</option>
		<option value="pinda">Dipinda kepada</option>
	</select>
	<p id="stattext" style="">koko</p>
	<div id="pilihanstatusdok">
		<!-- mansuh -->
		<div class="form-group statusdok" id="divmansuh" hidden>
			<label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span class="required">*</span></label>
			<div class="col-md-4 col-sm-4 col-xs-7">
				<input value="<?php echo $_SESSION['tarikh_wujud']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh" required class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy">
				<span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
			</div>
		</div>
		<!-- serah -->
		<div class="form-group statusdok" id="divserah" hidden>
			<label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="kod_jab_asal">Agensi Asal <span class="required">*</span>
			</label>
			<div class="col-md-4 col-sm-4 col-xs-7">
				<select class="form-control" id="kod_jab_asal" name="kod_jab_asal" required="required">
					<option value="1">Sila pilih...</option>
				</select>
			</div>
			<label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="kod_jab_baharu">Agensi Baharu <span class="required">*</span>
			</label>
			<div class="col-md-4 col-sm-4 col-xs-7">
				<select class="form-control" id="kod_jab_baharu" name="kod_jab_baharu" required="required">
					<option value="1">Sila pilih...</option>
				</select>
			</div>
		</div>
		<!-- pinda -->
		<div class="form-group statusdok" id="divpinda" hidden>
			<label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_asal">Tajuk Asal <span class="required">*</span>
			</label>
			<div class="col-md-4 col-sm-4 col-xs-7">
				<input value="<?php echo $_SESSION['bil_dok']; ?>" type="text" id="tajuk_asal" name="tajuk_asal" required class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}">
			</div>
			<label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_baharu">Tajuk Baharu <span class="required">*</span>
			</label>
			<div class="col-md-4 col-sm-4 col-xs-7">
				<input value="<?php echo $_SESSION['bil_dok']; ?>" type="text" id="tajuk_baharu" name="tajuk_baharu" required class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}">
			</div>
		</div>

	</div>



	<!-- javascript here -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
	<script>
		$('#choixVarX').on('change', function () {
		    switch ($(this).val()) {
		        case 'Pop':
		        case 'FoyersImposes':
		        case 'FoyersFiscaux':
		            $('#centrageX option[value="median"]').prop('selected', false);
		            $('#centrageX option[value="moyenne"]').prop('selected', false);
		            $('#centrageX option[value="pasCentre"]').prop('selected', true);
		            $('#dispersionX option[value="minmax"]').prop('selected', false);
		            $('#dispersionX option[value="ecarttype"]').prop('selected', false);
		            $('#dispersionX option[value="noDispersion"]').prop('selected', true);
		            $('#centrageX').prop('hidden', true);
		            $('#dispersionX').prop('hidden', true);
		            $('.selectpicker').selectpicker('refresh');
		            break;    
		        case 'PropRetraite':
		        case 'PropFoyersImposes':
		        case 'RevMoyen':
		            $('#centrageX').prop('hidden', false);
		            $('#dispersionX').prop('hidden', false);
		            $('.selectpicker').selectpicker('refresh');
		            break;
		    }
		});	
		$('#pilihanstatus').on('change', function () {
		    switch ($(this).val()) {
		        case 'mansuh':
		            $('#divmansuh').prop('hidden', false);
		            $('#divserah').prop('hidden', true);
		            $('#divpinda').prop('hidden', true);
		            var x = document.getElementById("pilihanstatus").value;
					document.getElementById("stattext").innerHTML = "You selected: " + x;
		            break;
		        case 'serah':
		            $('#divmansuh').prop('hidden', true);
		            $('#divserah').prop('hidden', false);
		            $('#divpinda').prop('hidden', true);
		            var x = document.getElementById("pilihanstatus").value;
					document.getElementById("stattext").innerHTML = "You selected: " + x;
		            break;
		        case 'pinda':
		            $('#divmansuh').prop('hidden', true);
		            $('#divserah').prop('hidden', true);
		            $('#divpinda').prop('hidden', false);
		            var x = document.getElementById("pilihanstatus").value;
					document.getElementById("stattext").innerHTML = "You selected: " + x;
		            break;
		    }
		});	
	</script>    
</body>
</html>