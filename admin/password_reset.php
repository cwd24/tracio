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

if (!has_capability(Sessions::getID(), 'admin:reset_user_passwords')) {
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
	$("#advisorstable").tablesorter();
});
</script>

</head>
<body>
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<h2>Reset Users Passwords</h2>
<?php 
// find out what users provider is

$myprovider = Sessions::getUserInfo('providerid');
$myrole = Sessions::getUserInfo('roleid');

if (!empty ($_GET['provider'])) {
	if (has_capability(Sessions::getID(), 'providers:control_all') || has_capability(Sessions::getID(), 'providers:control_subcontractors')) {
		if (has_access_to_provider (Sessions::getID (), $_GET['provider'])) {
			$myprovider = $_GET['provider'];
		}
	}
}


// $users = DB::executeContainedSelect('users_info', array ('UserID', 'loginid as Username', 'fname as Firstname', 'sname as Surname', 'email as Email', 'roleid'), 'roleid>' . $myrole . ' and providerid=' . $myprovider, 'Username');
$users = DB::runSelectQuery('SELECT sb_users_info.UserID, 
	sb_users_info.loginid AS Username, 
	sb_users_info.fname AS Firstname, 
	sb_users_info.sname AS Surname, 
	sb_users_info.email AS Email, 
	sb_roles_types.name AS Role
	FROM sb_users_info INNER JOIN sb_roles_types ON sb_users_info.roleid = sb_roles_types.RoleID
	WHERE sb_users_info.roleid>' . $myrole . ' and sb_users_info.providerid=' . $myprovider . ' ORDER BY Username', true);

$roles = getRolesBelow ($myrole);

$hashedpword = md5 ($CFG->defaultpassword);
//echo $hashedpword;
		
if (!empty ($_POST)) {
	// got any users?
	if (!empty ($_POST['users'])) {
		$hashedpword = md5 ($CFG->defaultpassword);		
		foreach ($_POST['users'] as $user) {
			$reset = DB::executeUpdate('users_info', array ('password'=>$hashedpword), array ('UserID'=>$user), 1);
			$username = DB::executeSelect('users_info', 'loginid', array ('UserID'=>$user));
			
			if ($reset) {
			?>
				<div class="alert sallgood"><?php echo 'Password reset for user "' .  $username['loginid'] . '" (id #' . $user . ')'; ?></div><div style="clear: both;"></div>
			<?php 
			} else {
			?>
				<div class="alert warning"><?php echo 'Password not changed for "' .  $username['loginid'] . '" (id #' . $user . ')';  ?></div><div style="clear: both;"></div>
			<?php
			}
		}
	
	}
}
?>

<div class="annot">
<p>Use this screen to reset passwords for users in your organisation.
</p>
</div>
<div class="subannot_first"></div>
<div class="subannot">
<p>Note: you may only change passwords for users below your current role. For example:</p><ul><li>an advisor may only reset passwords for learners,</li><li>a provider admin may reset passwords for advisors and learners</li></ul><p></p>
</div>

<div id="advisors" class="section" style="width:800px;">
<div class="sectionheader">Users</div>
<div class="sectioncontent">

<div class="subsection">
<h3>All Users</h3>
<div class="subannot_two"></div>
<div class="subannot">
<p>To reset other users' passwords, select the relevant checkboxes in the 'Select' column and click on the 'Reset Password(s)' button below.</p>
<p>All passwords will be reset to <strong><?php echo $CFG->defaultpassword; ?></strong>. Please advise users to change their password when they next log in.</p>
</div>
</div>

<?php if (has_capability(Sessions::getID(), 'providers:control_all') || has_capability(Sessions::getID(), 'providers:change_subcontractors_passwords')) {
	?>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" name="test" id="form3">
	<div>

	<table>
	<tr>
	<td> <label for="provider"><?php echo_string ('PROVIDER'); ?>:</label></td>
	<td>
		 <select name="provider" id="provider">
         
		<?php 
           //TODO - getProviders func?
           
           

                                        if (has_capability(Sessions::getID(), 'providers:control_all')) {
                                        	$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID, visible from sb_providers  group by name ORDER BY visible desc, name asc', true);
                                        } else if (has_capability(Sessions::getID(), 'providers:change_subcontractors_passwords')) {
                                    		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID, visible from sb_providers where superproviderid=' . Sessions::getSuperProviderID (Sessions::getUserInfo ('providerid')) . ' group by name ORDER BY visible desc, name asc', true);
                                       	}
                            		?>
                            		<?php if (count ($providers) > 1) {?>
                            		<!--  <option value="Any">
                                        <?php echo 'Any'; ?>
                                    </option>  -->
                                    <?php } ?>
                                    <?php 
                                       	$selected = $_GET['provider'] ? $_GET['provider'] : Sessions::getUserInfo('providerid') ; // get users actual provider
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
	</table>
	<input type="submit" value="Filter by Provider" name="filter" />
	</div>
	</form>
	<?php } // end of providers capability check ?>
	

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="form2">
<input type="hidden" value="adv" name="form" />
<?php
$dg = new DataGrid ($users, 'UserID');
$dg->setTableID ('advisorstable');
$dg->setTableClass ();
$dg->addHTMLCol('<input name="users[]" value="%s" type="checkbox" />', 'Select');
if ((has_capability(Sessions::getID(), 'users:delete_user') || has_capability(Sessions::getID(), 'users:edit_profile'))) {
	$dg->addHTMLCol('<a href="../user_edit.php?userid=%s">Edit</a>', 'Edit Profile');
}
$dg->removeDisplayField('UserID');
$dg->addAttr('th', 'align', 'left');

$dg->render ();
?>
<input type="submit" value="Reset Password(s)" name="" />


</form>
</div>
</div>
<?php  include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
