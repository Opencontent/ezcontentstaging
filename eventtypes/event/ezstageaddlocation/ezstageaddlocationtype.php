<?php

class eZStageAddLocationType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = 'ezstageaddlocation';

    function __construct()
    {
        $this->eZWorkflowEventType( self::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'extension/ezcontentstaging/eventtypes', 'Stage Add Location' ) );
        $this->setTriggerTypes( array( 'content' => array( 'addlocation' => array( 'after' ) ) ) );
    }

    function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        //$nodeID = $parameters['node_id'];
        $objectID = $parameters['object_id'];
        $selectNodeIDArray = $parameters['select_node_id_array'];

        // sanity checks

        if( count( $selectNodeIDArray ) == 0 )
        {
            return eZWorkflowType::STATUS_ACCEPTED;
        }

        $object = eZContentObject::fetch( $objectID );
        if ( !is_object( $object ) )
        {
            eZDebug::writeError( 'Unable to fetch object ' . $objectID, __METHOD__ );
            return eZWorkflowType::STATUS_ACCEPTED;
        }

        // fetch list of new parent nodes to make sure they exist AND to get their remote IDs
        $newParentNodes = eZContentObjectTreeNode::fetch( $selectNodeIDArray );
        if ( $newParentNodes instanceof eZContentObjectTreeNode )
        {
            $newParentNodes = array( $newParentNodes );
        }
        elseif( count( $newParentNodes ) == 0 )
        {
            eZDebug::writeError( 'Unable to fetch new parent nodes for object ' . $objectID, __METHOD__ );
            return eZWorkflowType::STATUS_ACCEPTED;
        }

        // build data for all newly created nodes
        $newNodesData = array();
        // be fast with single query instead of going the object way
        /// @todo use eZPO fetchObject function instead of direct eZDB for better abstraction
        ///       maybe these are already in node cache anyway?
        $db = eZDB::instance();
        foreach( $newParentNodes as $newParentNode )
    	{
            $sql = "SELECT node_id, remote_id, priority, sort_field, sort_order, path_string FROM ezcontentobject_tree where contentobject_id = '$objectID' AND parent_node_id  = " . $newParentNode->attribute( 'node_id' );
            $data = $db->arrayQuery( $sql );
            /// @todo test for errors
            $data = $data[0];
            $newNodesData[$data['path_string']] = array(
                'objectRemoteId' => $object->attribute( 'remote_id' ),
                'parentRemoteId' => $newParentNode->attribute( 'remote_id' ),
                'remoteId' => $data['remote_id'],
                'priority' => $data['priority'],
                'sortField' => $data['sort_field'],
                'sortOrder' => $data['sort_order']
            );
    	}

        // finally add, for every target feed, all new nodes that fall within it
        foreach ( eZContentStagingTarget::fetchList() as $target_id => $target )
        {
            foreach( $newNodesData as $newNodePathString => $newNodeData )
            {
                if ( $target->includesNodeByPath( $newNodePathString ) )
                {
                    eZContentStagingItem::addEvent(
                        $target_id,
                        $object->attribute( 'id' ),
                        eZContentStagingItemEvent::ACTION_ADDLOCATION,
                        $newNodeData
                    );
                }
            }
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType( eZStageAddLocationType::WORKFLOW_TYPE_STRING, 'eZStageAddLocationType' );

?>