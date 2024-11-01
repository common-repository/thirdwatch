# PaymentMethod

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_payment_type** | **string** | Values like - _cash, _check, _creditCard, _debitCard, _netBanking, _wallet, _cryptoCurrency, _electronicFundTransfer, _financing, _giftCard, _interac, _invoice, _moneyOrder, _masterPass, _points, _storeCredit, _thirdPartyProcessor, _voucher | [optional] 
**_amount** | **string** | The item unit price in numbers, in the base unit of the currency_code.e.g. \&quot;2500\&quot;. In case of multiple payment methods in order it&#39;s useful. | [optional] 
**_currency_code** | **string** | The [ISO-4217](http://en.wikipedia.org/wiki/ISO_4217) currency code for the amount. e.g., USD, INR alternative currencies, like bitcoin or points systems. In case of multiple payment methods in order it&#39;s useful. | [optional] 
**_payment_gateway** | **string** | fill value like bank name, gateway name, wallet name etc, e.g. payu, paypal, icici, paytm | [optional] 
**_account_name** | **string** | Account name oif the user for that payment method | [optional] 
**_card_bin** | **string** | The first six digits of the credit card number. These numbers contain information about the card issuer, the geography and other card details. | [optional] 
**_avs_response_code** | **string** | Response code from the AVS address verification system. Used in payments involving credit cards. | [optional] 
**_cvv_response_code** | **string** | Response code from the credit card company indicating if the CVV number entered matches the number on record. Used in payments involving credit cards. | [optional] 
**_card_last4** | **string** | The last four digits of the credit card number. | [optional] 
**_card_expiry_month** | **string** | Expiry month of the card. | [optional] 
**_card_expiry_year** | **string** | Expiry year of the card. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


