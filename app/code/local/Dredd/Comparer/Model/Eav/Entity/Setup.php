<?php
/**
 * @author ando MADADEV
 */

class Dredd_Comparer_Model_Eav_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
	/**
     * Add or update attribute to group
     *
     * @param int|string $entityType
     * @param int|string $setId
     * @param int|string $groupId
     * @param int|string $attributeId
     * @param int $sortOrder
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function addAttributeToGroup($entityType, $setId, $groupId, $attributeId, $sortOrder = null)
    {
        $entityType  = $this->getEntityTypeId($entityType);
        $setId       = $this->getAttributeSetId($entityType, $setId);
        $groupId     = $this->getAttributeGroupId($entityType, $setId, $groupId);
        $attributeId = $this->getAttributeId($entityType, $attributeId);

        $bind   = array(
            'entity_type_id'        => $entityType,
            'attribute_set_id'      => $setId,
            'attribute_group_id'    => $groupId,
            'attribute_id'          => $attributeId,
        );

        $select = $this->getConnection()->select()
            ->from($this->getTable('eav/entity_attribute'))
            ->where('entity_type_id=?', $entityType)
            ->where('attribute_set_id=?', $setId)
            ->where('attribute_id=?', $attributeId);
        $row = $this->getConnection()->fetchRow($select);
        if ($row) {
            // update
            if (!is_null($sortOrder)) {
                $bind['sort_order'] = $sortOrder;
            }

            $this->getConnection()->update(
                $this->getTable('eav/entity_attribute'),
                $bind,
                $this->getConnection()->quoteInto('entity_attribute_id=?', $row['entity_attribute_id'])
            );
        }
        else {
            if (is_null($sortOrder)) {
                $select = $this->getConnection()->select()
                    ->from($this->getTable('eav/entity_attribute'), 'MAX(sort_order) + 10')
                    ->where('entity_type_id=?', $entityType)
                    ->where('attribute_set_id=?', $setId)
                    ->where('attribute_group_id=?', $groupId);
                $sortOrder = $this->getConnection()->fetchOne($select);
            }
            $bind['sort_order'] = $sortOrder;
            $this->getConnection()->insert($this->getTable('eav/entity_attribute'), $bind);
        }

        return $this;
    }
}