TRaCIO
======
TRaCIO is a web-based tool that enables learning providers to measure 'soft skills' such as confidence, self-esteem, motivation, ability to co-operate, self-discipline and wellbeing.

The tool is split into two sections:

1. Learners self-assess themselves against a series of statements relating to the following themes at the start, the mid-point and the end of their learning:
Attendance
Timekeeping
Relationships
Participation
Self-esteem
Working with others
Beliefs and responsibilities
Once the learner completes each session, they are informed to arrange a planning session with their tutor.

2. The tutor then completes an assessment of the learner against a series of statements relating to the same themes at the same points in learning.

The TRaCIO tool is written in PHP and use a MySQL Database backend.  It was originally developed to support multiple learning providers, but is now available as an Open Source package.

Disclaimer: The current maintainers of this github repository are not the original programmer(s), but the maintainers have attempted to sanitise the code.   The code as per the GPL License, comes with no warranty, the owners of the Intellectual Property have given appropriate authorisation for the code to be released in this way.

An overview of installing tracio is as follows;

1. Download Tracio preferably via Git
   git clone https://github.com/rscwales/tracio 
   git submodule update --init
  Alternatively, download the zip file, noting that you will need to download the zip file for each repository in external/
2. Unpack the above into a folder in your webserver document root.
3. Create a MySQL Database and import the file doc/tracio.sql (If you have an existing Tracio SQL backup you should import that instead)
4. Update config.php and db_config.php
5. Test
6. You may wish to consider a nightly SQL dump of the database and appropriate backups of the webserver document root.

