# UpdateOrder

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_user_id** | **string** | The user&#39;s account ID according to your systems. Note that user IDs are case sensitive. | [optional] 
**_session_id** | **string** | The user&#39;s current session ID, used to tie a user&#39;s action before and after login or account creation. Required if no user_id values is provided. | [optional] 
**_order_id** | **string** | The ID for tracking this order in your system. | 
**_device_ip** | **string** | IP address of the request made by the user. Recommended for historical backfills and customers with mobile apps. | [optional] 
**_origin_timestamp** | **string** | Represents the time the event occured in your system. Send as a UNIX timestamp in milliseconds in string. | [optional] 
**_user_email** | **string** | Email of the user creating this order. Note - If the user&#39;s email is also their account ID in your system, set both the userId and userEmail fields to their email address. | [optional] 
**_amount** | **string** | The item unit price in numbers, in the base unit of the currency_code.e.g. \&quot;2500\&quot; | [optional] 
**_currency_code** | **string** | The [ISO-4217](http://en.wikipedia.org/wiki/ISO_4217) currency code for the amount. e.g., USD, INR alternative currencies, like bitcoin or points systems. | [optional] 
**_has_expedited_shipping** | **bool** | Whether the user requested priority/expedited shipping on their order. | [optional] 
**_shipping_method** | **string** | Indicates the method of delivery to the user. e.g. _electronic, _physical | [optional] 
**_order_referrer** | **string** | Referer website or user name. | [optional] 
**_is_pre_paid** | **bool** | is order is prepaid. | [optional] 
**_is_gift** | **bool** | Is user chosen gift pack. | [optional] 
**_is_return** | **bool** | Is this return order. | [optional] 
**_is_first_time_buyer** | **bool** | Is user first time buyer. | [optional] 
**_billing_address** | [**\ai\thirdwatch\Model\BillingAddress**](BillingAddress.md) |  | [optional] 
**_shipping_address** | [**\ai\thirdwatch\Model\ShippingAddress**](ShippingAddress.md) |  | [optional] 
**_payment_methods** | [**\ai\thirdwatch\Model\PaymentMethod[]**](PaymentMethod.md) | The payment information associated with this order. Represented as an array of nested payment_method objects containing payment type, payment gateway, credit card bin, etc. | [optional] 
**_promotions** | [**\ai\thirdwatch\Model\Promotion[]**](Promotion.md) | The list of promotions that apply to this order. You can add one or more promotions when creating or updating an order. Represented as a JSON array of promotion objects. You can also separately add promotions to the account via the addPromotion event. | [optional] 
**_items** | [**\ai\thirdwatch\Model\Item[]**](Item.md) | The list of items ordered. Represented as a JSON array of item | [optional] 
**_custom_info** | [**\ai\thirdwatch\Model\CustomInfo**](CustomInfo.md) |  | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


