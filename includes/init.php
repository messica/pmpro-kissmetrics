<?php

function pmprokm_init() {

    global $pmprokm_options, $current_user, $pmprokm_identity;

    //only run if we have both API key and JS code
    if(empty($pmprokm_options['apikey']) || empty($pmprokm_options['js']))
        return false;

    //initialize php
    KM::init($pmprokm_options['apikey']);

    //initialize js
    add_action('wp_footer', 'pmprokm_js_wp_footer');

    //if we're logged in track username, otherwise track cookie
    if(is_user_logged_in())
        $pmprokm_identity = $current_user->user_login;
    else
        $pmprokm_identity = pmprokm_read_js_identity();

    if(!empty($pmprokm_identity))
        KM::identify($pmprokm_identity);

}
add_action('init', 'pmprokm_init');

//handle cookies when not logged in
function pmprokm_read_js_identity() {
    if (isset($_COOKIE['km_ni'])) {
        return $_COOKIE['km_ni'];
    } else if (isset($_COOKIE['km_ai'])) {
        return $_COOKIE['km_ai'];
    }
}

function pmprokm_js_wp_footer() {
    
    global $pmprokm_options, $pmprokm_identity;

    //main KISSmetrics tracking js
    echo $pmprokm_options['js'];

    //set identity
    if(!empty($pmprokm_identity)) {
        ?>

        <script>
            var _kmq = _kmq || [];
            _kmq.push(['identify', '<?php echo $pmprokm_identity ?>']);
        </script>

        <?php
    }

}