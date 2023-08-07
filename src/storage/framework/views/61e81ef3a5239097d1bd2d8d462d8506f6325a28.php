  <div class="alert cookie-alert alert-dismissible" style="display:none">
    <a href="#" class="close close_cookie-alert" data-dismiss="alert" aria-label="close"></a>
    <p>
      <?php echo e(trans('messages.footer.using_cookies',['site_name'=>$site_name])); ?> <a href="<?php echo e(url('privacy_policy')); ?>"><?php echo e(trans('messages.user.privacy')); ?>.</a>
    </p>
  </div>
  <?php echo Html::script('js/jquery-1.11.3.js'); ?>

  <?php echo Html::script('js/jquery-ui.js'); ?>


  <?php echo Html::script('js/angular.js'); ?>

  <?php echo Html::script('js/angular-sanitize.js'); ?>

  <script>
    var app = angular.module('App', ['ngSanitize']);
    var APP_URL = <?php echo json_encode(url('/')); ?>;
    var LOGIN_USER_TYPE = '<?php echo LOGIN_USER_TYPE; ?>';
    var STRIPE_PUBLISH_KEY = "<?php echo e(payment_gateway('publish','Stripe')); ?>";
  </script>

  <?php echo Html::script('js/common.js?v='.$version); ?>

  <?php echo Html::script('js/user.js?v='.$version); ?>

  <?php echo Html::script('js/main.js?v='.$version); ?>

  <?php echo Html::script('js/bootstrap.min.js'); ?>

  <?php echo Html::script('js/jquery.bxslider.min.js'); ?>

  <?php echo Html::script('js/jquery.sliderTabs.min.js'); ?>

  <?php echo Html::script('js/responsiveslides.js?v='.$version); ?>

<!-- validation for popup email -->
 <script src="<?php echo e(asset('admin_assets/plugins/jQuery/jquery.validate.js')); ?>"></script>
  <?php echo $head_code; ?>


  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($map_key); ?>&libraries=places"></script>
  
  <script type="text/javascript">
    if(document.getElementById("map_canvas")){
      google.maps.event.addDomListener(window, 'load', initialize);
    }

    function initialize() {
      var map;
      // Set the latitude & longitude for our location (London Eye)
      var myLatlng = new google.maps.LatLng(51.503454,-0.119562);
      var mapOptions = {
          center: myLatlng, // Set our point as the centre location
          zoom: 14, // Set the zoom level
          mapTypeId: 'roadmap' // set the default map type
      };

      // Create a styled map
      // Create an array of styles.
      var styles = [{
          "stylers": [
              { "saturation": -100 },
              { "lightness": 40 }
          ]
      }];

      // Create a new StyledMapType object, passing it the array of styles,
      // as well as the name to be displayed on the map type control.
      var styledMap = new google.maps.StyledMapType(styles, {name: "Styled Map"});
            
      // Display a map on the page
      map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
      // Allow our satellite view have a tilted display (This only works for certain locations) 
      map.setTilt(45);

      //Associate the styled map with the MapTypeId and set it to display.
      map.mapTypes.set('map_style', styledMap);
      map.setMapTypeId('map_style');
    }

    // <!-- Start Display Cookie Alert in foot -->
    $(document).on('click','.cookie-alert .close_cookie-alert',function() {
        writeCookie('status','1',10);
      })

    var getCookiebyName = function(){
      var pair = document.cookie.match(new RegExp('status' + '=([^;]+)'));
      var result = pair ? pair[1] : 0;  
      $('.cookie-alert').show();
      if(result) {
        $('.cookie-alert').hide();
        return false;
      }
    };

    var url = window.location.href;
    var arr = url.split("/");
    var result = arr[0] + "//" + arr[2];
    var domain =  result.replace(/(^\w+:|^)\/\//, '');

    writeCookie = function(cname, cvalue, days) {
      var dt, expires;
      dt = new Date();
      dt.setTime(dt.getTime()+(days*24*60*60*1000));
      expires = "; expires="+dt.toGMTString();
      document.cookie = cname+"="+cvalue+expires+'; domain='+domain;
    }

    getCookiebyName();
    // <!-- End Display Cookie Alert in foot -->

    /* Loop through all dropdown buttons to toggle between hiding and showing its dropdown content - This allows the user to have multiple dropdowns without any conflict */
    var dropdown = document.getElementsByClassName("social-dropdown-btn");
    var i;

    for(i = 0; i < dropdown.length; i++) {
      dropdown[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var dropdownContent = this.nextElementSibling;
        if(dropdownContent.style.display === "block") {
          dropdownContent.style.display = "none";
        } else {
          dropdownContent.style.display = "block";
        }
      });
    }
</script>

<?php if(Route::current()->uri() == 'trip' || Route::current()->uri() == 'driver_trip' || Route::current()->uri() == 'driver_invoice'): ?>
  <?php echo Html::script('js/trip.js?v='.$version); ?>

<?php endif; ?>
<?php if(Route::current()->uri() == 'driver_payment' || Route::current()->uri() == 'documents/{id}'): ?>
  <?php echo Html::script('js/payment.js?v='.$version); ?>

<?php endif; ?>

<?php if(Route::current()->uri() == 'signup_rider' || Route::current()->uri() == 'signup_driver' || Route::current()->uri() == 'signup_company' || Route::current()->uri() == 'driver_profile'|| Route::current()->uri() == 'profile'): ?>

<script src="<?php echo e(url('js/accountkit.js?v='.$version)); ?>"></script>
<?php endif; ?>

<?php if(Route::current()->uri() == 'signin_rider'): ?>
<script type="text/javascript">
  var GOOGLE_CLIENT_ID  = "<?php echo e(GOOGLE_CLIENT_ID); ?>";
</script>
<script src="https://apis.google.com/js/api:client.js"></script>   
<script src="<?php echo e(url('js/googleapilogin.js?v='.$version)); ?>"></script>
<?php endif; ?>

<?php echo $__env->yieldPushContent('scripts'); ?>
<!-- get in tuch pop_up_email -->
<?php if(CheckGetInTuchpopup()): ?>
  <?php echo Html::script('js/pop_up_email.js?v='.$version); ?>

  <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer>
  </script>
<?php endif; ?>
<!-- get in tuch pop_up_email -->
<?php if(CheckGetInTuchpopup()): ?>
  <?php echo $__env->make('popup_email', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>

<?php if(env('Live_Chat') == "true"): ?>
<script>
  var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
  (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/57223b859f07e97d0da57cae/default';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
  })();
</script>
<?php endif; ?><?php /**PATH /opt/lampp/htdocs/client/gofer_2.5/resources/views/common/foot.blade.php ENDPATH**/ ?>