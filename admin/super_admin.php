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

if (!has_capability(Sessions::getID(), 'admin:super_admin')) {
	die (return_string ('ACCESS_DENIED'));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="../external/tablesorter/jquery.tablesorter.min.js"></script>

<link href="../external/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<link href="admin.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
$(document).ready(function(){
	$("#centrestable").tablesorter();
});
</script>

</head>
<body class="superadmin">
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<h2>Super Admin</h2>
<?php 


//process form
if (!empty ($_POST)) {
		if ($_POST['action'] == 'Add') {
			// check for a duplicate first
			$dup = DB::executeSelect ('providers', '*', array ('name'=>$_POST['newprovider']));
			// if centre does not exist in list, add it
			if (!$dup) {
				if (!empty ($_POST['newprovider'])) {
					$add = DB::executeInsert('providers', array ('name'=>$_POST['newprovider'], 'superproviderid'=>$_POST['superprovider']));
					if ($add) {
					?>
					<div class="alert sallgood">Provider '<?php echo $_POST['newprovider']; ?>' has been added.</div>
					<?php } else { ?>
					<div class="alert warning">Unable to add '<?php echo $_POST['newprovider']; ?>' as a new provider.</div>
					<?php } 
				}
			}
		} else if ($_POST['action'] == 'Archive') {
			foreach ($_POST['providers'] as $provider) {
				
				$rm = DB::executeUpdate ('providers', array ('visible'=>0), array ('ProviderID'=>$provider), 1);
				if ($rm) {
					?>
					<div class="alert sallgood">Provider '<?php echo $provider; ?>' has been archived.</div>
					<?php } else { ?>
					<div class="alert warning">Unable to archive provider '<?php echo $provider; ?>'.</div>
					<?php } 
			}
		}
}


$providers = DB::runSelectQuery('	SELECT DISTINCT(sb_providers.name), sb_providers.ProviderID, sb_super_providers.name AS SuperProvider
									FROM sb_providers LEFT OUTER JOIN sb_super_providers ON sb_providers.superproviderid = sb_super_providers.SuperProviderID
									WHERE sb_providers.visible = 1
									GROUP by sb_providers.name
									ORDER BY name ASC;');



?>


<div class="annot" style="clear: both;">
<p>Use this screen to control providers for the TRaCIO tool.</p>
</div>
<div class="subannot_first"></div>
<div class="subannot">
<p>The 'Providers' section will allow you to add new providers or archive existing providers.</p>
<p></p>
</div>


<div id="centres" class="section">
<div class="sectionheader">Providers</div>
<div class="sectioncontent">
<div class="subsection">
<h3>Existing Providers</h3>
<div class="subannot_two"></div>
<div class="subannot">
<p>To archive existing providers, select the relevant checkboxes in the 'Select' column and click on the 'Archive' button.</p>
</div>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="form1">

<?php
$dg = new DataGrid ($providers, 'ProviderID');
$dg->setTableID ('providerstable');
$dg->setTableClass ();
if ($CFG->allowCentreRemoval) {
	$dg->addHTMLCol('<div class="centred"><input name="providers[]" value="%s" type="checkbox" /></div>', 'Select');
}
$dg->removeDisplayFields(array ('ProviderID'));
$dg->addFieldTitle('name', 'Name');
$dg->addAttr('th', 'align', 'left');
$dg->addAttr('table', 'width', '100%');

$dg->setPagination(false);
$dg->render ();
?>
<?php if ($CFG->allowCentreRemoval) { ?>
<input type="submit" value="Archive" name="action" />
<?php } ?>
</form>
</div>
<div class="subsection noprint">
<h3>Add New Provider</h3>
<div class="subannot_two"></div>
<div class="subannot">
<p>Enter a new provider in the textbox below, select a super provider (if relevant) and click 'Add'.</p>
</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="form2">
<?php 
$superproviders = DB::runSelectQuery('SELECT * from sb_super_providers ORDER BY name asc');
?>
<label for="newprovider"><?php echo 'Provider:'; ?></label>
<input type="text" name="newprovider"/>
<br/>
<label for="superprovider"><?php echo 'Super Provider:'; ?></label><select name="superprovider" id="superprovider">
	<option value="0">None</option>
	<?php foreach ($superproviders as $superprovider) {?>
	<option value="<?php echo $superprovider['SuperProviderID']; ?>"><?php echo $superprovider['name']; ?></option>
	<?php } ?>
</select>
<input type="submit" value="Add" name="action" />
</form>
</div>
</div>

</div>
<?php  include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
