<?php






$dir = __DIR__ . '/api';
include $dir . '/auth.php';
include $dir . '/admin.php';
include $dir . '/learner.php';
include $dir . '/employer.php';
include $dir . '/instructor.php';
include $dir . '/miscellaneous.php';

//MISC________________

//Get all courses
//Learner get a specific course
//Get all Jobs
//Learner get a specific job


//ADMIN________________

//admin dashboard==== 
//admin course management - with stats
// admin add new course - assign it to an instructor
//admin logs - with stats
//admin users update status (activate | suspend)
//admin send bulk emails to users
//admin approves instructor

//LEARNER______________


//Learner view Jobs - with Match Score - with Partial Match
//Learner Dashboard
//Learner Save Job
//Learner Apply for a Job
//Learner enroll new course
//Learner continue reading inside the course
//Learner gets to see progress
//Learner get Badges 
//Learner View Badges
//Learner get certificate on complete
//Learner get list of certificates
//Learner view a certificate
//Learner copy certificate link
//Learner share certificate link on linkedIN
//Learner downloads the certificate via pdf

//INSTRUCTOR
//Instructor applies for the role
//admin approves/rejects on the instructor


//EMPLOYER
//Employer registers account and register company
//hr dashboard api - self jobs - team training process - top matched candidates
//employer get list of matched candidates - with filter skills
//employer add /post a job
//manage team dashboard
//invite team in bulk emails
//create a new team group
//assign courses to individual or team group
//track progress on team or inidividual


//project mangt dashboard
//with list of projects
//proposal management