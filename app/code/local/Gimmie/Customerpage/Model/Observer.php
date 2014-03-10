<?php
require_once(Mage::getBaseDir('lib').'/Gimmie/Gimmie.sdk.php');

class Gimmie_Customerpage_Model_Observer
{

  var $gimmie = Array(
    "gimmie_trigger_purchase" => "did_purchase",
    "gimmie_trigger_topspender" => "top_spender",
    "gimmie_trigger_birthdaypurchase" => "did_birthday_purchase",
    "gimmie_trigger_referral" => "did_referral"
  );

  const COOKIE_KEY_SOURCE = 'gimmie_customerpage_source';

  private function getConfig() {
    $dfd = Mage::getStoreConfig('Gimmie');
    return $dfd['message'];
  }

  private function getGimmie($config, $email) {
    $key = $config['consumer_key'];
    $secret = $config['secret_key'];

    $gimmie = Gimmie::getInstance($key, $secret);
    $gimmie->login($email);
    return $gimmie;
  }

  public function captureReferral(Varien_Event_Observer $observer)
  {
    $utmSource =$_GET['id'];

    if ($utmSource) {
      // here we will save the referrer affiliate ID
      Mage::getModel('core/cookie')->set(
        self::COOKIE_KEY_SOURCE,
        $utmSource,
        30 * 86400);
    }

    $dfd = Mage::getStoreConfig('Gimmie');
  }

  public function triggerReferral($event) {
    $event = 'did_magento_user_referral_other_user';
    $config = $this->getConfig();

    if ($config['gimmie_enabled'] && $config['gimmie_trigger_'.$event]) {
      $id= Mage::getModel('core/cookie')->get(
        Gimmie_Customerpage_Model_Observer::COOKIE_KEY_SOURCE
      );

      $customerData = Mage::getModel('customer/customer')->load($id)->getData();
      $email = $customerData['email'];

      $this->getGimmie($config, $email)->trigger($event);
    }
  }

