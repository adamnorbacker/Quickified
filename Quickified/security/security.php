<?php
defined( 'ABSPATH' ) or die( 'You are not allowed here.' );
//Remove WP version
add_filter( 'the_generator', 'disableFilter' );
remove_action( 'wp_head', 'wp_generator' );
//Remove login errors
add_filter( 'login_errors', "disableFilter" );
//Check the user
add_filter( 'authenticate', 'check_user', 30, 3 );
//Main for quickified security
add_action( 'wp_loaded', 'quickified_security_main' );
//Inloggningsskydd
add_action( 'wp_login_failed', 'wp_login_security' );

require_once __DIR__ . '/headerscanner.php';
function disableFilter()
  {
    return null;
  }

function get_the_user_ip()
  {
    if ( !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) )
      {
        //check ip from share internet
        $ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
      }
    elseif ( !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
      {
        //to check ip is pass from proxy
        $ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
      }
    else
      {
        $ip = $_SERVER[ 'REMOTE_ADDR' ];
      }
    return $ip;
  }

function check_user( $user, $username, $password )
  {
    if ( is_a( $user, 'WP_User' ) )
      {
        return $user;
      }
  }

function quickified_security_main()
  {
    global $wpdb;
    $table_name     = $wpdb->prefix . 'quickified_security';
    $currentIP      = get_the_user_ip();
    $baseurl        = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://" . $_SERVER[ 'HTTP_HOST' ];
    $currenturl     = $baseurl . $_SERVER[ 'REQUEST_URI' ];
    $domainemail    = $_SERVER[ 'HTTP_HOST' ];
    $domainemail    = ltrim( $domainemail, 'www.' );
    $serverprotocol = $_SERVER[ 'SERVER_PROTOCOL' ];
    $therandomurl   = ( $currenturl . "?quickified_security_unlock_" . rand( 100000, 10000000 ) );
    if ( stripos( $currenturl, 'wp-login' ) )
      {
        $clearloginblock = $wpdb->get_row( "SELECT * FROM $table_name WHERE ip = '" . $currentIP . "'" );
        if ( $wpdb->num_rows > 0 )
          {
            $users               = $wpdb->get_results( "SELECT user_email FROM $wpdb->users WHERE display_name = '$clearloginblock->username'" );
            $required_user_email = $users[ 0 ]->user_email;
            if ( $clearloginblock->reseturl_login == $currenturl )
              {
                $updateleverera = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `banned_login` = 0, `remaining_time_login` = 0, `tries_login` = 0, `reseturl_login` = '' WHERE `reseturl_login` = %s", $currenturl ) );
                if ( $updateleverera === false )
                  {
                    echo "error";
                  }
              }
            else
              {
                if ( $clearloginblock->banned_login == '1' && !$clearloginblock->reseturl_login == '0' )
                  {
                    header( "$serverprotocol 503 Service Unavailable" );
                    echo "<html>
                        <head>
                            <title>Quickified Security</title>
                        </head>
                        <body>
                            <h1>Quickified Security</h1>
                            <h2>We have blocked you from login by security reasons</h2>
                            <p>You've exceeded the number of login attempts. We've blocked IP address $currentIP for some time.</p></br><p>We will send you unlock instructions to the users email you tried to log in to.</p>
                        </body>
                        </html>";
                    $to           = $required_user_email;
                    $subject      = "Quickified Security Alert";
                    $baseurl      = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://" . $_SERVER[ 'HTTP_HOST' ];
                    $currenturl   = $baseurl . $_SERVER[ 'REQUEST_URI' ];
                    $pluginurlDIR = plugin_dir_url( __FILE__ ) . '../' . 'assets/images/security';
                    $message      = "<table background='$pluginurlDIR/quickified_security_header_main.jpg' style='width:100%; height:200px; background-size:contain; background-position:bottom left; background-repeat: no-repeat; '><tr><td></td></tr>
                        </table><p>Someone tried to get access to your account: $clearloginblock->username <br>from: $currenturl <br>with the IP: $currentIP <br>To access the site again, use this link: $clearloginblock->reseturl_login</p><br>
                        <img width='192px' height='69px' style='margin-top:30px; width:192px; height:69px;' src='$pluginurlDIR/loggo.png'>";
                    $headers      = array(
                         "From: Quickified Security <wordpress@$domainemail>" 
                    );
                    add_filter( 'wp_mail_content_type', 'set_content_type' );
                    function set_content_type( $content_type )
                      {
                        return 'text/html';
                      }
                    wp_mail( $to, $subject, $message, $headers );
                    exit();
                  }
              }
          }
      }
  }

