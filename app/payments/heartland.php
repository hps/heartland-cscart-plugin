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
    $checkout_url = "https://js.globalpay.com/v1/globalpayments.js";
    $key = $processor_data['processor_params']['publickey'];
    fn_set_session_data('secretkey', $processor_data['processor_params']['secretkey']);
    fn_set_session_data('order_info', $order_info);

    $formhtml = '<form name="securesubmit-form" id="securesubmit-form" action="'.$url.'" target="_parent" method="POST">
            <div class="clearfix">
                <div class="ty-credit-card">
                    <div class="ty-credit-card__control-group ty-control-group">
                        <label for="credit_card_number_" class="ty-control-group__title cm-cc-number cm-required">Card number</label>
                        <div id="credit-card-card-number" class="ty-credit-card__input cm-focus cm-autocomplete-off"></div>
                    </div>
                    <div class="ty-control-group ty-credit-card__cvv-field cvv-field">
                        <label for="card_cvc" class="ty-control-group__title cm-required cm-integer cm-cc-cvv2 cm-autocomplete-off">CVV/CVC</label>
                        <div id="credit-card-card-cvv" class="ty-credit-card__cvv-field-input"></div>
                    </div>
                    <div class="ty-credit-card__control-group ty-control-group">
                        <label for="exp_month" class="ty-control-group__title cm-cc-date cm-cc-exp-month cm-required">Valid thru (mm/yy)</label>
                        <div id="credit-card-card-expiration" class="ty-credit-card__input-short"></div>
                    </div>
                    <div class="ty-checkout-buttons ty-checkout-buttons__submit-order">
                        <div id="credit-card-submit" class="credit-card-submit"></div>
                    </div>
                    <input type="hidden" name="securesubmit_token" id="securesubmit_token" />
                    <input type="hidden" name="merchant_order_id" id="order_id" value="'.$order_id.'"/>
                 </div>
            </div>
        </form>';

echo <<<EOT
    <script src="{$checkout_url}"></script>
    {$formhtml}


<style>

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

.ty-control-group__title {
    display: block;
    padding: 6px 0;
    font-weight: bold;
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

div.credit-card-submit.disable-button{
	pointer-events: none;
	opacity: 0.65;
}
</style>


<div>

    <script type="text/javascript">
            GlobalPayments.configure({
                "publicApiKey": "{$key}"
            });

            // Create Form
            const cardForm = GlobalPayments.ui.form({
            fields: {
                "card-number": {
                placeholder: "•••• •••• •••• ••••",
                target: "#credit-card-card-number"
                },
                "card-expiration": {
                placeholder: "MM / YYYY",
                target: "#credit-card-card-expiration"
                },
                "card-cvv": {
                placeholder: "•••",
                target: "#credit-card-card-cvv"
                },
                "submit": {
                text: "SUBMIT MY ORDER",
                target: "#credit-card-submit"
                }
            },
            styles: {
                // Your styles
                "button#secure-payment-field.submit" : {
                    "background": "#ea621f !important",
                    "color": "#fff !important",
                    "display": "inline-block",
                    "margin-bottom": "0",
                    "padding" : "6px 14px !important",
                    "outline": "0px !important",
                    "border": "1px solid rgba(0, 0, 0, 0) !important",
                    "background-image": "none",
                    "color": "#fff !important",
                    "vertical-align": "middle !important",
                    "text-align": "center !important",
                    "line-height": "1.428571429 !important",
                    "cursor": "pointer",
                    "font-family": "Open Sans !important",
                    "font-size": "14px",
                    "font-weight": "normal",
                    "font-style": "normal",
                    "text-decoration": "none",
                    "-webkit-user-select": "none",
                    "-moz-user-select": "none",
                    "-ms-user-select": "none",
                    "-o-user-select": "none",
                    "user-select": "none",
                    "-webkit-transition": "background 200ms",
                    "-moz-transition": "background 200ms",
                    "-o-transition": "background 200ms",
                    "transition": "background 200ms",
                },
                "#securesubmit-form iframe" : {
                     "height" : "40px",
                },
                "iframe input":{
                    "height" : "30px"
                },

                "#secure-payment-field" : {
                    "background-color" : "#fff",
                    "border"           : "1px solid #ccc",
                    "border-radius"    : "4px",
                    "display"          : "block",
                    "font-size"        : "14px",
                    "height"           : "35px",
                    "padding"          : "6px 12px",
                    "width"            : "100%",
                  },
            }
            });


            cardForm.on('submit', 'click', function(){
                disableSubmit();
            });

            cardForm.ready(() => {
                console.log("Registration of all credit card fields occurred");
            });

            cardForm.on("token-success", (resp) => {
                if(resp.details.cardSecurityCode == false){
                    alert("Invalid Card Details");
                    stopDisableSubmit();
                }else{
                    successMethod(resp);
                }
            });

            cardForm.on("token-error", (resp) => {
                if(resp.error){
                    resp.reasons.forEach(function(v){
                        alert(v.message);
                    })
                    stopDisableSubmit();
                }
            });

            function successMethod(response) {
                document.getElementById("securesubmit_token").value = response.paymentReference;
                document.getElementById("securesubmit-form").submit();
            }

            function disableSubmit() {
                var submit_button = document.getElementById('credit-card-submit');
                    submit_button.classList.add("disable-button");
            }

            function stopDisableSubmit() {
                var submit_button = document.getElementById('credit-card-submit');
                    submit_button.classList.remove("disable-button");
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