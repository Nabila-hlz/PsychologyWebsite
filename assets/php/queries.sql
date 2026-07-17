-- search functionalities 
-- Get all psychologists
SELECT * FROM users WHERE role = 'therapist';
-- psychologist of special specialty 
SELECT * FROM users WHERE role = 'therapist' and specialty;


-- new user 
-- Register new patient
INSERT INTO `user` (`first name`, `last name`, `username`, `email`, `password_hash`, `phoneNB`, `role`, `created_at`, `gender`)
VALUES ('John', 'Doe', 'johndoe', 'john@email.com', 'hashed_password', '+213555123456', 'patient', NOW(), 'male');
/*
queries :
TABLE USER 
$user_id = "SELECT * FROM USER where user_id "; 
$FirstName = "SELECT * FROM USER where FirstName "; 
$FirstName = "SELECT * FROM USER where FirstName"; 
$username = "SELECT * FROM USER where username "; 
$email = "SELECT * FROM USER where email "; 
$password_hash  = "SELECT * FROM USER where password_hash "; 
$phoneNB = "SELECT * FROM USER where phoneNB "; 
$role = "SELECT * FROM USER where role "; 
$created_at = "SELECT * FROM USER where created_at "; 
$gender = "SELECT * FROM USER where gender "; 

------------------------------------------------------------------
TABLE CONTENT 
$content_id = "SELECT * FROM CONTENT where content_id "; 
$type = "SELECT * FROM CONTENT where type "; 
$title = "SELECT * FROM CONTENT where title "; 
$discription = "SELECT * FROM CONTENT where discription "; 
$path/url = "SELECT * FROM CONTENT where path/url"; 
$created_at = "SELECT * FROM CONTENT where created_at "; 
$therapistID = "SELECT * FROM CONTENT where therapistID "; 
$categoryID = "SELECT * FROM CONTENT where categoryID "; 

----------------------------------------------------------------

TABLE SESSION 
$session_id = "SELECT * FROM SESSION where session_id "; 
$patient_id = "SELECT * FROM SESSION where patient_id"; 
$therapist_id = "SELECT * FROM SESSION where therapist_id "; 
$status = "SELECT * FROM SESSION where status "; 
date = "SELECT * FROM SESSION where date"; 
$created_at = "SELECT * FROM SESSION where created_at "; 
$reason = "SELECT * FROM SESSION where reason "; 


----------------------------------------------------------------

CONTENT THERAPIST 
$therapist_id= "SELECT * FROM THERAPIST where therapist_id";
$session_price = "SELECT * FROM THERAPIST where session_price";
$payment_ref  = "SELECT * FROM THERAPIST where payment_ref ";
$bio = "SELECT * FROM THERAPIST where bio ";
$certificate = "SELECT * FROM THERAPIST where certificate ";
$cv = "SELECT * FROM THERAPIST where cv ";
photo = "SELECT * FROM THERAPIST where photo";

---------------------------------------------------------------
CONTENT PAYMENT 
$therapist_id= "SELECT * FROM PAYMENT where payment_id";
$session_price = "SELECT * FROM PAYMENT where amount";
$payment_ref  = "SELECT * FROM PAYMENT where status ";
$bio = "SELECT * FROM PAYMENT where created_at ";
$certificate = "SELECT * FROM PAYMENT where sessionID ";

-----------------------------------------------------------------
TABLE REVIEW 
$review_id = "SELECT * FROM REVIEW where review_id"; 
$user_id = "SELECT * FROM REVIEW where  user_id"; 
$comment = "SELECT * FROM REVIEW where comment "; 
$created_at  = "SELECT * FROM REVIEW where created_at "; 

-------------------------------------------------------------------
TABLE SPECIALITY
$speciality_id = "SELECT * FROM SPECIALITY where speciality_id "; 
$speciality_id = "SELECT * FROM SPECIALITY where speciality_name "; 

--------------------------------------------------------------------
TABLE SPECIALITY_THERAPIST
$therapist_id = "SELECT * FROM THERAPIST where therapist_id  "; 
$speciality_id = "SELECT * FROM THERAPIST where speciality_id "; 

------------------------------------------------------------------------

TABLE AVAILABILITY
$therapist_id = "SELECT * FROM AVAILABILITY where therapist_id  "; 
$day = "SELECT * FROM COAVAILABILITYNTENT where day "; 
*/