<?php
defined('MOODLE_INTERNAL') || die();

$string['addmember'] = 'Add a member';
$string['addpeer'] = 'Add peer...';
$string['addpeergroup'] = 'Add a peer group';
$string['after'] = 'After';
$string['aftergrade'] = 'After grade';
$string['afterlabel'] = 'After';
$string['aftermarks'] = 'After scores';
$string['afterpeer'] = 'After - peer';
$string['afterself'] = 'After - self';
$string['afterteacher'] = 'After - teacher';
$string['aftervideo'] = 'After video';
$string['allowstudentpeerselection'] = 'Allow students to select peers';
$string['allowstudentpeerselection_help'] = 'If enabled, students can select peer partners by themselves.';
$string['allowstudentupload'] = 'Students can upload videos';
$string['allowstudentupload_help'] = 'If enabled, students can upload before/after videos one by one. Bulk upload is available only for teachers.';
$string['allscores'] = '</span><span class="red">Self,</span> <span class="blue">Peer,</span> <span class="green">Teacher,</span> <span class="orange"> and Class</span> Scores';
$string['assess'] = 'Assess';
$string['assessagain'] = 'Assess again';
$string['assessedby'] = 'Assessed by';
$string['assignpeers'] = 'Assign peers';
$string['assignpeersrandomly'] = 'Assign peers randomly';
$string['associate'] = 'Associate';
$string['associated'] = 'Associated';
$string['associations'] = 'Associations';
$string['availabledate'] = 'Available from';
$string['backupdefaults'] = 'Backup defaults';
$string['backupusers'] = 'Include user data';
$string['backupusersdesc'] = 'Sets the default for whether to include user data (videos and grades) in backups.';
$string['before'] = 'Before';
$string['beforeafter'] = 'Before/after';
$string['beforegrade'] = 'Before grade';
$string['beforelabel'] = 'Before';
$string['beforemarks'] = 'Before scores';
$string['beforepeer'] = 'Peer';
$string['beforeself'] = 'Self';
$string['beforeteacher'] = 'Teacher';
$string['beforeclass'] = 'Class';
$string['beforevideo'] = 'Before video';
$string['bulkvideoupload'] = 'Bulk video upload';
$string['confirmdeletegrade'] = 'Are you sure you want to delete this grade?';
$string['confirmdeletevideos'] = 'Are you sure you want to delete {$a} videos?';
$string['course'] = 'course';
$string['currentgrade'] = 'Current grade in gradebook';
$string['delayedteachergrade'] = 'Delayed teacher grade';
$string['delayedteachergrade_help'] = 'If enabled, teacher assessments will not be displayed to students until they assess themselves.';
$string['deleteselectedvideos'] = 'Delete selected videos';
$string['deletevideo'] = 'Delete video';
$string['deletevideos'] = 'Bulk video deletion'; // 英和辞典でもen.wiktionaryでもdeleteに一般的な名詞用例は載ってないが、deleteを名詞的に使っている用例はいくらでもあるし視認性がいいからbulk video deleteとする分には歓迎。ただここでは原則的な名詞形にしておく
$string['deletevideos_videos'] = 'Videos';
$string['deletevideos_videos_help'] = 'All selected videos are deleted from the activity. Video data on the server will be cleaned up by Moodle cron.';
$string['description'] = 'Description';
$string['disassociate'] = 'Disassociate';
$string['diskspacetmpl'] = 'Server disk space: {$a->free} free / {$a->total} total';
$string['downloadexcel'] = 'Download results in Excel';
$string['duedate'] = 'Due date';
$string['errorcheckvideostodelete'] = 'Check videos to delete.';
$string['errorinvalidtiming'] = 'Invalid timing value';
$string['erroruploadvideo'] = 'Please upload a video';
$string['existingcourse'] = 'Publish to an existing course';
$string['existingcourse_help'] = 'If set to other than (New), videos will be published to the selected course. You need to be able to add resources to the course.';
$string['feedback'] = 'Feedback';
$string['feedbackfrom'] = 'Feedback from {$a}';
$string['ffmpegcommand'] = 'FFmpeg command';
$string['ffmpegcommanddesc'] = 'FFmpeg command line with placeholders: {INPUT} {OUTPUT}';
$string['ffmpegthumbnailcommand'] = 'FFmpeg thumbnail command';
$string['ffmpegthumbnailcommanddesc'] = 'FFmpeg command line with placeholders: {INPUT} {OUTPUT}, with options to output an image';
$string['filedeleted'] = 'File is deleted.';
$string['firstassess'] = 'First assess';
$string['grade'] = 'Grade';
$string['group'] = 'group';
$string['inputnewcoursename'] = 'Input a new course name';
$string['level'] = 'Level';
$string['liststudents'] = 'List students';
$string['loading'] = 'Loading...';
$string['managegrades'] = 'Manage grades';
$string['manageuploadedvideos'] = 'Manage uploaded videos';
$string['modulename'] = 'Video Assessment';
$string['modulenameplural'] = 'Video Assessments';
$string['mp4boxcommand'] = 'MP4Box command';
$string['mp4boxcommanddesc'] = 'MP4Box command which enables progressive playback of MP4 videos';
$string['myvideos'] = 'My video';
$string['nopeergroup'] = 'No peer groups yet';
$string['notext'] = 'No text';
$string['novideo'] = 'No video';
$string['operations'] = 'Operations';
$string['or'] = 'or';
$string['originalname'] = 'Original name';
$string['path'] = 'Path';
$string['peer'] = 'Peer';
$string['peerassessments'] = 'Peer assessments';
$string['peergroup'] = 'Peer group';
$string['peerratings'] = 'Peer ratings';
$string['peers'] = 'Peers';
$string['pluginadministration'] = 'Video Assessment administration';
$string['pluginname'] = 'Video Assessment';
$string['preventlate'] = 'Prevent late submissions';
$string['previewvideo'] = 'Preview video';
$string['printrubrics'] = 'Print all rubric report';
$string['printreport'] = 'Print report';
$string['printview'] = 'Open print view';
$string['publishvideos'] = 'Publish videos';
$string['publishvideos_videos'] = 'Videos';
$string['publishvideos_videos_help'] = 'Selected videos will be published to an existing course or a new course.';
$string['publishvideostocourse'] = 'Publish videos to a course';
$string['ratingpeer'] = 'Peer weighting';
$string['ratingpeer_help'] = 'The weighting of peer gradings of a student\'s total grade.';
$string['ratings'] = 'Ratings';
$string['ratingself'] = 'Self weighting';
$string['ratingself_help'] = 'The weighting of the self grading of a student\'s total grade.';
$string['ratingteacher'] = 'Teacher weighting';
$string['ratingteacher_help'] = 'The weighting of the teacher grading of a student\'s total grade.';
$string['reallydeletevideo'] = 'Are you sure you want to delete this video?';
$string['reallyresetallpeers'] = 'This will reset peer assignments and re-assign randomly. Continue?';
$string['remark'] = 'Remark';
$string['report'] = 'Report';
$string['retakevideo'] = 'Retake a video';
$string['reuploadvideo'] = 'Re-upload a video';
$string['score'] = 'Score';
$string['scores'] = 'Scores';
$string['saveassociations'] = 'Save associations';
$string['seereport'] = 'See report';
$string['self'] = 'Self';
$string['selfassessments'] = 'Self assessments';
$string['selfratings'] = 'Self ratings';
$string['settotalratingtoahundredpercent'] = 'Four ratings (Teacher + Self + Peer + Class) must equal 100%.';
$string['singlevideoupload'] = 'Single video upload';
$string['studentrubric'] = 'Student rubric';
$string['submissionby'] = 'Submission by {$a}';
$string['takevideo'] = 'Take a video';
$string['teacher'] = 'Teacher';
$string['teacherratings'] = 'Teacher ratings';
$string['teacherrubric'] = 'Teacher rubric';
$string['teacherselfpeer'] = 'Teacher/self/peer/class';
$string['timing'] = 'Timing';
$string['timinggrade'] = '{$a} grade';
$string['timinglabel'] = 'Your word for before/after';
$string['timinglabel_help'] = 'By inputting a word here, you can customize the labels for "before" and "after." If this is left blank, standard "before" and "after" are used.';
$string['timingscores'] = '{$a} scores';
$string['total'] = 'Total';
$string['totalgrade'] = 'Total grade';
$string['unassociated'] = 'Unassociated';
$string['upload'] = 'Upload';
$string['uploadedat'] = 'Uploaded at';
$string['uploadedtime'] = 'Uploaded time';
$string['uploadingvideo'] = 'Uploading video';
$string['uploadvideo'] = 'Upload a video';
$string['uploadvideos'] = 'Upload videos';
$string['usedpeers'] = 'Number of peer assessments';
$string['usedpeers_help'] = 'The number of peer assessments for each student.';
$string['video'] = 'Video';
$string['videoalreadyassociated'] = '{$a} has been already associated with a video.';
$string['videoassessment:addinstance'] = 'Add a new video assessment';
$string['videoassessment:associate'] = 'Associate bulk uploaded videos with users';
$string['videoassessment:bulkupload'] = 'Bulk upload videos';
$string['videoassessment:exportownsubmission'] = 'Export own submission';
$string['videoassessment:grade'] = 'Grade video assessment';
$string['videoassessment:gradepeer'] = 'Grade peer video assessment';
$string['videoassessment:submit'] = 'Submit video assessment';
$string['videoassessment:view'] = 'View video assessment';
$string['videoassessmentname'] = 'Video assessment name';
$string['videoformat'] = 'Video format';
$string['videoformatdesc'] = 'Video format';
$string['videos'] = 'Videos';
$string['viewassessmentsofmyvideo'] = 'View assessments of my video';
$string['viewassociatedvideos'] = 'View associated videos';
$string['weighting'] = 'Weighting';
$string['xfeedback'] = '{$a} feedback';
$string['xunassignedstudents'] = '{$a} unassigned students';
/**
 * Le Xuan Anh Version2
 */
