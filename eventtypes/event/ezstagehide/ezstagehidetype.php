<?php
/**
 * @package ezcontentstaging
 *
 * @copyright Copyright (C) 2011-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 * @todo check of ot can be moved to after action instead of before
 */

class eZStageHideType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = 'ezstagehide';

    public function __construct()
    {
        $this->eZWorkflowEventType( self::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'ezcontentstaging/eventtypes', 'Stage hide/unhide' ) );
        $this->setTriggerTypes( array( 'content' => array( 'hide' => array( 'before' ) ) ) );
    }

    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $nodeID = $parameters['node_id'];

        // sanity checks

        $node = eZContentObjectTreeNode::fetch( $nodeID );
        if ( !is_object( $node ) )
        {
            eZDebug::writeError( 'Unable to fetch node ' . $nodeID, __METHOD__ );
            return eZWorkflowType::STATUS_ACCEPTED;
        }

        $objectId = $node->attribute( 'contentobject_id' );
        $hiddenNodeData = array( 'nodeID' => $nodeID, 'nodeRemoteID' => $node->attribute( 'remote_id' ), 'hide' => !(bool)$node->attribute( 'is_hidden' ) );
        $affectedNodes = array( $nodeID );
        foreach ( eZContentStagingTarget::fetchByNode( $node ) as $targetId => $target )
        {
            eZContentStagingEvent::addEvent(
                $targetId,
                $objectId,
                eZContentStagingEvent::ACTION_HIDEUNHIDE,
                $hiddenNodeData,
                $affectedNodes
            );
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType( eZStageHideType::WORKFLOW_TYPE_STRING, 'eZStageHideType' );
