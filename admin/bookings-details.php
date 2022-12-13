<?php
add_action( 'admin_menu', function() {
    add_submenu_page(
        null,
        esc_html__( 'Eventer Booking Info', 'eventer' ),
        esc_html__( 'Eventer Booking Info', 'textdomain' ),
        'manage_options',
        'eventer-booking-info',
        'eventer_booking_details'
    );
} );

function eventer_booking_details(){
    echo '<style>
    body {font-family: Arial;}
    
    /* Style the tab */
    .tab {
      overflow: hidden;
      border: 1px solid #ccc;
      background-color: #f1f1f1;
    }
    
    /* Style the buttons inside the tab */
    .tab button {
      background-color: inherit;
      float: left;
      border: none;
      outline: none;
      cursor: pointer;
      padding: 14px 16px;
      transition: 0.3s;
      font-size: 17px;
    }
    
    /* Change background color of buttons on hover */
    .tab button:hover {
      background-color: #ddd;
    }
    
    /* Create an active/current tablink class */
    .tab button.active {
      background-color: #ccc;
    }
    
    /* Style the tab content */
    .tabcontent {
      display: none;
      padding: 6px 12px;
      border: 1px solid #ccc;
      border-top: none;
    }
    
    /* Style the close button */
    .topright {
      float: right;
      cursor: pointer;
      font-size: 28px;
    }
    
    .topright:hover {color: red;}
    </style>';
    $registrant = (isset($_REQUEST['registrant']))?$_REQUEST['registrant']:'';
    if($registrant){
        $get_details = eventer_get_registrant_details('id', $registrant);
        $user_details = unserialize($get_details->user_details);
        $user_details = array_column($user_details, 'value', 'name');
        $settings = $get_details->user_system;
        $settings = unserialize($settings);
        $payment = $get_details->paypal_details;
        $payment = unserialize($payment);
        $tickets_booked = $get_details->tickets;
        $tickets_booked = unserialize($tickets_booked);
        $new_booked = [];
        if($tickets_booked){
            foreach($tickets_booked as $tbook){
                if(isset($tbook['number']) && $tbook['number']>0){
                    $new_booked[$tbook['name']] = $tbook['number'];
                }
            }
        }
        $tickets_booked = $new_booked;
        $username = $get_details->username;
        $email = $get_details->email;
        echo '<h2>Booking Details</h2>
        <p>Below are the details of registration ID: '.$get_details->id.' and the username is '.$get_details->username.'</p>
        
        <div class="tab">
        <button class="tablinks" onclick="openCity(event, \'London\')" id="defaultOpen">Details</button>
        <button class="tablinks" onclick="openCity(event, \'Paris\')">Tickets Booked</button>
        <button class="tablinks" onclick="openCity(event, \'Tokyo\')">Payment Details</button>
        <!--<button class="tablinks" onclick="openCity(event, \'Tokyo\')">Tickets</button>-->
        </div>
        
        <div id="London" class="tabcontent">
        <span onclick="this.parentElement.style.display=\'none\'" class="topright">&times</span>
        <form class="eventer-update-user-details">
            <fieldset>';
                if($user_details){
                    foreach($user_details as $key=>$value){
                        $pos = strpos($key, 'quantity');
                        $pos1 = strpos($key, 'chosen');
                        if ($pos !== false || $pos1 !== false) continue;
                        echo $key.':<br>
                        <input type="text" name="'.$key.'" value="'.$value.'">
                        <br>';
                    }
                }
                echo '<br/>';
                echo '<input type="submit" value="Submit" class="">
            </fieldset>
        </form>
        </div>
        
        <div id="Paris" class="tabcontent">
        <span onclick="this.parentElement.style.display=\'none\'" class="topright">&times</span>
        <form class="eventer-update-user-settings">
            <fieldset>';
                if($settings){
                    $tickets = $settings['registrants'];
                    if($tickets){
                        foreach($tickets as $ticket=>$regs){
                            if(array_key_exists($ticket, $tickets_booked)){
                                if($tickets_booked[$ticket]<=0) continue;
                            }
                            echo '<div class="eventer-tickets-area">';
                            echo '<h4>'.$ticket.':<br></h4>';
                            foreach($regs as $reg){
                                $default_class = $disabled = $default_msg = '';
                                $name = $reg['name'];
                                $value = $reg['email'];
                                if($name==$username && $value==$email){
                                    $default_class = 'default-field';
                                    $disabled = "disabled";
                                    $default_msg = "These are the default values of registraion and you can modify them from Details section.";
                                }
                                echo '<span class="tickets-specific">';
                                echo '<input '.$disabled.' type="text" class="reg-name '.$default_class.'" value="'.$name.'">';
                                echo '<input '.$disabled.' type="text" class="reg-email '.$default_class.'" value="'.$value.'">
                                </span>
                                <p>'.$default_msg.'</p>
                                <br>';
                            }
                            echo '</div>';
                        }
                    }
                }
                echo '<br/>';
                echo '<input type="submit" value="Submit" class="">';
            echo '</fieldset>
        </form>
        </div>
        
        <div id="Tokyo" class="tabcontent">
        <span onclick="this.parentElement.style.display=\'none\'" class="topright">&times</span>';
        if($payment){
            foreach($payment as $key=>$value){
                if(is_array($value)) continue;
                echo '<div>
                    <label><strong>'.$key.': </strong></label>
                    <label style="background-color:red;">'.$value.'</label>
                </div>';
            }
        }
        echo '</div>
        
        <script>
        function openCity(evt, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(cityName).style.display = "block";
        evt.currentTarget.className += " active";
        }
        
        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();
        jQuery(document).ready(function($){
            jQuery(document).on("submit", ".eventer-update-user-details", function(e){
                e.preventDefault();
                var form_data = jQuery(this).serializeArray();
                var reg = '.$registrant.';
                var username = $("input[name=Name]").val();
                var email = $("input[name=email]").val();
                var request = $.ajax({
                    url:"'.admin_url('admin-ajax.php').'",
                    type:"post",
                    dataType:"json",
                    data:{
                        action:"eventer_booking_user_details_update",
                        reg:reg,
                        details:form_data,
                        username:username,
                        email:email
                    }
                });
                request.done(function (response) {
                    console.log(response);
                });
            });
            jQuery(document).on("submit", ".eventer-update-user-settings", function(e){
                e.preventDefault();
                var form_data = jQuery(this).serializeArray();
                var tickets = {};
                $(".eventer-tickets-area").each(function(){
                    var ticket_reg = [];
                    var ticket_name = $(this).find("h4").text();
                    $(this).find(".tickets-specific").each(function(){
                        ticket_reg.push({name:$(this).find(".reg-name").val(), email:$(this).find(".reg-email").val()});
                    });
                    tickets[ticket_name] = ticket_reg;
                });
                console.log(tickets);
                var reg = '.$registrant.'
                var request = $.ajax({
                    url:"'.admin_url('admin-ajax.php').'",
                    type:"post",
                    dataType:"json",
                    data:{
                        action:"eventer_booking_user_settings_update",
                        reg:reg,
                        details:tickets,                      settings:'.json_encode($settings).'
                    }
                });
                request.done(function (response) {
                    console.log(response);
                });
            });
        });
        </script>';
    }
    else{
        echo "No details found here";
    }
}

