<?php
/**
 * @package ezcontentstaging
 *
 * @copyright Copyright (C) 2011-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

$FunctionList = array(

    // returns the list of servers to which an object needs to sync, as an array( srv_id => array of event ids )
    'node_sync_events_by_target' => array(
        'name' => 'node_sync_targets',
        'call_method' => array(
           'class'  => 'eZContentStagingFunctionCollection',
           'method' => 'fetchSyncEventsByNodeGroupedByTarget' ),
           'parameters' => array( array( 'name'     => 'node_id',
                                         'type'     => 'integer',
                                         'required' => true ),
                                  array( 'name'     => 'object_id',
                                         'type'     => 'integer',
                                         'required' => false ),
                                  array( 'name'     => 'language',
                                         'type'     => 'string',
                                         'required' => false,
                                         'default'  => null ) ) ),

    'sync_target' => array(
        'name' => 'sync_target',
        'call_method' => array(
               'class'  => 'eZContentStagingFunctionCollection',
               'method' => 'fetchSyncTarget' ),
        'parameters' => array( array( 'name'     => 'target_id',
                                      'type'     => 'string',
                                      'required' => true ) ) ),

    'sync_events'  => array(
        'name' => 'sync_events',
        'call_method' => array(
            'class'  => 'eZContentStagingFunctionCollection',
            'method' => 'fetchSyncEvents' ),
        'parameters' => array( array( 'name'     => 'target_id',
                                      'type'     => 'string',
                                      'required' => false,
                                      'default'  => '' ),
                               array( 'name'     => 'offset',
                                      'type'     => 'integer',
                                      'required' => false,
                                      'default'  => 0 ),
                               array( 'name'     => 'limit',
                                      'type'     => 'integer',
                                      'required' => false,
                                      'default'  => 0 ),
                               array( 'name'     => 'language',
                                      'type'     => 'string',
                                      'required' => false,
                                      'default'  => null ),
                               array( 'name'     => 'status',
                                      'type'     => 'integer',
                                      'required' => false,
                                      'default'  => null ) ) ),

    'sync_events_count'  => array(
        'name' => 'sync_events_count',
        'call_method' => array(
            'class'  => 'eZContentStagingFunctionCollection',
            'method' => 'fetchSyncEventsCount' ),
        'parameters' => array( array( 'name'     => 'target_id',
                                      'type'     => 'string',
                                      'required' => false,
                                      'default'  => '' ),
                               array( 'name'     => 'language',
                                      'type'     => 'string',
                                      'required' => false,
                                      'default'  => null ),
                               array( 'name'     => 'status',
                                      'type'     => 'integer',
                                      'required' => false,
                                      'default'  => null ) ) ),

    // returns the list of events, as an array( object_id => array of events )
    'sync_events_by_object'  => array(
        'name' => 'sync_events',
        'call_method' => array(
            'class'  => 'eZContentStagingFunctionCollection',
            'method' => 'fetchSyncEventsByObject' ),
        'parameters' => array( array( 'name'     => 'target_id',
                                      'type'     => 'string',
                                      'required' => false,
                                      'default'  => '' ),
                               array( 'name'     => 'offset',
                                      'type'     => 'integer',
                                      'required' => false,
                                      'default'  => 0 ),
                               array( 'name'     => 'limit',
                                      'type'     => 'integer',
                                      'required' => false,
                                      'default'  => 0 ),
                               array( 'name'     => 'language',
                                      'type'     => 'string',
                                      'required' => false,
                                      'default'  => null ) ) ),
    // nb: currently unused

    // returns the list of servers to which an object needs to sync, as an array( srv_id => target obj )
    /*'object_sync_targets' => array(
        'name' => 'object_sync_targets',
        'call_method' => array(
            'class'  => 'eZContentStagingFunctionCollection',
            'method' => 'fetchObjectSyncTargets'  ),
        'parameters' => array( array( 'name'     => 'object_id',
                                      'type'     => 'string',
                                      'required' => true ) ) ),*/

    'sync_feeds_by_node' => array(
        'name' => 'sync_feeds_by_node',
        'call_method' => array(
               'class'  => 'eZContentStagingFunctionCollection',
               'method' => 'fetchFeedsByNodeId'  ),
        'parameters' => array( array( 'name'     => 'node_id',
                                      'type'     => 'integer',
                                      'required' => false,
                                      'default'  => null ) ) ),
);
