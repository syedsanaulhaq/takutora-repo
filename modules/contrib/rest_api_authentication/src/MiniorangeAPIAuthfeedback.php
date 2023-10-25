<?php
namespace Drupal\rest_api_authentication;
use Drupal\rest_api_authentication\utilities;

class MiniorangeAPIAuthfeedback{

public static function rest_api_authentication_feedback_form(){

      
    global $base_url;
    $feedback_url = $base_url.'/feedback';
    $_SESSION['mo_other'] = 'True';
    $form_id = $_POST['form_id'];
    $form_token = $_POST['form_token'];
    $admin_email=Utilities::getCustomerEmail();
?>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=PT+Serif" rel="stylesheet">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<style>

 .container{
    font-family: sans-serif;
 }
/* h4.modal-title {
    font-size: 18px;
}
 p {
    padding-top: 20px;
    text-align: left;
    margin-left: 10px;
}*/
input#rest_feedback_email {
    margin-left: 7px;
    width: 125%;
}
/* .modal-header {
    margin-bottom: -11px;
}  */
 .rest_loader {
            margin: auto;
            display: block;
            border: 5px solid #f3f3f3; /* Light grey */
            border-top: 5px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
        }
 
 @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
 </style>
 <script>
         $(document).ready(function () {
            $("#myModal").modal({
                backdrop: 'dynamic',
                keyboard: TRUE
            });
        });

        $(function () {
              $(".button").click(function () {
                  document.getElementById('rest_loader').style.display = 'block';
                  var reason = $("input[name='performance']:checked").val();
                  var q_feedback = document.getElementById("sso_feedback").value;
                  return false;
              });
          })

 </script>
</head>
<body>
 <div class="container" style="background: rgba(0, 0, 0, 0.1);width:100%;" >
    <div class="modal_fade" id="myModal"  role="dialog"  >
        <div class="modal-dialog" style="width: 500px;" role="dialog" >
            <div class="modal-content" style="border-radius: 20px;" >
                <div class="modal-header" style="padding: 25px; border-top-left-radius: 20px; border-top-right-radius: 20px; background-color: #8fc1e3;">
                        <h4 class="modal-title" style="color: white; text-align: center;">Hey, it seems like you want to deactivate Rest API Authentication Module</h4>
                        <hr>
						<h4 style="text-align: center; color: white;" class="modal-title">What happened?</h4>
                </div>
            <div class="modal-body" style="font-size: 11px; padding-right: 25px; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; background-color: #ececec;">
                 <form action="<?php echo $feedback_url; ?>" id="restapi_feedback">
                    <div>
                                <p>
                                    <?php
                                    if(empty(\Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_admin_email'))) { ?>
                                    <br><label style="font-size: 12.5px">Email ID:<label>&nbsp;&nbsp;&nbsp;&nbsp;<input onblur="validateEmail(this)" class="form-control"
                                        type="email" id="rest_feedback_email" required value= <?php echo $admin_email; ?>
                                        name="rest_feedback_email"/>
                                    <p style="display: none;color:red" id="email_error">Invalid Email</p>
                                    <?php
                                    } ?>
                                    <br>
                                    <?php
                                    $deactivate_reasons = array(
                                        t("Not Working"),
                                        t("Basic Authentication Not Working"),
                                        t("API key Authentication Not Working"),
                                        t("Does not have the features I'm looking for"),
                                        t("Confusing interface"),
                                        t("Bugs in the module"),
                                        t("Other reasons: "),
                                    );
                                    foreach ($deactivate_reasons as $deactivate_reasons) {
                                        ?>
                                     <div  class="radio" style="padding:2px;font-size: 8px;text-align:left">
                                            <label style="font-weight:normal;font-size:14.6px;color:maroon;" for="<?php echo $deactivate_reasons; ?>">
                                                <input type="radio" name="query" value="<?php echo $deactivate_reasons;?>" required>
                                                <?php echo $deactivate_reasons; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                <input type="hidden" name="form_token" value=<?php echo $form_token ?> >
                                <input type="hidden" name="form_id" value= <?php echo $form_id ?>>
                                <br>
                                <textarea class="form-control" id="query_feedback" name="query_feedback" rows="4" cols="50" style="margin-left:2%;text-align:left" placeholder="Write your query here"></textarea>
                                <br><br>
                                <div class="mo2f_modal-footer" style="margin-bottom: 5% !important;">
                                    <input type="submit" id="submit_button" name="rest_feedback_submit" class="button btn btn-primary" value="Submit and Continue" style=" display: block; font-size: 11px;float: left; margin-left: 21px;margin-bottom: 15%;" />
                                    <input type="submit" formnovalidate="formnovalidate" style="margin: auto; display: block; font-size: 11px; float: right;" name="rest_feedback_skip" class="btn btn-link" value="Skip" />
                                </div>
                                <div class="rest_loader" id="rest_api_feedback" style="display: none;"></div>
                                <?php
                                echo "<br><br>";
                                foreach($_POST as $key => $value) {
                                  self::hiddenRestapifields($key,$value);
                                }
                                ?>
                    </div>
                  </form>        
                </div>
             
          </div>  
       </div>         
    </div>
   </div>
  </div> 
</body>
</html>
<?php 
exit;

  }

  public static function hiddenRestapiFields($key,$value)
  {
    $hiddenRestapiField = "";
    $value2 = array();
    if(is_array($value)) {
      foreach($value as $key2 => $value2)
      {
        if(is_array($value2)){
          self::hiddenRestapiFields($key."[".$key2."]",$value2);
        } else {
          $hiddenRestapiField = "<input type='hidden' name='".$key."[".$key2."]"."' value='".$value2."'>";
        }
      }
    }else{
      $hiddenRestapiField = "<input type='hidden' name='".$key."' value='".$value."'>";
    }

    echo $hiddenRestapiField;
  }
}

