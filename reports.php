<?php

ini_set ("max_execution_time", 	500);
ini_set ("max_input_time", 500);

include_once ('./config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/lib/stats.php');
include_once ($CFG->apploc  . '/classes/datagrid.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/lib/roles.php');
include_once ($CFG->apploc  . '/lib/funcs.php');


//strings code !
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('question', 'general', 'interventions', 'reports', 'user'));

Sessions::checkUserLogIn ();

 
if (has_capability(Sessions::getID(), 'reports:view_collated_stats')) {
	// check if this user has access to the selected provider's data. throw them out if not.
	if (isset ($_GET['provider'])) {
		if ($_GET['provider'] != 'Any') {
			has_access_to_provider (Sessions::getID (), $_GET['provider']);
		}
	}
} else {
	die (return_string ('ACCESS_DENIED'));
}

ob_start();

if (count($_GET) == 1 && isset ($_GET['r'])) {
	$myfirstrun = true;
}
	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo_string ('APP_NAME'); ?> : <?php echo_string ('REPORTS'); ?></title>
<link href="<?php echo ($CFG->cssfile); ?>?rnd=<?php echo rand (0, 1000); ?>" rel="stylesheet" type="text/css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="js/resizer.js"></script>
<script type="text/javascript" src="external/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="external/tablesorter/addons/pager/jquery.tablesorter.pager.js"></script>

<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/<?php echo $CFG->jquery_ui_version; ?>/themes/base/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/<?php echo $CFG->jquery_ui_version; ?>/jquery-ui.min.js" type="text/javascript"></script>

<link href="styles/dashboard/dashboard.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
  <link rel="stylesheet" type="text/css" href="styles/dashboard/dashboard_ie.css" />
<![endif]-->
<link href="external/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css" /> 


<script type="text/javascript">
  $(document).ready(function(){
  
  	// temporarily disabled hiding of Excel Version dialog.
	 $('#e').click (function () {	
		$('#excelversion').toggle ();
	});

	// check onload
	if ($('#e').is (':checked')) {
		$('#excelversion').show ('fast');
	} else {
		$('#excelversion').hide ('fast');
	}
	 
	$('#provider').change (function (e) {

		  
			// get centres/advisors given provider
			if ($(this).val () > 0) {
				
					$.ajax({
					  url: 'ajax/getdata.php',
					  type: "POST",
					  data: {anyrow: true, provider: $(this).val (), q:'centres'},
					  success: function(data) {
						  if (data != 'false') {
						  	$('#ctr').show ();
						  	$('#ctr_label').show ();
						  	$('#ctrtd').html (data);
						  } else {
							$('#ctr').hide ();
							$('#ctr_label').hide ();
							$('#ctrtd').html ('<select name="ctr" id="ctr"><option value="0"><?php echo_string ('COMBO_ALL'); ?></option>');
						  }
					  }
					});
			} else {
				// clear dropdown
				$('#ctr').hide ();
				$('#ctr_label').hide ();
				$('#ctrtd').html ('<select name="ctr" id="ctr"><option value="0"><?php echo_string ('COMBO_ALL'); ?></option>');
			}
	  });
		
	  $("#startdate").datepicker({ dateFormat: 'dd/mm/yy' });
	  $("#enddate").datepicker({ dateFormat: 'dd/mm/yy' });



	  
	  

			//.tablesorter({widthFixed: false, widgets: ['zebra']})
	 	$("#userdg")
			
			.tablesorter({widthFixed: false})
			.tablesorterPager({container: $("#pager"), size: <?php echo $CFG->defaultPaginationSize; ?>});
			


	 	<?php if ($_GET['assesstype'] == 'both') { ?>
	 	//TODO - rebind on change
	 		//$("#userdg td:contains('Advisor')").parent().addClass ('odd');
	 	<?php } ?>
		; //{ headers: { 3:{sorter: false}, 4:{sorter: false}}});

		
		$("#butt").click (function () {
			$("#userdg").toggle ("fast");
		});

    <?php 
        	if (!empty ($_GET['prg'])) {
    ?>

    $('#prg').val(<?php echo $_GET['prg']; ?>);

    <?php } 
    
    if (!empty ($_GET['gender'])) {
    ?>
	    $('#gender').val('<?php echo $_GET['gender']; ?>');

    <?php } 
    if (!empty ($_GET['ethnicity'])) {
    ?>

    $('#ethnicity').val(<?php echo $_GET['ethnicity']; ?>);

    <?php } 
    if (!empty ($_GET['agegroup'])) {
    ?>

    $('#agegroup').val(<?php echo $_GET['agegroup']; ?>);

    <?php } 
    if (!empty ($_GET['assesstype'])) {
    ?>

    $('#assesstype').val('<?php echo $_GET['assesstype']; ?>');

    <?php } else {

$_GET['assesstype'] = 'a';
}
    
    if (!empty ($_GET['ctr'])) {
    ?>
    
    $('#ctr').val('<?php echo $_GET['ctr']; ?>');
    
  <?php  } 
  
  	if (!empty ($_GET['startdate'])) {
  	
  ?>
  
  	$('#startdate').val('<?php echo $_GET['startdate']; ?>');
  <?php }
  
  if (!empty ($_GET['enddate'])) {
  
  ?>
  $('#enddate').val('<?php echo $_GET['enddate']; ?>');
  <?php } 
  	
  	if (!empty ($_GET['interventions'])) {
 ?>
 $('#interventions').val('<?php echo $_GET['interventions']; ?>');
 <?php } ?>

 <?php if (!empty ($_GET['archived'])) { ?>
 $('#archived').attr('checked','checked');
<?php } ?>

  });
