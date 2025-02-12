'use strict';

$('#payeer-button').on('click', function() {

    $.ajax({
        type: "POST",
        url: paymentUrl,
        data: {
            '_token' : token,
            'gateway' : 'hormuud',
            'amount': amount,
            'currency_id': currency_id,
            'payment_type' : payment_type,
            'redirect_url' : redirect_url,
            'transaction_type' : transaction_type,
            'payment_method_id': payment_method_id,
            'uuid' : uuid,
            'params' : params
        },
        dataType: "json",
        beforeSend: function (xhr) {
            $("payeer-button").attr("disabled", true);
            $(".spinner").removeClass("d-none");
            $("#payeerSubmitBtnText").text(submitText);
        },
    }).done(function(response)
    {
        var response = response.data.data;
        $("input[name='m_shop']").val(response.m_shop);
        $("input[name='m_orderid']").val(response.m_orderid);
        $("input[name='m_amount']").val(response.m_amount);
        $("input[name='m_curr']").val(response.m_curr);
        $("input[name='m_desc']").val(response.m_desc);
        $("input[name='m_sign']").val(response.m_sign);
        $("input[name='m_params']").val(response.m_params);
        $("input[name='form[ps]']").val(2609);
        $("input[name='form[curr[2609]]']").val(response.m_curr);
        $("input[name='m_cipher_method']").val(response.m_cipher_method);

        $('#payeer-submit-button').trigger('click');

    });
});


