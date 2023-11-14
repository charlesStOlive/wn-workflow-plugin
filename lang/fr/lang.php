<?php

return [
    'base' => [
        'btn_workflow_create' => 'Créer et...',
        'btn_workflow_update' => 'Sauver et...',
        'confirm_no_trans_title' => 'Transitions interdites',
        'error' => [
            'trans_not_found' => 'Impossible de trouver la transition :t_name sur le modèle :m_name avec le WF :wf_name.',
        ],
        'must_trans' => 'Transition obligatoire',
        'popup' => [
            'confirm_title' => 'Confirmation',
            'next_place_label' => 'État suivant : ',
            'save_no_transition' => 'Votre modèle sera enregistré sans appliquer de transition.',
            'transition' => 'Transition : ',
        ],
        'state_change_forbidden' => 'Changement d\'état interdit',
    ],
    'plugin' => [
        'description' => 'Permet de générer des workflows et de les gérer à partir du système Symphony/Workflow',
        'name' => 'Plugin Workflow',
    ],
    'workflow' => [
        'change_state' => 'Changer d\'état',
        'no_change_state' => 'Ne pas changer d\'état',
        'state' => 'Etat',
        'state_change_forbidden' => 'Changement état interdit',
    ],
];
