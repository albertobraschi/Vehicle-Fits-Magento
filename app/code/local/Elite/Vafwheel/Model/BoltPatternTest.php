<?php
class Elite_Vafwheel_Model_BoltPatternTest extends Elite_Vaf_TestCase
{
    
    function testSingleToString()
    {
        $bolt = Elite_Vafwheel_Model_BoltPattern::create('4x114.3');
        $this->assertEquals( '4x114.3', $bolt->__toString() );
    }
    
    function testSingleLugCount()
    {
        $bolt = Elite_Vafwheel_Model_BoltPattern::create('4x114.3');
        $this->assertEquals( 4, $bolt->getLugCount() );
    }
    
    function testSingleBoltDistance()
    {
        $bolt = Elite_Vafwheel_Model_BoltPattern::create('4x114.3');
        $this->assertEquals( 114.3, $bolt->getDistance() );
    }
    
    function testOffset()
    {
        $bolt = Elite_Vafwheel_Model_BoltPattern::create('4x114.3', 38.5);
        $this->assertEquals( 38.5, $bolt->getOffset() );
    }
    
    function testOffsetThresholdMinimum()
    {
        $bolt = Elite_Vafwheel_Model_BoltPattern::create('4x114.3', 20);
        $this->assertEquals( 15, $bolt->offsetMin() );
    }
    
    function testOffsetThresholdMaximum()
    {
        $bolt = Elite_Vafwheel_Model_BoltPattern::create('4x114.3', 20);
        $this->assertEquals( 25, $bolt->offsetMax() );
    }
}