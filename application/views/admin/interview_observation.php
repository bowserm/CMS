<script type="text/javascript">
function show_hide_page_one(value) {
	if(value == 'adInRadio') {
		document.getElementById('adInTextDiv').style.display = "block";
		document.getElementById('adInLabel').style.display = "block";
		document.getElementById('otherTextDiv').style.display = "none";
		document.getElementById('otherLabel').style.display = "none";
	}
	else if(value == 'otherRadio')
	{
		document.getElementById('adInTextDiv').style.display = "none";
		document.getElementById('adInLabel').style.display = "none";
		document.getElementById('otherTextDiv').style.display = "block";
		document.getElementById('otherLabel').style.display = "block";
	}
	else if (value == 'other') {
		document.getElementById('adInTextDiv').style.display = "none";
		document.getElementById('adInLabel').style.display = "none";
		document.getElementById('otherTextDiv').style.display = "none";
		document.getElementById('otherLabel').style.display = "none";
	}
}
</script>
<div class="formBox">
	<form id='parentInterviewObservationForm' method="post" accept-charset='UTF-8' class="clearfix">
		
        <fieldset>
            <legend>
                Contact Information
            </legend>
            <ul>
                <li>
                    <label>Parent's Names:</label>
                    <input type='text' name='pFirstName' id='pFirstId' placeholder="First Name" max='50' />
                    <input type="text" name='pLastName' id="pLastId" placeholder="Last Name" max='50' />
                    </br>
                </li>
                <li>
                    <label>Children names/ages:</label>
                    <textarea name="namesAndAges" id="namesAndAgesId"
                    	placeholder="Names and ages here..." cols="100" rows="5" max="250" ><?php
							echo set_value('namesAndAges'); ?></textarea></br>
                    </br>
                </li>
                <li>
                    <label>Date Of Birth:</label>
                    <input type='text' name='dobName' id='dobId' max='15' placeholder="01/01/2001"/>
                    </br>
                </li>
                <li>
                    <label>Date first contacted:</label>
                    <input type='text' name='contactDateName' id='contactDateId' max='15' placeholder="01/01/2001"/>
                    </br>
                </li>
                <li>
                    <label>Phone Number:</label>
                    <input type='text' name='phoneName' id='phoneId' max='15' placeholder="555-555-5555"/>
                    </br>
                </li>
                <li>
                    <label>Visit Date:</label>
                    <input type='text' name='visitDateName' id='visitDateId' max='15' placeholder="01/01/2001"/>
                    </br>
                </li>
                <li>
                    <label>Email:</label>
                    <input type='text' name='emailName' id='emailId' max='50' placeholder="email@address.com"/>
                    </br>
                </li>
                <li>
                    How did you hear about CMS?</br>
                    <div class="radioButtons">
                        <input type='radio' name='learnedAboutName' id='webSearchId' onClick="showhide('other')"/>
                        Web Search
                        <input type='radio' name='learnedAboutName' id='cmsFamilyId' onClick="showhide('other')"/>
                        CMS Family
                        <input type='radio' name='learnedAboutName' id='friendsId' onClick="showhide('other')"/>
                        Friends
                        <input type='radio' name='learnedAboutName' id='adInRadioId' onClick="showhide('adInRadio')"/>
                        Ad In
                        <input type='radio' name='learnedAboutName' id='otherRadioId' onClick="showhide('otherRadio')"/>
                        Other
                        <div id="adInTextDiv" style="display:none">
                            <label for="adIn" id="adInLabelId" style="display:none">Ad In:</label>
                            <input type='text' name='adInName' id='adInId' max='100' placeholder="Where did you hear about the add?"/>
                        </div>
                        <div id="otherTextDiv" style="display:none" >
                            <label for="other" id="otherLabelId" style="display:none">Other:</label>
                            <input type='text' name='otherName' id='otherId' max='100' placeholder="Where did you hear about us?"/>
                        </div>
                    </div>
                </li>
            </ul>
        </fieldset>
	
		<fieldset>
			
			Level of Interest
			<div class="radioButtons">
			<input type="radio" name="interestName" id="interestLowId" /> Low
			<input type="radio" name="interestName" id="interestMediumId" /> Medium
			<input type="radio" name="interestName" id="interestHighId" /> High </br>
			</div>
			
			Understanding of Montessori
			<div class="radioButtons">
			<input type="radio" name="understandingName" id="understandingLowId" /> Little
			<input type="radio" name="understandingName" id="understandingMediumId" /> Average
			<input type="radio" name="understandingName" id="understandingHighId" /> High </br>
			</div>
			
			Willingness to learn more
			<div class="radioButtons">
			<input type="radio" name="willingnessName" id="willingnessLowId" /> Low
			<input type="radio" name="willingnessName" id="willingnessMediumId" /> Medium
			<input type="radio" name="willingnessName" id="willingnessHighId" /> High </br>
			</div>
			
			<ul>
			    <li>
    			    <label>If Moving: City/St:</label>	
    				<input type="text" name="movingCityName" id="movingCityId" max="50" placeholder="city"/> 
    				<input type="text" name="movingStateName" id="movingStateId" max="50" placeholder="state"/> </br>
				</li>
			    <li>
                    <label>School:</label>	
				<input type="text" name="movingSchoolName" id="movingSchoolId" max="100" /> </br>
				</li>
	        </ul>
			
	     	Notes:
	     	<div class="radioButtons"> 
			<input type="checkbox" name="learningNotesName" id="inDepthId" /> In Depth Learning
			<input type="checkbox" name="learningNotesName" id="ownPaceId" /> At Own Pace
			<input type="checkbox" name="learningNotesName" id="handsOnId" /> Hands On
			<input type="checkbox" name="learningNotesName" id="mixedAgesId" /> Mixed Ages </br>
	        </div>
			
			Montessori impressions: </br>
				<textarea name="montessoriImpressionsName" id="montessoriImpressionsId" cols="100" rows="2" max="1000" 
					placeholder="Montessori Impressions..."></textarea></br>
			Interviews impressions: </br>
				<textarea name="interviewImpressionsName" id="interviewsImpressionsId" cols="100" rows="4" max="3000"
					placeholder="Interviewer Impressions..."></textarea></br>
						
		</fieldset>
		
		<fieldset>
			Classroom Observation: </br>
			Date:
				<input type="text" name="observationDateName" id="observationDateId" max="15" placeholder="01/01/2001"/> </br>
			Class:
				<input type="text" name="classroomName" id="classId" max="50" placeholder="Name of Classroom"/> </br>
			Attended:
				<input type="radio" name="attendedRadioName" id="attendedRadioId" /> Yes
				<input type="radio" name="attendedRadioName" id="attendedRadioId" /> No </br>
			On Time:
				<input type="radio" name="onTimeRadioName" id="onTimeRadioId" /> Yes
				<input type="radio" name="onTimeRadioName" id="onTimeRadioId" /> No </br>
			Interview Date: 
				<input type="text" name="interviewDateName" id="interviewDateId" max="15" placeholder="01/01/2001"/> </br>
		</fieldset>
		
		<fieldset>
			Date Application Received:
				<input type="text" name="appReceivedName" id="appReceivedId" max="15" placeholder="01/01/2001"/> </br>
			Date Application Fee Received:
				<input type="text" name="feeReceivedName" id="feeReceivedId" max="15" placeholder="01/01/2001"/> </br>
		</fieldset>
		
			<input type="submit" value="Save and Continue" name="interviewObservationForm" class="submit"/>
	</form>
</div>
