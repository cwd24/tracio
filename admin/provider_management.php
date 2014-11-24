<?php 
include_once ('../config.php');
include_once ($CFG->apploc . '/db_connect.php');
include_once ($CFG->apploc . '/classes/db.php');
include_once ($CFG->apploc . '/lib/roles.php');
include_once ($CFG->apploc . '/classes/datagrid.php');
include_once ($CFG->apploc . '/lib/funcs.php');
include_once ($CFG->apploc . '/classes/sessions.php');

Sessions::checkUserLogIn ();

//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('question', 'general', 'user'));

if (!has_capability(Sessions::getID(), 'admin:provider_page')) {
	die (return_string ('ACCESS_DENIED'));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title><?php echo_string ('APP_NAME'); ?> : Provider Management</title>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="../external/tablesorter/jquery.tablesorter.min.js"></script>

<link href="../external/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<link href="admin.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
$(document).ready(function(){
	$("#centrestable").tablesorter();
	$("#advisorstable").tablesorter();
});
</script>

</head>
<body class="provider-man">
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<h2>Provider Admin</h2>
<?php 
// find out what users provider is
$provider = Sessions::getUserInfo('providerid');

// update 2013-01-09 added check to ensure user has access to the centres they are attempting to modify
// need to check only for mods and not for adds!

//process form
if (!empty ($_GET['form'])  ) {
	// which admin are we dealing with? ctr - centres, adv - advisors
	if ($_GET['form'] == 'ctr' && ($_GET['action'] == 'Add' || has_access_to_centres (Sessions::getID(), $_GET['centre']))) {
		if ($_GET['action'] == 'Add') {
			// check for a duplicate first
			$dup = DB::executeSelect ('centres', '*', array ('providerid'=>$provider, 'name'=>$_GET['newcentre']));
			// if centre does not exist in list, add it
			if (!$dup) {
				if (!empty ($_GET['newcentre'])) {
					$add = DB::executeInsert('centres', array ('providerid'=>$provider, 'name'=>$_GET['newcentre']));
				}
			}
		} else if ($_GET['action'] == 'Remove') {
			foreach ($_GET['centre'] as $centre) {
				//$rm = DB::executeDelete('centres', array ('CentreID'=>$centre), 1);
				$rm = DB::executeUpdate('centres', array ('visible'=>0), array ('CentreID'=>$centre), 1);
			}
		} else if ($_GET['action'] == 'Rename') {
			if (count ($_GET['centre']) == 1) {
				// only one centre selected, so go for it
				// retrieve the centre name from the database
				$centrename = DB::executeSelect('centres', array ('name', 'CentreID'), array('CentreID'=>$_GET['centre'][0]));
			} else {
				// no centres selected, or more than one, so bail.
			?><div class="alert warning">Please select a centre to rename. You must select only <em>one</em> centre.</div><div style="clear: both;"></div><?php 
				$_GET['action'] = '';
			}
		} else if ($_GET['action'] == 'Save') {
			$ud = DB::executeUpdate ('centres', array('name' => $_GET['newcentrename']), array ('CentreID'=>$_GET['centre']), 1 );
			if ($ud) {
				?><div class="alert sallgood">Centre renamed to '<?php echo $_GET['newcentrename']; ?>'</div><div style="clear:both"></div><?php 
			} else {
				?><div class="alert warning">Unable to rename centre.</div><div style="clear:both"></div><?php
			}
		}
	} else if ($_GET['form'] == 'adv') {
		if ($_GET['action'] == 'Add') {
			$dup = DB::executeSelect ('activations', '*', array ('providerid'=>$provider, 'fname'=>$_GET['fname'], 'sname'=>$_GET['sname'], 'email'=>$_GET['email']));
			if (!$dup) {
				if (!empty ($_GET['fname']) && !empty ($_GET['sname'])  && !empty ($_GET['email']) ) {
					$add = DB::executeInsert ('activations', array ('providerid'=>$provider, 'fname'=>$_GET['fname'], 'sname'=>$_GET['sname'], 'email'=>$_GET['email'], 'activationcode'=>uniqid ()));
				}
			}
		}  else if ($_GET['action'] == 'Remove') {
				foreach ($_GET['advisor'] as $adv) {
					$rm = DB::executeDelete('activations', array ('ActivationID'=>$adv), 1);
				}
		} else if ($_GET['action'] == 'Invite' || $_GET['action'] == 'Reinvite') {
			// work out who needs to be invited!
			// update 20130114 covering reinvites here...
			$invitees = array ();
			
			if ($_GET['action'] == 'Reinvite') {
				foreach ($_GET['advisor'] as $adv) {
					$invitees[] = DB::executeSelect('activations', array ('activationcode', 'ActivationID', 'fname', 'sname', 'email'), array ('ActivationID'=>$adv));
				}
			} else {
				$invitees = DB::executeContainedSelect('activations', array ('activationcode', 'ActivationID', 'fname', 'sname', 'email'), array ('providerid'=>$provider, 'emailsent'=>0));
			}
			
			if (!empty ($invitees)) {
				foreach ($invitees as $invitee) {
					
					
					$headers = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
					$headers .= 'From: TRaCIO <tracio@rsc-wales.ac.uk>' . "\r\n";
					
					$body = '<html><head><title>TRaCIO Invitation</title></head>';
					$body .= '<body style="background-color: #FFFFFF;">';
							$body .= '	<p>&nbsp;</p>';
						$body .= '		<h2>TRaCIO Invitation</h2>';
						$body .= '		<p>' . $invitee['fname'] . ' ' . $invitee['sname'] . ',</p>';
						$body .= '		<p>You have been invited by your institutional administrator to join TRaCIO as an advisor.</p>';
						$body .= '		<p>Please click the link below to accept this invite, or copy and paste the web address into your browser:</p>';
						$body .= '		<p><a href="' . $CFG->fullhttp . '/user_signup.php?ac=' . $invitee['activationcode'] . '">' . $CFG->fullhttp . '/user_signup.php?ac=' . $invitee['activationcode'] . '</a></p>';
						$body .= '		<p>Many thanks,</p>';
						$body .= '		<p>TRaCIO </p>';
						$body .= '		</body>';
						$body .= '	</html>';
									
				
					
					if ($CFG->emailsEnabled) {
						$emailsuccess = mail ($invitee['email'], 'TRaCIO Invitation', $body, $headers);
				
						if ($emailsuccess) {
					
							// if emails sent, change emailsent field to 1
							$res = DB::executeUpdate('activations', array ('emailsent'=>1), array ('ActivationID'=>$invitee['ActivationID']) );
							?>
							<div class="alert sallgood"><?php echo 'Invite sent to: ' . $invitee['fname'] . ' ' . $invitee['sname'] . ' (' . $invitee['email'] . ')'; ?></div><div style="clear: both;"></div>
							<?php 
						} else {
							?>
							<div class="alert warning"><?php echo 'Error sending invite to: ' . $invitee['fname'] . ' ' . $invitee['sname'] . ' (' . $invitee['email'] . ')'; ?></div><div style="clear: both;"></div>
							<?php 
						}
						
					} else {
						echo '<div class="fakeoutput">';
						echo 'EMAIL OUTPUT:<br/>' . $body;
						echo '</div>';
					}
				}
			}
		
		}
	}
}

$centres = DB::executeContainedSelect('centres', '*', array ('providerid'=>$provider, 'visible'=>1), 'name');
$advisors = DB::executeContainedSelect('activations', array ('ActivationID', 'fname as Firstname', 'sname as Surname', 'email as Email', 'activated', 'emailsent'), array ('providerid'=>$provider));

?>

<div class="annot">
<p>Use this screen to control centres and advisors for your organisation.</p>
</div>
<div class="subannot_first"></div>
<div class="subannot">
<p>The 'Centres' section will allow you to add new centres or remove existing centres. Centres are used for reports.</p>
<p>The 'Advisors' section is used to control all the registered advisors for your organisation.</p>
<p></p>
</div>


<div id="centres" class="section" style="width:100%;">

<div class="sectionheader">Centres</div>
<div class="sectioncontent">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" id="form1">
<input type="hidden" value="ctr" name="form" />
<?php if ($_GET['action'] == 'Rename') {?>
	<div class="subsection noprint delay">
	<h3>Rename Centre</h3>
	<div class="subannot_two"></div>
	<div class="subannot">
		<p>To rename the selected centre, enter it's new name below and click 'Save'.</p>
	</div>
	<label for="newcentrename"><?php echo_string ('CENTRE'); ?>: </label>
	<input type="text" name="newcentrename" value="<?php echo $centrename['name']; ?>"/>
	<input type="hidden" name="centre" value="<?php echo $centrename['CentreID']; ?>" />
	<input type="submit" value="Save" name="action" />
	<a href="<?php echo $_SERVER['PHP_SELF']; ?>">Cancel</a>
	</div>
<?php } else { ?>

<div class="subsection">
<h3>Existing Centres</h3>
<div class="subannot_two"></div>
<div class="subannot">
<p>To remove or rename existing centres, select the relevant checkboxes in the 'Select' column and click on the 'Remove' or 'Rename' buttons respectively.</p>
</div>


	
	
	
	<?php
	$dg = new DataGrid ($centres, 'CentreID');
	$dg->setTableID ('centrestable');
	$dg->setTableClass ();
	if ($CFG->allowCentreRemoval) {
		$dg->addHTMLCol('<div class="centred"><input name="centre[]" value="%s" type="checkbox" /></div>', 'Select');
	}
	$dg->removeDisplayFields(array ('CentreID', 'providerid', 'visible'));
	$dg->addFieldTitle('name', 'Name');
	$dg->addAttr('th', 'align', 'left');
	$dg->addAttr('table', 'width', '100%');
	
	$dg->render ();
	?>
	<?php if ($CFG->allowCentreRemoval) { ?>
	<input type="submit" value="Remove" name="action" onClick="return confirm('Are you sure you want to delete the centre(s)?'); " />
	<input type="submit" value="Rename" name="action" />
	<?php } ?>
	</div>
	
	
	
	<div class="subsection noprint">
	<h3>Add New Centre</h3>
	<div class="subannot_two"></div>
	<div class="subannot">
		<p>Enter a new centre in the textbox below and click 'Add'.</p>
	</div>
	<label for="newcentre"><?php echo_string ('CENTRE'); ?>: </label>
	<input type="text" name="newcentre" />
	<input type="submit" value="Add" name="action" />
	<?php }  ?>
	</div>
</form>
</div>
</div>


<div id="advisors" class="section" style="width:100%;">
<div class="sectionheader">Advisors</div>
<div class="sectioncontent">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" id="form2">
<input type="hidden" value="adv" name="form" />
<div class="subsection">
<h3>Existing Advisors</h3>
<div class="subannot_two"></div>
<div class="subannot">
<p>To remove existing advisors, select the relevant checkboxes in the 'Select' column and click on the 'Remove' button.</p>
<p>To send out a new email invitation to users who have yet to activate their accounts, select the relevant checkboxes in the 'Select' column and click on the 'Reinvite' button.</p>
<p><em>NB: You may only remove or reinvite advisors who have not already Activated their account following your invitation.</em></p>
</div>

<?php
$dg = new DataGrid ($advisors, 'ActivationID');
$dg->setTableID ('advisorstable');
$dg->setTableClass ();
$dg->addConditionalField('emailsent', '<div class="centred"><img src="../images/tick.gif"/></div>', 'Email Sent', 'emailsent', '! 0', '');
$dg->addConditionalField('activated', '<div class="centred"><img src="../images/tick.gif"/></div>', 'Activated', 'activated', '! 0', '');
$dg->addConditionalField('ActivationID', ' <input name="advisor[]" value="%s" type="checkbox" />', 'Select', 'activated', '= 0', '');
$dg->removeDisplayFields(array ( 'ActivationID', 'emailsent', 'activated'));
$dg->addAttr('th', 'align', 'left');

$dg->render ();
?>
<input type="submit" value="Remove" title="Remove Invitation" name="action" id="remove" />
<input type="submit" value="Reinvite" title="Resend Invitation" name="action" id="reinvite" />
</div>
<div class="subsection noprint addnewadv">
<h3>Add New Advisor</h3>
<div class="subannot_two"></div>
<div class="subannot">
<p>Enter details for the new advisor in the textboxes below and click 'Add'. Please enter a live email address as this will be used to email the invitation.</p>
</div>
<table>
<tr>
	<td>
		<label for="fname"><?php echo_string ('FN'); ?></label>
	</td>
	<td>
		<input type="text" name="fname" />
	</td>
</tr>
<tr>
	<td>
		<label for="sname"><?php echo_string ('SN'); ?></label>
	</td>
	<td>
		<input type="text" name="sname" />
	</td>
</tr>
<tr>
	<td>	
		<label for="email"><?php echo_string ('EMAIL'); ?></label>
	</td>
	<td>
		<input type="text" name="email" />
	</td>
</tr>
<tr>
	<td>
		<input type="submit" value="Add" name="action" />
	</td>
</tr>

</table>
</div>


<div class="subsection noprint invitenewadv">
<h3>Invite Advisors</h3>
<div class="subannot_two"></div>
<div class="subannot">
<p>If you have new advisors to invite, click on the 'Invite' button to send email invitations out.</p>
</div>
Click below to send emails to all currently uninvited advisors.<br/><br/>
<input type="submit" value="Invite" name="action" />
</div>
</form>
</div>
</div>
<?php  include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
