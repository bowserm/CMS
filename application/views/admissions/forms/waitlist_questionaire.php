<div class="formBox">
	<form id='studWaitlistQuestionaire'  method='post' accept-charset='UTF-8' class='clearfix'>
		<fieldset>
			<legend>
				Child's Name:
			</legend> 
			<input type="text" name="cFirstName" id="cFirstId" max="50" placeholder="First Name"/>
			<input type="text" name="cMiddleName" id="cMiddleId" max="50" placeholder="Middle Name" />
			<input type="text" name="cLastName" id="cLastId" max="50" placeholder="Last Name" /> </br> </br>
		</fieldset>
		<fieldset>
			<legend>
				Waitlist Questionaire:
			</legend> 
			<?php $i = 0; ?>
			<?php foreach($wlQuestions as $q): ?>
				<?php
					// print question
					$attributes = $q->attributes();
					echo $attributes['questiontext'] . '</br>';
				?>
				
				<!--Question Textarea-->
				<textarea name="<?php echo("q" . $i . "answer"); ?>" id="<?php echo ("q" . $i . "ID"); ?>" placeholder="Enter Answer Here..." cols="100" rows="5" max="250" ></textarea></br>
				
			<? $i++; ?>
			<?php endforeach; ?>
			
		</fieldset>
		
			<input type="submit" value="Save and Continue" name="waitlist_questionaire" class="submit"/>
	</form>
</div>
