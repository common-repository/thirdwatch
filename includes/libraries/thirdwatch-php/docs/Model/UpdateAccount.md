# UpdateAccount

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_user_id** | **string** | The user&#39;s account ID according to your systems. Note that user IDs are case sensitive. | [optional] 
**_session_id** | **string** | The user&#39;s current session ID, used to tie a user&#39;s action before and after login or account creation. Required if no user_id values is provided. | [optional] 
**_device_ip** | **string** | IP address of the request made by the user. Recommended for historical backfills and customers with mobile apps. | [optional] 
**_origin_timestamp** | **string** | Represents the time the event occured in your system. Send as a UNIX timestamp in milliseconds in string. | [optional] 
**_user_email** | **string** | Email of the user creating this order. Note - If the user&#39;s email is also their account ID in your system, set both the userId and userEmail fields to their email address. | [optional] 
**_first_name** | **string** | Provide the first name associated with the user here. | [optional] 
**_last_name** | **string** | Provide the last name associated with the user here. | [optional] 
**_phone** | **string** | The primary phone number of the user associated with this account. Provide the phone number as a string. | [optional] 
**_age** | **string** | Age of the user e.g. \&quot;25\&quot; | [optional] 
**_gender** | **string** | Gender of the user e.g. \&quot;_male\&quot;, \&quot;_female\&quot; or \&quot;_trans\&quot; | [optional] 
**_referral_code** | **string** | Code or promotion used by the user while creating account. | [optional] 
**_referrer_user_id** | **string** | The ID of the user that referred the current user to your business. This field is required for detecting referral fraud. | [optional] 
**_billing_address** | [**\ai\thirdwatch\Model\BillingAddress**](BillingAddress.md) |  | [optional] 
**_shipping_address** | [**\ai\thirdwatch\Model\ShippingAddress**](ShippingAddress.md) |  | [optional] 
**_payment_methods** | [**\ai\thirdwatch\Model\PaymentMethod[]**](PaymentMethod.md) | The payment information associated with this account. Represented as an array of nested payment_method objects containing payment type, payment gateway, credit card bin, etc. | [optional] 
**_promotions** | [**\ai\thirdwatch\Model\Promotion[]**](Promotion.md) | The list of promotions that apply to this account. You can add one or more promotions when creating or updating an order. Represented as a JSON array of promotion objects. You can also separately add promotions to the account via the addPromotion event. | [optional] 
**_social_sign_on_type** | **string** | If the user logged in with a social identify provider, give the name here. e.g. _google, _facebook, _twitter, _linkedin, _other | [optional] 
**_email_confirmed_status** | **string** | Status of email verification. e.g. _success, _failure, _pending | [optional] 
**_phone_confirmed_status** | **string** | Status of phone verification. e.g. _success, _failure, _pending | [optional] 
**_is_newsletter_subscribed** | **bool** | Is user subscribed for newsletter. e.g. true, false | [optional] 
**_account_status** | **string** | Current status of account, e.g. _active, _inactive | [optional] 
**_facebook_id** | **string** | Facebook user id or token of the user. This can help to varify his social identity. | [optional] 
**_google_id** | **string** | Google user id or token of the user. This can help to varify his social identity. | [optional] 
**_twitter_id** | **string** | Twitter handle or token of the user. This can help to varify his social identity. | [optional] 
**_custom_info** | [**\ai\thirdwatch\Model\CustomInfo**](CustomInfo.md) |  | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


