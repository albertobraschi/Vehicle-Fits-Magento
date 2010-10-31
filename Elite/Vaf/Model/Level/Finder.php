<?php
/**
* Vehicle Fits Free Edition - Copyright (c) 2008-2010 by Ne8, LLC
* PROFESSIONAL IDENTIFICATION:
* "www.vehiclefits.com"
* PROMOTIONAL SLOGAN FOR AUTHOR'S PROFESSIONAL PRACTICE:
* "Automotive Ecommerce Provided By Ne8 llc"
*
* All Rights Reserved
* VEHICLE FITS ATTRIBUTION ASSURANCE LICENSE (adapted from the original OSI license)
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the conditions in license.txt are met
*/
class Elite_Vaf_Model_Level_Finder extends Elite_Vaf_Model_Level_Finder_Abstract
{
    static function getInstance()
    {
        static $finder;
        if( is_null( $finder ) )
        {
            $finder = new Elite_Vaf_Model_Level_Finder();
        }
        return $finder;
    }
    
    function find( $level, $id )
    {
        return $this->selector()->find($level, $id);
    }
    
    /** @return Elite_Vaf_Model_Level */
    function findEntityByTitle( $type, $title, $parent_id = 0 )
    { 
        return $this->selector()->findEntityByTitle( $type, $title, $parent_id );
    }
    
    /** @return integer ID */
    function findEntityIdByTitle( $type, $title, $parent_id = 0 )
    {
        return $this->selector()->findEntityIdByTitle( $type, $title, $parent_id );
    }
    
    /**
    *  @param mixed Elite_Vaf_Model_Level|string name of level type
    * @param mixed $parent_id
    */
    function listAll( $level, $parent_id = 0 )
    {
        if(is_string($level))
        {
            $level = new Elite_Vaf_Model_Level($level);
        }
        return $this->selector()->listAll( $level, $parent_id );
    }
    
    /**
    * @param aray $slaveLevels - Ex. array('year'=>$year1,'year'=>$year2);
    * @param array $masterLevel - Ex. array('year'=>$year2);
    */
    function merge( $slaveLevels, $masterLevel )
    {
        $master_level_type = current($masterLevel);
        $master_vehicle = next($masterLevel);
        
        $level_type = $master_level_type;
        
        $slaveVehicles = $this->slaveVehicles($slaveLevels);
        foreach($slaveVehicles as $slaveVehicle)
        {
            if($slaveVehicle->levelIdsTruncateAfter($level_type) == $master_vehicle->levelIdsTruncateAfter($level_type))
            {
                continue;
            }
            
            $this->merge_vehicle($slaveVehicle, $master_vehicle, $level_type);
            $this->unlinkSlaves( $slaveVehicle, $master_vehicle, $level_type );
        }
    }
    
    function slaveVehicles($slaveLevels)
    {
        $slaveVehicles = array();
        $this->ensureSlavesSameGrain($slaveLevels);       
        
        foreach($slaveLevels as $levelsToBeMergedArray)
        {
            $level_type = current($levelsToBeMergedArray);
            $vehicle_object = next($levelsToBeMergedArray);
            
            $levelIds = $vehicle_object->levelIdsTruncateAfter($level_type);
            $slaveVehicles = array_merge($slaveVehicles, $this->vehicleFinder()->findByLevelIds($levelIds));
        }
        
        foreach($slaveVehicles as $slaveVehicle)
        {
            $slaveVehicle->toValueArray();
        }
        return $slaveVehicles;
    }
    
    function ensureSlavesSameGrain($slaveLevels)
    {
        $last_level_type = '';
        $i=0;
        foreach($slaveLevels as $levelsToBeMergedArray)
        {
            $level_type = current($levelsToBeMergedArray);
            if($last_level_type != $level_type && $i)
            {
                throw new Elite_Vaf_Model_Vehicle_Finder_Exception_DifferingGrain('slave levels should all be at same grain to merge');
            }
            $last_level_type = $level_type;
            $i++;
        }
    }
    
    function unlinkSlaves($slaveVehicle, $master_vehicle, $level_type )
    {
        if( $slaveVehicle->levelIdsTruncateAfter($level_type) != $master_vehicle->levelIdsTruncateAfter($level_type))
        {
            $params = $slaveVehicle->levelIdsTruncateAfter($level_type);
            $unlinkTarget = $this->vehicleFinder()->findOneByLevelIds($params, Elite_Vaf_Model_Vehicle_Finder::EXACT_ONLY);
            if($unlinkTarget)
            {
                $unlinkTarget->unlink();
            }
        }
    }
    
    function merge_vehicle($slave_vehicle, $master_vehicle, $level)
    {
        $titles = $slave_vehicle->toTitleArray();
        foreach( $this->getSchema()->getPrevLevelsIncluding($level) as $levelToReplace )
        {
            $titles[$levelToReplace] = $master_vehicle->getLevel($levelToReplace)->getTitle();
        }
        $new_vehicle = Elite_Vaf_Model_Vehicle::create($this->getSchema(), $titles);
        $new_vehicle->save();
        
        $this->mergeFitments($slave_vehicle, $new_vehicle, $level);
    }
    
    function vehicleFinder()
    {
        return new Elite_Vaf_Model_Vehicle_Finder($this->getSchema());
    }
    
    function mergeFitments($vehicle_object, $master_vehicle, $level_type)
    {
        foreach($this->getProductsThatFit($vehicle_object) as $product)
        {
            $params = $master_vehicle->levelIdsTruncateAfter($level_type);
            $descendantsOfMaster = $this->vehicleFinder()->findOneByLevelIds($params);
            $product->addVafFit($master_vehicle->toValueArray());
        }
    }
    
    function getProductsThatFit($vehicle_object)
    {
        $select = new Elite_Vaf_Select($this->getReadAdapter());
        $select
            ->from('elite_mapping')
            ->whereLevelIdsEqual($vehicle_object->toValueArray());
        
        $result = $select->query()->fetchAll();
        $products = array();
        foreach($result as $row)
        {
            $product = new Elite_Vaf_Model_Catalog_Product();
            $product->setId($row['entity_id']);
            array_push($products, $product);
        }
        return $products;
    }
    
    function __call($name, $arguments)
    {
        return call_user_func_array(array($this->selector(),$name), $arguments);
    }
    
    function selector()
    {
        return new Elite_Vaf_Model_Level_Finder_Selector;
    }
    
}