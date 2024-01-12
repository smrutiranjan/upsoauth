<?php
class upsoauth {
	var $code, $title, $description, $icon, $enabled;
	// class constructor
	function upsoauth() {
      global $order;

		$this->code = 'upsoauth';
		$this->name = MODULE_SHIPPING_UPSOUTH_TEXT_DESCRIPTION;
		$this->title = MODULE_SHIPPING_UPSOUTH_TEXT_TITLE;
		$this->description = MODULE_SHIPPING_UPSOUTH_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_SHIPPING_UPSOUTH_SORT_ORDER;
		//$this->icon = 'https://www.pispeakers.com/images/ups.png'; 
		$this->icon = '';
		$this->enabled = ((MODULE_SHIPPING_UPSOUTH_STATUS == 'True') ? true : false);
		$this->clientid = MODULE_SHIPPING_UPSOUTH_CLIENT_ID;
		$this->clientsecret = MODULE_SHIPPING_UPSOUTH_CLIENT_SECRET;
		$this->origin = MODULE_SHIPPING_UPSOUTH_ORIGIN;
		$this->origin_city = MODULE_SHIPPING_UPSOUTH_CITY;
		$this->origin_stateprov = MODULE_SHIPPING_UPSOUTH_STATEPROV;
		$this->origin_country = MODULE_SHIPPING_UPSOUTH_COUNTRY;
		$this->origin_postalcode = MODULE_SHIPPING_UPSOUTH_POSTALCODE;
		 
		$this->access_token='';
		$this->host = ((MODULE_SHIPPING_UPSOUTH_MODE == 'Test') ? 'wwwcie.ups.com' : 'onlinetools.ups.com');
		$this->timeintransit = '0';
        $this->timeInTransitView = MODULE_SHIPPING_UPSOAUTH_TIME_IN_TRANSIT_VIEW;
        $this->weight_for_timeintransit = '0';
        $now_unix_time = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        $this->today_unix_time = $now_unix_time;
        $this->today = date("Ymd");
		$this->email_errors = ((MODULE_SHIPPING_UPSOUTH_EMAIL_ERRORS == 'Yes') ? true : false);
		$this->quote_type = 'Commercial';
		$this->items_qty = 0;
		// insurance addition
        $this->insure_package = false;
		$this->pickup_method = MODULE_SHIPPING_UPSOUTH_PICKUP_METHOD;
		$this->package_type = MODULE_SHIPPING_UPSOUTH_PACKAGE_TYPE;
		if (defined('SHIPPING_UNIT_WEIGHT')) {
          $this->unit_weight = SHIPPING_UNIT_WEIGHT;
        } else {
          // for those who will undoubtedly forget or not know how to run the configuration_shipping.sql
          // we will set the default to pounds (LBS) and inches (IN)
          $this->unit_weight = 'LBS';
        }
        if (defined('SHIPPING_UNIT_LENGTH')) {
          $this->unit_length = SHIPPING_UNIT_LENGTH;
        } else {
          $this->unit_length = 'IN';
        }
        if (defined('SHIPPING_DIMENSIONS_SUPPORT') && SHIPPING_DIMENSIONS_SUPPORT == 'Ready-to-ship only') {
          $this->dimensions_support = 1;
        } elseif (defined('SHIPPING_DIMENSIONS_SUPPORT') && SHIPPING_DIMENSIONS_SUPPORT == 'With product dimensions') {
          $this->dimensions_support = 2;
        } else {
          $this->dimensions_support = 0;
        }
		 $this->pkgvalue = ceil($order->info['subtotal']); 
		 $this->logfile = '/home/pispeake/public_html/catalog/includes/modules/shipping/upsoauth.log';
		  $this->use_exec = '0';
		   if (($this->enabled == true) && ((int)MODULE_SHIPPING_UPSXML_RATES_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_UPSXML_RATES_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }
            if ($check_flag == false) {
                $this->enabled = false;
            }
        }

        // Available pickup types - set in admin
        $this->pickup_methods = array(
            'Daily Pickup' => '01',
            'Customer Counter' => '03',
            'One Time Pickup' => '06',
            'On Call Air Pickup' => '07',
            'Suggested Retail Rates (UPS Store)' => '11',
            'Letter Center' => '19',
            'Air Service Center' => '20'
        );

        // Available package types
        $this->package_types = array(
            'UPS Letter' => '01',
            'Package' => '02',
            'UPS Tube' => '03',
            'UPS Pak' => '04',
            'UPS Express Box' => '21',
            'UPS 25kg Box' => '24',
            'UPS 10kg Box' => '25'
        );
		// Human-readable Service Code lookup table. The values returned by the Rates and Service "shop" method are numeric.
        // Using these codes, and the administratively defined Origin, the proper human-readable service name is returned.
        // Note: The origin specified in the admin configuration affects only the product name as displayed to the user.
        $this->service_codes = array(
            // US Origin
            'US Origin' => array(
                '01' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_01,
                '02' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_02,
                '03' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_03,
                '07' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_07,
                '08' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_08,
                '11' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_11,
                '12' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_12,
                '13' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_13,
                '14' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_14,
                '54' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_54,
                '59' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_59,
                '65' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_US_ORIGIN_65
            ),
            // Canada Origin
            'Canada Origin' => array(
                '01' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_01,
                '02' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_02,
                '07' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_07,
                '08' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_08,
                '11' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_11,
                '12' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_12,
                '13' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_13,
                '14' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_14,
                '54' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_54,
                '65' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_CANADA_ORIGIN_65
            ),
            // European Union Origin
            'European Union Origin' => array(
                '07' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_07,
                '08' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_08,
                '11' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_11,
                '54' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_54,
                '65' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_65,
                // next five services Poland domestic only
                '82' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_82,
                '83' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_83,
                '84' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_84,
                '85' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_85,
                '86' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_EU_ORIGIN_86
            ),
            // Puerto Rico Origin
            'Puerto Rico Origin' => array(
                '01' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_PR_ORIGIN_01,
                '02' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_PR_ORIGIN_02,
                '03' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_PR_ORIGIN_03,
                '07' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_PR_ORIGIN_07,
                '08' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_PR_ORIGIN_08,
                '14' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_PR_ORIGIN_14,
                '54' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_PR_ORIGIN_54,
                '65' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_PR_ORIGIN_65
            ),
            // Mexico Origin
            'Mexico Origin' => array(
                '07' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_MEXICO_ORIGIN_07,
                '08' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_MEXICO_ORIGIN_08,
                '54' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_MEXICO_ORIGIN_54,
                '65' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_MEXICO_ORIGIN_65
            ),
            // All other origins
            'All other origins' => array(
                // service code 7 seems to be gone after January 2, 2007
                '07' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_OTHER_ORIGIN_07,
                '08' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_OTHER_ORIGIN_08,
                '11' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_OTHER_ORIGIN_11,
                '54' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_OTHER_ORIGIN_54,
                '65' => MODULE_SHIPPING_UPSOUTH_SERVICE_CODE_OTHER_ORIGIN_65
            )
        );
    }
	function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_UPSOUTH_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }
	 function install() {
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable UPS', 'MODULE_SHIPPING_UPSOUTH_STATUS', 'True', 'Do you want to offer UPS shipping?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS oauth client id', 'MODULE_SHIPPING_UPSOUTH_CLIENT_ID', '', 'Enter UPS client id.', '6', '1', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS client secret', 'MODULE_SHIPPING_UPSOUTH_CLIENT_SECRET', '', 'Enter UPS client Secret.', '6', '1', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_SHIPPING_UPSOUTH_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '19', now())");	
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Test or Production Mode', 'MODULE_SHIPPING_UPSOUTH_MODE', 'Test', 'Use this module in Test or Production mode?', '6', '12', 'tep_cfg_select_option(array(\'Test\', \'Production\'), ', now())");

		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin City', 'MODULE_SHIPPING_UPSOUTH_CITY', '', 'Enter the name of the origin city.', '6', '8', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin State/Province', 'MODULE_SHIPPING_UPSOUTH_STATEPROV', '', 'Enter the two-letter code for your origin state/province.', '6', '9', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin Country', 'MODULE_SHIPPING_UPSOUTH_COUNTRY', '', 'Enter the two-letter code for your origin country.', '6', '10', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin Zip/Postal Code', 'MODULE_SHIPPING_UPSOUTH_POSTALCODE', '', 'Enter your origin zip/postalcode.', '6', '11', now())");
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Services', 'MODULE_SHIPPING_UPSOUTH_TYPES', '', 'Select the UPS services to be offered.', '6', '20', 'get_multioption_upsxml',  'upsxml_cfg_select_multioption_indexed(array(\'US_01\', \'US_02\', \'US_03\', \'US_07\', \'US_54\', \'US_08\', \'CAN_01\', \'US_11\', \'US_12\', \'US_13\', \'US_14\', \'CAN_02\', \'US_59\', \'US_65\', \'CAN_14\', \'MEX_54\', \'EU_82\', \'EU_83\', \'EU_84\', \'EU_85\', \'EU_86\'), ',  now())");
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Weight', 'MODULE_SHIPPING_UPSOUTH_WEIGHT1', 'True', 'Do you want to show number of packages and package weight?', '6', '28', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Time in Transit View Type', 'MODULE_SHIPPING_UPSOAUTH_TIME_IN_TRANSIT_VIEW', 'Not', 'If and how the module should display the time in transit to the customer.', '6', '13', 'tep_cfg_select_option(array(\'Not\',\'Raw\', \'Detailed\'), ', now())");
		  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_UPSOAUTH_RATES_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '18', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
		  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Packaging Type', 'MODULE_SHIPPING_UPSOUTH_PACKAGE_TYPE', 'Package', 'What kind of packaging do you use?', '6', '5', 'tep_cfg_select_option(array(\'Package\', \'UPS Letter\', \'UPS Tube\', \'UPS Pak\', \'UPS Express Box\', \'UPS 25kg Box\', \'UPS 10kg box\'), ', now())");
		  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Pickup Method', 'MODULE_SHIPPING_UPSOUTH_PICKUP_METHOD', 'Daily Pickup', 'How do you give packages to UPS (only used when origin is US)?', '6', '4', 'tep_cfg_select_option(array(\'Daily Pickup\', \'Customer Counter\', \'One Time Pickup\', \'On Call Air Pickup\', \'Letter Center\', \'Air Service Center\', \'Suggested Retail Rates (UPS Store)\'), ', now())");
		   tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('billed dimensional weight', 'MODULE_SHIPPING_UPSOUTH_TEXT_BILLED_WEIGHT', 'True', 'Do you want to show this text?', '6', '30', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
	}
	 function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      /*return array('MODULE_SHIPPING_UPSOUTH_CLIENT_ID','MODULE_SHIPPING_UPSOUTH_CLIENT_SECRET','MODULE_SHIPPING_UPSOUTH_STATUS','MODULE_SHIPPING_UPSOUTH_SORT_ORDER', 'MODULE_SHIPPING_UPSOUTH_POSTALCODE', 'MODULE_SHIPPING_UPSOUTH_COUNTRY', 'MODULE_SHIPPING_UPSOUTH_STATEPROV', 'MODULE_SHIPPING_UPSOUTH_CITY','MODULE_SHIPPING_UPSOUTH_MODE','MODULE_SHIPPING_UPSOUTH_TYPES','MODULE_SHIPPING_UPSOUTH_WEIGHT1','MODULE_SHIPPING_UPSOAUTH_TIME_IN_TRANSIT_VIEW','MODULE_SHIPPING_UPSOAUTH_RATES_ZONE','MODULE_SHIPPING_UPSOUTH_PACKAGE_TYPE','MODULE_SHIPPING_UPSOUTH_PICKUP_METHOD');*/
	  return array('MODULE_SHIPPING_UPSOUTH_CLIENT_ID','MODULE_SHIPPING_UPSOUTH_CLIENT_SECRET','MODULE_SHIPPING_UPSOUTH_STATUS','MODULE_SHIPPING_UPSOUTH_SORT_ORDER', 'MODULE_SHIPPING_UPSOUTH_POSTALCODE', 'MODULE_SHIPPING_UPSOUTH_COUNTRY', 'MODULE_SHIPPING_UPSOUTH_STATEPROV', 'MODULE_SHIPPING_UPSOUTH_CITY','MODULE_SHIPPING_UPSOUTH_MODE','MODULE_SHIPPING_UPSOUTH_TYPES','MODULE_SHIPPING_UPSOUTH_TEXT_BILLED_WEIGHT');
    }
	// class methods
    function quote($method = '') {
		global $HTTP_POST_VARS, $order, $shipping_weight, $shipping_num_boxes, $total_weight, $boxcount, $cart, $packing;
		
        // UPS purports that if the origin is left out, it defaults to the account's location. Yeah, right.
        $state = $order->delivery['state'];
        $zone_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_name = '" .  tep_db_input($order->delivery['state']) . "' and zone_country_id = '" . $order->delivery['country']['id'] . "'");
        if (tep_db_num_rows($zone_query)) {
            $zone = tep_db_fetch_array($zone_query);
            $state = $zone['zone_code'];
        }
        $this->_upsOrigin(MODULE_SHIPPING_UPSOUTH_CITY, MODULE_SHIPPING_UPSOUTH_STATEPROV, MODULE_SHIPPING_UPSOUTH_COUNTRY, MODULE_SHIPPING_UPSOUTH_POSTALCODE);
        $this->_upsDest($order->delivery['city'], $state, $order->delivery['country']['iso_code_2'], $order->delivery['postcode']);
        
        // the check on $packing being an object will puzzle people who do things wrong (no changes when 
        // you enable dimensional support without changing checkout_shipping.php) but better be safe
        if ($this->dimensions_support > 0 && is_object($packing)) {
          $boxValue = 0;
          $totalWeight = $packing->getTotalWeight();
          $boxesToShip = $packing->getPackedBoxes();
          for ($i = 0; $i < count($boxesToShip); $i++) {
            $this->_addItem($boxesToShip[$i]['item_length'], $boxesToShip[$i]['item_width'], $boxesToShip[$i]['item_height'], $boxesToShip[$i]['item_weight'], $boxesToShip[$i]['item_price']);
          } // end for ($i = 0; $i < count($boxesToShip); $i++)
        } else {
          // The old method. Let osCommerce tell us how many boxes, plus the weight of each (or total? - might be sw/num boxes)
          $this->items_qty = 0; //reset quantities
          // $this->pkgvalue has been set as order subtotal around line 108, it will cause overcharging
          // of insurance if not divided by the number of boxes
          for ($i = 0; $i < $shipping_num_boxes; $i++) {
            $this->_addItem(0, 0, 0, $shipping_weight, number_format(($this->pkgvalue/$shipping_num_boxes), 2, '.', ''));
          }
        }
		 // BOF Time In Transit: used for expected delivery dates
      // is skipped when set to "Not" in the admin
      if ($this->timeInTransitView != 'Not') {
        if ($this->dimensions_support > 0) {
            $this->weight_for_timeintransit = round($totalWeight,1);
        } else {
            $this->weight_for_timeintransit = round($shipping_num_boxes * $shipping_weight,1);
        }
        // Added to workaround time in transit error 270033 if total weight of packages is over 150lbs or 70kgs
        if (($this->weight_for_timeintransit > 150) && ($this->unit_weight == "LBS")) {
          $this->weight_for_timeintransit = 150;          
        } else if (($this->weight_for_timeintransit > 70) && ($this->unit_weight == "KGS")) {
          $this->weight_for_timeintransit = 70;          
        }
        // make sure that when TimeinTransit fails to get results (error or not available)
        // this is not obvious to the client
        $_upsGetTimeServicesResult = $this->_upsGetTimeServices();
        if ($_upsGetTimeServicesResult != false && is_array($_upsGetTimeServicesResult)) {
          $this->servicesTimeintransit = $_upsGetTimeServicesResult;
        }
        if ($this->logfile) {
          error_log("------------------------------------------\n", 3, $this->logfile);
          error_log("Time in Transit: " . $this->timeintransit . "\n", 3, $this->logfile);
        }
      } // end if ($this->timeInTransitView != 'Not') 
      // EOF Time In Transit
  
        $upsQuote = $this->_upsGetQuote();
        if ((is_array($upsQuote)) && (sizeof($upsQuote) > 0)) {
          if (defined('MODULE_SHIPPING_UPSOUTH_WEIGHT1') &&  MODULE_SHIPPING_UPSOUTH_WEIGHT1 == 'False') {
            $this->quotes = array('id' => $this->code, 'name' => $this->name, 'module' => $this->title);
            usort($upsQuote, array($this, "rate_sort_func"));
          } else {
            if ($this->dimensions_support > 0) {
                $this->quotes = array('id' => $this->code, 'name' => $this->name, 'module' => $this->title . ' (' . $this->boxCount . ($this->boxCount > 1 ? ' pkgs, ' : ' pkg, ') . ceil($totalWeight) . ' ' . strtolower($this->unit_weight) . ' total)');
            } else {
                $this->quotes = array('id' => $this->code, 'name' => $this->name, 'module' => $this->title . ' (' . $shipping_num_boxes . ($this->boxCount > 1 ? ' pkgs x ' : ' pkg x ') . ceil($shipping_weight) . ' ' . strtolower($this->unit_weight) . ' total)');
            }
            usort($upsQuote, array($this, "rate_sort_func"));
          } // end else/if if (defined('MODULE_SHIPPING_UPSOUTH_WEIGHT1')
            $methods = array();
            for ($i=0; $i < sizeof($upsQuote); $i++) {
                list($type, $cost) = each($upsQuote[$i]);
                if (strpos($type, ' (')) {
                  $basetype = substr($type, 0, strpos($type, ' ('));
                } else {
                  $basetype = $type;
                }
                // BOF limit choices, behaviour changed from versions < 1.2
                if ($this->exclude_choices($basetype)) continue;
                // EOF limit choices
                if ( $method == '' || $method == $basetype ) {
                    $_type = $type;

                    if ($this->timeInTransitView == "Raw") {
                      if (isset($this->servicesTimeintransit[$basetype])) {
                        $_type = $_type . ", ".$this->servicesTimeintransit[$basetype]["date"];
                      }        
                    } else {
                      if (isset($this->servicesTimeintransit[$basetype])) {
                        $eta_array = explode("-", $this->servicesTimeintransit[$basetype]["date"]);
                        $months = array (" ", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
                        $eta_arrival_date = $months[(int)$eta_array[1]]." ".$eta_array[2].", ".$eta_array[0];
                        $_type .= ", <acronym title='Estimated Delivery Date'>EDD</acronym>: ".$eta_arrival_date;
                      }          
                    }                    
                    // changed to make handling percentage based
                    if ($this->handling_type == "Percentage") {
                        if ($_type) $methods[] = array('id' => $basetype, 'title' => $_type, 'cost' => ((($this->handling_fee * $cost)/100) + $cost));
                    } else {
                        if ($_type) $methods[] = array('id' => $basetype, 'title' => $_type, 'cost' => ($this->handling_fee + $cost));
                    }
                }
            }
            if ($this->tax_class > 0) {
                $this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
            }
            $this->quotes['methods'] = $methods;
        } else {
            if ( $upsQuote != false ) {
                $errmsg = substr(strstr($upsQuote,' '), 1);
            } else {
                $errmsg = MODULE_SHIPPING_UPSOUTH_RATES_TEXT_UNKNOWN_ERROR;
	    }
            $errmsg .= '<br>' . MODULE_SHIPPING_UPSOUTH_RATES_TEXT_IF_YOU_PREFER . ' ' . STORE_NAME.' via <a href="mailto:'.STORE_OWNER_EMAIL_ADDRESS.'"><u>Email</u></a>.';
            $this->quotes = array('module' => $this->title, 'name' => $this->name, 'error' => $errmsg);
        }
        if (tep_not_null($this->icon)) {
            $this->quotes['icon'] = tep_image($this->icon, $this->title);
        }
		//print_r($this->quotes);
        return $this->quotes;
	}
	 //***********************
    function _upsProduct($prod){
        $this->_upsProductCode = $prod;
    }

    //**********************************************
    function _upsOrigin($city, $stateprov, $country, $postal){
        $this->_upsOriginCity = $city;
        $this->_upsOriginStateProv = $stateprov;
        $this->_upsOriginCountryCode = $country;
        $postal = str_replace(' ', '', $postal);
        if ($country == 'US') {
            $this->_upsOriginPostalCode = substr($postal, 0, 5);
        } else {
            $this->_upsOriginPostalCode = $postal;
        }
    }

    //**********************************************
    function _upsDest($city, $stateprov, $country, $postal) {
        $this->_upsDestCity = $city;
        $this->_upsDestStateProv = $stateprov;
        $this->_upsDestCountryCode = $country;
        $postal = str_replace(' ', '', $postal);
        if ($country == 'US') {
            $this->_upsDestPostalCode = substr($postal, 0, 5);
            $territories = array('AS','FM','GU','MH','MP','PR','PW','VI');
            if (in_array($this->_upsDestStateProv,$territories)) {
              $this->_upsDestCountryCode = $stateprov;
              }
        } else if ($country == 'BR') {
            $this->_upsDestPostalCode = substr($postal, 0, 5);
        } else {
            $this->_upsDestPostalCode = $postal;
        }
    }

    //************************
    function _upsAction($action) {
        // rate - Single Quote; shop - All Available Quotes
        $this->_upsActionCode = $action;
    }
	 //********************************************
    function _addItem($length, $width, $height, $weight, $price = 0 ) {
        // Add box or item to shipment list. Round weights to 1 decimal places.
        if ((float)$weight < 1.0) {
            $weight = 1;
        } else {
            $weight = round($weight, 1);
        }
        $index = $this->items_qty;
        $this->item_length[$index] = ($length ? (string)$length : '0' );
        $this->item_width[$index] = ($width ? (string)$width : '0' );
        $this->item_height[$index] = ($height ? (string)$height : '0' );
        $this->item_weight[$index] = ($weight ? (string)$weight : '0' );
        $this->item_price[$index] = $price;
        $this->items_qty++;
    }
	function get_token(){		
	$curl = curl_init(); 
	curl_setopt_array($curl, [
		CURLOPT_HTTPHEADER => [
		"Content-Type: application/x-www-form-urlencoded",
		"x-merchant-id: ".$this->clientid,
		"Authorization: Basic " . base64_encode($this->clientid.":".$this->clientsecret)
	  ],
	  CURLOPT_POSTFIELDS => "grant_type=client_credentials",
	  CURLOPT_URL => "https://onlinetools.ups.com/security/v1/oauth/token",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_CUSTOMREQUEST => "POST",
	]);
	$response = curl_exec($curl);
	$error = curl_error($curl);
	curl_close($curl);
	if ($error) {
	  echo "cURL Error #:" . $error;
	} else {	
	  $res=json_decode($response);
	  $this->access_token=$res->access_token;
	  return $res->access_token;
	}
	}
	 //*********************
    function _upsGetQuote() {
$payload = array(
  "RateRequest" => array(
    "Request" => array(
      "TransactionReference" => array(
        "CustomerContext" => "CustomerContext",
		"TransactionIdentifier" => "TransactionIdentifier"
      )
    ),
    "Shipment" => array(
      "Shipper" => array(
        "Name" => "Wayne Parham",
        "ShipperNumber" => "WF6069",
        "Address" => array(
          "AddressLine" => array(
            "18 Newcastle Lane"
          ),
          "City" => "Bella Vista",
          "StateProvinceCode" => "AR",
          "PostalCode" => "72714",
          "CountryCode" => "US"
        )
      ),
      "ShipTo" => array(
        "Name" => "ShipToName",
        "Address" => array(          
          "City" => $this->_upsDestCity,
          "StateProvinceCode" => $this->_upsDestStateProv,
          "PostalCode" => $this->_upsDestPostalCode,
          "CountryCode" => $this->_upsDestCountryCode
        )
      ),
      "ShipFrom" => array(
        "Name" => "Wayne Parham",
        "Address" => array(
          "AddressLine" => array(
            "18 Newcastle Lane"
          ),
          "City" => $this->_upsOriginCity,
          "StateProvinceCode" =>$this->_upsOriginStateProv,
          "PostalCode" => $this->_upsOriginPostalCode ,
          "CountryCode" => $this->_upsOriginCountryCode
        )
      ),
      "PaymentDetails" => array(
        "ShipmentCharge" => array(
          "Type" => "01",
          "BillShipper" => array(
            "AccountNumber" => "WF6069"
          )
        )
      )
    )
  )
);
		
		// print '<pre>';  
        $numgroups = ceil($this->items_qty / 50); // UPS can only process 50 packages at once
        $xmlResult = '';  
		$payload["RateRequest"]["Shipment"]["NumOfPieces"]=$numgroups;
        for ($g = 0; $g < $numgroups; $g++) { // process each group of packages
        $start = $g * 50;
        $end = ($g + 1 == $numgroups) ? $this->items_qty : $start + 50; // if last group end with number of packages otherwise do 50 more
		$payload["RateRequest"]["Shipment"]["Package"]=array(); 
        for ($i = $start; $i < $end; $i++) {
			//echo 'i'.$i;
			 if ($this->dimensions_support > 0 && ($this->item_length[$i] > 0 ) && ($this->item_width[$i] > 0 ) && ($this->item_height[$i] > 0)) {
				array_push($payload["RateRequest"]["Shipment"]["Package"],array(
					"PackagingType" => array(
						"Code" => $this->package_types[$this->package_type],
						"Description" => "Packaging"
						),
					"Dimensions"=> array(
					  "UnitOfMeasurement" => array(
						"Code" => $this->unit_length,
						"Description" => "Inches"
					  ),
					  "Length" => $this->item_length[$i],
					  "Width" => $this->item_width[$i],
					  "Height" => $this->item_height[$i]
					),
					"PackageWeight" => array(
						  "UnitOfMeasurement" => array(
							"Code" => $this->unit_weight,
							"Description" => "Pounds"
						  ),
						"Weight" => $this->item_weight[$i]
					)
				));									
			 }
        } 
		//print '<pre>'; 
		//print_r($payload); die;
        // post request $strXML;
        $result = $this->_post($this->host,$payload);
		// end groups loop
        return $this->_parseResult($result);
    }
}
    //******************************************************************
    function _post($host,$jsonRequest) {		
		$query = array("additionalinfo" => "");
        $url = "https://".$host."/api/rating/v1/Shop?" . http_build_query($query);
        if ($this->logfile) {
            error_log("------------------------------------------\n", 3, $this->logfile);
            error_log("DATE AND TIME: ".date('Y-m-d H:i:s')."\n", 3, $this->logfile);
            error_log("UPS URL: " . $url . "\n", 3, $this->logfile);
        }
         // default behavior: cURL is assumed to be compiled in PHP
            $ch = curl_init();
            $token=$this->get_token();
			error_log("token: " . $token . "\n", 3, $this->logfile);
			curl_setopt_array($ch, [
			  CURLOPT_HTTPHEADER => ["Authorization: Bearer $token",
				"Content-Type: application/json",
				"transId: ".uniqid(),
				"transactionSrc: testing"
			  ],
			  CURLOPT_POSTFIELDS => json_encode($jsonRequest),
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_CUSTOMREQUEST => "POST",
			]);

            if ($this->logfile) {
                error_log("UPS REQUEST: " . json_encode($jsonRequest) . "\n", 3, $this->logfile);
            }
            $jsonResponse = curl_exec ($ch);
            
            if ($this->logfile) {
                error_log("UPS RESPONSE: " . $jsonResponse . "\n", 3, $this->logfile);
            }
            curl_close ($ch);
         
 
        if ($this->use_exec == '1') {
            return $jsonResponse; // $jsonResponse is an array in this case
        } else {
            return $jsonResponse;
        }
    }

    //*****************************
    function _parseResult($jsonResult) {
        // Parse json message returned by the UPS post server.
        $res = json_decode($jsonResult);
 
        // Get response code: 1 = SUCCESS, 0 = FAIL
        $responseStatusCode = $res->RateResponse->Response->ResponseStatus->Code;
        if ($responseStatusCode != '1') {
            $errorMsg = $res->RateResponse->Response;
            
            // log errors to file ups_error.log when set
            if ($this->ups_error_file) {
                error_log(date('Y-m-d H:i:s')."\tRates\t" . $errorMsg . "\t" . $_SESSION['customer_id']."\n", 3, $this->ups_error_file);    
            }
                return $errorMsg;
        }

        $ratedShipments = $res->RateResponse->RatedShipment;

        $aryProducts = false;
        $upstemp = array(); 
        if (isset($res->RateResponse->RatedShipment[0])) { 
		//echo 'RatedShipment more than 1';// more than 1 rate
          for ($i = 0; $i < count($ratedShipments); $i++) {
            $serviceCode = $ratedShipments[$i]->Service->Code;
            if ($this->use_negotiated_rates == 'True' && isset($ratedShipments[$i]->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue)) {
                $totalCharge = $ratedShipments[$i]->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
               } elseif ($this->manual_negotiated_rate > 0) {
                 $totalCharge = $ratedShipments[$i]->TotalCharges->MonetaryValue * ($this->manual_negotiated_rate/100);
               } else {
                 // standard UPS rates
                 $totalCharge = $ratedShipments[$i]->TotalCharges->MonetaryValue;
               }
              if (!($serviceCode && $totalCharge)) {
                continue;
              } 
            $ratedPackages = $ratedShipments[$i]->RatedPackage; // only do this once for the first service given
            if (isset($ratedShipments[$i]->RatedPackage)) { // multidimensional array of packages
              $boxCount = count($ratedPackages);
            } else {
              $boxCount = 1; // if there is only one package count($ratedPackages) returns
              // the number of fields in the array like TransportationCharges and BillingWeight
            }
            // if more than one group of packages, service codes will be repeated and therefore data needs to be combined
            $upstemp[$serviceCode]['charge'] += $totalCharge;
            $upstemp[$serviceCode]['boxes'] += $boxCount;
            $upstemp[$serviceCode]['billed_weight'] += $ratedShipments[$i]->BillingWeight->Weight;
            $upstemp[$serviceCode]['weight_code'] = $ratedShipments[$i]->BillingWeight->UnitOfMeasurement->Code;
          } // end for ($i = 0; $i < count($ratedShipments); $i++)
          $i = 0;
	  //print_r($this->service_codes);
          foreach ($upstemp as $key => $value) {
            $this->boxCount = $value['boxes']; // set total grouped package count
            $title = $this->service_codes['US Origin'][$key]; 
            if (MODULE_SHIPPING_UPSOUTH_WEIGHT1 == 'True' && MODULE_SHIPPING_UPSOUTH_TEXT_BILLED_WEIGHT == 'True')
			{$title .= ' (' . MODULE_SHIPPING_UPSOUTH_TEXT_BILLED_WEIGHT . $value['billed_weight'] . ' ' . $value['weight_code'] . ')';}
            $aryProducts[$i] = array($title => $value['charge']);
            $i++;
          } 
        }
        return $aryProducts;
    }

    // BOF Time In Transit

    //********************
    function _upsGetTimeServices() {
 
    }

    //***************************************
    
    // GM 11-15-2004: modified to return array with time for each service, as
    //                opposed to single transit time for hardcoded "GND" code

    function _transitparseResult($xmlTransitResult) {
         $transitTime = array();

        
        return $transitTime;
    }

    //  ***************************
  function exclude_choices($type) {
    // Used for exclusion of UPS shipping options, disallowed types are read from db (stored as 
    // short defines). The short defines are not used as such, to avoid collisions
    // with other shipping modules, they are prefixed with UPSOUTH_
    // These defines are found in the upsxml language file (UPSOUTH_US_01, UPSOUTH_CAN_14 etc.)
    $disallowed_types = explode(",", MODULE_SHIPPING_UPSOUTH_TYPES);
    if (strstr($type, "UPS")) {
        // this will chop off "UPS" from the beginning of the line - typically something like UPS Next Day Air (1 Business Days)
        $type_minus_ups = explode("UPS", $type );
        $type_root = trim($type_minus_ups[1]);
    } // end if (strstr($type, "UPS"):
    else { // service description does not contain UPS (unlikely)
        $type_root = trim($type);
    }
    for ($za = 0; $za < count ($disallowed_types); $za++ ) {
      // when no disallowed types are present, --none-- is in the db but causes an error because --none-- is
      // not added as a define
      if ($disallowed_types[$za] == '--none--' ) continue; 
        if ($type_root == constant('UPSOUTH_' . trim($disallowed_types[$za]))) {
            return true;
        } // end if ($type_root == constant(trim($disallowed_types[$za]))).
    }
    // if the type is not disallowed:
    return false;
  }
  // Next function used for sorting the shipping quotes on rate: low to high is default.
  function rate_sort_func ($a, $b) {
    
   $av = array_values($a);
   $av = $av[0];
   $bv = array_values($b);
   $bv = $bv[0];

  // return ($av == $bv) ? 0 : (($av < $bv) ? 1 : -1); // for having the high rates first
  return ($av == $bv) ? 0 : (($av > $bv) ? 1 : -1); // low rates first
  
  }
}
	 

 function get_multioption_upsxml($values) {
         if (tep_not_null($values)) {
             $values_array = explode(',', $values);
             foreach ($values_array as $key => $_method) {
               if ($_method == '--none--') {
                 $method = $_method;
               } else {
                 $method = constant('UPSOUTH_' . trim($_method));
               }
               $readable_values_array[] = $method;
             }
             $readable_values = implode(', ', $readable_values_array);
             return $readable_values;
         } else {
           return '';
         }
  }
  
  function upsxml_cfg_select_multioption_indexed($select_array, $key_value, $key = '') {
    for ($i=0; $i<sizeof($select_array); $i++) {
      $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
      $string .= '<br><input type="checkbox" name="' . $name . '" value="' . $select_array[$i] . '"';
      $key_values = explode( ", ", $key_value);
      if ( in_array($select_array[$i], $key_values) ) $string .= ' CHECKED';
      $string .= '> ' . constant('UPSOUTH_' . trim($select_array[$i]));
    } 
    $string .= '<input type="hidden" name="' . $name . '" value="--none--">';
    return $string;
  }