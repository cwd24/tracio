<?php 
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc 	. '/lib/roles.php');

include_once ($CFG->apploc 	. '/lib/strings.php');
include_strfiles(array ('general'));
?>
<div style="text-align:right; position:relative; left:0px; top:0px;"><a title="TRaCIO Home" href="<?php echo $CFG->apphttp; ?>/home.php"><img src="<?php echo $CFG->apphttp; ?>/images/tracio.png" alt="TRaCIO Home" align="left" /></a><img src="<?php echo ($CFG->imagefolder); ?>/logo.gif" alt="Logo" style="vertical-align: middle" /></div>
<div class="redbar"></div>
<div class="menubar">
	<div id="nav">
	  	<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/about.php"><?php echo_string ('MENU_ABOUT'); ?></a></div>
	  	<?php if (Sessions::checkUserLogIn (false)) { ?>
	        	<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/home.php?r=<?php echo rand (0, 10000); ?>"><?php echo_string ('MENU_HOME'); ?></a></div>
	        <?php if (has_capability (Sessions::getID (), 'reports:view_my_student_results')) {?>
	        	<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/dashboard.php?r=<?php echo rand (0, 10000); ?>"><?php echo_string ('MENU_RESULTS'); ?></a></div>
	        <?php } ?>
	        
	    	<?php if (has_capability(Sessions::getID(), 'reports:view_collated_stats')) { ?>
	    		<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/reports.php?r=<?php echo rand (0, 10000); ?>"><?php echo_string ('MENU_REPORTS'); ?></a></div>
	    	<?php } ?>
	    	
	    	<?php //admin:super_admin?>
	    	<?php if (has_capability(Sessions::getID(), 'admin:super_admin')) { ?>
	    		<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/admin/super_admin.php?r=<?php echo rand (0, 10000); ?>"><?php echo 'Super Admin'; ?></a></div>
	    	<?php } ?>
	    	
	    	<?php if (has_capability(Sessions::getID(), 'user:change_password')) { ?>
	    		<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/profile.php?r=<?php echo rand (0, 10000); ?>"><?php echo_string ('PROFILE'); ?></a></div>
	    	<?php } ?>
	    	
	    	<?php if (has_capability(Sessions::getID(), 'admin:provider_page')) { ?>
	    		<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/admin/provider_management.php?r=<?php echo rand (0, 10000); ?>"><?php echo_string ('MENU_PROVIDER_ADMIN'); ?></a></div>
	    	<?php } ?>
	    
	    	<?php if (has_capability(Sessions::getID(), 'admin:advisor_page')) { ?>
	    		<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/admin/advisor.php?r=<?php echo rand (0, 10000); ?>"><?php echo_string ('MENU_ADVISOR_ADMIN'); ?></a></div>
	    	<?php } ?>
	    	
	    	<?php if (has_capability(Sessions::getID(), 'admin:reset_user_passwords')) { ?>
	    		<?php if (Sessions::getUserInfo('roleid') == $CFG->advisorroleid) { ?>
	    			<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/admin/users.php?r=<?php echo rand (0, 10000); ?>"><?php echo_string ('MENU_PASSWORD_RESET'); ?></a></div>
	    		<?php } else { ?>
	    			<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/admin/users.php?r=<?php echo rand (0, 10000); ?>">User Admin</a></div>
	    		<?php } ?>
	    	<?php } ?>
	    	
	    	<?php if (has_capability(Sessions::getID(), 'users:add_user')) {?>
	    	 	<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/user_signup.php?action=adduser&r=<?php echo rand (0, 10000); ?>"><?php echo 'Add New User'; ?></a></div>
	    	<?php } ?>

	    	<?php if (has_capability(Sessions::getID(), 'users:import')) { ?>
	    		<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/admin/upload_users.php?r=<?php echo rand (0, 10000); ?>">Import Users</a></div>
	    	<?php } ?>
	    	
	    	<div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/login.php?logout=true"><?php echo_string ('MENU_LOGOUT'); ?></a></div>
        <?php } else { // logged out view ?>
	        <div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/login.php"><?php echo_string ('MENU_LOGIN'); ?></a></div>
	        <div class="navlink"><a href="<?php echo $CFG->apphttp; ?>/user_signup.php?r=<?php echo rand (0, 10000); ?>"><?php echo_string ('REGISTER'); ?></a></div>
        <?php }?>
    </div>
	<div class="menubartext"><?php echo_string ('TEXT_SIZE'); ?>: <a href="#" id="txt_small">A</a> | <a href="#" id="txt_norm">A</a> | <a href="#" id="txt_large">A</a> | <a  href="#" id="txt_xlarge">A</a></div>
</div>
<div id="main_content">
