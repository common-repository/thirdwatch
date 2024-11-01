# Chargeback

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_user_id** | **string** | The user&#39;s account ID according to your systems. Note that user IDs are case sensitive. | [optional] 
**_session_id** | **string** | The user&#39;s current session ID, used to tie a user&#39;s action before and after login or account creation. Required if no user_id values is provided. | [optional] 
**_order_id** | **string** | The ID for the order that this chargeback is filed against. This field is not required if this chargeback was filed against a transaction with no _orderId. | [optional] 
**_transaction_id** | **string** | The ID for the transaction that this chargeback is filed against. | [optional] 
**_chargeback_state** | **string** | The current state of the chargeback. e.g. _received, _accepted, _disputed, _won, _lost | [optional] 
**_chargeback_reason** | **string** | This field can be used to capture the reason given. e.g. _fraud, _duplicate, _product_not_received, _product_unacceptable, _other\&quot; | [optional] 
**_custom_info** | [**\ai\thirdwatch\Model\CustomInfo**](CustomInfo.md) |  | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


