<?php
/**
 *
 * @deprecated
 *
 * View used to sync one node
 *
 * @package ezcontentstaging
 *
 * @copyright Copyright (C) 2011-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

$http = eZHTTPTool::instance();
$targetId = $http->postVariable( 'TargetId' );
$syncErrors = array();
$syncResults = array();
$ini = eZIni::instance();
$module = $Params['Module'];

$relatedNodeEventsList = $currentNodeEvents = $toSync = array();

if ( $http->hasPostVariable( "CancelButton" ) && $http->hasPostVariable( 'NodeID' ) )
{
    $currentNode = eZContentObjectTreeNode::fetch( $http->postVariable( 'NodeID' ) );
    $module->redirectTo( $currentNode->attribute( 'url_alias' ) );
}

if ( $http->hasPostVariable( 'NodeID' ) )
{
    $currentNode = eZContentObjectTreeNode::fetch( $http->postVariable( 'NodeID' ), $ini->variable( 'RegionalSettings','ContentObjectLocale'), true );
    $currentObject = $currentNode->attribute( 'object' );

    $eventList = eZContentStagingEvent::fetchByNode( $currentNode->attribute( 'node_id' ), $currentNode->attribute( 'contentobject_id' ), $targetId );
    foreach ( $eventList as $event )
    {
        if ( $event instanceof eZContentStagingEvent )
        {
            $currentNodeEvents[$event->attribute( 'id' )] = $event;
        }
    }

    //collecte related objects
    $relatedObjectList = $currentObject->relatedContentObjectList();

    //Check if we need to sync related object
    $relatedObjectNeedingSync = $eventList = array();
    foreach ( $relatedObjectList as $relatedObject )
    {
        //$eventList = eZContentStagingEvent::fetchByObject( $relatedObject->ID );
        $eventList = eZContentStagingEvent::fetchByNode( $relatedObject->attribute( 'main_node_id' ), $relatedObject->attribute( 'contentobject_id' ), $targetId );
        if ( !empty( $eventList ) )
        {
            array_push( $relatedObjectNeedingSync, $relatedObject );
            foreach ( $eventList as $event )
            {
                if ( $event instanceof eZContentStagingEvent )
                {
                    $relatedNodeEventsList[$relatedObject->attribute( 'id' )][$event->attribute( 'id' )] = $event;
                }
            }
        }

        /*
        $relatedObjectNodes = $relatedObject->assignedNodes();
        foreach ( $relatedObjectNodes as $relatedObjectNode )
        {
            echo $relatedObjectNode->attribute( 'node_id' );
        }
        */

    }
}

if ( !empty( $currentNodeEvents ) && !$http->hasPostVariable( 'ConfirmSyncNodeButton' ) )
{
    if ( !empty( $relatedObjectNeedingSync ) )
    {
        $syncErrors[] = ezpI18n::tr( 'ezcontentstaging', 'The current node has some related contents that must be synchronized too. Please, confirm your action to run the synchronisation.' );
    }
    else
    {
        $syncErrors = null;
    }
    $syncResults = null;
}
else if ( !empty( $currentNodeEvents ) && $http->hasPostVariable( 'ConfirmSyncNodeButton' ) )
{
    $toSync = $currentNodeEvents;
    foreach ( $relatedNodeEventsList as $relatedNodeEvents )
    {

         $toSync += $relatedNodeEvents;
    }
    ksort( $toSync );

    $out = eZContentStagingEvent::syncEvents( $toSync );
    /// @todo apply i18n to messages
    /// @todo check that current user can sync - with limitations - this event
    foreach ( $out as $id => $resultCode )
    {
        $event = $toSync[$id];
        if ( $resultCode !== 0 )
        {
            $syncErrors[] = " Object " . $event->attribute( 'object_id' ) . " to be synchronised to feed " . $event->attribute( 'target_id' ) . ": failure ($resultCode) [Event $id]";
        }
        else
        {
            $syncResults[] = "Object " . $event->attribute( 'object_id' ) . " succesfully synchronised to feed " . $event->attribute( 'target_id' ) . " [Event $id]";
        }
    }
}
else
{
    $syncErrors[] = "No object(s) to be synchronized";
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'current_node', $currentNode );
$tpl->setVariable( 'sync_related_objects', $relatedObjectNeedingSync );
$tpl->setVariable( 'target_id', $targetId );
$tpl->setVariable( 'current_node_events', $currentNodeEvents );
$tpl->setVariable( 'related_node_events', $relatedNodeEventsList );
$tpl->setVariable( 'sync_errors', $syncErrors );
$tpl->setVariable( 'sync_results', $syncResults );


$Result['content'] = $tpl->fetch( 'design:contentstaging/syncevents.tpl' );

$Result['path'] = array( array( 'text' => ezpI18n::tr( 'ezcontentstaging', 'Content synchronization' ),
                                'url' => 'contentstaging/syncnode' ) );
