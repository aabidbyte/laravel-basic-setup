<x-ui.type-confirm-dialog-body :item-label="$modelLabel"
                               :confirm-event="'datatable:action-confirmed:' . $datatableId"
                               :confirm-data="['actionKey' => 'forceDeleteModel', 'uuid' => $modelUuid]" />
