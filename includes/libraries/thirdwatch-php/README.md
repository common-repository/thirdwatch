# thirdwatch_api
ThirdwatchApi - PHP client for thirdwatch_api

The first version of the Thirdwatch API is an exciting step forward towards making it easier for developers to pass data to Thirdwatch.  

# Introduction 

Once you've [registered your website/app](https://dashboardstaging.thirdwatch.co/login) it's easy to start sending data to Thirdwatch.  All endpoints are only accessible via https and are located at `staging.thirdwatch.co`. 
For instance: you can send event at the moment by ```HTTPS POST``` request to the following URL with your API key in ```Header``` and ```JSON``` data in request body. 

```   https://staging.thirdwatch.co/event/v1 ``` 

Every API request must contain ```API Key``` in header value ```X-THIRDWATCH-API-KEY```. Every event must contain your ```_userId``` (if this is not available, you can alternatively provide a ```_sessionId``` value also in ```_userId```). 

- API version: 0.0.2
- Package version: 0.0.2

## Requirements

PHP 5.5 and later

## Installation & Usage
### Composer

To install the bindings via [Composer](http://getcomposer.org/), add the following to `composer.json`:

```
{
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/thirdwatch/thirdwatch-php.git"
    }
  ],
  "require": {
    "thirdwatch/thirdwatch-php": "*@async"
  }
}
```

Then run `composer install`

### Manual Installation

Download the files and include `autoload.php`:

```php
    require_once('/path/to/thirdwatch-php/vendor/autoload.php');
```

## Tests

To run the unit tests:

```
composer install
./vendor/bin/phpunit
```

## Getting Started

Please follow the [installation procedure](#installation--usage) and then run the following:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$config = ai\thirdwatch\Configuration::getDefaultConfiguration()->setApiKey('X-THIRDWATCH-API-KEY', 'YOUR_API_KEY');
$cartData = array();

try{
    $cartData['_user_id'] = (string) 'USER_ID';
    $cartData['_session_id'] = (string) 'SESSION_ID';
    $cartData['_device_ip'] = (string) 'IP_ADDRESS';
    $cartData['_origin_timestamp'] = (string) (time() * 1000);
    $api_instance = new \ai\thirdwatch\Api\AddToCartApi(new GuzzleHttp\Client(), $config);
    $body = new \ai\thirdwatch\Model\AddToCart($cartData);
}
catch (Exception $e){
    echo $e->getMessage();
}

try {
    $result = $api_instance->addToCart($body);
    print_r($result);
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
```

## Documentation for API Endpoints

All URIs are relative to *https://staging.thirdwatch.co/event*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*AddPromotionApi* | [**addPromotion**](docs/Api/AddPromotionApi.md#addpromotion) | **POST** /v1/add_promotion | Use add_promotion to record when a user adds one or more promotions to their account.
*AddToCartApi* | [**addToCart**](docs/Api/AddToCartApi.md#addtocart) | **POST** /v1/add_to_cart | Use add_to_cart when a user adds an item to their shopping cart or list.
*ChargebackApi* | [**chargeback**](docs/Api/ChargebackApi.md#chargeback) | **POST** /v1/chargeback | Use chargeback to capture a chargeback reported on a transaction. This event can be called multiple times to record changes to the chargeback state.
*CreateAccountApi* | [**createAccount**](docs/Api/CreateAccountApi.md#createaccount) | **POST** /v1/create_account | Use create_account to pass user details at user registration.
*CreateOrderApi* | [**createOrder**](docs/Api/CreateOrderApi.md#createorder) | **POST** /v1/createOrder | Submit a new or existing order to Thirdwatch for review. This API should contain order item info, the payment info, and user identity info.
*CustomEventApi* | [**customEvent**](docs/Api/CustomEventApi.md#customevent) | **POST** /v1/custom_event | Use order_status to track the order processing workflow of a previously submitted order.
*ItemStatusApi* | [**itemStatus**](docs/Api/ItemStatusApi.md#itemstatus) | **POST** /v1/item_status | Use item_status to update the status of item that you’ve already pass to Thirdwatch.
*LinkSessionToUserApi* | [**linkSessionToUser**](docs/Api/LinkSessionToUserApi.md#linksessiontouser) | **POST** /v1/link_session_to_user | Use link_session_to_user to associate specific session to a user. Generally used only in anonymous checkout workflows.
*LoginApi* | [**login**](docs/Api/LoginApi.md#login) | **POST** /v1/login | Use login to record when a user attempts to log in.
*LogoutApi* | [**logout**](docs/Api/LogoutApi.md#logout) | **POST** /v1/logout | Use logout to record when a user logs out.
*OrderStatusApi* | [**orderStatus**](docs/Api/OrderStatusApi.md#orderstatus) | **POST** /v1/order_status | Use order_status to track the order processing workflow of a previously submitted order.
*RemoveFromCartApi* | [**removeFromCart**](docs/Api/RemoveFromCartApi.md#removefromcart) | **POST** /v1/remove_from_cart | Use remove_from_cart when a user removes an item from their shopping cart or list.
*ReportItemApi* | [**reportItem**](docs/Api/ReportItemApi.md#reportitem) | **POST** /v1/report_item | Use report_item to let us know when another user reports that this item may violate your company’s policies.
*SendMessageApi* | [**sendMessage**](docs/Api/SendMessageApi.md#sendmessage) | **POST** /v1/send_message | Use send_message to record when a user sends a message to other i.e. seller, support.
*SubmitReviewApi* | [**submitReview**](docs/Api/SubmitReviewApi.md#submitreview) | **POST** /v1/submit_review | Use submit_review when a user-submitted review of a product or seller.
*TagAPIApi* | [**tagUser**](docs/Api/TagAPIApi.md#taguser) | **POST** /v1/tag | The Tag API enables you to tell Thirdwatch which of your users are bad and which are good.
*TransactionApi* | [**transaction**](docs/Api/TransactionApi.md#transaction) | **POST** /v1/transaction | Use transaction to record attempts results to Pay, Transfer money, Refund or other transactions.
*UntagAPIApi* | [**unTagUser**](docs/Api/UntagAPIApi.md#untaguser) | **POST** /v1/untag | If you are unsure whether a user is bad or good, you can always remove tag and leave the user in a neutral state.
*UpdateAccountApi* | [**updateAccount**](docs/Api/UpdateAccountApi.md#updateaccount) | **POST** /v1/update_account | Use update_account to record changes to the user&#39;s account information.
*UpdateOrderApi* | [**updateOrder**](docs/Api/UpdateOrderApi.md#updateorder) | **POST** /v1/update_order | Update details of an existing order.


## Documentation For Models

 - [AddPromotion](docs/Model/AddPromotion.md)
 - [AddToCart](docs/Model/AddToCart.md)
 - [BillingAddress](docs/Model/BillingAddress.md)
 - [Chargeback](docs/Model/Chargeback.md)
 - [CreateAccount](docs/Model/CreateAccount.md)
 - [CreateOrder](docs/Model/CreateOrder.md)
 - [Custom](docs/Model/Custom.md)
 - [CustomInfo](docs/Model/CustomInfo.md)
 - [ErrorResponse](docs/Model/ErrorResponse.md)
 - [EventResponse](docs/Model/EventResponse.md)
 - [Item](docs/Model/Item.md)
 - [ItemStatus](docs/Model/ItemStatus.md)
 - [LinkSessionToUser](docs/Model/LinkSessionToUser.md)
 - [Login](docs/Model/Login.md)
 - [Logout](docs/Model/Logout.md)
 - [OrderStatus](docs/Model/OrderStatus.md)
 - [PaymentMethod](docs/Model/PaymentMethod.md)
 - [Promotion](docs/Model/Promotion.md)
 - [RemoveFromCart](docs/Model/RemoveFromCart.md)
 - [ReportItem](docs/Model/ReportItem.md)
 - [Seller](docs/Model/Seller.md)
 - [SendMessage](docs/Model/SendMessage.md)
 - [ShippingAddress](docs/Model/ShippingAddress.md)
 - [SubmitReview](docs/Model/SubmitReview.md)
 - [Tag](docs/Model/Tag.md)
 - [Transaction](docs/Model/Transaction.md)
 - [UnTag](docs/Model/UnTag.md)
 - [UpdateAccount](docs/Model/UpdateAccount.md)
 - [UpdateOrder](docs/Model/UpdateOrder.md)


## Documentation For Authorization


## api_key

- **Type**: API key
- **API key parameter name**: X-THIRDWATCH-API-KEY
- **Location**: HTTP header


## Author




