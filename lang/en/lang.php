<?php

return [
    'base' => [
        'btn_workflow_create' => 'Create and...',
        'btn_workflow_update' => 'Save and...',
        'confirm_no_trans_title' => 'Forbidden transitions',
        'error' => [
            'trans_not_found' => 'Unable to find the transition :t_name on the model :m_name with the WF :wf_name.',
        ],
        'must_trans' => 'Mandatory transition',
        'popup' => [
            'confirm_title' => 'Confirmation',
            'next_place_label' => 'Next state: ',
            'save_no_transition' => 'Your model will be saved without applying a transition.',
            'transition' => 'Transition: ',
        ],
        'state_change_forbidden' => 'State change forbidden',
    ],
    'plugin' => [
        'description' => 'Allows generating and managing workflows from the Symphony/Workflow system',
        'name' => 'Workflow Plugin',
    ],
    'workflow' => [
        'change_state' => 'Change state',
        'no_change_state' => 'Do not change state',
        'state' => 'State',
        'state_change_forbidden' => 'State change forbidden',
    ],
];
