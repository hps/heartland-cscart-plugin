<?php
use Tygh\Registry;

if ( !defined('AREA') ) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'return' && !empty($_REQUEST['merchant_order_id'])) {
        include_once ('heartland/Hps.php');
        $merchant_order_id = heartlandplace_order($_REQUEST['merchant_order_id']);
        $order_info = fn_get_session_data('order_info');

        $config = new HpsServicesConfig();
        $config->secretApiKey = fn_get_session_data('secretkey');
        
        $config->versionNumber = '2102';
        $config->developerId = '002914';

        $chargeService = new HpsCreditService($config);
        $address = new HpsAddress();
        $address->address = $order_info['b_address'];
        $address->city = $order_info['b_city'];
        $address->state = $order_info['b_state'];
        $address->zip = preg_replace('/[^0-9]/', '', $order_info['b_zipcode']);
        $address->country = $order_info['b_country'];

        $validCardHolder = new HpsCardHolder();
        $validCardHolder->firstName = $order_info['b_firstname'];
        $validCardHolder->lastName = $order_info['b_lastname'];
        $validCardHolder->address = $address;
        $validCardHolder->phoneNumber = preg_replace('/[^0-9]/', '', $order_info['b_phone']);

        $suToken = new HpsTokenData();
        $suToken->tokenValue = $_REQUEST['securesubmit_token'];

        try {
           $pp_response = array(
            'reason_text' => '',
            'order_status' => 'F'
           );

            $response = $chargeService->charge($order_info['total'], 'usd', $suToken, $validCardHolder);

            $pp_response['order_status'] = "P";
            $pp_response['reason_text'] = 'Payment processed.';
            $pp_response["transaction_id"] = $response->transactionId;

            fn_finish_payment($merchant_order_id, $pp_response);
            fn_order_placement_routines('route', $merchant_order_id);
        } catch (HpsException $e) {
            fn_set_notification('E', __('error'), "Transaction Failed: " . $e->getMessage() . " With order id: " . $_REQUEST['merchant_order_id']);
            fn_order_placement_routines('checkout_redirect');
        }
    }
    exit;
}
else {
    $url = fn_url("payment_notification.return?payment=heartland", AREA, 'current');
    $checkout_url = "js/securesubmit.js";
    $key = $processor_data['processor_params']['publickey'];
    fn_set_session_data('secretkey', $processor_data['processor_params']['secretkey']);
    fn_set_session_data('order_info', $order_info);

    $formhtml = '<form name="securesubmit-form" id="securesubmit-form" action="'.$url.'" target="_parent" method="POST">
                <input type="hidden" name="securesubmit_token" id="securesubmit_token" />
                <input type="hidden" name="merchant_order_id" id="order_id" value="'.$order_id.'"/>
            </form>';


echo <<<EOT
    <script src="{$checkout_url}"></script>
    {$formhtml}


<style>
body, p, div, li {
    color: #333;
    font-family: Open Sans;
    font-size: 13px;
    font-style: normal;
    font-weight: normal;
}
body {
    margin: 0;
}

* {
    -webkit-tap-highlight-color: rgba(0,0,0,0);
}

.clearfix:before, .clearfix:after {
    display: table;
    content: "";
    line-height: 0;
}
Pseudo ::after element
.clearfix:after {
    clear: both;
}
.clearfix:before, .clearfix:after {
    display: table;
    content: "";
    line-height: 0;
}
.ty-credit-card {
    display: inline-block;
    float: left;
    box-sizing: border-box;
    margin-bottom: 20px;
    padding: 15px 22px;
    max-width: 400px;
    border: 1px solid #f2f2f2;
    border-radius: 5px;
    background: white;
    margin: 20px;
}
.ty-control-group {
    margin: 0 0 12px 0;
    vertical-align: middle;
}
.ty-control-group:before, .ty-control-group:after {
    display: table;
    content: "";
    line-height: 0;
}
.ty-control-group:after {
    clear: both;
}
.ty-control-group:before, .ty-control-group:after {
    display: table;
    content: "";
    line-height: 0;
}
.ty-control-group__title {
    display: block;
    padding: 6px 0;
    font-weight: bold;
}
body, p, div, li {
    color: #333;
    font-family: Open Sans;
    font-size: 13px;
    font-style: normal;
}
label.cm-required:after {
    padding-left: 3px;
    color: #ea7162;
    content: "*";
    font-size: 13px;
    line-height: 1px;
}
input.ty-credit-card__input {
    padding: 8px;
    width: 100%;
    height: 40px;
    font-size: 18px;
}
select, input[type="text"], input[type="password"], textarea, select {
    border: 1px solid #c2c9d0;
    background: #fff;
    font-family: Open Sans;
    font-style: normal;
    font-weight: normal;
    box-sizing: border-box;
}
button, input {
    line-height: normal;
}
.ty-cc-icons {
    position: absolute;
    right: 57px;
    bottom: 25px;
    display: inline-block;
    margin: 0 0 15px;
}
ul {
   list-style: none;
}
.ty-control-group:after {
    clear: both;
}
.ty-control-group:before, .ty-control-group:after {
    display: table;
    content: "";
    line-height: 0;
}
.ty-credit-card__control-group {
    position: relative;
}
.ty-control-group {
    margin: 0 0 12px 0;
    vertical-align: middle;
}
body, p, div, li {
    color: #333;
    font-family: Open Sans;
    font-size: 13px;
    font-style: normal;
    font-weight: normal;
}
select, input[type="text"], input[type="password"], textarea, select {
    padding: 4px 8px;
    border: 1px solid #c2c9d0;
    background: #fff;
    font-family: Open Sans;
    font-size: 13px;
    font-style: normal;
    font-weight: normal;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}

ty-credit-card__input-short {
    margin: 0;
    width: 50px;
}
select, input[type="text"], input[type="password"] {
    height: 32px;
    -webkit-appearance: none;
    border-radius: 0;
}
select, input[type="text"], input[type="password"], textarea, select {
    padding: 4px 8px;
    border: 1px solid #c2c9d0;
    background: #fff;
    font-family: Open Sans;
    font-size: 13px;
    font-style: normal;
    font-weight: normal;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
button, input {
    line-height: normal;
}

.ty-btn__primary {
    background: #ea621f !important;
    color: #fff !important;
}
.ty-btn__big !important {
    padding: 6px 17px;
    text-transform: uppercase;
}
.ty-btn {
    display: inline-block;
    margin-bottom: 0;
    padding: 6px 14px!important;
    outline: 0px!important;
    border: 1px solid rgba(0,0,0,0)!important;
    background: #bdc3c7;
    background-image: none;
    color: #fff!important;
    vertical-align: middle!important;
    text-align: center!important;
    line-height: 1.428571429!important;
    cursor: pointer;
    font-family: Open Sans !important;
    font-size: 14px;
    font-weight: normal;
    font-style: normal;
    text-decoration: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    -o-user-select: none;
    user-select: none;
    -webkit-transition: background 200ms;
    -moz-transition: background 200ms;
    -o-transition: background 200ms;
    transition: background 200ms;
}
button, html input[type="button"], input[type="reset"], input[type="submit"] {
    -webkit-appearance: button;
}

.ty-checkout-buttons {
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px solid #d0d6db;
    text-align: center;
}
body, p, div, li {
    color: #333;
    font-family: Open Sans;
    font-size: 13px;
    font-style: normal;
    font-weight: normal;
}
</style>


<div>


<div class="clearfix">
    <div class="ty-credit-card">
        <div class="ty-credit-card__control-group ty-control-group">
            <label for="credit_card_number_" class="ty-control-group__title cm-cc-number cm-required">Card number</label>
            <input size="35" type="text" id="card_number" value="" class="ty-credit-card__input cm-focus cm-autocomplete-off" autocomplete="off">
        </div>

        <div class="ty-credit-card__control-group ty-control-group">
            <label for="exp_month" class="ty-control-group__title cm-cc-date cm-cc-exp-month cm-required">Valid thru (mm/yy)</label>
            <select id="exp_month" class="ty-credit-card__input-short">
                <option>01</option>
                        <option>02</option>
                        <option>03</option>
                        <option>04</option>
                        <option>05</option>
                        <option>06</option>
                        <option>07</option>
                        <option>08</option>
                        <option>09</option>
                        <option>10</option>
                        <option>11</option>
                        <option>12</option>
            </select>&nbsp;&nbsp;/&nbsp;&nbsp;<select id="exp_year" class="ty-credit-card__input-short"></select>
        </div>
        <div class="ty-control-group ty-credit-card__cvv-field cvv-field">
            <label for="card_cvc" class="ty-control-group__title cm-required cm-integer cm-cc-cvv2 cm-autocomplete-off">CVV/CVC</label>
            <input type="text" id="card_cvc" value="" size="4" maxlength="4" class="ty-credit-card__cvv-field-input">
        </div>

        <div class="ty-checkout-buttons ty-checkout-buttons__submit-order">
            <button onclick="tokenize()" id="securesubmit-button" class="ty-btn__big ty-btn__primary cm-checkout-place-order ty-btn" type="submit" name="dispatch[checkout.place_order]">SUBMIT MY ORDER</button>
        </div>
    </div>
</div>

    <script type="text/javascript">
        var myselect=document.getElementById("exp_year"), year = new Date().getFullYear();
        var gen = function(max){do{myselect.add(new Option(year++),null);}while(max-->0);}(10);

        function tokenize() {
            var hps = new HPS({
                api_key : "{$key}",
                card_number : document.getElementById("card_number").value,
                card_exp_year: document.getElementById("exp_year").value,
                card_exp_month: document.getElementById("exp_month").value,
                card_cvc: document.getElementById("card_cvc").value,
                success : successMethod,
                error: errorMethod
            });
            hps.tokenize();
        }

        function successMethod(response) {
            document.getElementById("securesubmit_token").value = response.token_value;
            document.getElementById("securesubmit-form").submit();
        }

        function errorMethod(response) {
            if (response.error) {
                alert(response.error.message);
            }
        }
    </script>
</body>
</html>
EOT;
exit;
}

function heartlandplace_order($original_order_id)
{
    $cart = & $_SESSION['cart'];
    $auth = & $_SESSION['auth'];

    list($order_id, $process_payment) = fn_place_order($cart, $auth);

    $data = array (
        'order_id' => $order_id,
        'type' => 'S',
        'data' => TIME,
    );
    db_query('REPLACE INTO ?:order_data ?e', $data);

    $data = array (
        'order_id' => $order_id,
        'type' => 'E', // extra order ID
        'data' => $original_order_id,
    );
    db_query('REPLACE INTO ?:order_data ?e', $data);

    return $order_id;
}

?>