$string['grade'] = 'Grading';
$string['managevideo'] = 'Manage videos';
$string['class'] = 'Class';
$string['open'] = 'Open Class Grading';
$string['close'] = 'Close Class Grading';
$string['classassessments'] = 'Class Assessments';

/* MinhTB VERSION 2 */
$string['allparticipants'] = 'All participants';
$string['assignclass'] = 'Assign class';
$string['sortid'] = 'Sort by ID';
$string['sortname'] = 'Sort by name';
$string['sortmanually'] = 'Sort manually';
$string['sortby'] = 'Sort by';
$string['order'] = 'Order';
$string['save'] = 'Save';
$string['orderasc'] = 'Ascending';
$string['orderdesc'] = 'Descending';
$string['namesort'] = 'First name / Surname';
$string['title'] = 'Title';
$string['groupname'] = 'Group name';
$string['existingcourseornewcourse'] = 'Publish to an existing course<br /> or a new course';
$string['insertintosection'] = 'Insert into section';
$string['addprefixtolabel'] = 'Add prefix to label name';
$string['addsuffixtolabel'] = 'Add suffix to label name';
$string['inputnewcourseshortname'] = 'Input a new course short name';
$string['courseshortnameexist'] = 'Short name is already used for another course';
$string['pleasechoosevideos'] = 'Please choose videos';