</script>

</head>

<body class="reports">
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>

<div class="boxer">
<h2>Reports</h2>
<div class="annot">
<p>Use this screen to produce reports based on various criteria.</p>
</div>
</div>


<div class="boxer">

<h3>Filters</h3>   
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="prg" value="1" id="prg"/>
 
<table>
	
	
	<?php // if can select provider...
	if (has_capability(Sessions::getID(), 'providers:control_all') || has_capability(Sessions::getID(), 'providers:control_subcontractors')) {

// increase memory load - in case user is going to run a large query...
ini_set("memory_limit","160M");

	?>
	<tr>
	<td> <label for="provider"><?php echo_string ('PROVIDER'); ?>:</label></td>
	<td colspan="3">
		 <select name="provider" id="provider">
         
		<?php 
  
           
           

                                        if (has_capability(Sessions::getID(), 'providers:control_all')) {
                                        	$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID, visible from sb_providers  group by name ORDER BY visible desc, name asc', true);
                                        } else if (has_capability(Sessions::getID(), 'providers:control_subcontractors')) {
                                    		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID, visible from sb_providers where superproviderid=' . Sessions::getSuperProviderID (Sessions::getUserInfo ('providerid')) . ' group by name ORDER BY visible desc, name asc', true);
                                       	}
                            		?>
                            		<?php if (count ($providers) > 1) {?>
                            		<option value="Any">
                                        <?php echo 'Any'; ?>
                                    </option>
                                    <?php } ?>
                                    <?php 
                                       	$selected = $_GET['provider'];
                                       // if (!empty ($valid_ac_code)) {
                                       // 	$selected = $res['providerid'];
                                       // }	
                                        foreach ($providers as $provider) {
                                    ?>
                                    <option value="<?php echo $provider['ProviderID']; ?>" <?php if ($provider['ProviderID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $provider['name']; ?> <?php if (!$provider['visible']) { echo "(Archived)"; } ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
	</td>
	</tr>
	<?php } // end of providers capability check ?>
		
		<tr>
                                   <td>
                                <label for="gender"><?php echo_string ('GENDER'); ?>:</label>
                            </td> 
                            <td colspan="3">
                            <select name="gender" id="gender">
                            	<option value="0">
                            		<?php echo_string ('COMBO_ALL'); ?>
                            	</option>
                            	<option value="m"><?php echo_string ('MALE'); ?></option>
                            	<option value="f"><?php echo_string ('FEMALE'); ?></option>
                            </select>
                            
                              
                           
                                </td>
                                </tr>
                                <tr>
                                  <td>
                                <label for="ethnicity"><?php echo_string ('ETHNICITY'); ?>:</label>
                            </td>
                                <td colspan="3">
                                
                              <select name="ethnicity" id="ethnicity">
                                    <option value="0">
                                        <?php echo_string ('COMBO_ALL'); ?>
                                    </option>
                                    <?php 
                                       
                                        $ethns = DB::executeSelect('ethnicity');
                                        $selected = $_POST['ethnicity'];
                                        foreach ($ethns as $ethn) {
                                    ?>
                                    <option value="<?php echo $ethn['EthnicityID']; ?>" <?php if ($ethn['EthnicityID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $ethn['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                                </td>
                                </tr>
                                <tr>
                                 <td>
                                <label for="agegroup"><?php echo_string ('AGE_GROUP'); ?></label>
                            </td>
                            <td colspan="3">
                                <select name="agegroup" id="agegroup">
                                     <option value="0">
                                        <?php echo_string ('COMBO_ALL'); ?>
                                     </option>
                                    <?php 
                                        //TODO - getProviders func?
                                        $ages = DB::executeSelect('age_groups');
                                        $selected = $_POST['agegroup'];
                                        foreach ($ages as $age) {
                                    ?>
                                    <option value="<?php echo $age['AgeID']; ?>" <?php if ($age['AgeID'] == $selected) {?>selected="selected"<?php } ?>>
                                    	<?php print $age['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                            </td>
                            </tr>
                            
                            <tr>
                            <td>
			<label for="ctr"><?php echo_string ('CENTRE'); ?>:</label>
		</td>
		<td id="ctrtd" colspan="3">
			<select name="ctr" id="ctr">
                                    <option value="0">
                                       <?php echo_string ('COMBO_ALL'); ?>
                                    </option>
                                    <?php 
                                        
                                       
                                        if (isset ($_GET['provider'])) {
                                        	// check rights?
                                        	$ctrs = DB::executeSelect('centres', '*', array ('providerid'=>$_GET['provider']));
                                        } else {
                                        	$ctrs = DB::executeSelect('centres', '*', array ('providerid'=>Sessions::getUserInfo('providerid')));
                                        }                
										$selected = $_POST['ctr'];
                                        foreach ($ctrs as $ctr) {
                                    ?>
                                    <option value="<?php echo $ctr['CentreID']; ?>" <?php if ($ctr['CentreID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $ctr['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                                </td>
                                </tr>
                              
                            
                            <tr>
                            <td>
                            	<label for="assesstype"><?php echo_string ('ACTIVITY_TYPE'); ?>:</label>
                            </td>
                             <td colspan="3">
                               <select name="assesstype" id="assesstype">
                                     <option value="a"><?php echo_string ('ADVISOR'); ?></option>
                                     <option value="l"><?php echo_string ('LEARNER'); ?></option>
                                     <option value="both">Both</option>
                                </select>
                           
                                </td>
                                </tr>
                                
                                <tr>
                                	<td>
                                		<label for="interventions"><?php echo_string ('IV'); ?>:</label>
                                	</td>
                                	<td colspan="3">
                                		<select name="interventions" id="interventions">
                                    <option value="0">
                                       <?php echo_string ('COMBO_ALL'); ?>
                                    </option>
                                    <?php 
                                       
                                        $ctrs = DB::executeSelect('intervention_types', '*');
                                                                      
										$selected = $_POST['interventions'];
                                        foreach ($ctrs as $ctr) {
                                    ?>
                                    <option value="<?php echo $ctr['TypeID']; ?>" <?php if ($ctr['TypeID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $ctr['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                                	</td>
                                </tr>
                               <tr>
                               	<td colspan="4">
                               		<?php echo_string ('DATE_START'); ?>: <input type="text" name="startdate" id="startdate" />
                               		<?php echo_string ('DATE_END'); ?>: <input type="text" name="enddate" id="enddate" />
                               	</td>
                               </tr>
                                <tr>
                               	<td colspan="4">
                               		Include Archived Learners: <input type="checkbox" name="archived" id="archived" />
                               	</td>
                               </tr>
                                 
                                 <tr>
                                 
                                 <td colspan="4">Export results to Excel?
					<input type="checkbox" name="e" id="e" value="xls" />
				     </td>
				     </tr>
				     <tr id="excelversion">
                                 <td colspan="4"><em>Excel Version:</em>
                                 	<input type="radio" name="fmt" value="c" <?php if ($_GET['fmt'] == 'c' || empty ($_GET['fmt']) ) { ?>checked<?php } ?> />Current (Office 2007+)
                     
					<input type="radio" name="fmt" value="o" <?php if ($_GET['fmt'] == 'o') { ?>checked<?php } ?>/>Legacy (Office 2003 and back)
                                 </td>
                                


                                 </tr> 
                            <tr>
                            	<td>
                            		<input type="submit" value="Run" />
                            		                            	</td>
                            </tr>
                            </table>
                            
                            </form>
                            </div>
<?php 
	// DETAILED INFORMATION TABLE (USERS)
	

if (!$myfirstrun ) {
	
	//get counts
	$query = '	SELECT count(distinct(sb_users_info.loginid)) as userscount
				FROM sb_users_attempt
				INNER JOIN sb_users_attempt_answers
					ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
				INNER JOIN sb_users_info
					ON sb_users_attempt.userid = sb_users_info.UserID
				INNER JOIN sb_ethnicity 
					ON sb_users_info.ethnicityid = sb_ethnicity.EthnicityID
				INNER JOIN sb_age_groups
					ON sb_age_groups.AgeID = sb_users_info.ageid ';
	
	if ($_GET ['ctr']) { 
			$query .= ' LEFT OUTER JOIN sb_centres ON sb_centres.CentreID = sb_users_info.centreid';
	}
	
	if ($_GET ['interventions']) { 
$query .= '			LEFT OUTER JOIN sb_user_interventions ON sb_user_interventions.userid = sb_users_info.UserID';
	}
	
	
// create where clause for both count and datagrid queries
	$whereq = array ();
	if ($_GET['ctr']) {
		array_push ($whereq, 'sb_users_info.centreid=' . $_GET['ctr']);
	}
	
	if ($_GET['provider']) {
	
	  if ( has_capability(Sessions::getID(), 'providers:control_all') || has_capability(Sessions::getID(), 'providers:control_subcontractors') ) {
	  
			if ($_GET['provider'] == 'Any') {
			
					// deal with multiple sub=providers for a 'super admin'					
					$provStr = '(';
					for ($i=0; $i< count($providers); $i++) {
						if ($i == 0) {
							$provStr .= 'sb_users_info.providerid=' . $providers[$i]['ProviderID'];
						} else {
							$provStr .= ' or sb_users_info.providerid=' . $providers[$i]['ProviderID'];
						}
					}
					$provStr .= ')';
					array_push ($whereq, $provStr);
				
			} else {
		
					// need to check if provider is allowed to see this provider...
					
	  				array_push ($whereq, 'sb_users_info.providerid=' . $_GET['provider']);
			
			}
	  } else {
	  	array_push ($whereq, 'sb_users_info.providerid=' . Sessions::getUserInfo('providerid'));
	  }
	} else {
		array_push ($whereq, 'sb_users_info.providerid=' . Sessions::getUserInfo('providerid'));
	}
	if ($_GET ['prg']) {
		//array_push ($whereq, 'sb_users_info.programmeid=' . $_GET['prg']);
	}
	if ($_GET ['ethnicity']) {
		array_push ($whereq, 'sb_users_info.ethnicityid=' . $_GET['ethnicity']);
	}
	if ($_GET ['agegroup']) {
		array_push ($whereq, 'sb_users_info.ageid=' . $_GET['agegroup']);
	}
	if ($_GET ['gender']) {
		array_push ($whereq, 'sb_users_info.gender="' . $_GET['gender'] . '"');
	}
	if ($_GET ['assesstype']) {
		if ($_GET['assesstype'] == 'both') {
			// update 2013-01-09: if 'both' exclude from query?
		} else {
			array_push ($whereq, 'sb_users_attempt.assessmenttype="' . $_GET['assesstype'] . '"');
		}
	}
	if ($_GET ['startdate']) {
		//array_push ($whereq, 'sb_users_attempt.`date` >= STR_TO_DATE(TIMESTAMP("' . ukdate2mysql ($_GET['startdate']) . '"), "Y%-%m-%d")');
		array_push ($whereq, 'sb_users_attempt.`date` >= "' . ukdate2mysql ($_GET['startdate']) . ' 00:00:00"');
	}
	if ($_GET ['enddate']) {
		//array_push ($whereq, 'sb_users_attempt.`date` <= STR_TO_DATE(TIMESTAMP("' . ukdate2mysql ($_GET['enddate']) . '"), "Y%-%m-%d")');
		array_push ($whereq, 'sb_users_attempt.`date` <= "' . ukdate2mysql ($_GET['enddate']) . ' 23:59:59"');
	}
	if ($_GET ['interventions']) {
		array_push ($whereq, 'sb_user_interventions.typeid="' . $_GET['interventions'] . '"');
	}
	
	if (! $_GET['archived']) {
		array_push ($whereq, 'sb_users_info.archived=0');	
	}
	
	$wheresql = '';
	$firstflag = true;
	foreach ($whereq as $q) {
		if ($firstflag) {
			$firstflag = false;
			$wheresql .= ' WHERE ';
			$wheresql .= $q;
		} else {
			$wheresql .= ' AND ' . $q;
		}
	}
	
	
	
	
	$query .= $wheresql;

	
	
	$usrcount = DB::runSelectQuery ($query, false);


?>

<?php 

/************* DISPLAY INFORMATION ON THE REPORT WHICH HAS BEEN RUN ***************/
$query = 'SELECT	(select sb_ethnicity.name from sb_ethnicity where sb_ethnicity.EthnicityID = ' . $_GET['ethnicity'] . ') as eth,
		(select sb_age_groups.name from sb_age_groups where sb_age_groups.AgeID = ' . $_GET['agegroup'] . ') as age,
		(select sb_centres.name from sb_centres where sb_centres.CentreID = ' . $_GET['ctr'] . ') as ctr,
		(select sb_providers.name from sb_providers where sb_providers.ProviderID = ' . $_GET['provider'] . ') as provider,
		(select sb_intervention_types.name from sb_intervention_types where sb_intervention_types.TypeID = ' . $_GET['interventions'] . ') as iv
		limit 1';
$blah = DB::runSelectQuery($query, false);
?>
<div class="boxer">
<h3><?php echo_string ('FILTER_CRITERIA'); ?></h3>   
<?php echo_string ('FILTER_SEARCH_FEEDBACK'); ?>
<ul>
	<li><?php echo_string ('PROVIDER'); ?>: <?php echo $_GET['provider'] ? $blah['provider']: return_string('ANY'); ?></li>
	<li><?php echo_string ('CENTRE'); ?>: <?php echo $_GET['ctr'] ? $blah['ctr']: return_string('ANY'); ?></li>
	<li><?php echo_string ('GENDER'); ?>: <?php if ($_GET['gender'] == "m") { echo_string('MALE'); } else if ($_GET['gender'] == "f") { echo_string('FEMALE'); } else { echo return_string('ANY'); }; ?></li>
	<li><?php echo_string ('ETHNICITY'); ?>: <?php echo $_GET['ethnicity'] ? $blah['eth']: return_string('ANY'); ?></li>
	<li><?php echo_string ('AGE_GROUP'); ?>: <?php echo $_GET['agegroup'] ? $blah['age']: return_string('ANY'); ?></li>
	<li><?php echo_string ('ACTIVITY_TYPE'); ?>: <?php if ($_GET['assesstype'] == "l") { echo_string('LEARNER'); } else { echo_string('ADVISOR'); } ?></li>
	<li><?php echo_string ('IV'); ?>: <?php echo $_GET['interventions'] ? $blah['iv']: return_string('ANY'); ?></li>
	<li><?php echo_string ('DATE_RANGE'); ?>:
	<?php if (!empty ($_GET['startdate']) && !empty ($_GET['enddate'])) {
		echo "Between " . $_GET['startdate'] . ' and ' . $_GET['enddate'];
	} else if (!empty ($_GET['startdate'])) {
		echo "After " . $_GET['startdate'];
	} else if (!empty ($_GET['enddate'])) {
		echo "Before " . $_GET['enddate'];
	} else {
		echo_string('ANY');
	}
	?>
	</li>
	<?php if ($_GET['archived']) {?>
	<li>Included archived users in report</li>
	<?php } ?>
</ul>
</div>


<?php /************** GET DISTANCE TRAVELLED *******************/ ?>
<div class="boxer">    
<h3><?php echo_string ('DB_DISTANCE'); ?></h3>                
<div style="float:left; text-align:center; border:solid 1px #CCCCCC; padding:5px;">



<?php if ($usrcount['userscount'] > 0) { ?>              
<?php 
	
	$query = 'SELECT sb_users_attempt.sitting as Sitting,
	AVG(sb_users_attempt_answers.q1) AS q1, 
	AVG(sb_users_attempt_answers.q2) AS q2, 
	AVG(sb_users_attempt_answers.q4) AS q4, 
	AVG(sb_users_attempt_answers.q5) AS q5, 
	AVG(sb_users_attempt_answers.q6) AS q6, 
	AVG(sb_users_attempt_answers.q7) AS q7, 
	AVG(sb_users_attempt_answers.q8) AS q8

FROM sb_users_attempt INNER JOIN sb_users_attempt_answers ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
	 INNER JOIN sb_users_info ON sb_users_attempt.userid = sb_users_info.UserID
	 INNER JOIN sb_ethnicity ON sb_users_info.ethnicityid = sb_ethnicity.EthnicityID
	 INNER JOIN sb_age_groups ON sb_age_groups.AgeID = sb_users_info.ageid ';
	
	if ($_GET ['ctr']) { 
			$query .= ' LEFT OUTER JOIN sb_centres ON sb_centres.CentreID = sb_users_info.centreid';
	}
	
	
	 
if ($_GET ['interventions']) { 
$query .= '			LEFT OUTER JOIN sb_user_interventions ON sb_user_interventions.userid = sb_users_info.UserID';
	}
	
	$query .= $wheresql;
	
	$query .= ' GROUP BY sitting';
	
	
	$rep = DB::runSelectQuery($query, true);
	
	
	?>
	<?php	
	
	
$dg2 = new DataGrid ($rep);
$dg2->addAttr('table', 'cellpadding', '5');
$dg2->addAttr('table', 'class', 'tablesorter');
$dg2->addAttr('table', 'id', 'userdg2');
$dg2->addAttr('td', 'bgcolor', 'red');
$dg2->addAttr('table', 'width', '50%');
$dg2->addAttr('tr', 'align', 'center');
$dg2->addAttr('table', 'border', '1');

	
// clear some memory.... 
unset($dg2);
unset ($rep);

}


/************ DATAGRID TABLE REPORT SECTION *********************/
if (!empty ($_GET)) {
	$query = '				SELECT
							sb_users_info.UserID,
							sb_users_info.sname as Surname, 
						 	sb_users_info.fname as Firstname, 
							sb_users_info.loginid as Username,
							sb_users_info.gender as Gender, 
							sb_users_attempt.sitting,
		     				sb_users_attempt_answers.q1,
							sb_users_attempt_answers.q2, 
							sb_users_attempt_answers.q4, 
							sb_users_attempt_answers.q5, 
							sb_users_attempt_answers.q7, 
							sb_users_attempt_answers.q8, 
							sb_users_attempt_answers.q6,
							sb_users_attempt.assessmenttype,
							sb_ethnicity.name as Ethnicity, 
												sb_age_groups.name as AgeGroup,
							sb_users_info.archivereason as ArchiveReason,
							sb_providers.name as Provider,
							sb_centres.name as Centre,
							DATE_FORMAT(sb_users_attempt.date,"%d/%m/%Y") AS date				
							FROM sb_users_attempt
							INNER JOIN sb_users_attempt_answers ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
							INNER JOIN sb_users_info ON sb_users_attempt.userid = sb_users_info.UserID
							INNER JOIN sb_ethnicity ON sb_users_info.ethnicityid = sb_ethnicity.EthnicityID
							
							INNER JOIN sb_age_groups ON sb_age_groups.AgeID = sb_users_info.ageid 
							INNER JOIN sb_providers ON sb_providers.ProviderID = sb_users_info.providerid ';
	
	
			$query .= ' LEFT OUTER JOIN sb_centres ON sb_centres.CentreID = sb_users_info.centreid';

	
	
if ($_GET ['interventions']) { 
$query .= '			LEFT OUTER JOIN sb_user_interventions ON sb_user_interventions.userid = sb_users_info.UserID';
	}
	

	$query .= $wheresql;
							
	$query .= ' ORDER BY sb_users_info.userid, sb_users_attempt.assessmenttype, sitting;';
	
	
	
	
	
		
	
		
$rep = DB::runSelectQuery($query, true);






// cleanse data and calculate distance for each user
$currentid = 0;
$currentassesstype = 'a';

$scores = array ();
// add empty row to ensure all rows are calc'd
array_push ($rep, '');
$sittings= 0;
$usersdata = array ();
$collecteddist = array ();

// update 2013-02-04 changed this loop below for efficiency - vast improvements all round!
// using $scores array instead of pulling from the DB.
foreach ($rep as $row) {
	
	$newid = $row['UserID'];
	$newassesstype = $row['assessmenttype'];
	
	// update 2013-01-09 check for a new user or new assessment type to split to new row (added for 'both' assessment type option).
	if ($currentid != $newid || $currentassesstype != $newassesstype) {
			
			// update 2013-01-09 attempting to split into two sets of results here.
			
			
			if (!empty ($scores)) {
				
					
					// do two sets of results...
					$dist = getDistanceTravelledAdvancedCollated  ($scores);
					if ($_GET['assesstype'] == 'both') {
						$totaldist = getDistanceTravelledAdvanced ($currentid, $currentassesstype, $scores);
						$numofsittings = count ($scores);
					} else {
						$totaldist = getDistanceTravelledAdvanced ($currentid, $_GET['assesstype'],  $scores);
						$numofsittings = count ($scores);
					}
					
					array_push ($usersdata,  array (
								'UserID'=>$currentid,
								'fname'=> $scores[0]['Firstname'] . ' ' . $scores[0]['Surname'],
								//'Surname'=> $scores[0]['Surname'],
								'Centre'=>$scores[0]['Centre'],
								'Sittings This Period'=>$sittings,
								'Total Sittings'=> $numofsittings,
								'Gender'=> $scores[0]['Gender'],
								'Ethnicity'=> $scores[0]['Ethnicity'],
								'Provider'=> $scores[0]['Provider'],
								'AgeGroup'=> $scores[0]['AgeGroup'],
								'Type'=> $currentassesstype == 'a' ? 'Advisor' : 'Learner',
								'Distance This Period'=>$dist['textperc'] . '%',
								'Total Distance Travelled'=> $totaldist['textperc'] . '%',
								'Archive Reason'=>$scores[0]['ArchiveReason'],
								'Sitting 1'=> isset ($scores[0]['date']) ? $scores[0]['date'] : '',
								'Sitting 2'=> isset ($scores[1]['date']) ? $scores[1]['date'] : '',
								'Sitting 3'=> isset ($scores[2]['date']) ? $scores[2]['date'] : '',
					));
					
					if ($sittings > 1) {
						array_push ($collecteddist, $dist);	
					}
				
			}
			
			// now start with new user
			$currentid = $newid;
			$currentassesstype = $newassesstype;
			
			$scores = array ();
			$sittings = 0;
	} 
	array_push ($scores, $row);
	$sittings ++;
	
	
} // rows

?>
<?php

$avgdist = getAverageOfDistanceTravelled ($collecteddist, 0);
	$xlsdist = $avgdist;
?>

<p><?php echo 'Average distance travelled for defined period is'; ?>: <?php echo $avgdist['textperc']; ?>%</p>
		<img src="http://chart.apis.google.com/chart?chxt=x,y&chxl=0:|Groovy|1:|-100%||100%&chs=170x100&chl=<?php echo $avgdist['textperc']; ?>%&cht=gom&chd=t:<?php echo $avgdist['graphperc']; ?>"/>
	</div>
	<div style="float:right">
</div>
</div>
<div class="boxer" id="detaildg">
<h3><?php echo_string ('USER_BREAKDOWN'); ?></h3>
<?php if ($usrcount['userscount'] == 0) { ?>
	<?php echo_string ('CRITERIA_NONE_FOUND'); ?>.<br/>
<?php } else {?>
	<?php echo sprint_string ('CRITERIA_NUM_FOUND', $usrcount['userscount']) ; ?>:<br/>
<?php } ?>

<?php 

$dg = new DataGrid ($usersdata, 'UserID');
$dg->addFieldTitle ('fname', 'Name');
$dg->addFieldTitle ('AgeGroup', 'Age Group');
$dg->addAttr('table', 'cellpadding', '5');
$dg->addAttr('table', 'class', 'tablesorter');
$dg->addAttr('table', 'id', 'userdg');
$dg->addAttr('tr', 'align', 'center');
$dg->addAttr('table', 'border', '0');
$dg->addAttr('table', 'width', '100%');
$dg->addAttr('th', 'border', '1');
if (! isset ( $_GET['archived'])) {
	$dg->removeDisplayField ('Archive Reason');
}
if ($_GET['assesstype'] != 'both') {
	$dg->removeDisplayField('Type');
}
$dg->setOutputFieldNameAsClass (false);
$dg->removeDisplayField ('UserID');
$dg->addHTMLCol('<a href="' . $CFG->fullhttp . '/dashboard.php?uid=%s">View</a>', 'User Report');
$dg->render ();
		
}
?>

</div>



<?php } ?>
<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
<?php 

$excelExport = isset ($_GET['e']) ? $_GET['e'] : 0;

if ($excelExport === 'xls') {

	$fmt = isset ($_GET['fmt']) ? $_GET['fmt'] : 0;
	
	ob_clean ();
	
	if ($fmt == 'o') { //'o' = old!	
		// export for IE6 and MS Office 2003.
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment; filename=tracio_report_" . date("Y-m-d") . ".xls");
		header("Content-Transfer-Encoding: binary");
		// code which caused a problem for Beth and does in IE6... Office versions pre-xlsx
	} else {
		// code for non IE6 and MS2003 versioning!
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=tracio_report_" . date("Y-m-d") . ".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
	}
	
	?>
	<h1>TRaCIO Report</h1>
	<p>Produced on <?php echo date('l jS \of F Y h:i:s A'); ?></p>
	<div class="boxer">
<h3><?php echo_string ('FILTER_CRITERIA'); ?></h3>   
<?php echo_string ('FILTER_SEARCH_FEEDBACK'); ?>
<ul>
	<li><?php echo_string ('CENTRE'); ?>: <?php echo $_GET['ctr'] ? $blah['ctr']: return_string('ANY'); ?></li>
	<li><?php echo_string ('GENDER'); ?>: <?php if ($_GET['gender'] == "m") { echo_string('MALE'); } else if ($_GET['gender'] == "f") { echo_string('FEMALE'); } else { echo return_string('ANY'); }; ?></li>
	<li><?php echo_string ('ETHNICITY'); ?>: <?php echo $_GET['ethnicity'] ? $blah['eth']: return_string('ANY'); ?></li>
	<li><?php echo_string ('AGE_GROUP'); ?>: <?php echo $_GET['agegroup'] ? $blah['age']: return_string('ANY'); ?></li>
	<li><?php echo_string ('ACTIVITY_TYPE'); ?>: <?php if ($_GET['assesstype'] == "l") { echo_string('LEARNER'); } else { echo_string('ADVISOR'); } ?></li>
	<li><?php echo_string ('IV'); ?>: <?php echo $_GET['interventions'] ? $blah['iv']: return_string('ANY'); ?></li>
	<li><?php echo_string ('DATE_RANGE'); ?>:
	<?php if (!empty ($_GET['startdate']) && !empty ($_GET['enddate'])) {
		echo "Between " . $_GET['startdate'] . ' and ' . $_GET['enddate'];
	} else if (!empty ($_GET['startdate'])) {
		echo "After " . $_GET['startdate'];
	} else if (!empty ($_GET['enddate'])) {
		echo "Before " . $_GET['enddate'];
	} else {
		echo_string('ANY');
	}
	?>
	</li>
</ul>
<ul>
<li>
	<?php if ($usrcount['userscount'] == 0) { ?>
	<?php echo_string ('CRITERIA_NONE_FOUND'); ?>.<br/>
	<?php } else {?>
	<?php echo sprint_string ('CRITERIA_NUM_FOUND', $usrcount['userscount']) ; ?>.<br/>
	<?php } ?>
	<?php 
	
	
	
	
	?>
	<p><?php echo 'Average distance travelled for defined period is'; ?>: <?php echo $xlsdist['textperc']; ?>%</p>
	</li>
</ul>

</div>
<?php 
	
	// override empty row madness
	$dg->setMaxRowsForPrint (10000);
	$dg->setPagination(false);
	echo $dg->render ();
	
} else {
	ob_end_flush();
}?>