function wp_login_security( $args )
  {
    global $wpdb;
    $table_name     = $wpdb->prefix . 'quickified_security';
    // Date format: 2019-05-06 13:53
    $currenttime    = current_time( 'Y-m-d H:i' );
    $domainemail    = $_SERVER[ 'HTTP_HOST' ];
    $serverprotocol = $_SERVER[ 'SERVER_PROTOCOL' ];
    $effectiveDate  = strtotime( "+2 weeks", strtotime( $currenttime ) );
    // Date format: 2019-05-25 11:35
    $twoweeks       = date( "Y-m-d H:i", $effectiveDate );
    $currentIP      = get_the_user_ip();
    $user_name      = "";
    if ( !empty( $_POST[ 'log' ] ) )
      {
        $user_name = $_POST[ 'log' ];
      }
    $user_password = "";
    if ( !empty( $_POST[ 'pwd' ] ) )
      {
        $user_password = $_POST[ 'pwd' ];
      }
    if ( username_exists( $user_name ) )
      {
        $the_user      = get_user_by( 'login', $user_name );
        $the_user_id   = $the_user->ID;
        $the_user_pass = $the_user->user_pass;
        if ( !wp_check_password( $user_password, $the_user_pass, $the_user_id ) )
          {
            $mylink = $wpdb->get_row( "SELECT * FROM $table_name WHERE ip = '" . $currentIP . "'" );
            if ( $wpdb->num_rows > 0 )
              {
                $users               = $wpdb->get_results( "SELECT user_email FROM $wpdb->users WHERE display_name = '$mylink->username'" );
                $required_user_email = $users[ 0 ]->user_email;
                $getloginturns       = $mylink->tries_login;
                $getremainingtime    = $mylink->remaining_time_login;
                $getloginturnssum    = $getloginturns + 1;
                if ( $getloginturns >= 2 )
                  {
                    if ( $getremainingtime == 0 )
                      {
                        $updateleverera = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `banned_login` = %d, `remaining_time_login` = %d WHERE `ip` = %s", 1, $twoweeks, $currentIP ) );
                        if ( $updateleverera === false )
                          {
                            echo "error";
                          }
                      }
                    else
                      {
                        if ( $currenttime >= $getremainingtime )
                          {
                            $updateleverera = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `banned_login` = 0, `remaining_time_login` = 0, `tries_login` = 0 WHERE `ip` = %s", $currentIP ) );
                            if ( $updateleverera === false )
                              {
                                echo "error";
                              }
                          }
                        else
                          {
                            $baseurl        = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://" . $_SERVER[ 'HTTP_HOST' ];
                            $randomurl      = $baseurl . $_SERVER[ 'REQUEST_URI' ];
                            $therandomurl   = ( $randomurl . "?quickified_security_unlock_" . rand( 100000, 10000000 ) );
                            $updateleverera = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `reseturl_login` = %s WHERE `ip` = %s", $therandomurl, $currentIP ) );
                            if ( $updateleverera === false )
                              {
                                echo "error";
                                exit();
                              }
                            else
                              {
                                header( "$serverprotocol 503 Service Unavailable" );
                                echo "<html><head><title>Too Many Requests</title></head>
                                <body><h1>Quickified Security</h1><h2>We have blocked you from login by security reasons</h2>
                                <p>You've exceeded the number of login attempts. We've blocked IP address $currentIP for some time.</p><br><p>We will send you unlock instructions to the users email you tried to log in to.</p></body>
                                </html>";
                                $to           = $required_user_email;
                                $subject      = "Quickified Security Alert";
                                $baseurl      = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://" . $_SERVER[ 'HTTP_HOST' ];
                                $currenturl   = $baseurl . $_SERVER[ 'REQUEST_URI' ];
                                $pluginurlDIR = plugin_dir_url( __FILE__ ) . '../' . 'assets/images/security';
                                $message      = "<table background='$pluginurlDIR/quickified_security_header_main.jpg' style='width:100%; height:200px; background-size:contain; background-position:bottom left; background-repeat: no-repeat; '><tr><td></td></tr>
                                </table><p>Someone tried to get access to your account: $clearloginblock->username <br>from: $currenturl <br>with the IP: $currentIP <br>To access the site again, use this link: $clearloginblock->reseturl_login</p><br>
                                <img width='192px' height='69px' style='margin-top:30px; width:192px; height:69px;' src='$pluginurlDIR/loggo.png'>";
                                $headers      = array(
                                     "From: Quickified Security <wordpress@$domainemail>" 
                                );
                                add_filter( 'wp_mail_content_type', 'set_content_type' );
                                function set_content_type( $content_type )
                                  {
                                    return 'text/html';
                                  }
                                wp_mail( $to, $subject, $message, $headers );
                                exit();
                              }
                          }
                      }
                  }
                else
                  {
                    $updateleverera = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `username` = %d, `tid` = %d, `ip` = %s, `tries_login` = %d WHERE `ip` = %s", $user_name, $currenttime, $currentIP, $getloginturnssum, $currentIP ) );
                    if ( $updateleverera === false )
                      {
                        echo "error";
                      }
                  }
              }
            else
              {
                
                echo "Does not exist";
                $wpdb->query( $wpdb->prepare( "INSERT INTO $table_name(username, tid, ip, tries_login, banned_login, remaining_time_login) VALUES(%s, %s, %s, %d, %d, %d)", $user_name, $currenttime, $currentIP, 1, 0, 0 ) );
              }
          }
      }
  }
