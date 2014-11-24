function getRadioValue (group) {
	for (i=0; i<group.length; i++) {
		  if (group[i].checked == true) {
			  return (group[i].value);
		  }
	}
	return false;
}
/*function validate_user_form (theForm) {
	var error = "Cannot submit the form:\n";
	var res = true;
	
	if (!getRadioValue (theForm.l_name)) {
		error += "\tNo value entered for question 1\n";
		res = false;
	}
	if (!getRadioValue (theForm.l_id)) {
		error += "\tNo value entered for question 3\n";
		res = false;
	}
	if (!getRadioValue (theForm.l_email)) {
		error += "\tNo value entered for question 4\n";
		res = false;
	}
	if (!getRadioValue (theForm.l_prg)) {
		error += "\tNo value entered for question 5\n";
		res = false;
	}
	if (!getRadioValue (theForm.q6)) {
		error += "\tNo value entered for question 6\n";
		res = false;
	}
	if (!getRadioValue (theForm.q7)) {
		error += "\tNo value entered for question 7\n";
		res = false;
	}
	
}*/
function validate_learner (theForm) {
	var error = "Cannot submit the form:\n";
	var res = true;
	
	if (!getRadioValue (theForm.q1)) {
		error += "\tNo value entered for question 1\n";
		res = false;
	}
	if (!getRadioValue (theForm.q2)) {
		error += "\tNo value entered for question 2\n";
		res = false;
	}
	if (!getRadioValue (theForm.q4)) {
		error += "\tNo value entered for question 4\n";
		res = false;
	}
	if (!getRadioValue (theForm.q5)) {
		error += "\tNo value entered for question 5\n";
		res = false;
	}
	if (!getRadioValue (theForm.q6)) {
		error += "\tNo value entered for question 6\n";
		res = false;
	}
	if (!getRadioValue (theForm.q7)) {
		error += "\tNo value entered for question 7\n";
		res = false;
	}
	if (!getRadioValue (theForm.q8)) {
		error += "\tNo value entered for question 8\n";
		res = false;
	}
	
	/* if (!theForm.ft_checkbox.checked && !theForm.pt_checkbox.checked) {
	 	error += "\tNo prospectus selected!\n";
	 	res = false;
	}*/
	
	if (!res) {
		alert (error);
	} else {
		res = true;//confirm ("Are you sure you wish to complete this activity?\nClick 'Cancel' to review your answers.");
	}
	return res;
}