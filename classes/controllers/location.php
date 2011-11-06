<?php
/**
 * @package ezcontentstaging
 *
 * @version $Id$;
 *
 * @author
 * @copyright
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 * @todo finish moving all content-mofication-actions to the model
 * @todo decide how much typecast we do on parameters passed to calls of model's methods
 */

class contentStagingRestLocationController extends contentStagingRestBaseController
{

    // *** rest actions ***

    /**
     * Handle GET on a location from its [remote] id
     *
     * Requests:
     * - GET /content/locations/remote/<remoteId>
     * - GET /content/locations/<Id>
     *
     * @return void
     */
    public function doLoad()
    {

        $node = $this->node();
        if ( !$node instanceof eZContentObjectTreeNode )
        {
            return $node;
        }

        $result = new ezpRestMvcResult();
        $result->variables['Location'] = (array) new contentStagingLocation( $node );
        return $result;

    }

    /**
     * Handle hide or unhide request for a location from its [remote] id
     *
     * Requests:
     * - POST /content/locations/remote/<remoteId>?hide=<status>
     * - POST /content/locations/<Id>?hide=<status>
     * @return ezpRestMvcResult
     */
    public function doHideUnhide()
    {
        $node = $this->node();
        if ( !$node instanceof eZContentObjectTreeNode )
        {
            return $node;
        }

        $result = new ezpRestMvcResult();
        if ( !isset( $this->request->get['hide'] ) )
        {
            $result->status = new ezpRestHttpResponse(
                ezpHttpResponseCodes::BAD_REQUEST,
                'The "hide" parameter is missing'
            );
            return $result;
        }
        /// @todo
        $hide = (bool) $this->request->get['hide'];
        if ( $hide )
        {
            eZContentObjectTreeNode::hideSubTree( $node );
        }
        else
        {
            eZContentObjectTreeNode::unhideSubTree( $node );
        }
        $result->status = new ezpRestHttpResponse( 204 );
        return $result;
    }

    /**
     * Update the sort order and sort field or the priority of a node
     *
     * Request:
     * - PUT /content/locations/remote/<remoteId>
     * - PUT /content/locations/<Id>
     * @return ezpRestMvcResult
     */
    public function doUpdate()
    {
        $node = $this->node();
        if ( !$node instanceof eZContentObjectTreeNode )
        {
            return $node;
        }

        $result = new ezpRestMvcResult();

        if ( isset( $this->request->inputVariables['sortField'] )
                && isset( $this->request->inputVariables['sortOrder'] ) )
        {
            contentStagingLocation::updateSort(
                $node,
                $this->request->inputVariables['sortField'],
                $this->request->inputVariables['sortOrder']
            );
        }

        if ( isset( $this->request->inputVariables['priority'] ) )
        {
            contentStagingLocation::updatePriority(
                $node,
                (int)$this->request->inputVariables['priority']
            );
        }

        if ( isset( $this->request->inputVariables['remoteId'] ) )
        {
            contentStagingLocation::updateRemoteId(
                $node,
                $this->request->inputVariables['remoteId']
            );
        }

        $result->variables['Location'] = (array) new contentStagingLocation( $node );
        return $result;
    }

    /**
     * Handle move operation of a location to another location
     *
     * Request:
     * - PUT /content/locations/remote/<remoteId>/parent?destParentRemoteId=<dest>
     * - PUT /content/locations/<Id>/parent?destParentRemoteId=<dest>
     *
     * @return ezpRestMvcResult
     */
    public function doMove()
    {
        $node = $this->node();
        if ( !$node instanceof eZContentObjectTreeNode )
        {
            return $node;
        }

        $result = new ezpRestMvcResult();
        if ( !isset( $this->request->get['destParentRemoteId'] ) )
        {
            $result->status = new ezpRestHttpResponse(
                ezpHttpResponseCodes::BAD_REQUEST,
                'The "destParentRemoteId" parameter is missing'
            );
            return $result;
        }
        $destParentRemoteId = $this->request->get['destParentRemoteId'];
        $dest = eZContentObjectTreeNode::fetchByRemoteID( $destParentRemoteId );
        if ( !$dest instanceof eZContentObjectTreeNode )
        {
            $result->status = new ezpRestHttpResponse(
                ezpHttpResponseCodes::NOT_FOUND,
                "Cannot find the location with remote id '{$destParentRemoteId}'"
            );
            return $result;
        }
        eZContentOperationCollection::moveNode(
            $node->attribute( 'node_id' ),
            $node->attribute( 'contentobject_id' ),
            $dest->attribute( 'node_id' )
        );

        $newNode = eZContentObjectTreeNode::fetch( $node->attribute( 'node_id' ) );
        $result->variables['Location'] = (array) new contentStagingLocation( $newNode );
        return $result;

    }

    /**
     * Handle DELETE request for a location
     *
     * Request:
     * - DELETE /content/locations/remote/<remoteId>?trash=true|false
     * - DELETE /content/locations/<Id>?trash=true|false
     *
     * @return ezpRestMvcResult
     */
    public function doRemove()
    {
        $node = $this->node();
        if ( !$node instanceof eZContentObjectTreeNode )
        {
            return $node;
        }

        $result = new ezpRestMvcResult();
        $moveToTrash = true;
        if ( isset( $this->request->get['trash'] ) )
        {
            /// @tood move to common code: trim, strtolower
            $moveToTrash = ( $this->request->get['trash'] === 'true' );
        }
        eZContentObjectTreeNode::removeSubtrees(
            array( $node->attribute( 'node_id' ) ),
            $moveToTrash
        );

        $result->status = new ezpRestHttpResponse( 204 );
        return $result;
    }


    // *** helper methods ***

    /// @todo assert error if neither Id nor remoteId are present
    protected function node()
    {
        if ( isset( $this->remoteId ) )
        {
            $node = eZContentObjectTreeNode::fetchByRemoteID( $this->remoteId );
            if ( !$node instanceof eZContentObjectTreeNode )
            {
                $result->status = new ezpRestHttpResponse(
                    ezpHttpResponseCodes::NOT_FOUND,
                    "Location with remote id '{$this->remoteId}' not found"
                );
                return $result;
            }
            return $node;
        }
        if ( isset( $this->Id ) )
        {
            $node = eZContentObjectTreeNode::fetch( $this->Id );
            if ( !$node instanceof eZContentObjectTreeNode )
            {
                $result->status = new ezpRestHttpResponse(
                    ezpHttpResponseCodes::NOT_FOUND,
                    "Location with id '{$this->Id}' not found"
                );
                return $result;
            }
        }
        return $node;
    }

}