function eventer_booking_user_details_update(){
    $registrant_id = (isset($_REQUEST['reg']))?$_REQUEST['reg']:'';
    $user_details = (isset($_REQUEST['details']))?$_REQUEST['details']:'';
    $username = (isset($_REQUEST['username']))?$_REQUEST['username']:'';
    $email = (isset($_REQUEST['email']))?$_REQUEST['email']:'';
    $user_details = serialize($user_details);
    eventer_update_registrant_details(array('user_details' => $user_details, 'username'=>$username, 'email'=>$email), $registrant_id, array("%s", "%s", "%s"));
    wp_die();
}
add_action('wp_ajax_eventer_booking_user_details_update', 'eventer_booking_user_details_update');

function eventer_booking_user_settings_update(){
    $registrant_id = (isset($_REQUEST['reg']))?$_REQUEST['reg']:'';
    $user_details = (isset($_REQUEST['details']))?$_REQUEST['details']:'';
    $settings = (isset($_REQUEST['settings']))?$_REQUEST['settings']:'';
    $user_details = $user_details;
    //$settings = json_decode($settings, true);
    
    $settings['registrants'] = $user_details;
    eventer_update_registrant_details(array('user_system' => serialize($settings)), $registrant_id, array("%s", "%s"));
    wp_die();
}
add_action('wp_ajax_eventer_booking_user_settings_update', 'eventer_booking_user_settings_update');