  public function giveoutPointsAndTriggerPurchased($event)
  {
    $config = $this->getConfig();
    if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE && $config['gimmie_enabled']) {

      $order_id = $event->getOrder()->getId();
      $order = Mage::getModel('sales/order')->load($order_id);
      $email = $order->getCustomerEmail();

      $purchased_event = 'did_magento_user_purchased_item';
      if ($config["gimmie_trigger_$purchased_event"]) {
        $this->getGimmie($config, $email)->trigger($purchased_event);
      }

      $date = getdate(strtotime($order->getCustomerDob()));
      $birthMonth = $date['mon'];
      $currentMonth = date('n');

      $birthmonth_event = 'did_magento_user_born_this_month';
      if ($config["gimmie_trigger_$birthmonth_event"] && ($birthMonth == $currentMonth)) {
        $this->getGimmie($config, $email)->trigger($birthmonth_event);
      }

      $amountWithoutTax = $order->getGrandTotal() - $order->getShippingAmount();

      $dollarExchanges = is_numeric($config['purchase_exchange_dollar']) ? intval($config['purchase_exchange_dollar']) : -1;
      $pointsExchanges = is_numeric($config['purchase_exchange_points']) ? intval($config['purchase_exchange_points']) : -1;

      if (is_numeric($amountWithoutTax) && $amountWithoutTax > 0 && $dollarExchanges > 0 && $pointsExchanges > 0) {
        $totalPoints = $amountWithoutTax / $dollarExchanges * $pointsExchanges;
        $this->getGimmie($config, $email)->change_points($totalPoints, "Award $totalPoints for spending $amountWithoutTax");
      }

    }
  }

  public function monthTopSpender(Varian_Event_Observer $observer) {

    $event = 'did_magento_user_spent_the_most';
    $config = $this->getConfig();
    if ($config['gimmie_enabled'] && $config["gimmie_trigger_$event"]) {

      $date = getdate(strtotime('-1 months'));
      $targetMonth = $date['mon'];
      $targetYear = $date['year'];

      $first = date('Y-m-d', mktime(0, 0, 0, $targetMonth, 1, $targetYear));
      $last = date('Y-m-t', mktime(0, 0, 0, $targetMonth, 1, $targetYear));

      $read = Mage::getModel('sales/order')->getCollection()->getConnection('core_read');
      $cursor = $read->query('SELECT * , SUM( grand_total ) AS `grand_total` FROM `sales_flat_order` WHERE (created_at >= :from) AND (created_at <= :to) AND `status` = "complete" ORDER BY `grand_total`    DESC LIMIT 0,1', array('from' => $first, 'to' => $last));
      $row = $cursor->fetch();

      $email = $row['customer_email'];
      $this->getGimmie($config, $email)->trigger($event);
    }

  }

  public function toOptionArray()
  {
    return array(

      array('value'=>AF, 'label'=>Mage::helper('helpdata')->__('Afghanistan')),
      array('value'=>AL, 'label'=>Mage::helper('helpdata')->__('Albania')),
      array('value'=>DZ, 'label'=>Mage::helper('helpdata')->__('Algeria')),

      array('value'=>'AS', 'label'=>Mage::helper('helpdata')->__('American Samoa')),

      array('value'=>AD, 'label'=>Mage::helper('helpdata')->__('Andorra')),
      array('value'=>AO, 'label'=>Mage::helper('helpdata')->__('Angola')),
      array('value'=>AI, 'label'=>Mage::helper('helpdata')->__('Anguilla')),

      array('value'=>AQ, 'label'=>Mage::helper('helpdata')->__('Antarctica')),
      array('value'=>AG, 'label'=>Mage::helper('helpdata')->__('Antigua and Barbuda')),
      array('value'=>AR, 'label'=>Mage::helper('helpdata')->__('Argentina')),
      array('value'=>AM, 'label'=>Mage::helper('helpdata')->__('Armenia')),

      array('value'=>AW, 'label'=>Mage::helper('helpdata')->__('Aruba')),
      array('value'=>AU, 'label'=>Mage::helper('helpdata')->__('Australia')),
      array('value'=>AT, 'label'=>Mage::helper('helpdata')->__('Austria')),
      array('value'=>AZ, 'label'=>Mage::helper('helpdata')->__('Azerbaijan')),
      array('value'=>BS, 'label'=>Mage::helper('helpdata')->__('Bahamas')),
      array('value'=>BH, 'label'=>Mage::helper('helpdata')->__('Bahrain')),
      array('value'=>BD, 'label'=>Mage::helper('helpdata')->__('Bangladesh')),
      array('value'=>BB, 'label'=>Mage::helper('helpdata')->__('Barbados')),
      array('value'=>BY, 'label'=>Mage::helper('helpdata')->__('Belarus')),
      array('value'=>BE, 'label'=>Mage::helper('helpdata')->__('Belgium')),
      array('value'=>BZ, 'label'=>Mage::helper('helpdata')->__('Belize')),
      array('value'=>BJ, 'label'=>Mage::helper('helpdata')->__('Benin')),
      array('value'=>BM, 'label'=>Mage::helper('helpdata')->__('Bermuda')),
      array('value'=>BT, 'label'=>Mage::helper('helpdata')->__('Bhutan')),
      array('value'=>BO, 'label'=>Mage::helper('helpdata')->__('Bolivia')),
      array('value'=>BA, 'label'=>Mage::helper('helpdata')->__('Bosnia and Herzegovina')),
      array('value'=>BW, 'label'=>Mage::helper('helpdata')->__('Botswana')),
      array('value'=>BV, 'label'=>Mage::helper('helpdata')->__('Bouvet Island')),
      array('value'=>BR, 'label'=>Mage::helper('helpdata')->__('Brazil')),
      array('value'=>IO, 'label'=>Mage::helper('helpdata')->__('British Indian Ocean Territory')),
      array('value'=>VG, 'label'=>Mage::helper('helpdata')->__('British Virgin Islands')),
      array('value'=>BN, 'label'=>Mage::helper('helpdata')->__('Brunei')),
      array('value'=>BG, 'label'=>Mage::helper('helpdata')->__('Bulgaria')),
      array('value'=>BF, 'label'=>Mage::helper('helpdata')->__('Burkina Faso')),
      array('value'=>BI, 'label'=>Mage::helper('helpdata')->__('Burundi')),
      array('value'=>KH, 'label'=>Mage::helper('helpdata')->__('Cambodia')),
      array('value'=>CM, 'label'=>Mage::helper('helpdata')->__('Cameroon')),
      array('value'=>CA, 'label'=>Mage::helper('helpdata')->__('Canada')),
      array('value'=>CV, 'label'=>Mage::helper('helpdata')->__('Cape Verde')),
      array('value'=>KY, 'label'=>Mage::helper('helpdata')->__('Cayman Islands')),
      array('value'=>CF, 'label'=>Mage::helper('helpdata')->__('Central African Republic')),
      array('value'=>TD, 'label'=>Mage::helper('helpdata')->__('Chad')),
      array('value'=>CL, 'label'=>Mage::helper('helpdata')->__('Chile')),
      array('value'=>CN, 'label'=>Mage::helper('helpdata')->__('China')),
      array('value'=>CX, 'label'=>Mage::helper('helpdata')->__('Christmas Island')),
      array('value'=>CC, 'label'=>Mage::helper('helpdata')->__('Cocos [Keeling] Islands')),
      array('value'=>CO, 'label'=>Mage::helper('helpdata')->__('Colombia')),
      array('value'=>KM, 'label'=>Mage::helper('helpdata')->__('Comoros')),
      array('value'=>CG, 'label'=>Mage::helper('helpdata')->__('Congo - Brazzaville')),
      array('value'=>CD, 'label'=>Mage::helper('helpdata')->__('Congo - Kinshasa')),
      array('value'=>CK, 'label'=>Mage::helper('helpdata')->__('Cook Islands')),
      array('value'=>CR, 'label'=>Mage::helper('helpdata')->__('Costa Rica')),
      array('value'=>HR, 'label'=>Mage::helper('helpdata')->__('Croatia')),
      array('value'=>CU, 'label'=>Mage::helper('helpdata')->__('Cuba')),
      array('value'=>CY, 'label'=>Mage::helper('helpdata')->__('Cyprus')),
      array('value'=>CZ, 'label'=>Mage::helper('helpdata')->__('Czech Republic')),
      array('value'=>CI, 'label'=>Mage::helper('helpdata')->__('Côte dIvoire')),
      array('value'=>DK, 'label'=>Mage::helper('helpdata')->__('Denmark')),
      array('value'=>DJ, 'label'=>Mage::helper('helpdata')->__('Djibouti')),
      array('value'=>DM, 'label'=>Mage::helper('helpdata')->__('Dominica')),
      array('value'=>'DO', 'label'=>Mage::helper('helpdata')->__('Dominican Republic')),
      array('value'=>EC, 'label'=>Mage::helper('helpdata')->__('Ecuador')),
      array('value'=>EG, 'label'=>Mage::helper('helpdata')->__('Egypt')),
      array('value'=>SV, 'label'=>Mage::helper('helpdata')->__('El Salvador')),
      array('value'=>GQ, 'label'=>Mage::helper('helpdata')->__('Equatorial Guinea')),
      array('value'=>ER, 'label'=>Mage::helper('helpdata')->__('Eritrea')),
      array('value'=>EE, 'label'=>Mage::helper('helpdata')->__('Estonia')),
      array('value'=>ET, 'label'=>Mage::helper('helpdata')->__('Ethiopia')),
      array('value'=>FK, 'label'=>Mage::helper('helpdata')->__('Falkland Islands')),
      array('value'=>FO, 'label'=>Mage::helper('helpdata')->__('Faroe Islands')),
      array('value'=>FJ, 'label'=>Mage::helper('helpdata')->__('Fiji')),
      array('value'=>FI, 'label'=>Mage::helper('helpdata')->__('Finland')),
      array('value'=>FR, 'label'=>Mage::helper('helpdata')->__('France')),
      array('value'=>GF, 'label'=>Mage::helper('helpdata')->__('French Guiana')),
      array('value'=>PF, 'label'=>Mage::helper('helpdata')->__('French Polynesia')),
      array('value'=>TF, 'label'=>Mage::helper('helpdata')->__('French Southern Territories')),
      array('value'=>GA, 'label'=>Mage::helper('helpdata')->__('Gabon')),
      array('value'=>GM, 'label'=>Mage::helper('helpdata')->__('Gambia')),
      array('value'=>GE, 'label'=>Mage::helper('helpdata')->__('Georgia')),
      array('value'=>DE, 'label'=>Mage::helper('helpdata')->__('Germany')),
      array('value'=>GH, 'label'=>Mage::helper('helpdata')->__('Ghana')),
      array('value'=>GI, 'label'=>Mage::helper('helpdata')->__('Gibraltar')),
      array('value'=>GR, 'label'=>Mage::helper('helpdata')->__('Greece')),
      array('value'=>GL, 'label'=>Mage::helper('helpdata')->__('Greenland')),
      array('value'=>GD, 'label'=>Mage::helper('helpdata')->__('Grenada')),
      array('value'=>GP, 'label'=>Mage::helper('helpdata')->__('Guadeloupe')),
      array('value'=>GU, 'label'=>Mage::helper('helpdata')->__('Guam')),
      array('value'=>GT, 'label'=>Mage::helper('helpdata')->__('Guatemala')),
      array('value'=>GG, 'label'=>Mage::helper('helpdata')->__('Guernsey')),
      array('value'=>GN, 'label'=>Mage::helper('helpdata')->__('Guinea')),
      array('value'=>GW, 'label'=>Mage::helper('helpdata')->__('Guinea-Bissau')),
      array('value'=>GY, 'label'=>Mage::helper('helpdata')->__('Guyana')),
      array('value'=>HT, 'label'=>Mage::helper('helpdata')->__('Haiti')),
      array('value'=>HM, 'label'=>Mage::helper('helpdata')->__('Heard Island and McDonald Islands')),
      array('value'=>HN, 'label'=>Mage::helper('helpdata')->__('Honduras')),
      array('value'=>HK, 'label'=>Mage::helper('helpdata')->__('Hong Kong SAR China')),
      array('value'=>HU, 'label'=>Mage::helper('helpdata')->__('Hungary')),
      array('value'=>IS, 'label'=>Mage::helper('helpdata')->__('Iceland')),
      array('value'=>IN, 'label'=>Mage::helper('helpdata')->__('India')),
      array('value'=>ID, 'label'=>Mage::helper('helpdata')->__('Indonesia')),
      array('value'=>IR, 'label'=>Mage::helper('helpdata')->__('Iran')),
      array('value'=>IQ, 'label'=>Mage::helper('helpdata')->__('Iraq')),
      array('value'=>IE, 'label'=>Mage::helper('helpdata')->__('Ireland')),
      array('value'=>IM, 'label'=>Mage::helper('helpdata')->__('Isle of Man')),
      array('value'=>IL, 'label'=>Mage::helper('helpdata')->__('Israel')),
      array('value'=>IT, 'label'=>Mage::helper('helpdata')->__('Italy')),
      array('value'=>JM, 'label'=>Mage::helper('helpdata')->__('Jamaica')),
      array('value'=>JP, 'label'=>Mage::helper('helpdata')->__('Japan')),
      array('value'=>JE, 'label'=>Mage::helper('helpdata')->__('Jersey')),
      array('value'=>JO, 'label'=>Mage::helper('helpdata')->__('Jordan')),
      array('value'=>KZ, 'label'=>Mage::helper('helpdata')->__('Kazakhstan')),
      array('value'=>KE, 'label'=>Mage::helper('helpdata')->__('Kenya')),
      array('value'=>KI, 'label'=>Mage::helper('helpdata')->__('Kiribati')),
      array('value'=>KW, 'label'=>Mage::helper('helpdata')->__('Kuwait')),
      array('value'=>KG, 'label'=>Mage::helper('helpdata')->__('Kyrgyzstan')),
      array('value'=>LA, 'label'=>Mage::helper('helpdata')->__('Laos')),
      array('value'=>LV, 'label'=>Mage::helper('helpdata')->__('Latvia')),
      array('value'=>LB, 'label'=>Mage::helper('helpdata')->__('Lebanon')),
      array('value'=>LS, 'label'=>Mage::helper('helpdata')->__('Lesotho')),
      array('value'=>LR, 'label'=>Mage::helper('helpdata')->__('Liberia')),
      array('value'=>LY, 'label'=>Mage::helper('helpdata')->__('Libya')),
      array('value'=>LI, 'label'=>Mage::helper('helpdata')->__('Liechtenstein')),
      array('value'=>LT, 'label'=>Mage::helper('helpdata')->__('Lithuania')),
      array('value'=>LU, 'label'=>Mage::helper('helpdata')->__('Luxembourg')),
      array('value'=>MO, 'label'=>Mage::helper('helpdata')->__('Macau SAR China')),
      array('value'=>MK, 'label'=>Mage::helper('helpdata')->__('Macedonia')),
      array('value'=>MG, 'label'=>Mage::helper('helpdata')->__('Madagascar')),
      array('value'=>MW, 'label'=>Mage::helper('helpdata')->__('Malawi')),
      array('value'=>MY, 'label'=>Mage::helper('helpdata')->__('Malaysia')),
      array('value'=>MV, 'label'=>Mage::helper('helpdata')->__('Maldives')),
      array('value'=>ML, 'label'=>Mage::helper('helpdata')->__('Mali')),
      array('value'=>MT, 'label'=>Mage::helper('helpdata')->__('Malta')),
      array('value'=>MH, 'label'=>Mage::helper('helpdata')->__('Marshall Islands')),
      array('value'=>MQ, 'label'=>Mage::helper('helpdata')->__('Martinique')),
      array('value'=>MR, 'label'=>Mage::helper('helpdata')->__('Mauritania')),
      array('value'=>MU, 'label'=>Mage::helper('helpdata')->__('Mauritius')),
      array('value'=>YT, 'label'=>Mage::helper('helpdata')->__('Mayotte')),
      array('value'=>MX, 'label'=>Mage::helper('helpdata')->__('Mexico')),
      array('value'=>FM, 'label'=>Mage::helper('helpdata')->__('Micronesia')),
      array('value'=>MD, 'label'=>Mage::helper('helpdata')->__('Moldova')),
      array('value'=>MC, 'label'=>Mage::helper('helpdata')->__('Monaco')),
      array('value'=>MN, 'label'=>Mage::helper('helpdata')->__('Mongolia')),
      array('value'=>ME, 'label'=>Mage::helper('helpdata')->__('Montenegro')),
      array('value'=>MS, 'label'=>Mage::helper('helpdata')->__('Montserrat')),
      array('value'=>MA, 'label'=>Mage::helper('helpdata')->__('Morocco')),
      array('value'=>MZ, 'label'=>Mage::helper('helpdata')->__('Mozambique')),
      array('value'=>MM, 'label'=>Mage::helper('helpdata')->__('Myanmar [Burma]')),
      array('value'=>NA, 'label'=>Mage::helper('helpdata')->__('Namibia')),
      array('value'=>NR, 'label'=>Mage::helper('helpdata')->__('Nauru')),
      array('value'=>NP, 'label'=>Mage::helper('helpdata')->__('Nepal')),
      array('value'=>NL, 'label'=>Mage::helper('helpdata')->__('Netherlands')),
      array('value'=>AN, 'label'=>Mage::helper('helpdata')->__('Netherlands Antilles')),
      array('value'=>NC, 'label'=>Mage::helper('helpdata')->__('New Caledonia')),
      array('value'=>NZ, 'label'=>Mage::helper('helpdata')->__('New Zealand')),
      array('value'=>NI, 'label'=>Mage::helper('helpdata')->__('Nicaragua')),
      array('value'=>NE, 'label'=>Mage::helper('helpdata')->__('Niger')),
      array('value'=>NG, 'label'=>Mage::helper('helpdata')->__('Nigeria')),
      array('value'=>NU, 'label'=>Mage::helper('helpdata')->__('Niue')),
      array('value'=>NF, 'label'=>Mage::helper('helpdata')->__('Norfolk Island')),
      array('value'=>KP, 'label'=>Mage::helper('helpdata')->__('North Korea')),
      array('value'=>MP, 'label'=>Mage::helper('helpdata')->__('Northern Mariana Islands')),
      array('value'=>NO, 'label'=>Mage::helper('helpdata')->__('Norway')),
      array('value'=>OM, 'label'=>Mage::helper('helpdata')->__('Oman')),
      array('value'=>PK, 'label'=>Mage::helper('helpdata')->__('Pakistan')),
      array('value'=>PW, 'label'=>Mage::helper('helpdata')->__('Palau')),
      array('value'=>PS, 'label'=>Mage::helper('helpdata')->__('Palestinian Territories')),
      array('value'=>PA, 'label'=>Mage::helper('helpdata')->__('Panama')),
      array('value'=>PG, 'label'=>Mage::helper('helpdata')->__('Papua New Guinea')),
      array('value'=>PY, 'label'=>Mage::helper('helpdata')->__('Paraguay')),
      array('value'=>PE, 'label'=>Mage::helper('helpdata')->__('Peru')),
      array('value'=>PH, 'label'=>Mage::helper('helpdata')->__('Philippines')),
      array('value'=>PN, 'label'=>Mage::helper('helpdata')->__('Pitcairn Islands')),
      array('value'=>PL, 'label'=>Mage::helper('helpdata')->__('Poland')),
      array('value'=>PT, 'label'=>Mage::helper('helpdata')->__('Portugal')),
      array('value'=>PR, 'label'=>Mage::helper('helpdata')->__('Puerto Rico')),
      array('value'=>QA, 'label'=>Mage::helper('helpdata')->__('Qatar')),
      array('value'=>RO, 'label'=>Mage::helper('helpdata')->__('Romania')),
      array('value'=>RU, 'label'=>Mage::helper('helpdata')->__('Russia')),
      array('value'=>RW, 'label'=>Mage::helper('helpdata')->__('Rwanda')),
      array('value'=>RE, 'label'=>Mage::helper('helpdata')->__('Réunion')),
      array('value'=>BL, 'label'=>Mage::helper('helpdata')->__('Saint Barthélemy')),
      array('value'=>SH, 'label'=>Mage::helper('helpdata')->__('Saint Helena')),
      array('value'=>KN, 'label'=>Mage::helper('helpdata')->__('Saint Kitts and Nevis')),
      array('value'=>LC, 'label'=>Mage::helper('helpdata')->__('Saint Lucia')),
      array('value'=>MF, 'label'=>Mage::helper('helpdata')->__('Saint Martin')),
      array('value'=>PM, 'label'=>Mage::helper('helpdata')->__('Saint Pierre and Miquelon')),
      array('value'=>VC, 'label'=>Mage::helper('helpdata')->__('Saint Vincent and the Grenadines')),
      array('value'=>WS, 'label'=>Mage::helper('helpdata')->__('Samoa')),
      array('value'=>SM, 'label'=>Mage::helper('helpdata')->__('San Marino')),
      array('value'=>SA, 'label'=>Mage::helper('helpdata')->__('Saudi Arabia')),
      array('value'=>SN, 'label'=>Mage::helper('helpdata')->__('Senegal')),
      array('value'=>RS, 'label'=>Mage::helper('helpdata')->__('Serbia')),
      array('value'=>SC, 'label'=>Mage::helper('helpdata')->__('Seychelles')),
      array('value'=>SL, 'label'=>Mage::helper('helpdata')->__('Sierra Leone')),
      array('value'=>SG, 'label'=>Mage::helper('helpdata')->__('Singapore')),
      array('value'=>SK, 'label'=>Mage::helper('helpdata')->__('Slovakia')),
      array('value'=>SI, 'label'=>Mage::helper('helpdata')->__('Slovenia')),
      array('value'=>SB, 'label'=>Mage::helper('helpdata')->__('Solomon Islands')),
      array('value'=>SO, 'label'=>Mage::helper('helpdata')->__('Somalia')),
      array('value'=>ZA, 'label'=>Mage::helper('helpdata')->__('South Africa')),
      array('value'=>GS, 'label'=>Mage::helper('helpdata')->__('South Georgia and the South Sandwich Islands')),
      array('value'=>KR, 'label'=>Mage::helper('helpdata')->__('South Korea')),
      array('value'=>ES, 'label'=>Mage::helper('helpdata')->__('Spain')),
      array('value'=>LK, 'label'=>Mage::helper('helpdata')->__('Sri Lanka')),
      array('value'=>SD, 'label'=>Mage::helper('helpdata')->__('Sudan')),
      array('value'=>SR, 'label'=>Mage::helper('helpdata')->__('Suriname')),
      array('value'=>SJ, 'label'=>Mage::helper('helpdata')->__('Svalbard and Jan Mayen')),
      array('value'=>SZ, 'label'=>Mage::helper('helpdata')->__('Swaziland')),
      array('value'=>SE, 'label'=>Mage::helper('helpdata')->__('Sweden')),
      array('value'=>CH, 'label'=>Mage::helper('helpdata')->__('Switzerland')),
      array('value'=>SY, 'label'=>Mage::helper('helpdata')->__('Syria')),
      array('value'=>ST, 'label'=>Mage::helper('helpdata')->__('São Tomé and Príncipe')),
      array('value'=>TW, 'label'=>Mage::helper('helpdata')->__('Taiwan')),
      array('value'=>TJ, 'label'=>Mage::helper('helpdata')->__('Tajikistan')),
      array('value'=>TZ, 'label'=>Mage::helper('helpdata')->__('Tanzania')),
      array('value'=>TH, 'label'=>Mage::helper('helpdata')->__('Thailand')),
      array('value'=>TL, 'label'=>Mage::helper('helpdata')->__('Timor-Leste')),
      array('value'=>TG, 'label'=>Mage::helper('helpdata')->__('Togo')),
      array('value'=>TK, 'label'=>Mage::helper('helpdata')->__('Tokelau')),
      array('value'=>TO, 'label'=>Mage::helper('helpdata')->__('Tonga')),
      array('value'=>TT, 'label'=>Mage::helper('helpdata')->__('Trinidad and Tobago')),
      array('value'=>TN, 'label'=>Mage::helper('helpdata')->__('Tunisia')),
      array('value'=>TR, 'label'=>Mage::helper('helpdata')->__('Turkey')),
      array('value'=>TM, 'label'=>Mage::helper('helpdata')->__('Turkmenistan')),
      array('value'=>TC, 'label'=>Mage::helper('helpdata')->__('Turks and Caicos Islands')),
      array('value'=>TV, 'label'=>Mage::helper('helpdata')->__('Tuvalu')),
      array('value'=>UM, 'label'=>Mage::helper('helpdata')->__('U.S. Minor Outlying Islands')),
      array('value'=>VI, 'label'=>Mage::helper('helpdata')->__('U.S. Virgin Islands')),
      array('value'=>UG, 'label'=>Mage::helper('helpdata')->__('Uganda')),
      array('value'=>UA, 'label'=>Mage::helper('helpdata')->__('Ukraine')),
      array('value'=>AE, 'label'=>Mage::helper('helpdata')->__('United Arab Emirates')),
      array('value'=>GB, 'label'=>Mage::helper('helpdata')->__('United Kingdom')),
      array('value'=>US, 'label'=>Mage::helper('helpdata')->__('United States')),
      array('value'=>UY, 'label'=>Mage::helper('helpdata')->__('Uruguay')),
      array('value'=>UZ, 'label'=>Mage::helper('helpdata')->__('Uzbekistan')),
      array('value'=>VU, 'label'=>Mage::helper('helpdata')->__('Vanuatu')),
      array('value'=>VA, 'label'=>Mage::helper('helpdata')->__('Vatican City')),
      array('value'=>VE, 'label'=>Mage::helper('helpdata')->__('Venezuela')),
      array('value'=>VN, 'label'=>Mage::helper('helpdata')->__('Vietnam')),
      array('value'=>WF, 'label'=>Mage::helper('helpdata')->__('Wallis and Futuna')),
      array('value'=>EH, 'label'=>Mage::helper('helpdata')->__('Western Sahara')),
      array('value'=>YE, 'label'=>Mage::helper('helpdata')->__('Yemen')),
      array('value'=>ZM, 'label'=>Mage::helper('helpdata')->__('Zambia')),
      array('value'=>ZW, 'label'=>Mage::helper('helpdata')->__('Zimbabwe')),
      array('value'=>AX, 'label'=>Mage::helper('helpdata')->__('Åland Islands')),

    );
  }
}
