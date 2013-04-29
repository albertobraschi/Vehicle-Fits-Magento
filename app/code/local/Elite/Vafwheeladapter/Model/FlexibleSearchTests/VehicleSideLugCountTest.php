<?php
/**
 * Vehicle Fits
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Vehicle Fits to newer
 * versions in the future. If you wish to customize Vehicle Fits for your
 * needs please refer to http://www.vehiclefits.com for more information.

 * @copyright  Copyright (c) 2013 Vehicle Fits, llc
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Elite_Vafwheeladapter_Model_FlexibleSearchTests_VehicleSideLugCountTest extends VF_TestCase
{
	function testShouldGetFromRequest()
    {
        $flexibleSearch = $this->flexibleWheeladapterSearch(array('vehicle_lug_count'=>'5'));
        $this->assertEquals( 5, $flexibleSearch->vehicleSideLugCount(), 'should get vehicle side lug count from request' );
    }
    
    function testShouldStoreInSession()
    {
        $flexibleSearch = $this->flexibleWheeladapterSearch(array('vehicle_lug_count'=>'5'));
        Elite_Vaf_Helper_Data::getInstance()->storeFitInSession();
        $this->assertEquals( 5, $this->flexibleWheeladapterSearch()->vehicleSideLugCount(), 'should store vehicle side lug count in session' );
    }
    
    function testShouldClearFromSession()
    {
        $flexibleSearch = $this->flexibleWheeladapterSearch(array('vehicle_lug_count'=>'5'));
        Elite_Vaf_Helper_Data::getInstance()->storeFitInSession();
        
        $flexibleSearch = $this->flexibleWheeladapterSearch(array('vehicle_lug_count'=>'0'));
        Elite_Vaf_Helper_Data::getInstance()->storeFitInSession();
        
        $this->assertNull( $this->flexibleWheeladapterSearch()->vehicleSideLugCount(), 'should clear vehicle side lug count from session' );
    }
}
