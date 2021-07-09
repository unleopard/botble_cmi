<html>
<body onload="send();">

<h3>Vous allez redirext vers CMI</h3>




<form name="pay_form" action="https://testpayment.cmi.co.ma/fim/est3Dgate" method="post" id="form_cmi">
    <style>
        input {
            width: 700px;
            padding: 5px 10px;
            margin-bottom: 5px;
        }
    </style>





    <input type="hidden" name="clientid" value="#clientid#" /><br>
    <input type="hidden" name="HASH" value="#hash#" /><br>
    <input type="hidden" name="amount" value="#amount#" /><br>
    <input type="hidden" name="okUrl" value="#okUrl#" /><br>
    <input type="hidden" name="failUrl" value="#failUrl#" /><br>
    <input type="hidden" name="TranType" value="#TranType#" /><br>
    <input type="hidden" name="callbackUrl" value="#callbackUrl#" /><br>
    <input type="hidden" name="shopurl" value="#shopurl#" /><br>
    <input type="hidden" name="currency" value="#currency#" /><br>
    <input type="hidden" name="rnd" value="#rnd#" /><br>
    <input type="hidden" name="storetype" value="#storetype#" /><br>
    <input type="hidden" name="hashAlgorithm" value="#hashAlgorithm#" /><br>
    <input type="hidden" name="lang" value="#lang#" /><br>
    <input type="hidden" name="BillToName" value="#BillToName#" /><br>
    <input type="hidden" name="BillToCompany" value="" /><br>
    <input type="hidden" name="BillToStreet1" value="#BillToStreet1#" /><br>
    <input type="hidden" name="BillToCity" value="#BillToCity#" /><br>
    <input type="hidden" name="BillToStateProv" value="#BillToStateProv#" /><br>
    <input type="hidden" name="BillToCountry" value="#BillToCountry#" /><br>
    <input type="hidden" name="email" value="#email#" /><br>
    <input type="hidden" name="BillToTelVoice" value="#BillToTelVoice#" /><br>
    <input type="hidden" name="encoding" value="#encoding#" /><br>
    <input type="hidden" name="AutoRedirect" value="#AutoRedirect#" /><br>
    <input type="hidden" name="CallbackResponse" value="#CallbackResponse#" /><br>
    <input type="hidden" name="oid" value="#oid#" />
</form>

<script>
    function send() {
        let form = document.getElementById('form_cmi');
        form.submit();
    }
</script>
</body>
</html>