<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\TwilioSMSNotification\Helper;

/**
 * Class Constant
 * @package Yosto\TwilioSMSNotification\Helper
 */
class Constant
{
    const TWILIOSMS_TABLE = 'yosto_twiliosms_commonlog';

    const TWILIOSMS_ID = 'twiliosms_id';

    const TWILIOSMS_TABLE_COMMENT = 'Twilio SMS notification table';

    const TWILIOSMS_MODEL = 'Yosto\TwilioSMSNotification\Model\TwilioSMS';

    const TWILIOSMS_RESOURCE_MODEL
        = 'Yosto\TwilioSMSNotification\Model\ResourceModel\TwilioSMS';

    const IS_IDENTITY = 'identity';
    const IS_UNSIGNED = 'unsigned';
    const IS_NULLABLE = 'nullable';
    const IS_PRIMARY = 'primary';
    const DEFAULT_PROPERTY = 'default';
    const DB_TYPE = 'type';
    const INNO_DB = 'InnoDB';
    const CHARSET = 'charset';
    const UTF8 = 'utf8';

    const CATEGORY = "category";
    const TIME = "time";
    const PHONE_LIST = "phone_list";
    const MESSAGE = "message";

    const TEST_MESSAGE = "Hello, you are testing twilio message";

    const ORDER_ID_REPLACE = "{order_id}";
    const GRAND_TOTAL_REPLACE = "{grand_total}";
    const NAME_REPLACE = "{name}";
    const EMAIL_REPLACE = "{email}";
    const PRODUCTS = "{products}";
    const FROM = "From";
    const TO = "To";
    const BODY = "Body";

    const NEW_ORDER = 'NEW_ORDER';
    const INVOICE_CREATED = 'INVOICE_CREATED';
    const ORDER_CANCELED = 'ORDER_CANCELED';
    const ORDER_HOLD = 'ORDER_HOLD';
    const ORDER_UNHOLD = 'ORDER_UNHOLD';
    const CUSTOMER_REGISTRATION = 'CUSTOMER_REGISTRATION';
    const REFUND_CREDITMEMO = "CREDIT_MEMO_REFUND";
    const SHIPMENT_CREATED = "SHIPMENT_CREATED";

    const ENABLE_PATH = "twiliosmsnotification/twilioconfig/enable";
    const ACCOUNT_SID_PATH = "twiliosmsnotification/twilioconfig/account_sid";
    const ACCOUNT_TOKEN_PATH = "twiliosmsnotification/twilioconfig/account_token";
    const TWILIO_PHONE_PATH = "twiliosmsnotification/twilioconfig/phone";
    const OWNER_PHONE_PATH = "twiliosmsnotification/twilioconfig/admin_phone";

    const NEW_ORDER_PATH = "twiliosmsnotification/storeownersms/neworder_sms";
    const CUSTOMER_REGISTRATION_PATH = "twiliosmsnotification/storeownersms/customer_registration";

    const ORDER_HOLD_PATH = "twiliosmsnotification/smstocustomer/order_hold";
    const ORDER_UNHOLD_PATH = "twiliosmsnotification/smstocustomer/order_unhold";
    const ORDER_CANCELED_PATH = "twiliosmsnotification/smstocustomer/order_cancelled";
    const INVOICE_CREATED_PATH = "twiliosmsnotification/smstocustomer/invoice_created";
    const REFUND_CREDITMEMO_PATH = "twiliosmsnotification/smstocustomer/refund_order";
    const SHIPMENT_CREATED_PATH = "twiliosmsnotification/smstocustomer/shipment_created";
    const NEW_ORDER_CUSTOMER_PLACE_PATH = "twiliosmsnotification/smstocustomer/customer_placeorder";

    const IS_ENABLE = "1";
}