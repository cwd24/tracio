-- MySQL dump 10.13  Distrib 5.5.40, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: tracio
-- ------------------------------------------------------
-- Server version	5.5.40-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `sb_activations`
--

DROP TABLE IF EXISTS `sb_activations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_activations` (
  `ActivationID` int(11) NOT NULL AUTO_INCREMENT,
  `activationcode` tinytext NOT NULL,
  `issuedate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Boolean 1- if activated 0- if not',
  `fname` text NOT NULL,
  `sname` text NOT NULL,
  `email` text NOT NULL,
  `providerid` int(11) NOT NULL,
  `jsondata` text NOT NULL COMMENT 'Potential storage space for form variables - if activation is used for all user types.',
  `emailsent` tinyint(1) NOT NULL DEFAULT '0',
  `roleid` int(11) NOT NULL DEFAULT '40',
  PRIMARY KEY (`ActivationID`)
) ENGINE=MyISAM AUTO_INCREMENT=824 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_activations`
--

LOCK TABLES `sb_activations` WRITE;
/*!40000 ALTER TABLE `sb_activations` DISABLE KEYS */;
/*!40000 ALTER TABLE `sb_activations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_activity_revisions`
--

DROP TABLE IF EXISTS `sb_activity_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_activity_revisions` (
  `RevisionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `delegate` int(10) unsigned NOT NULL,
  `assessmenttype` tinytext NOT NULL,
  `attemptid` int(10) unsigned NOT NULL,
  `question` tinytext NOT NULL COMMENT 'Store as number (1-8)',
  `orig_answer` int(10) unsigned NOT NULL,
  `new_answer` int(10) unsigned NOT NULL,
  `additional` text NOT NULL COMMENT 'Use this field to store any non-standard revisions (e.g. intervention changes, etc.)',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`RevisionID`)
) ENGINE=MyISAM AUTO_INCREMENT=294 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_activity_revisions`
--

LOCK TABLES `sb_activity_revisions` WRITE;
/*!40000 ALTER TABLE `sb_activity_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sb_activity_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_age_groups`
--

DROP TABLE IF EXISTS `sb_age_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_age_groups` (
  `AgeID` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  PRIMARY KEY (`AgeID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_age_groups`
--

LOCK TABLES `sb_age_groups` WRITE;
/*!40000 ALTER TABLE `sb_age_groups` DISABLE KEYS */;
INSERT INTO `sb_age_groups` VALUES (1,'16-18'),(2,'19-24'),(3,'25+');
/*!40000 ALTER TABLE `sb_age_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_centres`
--

DROP TABLE IF EXISTS `sb_centres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_centres` (
  `CentreID` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `providerid` int(11) NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`CentreID`)
) ENGINE=MyISAM AUTO_INCREMENT=251 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_centres`
--

LOCK TABLES `sb_centres` WRITE;
/*!40000 ALTER TABLE `sb_centres` DISABLE KEYS */;
/*!40000 ALTER TABLE `sb_centres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_ethnicity`
--

DROP TABLE IF EXISTS `sb_ethnicity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_ethnicity` (
  `EthnicityID` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`EthnicityID`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_ethnicity`
--

LOCK TABLES `sb_ethnicity` WRITE;
/*!40000 ALTER TABLE `sb_ethnicity` DISABLE KEYS */;
INSERT INTO `sb_ethnicity` VALUES (1,'White: British'),(2,'White: Irish'),(3,'White: Other'),(4,'Mixed: White/Black Caribbean'),(5,'Mixed: White/Black African'),(6,'Mixed: White/Asian'),(7,'Mixed: Other'),(8,'Black or Black British: Caribbean'),(9,'Black or Black British: African'),(10,'Black or Black British: Other'),(11,'Asian or Asian British: Indian'),(12,'Asian or Asian British: Pakistani'),(13,'Asian or Asian British: Bangladeshi'),(14,'Asian or Asian British: Other'),(15,'Chinese or Other Ethnic group: Chinese'),(16,'Chinese or Other Ethnic group: Chinese or Other Ethnic group Any');
/*!40000 ALTER TABLE `sb_ethnicity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_groups`
--

DROP TABLE IF EXISTS `sb_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_groups` (
  `GroupID` int(11) NOT NULL AUTO_INCREMENT,
  `providerid` int(11) NOT NULL,
  `groupname` text NOT NULL,
  `assessorid` int(11) NOT NULL COMMENT 'Tutor - as userid',
  PRIMARY KEY (`GroupID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_groups`
--

LOCK TABLES `sb_groups` WRITE;
/*!40000 ALTER TABLE `sb_groups` DISABLE KEYS */;
INSERT INTO `sb_groups` VALUES (1,1,'Demo Group',38),(2,1,'Chris\'s Group',22);
/*!40000 ALTER TABLE `sb_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_intervention_types`
--

DROP TABLE IF EXISTS `sb_intervention_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_intervention_types` (
  `TypeID` int(4) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  PRIMARY KEY (`TypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_intervention_types`
--

LOCK TABLES `sb_intervention_types` WRITE;
/*!40000 ALTER TABLE `sb_intervention_types` DISABLE KEYS */;
INSERT INTO `sb_intervention_types` VALUES (1,'GOALS'),(2,'Pacific Institute'),(3,'ASDAN'),(4,'Numeracy'),(5,'Literacy'),(100,'Other');
/*!40000 ALTER TABLE `sb_intervention_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_logs`
--

DROP TABLE IF EXISTS `sb_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_logs` (
  `LogID` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `message` text NOT NULL,
  `type` text NOT NULL,
  PRIMARY KEY (`LogID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_logs`
--

LOCK TABLES `sb_logs` WRITE;
/*!40000 ALTER TABLE `sb_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sb_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_programmes`
--

DROP TABLE IF EXISTS `sb_programmes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_programmes` (
  `ProgrammeID` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`ProgrammeID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_programmes`
--

LOCK TABLES `sb_programmes` WRITE;
/*!40000 ALTER TABLE `sb_programmes` DISABLE KEYS */;
INSERT INTO `sb_programmes` VALUES (1,'SkillBuild');
/*!40000 ALTER TABLE `sb_programmes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_providers`
--

DROP TABLE IF EXISTS `sb_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_providers` (
  `ProviderID` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL COMMENT 'College name',
  `contact` text NOT NULL COMMENT 'Userid of primary contact for this provider',
  `sector` tinytext NOT NULL,
  `origid` tinyint(4) NOT NULL,
  `superproviderid` int(10) unsigned NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ProviderID`)
) ENGINE=MyISAM AUTO_INCREMENT=237 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_providers`
--

LOCK TABLES `sb_providers` WRITE;
/*!40000 ALTER TABLE `sb_providers` DISABLE KEYS */;
INSERT INTO `sb_providers` VALUES (223,'Demo Provider','','',0,0,1);
/*!40000 ALTER TABLE `sb_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_questions`
--

DROP TABLE IF EXISTS `sb_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_questions` (
  `QuestionID` int(11) NOT NULL AUTO_INCREMENT,
  `stridentifier` text NOT NULL,
  PRIMARY KEY (`QuestionID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_questions`
--

LOCK TABLES `sb_questions` WRITE;
/*!40000 ALTER TABLE `sb_questions` DISABLE KEYS */;
INSERT INTO `sb_questions` VALUES (1,'Q1'),(2,'Q2'),(3,'Q3'),(4,'Q4'),(5,'Q5'),(6,'Q6');
/*!40000 ALTER TABLE `sb_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_roles_capabilities`
--

DROP TABLE IF EXISTS `sb_roles_capabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_roles_capabilities` (
  `CapabilityID` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` text NOT NULL,
  `name` text NOT NULL,
  PRIMARY KEY (`CapabilityID`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_roles_capabilities`
--

LOCK TABLES `sb_roles_capabilities` WRITE;
/*!40000 ALTER TABLE `sb_roles_capabilities` DISABLE KEYS */;
INSERT INTO `sb_roles_capabilities` VALUES (1,'users:import','Import Users'),(2,'users:add_tutor','Add/Edit Tutor'),(3,'users:add_provider','Add/Edit Provider'),(4,'groups:add_group','Add/Edit Student Group'),(22,'reports:view_comparative_results','View Advisor and Student Comparative Results'),(6,'reports:view_collated_stats','View/Filter Collated Statistics'),(7,'reports:view_student_results','View Student Results'),(8,'users:view_tutor_profile','View Tutor Profile'),(9,'users:edit_profile','Edit Student Profile'),(10,'results:import_results','Import Student Results'),(11,'activity:sit_advisor','Sit Advisor Activity'),(12,'visual:customise_graphs','Customise the Appearance of Graphs'),(13,'visual:customise_appearance','Customise the Appearance of the SkillBuild Tool'),(14,'users:add_user','Add/Edit User (only below current role)'),(15,'groups:assign_groups','Assign Student Groups to User'),(16,'groups:assign_students','Assign Students to Group'),(17,'activity:sit_learner','Sit Learner Activity'),(19,'beta:allow_clear_results','Allow Clear Results'),(20,'reports:view_advisor_results','View Advisor Results'),(21,'admin:provider_page','Access Provider Management Page'),(23,'user:change_password','Change User Password'),(24,'admin:advisor_page','Access Advisor Admin Page'),(25,'beta:pilot_feedback','Pilot Beta Responses Form'),(26,'admin:reset_user_passwords','Reset passwords for other users (below users current role)'),(28,'activity:view_revisions','View Revisions on an Activity'),(29,'users:change_centre','Change User Centre'),(30,'admin:super_admin','Administration of super admin screens.'),(32,'providers:control_subcontractors','Allows super providers to administer subcontractors/subproviders.'),(33,'providers:control_all','Allows administration of all providers (super function).'),(34,'reports:view_my_student_results','View My Own Personal Results'),(35,'users:change_centre_to_any','Change User Centre to Any Location or All'),(36,'users:delete_user','Delete a user from the system (this is terminal).'),(37,'providers:change_subcontractors_passwords','Change passwords of subcontractors.'),(38,'users:archive_user','The ability to archive and unarchive (revive) a learner.'),(39,'profile:set_email_notifications','Enable or disable email notifications for self.');
/*!40000 ALTER TABLE `sb_roles_capabilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_roles_capabilities_assigment`
--

DROP TABLE IF EXISTS `sb_roles_capabilities_assigment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_roles_capabilities_assigment` (
  `AssignmentID` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` int(11) NOT NULL,
  `capabilityid` int(11) NOT NULL,
  `allow` int(1) NOT NULL COMMENT 'Boolean value whether this right is allowed for this user role. 1 - allowed, 0 - disallowed.',
  PRIMARY KEY (`AssignmentID`)
) ENGINE=MyISAM AUTO_INCREMENT=459 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_roles_capabilities_assigment`
--

LOCK TABLES `sb_roles_capabilities_assigment` WRITE;
/*!40000 ALTER TABLE `sb_roles_capabilities_assigment` DISABLE KEYS */;
INSERT INTO `sb_roles_capabilities_assigment` VALUES (42,50,34,1),(43,50,17,1),(334,3,21,1),(333,40,20,1),(332,40,11,1),(451,2,38,1),(330,1,16,1),(329,1,11,0),(353,40,11,1),(357,40,22,1),(358,3,23,1),(356,3,6,1),(354,40,20,1),(387,2,20,1),(351,1,21,1),(350,1,20,1),(349,1,19,1),(401,2,30,0),(347,1,15,1),(346,1,14,1),(345,1,13,1),(344,1,12,1),(343,1,10,1),(342,1,9,1),(341,1,8,1),(340,1,7,1),(339,1,6,1),(338,1,4,1),(337,1,3,1),(336,1,2,1),(335,1,1,1),(359,40,23,1),(360,8,23,1),(361,40,24,1),(362,3,20,1),(363,3,25,1),(364,40,25,1),(365,2,25,1),(366,1,25,1),(367,1,26,1),(368,2,26,1),(369,3,26,1),(370,40,26,1),(371,40,28,1),(413,3,20,1),(373,8,20,1),(374,8,11,1),(375,2,22,1),(376,8,20,1),(377,50,23,1),(378,3,22,1),(379,8,25,1),(380,8,26,1),(381,8,28,1),(382,40,29,1),(383,40,9,1),(384,3,9,1),(385,50,9,0),(386,1,30,1),(388,2,8,0),(389,2,7,1),(390,2,6,1),(391,2,4,1),(392,2,3,1),(393,2,2,1),(394,2,1,1),(395,2,9,1),(396,2,12,1),(397,2,13,1),(398,2,14,1),(399,2,15,1),(400,2,16,1),(402,2,11,0),(403,2,25,1),(404,2,10,1),(405,2,16,1),(406,2,21,1),(407,2,20,1),(408,2,19,1),(453,2,23,1),(410,2,32,1),(411,1,33,1),(412,3,14,1),(416,40,7,1),(452,2,36,1),(417,50,29,1),(418,40,35,1),(419,50,7,0),(420,3,7,1),(421,1,36,1),(422,2,37,0),(423,3,36,1),(424,3,38,1),(425,40,38,0),(426,35,6,1),(427,35,7,1),(428,35,11,1),(429,35,14,1),(430,35,20,1),(431,35,21,1),(432,35,22,1),(433,35,23,1),(434,35,24,1),(435,35,25,1),(436,35,26,1),(437,35,28,1),(438,35,29,1),(439,35,35,1),(440,35,36,1),(441,35,38,1),(442,25,9,1),(443,3,1,1),(444,35,1,1),(445,1,38,1),(446,1,23,1),(447,1,7,1),(450,1,28,1),(449,1,22,1),(454,1,39,1),(455,2,39,1),(456,3,39,1),(457,35,39,1),(458,40,39,0);
/*!40000 ALTER TABLE `sb_roles_capabilities_assigment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_roles_types`
--

DROP TABLE IF EXISTS `sb_roles_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_roles_types` (
  `RoleID` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL COMMENT 'Name of role (e.g. Super Admin, Institutional Admin, etc).',
  `abbrev` text NOT NULL,
  `hierachy` int(11) NOT NULL,
  PRIMARY KEY (`RoleID`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_roles_types`
--

LOCK TABLES `sb_roles_types` WRITE;
/*!40000 ALTER TABLE `sb_roles_types` DISABLE KEYS */;
INSERT INTO `sb_roles_types` VALUES (1,'DfES Admin','DA',1),(2,'Super Provider Admin','SA',2),(3,'Provider Admin','PA',3),(40,'Advisor','A',5),(50,'Learner','L',6),(35,'Advisor Plus','A+',4);
/*!40000 ALTER TABLE `sb_roles_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_sittings`
--

DROP TABLE IF EXISTS `sb_sittings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_sittings` (
  `SittingID` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinytext NOT NULL,
  `sittingnum` tinyint(1) NOT NULL,
  `name` tinytext NOT NULL,
  `url` tinytext NOT NULL,
  PRIMARY KEY (`SittingID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_sittings`
--

LOCK TABLES `sb_sittings` WRITE;
/*!40000 ALTER TABLE `sb_sittings` DISABLE KEYS */;
/*!40000 ALTER TABLE `sb_sittings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_strings`
--

DROP TABLE IF EXISTS `sb_strings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_strings` (
  `StringID` int(11) NOT NULL AUTO_INCREMENT,
  `lang` text NOT NULL COMMENT 'This is ''en'' or ''cy''.',
  `identifier` text NOT NULL COMMENT 'An identifier for the phrase which is used in the PHP code.',
  `phrase` text NOT NULL,
  `translated` tinyint(1) NOT NULL COMMENT 'Has this been translated? Used for doing a search to discover phrases that require translation.',
  `category` text NOT NULL,
  PRIMARY KEY (`StringID`)
) ENGINE=MyISAM AUTO_INCREMENT=268 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_strings`
--

LOCK TABLES `sb_strings` WRITE;
/*!40000 ALTER TABLE `sb_strings` DISABLE KEYS */;
INSERT INTO `sb_strings` VALUES (35,'en','L3_6','Money problems',0,'question'),(36,'en','L3_7','Commitment and motivation (how you feel about the programme)',0,'question'),(37,'en','L3_8','Other issues.  State below',0,'question'),(38,'en','L4_1','I find it hard to get on with people',0,'question'),(226,'en','L7_THEME_SUB','How I behave when in a group',0,'question'),(39,'en','L4_2','I get on quite well with people I know',0,'question'),(225,'en','L6_THEME_SUB','What I think about myself',0,'question'),(40,'en','L4_3','I get on well with people I know',0,'question'),(224,'en','L5_THEME_SUB','Getting involved with new activites',0,'question'),(41,'en','L4_4','I get on quite well with lots of different people in situations I am familiar with',0,'question'),(223,'en','L4_THEME_SUB','Getting involved with other people',0,'question'),(42,'en','L4_5','I find it really easy to get on very well with all sorts of people, even in situations that are new to me',0,'question'),(43,'en','L5_1','I avoid trying new things',0,'question'),(44,'en','L5_2','I\'ll only try something new if I think I can do it',0,'question'),(45,'en','L5_3','I\'ll try something new if I have some help',0,'question'),(117,'en','A1_QUESTION','Barriers to Participation and the Act of Participating',0,'question'),(118,'en','A1_THEME','Attendance',0,'question'),(119,'en','A2_QUESTION','Barriers to Participation and the Act of Participating',0,'question'),(120,'en','A2_THEME','Time Keeping',0,'question'),(121,'en','A3_QUESTION','Would any of the following issues affect attendance? Tick all that apply',0,'question'),(122,'en','A3_THEME','Attendance and Time Keeping',0,'question'),(123,'en','A4_QUESTION','Getting Involved with Other People',0,'question'),(46,'en','L5_4','I enjoy trying out something new if it comes my way',0,'question'),(47,'en','L5_5','I am always on the look out for something new to try',0,'question'),(48,'en','L6_1','I\'m not good at anything',0,'question'),(49,'en','L6_2','I\'m not too bad at some things',0,'question'),(264,'en','MENU_PROVIDER_ADMIN','Provider Admin',0,'general'),(50,'en','L6_3','I think I am quite an average sort of person',0,'question'),(265,'en','ADMIN_HOME_TEXT','Please select from the above menu.',0,'general'),(51,'en','L6_4','I know what I\'m good at',0,'question'),(52,'en','L6_5','I know what I\'m good at and know how to improve myself',0,'question'),(266,'en','ANY','Any',0,'general'),(53,'en','L7_1','I tend to disrupt the group',0,'question'),(54,'en','L7_2','I usually keep quiet and don\'t join in',0,'question'),(55,'en','L7_3','I join in a little and will do what I\'m asked',0,'question'),(259,'en','ACTIVATION_FOUND','Activation code found.',0,'user'),(56,'en','L7_4','I make suggestions and offer to take on various tasks',0,'question'),(258,'en','ACTIVATION_FAIL','User already signed up or invalid activation code.',0,'user'),(57,'en','L7_5','I often lead the group and encourage everybody to join in',0,'question'),(256,'en','YOUR_LEARNERS','Your Learners',0,'general'),(58,'en','L8_1','I do what I want to, without considering anybody else',0,'question'),(59,'en','L8_2','I try to take other people into consideration sometimes',0,'question'),(60,'en','L8_3','I try to be considerate and fair to other people most of the time',0,'question'),(255,'en','A_ALL_COMPLETE','All advisor sittings are complete for user %s.',0,'general'),(61,'en','L8_4','I always act considerately and fairly to everybody all of the time',0,'question'),(254,'en','ACCESS_DENIED','Access denied',0,'general'),(62,'en','L8_5','I treat people considerately and will stand up for others if necessary',0,'question'),(241,'en','ABOUT_TEXT','<h1>Introduction to the tool</h1>\n<p>The Welsh Assembly Government measures provider performance based on the achievement of qualifications. However, some learning programmes give rise to additional benefits which cannot always be measured in this way.  These include \'soft skills\' such as confidence, self-esteem, motivation, ability to co-operate, self-discipline and wellbeing.</p>  \n<p>The Assembly has funded the development of a web-based tool which providers can use to record learners\' \'soft skills\' outcomes in a systematic way.</p>\n<p>The tool is spilt into two sections:</p>\n<p>(i) Learners self-assess themselves against a series of statements relating to the following themes at the start, the mid-point and the end of their learning:</p>\n<ul>\n<li>Attendance</li>\n<li>Timekeeping</li>\n<li>Relationships</li>\n<li>Engagement</li>\n<li>Self-esteem</li>\n<li>Working with others</li>\n<li>Beliefs and responsibilites</li>\n</ul>\n<p>Once the learner completes each session, they are informed to arrange a planning session with their tutor.</p>\n<p>(ii) The tutor then completes an assessment of the learner against a series of statements relating to the same themes at the same points in learning.</p>',0,'general'),(63,'en','L1_QUESTION','Please select one answer that best describes your attendance on the programme',0,'question'),(64,'en','L1_THEME','Attendance',0,'question'),(65,'en','L2_QUESTION','Please select one answer that best describes your time keeping on the programme',0,'question'),(66,'en','L2_THEME','Time Keeping',0,'question'),(67,'en','L3_QUESTION','Would any of the following issues affect your punctuality and attendance?  You can tick more than one',0,'question'),(68,'en','L3_THEME','Attendance and Time Keeping',0,'question'),(69,'en','L4_QUESTION','Please select one answer that best describes how you get on with people (adults and friends)',0,'question'),(70,'en','L4_THEME','Relationships',0,'question'),(71,'en','L6_QUESTION','Please select one answer that best describes what you think of yourself',0,'question'),(72,'en','L6_THEME','Self Esteem',0,'question'),(73,'en','L7_QUESTION','Please select one answer that best describes your behaviour when working in a group',0,'question'),(267,'','','',0,''),(74,'en','L7_THEME','Working with Others',0,'question'),(75,'en','L8_QUESTION','Please select one answer that best describes your values and standards',0,'question'),(76,'en','L8_THEME','My Beliefs',0,'question'),(77,'en','L5_QUESTION','Please select one answer that best describes how you feel when you have to take part in a new activity',0,'question'),(78,'en','L5_THEME','Engagement',0,'question'),(1,'en','WAG','The Welsh Assembly Government',1,'general'),(2,'cy','WAG','Llywodraeth Cynulliad Cymru',1,'question'),(17,'en','TEXT_SIZE','Text Size',0,'general'),(19,'en','LEARNER','Learner',0,'general'),(20,'en','L1_1','Poor (I rarely attend)',0,'question'),(21,'en','L1_2','I attend less than 3 days a week',0,'question'),(22,'en','L1_3','I attend 3 or more days a week',0,'question'),(23,'en','L1_4','I sometimes take a day off if I don\'t feel like coming in',0,'question'),(24,'en','L1_5','I always attend and let people know if I am going to be absent',0,'question'),(25,'en','L2_1','I am always late',0,'question'),(26,'en','L2_2','I am sometimes late as I have trouble getting up in time',0,'question'),(27,'en','L2_3','I am sometimes late due to personal problems',0,'question'),(28,'en','L2_4','I am sometimes running late but I let people know',0,'question'),(29,'en','L2_5','I am never late',0,'question'),(30,'en','L3_1','I am a carer (child, family or other)',0,'question'),(31,'en','L3_2','Doctor\'s or hospital appointment',0,'question'),(32,'en','L3_3','Accessing support (drug or alcohol programmes, etc.)',0,'question'),(33,'en','L3_4','Meeting support workers (social workers, etc.)',0,'question'),(34,'en','L3_5','Transport problems',0,'question'),(79,'en','A1_1','Poor - rarely attends',0,'question'),(253,'en','USER_BREAKDOWN','User Breakdown',0,'reports'),(80,'en','A1_2','Attends less than 3 days a week',0,'question'),(81,'en','A1_3','Attends 3 or more days a week',0,'question'),(82,'en','A1_4','Attends regularly but takes odd days off (unauthorised)',0,'question'),(83,'en','A1_5','Attends regularly and informs why absent',0,'question'),(84,'en','A3_1','Carer (child / family / other)',0,'question'),(85,'en','A3_2','Medical (doctor\'s and/or hospital appointments)',0,'question'),(86,'en','A3_3','Accessing support (drug and/or alcohol misuse, etc.)',0,'question'),(87,'en','A3_4','Support Worker (Social Worker / Youth Worker / Youth Offending Service)	',0,'question'),(88,'en','A3_5','Transport problems',0,'question'),(89,'en','A3_6','Money problems',0,'question'),(90,'en','A3_7','Commitment and motivation (how they feel about the programme)',0,'question'),(91,'en','A3_8','Other issues. State below:',0,'question'),(92,'en','A4_1','Finds it hard to get on with people',0,'question'),(240,'en','ACTIVITY_TYPE','Activity Type',0,'general'),(93,'en','A4_2','Gets on <strong>quite</strong> well with people he/she knows',0,'question'),(94,'en','A4_3','Gets on <strong>very</strong> well with people he/she knows',0,'question'),(95,'en','A4_4','Gets on well with lots of different people in situations they are familiar with',0,'question'),(96,'en','A4_5','Finds it really easy to get on with all sorts of people, even in situations that are new to them',0,'question'),(97,'en','A5_1','Avoids trying new things',0,'question'),(98,'en','A5_2','Only tries something new if they think they can do it',0,'question'),(99,'en','A5_3','Will try something new if help is provided',0,'question'),(100,'en','A5_4','Enjoys trying out something new if the opportunity arises',0,'question'),(101,'en','A5_5','Is always on the lookout for something new to try',0,'question'),(102,'en','A6_1','Are not good at anything',0,'question'),(103,'en','A6_2','Are not too bad at some things',0,'question'),(104,'en','A6_3','Are quite an average sort of person',0,'question'),(105,'en','A6_4','Know what they are good at',0,'question'),(106,'en','A6_5','Know what they are good at and how to improve',0,'question'),(107,'en','A7_1','Tends to disrupt the group',0,'question'),(108,'en','A7_2','Usually keeps quiet and does not join in',0,'question'),(109,'en','A7_3','Joins in occasionally and will do what they are asked to',0,'question'),(110,'en','A7_4','Makes suggestions and offers to take on various tasks',0,'question'),(111,'en','A7_5','Often leads the group and encourages others to take part',0,'question'),(245,'en','CENTRE','Centre',0,'general'),(112,'en','A8_1','Do what they want without any consideration for others',0,'question'),(251,'en','CRITERIA_NUM_FOUND','%s user(s) were found matching the criteria',0,'reports'),(252,'en','DT_IS','Distance travelled is',0,'reports'),(113,'en','A8_2','Take other people into consideration sometimes',0,'question'),(114,'en','A8_3','Be considerate and fair to other people most of the time',0,'question'),(250,'en','CRITERIA_NONE_FOUND','No users were found matching the criteria',0,'reports'),(115,'en','A8_4','Act considerately and fairly to everybody all of the time',0,'question'),(116,'en','A8_5','I treat people considerately and will stand up for others if necessary',0,'question'),(248,'en','FILTER_CRITERIA','Filter Criteria',0,'reports'),(249,'en','FILTER_SEARCH_FEEDBACK','You searched for all users matching the following criteria',0,'reports'),(247,'en','DATE_RANGE','Date range',0,'general'),(243,'en','APP_NAME_LONG','Taking Responsibility and Capturing Individual Outcomes',0,'general'),(234,'en','A5_THEME_SUB','Presents as someone who believes that they',0,'question'),(235,'en','A8_THEME_SUB','The Learner Appears to',0,'question'),(236,'en','A7_THEME_SUB','Behaviour in a Group',0,'question'),(237,'en','A6_THEME_SUB','Presents as Someone who Believes that they',0,'question'),(239,'en','COMBO_ALL','Any',0,'general'),(229,'en','A2_3','Sometimes late due to personal problems',0,'question'),(230,'en','A2_4','Sometimes late but informs why',0,'question'),(231,'en','A2_5','Never late',0,'question'),(232,'en','A3_THEME_SUB','Getting Involved with Other People',0,'question'),(233,'en','A4_THEME_SUB','Getting Involved with New Activities',0,'question'),(124,'en','A4_THEME','Relationships',0,'question'),(125,'en','A5_QUESTION','Getting Involved with New Activities',0,'question'),(126,'en','A5_THEME','Engagement',0,'question'),(127,'en','A6_QUESTION','Presents as Someone who Believes that they',0,'question'),(128,'en','A6_THEME','Self Esteem',0,'question'),(129,'en','A7_QUESTION','Behaviour in a Group',0,'question'),(130,'en','A7_THEME','Working with Others',0,'question'),(131,'en','A8_QUESTION','The Learner Appears to',0,'question'),(132,'en','A8_THEME','Beliefs and Responsibilities',0,'question'),(238,'en','MENU_REPORTS','Reports',0,'general'),(133,'en','IV_NONE','No interventions have been undertaken.',0,'interventions'),(134,'en','APP_NAME','TRaCIO',0,'general'),(135,'en','IV','Interventions',0,'interventions'),(136,'en','IV_SINCE_LAST','Which of the following intervention activities has %s completed since the last sitting?',0,'interventions'),(137,'en','IV_PROCEED','Proceed to Activity',0,'interventions'),(138,'en','A_ACTIVITY','Advisor Activity',0,'question'),(139,'en','L_ACTIVITY','Learner Activity',0,'question'),(140,'en','ACTIVITY_COMPLETE','Well done, you have completed the activity. You now need to have a planning session with your tutor.',0,'question'),(141,'en','ACTIVITY_ALREADY_COMPLETE','You have already completed this assessment.',0,'question'),(142,'en','APP_WELCOME','Welcome to the %s tool',0,'general'),(143,'en','COMM','Commencement',0,'general'),(144,'en','MID','Mid-Point',0,'general'),(145,'en','COMP','Completion',0,'general'),(146,'en','LOGIN_TITLE','Login Screen',0,'general'),(147,'en','LOGIN_FAIL','Login Failed! Please check your username and password.',0,'general'),(148,'en','USERNAME','Username',0,'general'),(149,'en','PASSWORD','Password',0,'general'),(150,'en','LOGIN_SUCCESS','You are already logged in. <a href=\"home.php\">Click here to go home</a>.',0,'general'),(151,'en','LOGIN_BTN','Login',0,'general'),(152,'en','WELCOME','Welcome',1,'general'),(153,'cy','WELCOME','Croeso',0,'general'),(154,'en','MENU_ABOUT','About',0,'general'),(155,'en','MENU_HOME','Home',0,'general'),(156,'en','MENU_RESULTS','My Results',0,'general'),(157,'en','MENU_PROFILE','My Profile',0,'general'),(158,'en','MENU_CONTACTS','My Contacts',0,'general'),(159,'en','MENU_LOGOUT','Logout',0,'general'),(160,'en','MENU_LOGIN','Login',0,'general'),(161,'en','DB_DISTANCE','Distance Travelled',0,'reports'),(162,'en','ADVISOR','Advisor',0,'general'),(163,'en','JS_DISABLED','Your browser does not support JavaScript. Please enable JavaScript for full reports.',0,'general'),(164,'en','USER_INFO','User Information',0,'general'),(165,'en','ASSESSOR','Assessor',0,'general'),(166,'en','INSTITUTION','Institution',0,'general'),(167,'en','PRG_TYPE','Programme Type',0,'general'),(168,'en','AGE_GROUP','Age Group',0,'general'),(169,'en','IV_ALL','All Interventions',0,'interventions'),(170,'en','USER_ANSWERS','User Answers',0,'reports'),(171,'en','NONE_SPEC','None Specified',0,'reports'),(172,'en','REPORTS','Reports',0,'reports'),(173,'en','SITTING','Sitting',0,'general'),(174,'en','ADD_USER','Add a User',0,'user'),(175,'en','FN','First Name(s)',0,'user'),(176,'en','SN','Surname',0,'user'),(177,'en','EMAIL','Email',1,'user'),(178,'cy','EMAIL','Ebost',1,'user'),(179,'en','COMBO_SELECT','Please Select',0,'general'),(180,'en','PROVIDER','Provider',0,'general'),(181,'en','DATE_START','Start Date',0,'general'),(182,'en','DATE_END','End Date',0,'general'),(183,'en','DATE_MM','MM',0,'general'),(184,'en','DATE_YYYY','YYYY',0,'general'),(185,'en','GENDER','Gender',0,'general'),(186,'en','MALE','Male',0,'user'),(187,'en','FEMALE','Female',0,'user'),(188,'en','ETHNICITY','Ethnicity',0,'user'),(189,'en','SUBMIT','Submit',0,'general'),(190,'en','FORM_INCOMPLETE','Form incomplete',0,'general'),(191,'en','PASSWORD_CHOOSE','Choose a password',0,'user'),(192,'en','PASSWORD_RETYPE','Retype password',0,'user'),(193,'en','LENGTH_CRITERIA_MIN','Must be at least %s characters long',0,'user'),(194,'en','PASSWORD_MATCH_FAIL','Passwords do not match',0,'user'),(195,'en','TERMS_ETC','I agree to the Terms of Use and Privacy Policy.',0,'general'),(196,'en','PASSWORD_MATCH','Passwords match',0,'user'),(197,'en','USERNAME_AVAIL','Username is available.',0,'user'),(198,'en','USERNAME_AVAIL_FAIL','Username is already taken. Please try another.',0,'user'),(199,'en','LOGGED_IN','You are already logged in.',0,'user'),(200,'en','PASSWORD_SHORT','Password is too short.',0,'user'),(201,'en','USERNAME_SHORT','Username is too short.',0,'user'),(202,'en','REGISTER','Register',0,'general'),(203,'en','USER_SIGNUP','If you do not yet have a user account, <a href=\"%s\">register here</a>',0,'user'),(204,'en','USER_SIGNUP_TITLE','New User Registration',0,'user'),(205,'en','EMAIL_INVALID_FORMAT','Please enter a valid email address',0,'user'),(206,'en','EMAIL_UNAVAIL','This email address is already registered',0,'user'),(207,'en','USERNAME_INVALID_FORMAT','Invalid format for username. Please use only alphabetical and numeric characters only.<br/>Length between 6 and 20 characters.',0,'user'),(208,'en','LENGTH_CRITERIA_MAX','Must be less than %s characters long',0,'user'),(209,'en','USERNAME_LONG','Username is too long',0,'user'),(210,'en','FIELD_LENGTH_LONG','%s is too long',0,'user'),(211,'en','FIELD_LENGTH_SHORT','%s is too short',0,'user'),(212,'en','IMG_LOGO_SMALL','logo_small.gif',0,'general'),(213,'en','IMG_LOGO_BIG','wag_logo.gif',0,'general'),(214,'en','IMG_Q1','1.gif',0,'question'),(215,'en','IMG_Q2','2.gif',0,'question'),(216,'en','IMG_Q3','3.gif',0,'question'),(217,'en','IMG_Q4','4.gif',0,'question'),(218,'en','IMG_Q5','5.gif',0,'question'),(219,'en','IMG_Q6','6.gif',0,'question'),(220,'en','IMG_Q7','7.gif',0,'question'),(221,'en','IMG_Q8','8.gif',0,'question'),(222,'en','A2_1','Always late',0,'question'),(227,'en','L8_THEME_SUB','What I think about my own responsibilities',0,'question'),(228,'en','A2_2','Sometimes late as cannot get up in time',0,'question');
/*!40000 ALTER TABLE `sb_strings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_super_providers`
--

DROP TABLE IF EXISTS `sb_super_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_super_providers` (
  `SuperProviderID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `providerid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`SuperProviderID`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_super_providers`
--

LOCK TABLES `sb_super_providers` WRITE;
/*!40000 ALTER TABLE `sb_super_providers` DISABLE KEYS */;
INSERT INTO `sb_super_providers` VALUES (17,'Demo Super Provider',223);
/*!40000 ALTER TABLE `sb_super_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_user_interventions`
--

DROP TABLE IF EXISTS `sb_user_interventions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_user_interventions` (
  `InterventionID` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `typeid` int(11) NOT NULL,
  `datestart` datetime NOT NULL,
  `dateend` datetime NOT NULL,
  `sitting` int(11) NOT NULL,
  `other` varchar(1000) DEFAULT NULL COMMENT 'For adding an ''''other'''' option if relevant. TypeID needs to be 100 (''''Other'''') for this to be set and displayed.',
  PRIMARY KEY (`InterventionID`)
) ENGINE=MyISAM AUTO_INCREMENT=14396 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_user_interventions`
--

LOCK TABLES `sb_user_interventions` WRITE;
/*!40000 ALTER TABLE `sb_user_interventions` DISABLE KEYS */;
INSERT INTO `sb_user_interventions` VALUES (706,1425,1,'0000-00-00 00:00:00','0000-00-00 00:00:00',1,NULL);
/*!40000 ALTER TABLE `sb_user_interventions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_users_attempt`
--

DROP TABLE IF EXISTS `sb_users_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_users_attempt` (
  `AttemptID` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sitting` tinyint(1) NOT NULL COMMENT 'What number attempt is this for the user?',
  `assessmenttype` tinytext NOT NULL COMMENT 'Is this with the tutor or alone? Store as tutor OR self?',
  `delegate` int(11) NOT NULL COMMENT 'User id of user who undertook the activity on the users behalf. Used for updates and advisor id storage.',
  PRIMARY KEY (`AttemptID`)
) ENGINE=MyISAM AUTO_INCREMENT=42494 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_users_attempt`
--

LOCK TABLES `sb_users_attempt` WRITE;
/*!40000 ALTER TABLE `sb_users_attempt` DISABLE KEYS */;
INSERT INTO `sb_users_attempt` VALUES (3710,1425,'2011-06-17 10:13:22',1,'l',0),(3711,1425,'2011-06-17 10:13:46',2,'l',0),(3717,1425,'2011-06-20 09:12:12',1,'a',0);
/*!40000 ALTER TABLE `sb_users_attempt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_users_attempt_answers`
--

DROP TABLE IF EXISTS `sb_users_attempt_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_users_attempt_answers` (
  `answersID` int(11) NOT NULL AUTO_INCREMENT,
  `attemptid` int(11) NOT NULL COMMENT 'Unique ID for Table',
  `q1` tinyint(2) NOT NULL,
  `q2` tinyint(2) NOT NULL,
  `q3` tinyint(2) NOT NULL,
  `q4` tinyint(2) NOT NULL,
  `q5` tinyint(2) NOT NULL,
  `q6` tinyint(2) NOT NULL,
  `q7` tinyint(2) NOT NULL,
  `q8` tinyint(2) NOT NULL,
  PRIMARY KEY (`answersID`)
) ENGINE=MyISAM AUTO_INCREMENT=41402 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_users_attempt_answers`
--

LOCK TABLES `sb_users_attempt_answers` WRITE;
/*!40000 ALTER TABLE `sb_users_attempt_answers` DISABLE KEYS */;
INSERT INTO `sb_users_attempt_answers` VALUES (2618,3710,2,2,0,3,2,4,5,4),(2619,3711,4,4,0,4,4,5,2,3),(2625,3717,4,2,0,4,3,4,5,4);
/*!40000 ALTER TABLE `sb_users_attempt_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_users_attempt_answers_attendances`
--

DROP TABLE IF EXISTS `sb_users_attempt_answers_attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_users_attempt_answers_attendances` (
  `AttendancesID` int(11) NOT NULL AUTO_INCREMENT,
  `answersid` int(11) NOT NULL,
  `chk1` tinyint(1) NOT NULL,
  `chk2` tinyint(1) NOT NULL,
  `chk3` tinyint(1) NOT NULL,
  `chk4` tinyint(1) NOT NULL,
  `chk5` tinyint(1) NOT NULL,
  `chk6` tinyint(1) NOT NULL,
  `chk7` tinyint(1) NOT NULL,
  `other` text NOT NULL,
  `attemptid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`AttendancesID`)
) ENGINE=MyISAM AUTO_INCREMENT=41402 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_users_attempt_answers_attendances`
--

LOCK TABLES `sb_users_attempt_answers_attendances` WRITE;
/*!40000 ALTER TABLE `sb_users_attempt_answers_attendances` DISABLE KEYS */;
INSERT INTO `sb_users_attempt_answers_attendances` VALUES (2618,2618,0,0,0,1,1,0,0,'',0),(2619,2619,0,0,0,0,0,1,0,'',0),(2625,2625,0,0,0,0,1,0,0,'',0);
/*!40000 ALTER TABLE `sb_users_attempt_answers_attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_users_confirmation`
--

DROP TABLE IF EXISTS `sb_users_confirmation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_users_confirmation` (
  `ConfirmationID` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `code` text NOT NULL COMMENT 'Unique guid created registration code which will be provided in the email.',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `valid` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ConfirmationID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_users_confirmation`
--

LOCK TABLES `sb_users_confirmation` WRITE;
/*!40000 ALTER TABLE `sb_users_confirmation` DISABLE KEYS */;
/*!40000 ALTER TABLE `sb_users_confirmation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_users_info`
--

DROP TABLE IF EXISTS `sb_users_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_users_info` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique id counter',
  `loginid` text NOT NULL COMMENT 'Login username for users',
  `password` text NOT NULL COMMENT 'md5 hashed password',
  `fname` text NOT NULL,
  `sname` text NOT NULL,
  `email` text NOT NULL,
  `roleid` int(11) NOT NULL,
  `providerid` int(16) NOT NULL,
  `ethnicityid` int(11) NOT NULL,
  `gender` tinytext NOT NULL,
  `ageid` int(11) NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `groupid` int(11) NOT NULL,
  `centreid` int(11) NOT NULL,
  `programmeid` int(11) NOT NULL,
  `registerdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `advisorid` int(11) NOT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=MyISAM AUTO_INCREMENT=1531 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_users_info`
--

LOCK TABLES `sb_users_info` WRITE;
/*!40000 ALTER TABLE `sb_users_info` DISABLE KEYS */;
INSERT INTO `sb_users_info` VALUES (3,'learner','5f4dcc3b5aa765d61d8327deb882cf99','Sc','Temp','learner@rsc-wales.ac.uk',5,223,1,'m',2,0,1,0,1,'2011-06-17 10:06:23',0),(2,'advisor','5f4dcc3b5aa765d61d8327deb882cf99','Sc','Advisor','advisor@rsc-wales.ac.uk',4,223,1,'m',2,0,1,0,1,'2011-06-17 10:58:59',0),(1,'admin','5f4dcc3b5aa765d61d8327deb882cf99','Admin','Admin','admin@rsc-wales.ac.uk',1,223,1,'f',2,0,1,0,1,'2014-11-19 16:40:29',0);
/*!40000 ALTER TABLE `sb_users_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_users_learner_assignment`
--

DROP TABLE IF EXISTS `sb_users_learner_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_users_learner_assignment` (
  `AssignmentID` int(11) NOT NULL AUTO_INCREMENT,
  `advisorid` int(11) NOT NULL,
  `learnerid` int(11) NOT NULL,
  `enabled` int(1) NOT NULL COMMENT '1-enabled, 0-disabled',
  PRIMARY KEY (`AssignmentID`)
) ENGINE=MyISAM AUTO_INCREMENT=16174 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_users_learner_assignment`
--

LOCK TABLES `sb_users_learner_assignment` WRITE;
/*!40000 ALTER TABLE `sb_users_learner_assignment` DISABLE KEYS */;
INSERT INTO `sb_users_learner_assignment` VALUES (1043,1426,1425,1);
/*!40000 ALTER TABLE `sb_users_learner_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sb_users_preferences`
--

DROP TABLE IF EXISTS `sb_users_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sb_users_preferences` (
  `preferenceID` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `pref` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`preferenceID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sb_users_preferences`
--

LOCK TABLES `sb_users_preferences` WRITE;
/*!40000 ALTER TABLE `sb_users_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `sb_users_preferences` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-20 16:04:08
