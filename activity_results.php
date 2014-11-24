<?php
include_once ('config.php');
include_once ($CFG->apploc  . '/templates/config.php');
include_once ($CFG->apploc  . '/classes/log.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/lib/stats.php');
include_once ($CFG->apploc  . '/classes/datagrid.php');
include_once ($CFG->apploc  . '/classes/graph.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
Sessions::checkUserLogIn ();

$sitting = !empty ($_GET['sitting'])?$_GET['sitting']:1;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo ($CFG->appname); ?>: Results</title>
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
	
	
	<style>
	table {
	/* border: thin;
	 border-color: black;
	 border-style: solid; */
		}
		
	</style>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>


                    	
</head>
    <body>
		<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
            <div class="results">
				<?php
				
				$userdata = DB::runSelectQuery('SELECT sb_providers.name AS providername, 
	sb_users_info.loginid, 
	sb_users_info.name, 
	sb_groups.groupname, 
	sb_groups.assessorid,
	sb_users_info.userid,
	sb_programmes.name as prgname, 
	sb_age_groups.name as agegroup,
	(select sb_users_info.name from sb_users_info where sb_groups.assessorid = sb_users_info.Userid) as assessorname
FROM sb_users_info INNER JOIN sb_providers ON sb_users_info.providerid = sb_providers.ProviderID
	 INNER JOIN sb_groups ON sb_users_info.groupid = sb_groups.GroupID
	 INNER JOIN sb_programmes ON sb_users_info.programmeid = sb_programmes.ProgrammeID
	 INNER JOIN sb_age_groups ON sb_users_info.ageid = sb_age_groups.AgeID
WHERE sb_users_info.UserID =' . Sessions::getID());
				
				if (!$userdata) {
					die ("Couldn't access user data."  . mysql_error());
				}
								
                ?>
            	<fieldset>
                	<legend><?php echo $userdata['name']; ?> (<?php echo $userdata['loginid']; ?>)</legend>
                    <p>
                    	Date of Sittings: 25/10/2008 | 10/03/2009 | 16/06/2009
                    </p>
                    <p>
                    	Assessor name: <?php echo $userdata['assessorname']; ?> | Institution: <?php echo $userdata['providername']; ?> | Program type: <?php  echo $userdata['prgname']; ?> | Age group: <?php echo $userdata['agegroup']; ?>
                    </p>
                    <p>
                    	<?php displayStatsNew ('l', Sessions::getID()); ?>
                    	<!-- <img src="images/1f9d580129eccdc50d494ed32cc52b6e.png"/>  -->
                    	Distance Travelled: <?php echo getDistanceTravelled (Sessions::getID(), 'l'); ?>%
                    	
                    </p>
                    <hr />
                    
                    	<?php displayStatsNew ('a', Sessions::getID()); ?>
                    	<?php $dist = getDistanceTravelled (Sessions::getID(), 'a', 'full'); ?>
                    	<p>Initial Score: <?php echo $dist['initialscore'] . ' / ' . $dist['totalscore']; ?></p>
                    	<p>Mid Score: <?php echo $dist['midscore'] . ' / ' . $dist['totalscore']; ?></p>
                    	<p>Final Score: <?php echo $dist['finalscore'] . ' / ' . $dist['totalscore']; ?></p>
              			<p>Distance Travelled: <?php echo $dist['distanceperc']; ?>%</p>
                    
                </fieldset>
                
            
            </div>
           
         
            
        <?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
    </body>
</html>
