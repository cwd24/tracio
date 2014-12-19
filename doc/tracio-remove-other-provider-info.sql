-- This script can be used to remove all data not belonging to a provider or superprovider, in order to provide the data as a dump
-- Change the following line with the superprovider or provider ids that you want to keep
--DELETE FROM sb_providers WHERE providerid != 25 AND superproviderid NOT IN (SELECT superproviderid FROM sb_super_providers WHERE providerid = 25);
DELETE FROM sb_providers WHERE providerid != 219;

-- Remove data from tables based on providerid
DELETE FROM sb_activations WHERE providerid NOT IN (SELECT providerid FROM sb_providers);
DELETE FROM sb_centres WHERE providerid NOT IN (SELECT providerid FROM sb_providers);
DELETE FROM sb_users_info WHERE providerid NOT IN (SELECT providerid FROM sb_providers);
DELETE FROM sb_super_providers WHERE providerid NOT IN (SELECT providerid FROM sb_providers);


-- Remove data from tables based on userid
DELETE FROM sb_activity_revisions WHERE userid NOT IN (SELECT userid FROM sb_users_info);
DELETE FROM sb_user_interventions WHERE userid NOT IN (SELECT userid FROM sb_users_info);
DELETE FROM sb_users_attempt WHERE userid NOT IN (SELECT userid FROM sb_users_info);
DELETE FROM sb_users_confirmation WHERE userid NOT IN (SELECT userid FROM sb_users_info);
DELETE FROM sb_users_learner_assignment WHERE learnerid NOT IN (SELECT userid FROM sb_users_info);
DELETE FROM sb_users_preferences WHERE userid NOT IN (SELECT userid FROM sb_users_info);

-- Remove attempted answers data
DELETE FROM sb_users_attempt_answers WHERE attemptid NOT IN (SELECT attemptid FROM sb_users_attempt);
DELETE FROM sb_users_attempt_answers_attendances WHERE answersid NOT IN (SELECT answersid FROM sb_users_attempt_answers);
