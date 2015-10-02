{* $Id$ *}

<div class="form-field">
    <label for="publickey">{__("publickey")}:</label>
    <input type="text" name="payment_data[processor_params][publickey]" id="publickey" value="{$processor_params.publickey}" class="input-text" />
</div>

<div class="form-field">
    <label for="secretkey">{__("secretkey")}:</label>
    <input type="text" name="payment_data[processor_params][secretkey]" id="secretkey" value="{$processor_params.secretkey}" class="input-text" />
</div>
<div style="display:none;">
<select style="display:none;" name="payment_data[processor_params][iframe_mode]" id="iframe_mode_{$payment_id}"><option value="Y" selected="selected">{__("enabled")}</option></select>
</div>