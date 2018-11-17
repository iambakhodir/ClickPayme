Click va Payme to'lov sistemasida kartani yaratish va karta orqali to'lov qilish uchun Yii1 frameworki uchun component.

`protected/config/main.php`

```php
 'components' => array(
    'ClickPayme' => array(
        'class' => 'application.components.ClickPayme',
        'paycomId' => '', //paycom merchant id
        'paycomKey' => '', //paycom token
        'clickMerchantId' => '', //click merchant id
        'clickServiceId' => '', //click service id,
        'clickSecretKey' => '', //click secret key
        'clickMerchantUserId' => '', //click merchant user id
    ),
 ....
 )
```