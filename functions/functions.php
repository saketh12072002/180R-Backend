<?php
// Clean String Values
function clean ($string)
{
return htmlentities($string);
}
// Redirection
function redirect($location)
{
return header("location:{$location}");
}
// Set Session Message
function set_message($msg)
{
if(!empty($msg))
{
$_SESSION['Message'] = $msg;
}
else
{
$msg="";
}
}
// Display Message Function
function display_message()
{
if(isset($_SESSION['Message']))
{
echo $_SESSION['Message'];
unset($_SESSION['Message']);
}
}
// Generate Token
function Token_Generator()
{
$token = $_SESSION['token']=md5(uniqid(mt_rand(),true));
return $token;
}
// Send Email Function
function send_email($email,$sub,$msg,$header)
{
return mail($email,$sub,$msg,$header);
}
//***********User Validation Functions********** */
// Errors Function
function Error_validation($Error)
{
return '<div class="alert alert-danger">'.$Error.'</div>';
}
// User Validation Function
function user_validation()
{
if($_SERVER['REQUEST_METHOD']=='POST')
{
$FirstName = clean($_POST['FirstName']);
$LastName = clean($_POST['LastName']);
$UserName = clean($_POST['UserName']);
$Email = clean($_POST['Email']);
$Pass = clean($_POST['pass']);
$CPass = clean($_POST['cpass']);
$Errors = [];
$Max = 20;
$Min = 03;
// Check the First Name Characters
if(strlen($FirstName)<$Min)
{
$Errors[]= " First Name Cannot Be Less Than {$Min} Characters ";
}
// Check the First Name Characters
if(strlen($FirstName)>$Max)
{
$Errors[]= " First Name Cannot Be More Than {$Max} Characters ";
}
// Check the Last Name Characters
if(strlen($LastName)<$Min)
{
$Errors[]= " Last Name Cannot Be Less Than {$Min} Characters ";
}
// Check the Last Name Characters
if(strlen($LastName)>$Max)
{
$Errors[]= " Last Name Cannot Be More Than {$Max} Characters ";
}
// Check the Users Characters
if(!preg_match("/^[a-zA-Z,0-9]*$/",$UserName))
{
$Errors[]= " User Name Cannot Be Accept Those Characters ";
}
// Check the Email Exists
if(Email_Exists($Email))
{
$Errors[]= " Email Already Registered ! ";
}
// Check the User Name Exists
if(User_Exists($UserName))
{
$Errors[]= " User Name Already Registered ! ";
}
// Password & Confirm Password
if($Pass!=$CPass)
{
$Errors[]= " Password Not Matched ! ";
}
if(!empty($Errors))
{
foreach($Errors as $Error)
{
echo Error_validation($Error);
}
}
else
{
if(user_registration($FirstName,$LastName,$UserName,$Email,$Pass))
{
set_message('<p class="bg-success text-center lead">You Have Successfully Registered Please Check Your Activation Link</p>');
redirect("index.php");
}
else
{
set_message('<p class="bg-danger text-center lead"> Your Account Not Registered Please Try Again </p>');
redirect("index.php");
}
}
}
}
// Email Exists Function
function Email_Exists($email)
{
$sql = " select * from users where Email='$email'";
$result = Query($sql);
if(fatech_data($result))
{
return true;
}
else
{
return false;
}
}
// User Exists Function
function User_Exists($user)
{
$sql = " select * from users where UserName='$user'";
$result = Query($sql);
if(fatech_data($result))
{
return true;
}
else
{
return false;
}
}
// User Registration Function
function user_registration($FName,$LName,$UName,$Email,$Pass)
{
$FirstName = escape($FName);
$LastName = escape($LName);
$UserName = escape($UName);
$Email = escape($Email);
$Pass = escape($Pass);
if(Email_Exists($Email))
{
return true;
}
else if(User_Exists($UserName))
{
return true;
}
else
{
$Password = md5($Pass);
$Validation_code = md5($UserName + microtime());
$sql = "insert into users (FirstName,LastName,UserName,Email,Password,Validation_Code,Active) values ('$FirstName','$LastName','$UserName','$Email','$Password','$Validation_code','0')";
$result = Query($sql);
confirm($result);
$subject = " Active Your Account ";
$msg = " Please Click the Link Below to Active Your Account http://localhost/loginpro/activate.php?Email=$Email&Code=$Validation_code";
$header = "From No-Reply admin@onlineittuts.com";
send_email($Email,$subject,$msg,$header);
return true;
}
}
//Activation Function
function activation()
{
if($_SERVER['REQUEST_METHOD']=="GET")
{
$Email = $_GET['Email'];
$Code = $_GET['Code'];
$sql = " select * from users where Email='$Email' AND Validation_Code='$Code'";
$result = Query($sql);
confirm($result);
if(fatech_data($result))
{
$sqlquery = " update users set Active='1', Validation_Code='0' where Email='$Email' AND Validation_Code='$Code'";
$result2 = Query($sqlquery);
confirm($result2);
set_message('<p class="bg-success text-center lead"> Your Account Successfully Activated </p>');
redirect('login.php');
}
else
{
echo '<p class="bg-danger text-center lead"> Your Account  Not Activated </p>';
}
}
}
///User Login Validation Function
function login_validation()
{
$Errors = [];
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
$UserEmail = clean($_POST['UEmail']);
$UserPass = clean($_POST['UPass']);
$Remember = isset($_POST['remember']);
if(empty($UserEmail))
{
$Errors[] = " Please Enter Your Email ";
}
if(empty($UserPass))
{
$Errors[] = " Please Enter Your Password ";
}
if(!empty($Errors))
{
foreach ($Errors as $Error)
{
echo Error_validation($Error);
}
}
else
{
if(user_login($UserEmail,$UserPass,$Remember))
{
redirect("admin.php");
}
else
{
echo Error_validation(" Please Enter Correct Email or Password");
}
}
}
}
// User Login Function
function user_login($UEmail,$UPass,$Remember)
{
$query = "select * from users where Email='$UEmail' and Active='1'";
$result = Query($query);
if($row=fatech_data($result))
{
$db_pass = $row['Password'];
if(md5($UPass)==$db_pass)
{
if($Remember == true)
{
setcookie('email',$UEmail, time() + 86400);
}
$_SESSION['Email']=$UEmail;
return true;
}
else
{
return false;
}
}
}
//Logged in Function
function logged_in()
{
if(isset($_SESSION['Email']) || isset($_COOKIE['email']))
{
return true;
}
else
{
return false;
}
}
/////////////Recover Function///////////////////
function recover_password()
{
if($_SERVER['REQUEST_METHOD'] == "POST")
{
if(isset($_SESSION['token']) && $_POST['token'] == $_SESSION['token'])
{
$Email = $_POST['UEmail'];
if(Email_Exists($Email))
{
$code = md5($Email+microtime());
setcookie('temp_code',$code,time()+300);
$sql = "update users set Validation_Code='$code' where Email='$Email'";
Query($sql);
$Subject = " Please Reset the Password ";
$Message = "Please Follow on Below Link to Reset the Password '<b>{$code}</b>' http://localhost/loginpro/code.php?Email='$Email'&Code='$code'";
$header = "noreply@onlineittuts.com";
if(send_email($Email,$Subject,$Message,$header))
{
echo '<div class="alert alert-success"> Please Check Your Email :) </div>';
}
else
{
echo Error_validation(" We Coudn't Send an Email ");
}
}
else
{
echo Error_validation(" Email Not Found....");
}
}
else
{
redirect("index.php");
}
}
}
/// Validation Code Function
function validation_code()
{
if(isset($_COOKIE['temp_code']))
{
if(!isset($_GET['Email']) && !isset($_GET['Code']))
{
redirect("index.php");
}
else if(empty($_GET['Email']) && empty($_GET['Code']))
{
redirect("index.php");
}
else
{
if(isset($_POST['recover-code']))
{
$Code = $_POST['recover-code'];
$Email = $_GET['Email'];
$query = "select * from users where Validation_Code='$Code' and Email='$Email'";
$result = Query($query);
if(fatech_data($result))
{
setcookie('temp_code',$Code, time()+300);
redirect("reset.php?Email=$Email&Code=$Code");
}
else
{
echo Error_validation(" Your Code is Wrong :) ");
}
}
}
}
else
{
set_message('<div class="alert alert-danger"> Your Code Has Been Expired :) </div>');
redirect("recover.php");
}
}
///////////////Reset Password Function//////////////////////
function reset_password()
{
if(isset($_COOKIE['temp_code']))
{
if(isset($_GET['Email']) && isset($_GET['Code']))
{
if(isset($_SESSION['token']) && isset($_POST['token']))
{
if($_SESSION['token'] == $_POST['token'])
{
if($_POST['reset-pass'] === $_POST['reset-c-pass'])
{
$Password = md5($_POST['reset-pass']);
$query2 = "update users set Password='".$Password."', Validation_Code=0 where Email='".$_GET['Email']."'";
$result = Query($query2);
if($result)
{
set_message('<div class="alert alert-success"> Your Password Has Been Updated : )</dvi>');
redirect("login.php");
}
else
{
set_message('<div class="alert alert-danger"> Something Went Wrong :) </dvi>');
}
}
else
{
set_message('<div class="alert alert-danger"> Password Not Matched :) </dvi>');
}
}
}
}
else
{
set_message('<div class="alert alert-danger> Your Code or Your Email Has Not Matched :) </dvi>');
}
}
else
{
set_message('<div class="alert alert-danger> Your Time Period Has Been Expired </dvi>');
}
}
?>